<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Delivery;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Log;

class EnovaXmlService
{
    /**
     * Generuje XML zamówienia dla Enova.
     *
     * @param Order $order
     * @return string XML content
     */
    public function generateXml(Order $order): string
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        // Session root
        $session = $dom->createElement('session');
        $session->setAttribute('xmlns', 'http://www.soneta.pl/schema/business');
        $session->setAttribute('business', 'true');
        $dom->appendChild($session);

        // Kontrahent (stały)
        $kontrahent = $dom->createElement('Kontrahent');
        $kontrahent->setAttribute('business', 'false');
        $kontrahent->setAttribute('id', 'id2');
        $kontrahent->setAttribute('key', 'Kod=' . config('enova.orders.contractor', 'WWW'));
        $session->appendChild($kontrahent);

        // DokumentHandlowy
        $dokument = $dom->createElement('DokumentHandlowy');
        $dokument->setAttribute('guid', $order->ext_order_id);
        $session->appendChild($dokument);

        // Nagłówek dokumentu
        $this->addHeader($dom, $dokument, $order);

        // Płatności
        $this->addPayments($dom, $dokument, $order);

        // Pozycje (produkty + dostawa)
        $this->addPositions($dom, $dokument, $order);

        // Dane kontrahenta (faktura) - ZAWSZE dodajemy
        $this->addContractorData($dom, $dokument, $order);

        // Dane odbiorcy (dostawa)
        $this->addRecipientData($dom, $dokument, $order);

        // Features (email, telefon, uwagi)
        $this->addFeatures($dom, $dokument, $order);

