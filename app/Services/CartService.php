<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class CartService
{
    private const CART_COOKIE_NAME = 'shopping_cart';
    private const CART_COOKIE_EXPIRY = 60 * 24 * 30; // 30 dni

    /**
     * Pobiera zawartość koszyka z cookies
     */
    public function getCart(): array
    {
        $cartData = Cookie::get(self::CART_COOKIE_NAME);

        Log::info('Getting cart from cookie:', ['data' => $cartData]);

        if (!$cartData) {
            return $this->getEmptyCart();
        }

        $cart = json_decode($cartData, true);

        // Sprawdź czy struktura jest poprawna
        if (!is_array($cart) || !isset($cart['items'])) {
            return $this->getEmptyCart();
        }

        Log::info('Cart loaded:', $cart);
        return $cart;
    }

    /**
     * Dodaje produkt do koszyka
     */
    public function addToCart(int $productId, string $productName, float $price, string $image, int $quantity = 1): array
    {
        $cart = $this->getCart();

        if (isset($cart['items'][$productId])) {
            // Produkt już istnieje - zwiększ ilość
            // $cart['items'][$productId]['quantity'] += $quantity;
        } else {
            // Nowy produkt
            $cart['items'][$productId] = [
                'id' => $productId,
                'name' => $productName,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $image
            ];
        }

        $this->updateCartTotals($cart);
        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Aktualizuje ilość produktu w koszyku
     */
    public function updateQuantity(int $productId, int $quantity): array
    {
        $cart = $this->getCart();

        if ($quantity <= 0) {
            // Usuń produkt jeśli ilość <= 0
            unset($cart['items'][$productId]);
        } else {
            // Aktualizuj ilość
            if (isset($cart['items'][$productId])) {
                $cart['items'][$productId]['quantity'] = $quantity;
            }
        }

        $this->updateCartTotals($cart);
        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Usuwa produkt z koszyka
     */
    public function removeFromCart(int $productId): array
    {
        $cart = $this->getCart();
        unset($cart['items'][$productId]);

        $this->updateCartTotals($cart);
        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Czyści cały koszyk
     */
    public function clearCart(): array
    {
        Log::info('Clearing cart...');
        $emptyCart = $this->getEmptyCart();
        $this->saveCart($emptyCart);
        Log::info('Cart cleared:', $emptyCart);
        return $emptyCart;
    }

    /**
     * Sprawdza czy produkt jest w koszyku
     */
    public function isProductInCart(int $productId): bool
    {
        $cart = $this->getCart();
        return isset($cart['items'][$productId]);
    }

    /**
     * Aktualizuje sumy w koszyku
     */
    private function updateCartTotals(array &$cart): void
    {
        $total = 0;
        $itemCount = 0;

        foreach ($cart['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        $cart['total'] = $total;
        $cart['item_count'] = count($cart['items']); // Liczba różnych produktów
    }

    /**
     * Zapisuje koszyk do cookies
     */
    private function saveCart(array $cart): void
    {
        Cookie::queue(
            self::CART_COOKIE_NAME,
            json_encode($cart),
            self::CART_COOKIE_EXPIRY
        );
    }

    /**
     * Zwraca pusty koszyk
     */
    private function getEmptyCart(): array
    {
        return [
            'items' => [],
            'total' => 0,
            'item_count' => 0
        ];
    }
}
