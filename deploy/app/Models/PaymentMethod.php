<?php

namespace App\Models;

/**
 * Model dla tabeli SposobyZaplaty z bazy Enova.
 * Model jest tylko do odczytu - dane są zarządzane w systemie Enova.
 */
class PaymentMethod extends EnovaModel
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'SposobyZaplaty';

    /**
     * Klucz główny powiązany z tabelą.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Model jest tylko do odczytu - dane są zarządzane w systemie Enova.
     */
    public $timestamps = false;
}
