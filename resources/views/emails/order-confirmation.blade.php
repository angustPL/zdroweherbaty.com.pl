<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potwierdzenie zamówienia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #ddd;
        }

        .header {
            background-color: #026941;
            color: white;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .header-logo {
            max-width: 200px;
            height: auto;
            margin: 0 auto 15px;
            display: block;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .section h2 {
            margin-top: 0;
            color: #026941;
            font-size: 18px;
            border-bottom: 2px solid #026941;
            padding-bottom: 5px;
        }

        .section h3 {
            margin-top: 0;
            color: #026941;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background-color: white;
        }

        table th {
            background-color: #ecf0f1;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #bdc3c7;
        }

        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ecf0f1;
        }

        table tfoot td {
            font-weight: bold;
            font-size: 1.1em;
            background-color: #ecf0f1;
            border-top: 2px solid #026941;
            padding: 15px 10px;
        }

        .order-number {
            font-size: 20px;
            font-weight: bold;
            color: #026941;
            margin: 15px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th {
            text-align: right;
            width: 40%;
            padding-right: 15px;
            font-weight: normal;
            color: #7f8c8d;
        }

        .info-table td {
            font-weight: bold;
        }

        .footer {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .bank-info {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .bank-info strong {
            color: #856404;
        }

        .notes {
            background-color: #e7f3ff;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <!-- Logo BIFIX -->
            <img src="https://www.zdroweherbaty.com.pl/img/bifix-logo.png" alt="BIFIX" class="header-logo" />
            <h1>Szczegóły zamówienia</h1>
        </div>

        <div class="section">
            <p>Dzień dobry {{ $order->customer_first_name }} {{ $order->customer_last_name }},</p>
            <p>Dziękujemy za złożenie zamówienia w naszym sklepie. Poniżej znajdują się szczegóły Twojego zamówienia.
            </p>
        </div>

        <div class="section">
            @if (!empty($order->orderNumber))
                <div class="order-number">Numer zamówienia: {{ $order->orderNumber }}</div>
            @else
                <div class="order-number">Numer zamówienia: {{ $order->ext_order_id }}</div>
            @endif
            <table class="info-table">
                <tr>
                    <th>Data zamówienia:</th>
                    <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>{{ $order->status->label() }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Dane klienta</h2>
            <table class="info-table">
                <tr>
                    <th>Imię i nazwisko:</th>
                    <td>{{ $order->customer_first_name }} {{ $order->customer_last_name }}</td>
                </tr>
                <tr>
                    <th>Adres:</th>
                    <td>
                        {{ $order->delivery_street }} {{ $order->delivery_street_number }}@if ($order->delivery_apartment)
                            /{{ $order->delivery_apartment }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Kod pocztowy:</th>
                    <td>{{ $order->delivery_postal_code }} {{ $order->delivery_city }}@if ($order->delivery_post_office)
                            , {{ $order->delivery_post_office }}
                        @endif
                    </td>
                </tr>
                @if ($order->customer_phone)
                    <tr>
                        <th>Telefon:</th>
                        <td>{{ $order->customer_phone }}</td>
                    </tr>
                @endif
                <tr>
                    <th>E-mail:</th>
                    <td>{{ $order->customer_email }}</td>
                </tr>
            </table>
        </div>

        @if ($order->invoice_required)
            <div class="section">
                <h2>Dane do faktury</h2>
                <table class="info-table">
                    <tr>
                        <th>Nazwa:</th>
                        <td>{{ $order->invoice_company_name }}</td>
                    </tr>
                    <tr>
                        <th>NIP:</th>
                        <td>{{ $order->invoice_nip }}</td>
                    </tr>
                    <tr>
                        <th>Adres:</th>
                        <td>
                            {{ $order->invoice_street }} {{ $order->invoice_street_number }}@if ($order->invoice_apartment)
                                /{{ $order->invoice_apartment }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Kod pocztowy:</th>
                        <td>{{ $order->invoice_postal_code }} {{ $order->invoice_city }}@if ($order->invoice_post_office)
                                , {{ $order->invoice_post_office }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        @if ($order->parcel_locker_data)
            <div class="section">
                <h2>Paczkomat</h2>
                <table class="info-table">
                    <tr>
                        <th>Nazwa:</th>
                        <td><strong>{{ $order->parcel_locker_data['name'] ?? '' }}</strong></td>
                    </tr>
                    @if (isset($order->parcel_locker_data['address']))
                        <tr>
                            <th>Adres:</th>
                            <td>
                                {{ $order->parcel_locker_data['address']['line1'] ?? '' }}<br>
                                {{ $order->parcel_locker_data['address']['line2'] ?? '' }}
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        @endif

        <div class="section">
            <h2>Zamówione towary</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">Lp.</th>
                        <th>Nazwa</th>
                        <th style="text-align: right;">Cena</th>
                        <th style="text-align: right;">Ilość</th>
                        <th style="text-align: right;">Suma</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td style="text-align: center;">{{ $loop->iteration }}.</td>
                            <td>{{ $item['name'] ?? 'Produkt' }}</td>
                            <td style="text-align: right;">{{ number_format($item['price'] ?? 0, 2, ',', ' ') }} zł
                            </td>
                            <td style="text-align: right;">{{ $item['quantity'] ?? 1 }}</td>
                            <td style="text-align: right;">
                                <strong>{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2, ',', ' ') }}
                                    zł</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;">Wartość produktów:</td>
                        <td style="text-align: right;">{{ number_format($order->subtotal, 2, ',', ' ') }} zł</td>
                    </tr>
                    @if ($order->delivery_cost > 0 || $order->is_free_delivery)
                        <tr>
                            <td colspan="4" style="text-align: right;">Dostawa
                                ({{ $order->delivery_name }}):</td>
                            <td style="text-align: right;">
                                {{ $order->is_free_delivery ? '0,00 zł' : number_format($order->delivery_cost, 2, ',', ' ') . ' zł' }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="4" style="text-align: right; font-size: 1.2em;">Razem do zapłaty:</td>
                        <td style="text-align: right; font-size: 1.2em; color: #026941;">
                            <strong>{{ number_format($order->total, 2, ',', ' ') }} zł</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($order->notes)
            <div class="section notes">
                <h3>Uwagi:</h3>
                <p style="white-space: pre-wrap;">{{ $order->notes }}</p>
            </div>
        @endif

        <div class="section">
            @php
                $paymentMessages = [
                    'cash' =>
                        'Zapraszamy po odbiór zamówienia od poniedziałku do piątku w godz. 8.00-16.00 pod adresem Górki Małe, ul. Dworska 33, 95-080 Tuszyn.',
                    'payu_blik' => 'Zamówienie zostanie zrealizowane po zaksięgowaniu płatności na koncie.',
                    'payu_card' => 'Zamówienie zostanie zrealizowane po zaksięgowaniu płatności na koncie.',
                    'payu_google_pay' => 'Zamówienie zostanie zrealizowane po zaksięgowaniu płatności na koncie.',
                    'payu_apple_pay' => 'Zamówienie zostanie zrealizowane po zaksięgowaniu płatności na koncie.',
                    'payu_transfer' =>
                        'Zamówienie zostanie zrealizowane po zaksięgowaniu płatności na koncie. Dane do przelewu dostępne będą pod poniższym adresem po przyjęciu zamówienia przez system: <a href="' .
                        route('order.info', $order->ext_order_id) .
                        '">' .
                        route('order.info', $order->ext_order_id) .
                        '</a>',
                ];
                $paymentMethod = $order->payment ? $order->payment->payment_method : 'cash';
                $message = $paymentMessages[$paymentMethod] ?? $paymentMessages['cash'];
            @endphp
            <p>{!! $message !!}</p>
        </div>

        <div class="footer">
            <p>Dziękujemy za zakupy w naszym sklepie!</p>
            <p>W razie pytań prosimy o kontakt.</p>
        </div>
    </div>
</body>

</html>
