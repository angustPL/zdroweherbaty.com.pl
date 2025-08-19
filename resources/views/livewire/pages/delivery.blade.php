{{-- Logika: app/Livewire/Pages/Dostawa.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Delivery;

layout('layouts.app');

state(['deliveries' => [], 'opcjeDostawy' => []]);

mount(function () {
    $this->deliveries = Delivery::join('Ceny', 'Towary.ID', '=', 'Ceny.Towar')
        ->join('Features as PaymentFeatures', function ($join) {
            $join->on('Towary.ID', '=', 'PaymentFeatures.Parent')->where('PaymentFeatures.Name', '=', config('enova.orders.feature_payment_method'));
        })
        ->join('SposobyZaplaty', 'PaymentFeatures.Data', '=', 'SposobyZaplaty.ID')
        // ->where('Towary.Blokada', '=', 0)
        ->where('Ceny.Definicja', config('enova.prices.definition'))
        ->orderBy('MasaBruttoValue')
        ->orderBy('Ceny.BruttoValue')
        ->select(['Towary.ID', 'Towary.Nazwa', 'Towary.Opis', 'Towary.MasaBruttoValue', 'Ceny.BruttoValue', 'SposobyZaplaty.Nazwa as PaymentMethodName'])
        ->get()
        ->map(function ($delivery) {
            return [
                'ID' => $delivery->ID,
                'Nazwa' => $delivery->Nazwa,
                'Opis' => $delivery->Opis,
                'MasaBruttoValue' => $delivery->MasaBruttoValue,
                'BruttoValue' => $delivery->BruttoValue,
                'PaymentMethod' => $delivery->PaymentMethodName ?? '-',
            ];
        })
        ->toArray();

    $this->opcjeDostawy = [];
    foreach ($this->deliveries as $delivery) {
        $this->opcjeDostawy[$delivery['MasaBruttoValue']][] = $delivery;
    }
});

?>

<div>
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Opcje dostawy
        </h1>
        @if (config('enova.orders.free_delivery_threshold') > 0)
            <p>
                Bezpłatna dostawa dla zamówień o wartości większej niż
                <strong class="text-lg">{{ number_format(config('enova.orders.free_delivery_threshold'), 0, ',', '.') }}
                    zł</strong>
            </p>
            <p>
                Koszty dostawy dla zamówień o wartości do
                {{ number_format(config('enova.orders.free_delivery_threshold'), 0, ',', '.') }} zł
                przedstawia tabela.
            </p>
        @endif
    </div>

    @if (empty($this->opcjeDostawy))
        <div class="text-center py-12">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                </path>
            </svg>
            <h3 class="text-xl font-medium mb-2">Brak opcji dostawy</h3>
            <p class="text-gray-500">Aktualnie nie ma dostępnych opcji dostawy</p>
        </div>
    @else
        <div class="overflow-hidden bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Zakres wagi
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rodzaj dostawy
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sposób zapłaty
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Koszt
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($this->opcjeDostawy as $zakres)
                            @php $lp = 1; @endphp
                            @foreach ($zakres as $value)
                                <tr>
                                    @if ($lp == 1)
                                        <td rowspan="{{ count($zakres) }}"
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                            do {{ number_format($value['MasaBruttoValue'], 0, ',', '.') }} kg
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $value['Nazwa'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        {{ $value['PaymentMethod'] }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">
                                        {{ number_format($value['BruttoValue'], 2, ',', '.') }} zł
                                    </td>
                                </tr>
                                @php $lp++; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
