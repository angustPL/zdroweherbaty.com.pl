@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            @if ($alreadyUnsubscribed)
                <flux:callout variant="warning" icon="information-circle">
                    <flux:callout.heading>Jesteś już wypisany z newslettera</flux:callout.heading>
                    <flux:callout.text>
                        Ten adres email został już wcześniej usunięty z listy subskrybentów newslettera ZdroweHerbaty.com.pl
                    </flux:callout.text>
                </flux:callout>
            @else
                <flux:callout variant="success" icon="check-circle">
                    <flux:callout.heading>Pomyślnie wypisano z newslettera</flux:callout.heading>
                    <flux:callout.text>
                        Zostałeś pomyślnie wypisany z newslettera ZdroweHerbaty.com.pl. Nie będziesz już otrzymywać od nas
                        informacji o nowościach i promocjach.
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="mt-8 text-center">
                <flux:heading size="lg" class="mb-4">Czy zmieniłeś zdanie?</flux:heading>
                <p class="text-gray-600 mb-6">
                    Możesz zapisać się na newsletter ponownie podczas wizyty na naszej stronie.
                </p>

                <flux:button href="/" variant="primary">
                    Wróć do sklepu
                </flux:button>
            </div>

            <div class="mt-12 text-center text-sm text-gray-500">
                <p>ZdroweHerbaty.com.pl - Twoje źródło zdrowych herbat</p>
                <p class="mt-2">
                    Jeśli masz pytania, skontaktuj się z nami:
                    <a href="mailto:{{ config('mail.from.address') }}" class="text-blue-600 hover:text-blue-800">
                        {{ config('mail.from.address') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
