<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends EnovaModel
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
        parent::booted();

        // Global scope dla opcji dostawy - automatycznie filtruje tylko rekordy z cechą grupa i wartością dostawy
        static::addGlobalScope('delivery', function (Builder $builder) {
            $builder->whereHas('features', function ($query) {
                $query->where('Name', 'grupa')
                    ->where('Data', config('enova.delivery.group_name'));
            })
                ->where('Towary.Blokada', 0);
        });

        // Global scope dla produktów nie zablokowanych
        static::addGlobalScope('notBlocked', function (Builder $builder) {
            $builder->where('Towary.Blokada', 0);
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
     * Pobiera cechę reprezentującą nazwę produktu.
     */
    public function productNameFeature()
    {
        return $this->hasOne(Feature::class, 'Parent', 'ID')
            ->where('Name', config('enova.features.product_name'))
            ->select(['Parent', 'Data as Name']);
    }

    public function price()
    {
        return $this->hasOne(Price::class, 'Towar', 'ID')
            ->where('Definicja', config('enova.prices.definition'));
    }

    /**
     * Pobiera sposób zapłaty dla dostawy.
     */
    public function paymentMethod()
    {
        return $this->hasOne(Feature::class, 'Parent', 'ID')
            ->where('Name', config('enova.orders.feature_payment_method'))
            ->join('SposobyZaplaty', 'Features.Data', '=', 'SposobyZaplaty.ID')
            ->select(['Features.Parent', 'SposobyZaplaty.ID', 'SposobyZaplaty.Nazwa', 'SposobyZaplaty.Opis']);
    }

    /**
     * Scope zapytania do opcji dostawy.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelivery($query)
    {
        return $query->whereHas('features', function ($query) {
            $query->where('Name', 'grupa')
                ->where('Data', config('enova.delivery.group_name'));
        });
    }

    /**
     * Scope do sortowania opcji dostawy.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByDelivery($query)
    {
        return $query->join('Ceny', 'Towary.ID', '=', 'Ceny.Towar')
            ->where('Ceny.Definicja', config('enova.prices.definition'))
            ->orderBy('MasaBruttoValue')
            ->orderBy('Ceny.BruttoValue');
    }

    /**
     * Mapuje opcję dostawy do tablicy z danymi do wyświetlenia.
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
            'NettoValue' => $this->price?->NettoValue ?? 0,
            'BruttoValue' => $this->price?->BruttoValue ?? 0,
            'StandardowaIloscValue' => $this->price?->StandardowaIloscValue ?? 0,
        ];
    }
}
