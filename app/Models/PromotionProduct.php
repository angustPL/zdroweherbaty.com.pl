<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionProduct extends Model
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'promotion_products';

    /**
     * Pola, które mogą być masowo przypisane.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'promotion_id',
        'product_id',
    ];

    /**
     * Relacja z promocją.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}

