<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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
     * Wyniki są cache'owane zgodnie z konfiguracją (domyślnie 24 godziny).
     *
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie z konfiguracji)
     * @return array
     */
    public static function getHierarchicalStructure(?int $ttl = null): array
    {
        $cacheKey = 'enova_groups_hierarchy';

        $cacheTtl = $ttl ?? config('enova.cache.ttl', 86400);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            $groups = self::orderBy('Data')->get();
            $hierarchy = [];

            foreach ($groups as $group) {
                $path = $group->clean_name;
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
        });
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
}
