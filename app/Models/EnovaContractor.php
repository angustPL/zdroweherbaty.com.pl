<?php

namespace App\Models;

/**
 * Model reprezentujący kontrahenta/odbiorcę w Enova (Kontrahenci)
 * Read-only model - dane pochodzą z Enova
 *
 * Używany dla:
 * - Odbiorcy (Typ <> 0) - dane dostawy
 * - Kontrahenta do faktury (Typ = 0 i ma NIP) - dane do faktury
 */
class EnovaContractor extends EnovaModel
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'DaneKontrahentow';

    /**
     * Klucz główny powiązany z tabelą.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Scope do wyszukiwania po Host i HostType (np. dla zamówienia).
     */
    public function scopeByHost($query, int $hostId, string $hostType = 'DokHandlowe')
    {
        return $query->where('Host', $hostId)
            ->where('HostType', $hostType);
    }

    /**
     * Scope do wyszukiwania odbiorców (Typ <> 0).
     */
    public function scopeRecipients($query)
    {
        return $query->where('Typ', '<>', 0);
    }

    /**
     * Scope do wyszukiwania kontrahentów do faktury (Typ = 0 i ma NIP).
     */
    public function scopeInvoiceContractors($query)
    {
        return $query->where('Typ', 0)
            ->whereNotNull('NIP');
    }
}
