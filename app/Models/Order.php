<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model reprezentujący zamówienie w lokalnej bazie danych.
 *
 * Zawiera tylko dane lokalne. Jeśli zamówienie istnieje w Enova,
 * dane są pobierane bezpośrednio z Enova (model EnovaOrder) i nie są zapisywane lokalnie.
 */
class Order extends Model
{
    use SoftDeletes;

    /**
     * Pola, które mogą być masowo przypisane.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Identyfikatory
        'ext_order_id',

        // Status
        'status',

        // Dane klienta
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',

        // Adres dostawy
        'delivery_street',
        'delivery_street_number',
        'delivery_apartment',
        'delivery_city',
        'delivery_postal_code',
        'delivery_post_office',
        'delivery_country',

        // Dane do faktury
        'invoice_required',
        'invoice_company_name',
        'invoice_nip',
        'invoice_street',
        'invoice_street_number',
        'invoice_apartment',
        'invoice_city',
        'invoice_postal_code',
        'invoice_post_office',

        // Dostawa
        'delivery_id',
        'delivery_name',
        'delivery_price',

        // Produkty
        'items',

        // Kwoty
        'subtotal',
        'delivery_cost',
        'is_free_delivery',
        'total',
        'currency',

        // Dodatkowe informacje
        'notes',
        'parcel_locker_data',
    ];

    /**
     * Pola, które powinny być rzutowane.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_required' => 'boolean',
        'is_free_delivery' => 'boolean',
        'items' => 'array',
        'parcel_locker_data' => 'array',
        'delivery_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Mapuje status z obsługą starych wartości "paid".
     * Obsługuje kompatybilność wsteczną z wartościami "paid".
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Mapuj stare wartości na nowe enum
                return match ($value) {
                    'paid' => OrderStatus::PROCESSING, // "paid" to stara wartość, mapujemy na PROCESSING
                    default => OrderStatus::tryFrom($value) ?? OrderStatus::PENDING, // Używamy tryFrom() aby uniknąć wyjątków
                };
            },
            set: function ($value) {
                if ($value instanceof OrderStatus) {
                    return $value->value;
                }
                // Mapuj stare wartości przy zapisie
                return match ($value) {
                    'paid' => OrderStatus::PROCESSING->value,
                    default => $value,
                };
            }
        );
    }

    /**
     * Relacja z płatnościami.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relacja z ostatnią płatnością.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Pobierz pełne imię i nazwisko klienta.
     */
    public function getCustomerFullNameAttribute(): string
    {
        return trim("{$this->customer_first_name} {$this->customer_last_name}");
    }

    /**
     * Sprawdź, czy zamówienie ma płatność PayU.
     */
    public function hasPayuPayment(): bool
    {
        return $this->payments()
            ->whereNotNull('payu_order_id')
            ->exists();
    }

    /**
     * Pobierz numer zamówienia (lokalny ID).
     */
    public function getOrderNumberAttribute(): string
    {
        return "#{$this->id}";
    }
}
