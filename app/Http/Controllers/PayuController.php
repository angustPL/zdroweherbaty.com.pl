<?php

namespace App\Http\Controllers;

use App\Services\PayuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayuController extends Controller
{
    public function __construct(
        private PayuService $payuService
    ) {}

    /**
     * Obsługuje powrót użytkownika po płatności (continue_url)
     */
    public function success(Request $request)
    {
        Log::info('PayU success callback', [
            'all_params' => $request->all(),
            'query_params' => $request->query(),
            'orderId' => $request->get('orderId'),
            'extOrderId' => $request->get('extOrderId'),
            'url' => $request->fullUrl(),
        ]);

        // PayU może zwrócić orderId lub extOrderId w parametrach URL
        $orderId = $request->get('orderId');
        $extOrderId = $request->get('extOrderId');

        $payment = null;
        $payuOrderId = null;

        // Najpierw spróbuj znaleźć po orderId (PayU orderId)
        if ($orderId) {
            $payment = \App\Models\Payment::where('payu_order_id', $orderId)->first();
            if ($payment) {
                $payuOrderId = $orderId;
                Log::info('PayU success: Found payment by orderId', [
                    'orderId' => $orderId,
                    'payment_id' => $payment->id,
                    'ext_order_id' => $payment->order->ext_order_id ?? null,
                ]);
            }
        }

        // Jeśli nie znaleziono, spróbuj po extOrderId
        if (!$payment && $extOrderId) {
            $payment = \App\Models\Payment::where('ext_order_id', $extOrderId)->first();
            if ($payment) {
                Log::info('PayU success: Found payment by extOrderId', [
                    'extOrderId' => $extOrderId,
                    'payment_id' => $payment->id,
                ]);
            }
        }

        // Jeśli nadal nie znaleziono, spróbuj znaleźć po extOrderId z zamówienia
        if (!$payment && $extOrderId) {
            $order = \App\Models\Order::where('ext_order_id', $extOrderId)->first();
            if ($order && $order->payment) {
                $payment = $order->payment;
                Log::info('PayU success: Found payment via order by extOrderId', [
                    'extOrderId' => $extOrderId,
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                ]);
            }
        }

        // Jeśli nadal nie znaleziono, spróbuj znaleźć ostatnie zamówienie z PayU płatnością
        // (może PayU nie zwraca parametrów w URL, ale możemy znaleźć ostatnie zamówienie)
        if (!$payment) {
            // Znajdź ostatnią płatność PayU z ostatnich 10 minut
            $tenMinutesAgo = Carbon::now()->subMinutes(10);
            $payment = \App\Models\Payment::whereNotNull('payu_order_id')
                ->where('created_at', '>=', $tenMinutesAgo)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($payment) {
                $payuOrderId = $payment->payu_order_id;
                Log::info('PayU success: Found payment by recent PayU order', [
                    'payment_id' => $payment->id,
                    'payu_order_id' => $payuOrderId,
                    'ext_order_id' => $payment->order->ext_order_id ?? null,
                ]);
            }
        }

        if ($payment && $payment->order && $payment->order->ext_order_id) {
            $paymentStatus = null;
            $paymentFailed = false;
            $failureReason = null;

            // Sprawdź aktualny status płatności w PayU (jeśli mamy payu_order_id)
            if ($payuOrderId) {
                $orderStatus = $this->payuService->getOrderStatus($payuOrderId);

                if ($orderStatus) {
                    // Zaktualizuj dane płatności w bazie lokalnej PRZED przekierowaniem
                    $this->updatePaymentFromPayuResponse($payment, $orderStatus, $payuOrderId);

                    // Odśwież płatność, aby pobrać aktualny status
                    $payment->refresh();
                    $paymentStatus = $payment->status;

                    // Sprawdź czy płatność nie powiodła się
                    if ($paymentStatus === 'failed') {
                        $paymentFailed = true;
                        $failureReason = $payment->failure_reason;
                    }
                }
            } else {
                // Jeśli nie mamy payu_order_id, odśwież płatność aby sprawdzić aktualny status
                $payment->refresh();
                $paymentStatus = $payment->status;

                if ($paymentStatus === 'failed') {
                    $paymentFailed = true;
                    $failureReason = $payment->failure_reason;
                }
            }

            // Przekieruj na stronę zamówienia
            Log::info('PayU success: Redirecting to order info', [
                'payu_order_id' => $payuOrderId,
                'ext_order_id' => $payment->order->ext_order_id,
                'order_id' => $payment->order->id,
                'payment_status' => $paymentStatus,
                'payment_failed' => $paymentFailed,
            ]);

            $redirect = redirect()->route('order.info', ['ext_order_id' => $payment->order->ext_order_id]);

            // Jeśli płatność nie powiodła się, przekieruj z komunikatem błędu
            if ($paymentFailed) {
                return $redirect->with('order_error', $failureReason ?? 'Płatność nie została zrealizowana. Spróbuj ponownie.');
            }

            // Jeśli płatność zakończona sukcesem, przekieruj z komunikatem sukcesu
            if ($paymentStatus === 'completed') {
                return $redirect->with('order_success', true);
            }

            // W innych przypadkach (pending, waiting_for_confirmation) przekieruj bez komunikatu
            return $redirect;
        } else {
            Log::warning('PayU success: Payment or order not found, trying to find order directly', [
                'payu_order_id' => $orderId,
                'ext_order_id' => $extOrderId,
                'payment_found' => $payment !== null,
                'order_found' => $payment && $payment->order !== null,
                'ext_order_id_exists' => $payment && $payment->order && $payment->order->ext_order_id !== null,
            ]);

            // Jeśli mamy extOrderId, spróbuj znaleźć zamówienie bezpośrednio
            if ($extOrderId) {
                $order = \App\Models\Order::where('ext_order_id', $extOrderId)->first();
                if ($order) {
                    Log::info('PayU success: Found order directly by ext_order_id, redirecting', [
                        'ext_order_id' => $extOrderId,
                        'order_id' => $order->id,
                    ]);
                    return redirect()->route('order.info', ['ext_order_id' => $extOrderId]);
                }
            }

            // Jeśli mamy orderId, spróbuj znaleźć płatność po payu_order_id i zamówienie
            if ($orderId) {
                $payment = \App\Models\Payment::where('payu_order_id', $orderId)->with('order')->first();
                if ($payment && $payment->order && $payment->order->ext_order_id) {
                    Log::info('PayU success: Found payment with order by payu_order_id, redirecting', [
                        'payu_order_id' => $orderId,
                        'ext_order_id' => $payment->order->ext_order_id,
                    ]);
                    return redirect()->route('order.info', ['ext_order_id' => $payment->order->ext_order_id]);
                }
            }
        }

        // Jeśli nie znaleziono zamówienia, przekieruj na stronę składania zamówienia
        Log::error('PayU success: Could not find order or payment, redirecting to order.create', [
            'payu_order_id' => $orderId,
            'ext_order_id' => $extOrderId,
            'request_params' => $request->all(),
        ]);
        return redirect()->route('order.create')
            ->with('message', 'Dziękujemy za złożenie zamówienia. Status płatności zostanie zaktualizowany po weryfikacji.');
    }

    /**
     * Obsługuje powiadomienie od PayU o statusie płatności (notify_url)
     */
    public function notify(Request $request)
    {
        $body = $request->getContent();
        $signatureHeader = $request->header('OpenPayu-Signature', '');

        Log::info('PayU notify callback', [
            'body' => $body,
            'signature' => $signatureHeader,
            'headers' => $request->headers->all(),
        ]);

        // Weryfikuj sygnaturę
        if (!$this->payuService->verifySignature($body, $signatureHeader)) {
            Log::warning('PayU notify: Invalid signature', [
                'body' => $body,
                'signature' => $signatureHeader,
            ]);
            return response('Invalid signature', 400);
        }

        $data = json_decode($body, true);

        // PayU wysyła dane w formacie: { "order": { "orderId": "...", "status": "...", ... } }
        if (isset($data['order']['orderId'])) {
            $orderId = $data['order']['orderId'];

            // Znajdź płatność po payu_order_id
            $payment = \App\Models\Payment::where('payu_order_id', $orderId)->first();

            if ($payment && $payment->order) {
                $oldStatus = $payment->status;

                // Zaktualizuj dane płatności w bazie lokalnej
                $this->updatePaymentFromPayuResponse($payment, $data['order'], $orderId);

                // Odśwież płatność, aby pobrać nowy status
                $payment->refresh();

                Log::info('PayU notify: Payment updated', [
                    'order_id' => $payment->order->id,
                    'ext_order_id' => $payment->order->ext_order_id,
                    'payu_order_id' => $orderId,
                    'old_status' => $oldStatus,
                    'new_status' => $payment->status,
                    'order_status' => $payment->order->status,
                ]);

                // Jeśli płatność nie powiodła się, zapisz informację o błędzie
                if ($payment->status === 'failed' && $payment->failure_reason) {
                    Log::warning('PayU notify: Payment failed', [
                        'order_id' => $payment->order->id,
                        'ext_order_id' => $payment->order->ext_order_id,
                        'payu_order_id' => $orderId,
                        'failure_reason' => $payment->failure_reason,
                    ]);
                }
            } else {
                Log::warning('PayU notify: Payment or order not found', [
                    'payu_order_id' => $orderId,
                    'payment_found' => $payment !== null,
                    'order_found' => $payment && $payment->order !== null,
                ]);
            }
        } else {
            Log::warning('PayU notify: Invalid data structure', [
                'data' => $data,
            ]);
        }

        return response('OK', 200);
    }

    /**
     * Aktualizuje dane płatności w bazie lokalnej na podstawie odpowiedzi z PayU.
     *
     * @param \App\Models\Payment $payment
     * @param array $payuData Dane z PayU (order lub orders[0])
     * @param string $orderId PayU orderId
     */
    private function updatePaymentFromPayuResponse(\App\Models\Payment $payment, array $payuData, string $orderId): void
    {
        // PayU może zwrócić różne struktury odpowiedzi
        // Sprawdź czy to tablica orders lub bezpośrednio order
        $orderData = null;
        if (isset($payuData['orders']) && is_array($payuData['orders']) && count($payuData['orders']) > 0) {
            $orderData = $payuData['orders'][0];
        } elseif (isset($payuData['status'])) {
            $orderData = $payuData;
        }

        if (!$orderData) {
            Log::warning('PayU: Could not extract order data', [
                'payu_data' => $payuData,
            ]);
            return;
        }

        $status = $orderData['status'] ?? null;
        $statusDesc = $orderData['statusDesc'] ?? null;

        // Mapuj status PayU na status lokalny
        $localStatus = $this->payuService->mapPayuStatusToLocal($status);

        // Przygotuj dane do aktualizacji
        $updateData = [
            'status' => $localStatus,
            'payu_data' => $payuData, // Zapisz pełną odpowiedź z PayU
        ];

        // Jeśli płatność zakończona sukcesem, ustaw paid_at
        if ($localStatus === 'completed') {
            $updateData['paid_at'] = now();
        }

        // Jeśli płatność nie powiodła się, zapisz przyczynę
        if ($localStatus === 'failed' && $statusDesc) {
            $updateData['failure_reason'] = $statusDesc;
        }

        // Zaktualizuj płatność
        $payment->update($updateData);

        // Zaktualizuj status zamówienia
        $orderStatus = $this->payuService->mapPaymentStatusToOrderStatus($localStatus);
        if ($orderStatus) {
            $payment->order->update([
                'status' => $orderStatus,
            ]);
        }

        Log::info('PayU: Payment updated in local database', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order->id,
            'payu_order_id' => $orderId,
            'payu_status' => $status,
            'local_status' => $localStatus,
            'order_status' => $orderStatus,
        ]);
    }
}
