<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Group extends EnovaModel
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'Features';

    /**
     * Określa czy ID są auto-inkrementowane.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Klucz główny dla modelu.
     *
     * @var array
     */
    protected $primaryKey = ['Parent', 'ParentType', 'Name', 'Lp'];

    /**
     * Typ auto-inkrementowanego ID.
     *
     * @var string
     */
    protected $keyType = 'array';

    /**
     * Pobiera wartość klucza głównego modelu.
     *
     * @return array
     */
    public function getKey()
    {
        return [
            'Parent' => $this->Parent,
            'ParentType' => $this->ParentType,
            'Name' => $this->Name,
            'Lp' => $this->Lp
        ];
    }

    /**
     * Metoda "booted" modelu.
     */
    protected static function booted(): void
    {
        parent::booted();

        // Global scope dla grup - automatycznie filtruje tylko grupy z odpowiednim prefiksem
        static::addGlobalScope('withPrefix', function (Builder $builder) {
            $prefix = config('enova.features.product_group_prefix');
            $builder->where('Data', 'like', $prefix . '%');
        });
    }

    /**
     * Pobiera oczyszczoną nazwę grupy.
     *
     * @return string
     */
    public function getCleanNameAttribute(): string
    {
        $prefix = config('enova.features.product_group_prefix');
        // Usuń prefiks
        $name = Str::after($this->Data, $prefix);
        // Usuń końcowy ukośnik
        return rtrim($name, '\\');
    }

    /**
     * Pobiera hierarchiczną strukturę grup.
     * Tylko grupy, które mają przynajmniej jeden produkt spełniający wszystkie warunki
     * (ma grupę, ma product_mark=1, nie jest zablokowany).
     * Wyniki są cache'owane zgodnie z konfiguracją (domyślnie 24 godziny).
     * W przypadku awarii Enova używa cache (nawet jeśli wygasł).
     *
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie z konfiguracji)
     * @return array
     */
    public static function getHierarchicalStructure(?int $ttl = null): array
    {
        $cacheKey = 'enova_groups_hierarchy_with_products';

        return static::getCachedWithBackup(
            $cacheKey,
            function () {
                $prefix = config('enova.features.product_group_prefix');
                $productGroupName = config('enova.features.product_group');
                $productMark = config('enova.features.product_mark');

                // Pobierz tylko grupy, które mają przynajmniej jeden produkt spełniający wszystkie warunki
                // (ma grupę, ma product_mark=1, nie jest zablokowany)
                // Group to Feature, gdzie Name = 'www_grupa', Data = ścieżka grupy, Parent = ID produktu
                // Musimy sprawdzić, czy istnieją produkty (Towary) które:
                // 1. Mają Feature z Name='www_grupa' i Data=ścieżka grupy (czyli są w tej grupie)
                // 2. Mają Blokada=0 (nie są zablokowane)
                // 3. Mają Feature z Name=product_mark i Data='1' (są oznaczone jako dostępne w sklepie)

                $groupsWithProducts = self::select('Features.Data')
                    ->where('Features.Data', 'like', $prefix . '%')
                    ->where('Features.Name', $productGroupName)
                    ->whereExists(function ($query) use ($productMark) {
                        $query->select(DB::raw(1))
                            ->from('Towary')
                            ->whereColumn('Towary.ID', 'Features.Parent')
                            ->where('Towary.Blokada', 0) // notBlocked
                            ->whereExists(function ($subQuery) use ($productMark) {
                                $subQuery->select(DB::raw(1))
                                    ->from('Features as F2')
                                    ->whereColumn('F2.Parent', 'Towary.ID')
                                    ->where('F2.Name', $productMark)
                                    ->where('F2.Data', '1'); // hasProductMark
                            });
                    })
                    ->distinct()
                    ->orderBy('Features.Data')
                    ->get();

                $hierarchy = [];

                foreach ($groupsWithProducts as $group) {
                    $path = Str::after($group->Data, $prefix);
                    $path = rtrim($path, '\\');
                    $parts = explode('\\', $path);

                    $current = &$hierarchy;

                    foreach ($parts as $part) {
                        if (!isset($current[$part])) {
                            $current[$part] = [
                                'name' => $part,
                                'full_path' => implode('\\', array_slice($parts, 0, array_search($part, $parts) + 1)),
                                'children' => []
                            ];
                        }
                        $current = &$current[$part]['children'];
                    }
                }

                return $hierarchy;
            },
            [], // wartość domyślna: pusta tablica
            $ttl,
            'groups hierarchy'
        );
    }

    /**
     * Pobiera pojedynczą grupę po ścieżce z cache'owaniem.
     * W przypadku awarii Enova używa cache (nawet jeśli wygasł).
     *
     * @param string $groupPath Pełna ścieżka grupy w formacie Enova (np. "kategoria\Zestawy herbat\")
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie 48h)
     * @return static|null
     */
    public static function getCachedByPath(string $groupPath, ?int $ttl = null)
    {
        $cacheKey = 'enova_group_' . md5($groupPath);

        $result = static::getCachedWithBackup(
            $cacheKey,
            function () use ($groupPath) {
                $group = static::where('Data', $groupPath)->first();
                // Jeśli grupa istnieje, zwróć jako tablicę atrybutów (dla łatwiejszej serializacji)
                return $group ? [
                    'ID' => $group->Parent ?? null,
                    'Data' => $group->Data ?? null,
                    'Name' => $group->Name ?? null,
                    'clean_name' => $group->clean_name ?? null,
                ] : null;
            },
            null, // wartość domyślna: null
            $ttl,
            "group_path: {$groupPath}"
        );

        // Jeśli wynik to tablica, utwórz model z atrybutów
        if (is_array($result) && isset($result['Data'])) {
            $group = new static();
            $group->Parent = $result['ID'] ?? null;
            $group->Data = $result['Data'] ?? null;
            $group->Name = $result['Name'] ?? null;
            // clean_name jest atrybutem obliczanym, więc nie trzeba go zapisywać
            return $group;
        }

        return $result;
    }

    /**
     * Relacja do produktów przypisanych do tej grupy.
     * Grupa to Feature, gdzie Parent = ID produktu, Name = 'www_grupa', Data = ścieżka grupy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'Parent', 'ID');
    }

    /**
     * Rekurencyjnie przekształca hierarchię w płaską listę dla Flux UI.
     *
     * @param array $hierarchy
     * @param int $level
     * @return array
     */
    public static function flattenHierarchyForFlux(array $hierarchy, int $level = 0): array
    {
        $result = [];

        foreach ($hierarchy as $item) {
            $result[] = [
                'name' => $item['name'],
                'full_path' => $item['full_path'],
                'level' => $level,
                'has_children' => !empty($item['children'])
            ];

            if (!empty($item['children'])) {
                $result = array_merge($result, self::flattenHierarchyForFlux($item['children'], $level + 1));
            }
        }

        return $result;
    }

    /**
     * Pobiera wszystkie grupy (bez filtracji produktów) - podobnie jak drzewko.
     * Używane do autocomplete gdzie potrzebujemy wszystkie grupy.
     *
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie z konfiguracji)
     * @return array
     */
    public static function getAllGroupsHierarchy(?int $ttl = null): array
    {
        $cacheKey = 'enova_all_groups_hierarchy';

        return static::getCachedWithBackup(
            $cacheKey,
            function () {
                // Formularz promocji potrzebuje TYLKO grupy z produktami
                $prefix = config('enova.features.product_group_prefix');
                $productGroupName = config('enova.features.product_group');

                // Debug: pokaż konfigurację
                logger('DEBUG - Prefix: ' . $prefix);
                logger('DEBUG - ProductGroupName: ' . $productGroupName);

                // Pobierz grupy TYLKO z produktów (formularz promocji)
                $query = self::select('Features.Data', 'Features.Name')
                    ->where('Features.Data', 'like', $prefix . '%')
                    ->where('Features.Name', $productGroupName)
                    ->whereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('Towary')
                            ->whereColumn('Towary.ID', 'Features.Parent');
                    });

                // Debug: pokaż zapytanie SQL
                logger('DEBUG - SQL Query: ' . $query->toSql());
                logger('DEBUG - SQL Bindings: ' . json_encode($query->getBindings()));

                $allGroups = $query->orderBy('Features.Data')->get();

                // Debug: pokaż liczbę grup z bazy
                logger('DEBUG - Grupy z produktami (przed hierarchią): ' . $allGroups->count());

                // Debug: pokaż pierwsze 5 grup do analizy
                foreach ($allGroups->take(5) as $group) {
                    logger('DEBUG - Przykładowa grupa z produktem: ' . $group->Data);
                }

                $hierarchy = [];

                foreach ($allGroups as $group) {
                    $path = Str::after($group->Data, $prefix);
                    $path = rtrim($path, '\\');
                    $parts = explode('\\', $path);

                    $current = &$hierarchy;

                    foreach ($parts as $part) {
                        if (!isset($current[$part])) {
                            $current[$part] = [
                                'name' => $part,
                                'full_path' => implode('\\', array_slice($parts, 0, array_search($part, $parts) + 1)),
                                'children' => []
                            ];
                        }
                        $current = &$current[$part]['children'];
                    }
                }

                // Debug: pokaż liczbę grup po hierarchii
                logger('DEBUG - Grupy z produktami po hierarchii: ' . count($hierarchy, COUNT_RECURSIVE));

                return $hierarchy;
            },
            [], // wartość domyślna: pusta tablica
            $ttl,
            'all groups hierarchy'
        );
    }
}
