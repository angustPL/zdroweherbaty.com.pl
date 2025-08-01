<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends EnovaModel
{
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
        // Global scope dla produktów z grupą - automatycznie filtruje tylko produkty posiadające przypisaną grupę
        static::addGlobalScope('hasGroup', function (Builder $builder) {
            $builder->whereHas('group');
        });

        // Global scope dla produktów z cechą product_mark - automatycznie filtruje tylko produkty oznaczone jako dostępne w sklepie
        static::addGlobalScope('hasProductMark', function (Builder $builder) {
            $builder->whereHas('features', function ($query) {
                $query->where('Name', config('enova.features.product_mark'))
                    ->where('Data', '1')
                    ->where('Blokada', 0);
            });
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
     * Mapuje produkt do tablicy z danymi do wyświetlenia.
     *
     * @return array
     */
    public function toDisplayArray()
    {
        return [
            'ID' => $this->ID,
            'Nazwa' => $this->productNameFeature->Name ?? $this->Nazwa,
            'Grupa' => $this->group->clean_name ?? null,
            'Opis' => $this->Opis,
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
        ];
    }
}
