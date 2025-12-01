<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPromotion extends Model
{
    /**
     * Tabela powiązana z modelem.
     *
     * @var string
     */
    protected $table = 'order_promotions';

    /**
     * Pola, które mogą być masowo przypisane.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'promotion_id',
    ];

    /**
     * Relacja z zamówieniem.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relacja z promocją.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}

