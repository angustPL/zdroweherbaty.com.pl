{{-- Logika: app/Livewire/Pages/Dostawa.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Delivery;
use App\Models\Promotion;

layout('layouts.app');

// SEO Meta Tags - canonical URL jest automatycznie ustawiany z konfiguracji

// Open Graph - URL jest automatycznie ustawiany z konfiguracji

// Schema.org JSON-LD - tylko typ (reszta z domyślnych)
app('seotools.json-ld')->setType('WebPage');

state(['deliveries' => [], 'opcjeDostawy' => [], 'freeDeliveryThreshold' => 0]);

mount(function () {
    $this->deliveries = Delivery::getCachedAll();

    $this->opcjeDostawy = [];
    foreach ($this->deliveries as $delivery) {
        $this->opcjeDostawy[$delivery['MasaBruttoValue']][] = $delivery;
    }

    // Pobierz próg bezpłatnej dostawy z promocji w bazie
    $freeDeliveryPromotion = Promotion::where('type', 'automatic')
        ->where('discount_type', 'free_delivery')
        ->where('is_active', true)
        ->where(function ($query) {
            $query->whereNull('valid_from')
                ->orWhere('valid_from', '<=', now());
        })
        ->where(function ($query) {
            $query->whereNull('valid_to')
                ->orWhere('valid_to', '>=', now());
        })
        ->first();

    $this->freeDeliveryThreshold = $freeDeliveryPromotion && $freeDeliveryPromotion->min_order_amount 
        ? (float) $freeDeliveryPromotion->min_order_amount 
        : (float) config('enova.delivery.free_delivery_threshold', 0);

    // GTM page type
    try {
        app('googletagmanager')->set('pageType', 'delivery');
    } catch (\Exception $e) {
        // Silent fail - GTM event not critical for functionality
    }
});

?>

<div>
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Opcje dostawy
        </h1>
        @if ($freeDeliveryThreshold > 0)
            <p>
                Bezpłatna dostawa dla zamówień o wartości większej niż
                <strong class="text-lg">{{ number_format($freeDeliveryThreshold, 0, ',', '.') }} zł</strong>
            </p>
            <p>
                Koszty dostawy dla zamówień o wartości do
                {{ number_format($freeDeliveryThreshold, 0, ',', '.') }} zł
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
