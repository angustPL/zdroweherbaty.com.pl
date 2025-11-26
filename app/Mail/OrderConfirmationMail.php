<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\EnovaOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    ) {
        // Ustaw orderNumber (NumerPelny z Enova jeśli dostępny, w przeciwnym razie ext_order_id)
        $enovaOrder = EnovaOrder::byGuid($order->ext_order_id)->first();
        if ($enovaOrder && !empty($enovaOrder->NumerPelny)) {
            $order->orderNumber = $enovaOrder->NumerPelny;
        } else {
            $order->orderNumber = $order->ext_order_id;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Potwierdzenie zamówienia',
            replyTo: config('enova.orders.email.address', 'sklep@bifix.pl'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
