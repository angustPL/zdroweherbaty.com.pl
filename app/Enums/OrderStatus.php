<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted'; // Złożone, oczekuje na synchronizację z Enova
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Oczekuje'),
            self::SUBMITTED => __('Złożone'),
            self::PROCESSING => __('W realizacji'),
            self::COMPLETED => __('Zrealizowane'),
            self::CANCELLED => __('Anulowane'),
        };
    }
}