        return $dom->saveXML();
    }

    /**
     * Dodaje nagłówek dokumentu.
     */
    protected function addHeader(DOMDocument $dom, DOMElement $dokument, Order $order): void
    {
        $contractor = config('enova.orders.contractor', 'WWW');
        $symbol = config('enova.orders.symbol', 'ZOW');
        $warehouse = config('enova.orders.warehouse', 'WWW');

        $data = $order->created_at->format('Y-m-d');
        $czas = $order->created_at->format('H:i');

        $this->addElement($dom, $dokument, 'Definicja', null, ['where' => "Symbol={$symbol}"]);
        $this->addElement($dom, $dokument, 'Magazyn', null, ['where' => "Symbol={$warehouse}"]);
        $this->addElement($dom, $dokument, 'Data', $data);
        $this->addElement($dom, $dokument, 'Czas', $czas);
        $this->addElement($dom, $dokument, 'DataOperacji', $data);
        $this->addElement($dom, $dokument, 'Kontrahent', null, ['where' => "Kod={$contractor}"]);
        $this->addElement($dom, $dokument, 'Odbiorca', null, ['where' => "Kod={$contractor}"]);
    }

    /**
     * Dodaje sekcję płatności.
     */
    protected function addPayments(DOMDocument $dom, DOMElement $dokument, Order $order): void
    {
        $platnosci = $dom->createElement('Platnosci');
        $dokument->appendChild($platnosci);

        $platnosc = $dom->createElement('Platnosc');
        $platnosc->setAttribute('class', 'Soneta.Kasa.Naleznosc,Soneta.Kasa');
        $platnosci->appendChild($platnosc);

        // GUID sposobu zapłaty
        $payment = $order->payment;
        $paymentMethodGuid = $payment?->payment_method_guid ?? config('enova.payment.methods.gotowka');
        $terminDni = $payment?->termin_dni ?? 0;

        $this->addElement($dom, $platnosc, 'SposobZaplaty', $paymentMethodGuid);
        $this->addElement($dom, $platnosc, 'Kwota', $this->formatAmount($order->total) . ' PLN');
        $this->addElement($dom, $platnosc, 'TerminDni', (string) $terminDni);
    }

    /**
     * Dodaje pozycje (produkty + dostawa).
     */
    protected function addPositions(DOMDocument $dom, DOMElement $dokument, Order $order): void
    {
        $pozycje = $dom->createElement('Pozycje');
        $dokument->appendChild($pozycje);

        $items = $order->items ?? [];

        // Produkty
        foreach ($items as $item) {
            $this->addPosition($dom, $pozycje, $item);
        }

        // Dostawa jako pozycja
        if ($order->delivery_id) {
            $this->addDeliveryPosition($dom, $pozycje, $order);
        }
    }

    /**
     * Dodaje pozycję produktu.
     */
    protected function addPosition(DOMDocument $dom, DOMElement $pozycje, array $item): void
    {
        $pozycja = $dom->createElement('Pozycja');
        $pozycje->appendChild($pozycja);

        // Pobierz kod produktu z Enova
        $product = Product::find($item['id'] ?? null);
        $kod = $product?->Kod ?? '';

        // Pobierz cenę i jednostkę
        $price = $product?->price;
        $bruttoValue = $price?->BruttoValue ?? ($item['price'] ?? 0);
        $przecenaBruttoValue = $bruttoValue; // Jeśli nie ma przeceny, użyj normalnej ceny
        $jednostka = $price?->StandardowaIloscSymbol ?? $price?->Jednostka ?? 'szt';
        $ilosc = $item['quantity'] ?? 1;

        $this->addElement($dom, $pozycja, 'Towar', null, ['where' => "Kod={$kod}"]);
        $this->addElement($dom, $pozycja, 'Ilosc', "{$ilosc} {$jednostka}");
        $this->addElement($dom, $pozycja, 'Cena', $this->formatAmount($bruttoValue) . ' PLN');
        $this->addElement($dom, $pozycja, 'CenaPoRabacie', $this->formatAmount($przecenaBruttoValue) . ' PLN');
        $this->addElement($dom, $pozycja, 'Wspolczynnik', '1/1');
    }

    /**
     * Dodaje pozycję dostawy.
     */
    protected function addDeliveryPosition(DOMDocument $dom, DOMElement $pozycje, Order $order): void
    {
        $pozycja = $dom->createElement('Pozycja');
        $pozycje->appendChild($pozycja);

        // Pobierz kod dostawy z Enova
        $delivery = Delivery::find($order->delivery_id);
        $kod = $delivery?->Kod ?? '';

        $price = $delivery?->price;
        $bruttoValue = $price?->BruttoValue ?? ($order->delivery_price ?? 0);
        $jednostka = $price?->StandardowaIloscSymbol ?? $price?->Jednostka ?? 'szt';

        $this->addElement($dom, $pozycja, 'Towar', null, ['where' => "Kod={$kod}"]);
        $this->addElement($dom, $pozycja, 'Ilosc', "1 {$jednostka}");
        $this->addElement($dom, $pozycja, 'Cena', $this->formatAmount($bruttoValue) . ' PLN');
        $this->addElement($dom, $pozycja, 'CenaPoRabacie', $this->formatAmount($bruttoValue) . ' PLN');
        $this->addElement($dom, $pozycja, 'Wspolczynnik', '1/1');
    }

    /**
     * Dodaje dane kontrahenta (faktura) - ZAWSZE dodajemy.
     */
    protected function addContractorData(DOMDocument $dom, DOMElement $dokument, Order $order): void
    {
        $daneKontrahenta = $dom->createElement('DaneKontrahenta');
        $dokument->appendChild($daneKontrahenta);

        if ($order->invoice_required && $order->invoice_company_name) {
            // Faktura - użyj danych z faktury
            $this->addElement($dom, $daneKontrahenta, 'Nazwa', $order->invoice_company_name);
            $this->addElement($dom, $daneKontrahenta, 'NIP', $order->invoice_nip ?? '');
        } else {
            // Brak faktury - skopiuj dane odbiorcy
            $this->addElement($dom, $daneKontrahenta, 'Nazwa', $order->customer_full_name);
            $this->addElement($dom, $daneKontrahenta, 'NIP', '');
        }

        $adres = $dom->createElement('Adres');
        $daneKontrahenta->appendChild($adres);

        if ($order->invoice_required && $order->invoice_street) {
            // Faktura - użyj adresu z faktury
            $this->addElement($dom, $adres, 'Ulica', $order->invoice_street);
            $this->addElement($dom, $adres, 'NrDomu', $order->invoice_street_number ?? '');
            $this->addElement($dom, $adres, 'NrLokalu', $order->invoice_apartment ?? '');
            $this->addElement($dom, $adres, 'Miejscowosc', $order->invoice_city ?? '');
            $this->addElement($dom, $adres, 'Poczta', $order->invoice_post_office ?? '');
            $this->addElement($dom, $adres, 'KodPocztowy', $this->formatPostalCode($order->invoice_postal_code ?? ''));
        } else {
            // Brak faktury - skopiuj adres odbiorcy
            $this->addElement($dom, $adres, 'Ulica', $order->delivery_street);
            $this->addElement($dom, $adres, 'NrDomu', $order->delivery_street_number);
            $this->addElement($dom, $adres, 'NrLokalu', $order->delivery_apartment ?? '');
            $this->addElement($dom, $adres, 'Miejscowosc', $order->delivery_city ?? '');
            $this->addElement($dom, $adres, 'Poczta', $order->delivery_post_office);
            $this->addElement($dom, $adres, 'KodPocztowy', $this->formatPostalCode($order->delivery_postal_code));
        }
    }

    /**
     * Dodaje dane odbiorcy (dostawa).
     */
    protected function addRecipientData(DOMDocument $dom, DOMElement $dokument, Order $order): void
    {
        $daneOdbiorcy = $dom->createElement('DaneOdbiorcy');
        $dokument->appendChild($daneOdbiorcy);

        $this->addElement($dom, $daneOdbiorcy, 'Nazwa', $order->customer_full_name);
        $this->addElement($dom, $daneOdbiorcy, 'NIP', '');

        $adres = $dom->createElement('Adres');
        $daneOdbiorcy->appendChild($adres);

        $this->addElement($dom, $adres, 'Ulica', $order->delivery_street);
        $this->addElement($dom, $adres, 'NrDomu', $order->delivery_street_number);
        $this->addElement($dom, $adres, 'NrLokalu', $order->delivery_apartment ?? '');
        $this->addElement($dom, $adres, 'Telefon', $this->formatPhone($order->customer_phone ?? ''));
        $this->addElement($dom, $adres, 'Miejscowosc', $order->delivery_city ?? '');
        $this->addElement($dom, $adres, 'Poczta', $order->delivery_post_office);
        $this->addElement($dom, $adres, 'KodPocztowy', $this->formatPostalCode($order->delivery_postal_code));
    }

    /**
     * Dodaje features (email, telefon, uwagi).
     */
    protected function addFeatures(DOMDocument $dom, DOMElement $dokument, Order $order): void
    {
        $features = $dom->createElement('features');
        $dokument->appendChild($features);

        $this->addFeature($dom, $features, 'E-mail_zamowienia', $order->customer_email);
        $this->addFeature($dom, $features, 'Telefon_zamowienia', $this->formatPhone($order->customer_phone ?? ''));

        // Uwagi - już zawierają paczkomat i promocje (zapisane w order-create)
        // Format: "Paczkomat: ...\n\nPromocja: ...\n\nUwagi klienta"
        $uwagi = $order->notes ?? '';
        
        // Jeśli notes nie zawiera jeszcze promocji, dodaj ją (dla kompatybilności wstecznej)
        if (!empty($order->promotion_code) && stripos($uwagi, 'Promocja:') === false) {
            $promotionInfo = 'Promocja: ' . $order->promotion_code 
                . ' - zniżka: ' . number_format($order->discount_amount, 2, ',', '.') . ' zł';
            
            if (!empty($uwagi)) {
                $uwagi = $promotionInfo . "\n\n" . $uwagi;
            } else {
                $uwagi = $promotionInfo;
            }
        }
        
        $this->addFeature($dom, $features, 'Uwagi', $uwagi);
    }

    /**
     * Dodaje feature element.
     */
    protected function addFeature(DOMDocument $dom, DOMElement $features, string $name, string $value): void
    {
        $feature = $dom->createElement('feature', $this->escapeXml($value));
        $feature->setAttribute('name', $name);
        $features->appendChild($feature);
    }

    /**
     * Dodaje element XML z opcjonalnymi atrybutami.
     */
    protected function addElement(DOMDocument $dom, DOMElement $parent, string $name, ?string $value, array $attributes = []): void
    {
        if ($value === null && empty($attributes)) {
            // Element samozamykający się (np. <Definicja where="..." />)
            $element = $dom->createElement($name);
            foreach ($attributes as $attrName => $attrValue) {
                $element->setAttribute($attrName, $attrValue);
            }
            $parent->appendChild($element);
        } else {
            // Element z wartością
            $element = $dom->createElement($name, $value !== null ? $this->escapeXml($value) : '');
            foreach ($attributes as $attrName => $attrValue) {
                $element->setAttribute($attrName, $attrValue);
            }
            $parent->appendChild($element);
        }
    }

    /**
     * Formatuje kwotę do 2 miejsc po przecinku.
     */
    protected function formatAmount(float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * Formatuje kod pocztowy - tylko cyfry (bez myślników).
     */
    protected function formatPostalCode(string $code): string
    {
        return preg_replace('/[^0-9]/', '', $code);
    }

    /**
     * Formatuje numer telefonu - usuwa spacje i myślniki.
     */
    protected function formatPhone(string $phone): string
    {
        return preg_replace('/[\s\-]/', '', $phone);
    }

    /**
     * Escape'uje wartość XML (zabezpiecza przed <, >, &).
     */
    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Zapisuje XML do pliku.
     *
     * @param Order $order
     * @param string $directory
     * @return string Path to saved file
     */
    public function saveToFile(Order $order, string $directory): string
    {
        $xml = $this->generateXml($order);
        $filename = $order->ext_order_id . '.xml';
        $path = rtrim($directory, '/') . '/' . $filename;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $xml);

        return $path;
    }

    /**
     * Zwraca zawartość XML jako string.
     *
     * @param Order $order
     * @return string
     */
    public function getXmlContent(Order $order): string
    {
        return $this->generateXml($order);
    }

    /**
     * Wysyła XML do Enova przez FTP lub kopiuje do katalogu.
     *
     * @param Order $order
     * @param string $localPath Ścieżka do lokalnego pliku XML
     * @return bool True jeśli wysłano pomyślnie
     */
    public function sendXml(Order $order, string $localPath): bool
    {
        $ftpHost = config('enova.orders.ftp.host');

        if (!empty($ftpHost)) {
            // Wysyłka przez FTP
            return $this->sendXmlViaFtp($order, $localPath);
        } else {
            // Kopia do katalogu docelowego
            return $this->copyXmlToDirectory($order, $localPath);
        }
    }

    /**
     * Wysyła XML przez FTP.
     *
     * @param Order $order
     * @param string $localPath
     * @return bool
     */
    protected function sendXmlViaFtp(Order $order, string $localPath): bool
    {
        try {
            $ftpHost = config('enova.orders.ftp.host');
            $ftpUser = config('enova.orders.ftp.user');
            $ftpPass = config('enova.orders.ftp.pass');
            $ftpPath = config('enova.orders.ftp.path', '/');

            if (empty($ftpHost) || empty($ftpUser) || empty($ftpPass)) {
                \Log::warning('Brak konfiguracji FTP dla wysyłki XML', [
                    'order_id' => $order->id,
                    'ext_order_id' => $order->ext_order_id,
                ]);
                return false;
            }

            $connId = ftp_connect($ftpHost);
            if (!$connId) {
                \Log::error('Nie można połączyć się z serwerem FTP', [
                    'order_id' => $order->id,
                    'ftp_host' => $ftpHost,
                ]);
                return false;
            }

            if (!ftp_login($connId, $ftpUser, $ftpPass)) {
                \Log::error('Błąd logowania do FTP', [
                    'order_id' => $order->id,
                    'ftp_user' => $ftpUser,
                ]);
                ftp_close($connId);
                return false;
            }

            // Ustaw tryb pasywny jeśli wymagane
            ftp_pasv($connId, config('enova.orders.ftp.passive', true));

            $remoteFilename = $order->ext_order_id . '.xml';
            $remotePath = rtrim($ftpPath, '/') . '/' . $remoteFilename;

            $result = ftp_put($connId, $remotePath, $localPath, FTP_ASCII);
            ftp_close($connId);

            if (!$result) {
                \Log::error('Błąd wysyłki pliku XML przez FTP', [
                    'order_id' => $order->id,
                    'local_path' => $localPath,
                    'remote_path' => $remotePath,
                ]);
                return false;
            }

            \Log::info('XML wysłany przez FTP', [
                'order_id' => $order->id,
                'ext_order_id' => $order->ext_order_id,
                'remote_path' => $remotePath,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Wyjątek podczas wysyłki XML przez FTP: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'ext_order_id' => $order->ext_order_id,
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Kopiuje XML do katalogu docelowego.
     *
     * @param Order $order
     * @param string $localPath
     * @return bool
     */
    protected function copyXmlToDirectory(Order $order, string $localPath): bool
    {
        try {
            $destinationDir = config('enova.orders.xml_destination', storage_path('app/enova/orders/sent'));

            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            $filename = $order->ext_order_id . '.xml';
            $destinationPath = rtrim($destinationDir, '/') . '/' . $filename;

            $result = copy($localPath, $destinationPath);

            if ($result) {
                \Log::info('XML skopiowany do katalogu docelowego', [
                    'order_id' => $order->id,
                    'ext_order_id' => $order->ext_order_id,
                    'destination_path' => $destinationPath,
                ]);
            } else {
                \Log::error('Błąd kopiowania XML do katalogu docelowego', [
                    'order_id' => $order->id,
                    'local_path' => $localPath,
                    'destination_path' => $destinationPath,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error('Wyjątek podczas kopiowania XML: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'ext_order_id' => $order->ext_order_id,
                'exception' => $e,
            ]);
            return false;
        }
    }
}
