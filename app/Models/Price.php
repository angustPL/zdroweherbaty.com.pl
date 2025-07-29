<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Price extends EnovaModel
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'Ceny';

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
        // Global scope dla cen z określoną definicją - automatycznie filtruje tylko ceny z konfigurowaną definicją
        static::addGlobalScope('hasDefinition', function (Builder $builder) {
            $builder->where('Definicja', config('enova.prices.definition'));
        });
    }

    /**
     * Pobiera produkt powiązany z ceną.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'Towar', 'ID');
    }
}
