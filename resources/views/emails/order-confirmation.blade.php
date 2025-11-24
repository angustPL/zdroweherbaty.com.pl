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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #4a5568;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            background-color: #f7fafc;
            padding: 20px;
            margin-top: 20px;
        }

        .order-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4a5568;
        }

        .order-items {
            margin: 20px 0;
        }

        .order-item {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-summary {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-top: 2px solid #4a5568;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #4a5568;
        }

        .customer-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
        }

        .footer {
            text-align: center;
            color: #718096;
            font-size: 0.9em;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Potwierdzenie zamówienia</h1>
    </div>

    <div class="content">
        <p>Dzień dobry {{ $order->customer_first_name }} {{ $order->customer_last_name }},</p>

        <p>Dziękujemy za złożenie zamówienia w naszym sklepie. Poniżej znajdują się szczegóły Twojego zamówienia.</p>

        <div class="order-info">
            <h2>Numer zamówienia: {{ $order->ext_order_id }}</h2>
            <p><strong>Data zamówienia:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Status:</strong> {{ $order->status->label() }}</p>
        </div>

        <div class="order-items">
            <h3>Produkty:</h3>
            @foreach ($order->items as $item)
                <div class="order-item">
                    <div>
                        <strong>{{ $item['name'] ?? 'Produkt' }}</strong><br>
                        <small>Ilość: {{ $item['quantity'] ?? 1 }} ×
                            {{ number_format($item['price'] ?? 0, 2, ',', ' ') }} zł</small>
                    </div>
                    <div>
                        <strong>{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2, ',', ' ') }}
                            zł</strong>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="order-summary">
            <div class="summary-row">
                <span>Wartość produktów:</span>
                <span>{{ number_format($order->subtotal, 2, ',', ' ') }} zł</span>
            </div>
            @if ($order->delivery_cost > 0 || $order->is_free_delivery)
                <div class="summary-row">
                    <span>Dostawa ({{ $order->delivery_name }}):</span>
                    <span>{{ $order->is_free_delivery ? '0,00 zł' : number_format($order->delivery_cost, 2, ',', ' ') . ' zł' }}</span>
                </div>
            @endif
            <div class="summary-row total">
                <span>Razem:</span>
                <span>{{ number_format($order->total, 2, ',', ' ') }} zł</span>
            </div>
        </div>

        <div class="customer-info">
            <h3>Dane do dostawy:</h3>
            <p>
                {{ $order->customer_first_name }} {{ $order->customer_last_name }}<br>
                {{ $order->delivery_street }} {{ $order->delivery_street_number }}@if ($order->delivery_apartment)
                    , {{ $order->delivery_apartment }}
                @endif
                <br>
                {{ $order->delivery_postal_code }} {{ $order->delivery_city }}@if ($order->delivery_post_office)
                    , {{ $order->delivery_post_office }}
                @endif
                <br>
                {{ $order->delivery_country }}
            </p>
            @if ($order->customer_phone)
                <p><strong>Telefon:</strong> {{ $order->customer_phone }}</p>
            @endif
            <p><strong>Email:</strong> {{ $order->customer_email }}</p>
        </div>

        @if ($order->parcel_locker_data)
            <div class="customer-info">
                <h3>Paczkomat:</h3>
                <p>
                    <strong>{{ $order->parcel_locker_data['name'] ?? '' }}</strong><br>
                    @if (isset($order->parcel_locker_data['address']))
                        {{ $order->parcel_locker_data['address']['line1'] ?? '' }}<br>
                        {{ $order->parcel_locker_data['address']['line2'] ?? '' }}
                    @endif
                </p>
            </div>
        @endif

        @if ($order->invoice_required)
            <div class="customer-info">
                <h3>Dane do faktury:</h3>
                <p>
                    <strong>{{ $order->invoice_company_name }}</strong><br>
                    NIP: {{ $order->invoice_nip }}<br>
                    {{ $order->invoice_street }} {{ $order->invoice_street_number }}@if ($order->invoice_apartment)
                        , {{ $order->invoice_apartment }}
                    @endif
                    <br>
                    {{ $order->invoice_postal_code }} {{ $order->invoice_city }}@if ($order->invoice_post_office)
                        , {{ $order->invoice_post_office }}
                    @endif
                </p>
            </div>
        @endif

        @if ($order->notes)
            <div class="customer-info">
                <h3>Uwagi:</h3>
                <p>{{ nl2br(e($order->notes)) }}</p>
            </div>
        @endif

        <div class="customer-info">
            <h3>Płatność:</h3>
            <p>
                @if ($order->payment)
                    <strong>{{ $order->payment->payment_method }}</strong>
                @else
                    <strong>Do ustalenia</strong>
                @endif
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Dziękujemy za zakupy w naszym sklepie!</p>
        <p>W razie pytań prosimy o kontakt.</p>
    </div>
</body>

</html>
