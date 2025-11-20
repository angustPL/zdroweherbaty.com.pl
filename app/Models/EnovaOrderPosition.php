<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model reprezentujący pozycję zamówienia w Enova (PozycjeZamowien)
 * Read-only model - dane pochodzą z Enova
 */
class EnovaOrderPosition extends EnovaModel
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'PozycjeDokHan';

    /**
     * Klucz główny powiązany z tabelą.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Relacja z zamówieniem.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(EnovaOrder::class, 'Dokument', 'ID');
    }

    /**
     * Relacja z produktem (Towar).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'Towar', 'ID');
    }

    /**
     * Scope do wyszukiwania po ID dokumentu (zamówienia).
     */
    public function scopeByOrderId($query, int $orderId)
    {
        return $query->where('Dokument', $orderId);
    }
}
