<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Order confirmation') }}</title>
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
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #026941;
        }

        .logo {
            width: 150px;
            height: 75px;
            margin: 0 auto 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        .order-number {
            font-size: 18px;
            font-weight: bold;
            color: #026941;
            margin-bottom: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th {
            text-align: left;
            padding: 8px;
            background-color: #f5f5f5;
            width: 150px;
        }

        .info-table td {
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        table td[style*="text-align: right"] {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
        }

        .notes {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #026941;
            margin-bottom: 20px;
        }

        h2 {
            color: #026941;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        h3 {
            color: #026941;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo" style="text-align: center; margin-bottom: 20px;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/bifix-logo.png'))) }}"
                alt="Bifix Logo" style="width: 150px; height: auto;">
        </div>
        <h1>{{ __('Order confirmation') }}</h1>
    </div>

    {{-- Order Details --}}
    <div class="section">
        <p>{{ __('Good day') }} {{ $order->customer_first_name }} {{ $order->customer_last_name }},</p>
        <p>{{ __('Thank you for placing an order in our store. Below are the details of your order.') }}</p>
    </div>

    <div class="section">
        @if (!empty($order->orderNumber))
            <div class="order-number">{{ __('Order number') }}: {{ $order->orderNumber }}</div>
        @else
            <div class="order-number">{{ __('Order number') }}: {{ $order->ext_order_id }}</div>
        @endif
        <table class="info-table">
            <tr>
                <th>{{ __('Order date') }}:</th>
                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
            </tr>
            <tr>
                <th>{{ __('Status') }}:</th>
                <td>{{ $order->status->label() }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>{{ __('Customer data') }}</h2>
        <table class="info-table">
            <tr>
                <th>{{ __('Name') }}:</th>
                <td>{{ $order->customer_first_name }} {{ $order->customer_last_name }}</td>
            </tr>
            <tr>
                <th>{{ __('Address') }}:</th>
                <td>
                    {{ $order->delivery_street }} {{ $order->delivery_street_number }}
                    @if ($order->delivery_apartment)
                        /{{ $order->delivery_apartment }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ __('Postal code') }}:</th>
                <td>{{ $order->delivery_postal_code }} {{ $order->delivery_city }}</td>
            </tr>
            @if ($order->customer_phone)
                <tr>
                    <th>{{ __('Phone') }}:</th>
                    <td>{{ $order->customer_phone }}</td>
                </tr>
            @endif
            <tr>
                <th>{{ __('Email Address') }}:</th>
                <td>{{ $order->customer_email }}</td>
            </tr>
        </table>
    </div>

    @if ($order->parcel_locker_name)
        <div class="section">
            <h2>{{ __('Parcel locker') }}</h2>
            <table class="info-table">
                <tr>
                    <th>{{ __('Name') }}:</th>
                    <td><strong>{{ $order->parcel_locker_name }}</strong></td>
                </tr>
                @if ($order->parcel_locker_address)
                    <tr>
                        <th>{{ __('Address') }}:</th>
                        <td>{!! nl2br($order->parcel_locker_address) !!}</td>
                    </tr>
                @endif
            </table>
        </div>
    @endif

    @if ($order->invoice_nip || $order->invoice_company_name || $order->invoice_address)
        <div class="section">
            <h2>{{ __('Invoice data') }}</h2>
            <table class="info-table">
                @if ($order->invoice_company_name)
                    <tr>
                        <th>{{ __('Company name') }}:</th>
                        <td>{{ $order->invoice_company_name }}</td>
                    </tr>
                @endif
                @if ($order->invoice_nip)
                    <tr>
                        <th>{{ __('NIP') }}:</th>
                        <td>{{ $order->invoice_nip }}</td>
                    </tr>
                @endif
                @if ($order->invoice_address)
                    <tr>
                        <th>{{ __('Address') }}:</th>
                        <td>{{ $order->invoice_address }}</td>
                    </tr>
                @endif
            </table>
        </div>
    @endif

    <div class="section">
        <h2>{{ __('Ordered items') }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">{{ __('No.') }}</th>
                    <th>{{ __('Product name') }}</th>
                    <th style="text-align: right;">{{ __('Price') }}</th>
                    <th style="text-align: right;">{{ __('Quantity') }}</th>
                    <th style="text-align: right;">{{ __('Sum') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($order->items as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}.</td>
                        <td>{{ $item['name'] }}</td>
                        <td style="text-align: right;">{{ number_format($item['price'], 2, ',', ' ') }} zł</td>
                        <td style="text-align: right;">{{ $item['quantity'] }}</td>
                        <td style="text-align: right;">
                            <strong>{{ number_format($item['price'] * $item['quantity'], 2, ',', ' ') }} zł</strong>
                        </td>
                    </tr>
                    @php $total += $item['price'] * $item['quantity']; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;">{{ __('Product value') }}:</td>
                    <td style="text-align: right;">{{ number_format($total, 2, ',', ' ') }} zł</td>
                </tr>
                @if ($order->delivery_cost > 0)
                    <tr>
                        <td colspan="4" style="text-align: right;">{{ __('Delivery') }}
                            ({{ $order->delivery_name }}):</td>
                        <td style="text-align: right;">{{ number_format($order->delivery_cost, 2, ',', ' ') }} zł</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="4" style="text-align: right; font-size: 1.2em;">{{ __('Total to pay') }}:</td>
                    <td style="text-align: right; font-size: 1.2em; color: #026941;">
                        <strong>{{ number_format($order->total ?? $total, 2, ',', ' ') }} zł</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($order->notes)
        <div class="section notes">
            <h3>{{ __('Notes') }}:</h3>
            <p style="white-space: pre-wrap;">{{ $order->notes }}</p>
        </div>
    @endif

    <div class="section">
        <p>Zapraszamy po odbiór zamówienia od poniedziałku do piątku w godz. 8.00-16.00 pod adresem Górki Małe, ul.
            Dworska 33, 95-080 Tuszyn.</p>
    </div>

    <div class="footer">
        <p>{{ __('Thank you for shopping in our store!') }}</p>
        <p>{{ __('In case of questions, please contact us.') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
    </div>
</body>

</html>
