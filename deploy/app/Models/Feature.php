<?php

namespace App\Models;

class Feature extends EnovaModel
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
}
