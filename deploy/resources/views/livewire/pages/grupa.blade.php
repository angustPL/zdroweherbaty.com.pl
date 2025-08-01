{{-- Logika: app/Livewire/Pages/Grupa.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;
use App\Models\Group;

layout('layouts.app');

state(['group' => '', 'products' => [], 'categoryName' => '']);

mount(function ($group) {
    $this->group = $group;

    // Dekodowanie URL
    $decodedGroup = urldecode($group);

    // Konwersja z formatu URL na format bazy danych
    $groupPath = str_replace(config('enova.grupa_url_separator'), '\\', $decodedGroup);

    // Dodanie prefixu i końcowego ukośnika dla formatu Enova
    $prefix = config('enova.features.product_group_prefix');
    $dbPath = $prefix . $groupPath . '\\';

    // Pobieranie produktów dla danej kategorii z nazwą i ceną
    $this->products = Product::with(['productNameFeature', 'price', 'group'])
        ->whereHas('group', function ($query) use ($dbPath) {
            $query->where('Data', $dbPath);
        })
        ->get()
        ->map(function ($product) {
            return $product->toDisplayArray();
        });

    // Pobieranie nazwy kategorii
    $category = Group::where('Data', $dbPath)->first();
    $this->categoryName = $category ? $category->clean_name : $decodedGroup;
});

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-black mt-1 mb-2">{{ $categoryName }}</h1>
        <p class="text-gray-600">Znaleziono {{ $products->count() }} produktów</p>
    </div>

    @if ($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <livewire:components.product-card :product="$product" variant="default" />
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Brak produktów w tej kategorii</p>
        </div>
    @endif
</div>
