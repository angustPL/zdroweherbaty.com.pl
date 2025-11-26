<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayuService
{
    private string $posId;
    private string $key;
    private string $key2;
    private string $posAuthKey;
    private string $baseUrl;
    private bool $isSandbox;

    public function __construct()
    {
        $this->posId = config('enova.payment.payu.pos_id');
        $this->key = config('enova.payment.payu.key', ''); // Nieużywany w REST API, tylko dla kompatybilności
        $this->key2 = config('enova.payment.payu.key2');
        $this->posAuthKey = config('enova.payment.payu.pos_auth_key');

        // Określ czy to sandbox czy produkcja
        // Dla sandbox używamy secure.snd.payu.com, dla produkcji secure.payu.com
        $this->isSandbox = env('PAYU_SANDBOX', true); // Domyślnie sandbox dla dev

        // Base URL dla REST API
        $this->baseUrl = $this->isSandbox
            ? 'https://secure.snd.payu.com'
            : 'https://secure.payu.com';
    }

    /**
     * Pobiera token OAuth do autoryzacji
     */
    private function getAccessToken(): ?string
    {
        $cacheKey = 'payu_access_token';

        // Sprawdź cache (token ważny przez 1 godzinę)
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::asForm()->post("{$this->baseUrl}/pl/standard/user/oauth/authorize", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->posId,
                'client_secret' => $this->posAuthKey,
            ]);

            $status = $response->status();
            $body = $response->body();

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;

                if ($token) {
                    // Cache token na 1 godzinę (PayU zwykle daje token ważny 1h)
                    $expiresIn = $data['expires_in'] ?? 3600;
                    // now() helper z Laravel zwraca Carbon instance
                    Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 60)); // -60 sekund marginesu

                    Log::info('PayU OAuth token obtained', [
                        'token_length' => strlen($token),
                        'expires_in' => $expiresIn,
                    ]);

                    return $token;
                }
            }

            Log::error('PayU OAuth error', [
                'status' => $status,
                'body' => substr($body, 0, 500),
                'url' => "{$this->baseUrl}/pl/standard/user/oauth/authorize",
            ]);
        } catch (\Exception $e) {
            Log::error('PayU OAuth exception', [
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Tworzy zamówienie w PayU
     *
     * @param array $orderData Dane zamówienia
     * @param string|null $payuOption Wybrana opcja PayU (blik, c, ap, jp, przelew)
     * @return array|null Zwraca dane zamówienia z redirectUri lub null w przypadku błędu
     */
    public function createOrder(array $orderData, ?string $payuOption = null): ?array
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('PayU: Failed to get access token');
            return null;
        }

        // Przygotuj dane zamówienia zgodnie z dokumentacją PayU REST API
        // Buduj URL dynamicznie, aby działało za proxy (używa request()->getSchemeAndHttpHost())
        //
        // Dla środowiska deweloperskiego z domeną .test możesz użyć fwd.host jako proxy:
        // PAYU_CONTINUE_URL=https://fwd.host/http://zdroweherbaty.com.pl.test/payu/success
        // PAYU_NOTIFY_URL=https://fwd.host/http://zdroweherbaty.com.pl.test/payu/notify
        $continueUrl = config('enova.payment.payu.continue_url');
        if (empty($continueUrl)) {
            // Użyj request()->getSchemeAndHttpHost() aby działało za proxy
            $continueUrl = request()->getSchemeAndHttpHost() . '/payu/success';
        }

        // Dodaj extOrderId do continueUrl jako parametr, aby PayU go przekazał z powrotem
        if (isset($orderData['ext_order_id'])) {
            $separator = strpos($continueUrl, '?') !== false ? '&' : '?';
            $continueUrl .= $separator . 'extOrderId=' . urlencode($orderData['ext_order_id']);
        }

        $notifyUrl = config('enova.payment.payu.notify_url');
        if (empty($notifyUrl)) {
            $notifyUrl = request()->getSchemeAndHttpHost() . '/payu/notify';
        }

        $requestData = [
            'notifyUrl' => $notifyUrl,
            'customerIp' => request()->ip(),
            'merchantPosId' => $this->posId,
            'description' => $orderData['description'] ?? 'Zamówienie',
            'currencyCode' => $orderData['currency'] ?? 'PLN',
            'totalAmount' => $this->formatAmount($orderData['total_amount']),
            'continueUrl' => $continueUrl,
            'buyer' => $orderData['buyer'] ?? [],
            'products' => $orderData['products'] ?? [],
        ];

        // Dodaj extOrderId jeśli jest podane
        if (isset($orderData['ext_order_id'])) {
            $requestData['extOrderId'] = $orderData['ext_order_id'];
        }

        // Jeśli wybrano konkretną metodę płatności, dodaj payMethods
        // PayU REST API oczekuje: payMethods.payMethod jako obiekt (nie tablica)
        if ($payuOption) {
            // Mapuj wartości dla kompatybilności (jeśli ktoś używa 'przelew' zamiast 'p')
            $payuValue = $this->mapPayuOptionValue($payuOption);

            $requestData['payMethods'] = [
                'payMethod' => [
                    'type' => $this->getPayMethodType($payuValue),
                    'value' => $payuValue,
                ],
            ];

            Log::info('PayU: Adding payMethod', [
                'original_option' => $payuOption,
                'mapped_value' => $payuValue,
                'type' => $this->getPayMethodType($payuValue),
            ]);
        }

        try {
            // Wyłącz automatyczne przekierowania i ustaw nagłówki ręcznie
            $response = Http::withoutRedirecting()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ])
                ->post("{$this->baseUrl}/api/v2_1/orders", $requestData);

            $status = $response->status();
            $body = $response->body();
            $data = $response->json();

            Log::info('PayU order response', [
                'status' => $status,
                'headers' => $response->headers(),
                'location_header' => $response->header('Location'),
                'body_length' => strlen($body),
                'body_preview' => substr($body, 0, 500),
                'is_json' => $data !== null,
                'orderId' => $data['orderId'] ?? null,
                'redirectUri' => $data['redirectUri'] ?? null,
            ]);

            // PayU REST API zwraca 302 z JSON-em w body zawierającym redirectUri
            // To jest normalne zachowanie - traktujemy to jako sukces
            if ($status === 302 && $data && isset($data['redirectUri'])) {
                Log::info('PayU order created successfully (302 redirect)', [
                    'orderId' => $data['orderId'] ?? null,
                    'redirectUri' => $data['redirectUri'],
                ]);
                return $data;
            }

            if ($response->successful() && $data) {
                return $data;
            }

            // Jeśli zwrócono HTML zamiast JSON, to prawdopodobnie błąd autoryzacji lub endpoint
            if ($status === 200 && $data === null) {
                Log::error('PayU returned HTML instead of JSON', [
                    'url' => "{$this->baseUrl}/api/v2_1/orders",
                    'access_token_length' => strlen($accessToken ?? ''),
                ]);
            }

            // Sprawdź czy błąd to ORDER_NOT_UNIQUE - wtedy PayU zwraca orderId istniejącego zamówienia
            if ($status === 400 && $data && isset($data['status']['codeLiteral']) && $data['status']['codeLiteral'] === 'ORDER_NOT_UNIQUE') {
                $existingOrderId = $data['orderId'] ?? null;
                if ($existingOrderId) {
                    Log::info('PayU: Order with extOrderId already exists, returning existing orderId', [
                        'orderId' => $existingOrderId,
                        'extOrderId' => $orderData['ext_order_id'] ?? null,
                    ]);
                    // Zwróć dane z orderId, aby kontroler mógł pobrać status i redirectUri
                    return [
                        'orderId' => $existingOrderId,
                        'error' => 'ORDER_NOT_UNIQUE',
                        'extOrderId' => $orderData['ext_order_id'] ?? null,
                    ];
                }
            }

            Log::error('PayU create order error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'request' => $requestData,
            ]);
        } catch (\Exception $e) {
            Log::error('PayU create order exception', [
                'message' => $e->getMessage(),
                'request' => $requestData,
            ]);
        }

        return null;
    }

    /**
     * Pobiera status zamówienia
     */
    public function getOrderStatus(string $orderId): ?array
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/api/v2_1/orders/{$orderId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('PayU get order status exception', [
                'message' => $e->getMessage(),
                'orderId' => $orderId,
            ]);
        }

        return null;
    }

    /**
     * Mapuje status PayU na status lokalny (Enum).
     *
     * @param string|null $payuStatus
     * @return PaymentStatus
     */
    public function mapPayuStatusToLocal(?string $payuStatus): PaymentStatus
    {
        return match ($payuStatus) {
            'COMPLETED' => PaymentStatus::COMPLETED,
            'PENDING' => PaymentStatus::PENDING,
            'WAITING_FOR_CONFIRMATION' => PaymentStatus::WAITING_FOR_CONFIRMATION,
            'CANCELED', 'REJECTED' => PaymentStatus::FAILED,
            default => PaymentStatus::PENDING,
        };
    }

    /**
     * Mapuje status płatności na status zamówienia (Enum).
     *
     * @param PaymentStatus $paymentStatus
     * @return OrderStatus|null
     */
    public function mapPaymentStatusToOrderStatus(PaymentStatus $paymentStatus): ?OrderStatus
    {
        return match ($paymentStatus) {
            PaymentStatus::COMPLETED => OrderStatus::COMPLETED, // Zamówienie opłacone - oznaczone jako zrealizowane
            PaymentStatus::FAILED => OrderStatus::PENDING, // Płatność nieudana - zamówienie pozostaje w oczekiwaniu
            default => null, // Nie zmieniaj statusu zamówienia dla innych statusów (pending, waiting_for_confirmation)
        };
    }

    /**
     * Weryfikuje sygnaturę z notify_url
     *
     * Dla REST API PayU wysyła sygnaturę w nagłówku OpenPayu-Signature
     * Format: algorithm=MD5;signature=xxxxx
     */
    public function verifySignature(string $body, string $signatureHeader): bool
    {
        // Parsuj nagłówek sygnatury (format: algorithm=MD5;signature=xxxxx)
        $signatureParts = [];
        foreach (explode(';', $signatureHeader) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $signatureParts[$key] = $value;
        }

        $algorithm = $signatureParts['algorithm'] ?? 'MD5';
        $signature = $signatureParts['signature'] ?? '';

        if ($algorithm !== 'MD5') {
            Log::warning('PayU: Unsupported signature algorithm', ['algorithm' => $algorithm]);
            return false;
        }

        // Oblicz sygnaturę: MD5(key2 + body)
        $signatureString = $this->key2 . $body;
        $calculatedSignature = md5($signatureString);

        return strtoupper($calculatedSignature) === strtoupper($signature);
    }

    /**
     * Formatuje kwotę do formatu PayU (w groszach jako integer)
     */
    private function formatAmount(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Mapuje wartość opcji PayU na poprawną wartość dla API
     */
    private function mapPayuOptionValue(string $payuOption): string
    {
        // Mapuj 'przelew' na 'p' (przelew bankowy w PayU REST API)
        return match (strtolower($payuOption)) {
            'przelew' => 'p',
            default => $payuOption,
        };
    }

    /**
     * Określa typ metody płatności na podstawie opcji
     */
    private function getPayMethodType(string $payuOption): string
    {
        // Domyślnie PBL (Payment Button Library) dla większości metod
        return 'PBL';
    }
}
