<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model reprezentujący zamówienie w Enova (DokHandlowe)
 * Read-only model - dane pochodzą z Enova
 */
class EnovaOrder extends EnovaModel
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'DokHandlowe';

    /**
     * Klucz główny powiązany z tabelą.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Mapowanie kolumn (jeśli potrzebne).
     *
     * @var array<string, string>
     */
    protected $attributes = [];

    /**
     * Pobierz pozycje zamówienia.
     * Używa modelu EnovaOrderPosition z scope byOrderId.
     */
    public function getPositions()
    {
        if (!$this->getKey()) {
            return collect();
        }

        // Użyj bezpośrednio modelu EnovaOrderPosition z scope byOrderId
        return EnovaOrderPosition::byOrderId($this->getKey())->get();
    }

    /**
     * Relacja z pozycjami zamówienia.
     */
    public function positions(): HasMany
    {
        return $this->hasMany(EnovaOrderPosition::class, 'Dokument', 'ID');
    }

    /**
     * Pobierz kontrahentów/odbiorców dla tego zamówienia.
     * Używa tabeli DaneKontrahentow z warunkiem Host = ID zamówienia i HostType = 'DokHandlowe'.
     */
    public function getContractors()
    {
        if (!$this->getKey()) {
            return collect();
        }

        return EnovaContractor::where('Host', $this->getKey())
            ->where('HostType', 'DokHandlowe')
            ->orderBy('ID', 'DESC')
            ->get();
    }

    /**
     * Pobierz odbiorcę (Typ <> 0).
     */
    public function getRecipient()
    {
        if (!$this->getKey()) {
            return null;
        }

        return EnovaContractor::where('Host', $this->getKey())
            ->where('HostType', 'DokHandlowe')
            ->where('Typ', '<>', 0)
            ->orderBy('ID', 'DESC')
            ->first();
    }

    /**
     * Pobierz kontrahenta do faktury (Typ = 0 i ma NIP).
     */
    public function getInvoiceContractor()
    {
        if (!$this->getKey()) {
            return null;
        }

        return EnovaContractor::where('Host', $this->getKey())
            ->where('HostType', 'DokHandlowe')
            ->where('Typ', 0)
            ->whereNotNull('NIP')
            ->orderBy('ID', 'DESC')
            ->first();
    }

    /**
     * Pobierz email z Features.
     */
    public function getEmailAttribute(): ?string
    {
        if (!$this->getKey()) {
            return null;
        }
        $feature = Feature::where('Parent', $this->getKey())
            ->where('Name', 'E-mail_zamowienia')
            ->first();
        return $feature?->Data;
    }

    /**
     * Pobierz telefon z Features.
     */
    public function getPhoneAttribute(): ?string
    {
        if (!$this->getKey()) {
            return null;
        }
        $feature = Feature::where('Parent', $this->getKey())
            ->where('Name', 'Telefon_zamowienia')
            ->first();
        return $feature?->Data;
    }

    /**
     * Pobierz uwagi z Features.
     */
    public function getNotesAttribute(): ?string
    {
        if (!$this->getKey()) {
            return null;
        }
        $feature = Feature::where('Parent', $this->getKey())
            ->where('Name', 'Uwagi')
            ->first();
        return $feature?->Data;
    }

    /**
     * Scope do wyszukiwania po GUID (ext_order_id).
     */
    public function scopeByGuid($query, string $guid)
    {
        return $query->where('Guid', $guid);
    }

    /**
     * Scope do wyszukiwania po NumerPelny.
     */
    public function scopeByOrderNumber($query, string $orderNumber)
    {
        return $query->where('NumerPelny', $orderNumber);
    }
}
