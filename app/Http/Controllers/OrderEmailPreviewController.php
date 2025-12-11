<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\EnovaOrder;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderEmailPreviewController extends Controller
{
    /**
     * Wyświetl podgląd emaila dla zamówienia
     */
    public function preview(string $ext_order_id)
    {
        \Log::info('Email preview requested for GUID: ' . $ext_order_id);

        try {
            $enovaOrder = null;
            // Najpierw sprawdź czy zamówienie jest w lokalnej bazie
            $order = Order::where('ext_order_id', $ext_order_id)->first();

            if (!$order) {
                // Jeśli nie ma w lokalnej bazie, sprawdź czy jest w Enova
                $enovaOrder = EnovaOrder::byGuid($ext_order_id)->first();

                if ($enovaOrder) {
                    // Konwertuj zamówienie z Enova na format Order (dla emaila)
                    $order = $this->convertEnovaOrderToOrder($enovaOrder);
                } else {
                    // Sprawdź czy w ogóle są jakieś zamówienia w Enova
                    $anyEnovaOrder = EnovaOrder::first();
                    if (!$anyEnovaOrder) {
                        abort(404, 'Zamówienie nie znalezione. Brak połączenia z Enova lub brak zamówień.');
                    } else {
                        abort(404, 'Zamówienie o GUID "' . $ext_order_id . '" nie zostało znalezione ani w lokalnej bazie, ani w Enova.');
                    }
                }
            } else {
                // Jeśli zamówienie jest w lokalnej bazie, sprawdź czy jest też w Enova (dla NumerPelny)
                $enovaOrder = EnovaOrder::byGuid($ext_order_id)->first();
            }

            // Ustaw orderNumber (NumerPelny z Enova jeśli dostępny, w przeciwnym razie ext_order_id)
            if ($enovaOrder && !empty($enovaOrder->NumerPelny)) {
                $order->orderNumber = $enovaOrder->NumerPelny;
            } else {
                $order->orderNumber = $order->ext_order_id;
            }

            // Załaduj relację payment jeśli istnieje (tylko jeśli order ma ID w bazie)
            if (isset($order->id) && $order->id) {
                try {
                    $order->load('payment');
                } catch (\Exception $e) {
                    // Ignoruj błędy ładowania relacji
                }
            }

            // Użyj Markdown do renderowania emaila tak jak przy password reset
            return response(app(\Illuminate\Mail\Markdown::class)->render('emails.order-confirmation', [
                'order' => $order
            ]), 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            // W przypadku błędu, zwróć 404 z informacją o błędzie
            \Log::error('Błąd podglądu emaila: ' . $e->getMessage(), [
                'ext_order_id' => $ext_order_id,
                'trace' => $e->getTraceAsString()
            ]);
            abort(404, 'Błąd podczas pobierania zamówienia: ' . $e->getMessage());
        }
    }

    /**
     * Konwertuj zamówienie z Enova na format Order (dla podglądu emaila)
     */
    private function convertEnovaOrderToOrder(EnovaOrder $enovaOrder): Order
    {
        // Pobierz pozycje zamówienia
        $positions = $enovaOrder->getPositions();

        // Pobierz odbiorcę
        $recipient = $enovaOrder->getRecipient();

        // Pobierz kontrahenta do faktury
        $contractor = $enovaOrder->getInvoiceContractor();

        // Przygotuj items (tylko produkty, bez dostawy)
        $items = [];
        $deliveryCost = 0;
        $deliveryName = 'Dostawa';

        foreach ($positions as $position) {
            // Sprawdź czy to dostawa (może być pozycją bez produktu lub z określoną nazwą)
            $isDelivery = empty($position->Towar) ||
                stripos($position->Nazwa ?? '', 'dostawa') !== false ||
                stripos($position->Nazwa ?? '', 'paczkomat') !== false;

            if ($isDelivery) {
                $deliveryCost = (float) ($position->CenaValue ?? 0);
                $deliveryName = $position->Nazwa ?? 'Dostawa';
            } else {
                $items[] = [
                    'name' => $position->NazwaWWW ?? $position->Nazwa ?? 'Produkt',
                    'price' => (float) ($position->CenaValue ?? 0),
                    'quantity' => (int) ($position->IloscValue ?? 1),
                ];
            }
        }

        // Utwórz obiekt Order (nie zapisujemy do bazy, tylko do wyświetlenia)
        $order = new Order();
        $order->ext_order_id = $enovaOrder->Guid;
        $order->status = \App\Enums\OrderStatus::PROCESSING; // Domyślnie processing dla zamówień z Enova
        $order->items = $items;

        // Oblicz subtotal z produktów
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        }
        $order->subtotal = $subtotal;
        $order->delivery_cost = $deliveryCost;
        $order->is_free_delivery = $deliveryCost == 0;
        $order->delivery_name = $deliveryName;
        $order->total = (float) ($enovaOrder->SumaBrutto ?? 0);

        // Data z Enova (Data + Czas)
        if (isset($enovaOrder->Data) && $enovaOrder->Data) {
            $data = Carbon::parse($enovaOrder->Data);
            if (isset($enovaOrder->Czas) && $enovaOrder->Czas) {
                $timeParts = explode(':', $enovaOrder->Czas);
                $data->setTime((int) ($timeParts[0] ?? 0), (int) ($timeParts[1] ?? 0));
            }
            $order->created_at = $data;
        } else {
            $order->created_at = Carbon::now();
        }

        // Dane klienta z odbiorcy
        if ($recipient) {
            // Podziel ImieNazwisko jeśli jest razem
            $fullName = $recipient->ImieNazwisko ?? '';
            $nameParts = explode(' ', $fullName, 2);
            $order->customer_first_name = $nameParts[0] ?? '';
            $order->customer_last_name = $nameParts[1] ?? ($recipient->Nazwisko ?? '');

            // Email i telefon z Features (jeśli nie ma w odbiorcy)
            // Użyj accessorów z EnovaOrder
            $order->customer_email = $enovaOrder->email ?? $recipient->Email ?? '';
            $order->customer_phone = $enovaOrder->phone ?? $recipient->AdresTelefon ?? null;

            $order->delivery_street = $recipient->AdresUlica ?? '';
            $order->delivery_street_number = $recipient->AdresNrDomu ?? '';
            $order->delivery_apartment = $recipient->AdresNrLokalu ?? null;
            $order->delivery_city = $recipient->AdresMiejscowosc ?? '';
            $order->delivery_postal_code = $recipient->AdresKodPocztowy ?? '';
            $order->delivery_post_office = $recipient->AdresPoczta ?? '';
            $order->delivery_country = 'Polska';
        }

        // Dane faktury z kontrahenta
        if ($contractor) {
            $order->invoice_required = true;
            $order->invoice_company_name = $contractor->Nazwa ?? '';
            $order->invoice_nip = $contractor->NIP ?? '';
            $order->invoice_street = $contractor->AdresUlica ?? '';
            $order->invoice_street_number = $contractor->AdresNrDomu ?? '';
            $order->invoice_apartment = $contractor->AdresNrLokalu ?? null;
            $order->invoice_city = $contractor->AdresMiejscowosc ?? '';
            $order->invoice_postal_code = $contractor->AdresKodPocztowy ?? '';
            $order->invoice_post_office = $contractor->AdresPoczta ?? '';
        } else {
            $order->invoice_required = false;
        }

        // Uwagi z Features (użyj accessora)
        $order->notes = $enovaOrder->notes ?? null;

        // Paczkomat - sprawdź w uwagach
        if ($order->notes && stripos($order->notes, 'Paczkomat:') !== false) {
            // Wyciągnij dane paczkomatu z uwag (format: "Paczkomat: name, address.line1, address.line2")
            preg_match('/Paczkomat:\s*(.+?)(?:\n|$)/i', $order->notes, $matches);
            if (!empty($matches[1])) {
                $parts = explode(',', $matches[1]);
                if (count($parts) >= 3) {
                    $order->parcel_locker_data = [
                        'name' => trim($parts[0]),
                        'address' => [
                            'line1' => trim($parts[1]),
                            'line2' => trim($parts[2]),
                        ],
                    ];
                }
            }
        }

        return $order;
    }

    /**
     * Wyślij email z zamówieniem
     */
    public function sendOrderEmail(string $ext_order_id)
    {
        try {
            // Znajdź zamówienie w lokalnej bazie
            $order = Order::where('ext_order_id', $ext_order_id)->first();

            if (!$order) {
                return "Order not found: " . $ext_order_id;
            }

            // Wyślij email
            \Mail::to($order->customer_email)->send(new \App\Mail\OrderConfirmation($order));

            return "Email sent successfully to: " . $order->customer_email;
        } catch (\Exception $e) {
            return "Error sending email: " . $e->getMessage();
        }
    }

    /**
     * Prosty podgląd emaila dla zamówienia
     */
    public function simplePreview(string $ext_order_id)
    {
        try {
            // Znajdź zamówienie w lokalnej bazie
            $order = Order::where('ext_order_id', $ext_order_id)->first();

            if (!$order) {
                return "Order not found: " . $ext_order_id;
            }

            // Użyj oryginalnego szablonu z komponentami mail
            return view('emails.order-confirmation', [
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Wyświetl podgląd emaila z formularza kontaktowego
     */
    public function previewContactForm()
    {
        // Przykładowe dane do testowania
        $name = 'Jan Kowalski';
        $email = 'jan.kowalski@example.com';
        $messageText = "To jest przykładowa wiadomość testowa z formularza kontaktowego.\n\nSprawdzam jak wygląda nagłówek z logo oraz ogólny wygląd wiadomości email.\n\nPozdrawiam,\nJan Kowalski";

        return view('emails.contact-form', [
            'name' => $name,
            'email' => $email,
            'messageText' => $messageText
        ]);
    }

    /**
     * Wyświetl podgląd emaila resetowania hasła
     */
    public function previewPasswordReset()
    {
        // Przykładowe dane do testowania resetowania hasła
        $token = 'abcd1234efgh5678ijkl9012mnop3456';

        // Stworzenie przykładowego użytkownika (bez zapisu do bazy)
        $user = new class {
            public $email = 'jan.kowalski@example.com';

            public function getEmailForPasswordReset()
            {
                return $this->email;
            }
        };

        // Używamy domyślnego szablonu Laravela do resetowania hasła
        return (new \Illuminate\Auth\Notifications\ResetPassword($token))
            ->toMail($user);
    }
}
