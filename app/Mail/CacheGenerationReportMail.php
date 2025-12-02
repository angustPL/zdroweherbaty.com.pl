<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CacheGenerationReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $stats;
    public float $duration;
    public bool $success;
    public ?string $errorMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(
        array $stats,
        float $duration,
        bool $success = true,
        ?string $errorMessage = null
    ) {
        $this->stats = $stats;
        $this->duration = $duration;
        $this->success = $success;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->success 
            ? '✓ Raport generowania cache Enova - Sukces'
            : '✗ Raport generowania cache Enova - Błąd';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cache-generation-report',
        );
    }
}

