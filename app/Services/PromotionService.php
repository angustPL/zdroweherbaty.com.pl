<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PromotionService
{
    /**
     * Znajdź promocję po kodzie.
     */
    public function findByCode(string $code): ?Promotion
    {
        return Promotion::where('code', strtoupper(trim($code)))
            ->where('type', 'code')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Sprawdź, czy promocja może być zastosowana do koszyka.
     *
     * @param Promotion $promotion
     * @param array $cartItems Array produktów z koszyka: [['id' => int, 'group' => string, 'price' => float, 'quantity' => int], ...]
     * @param float $cartTotal Całkowita wartość koszyka
     * @param string|null $customerEmail Email klienta (dla limitów per user)
     * @param Collection|null $existingPromotions Istniejące promocje w koszyku
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validatePromotion(
        Promotion $promotion,
        array $cartItems,
        float $cartTotal,
        ?string $customerEmail = null,
        ?Collection $existingPromotions = null
    ): array {
        // Sprawdź czy promocja jest ważna
        if (!$promotion->isValid()) {
            return [
                'valid' => false,
                'message' => 'Promocja nie jest już ważna lub została wyczerpana.',
            ];
        }

        // Sprawdź limit per użytkownik
        if (!$promotion->canBeUsedBy($customerEmail)) {
            return [
                'valid' => false,
                'message' => 'Osiągnięto limit użyć tej promocji.',
            ];
        }

        // Sprawdź minimalną kwotę zamówienia
        if ($promotion->min_order_amount && $cartTotal < $promotion->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Minimalna kwota zamówienia dla tej promocji to ' . number_format($promotion->min_order_amount, 2, ',', '.') . ' zł.',
            ];
        }

        // Sprawdź czy promocja dotyczy produktów w koszyku
        $hasProducts = $promotion->promotionProducts()->count() > 0;
        $hasGroups = $promotion->promotionGroups()->count() > 0;

        if ($hasProducts || $hasGroups) {
            $hasApplicableProduct = false;

            foreach ($cartItems as $item) {
                $productId = $item['id'] ?? null;
                $groupId = $item['group'] ?? null;

                if ($promotion->appliesToProduct($productId, $groupId)) {
                    $hasApplicableProduct = true;
                    break;
                }
            }

            if (!$hasApplicableProduct) {
                return [
                    'valid' => false,
                    'message' => 'Ta promocja nie dotyczy produktów w koszyku.',
                ];
            }
        }

        // Promocje z always_applicable są zawsze stosowane
        if ($promotion->always_applicable) {
            // Pomijamy sprawdzanie łączenia
        } elseif ($existingPromotions && $existingPromotions->isNotEmpty()) {
            // Sprawdź czy można łączyć z istniejącymi promocjami
            foreach ($existingPromotions as $existingPromotion) {
                // Pomijamy promocje z always_applicable przy sprawdzaniu
                if ($existingPromotion->always_applicable) {
                    continue;
                }
                
                if (!$this->canCombinePromotions($promotion, $existingPromotion)) {
                    return [
                        'valid' => false,
                        'message' => 'Ta promocja nie może być łączona z innymi aktywnymi promocjami.',
                    ];
                }
            }
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Oblicz zniżkę dla koszyka z uwzględnieniem produktów.
     *
     * @param Promotion $promotion
     * @param array $cartItems Array produktów z koszyka
     * @param float $cartTotal Całkowita wartość koszyka
     * @return float Kwota zniżki
     */
    public function calculateDiscount(Promotion $promotion, array $cartItems, float $cartTotal): float
    {
        // Jeśli promocja dotyczy tylko wybranych produktów/grup, oblicz tylko dla nich
        $hasProducts = $promotion->promotionProducts()->count() > 0;
        $hasGroups = $promotion->promotionGroups()->count() > 0;

        if ($hasProducts || $hasGroups) {
            $applicableTotal = 0;

            foreach ($cartItems as $item) {
                $productId = $item['id'] ?? null;
                $groupId = $item['group'] ?? null;
                $price = $item['price'] ?? 0;
                $quantity = $item['quantity'] ?? 1;

                if ($promotion->appliesToProduct($productId, $groupId)) {
                    $applicableTotal += $price * $quantity;
                }
            }

            return $promotion->calculateDiscount($applicableTotal);
        }

        // Dla promocji ogólnych oblicz na całej kwocie
        return $promotion->calculateDiscount($cartTotal);
    }

    /**
     * Pobierz wszystkie aktywne promocje automatyczne.
     *
     * @return Collection<Promotion>
     */
    public function getAutomaticPromotions(): Collection
    {
        return Promotion::where('type', 'automatic')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->get();
    }

    /**
     * Zastosuj promocje automatyczne do koszyka.
     *
     * @param array $cartItems
     * @param float $cartTotal
     * @param float $deliveryCost
     * @param Promotion|null $existingPromotion Istniejąca promocja (np. kod promocyjny)
     * @return array ['discount_amount' => float, 'free_delivery' => bool, 'promotion' => Promotion|null, 'promotions' => Collection]
     */
    public function applyAutomaticPromotions(
        array $cartItems,
        float $cartTotal,
        float $deliveryCost,
        ?Promotion $existingPromotion = null
    ): array {
        $result = [
            'discount_amount' => 0,
            'free_delivery' => false,
            'promotion' => null,
            'promotions' => collect(), // Wszystkie zastosowane promocje
        ];

        $automaticPromotions = $this->getAutomaticPromotions();

        foreach ($automaticPromotions as $promotion) {
            // Promocje z always_applicable są zawsze stosowane
            if ($promotion->always_applicable) {
                // Pomijamy sprawdzanie łączenia - zawsze dodajemy
            } elseif ($existingPromotion) {
                // Sprawdź czy można łączyć z istniejącą promocją
                if (!$this->canCombinePromotions($existingPromotion, $promotion)) {
                    continue; // Nie można łączyć
                }
            }

            $validation = $this->validatePromotion($promotion, $cartItems, $cartTotal);

            if (!$validation['valid']) {
                continue;
            }

            if ($promotion->discount_type === 'free_delivery') {
                // Sprawdź czy spełniony jest warunek minimalnej kwoty
                if (!$promotion->min_order_amount || $cartTotal >= $promotion->min_order_amount) {
                    $result['free_delivery'] = true;
                    if (!$result['promotion'] || $promotion->priority > $result['promotion']->priority) {
                        $result['promotion'] = $promotion;
                    }
                    $result['promotions']->push($promotion);
                }
            } else {
                // Dla innych typów promocji oblicz zniżkę
                $discount = $this->calculateDiscount($promotion, $cartItems, $cartTotal);
                if ($discount > $result['discount_amount']) {
                    $result['discount_amount'] = $discount;
                    if (!$result['promotion'] || $promotion->priority > $result['promotion']->priority) {
                        $result['promotion'] = $promotion;
                    }
                    $result['promotions']->push($promotion);
                }
            }
        }

        return $result;
    }

    /**
     * Sprawdź, czy dwie promocje mogą być łączone.
     *
     * @param Promotion $promotion1
     * @param Promotion $promotion2
     * @return bool
     */
    public function canCombinePromotions(Promotion $promotion1, Promotion $promotion2): bool
    {
        // Obie promocje muszą pozwalać na łączenie, żeby można je było połączyć
        return $promotion1->can_combine_with_others && $promotion2->can_combine_with_others;
    }

    /**
     * Wybierz najlepszą promocję z listy (na podstawie priorytetu i wartości zniżki).
     *
     * @param Collection $promotions
     * @param array $cartItems
     * @param float $cartTotal
     * @return Promotion|null
     */
    public function selectBestPromotion(Collection $promotions, array $cartItems, float $cartTotal): ?Promotion
    {
        if ($promotions->isEmpty()) {
            return null;
        }

        // Sortuj po priorytecie (wyższy = lepszy), potem po wartości zniżki
        return $promotions
            ->sortByDesc(function ($promotion) use ($cartItems, $cartTotal) {
                $discount = $this->calculateDiscount($promotion, $cartItems, $cartTotal);
                return [$promotion->priority, $discount];
            })
            ->first();
    }

    /**
     * Zarejestruj użycie promocji w zamówieniu.
     *
     * @param Promotion $promotion
     * @param Order $order
     * @return void
     */
    public function recordUsage(Promotion $promotion, Order $order): void
    {
        // Dodaj relację w tabeli pivot
        $order->promotions()->attach($promotion->id);

        // Zwiększ licznik użyć
        $promotion->increment('usage_count');
    }
}
