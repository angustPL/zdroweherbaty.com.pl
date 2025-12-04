<?php

use function Livewire\Volt\{state, mount, layout, action};
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

layout('layouts.app');

// SEO Meta Tags
app('seotools')->setTitle('Kontakt - Zdrowe Herbaty BIFIX');
app('seotools')->setDescription('Skontaktuj się z nami w sprawie herbat BIFIX. Adres, telefon, email i godziny otwarcia.');

state([
    'name' => '',
    'email' => '',
    'message' => '',
    'submitted' => false,
]);

$submit = action(function () {
    $rules = [
        'name' => 'required|string|min:2|max:255',
        'email' => ['required', 'email:rfc,dns', 'max:255', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/'],
        'message' => 'required|string|min:10|max:5000',
    ];

    $messages = [
        'name.required' => 'Imię i nazwisko jest wymagane.',
        'name.min' => 'Imię i nazwisko musi mieć co najmniej :min znaki.',
        'email.required' => 'Adres email jest wymagany.',
        'email.email' => 'Podaj prawidłowy adres email.',
        'message.required' => 'Wiadomość jest wymagana.',
        'message.min' => 'Wiadomość musi mieć co najmniej :min znaków.',
    ];

    // Walidacja - ValidationException jest automatycznie obsługiwany przez Livewire
    // Jeśli walidacja nie przejdzie, ValidationException zostanie rzucony i Livewire go obsłuży
    $this->validate($rules, $messages);

    // Jeśli walidacja przeszła, kontynuujemy
    $name = trim(strip_tags($this->name));
    $email = trim($this->email);

    // Dodatkowa walidacja emaila - upewniamy się, że jest prawidłowy
    // Sprawdzamy zarówno przez filter_var jak i przez regex dla większej pewności
    $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false && preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email);

    if (!$isValidEmail) {
        Log::warning('Nieprawidłowy email w formularzu kontaktowym', [
            'email' => $email,
            'name' => $name,
        ]);
        throw new \Illuminate\Validation\ValidationException(validator([], []), ['email' => ['Podaj prawidłowy adres email (np. przyklad@domena.pl).']]);
    }

    $messageText = trim(strip_tags($this->message));

    try {
        $recipientEmail = config('enova.orders.email.address', 'sklep@bifix.pl');
        // Upewniamy się, że przekazujemy dane w poprawnej kolejności
        Mail::to($recipientEmail)->send(new ContactFormMail($name, $email, $messageText));

        $this->submitted = true;
        $this->reset(['name', 'email', 'message']);

        Log::info('Formularz kontaktowy wysłany', [
            'from' => $email,
            'to' => $recipientEmail,
        ]);
    } catch (\Exception $e) {
        Log::error('Błąd wysyłki formularza kontaktowego: ' . $e->getMessage());
        session()->flash('error', 'Wystąpił błąd podczas wysyłania wiadomości. Spróbuj ponownie później.');
    }
});

mount(function () {});

?>

<div>
    <section class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Kontakt</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Dane kontaktowe -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Dane kontaktowe</h2>
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">BiFIX Wojciech Piasecki Sp. j.</h3>
                            <div class="space-y-2 text-gray-600">
                                <p>Górki Małe, ul. Dworska 33</p>
                                <p>95-080 Tuszyn</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Telefon i Fax</h3>
                            <div class="space-y-2 text-gray-600">
                                <p>Tel. <a href="tel:+48426144058" class="hover:text-primary">42 614 40 58</a> wew. 155
                                </p>
                                <p>Fax. <a href="tel:+48426144120" class="hover:text-primary">42 614 41 20</a></p>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Osoba do kontaktu ws. sklepu</h3>
                            <div class="space-y-2 text-gray-600">
                                <p class="font-medium">Małgorzata Frączkowska</p>
                                <p>od poniedziałku do piątku, godz.: 8:00 - 16:00</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Dział realizacji zamówień</h3>
                            <p class="text-gray-600">
                                <a href="mailto:sklep@bifix.pl" class="hover:text-primary">sklep@bifix.pl</a>
                            </p>
                        </div>

                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Dział reklamacji</h3>
                            <p class="text-gray-600">
                                <a href="mailto:reklamacjasklep@bifix.pl"
                                    class="hover:text-primary">reklamacjasklep@bifix.pl</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formularz -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Formularz kontaktowy</h2>

                    @if ($submitted)
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <div class="text-center py-8">
                                <div class="text-green-600 text-6xl mb-4">✓</div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Dziękujemy!</h3>
                                <p class="text-gray-600 mb-4">Twoja wiadomość została wysłana. Skontaktujemy się z Tobą
                                    wkrótce.</p>
                                <flux:button wire:click="$set('submitted', false)">Wyślij kolejną wiadomość
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            @if (session('error'))
                                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form wire:submit.prevent="submit" class="space-y-6">
                                <flux:input wire:model="name" label="Imię i nazwisko"
                                    placeholder="Wprowadź swoje imię i nazwisko" />

                                <flux:input wire:model="email" label="Email" type="email"
                                    placeholder="Wprowadź swój email" />

                                <flux:textarea wire:model="message" label="Wiadomość" rows="4"
                                    placeholder="Wprowadź treść wiadomości" />

                                <flux:button type="submit" variant="primary" class="w-full"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove>Wyślij wiadomość</span>
                                    <span wire:loading>Wysyłanie...</span>
                                </flux:button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

</div>
