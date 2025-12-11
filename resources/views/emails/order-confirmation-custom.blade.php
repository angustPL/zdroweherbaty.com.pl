@extends('emails.layouts.app', [
    'title' => __('Order confirmation'),
    'headerUrl' => config('app.url'),
    'logo' => $logo ?? null,
])

@section('content')
    <!-- Greeting -->
    <p style="margin: 0 0 20px; color: #333; font-size: 16px;">
        {{ __('Good day') }} {{ $order->customer_first_name }} {{ $order->customer_last_name }},
    </p>

    <p style="margin: 0 0 30px; color: #333; font-size: 16px;">
        {{ __('Thank you for placing an order in our store. Below are the details of your order.') }}
    </p>

    <!-- Order Details -->
    <div class="panel">
        <h3>{{ __('Szczegóły zamówienia') }}</h3>
        <p><strong>{{ __('Order date') }}:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
        <p><strong>Aktualne dane online:</strong> <a href="{{ config('app.url') }}/zamowienie{{ $order->id }}"
                style="color: #026941;">Sprawdź status zamówienia</a></p>
    </div>

    <!-- Customer Data -->
    <div class="panel">
        <h3>{{ __('Customer data') }}</h3>
        <p><strong>{{ __('Name') }}:</strong> {{ $order->customer_first_name }} {{ $order->customer_last_name }}</p>
        <p><strong>{{ __('Address') }}:</strong> {{ $order->delivery_street }}
            {{ $order->delivery_street_number }}{{ $order->delivery_apartment ? '/' . $order->delivery_apartment : '' }}
        </p>
        <p><strong>{{ __('Postal code') }}:</strong> {{ $order->delivery_postal_code }} {{ $order->delivery_city }}</p>
        @if ($order->customer_phone)
            <p><strong>{{ __('Phone') }}:</strong> {{ $order->customer_phone }}</p>
        @endif
        <p><strong>{{ __('Email Address') }}:</strong> {{ $order->customer_email }}</p>
    </div>

    <!-- Payment Information -->
    <div class="panel">
        <h3>Informacje o płatności</h3>
        @if (isset($order->payment))
            <p><strong>Metoda płatności:</strong>
                @if ($order->payment->isPayu())
                    PayU - {{ $order->payment->payu_option ?? 'płatność online' }}
                @elseif($order->payment->isCash())
                    Płatność przy odbiorze
                @else
                    {{ $order->payment->payment_method ?? 'Brak danych' }}
                @endif
            </p>
            <p><strong>Status płatności:</strong> {{ $order->payment->status->label() ?? 'Nieopłacone' }}</p>
            @if (
                $order->payment->isPayu() &&
                    ($order->payment->status === \App\Enums\PaymentStatus::PENDING ||
                        $order->payment->status === \App\Enums\PaymentStatus::WAITING_FOR_CONFIRMATION))
                <p style="margin-top: 15px; color: #666;">
                    <strong>Link do płatności:</strong>
                    <a href="{{ config('app.url') }}/payment/payu/{{ $order->ext_order_id }}"
                        style="color: #026941;">Dokończ płatność online</a>
                </p>
            @endif
        @else
            <p><strong>Metoda płatności:</strong> Brak danych</p>
            <p><strong>Status płatności:</strong> Nieokreślony</p>
        @endif
    </div>

    @if ($order->parcel_locker_name)
        <!-- Parcel Locker -->
        <div class="panel">
            <h3>{{ __('Parcel locker') }}</h3>
            <p><strong>{{ __('Name') }}:</strong> {{ $order->parcel_locker_name }}</p>
            @if ($order->parcel_locker_address)
                <p><strong>{{ __('Address') }}:</strong> {{ $order->parcel_locker_address }}</p>
            @endif
        </div>
    @endif

    @if ($order->invoice_nip || $order->invoice_company_name || $order->invoice_address)
        <!-- Invoice Data -->
        <div class="panel">
            <h3>{{ __('Invoice data') }}</h3>
            @if ($order->invoice_company_name)
                <p><strong>{{ __('Company name') }}:</strong> {{ $order->invoice_company_name }}</p>
            @endif
            @if ($order->invoice_nip)
                <p><strong>{{ __('NIP') }}:</strong> {{ $order->invoice_nip }}</p>
            @endif
            @if ($order->invoice_address)
                <p><strong>{{ __('Address') }}:</strong> {{ $order->invoice_address }}</p>
            @endif
        </div>
    @endif

    <!-- Ordered Items -->
    <div class="panel">
        <h3>{{ __('Ordered items') }}</h3>
        <table class="email-table">
            <thead>
                <tr>
                    <th>{{ __('No.') }}</th>
                    <th>{{ __('Product name') }}</th>
                    <th class="text-right">{{ __('Price') }}</th>
                    <th class="text-right">{{ __('Quantity') }}</th>
                    <th class="text-right">{{ __('Sum') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                    $counter = 1;
                    // Debug - usuń po sprawdzeniu
                    if (request()->has('debug')) {
                        dd($order->items);
                    }
                @endphp
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $counter }}.</td>
                        <td>{{ $item['name'] }}</td>
                        <td class="text-right">{{ number_format($item['price'], 2, ',', ' ') }} zł</td>
                        <td class="text-right">{{ $item['quantity'] }}</td>
                        <td class="text-right"><strong>{{ number_format($item['price'] * $item['quantity'], 2, ',', ' ') }}
                                zł</strong></td>
                    </tr>
                    @php
                        $total += $item['price'] * $item['quantity'];
                        $counter++;
                    @endphp
                @endforeach
                <tr>
                    <td colspan="4" class="text-right">{{ __('Wartość produktów') }}:</td>
                    <td class="text-right">{{ number_format($total, 2, ',', ' ') }} zł</td>
                </tr>
                @if ($order->delivery_cost > 0)
                    <tr>
                        <td colspan="4" class="text-right">{{ __('Delivery') }} ({{ $order->delivery_name }}):</td>
                        <td class="text-right">{{ number_format($order->delivery_cost, 2, ',', ' ') }} zł</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="4" class="text-right" style="font-size: 1.2em;">{{ __('Total to pay') }}:</td>
                    <td class="text-right" style="font-size: 1.2em;">
                        {{ number_format($order->total ?? $total, 2, ',', ' ') }} zł</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($order->notes)
        <!-- Notes -->
        <div class="panel">
            <h3>{{ __('Notes') }}:</h3>
            <p style="white-space: pre-wrap;">{{ $order->notes }}</p>
        </div>
    @endif

    <!-- Thanks -->
    <p style="margin: 20px 0; color: #333; font-size: 16px;">
        Dziękujemy za zakupy w naszym sklepie!<br>
        W razie pytań prosimy o <a href="mailto:{{ config('mail.from.address') }}?subject=Pytanie dotyczy zamówienia"
            style="color: #026941;">kontakt</a>.
    </p>
@endsection
