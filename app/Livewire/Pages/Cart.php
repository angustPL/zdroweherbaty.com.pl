<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Services\CartService;
use App\Services\PromotionService;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\JsonLd;

class Cart extends Component
{
    public $cart = [];
    public $promotionCode = '';
    public $appliedPromotion = null;
    public $promotionDiscount = 0;
    public $promotionError = '';

    protected $listeners = [
        'cart-updated' => 'loadCart'
    ];

    public function mount()
    {
        // SEO Meta Tags - tylko canonical (reszta z domyślnych)
        SEOTools::setCanonical(url('/koszyk'));

        // Open Graph - tylko URL (reszta z domyślnych)
        SEOTools::opengraph()->setUrl(url('/koszyk'));

        // Schema.org JSON-LD - pełne dane dla koszyka
        JsonLd::setType('WebPage')
            ->addValue('url', url('/koszyk'))
            ->addValue('name', 'Koszyk - Zdrowe Herbaty BIFIX')
            ->addValue('description', 'Twój koszyk z herbatami BIFIX. Sprawdź produkty, ilości i przejdź do kasy.');

        $this->loadCart();
    }

    public function loadCart()
    {
        $cartService = app(CartService::class);
        $this->cart = $cartService->getCart();
        
        // NIE przywracamy kodu promocyjnego z sesji - użytkownik musi go wprowadzić za każdym razem
        // Wyczyść kod promocyjny przy każdym wejściu do koszyka
        $this->removePromotionCode();

        // GTM begin_checkout event
        if (!empty($this->cart['items'])) {
            try {
                // Set page type
                app('googletagmanager')->set('pageType', 'cart');

                $items = [];
                foreach ($this->cart['items'] as $item) {
                    $items[] = [
                        'item_id' => $item['id'],
                        'item_name' => $item['name'],
                        'price' => $item['price'],
                        'currency' => 'PLN',
                        'quantity' => $item['quantity']
                    ];
                }

                app('googletagmanager')->set([
                    'event' => 'begin_checkout',
                    'ecommerce' => [
                        'items' => $items,
                        'cart_total' => $this->cart['total'] ?? 0,
                        'cart_count' => $this->cart['item_count'] ?? 0
                    ]
                ]);
            } catch (\Exception $e) {
                // Silent fail - GTM event not critical for functionality
            }
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        try {
            $cartService = app(CartService::class);
            $cartService->updateQuantity($productId, $quantity);

            // Emituj event do odświeżenia ikony koszyka
            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Koszyk zaktualizowany',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Błąd podczas aktualizacji koszyka',
            ]);
        }
    }

    public function removeFromCart($productId)
    {
        try {
            $cartService = app(CartService::class);
            $cartService->removeFromCart($productId);

            // Emituj event do odświeżenia ikony koszyka
            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Produkt usunięty z koszyka',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Błąd podczas usuwania z koszyka',
            ]);
        }
    }

    public function clearCart()
    {
        try {
            $cartService = app(CartService::class);
            $cartService->clearCart();
            
            // Usuń kod promocyjny
            $this->removePromotionCode();

            // Emituj event do odświeżenia ikony koszyka
            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Koszyk został wyczyszczony',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Błąd podczas czyszczenia koszyka',
            ]);
        }
    }

    public function applyPromotionCode()
    {
        $this->promotionError = ''; // Wyczyść poprzedni błąd
        $this->validateAndApplyPromotion();
    }

    public function removePromotionCode()
    {
        $this->promotionCode = '';
        $this->appliedPromotion = null;
        $this->promotionDiscount = 0;
        $this->promotionError = '';
        session()->forget('promotion_code');
    }

    protected function validateAndApplyPromotion()
    {
        if (empty($this->promotionCode)) {
            $this->removePromotionCode();
            return;
        }

        try {
            $promotionService = app(PromotionService::class);
            $promotion = $promotionService->findByCode($this->promotionCode);

            if (!$promotion) {
                $this->appliedPromotion = null;
                $this->promotionDiscount = 0;
                $this->promotionError = 'Nieprawidłowy kod rabatowy';
                session()->forget('promotion_code');
                return;
            }

            // Przygotuj dane koszyka
            $cartItems = [];
            foreach ($this->cart['items'] ?? [] as $productId => $item) {
                $cartItems[] = [
                    'id' => $item['id'] ?? $productId,
                    'group' => $item['group'] ?? null,
                    'price' => $item['price'] ?? 0,
                    'quantity' => $item['quantity'] ?? 1,
                ];
            }
            $cartTotal = $this->cart['total'] ?? 0;

            // Waliduj promocję
            $validation = $promotionService->validatePromotion($promotion, $cartItems, $cartTotal);
            
            if (!$validation['valid']) {
                $this->appliedPromotion = null;
                $this->promotionDiscount = 0;
                $this->promotionError = $validation['message'] ?? 'Kod promocyjny nie może być zastosowany';
                session()->forget('promotion_code');
                return;
            }

            // Oblicz zniżkę
            $this->appliedPromotion = $promotion;
            $this->promotionDiscount = $promotionService->calculateDiscount($promotion, $cartItems, $cartTotal);
            $this->promotionError = ''; // Wyczyść błąd przy sukcesie
            
            // Zapisz kod w sesji
            session(['promotion_code' => strtoupper(trim($this->promotionCode))]);
        } catch (\Exception $e) {
            $this->appliedPromotion = null;
            $this->promotionDiscount = 0;
            $this->promotionError = 'Błąd podczas zastosowania kodu promocyjnego';
            session()->forget('promotion_code');
        }
    }

    public function getSnapshotData()
    {
        return [
            'cart' => $this->cart,
        ];
    }

    public function render()
    {
        return view('livewire.pages.cart')->layout('layouts.app');
    }
}
