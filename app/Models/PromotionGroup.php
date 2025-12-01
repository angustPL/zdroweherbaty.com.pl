<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionGroup extends Model
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'promotion_groups';

    /**
     * Pola, które mogą być masowo przypisane.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'promotion_id',
        'group_path',
    ];

    /**
     * Relacja z promocją.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}

