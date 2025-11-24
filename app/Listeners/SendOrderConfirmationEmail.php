<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        try {
            $order = $event->order;

            // Wyślij email do klienta
            Mail::to($order->customer_email)
                ->send(new OrderConfirmationMail($order));

            // Wyślij email do sklepu (jeśli skonfigurowany)
            $shopEmail = config('enova.orders.email.address');
            if ($shopEmail && $shopEmail !== $order->customer_email) {
                Mail::to($shopEmail)
                    ->send(new OrderConfirmationMail($order));
            }

            Log::info('Email potwierdzenia zamówienia wysłany', [
                'order_id' => $order->id,
                'ext_order_id' => $order->ext_order_id,
                'customer_email' => $order->customer_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd wysyłki email potwierdzenia zamówienia: ' . $e->getMessage(), [
                'order_id' => $event->order->id,
                'ext_order_id' => $event->order->ext_order_id,
                'exception' => $e,
            ]);
        }
    }
}
