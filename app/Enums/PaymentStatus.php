<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case WAITING_FOR_CONFIRMATION = 'waiting_for_confirmation';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Oczekuje na płatność'),
            self::WAITING_FOR_CONFIRMATION => __('Oczekuje na potwierdzenie'),
            self::COMPLETED => __('Opłacone'),
            self::FAILED => __('Nieudane'),
        };
    }
}
