<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model reprezentujący płatność w lokalnej bazie danych.
 *
 * Zawiera tylko dane lokalne. Jeśli płatność istnieje w Enova,
 * dane są pobierane bezpośrednio z Enova i nie są zapisywane lokalnie.
 */
class Payment extends Model
{
    /**
     * Pola, które mogą być masowo przypisane.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_method_guid',
        'payu_order_id',
        'payu_option',
        'ext_order_id',
        'status',
        'amount',
        'currency',
        'termin_dni',
        'payu_data',
        'paid_at',
        'failure_reason',
    ];

    /**
     * Pola, które powinny być rzutowane.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'termin_dni' => 'integer',
        'status' => PaymentStatus::class,
        'payu_data' => 'array',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacja z zamówieniem.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Sprawdź, czy płatność jest przez PayU.
     */
    public function isPayu(): bool
    {
        return str_starts_with($this->payment_method, 'payu_');
    }

    /**
     * Sprawdź, czy płatność jest gotówką.
     */
    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    /**
     * Sprawdź, czy płatność jest zakończona.
     */
    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }

    /**
     * Sprawdź, czy płatność oczekuje na potwierdzenie.
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING || $this->status === PaymentStatus::WAITING_FOR_CONFIRMATION;
    }

    /**
     * Sprawdź, czy płatność nie powiodła się.
     */
    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }
}
