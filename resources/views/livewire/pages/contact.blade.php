<?php

use function Livewire\Volt\{state, mount, rules, layout};

layout('layouts.app');

state([
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => '',
    'submitted' => false,
]);

rules([
    'name' => 'required|min:2',
    'email' => 'required|email',
    'subject' => 'required|min:5',
    'message' => 'required|min:10',
]);

$submit = function () {
    $this->validate();

    // Tutaj można dodać logikę wysyłania emaila
    // Mail::to('info@zdroweherbaty.com.pl')->send(new ContactForm($this->name, $this->email, $this->subject, $this->message));

    $this->submitted = true;
    $this->reset(['name', 'email', 'subject', 'message']);
};

?>

<div>
    <!-- Hero Section -->
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Kontakt
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Skontaktuj się z nami - chętnie pomożemy!
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Info Section -->
    <section class="bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Contact Information -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Dane kontaktowe</h2>

                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Adres</h3>
                                <p class="text-gray-600">
                                    ul. Przykładowa 1<br>
                                    00-000 Warszawa<br>
                                    Polska
                                </p>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Telefon</h3>
                                <p class="text-gray-600">
                                    +48 123 456 789
                                </p>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Email</h3>
                                <p class="text-gray-600">
                                    info@zdroweherbaty.com.pl
                                </p>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Godziny otwarcia</h3>
                                <div class="space-y-2 text-gray-600">
                                    <p>Poniedziałek - Piątek: 9:00 - 18:00</p>
                                    <p>Sobota: 9:00 - 14:00</p>
                                    <p>Niedziela: Zamknięte</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Formularz kontaktowy</h2>

                    @if ($submitted)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="p-6">
                                <div class="text-center py-8">
                                    <div class="text-green-600 text-6xl mb-4">✓</div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Dziękujemy!</h3>
                                    <p class="text-gray-600">Twoja wiadomość została wysłana. Skontaktujemy się z Tobą
                                        wkrótce.</p>
                                    <flux:button wire:click="$set('submitted', false)" class="mt-4">
                                        Wyślij kolejną wiadomość
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="p-6">
                                <form wire:submit="submit" class="space-y-6">
                                    <flux:field label="Imię i nazwisko" name="name">
                                        <flux:input wire:model="name" placeholder="Wprowadź swoje imię i nazwisko" />
                                        @error('name')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </flux:field>

                                    <flux:field label="Email" name="email">
                                        <flux:input wire:model="email" type="email"
                                            placeholder="Wprowadź swój email" />
                                        @error('email')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </flux:field>

                                    <flux:field label="Temat" name="subject">
                                        <flux:input wire:model="subject" placeholder="Temat wiadomości" />
                                        @error('subject')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </flux:field>

                                    <flux:field label="Wiadomość" name="message">
                                        <flux:textarea wire:model="message" rows="4"
                                            placeholder="Wprowadź treść wiadomości" />
                                        @error('message')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </flux:field>

                                    <flux:button type="submit" class="w-full" wire:loading.attr="disabled">
                                        <span wire:loading.remove>Wyślij wiadomość</span>
                                        <span wire:loading>Wysyłanie...</span>
                                    </flux:button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-white py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Często zadawane pytania
                </h2>
            </div>

            <flux:accordion>
                <flux:accordion.item>
                    <flux:accordion.title data-item="1">Jak mogę złożyć zamówienie?</flux:accordion.title>
                    <flux:accordion.content data-item="1">
                        <p class="text-gray-600">
                            Zamówienia można składać przez nasz sklep internetowy. Wystarczy wybrać produkty,
                            dodać je do koszyka i przejść do kasy, gdzie podasz dane dostawy i wybierzesz
                            sposób płatności.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.title data-item="2">Jakie są koszty dostawy?</flux:accordion.title>
                    <flux:accordion.content data-item="2">
                        <p class="text-gray-600">
                            Koszty dostawy zależą od wybranej metody. Kurier DPD: 15 zł, Poczta Polska: 12 zł.
                            Dla zamówień powyżej 100 zł dostawa kurierem DPD jest bezpłatna.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.title data-item="3">Jak długo trwa dostawa?</flux:accordion.title>
                    <flux:accordion.content data-item="3">
                        <p class="text-gray-600">
                            Dostawa kurierem DPD trwa 1-2 dni robocze, a Pocztą Polską 2-3 dni robocze.
                            Towar wysyłamy w ciągu 1-2 dni od potwierdzenia zamówienia.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.title data-item="4">Czy mogę zwrócić towar?</flux:accordion.title>
                    <flux:accordion.content data-item="4">
                        <p class="text-gray-600">
                            Tak, masz prawo do zwrotu towaru w ciągu 14 dni od otrzymania.
                            Towar musi być w stanie nienaruszonym i w oryginalnym opakowaniu.
                        </p>
                    </flux:accordion.content>
                </flux:accordion.item>
            </flux:accordion>
        </div>
    </section>
</div>
