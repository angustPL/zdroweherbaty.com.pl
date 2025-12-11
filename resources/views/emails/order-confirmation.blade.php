<x-mail::message>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ __('Order confirmation') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Greeting --}}
    {{ __('Good day') }} {{ $order->customer_first_name }} {{ $order->customer_last_name }},
    {{ __('Thank you for placing an order in our store. Below are the details of your order.') }}

    {{-- Order Details --}}
    <x-mail::panel>
        **{{ __('Order number') }}:** {{ $order->orderNumber ?? $order->ext_order_id }}
        **{{ __('Order date') }}:** {{ $order->created_at->format('d.m.Y H:i') }}
        **{{ __('Status') }}:** {{ $order->status->label() }}
    </x-mail::panel>

    {{-- Customer Data --}}
    <x-mail::panel>
        **{{ __('Customer data') }}
        **{{ __('Name') }}:** {{ $order->customer_first_name }} {{ $order->customer_last_name }}
        **{{ __('Address') }}:** {{ $order->delivery_street }}
        {{ $order->delivery_street_number }}{{ $order->delivery_apartment ? '/' . $order->delivery_apartment : '' }}
        **{{ __('Postal code') }}:** {{ $order->delivery_postal_code }} {{ $order->delivery_city }}
        @if ($order->customer_phone)
            **{{ __('Phone') }}:** {{ $order->customer_phone }}
        @endif
        **{{ __('Email Address') }}:** {{ $order->customer_email }}
    </x-mail::panel>

    @if ($order->parcel_locker_name)
        {{-- Parcel Locker --}}
        <x-mail::panel>
            **{{ __('Parcel locker') }}
            **{{ __('Name') }}:** {{ $order->parcel_locker_name }}
            @if ($order->parcel_locker_address)
                **{{ __('Address') }}:** {{ $order->parcel_locker_address }}
            @endif
        </x-mail::panel>
    @endif

    @if ($order->invoice_nip || $order->invoice_company_name || $order->invoice_address)
        {{-- Invoice Data --}}
        <x-mail::panel>
            **{{ __('Invoice data') }}
            @if ($order->invoice_company_name)
                **{{ __('Company name') }}:** {{ $order->invoice_company_name }}
            @endif
            @if ($order->invoice_nip)
                **{{ __('NIP') }}:** {{ $order->invoice_nip }}
            @endif
            @if ($order->invoice_address)
                **{{ __('Address') }}:** {{ $order->invoice_address }}
            @endif
        </x-mail::panel>
    @endif

    {{-- Ordered Items --}}
    <x-mail::panel>
        **{{ __('Ordered items') }}
        <x-mail::table>
            | {{ __('No.') }} | {{ __('Product name') }} | {{ __('Price') }} | {{ __('Quantity') }} |
            {{ __('Sum') }} |
            |---|---|---|---|---|
            @php $total = 0; @endphp
            @foreach ($order->items as $index => $item)
                | {{ $index + 1 }}. | {{ $item['name'] }} | {{ number_format($item['price'], 2, ',', ' ') }} zł |
                {{ $item['quantity'] }} | **{{ number_format($item['price'] * $item['quantity'], 2, ',', ' ') }} zł**
                |
                @php $total += $item['price'] * $item['quantity']; @endphp
            @endforeach
            |---|---|---|---|---|
            | | | **{{ __('Product value') }}:** | | **{{ number_format($total, 2, ',', ' ') }} zł** |
            @if ($order->delivery_cost > 0)
                | | | **{{ __('Delivery') }} ({{ $order->delivery_name }}):** | |
                **{{ number_format($order->delivery_cost, 2, ',', ' ') }} zł** |
            @endif
            | | | **{{ __('Total to pay') }}:** | | **{{ number_format($order->total ?? $total, 2, ',', ' ') }} zł** |
        </x-mail::table>
    </x-mail::panel>

    @if ($order->notes)
        {{-- Notes --}}
        <x-mail::panel>
            **{{ __('Notes') }}:**
            {{ $order->notes }}
        </x-mail::panel>
    @endif

    {{-- Pickup Info --}}
    Zapraszamy po odbiór zamówienia od poniedziałku do piątku w godz. 8.00-16.00 pod adresem Górki Małe, ul. Dworska 33,
    95-080 Tuszyn.

    {{-- Footer --}}
    Dziękujemy za zakupy w naszym sklepie!

    W razie pytań prosimy o kontakt.
</x-mail::message>
