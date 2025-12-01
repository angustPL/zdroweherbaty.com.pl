<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    /**
     * Pola, które mogą być masowo przypisane.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'conditions',
        'valid_from',
        'valid_to',
        'usage_limit',
        'usage_count',
        'usage_limit_per_user',
        'is_active',
        'can_combine_with_others',
        'always_applicable',
        'priority',
    ];

    /**
     * Pola, które powinny być rzutowane.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'conditions' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'usage_limit_per_user' => 'integer',
        'is_active' => 'boolean',
        'can_combine_with_others' => 'boolean',
        'always_applicable' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Relacja many-to-many z zamówieniami (tabela pivot order_promotions).
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_promotions')
            ->withTimestamps();
    }


    /**
     * Relacja z produktami (tabela pivot).
     * Jeśli brak rekordów = promocja dotyczy wszystkich produktów.
     */
    public function promotionProducts(): HasMany
    {
        return $this->hasMany(PromotionProduct::class);
    }

    /**
     * Pobierz ID produktów objętych promocją.
     */
    public function getProductIdsAttribute(): array
    {
        return $this->promotionProducts()->pluck('product_id')->toArray();
    }

    /**
     * Relacja z grupami produktów (tabela pivot).
     * Jeśli brak rekordów = promocja dotyczy wszystkich grup.
     */
    public function promotionGroups(): HasMany
    {
        return $this->hasMany(PromotionGroup::class);
    }

    /**
     * Pobierz ścieżki grup objętych promocją.
     */
    public function getGroupPathsAttribute(): array
    {
        return $this->promotionGroups()->pluck('group_path')->toArray();
    }

    /**
     * Sprawdź, czy promocja jest aktualnie ważna.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $now->gt($this->valid_to)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Sprawdź, czy promocja może być użyta przez danego użytkownika.
     */
    public function canBeUsedBy(string $email = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->usage_limit_per_user && $email) {
            $userUsageCount = $this->orders()
                ->where('customer_email', $email)
                ->count();

            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sprawdź, czy promocja dotyczy danego produktu.
     */
    public function appliesToProduct(int $productId, string $groupId = null): bool
    {
        // Jeśli nie ma ograniczeń produktów/grup, dotyczy wszystkich
        $hasProducts = $this->promotionProducts()->count() > 0;
        $hasGroups = $this->promotionGroups()->count() > 0;

        if (!$hasProducts && !$hasGroups) {
            return true;
        }

        // Sprawdź konkretne produkty
        if ($hasProducts && $this->promotionProducts()->where('product_id', $productId)->exists()) {
            return true;
        }

        // Sprawdź grupy produktów
        if ($hasGroups && $groupId && $this->promotionGroups()->where('group_path', $groupId)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Oblicz kwotę zniżki dla danej kwoty zamówienia.
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->min_order_amount && $orderAmount < $this->min_order_amount) {
            return 0;
        }

        $discount = 0;

        switch ($this->discount_type) {
            case 'percentage':
                $discount = $orderAmount * ($this->discount_value / 100);
                if ($this->max_discount_amount) {
                    $discount = min($discount, $this->max_discount_amount);
                }
                break;

            case 'fixed':
                $discount = min($this->discount_value, $orderAmount);
                break;

            case 'free_delivery':
                // Dla darmowej dostawy zwracamy 0 - logika jest w innym miejscu
                $discount = 0;
                break;
        }

        return round($discount, 2);
    }
}
