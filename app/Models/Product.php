<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Product extends EnovaModel
{
    use Searchable;

    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'Towary';

    /**
     * Klucz główny powiązany z tabelą.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Metoda "booted" modelu.
     */
    protected static function booted(): void
    {
        parent::booted();

        // Global scope dla produktów z grupą - automatycznie filtruje tylko produkty posiadające grupę
        static::addGlobalScope('hasGroup', function (Builder $builder) {
            $builder->whereHas('group');
        });

        // Global scope dla produktów z cechą product_mark - automatycznie filtruje tylko produkty oznaczone jako dostępne w sklepie
        static::addGlobalScope('hasProductMark', function (Builder $builder) {
            $builder->whereHas('features', function ($query) {
                $query->where('Name', config('enova.features.product_mark'))
                    ->where('Data', '1');
            });
        });

        // Global scope dla produktów nie zablokowanych
        static::addGlobalScope('notBlocked', function (Builder $builder) {
            $builder->where('Blokada', 0);
        });
    }

    /**
     * Pobiera wszystkie cechy produktu.
     */
    public function features()
    {
        return $this->hasMany(Feature::class, 'Parent', 'ID');
    }

    /**
     * Pobiera cechę reprezentującą grupę produktu.
     */
    public function group()
    {
        return $this->hasOne(Group::class, 'Parent', 'ID');
    }

    /**
     * Pobiera cechę reprezentującą nazwę produktu.
     */
    public function productNameFeature()
    {
        return $this->hasOne(Feature::class, 'Parent', 'ID')
            ->where('Name', config('enova.features.product_name'))
            ->select(['Parent', 'Data as Name']);
    }

    /**
     * Pobiera cechę reprezentującą oznaczenie produktu (product_mark).
     */
    public function productMark()
    {
        return $this->hasOne(Feature::class, 'Parent', 'ID')
            ->where('Name', config('enova.features.product_mark'));
    }

    /**
     * Pobiera główną cenę produktu.
     */
    public function price()
    {
        return $this->hasOne(Price::class, 'Towar', 'ID')
            ->where('Definicja', config('enova.prices.definition'))
            ->select(['Towar', 'NettoValue', 'BruttoValue', 'StandardowaIloscValue']);
    }

    /**
     * Scope zapytania do produktów z określonej grupy.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $cleanGroupName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereGroupIs($query, $cleanGroupName)
    {
        $prefix = config('enova.features.product_group_prefix');
        $fullGroupName = $prefix . $cleanGroupName . '\\';

        return $query->whereHas('group', function ($groupQuery) use ($fullGroupName) {
            $groupQuery->where('Data', $fullGroupName);
        });
    }

    /**
     * Pobiera produkty z określonej grupy z cache'owaniem.
     * Wyniki są cache'owane zgodnie z konfiguracją (domyślnie 24 godziny).
     * W przypadku awarii Enova używa cache (nawet jeśli wygasł).
     *
     * @param string $groupPath Pełna ścieżka grupy w formacie Enova (np. "Grupa\Podgrupa\")
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie z konfiguracji)
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public static function getCachedByGroup(string $groupPath, ?int $ttl = null)
    {
        $cacheKey = 'enova_products_group_' . md5($groupPath);

        return static::getCachedWithBackup(
            $cacheKey,
            function () use ($groupPath) {
                return static::with(['productNameFeature', 'price', 'group', 'productMark'])
                    ->whereHas('group', function ($query) use ($groupPath) {
                        $query->where('Data', $groupPath);
                    })
                    ->get()
                    ->map(function ($product) {
                        return $product->toDisplayArray();
                    })
                    ->toArray(); // Zawsze zwracaj tablicę dla spójności z cache
            },
            [], // wartość domyślna: pusta tablica
            $ttl,
            "group_path: {$groupPath}"
        );
    }

    /**
     * Pobiera pojedynczy produkt z cache'owaniem.
     * Wyniki są cache'owane zgodnie z konfiguracją (domyślnie 24 godziny).
     * W przypadku awarii Enova używa cache (nawet jeśli wygasł).
     *
     * @param int $productId ID produktu
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie z konfiguracji)
     * @return array|null
     */
    public static function getCachedById(int $productId, ?int $ttl = null): ?array
    {
        $cacheKey = 'enova_product_' . $productId;

        return static::getCachedWithBackup(
            $cacheKey,
            function () use ($productId) {
                $product = static::with(['productNameFeature', 'price', 'group', 'productMark'])
                    ->where('ID', $productId)
                    ->first();

                return $product ? $product->toDisplayArray() : null;
            },
            null, // wartość domyślna: null
            $ttl,
            "product_id: {$productId}"
        );
    }

    /**
     * Pobiera wszystkie produkty z cache'owaniem.
     * Wyniki są cache'owane zgodnie z konfiguracją (domyślnie 24 godziny).
     * W przypadku awarii Enova używa cache (nawet jeśli wygasł).
     *
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie z konfiguracji)
     * @return array
     */
    public static function getCachedAll(?int $ttl = null): array
    {
        $cacheKey = 'enova_products_all';

        return static::getCachedWithBackup(
            $cacheKey,
            function () {
                $products = static::with(['productNameFeature', 'price', 'group', 'productMark'])
                    ->get();

                // Batch check all image files at once to reduce memory usage
                $imagePaths = [];
                foreach ($products as $product) {
                    $imagePaths[] = 'img/towary/' . $product->ID . '_200x120.jpg';
                    $imagePaths[] = 'img/towary/' . $product->ID . '_800x600.jpg';
                }
                
                // Check all files in one operation
                $existingImages = [];
                $storage = Storage::disk('public');
                foreach ($imagePaths as $path) {
                    if ($storage->exists($path)) {
                        $existingImages[$path] = true;
                    }
                }

                // Map products with pre-checked image existence
                return $products->map(function ($product) use ($existingImages) {
                    $imageSmall = 'img/towary/' . $product->ID . '_200x120.jpg';
                    $imageLarge = 'img/towary/' . $product->ID . '_800x600.jpg';

                    return [
                        'ID' => $product->ID,
                        'Nazwa' => $product->productNameFeature->Name ?? $product->Nazwa,
                        'Grupa' => $product->group->clean_name ?? null,
                        'Opis' => $product->Opis,
                        'HasProductMark' => (string) ($product->productMark->Data ?? '') === '1',
                        'MasaNettoValue' => $product->MasaNettoValue,
                        'MasaNettoSymbol' => $product->MasaNettoSymbol,
                        'MasaBruttoValue' => $product->MasaBruttoValue,
                        'MasaBruttoSymbol' => $product->MasaBruttoSymbol,
                        'SWW' => $product->SWW,
                        'DefinicjaStawki' => $product->DefinicjaStawki,
                        'NettoValue' => $product->price->NettoValue,
                        'BruttoValue' => $product->price->BruttoValue,
                        'StandardowaIloscValue' => $product->price->StandardowaIloscValue,
                        'Jednostka' => $product->price->Jednostka,
                        'StandardowaIloscSymbol' => $product->price->StandardowaIloscSymbol,
                        'HasImageSmall' => isset($existingImages[$imageSmall]),
                        'HasImageLarge' => isset($existingImages[$imageLarge]),
                        'ImageSmallPath' => $imageSmall,
                        'ImageLargePath' => $imageLarge,
                    ];
                })->toArray();
            },
            [], // wartość domyślna: pusta tablica
            $ttl,
            'all products'
        );
    }

    /**
     * Mapuje produkt do tablicy z danymi do wyświetlenia.
     *
     * @return array
     */
    public function toDisplayArray()
    {
        // Sprawdź istnienie obrazów raz i zapisz w cache, aby uniknąć wielokrotnych sprawdzeń plików
        $imageSmall = 'img/towary/' . $this->ID . '_200x120.jpg';
        $imageLarge = 'img/towary/' . $this->ID . '_800x600.jpg';

        return [
            'ID' => $this->ID,
            'Nazwa' => $this->productNameFeature->Name ?? $this->Nazwa,
            'Grupa' => $this->group->clean_name ?? null,
            'Opis' => $this->Opis,
            'HasProductMark' => (string) ($this->productMark->Data ?? '') === '1',
            'MasaNettoValue' => $this->MasaNettoValue,
            'MasaNettoSymbol' => $this->MasaNettoSymbol,
            'MasaBruttoValue' => $this->MasaBruttoValue,
            'MasaBruttoSymbol' => $this->MasaBruttoSymbol,
            'SWW' => $this->SWW,
            'DefinicjaStawki' => $this->DefinicjaStawki,
            'NettoValue' => $this->price->NettoValue,
            'BruttoValue' => $this->price->BruttoValue,
            'StandardowaIloscValue' => $this->price->StandardowaIloscValue,
            'Jednostka' => $this->price->Jednostka,
            'StandardowaIloscSymbol' => $this->price->StandardowaIloscSymbol,
            'HasImageSmall' => Storage::disk('public')->exists($imageSmall),
            'HasImageLarge' => Storage::disk('public')->exists($imageLarge),
            'ImageSmallPath' => $imageSmall,
            'ImageLargePath' => $imageLarge,
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->ID,
            'name' => $this->productNameFeature->Name ?? $this->Nazwa,
            'description' => $this->Opis,
            'group' => $this->group->clean_name ?? null,
            'price' => $this->price?->BruttoValue ?? 0,
            'price_range' => $this->getPriceRange(),
        ];
    }

    /**
     * Get the price range for the product.
     *
     * @return string
     */
    private function getPriceRange()
    {
        $price = $this->price?->BruttoValue ?? 0;

        if ($price <= 10) return 'budget';
        if ($price <= 25) return 'medium';
        if ($price <= 50) return 'premium';
        return 'luxury';
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'products';
    }
}
