<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $messageText
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Upewniamy się, że email jest prawidłowy przed użyciem w replyTo
        // Sprawdzamy zarówno przez filter_var jak i przez regex dla większej pewności
        $validEmail = filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false
            && preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $this->email);

        // Logowanie do debugowania
        \Illuminate\Support\Facades\Log::info('ContactFormMail::envelope()', [
            'email' => $this->email,
            'name' => $this->name,
            'validEmail' => $validEmail,
        ]);

        // Tylko jeśli email jest prawidłowy, ustawiamy replyTo z nazwą
        // Używamy klasy Address dla poprawnego formatowania z nazwą wyświetlaną
        if ($validEmail) {
            return new Envelope(
                subject: 'Formularz kontaktowy - Zdrowe Herbaty BIFIX',
                replyTo: [
                    new Address($this->email, $this->name),
                ],
            );
        }

        // Jeśli email nie jest prawidłowy, nie ustawiamy replyTo
        return new Envelope(
            subject: 'Formularz kontaktowy - Zdrowe Herbaty BIFIX',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'messageText' => $this->messageText,
            ],
        );
    }
}
