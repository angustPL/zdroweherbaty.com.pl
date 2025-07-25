<?php

use function Livewire\Volt\{state, mount, layout};

layout('layouts.app');

state(['expandedSections' => []]);

$toggleSection = function ($section) {
    if (in_array($section, $this->expandedSections)) {
        $this->expandedSections = array_diff($this->expandedSections, [$section]);
    } else {
        $this->expandedSections[] = $section;
    }
};

?>

<div>
    <!-- Hero Section -->
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Regulamin
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Regulamin sklepu internetowego Zdrowe Herbaty
                </p>
            </div>
        </div>
    </section>

    <!-- Terms Section -->
    <section class="bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                wire:click="toggleSection('section1')"
                :class="{ 'ring-2 ring-primary': in_array('section1', $expandedSections) }">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">§1. Postanowienia ogólne</h3>
                        <span class="text-sm text-gray-500">
                            {{ in_array('section1', $expandedSections) ? 'Kliknij aby ukryć' : 'Kliknij aby rozwinąć' }}
                        </span>
                    </div>
                    @if (in_array('section1', $expandedSections))
                        <div class="mt-4 space-y-4 text-gray-600">
                            <p>
                                1.1. Niniejszy regulamin określa zasady korzystania ze sklepu internetowego
                                "Zdrowe Herbaty" dostępnego pod adresem www.zdroweherbaty.com.pl
                            </p>
                            <p>
                                1.2. Właścicielem sklepu jest firma Zdrowe Herbaty z siedzibą w Warszawie.
                            </p>
                            <p>
                                1.3. Sklep oferuje sprzedaż herbat i produktów herbacianych.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                wire:click="toggleSection('section2')"
                :class="{ 'ring-2 ring-primary': in_array('section2', $expandedSections) }">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">§2. Zamówienia</h3>
                        <span class="text-sm text-gray-500">
                            {{ in_array('section2', $expandedSections) ? 'Kliknij aby ukryć' : 'Kliknij aby rozwinąć' }}
                        </span>
                    </div>
                    @if (in_array('section2', $expandedSections))
                        <div class="mt-4 space-y-4 text-gray-600">
                            <p>
                                2.1. Zamówienia można składać przez formularz dostępny na stronie sklepu.
                            </p>
                            <p>
                                2.2. Każde zamówienie wymaga potwierdzenia przez sklep.
                            </p>
                            <p>
                                2.3. Ceny produktów podane na stronie są cenami brutto i zawierają podatek VAT.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                wire:click="toggleSection('section3')"
                :class="{ 'ring-2 ring-primary': in_array('section3', $expandedSections) }">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">§3. Płatności</h3>
                        <span class="text-sm text-gray-500">
                            {{ in_array('section3', $expandedSections) ? 'Kliknij aby ukryć' : 'Kliknij aby rozwinąć' }}
                        </span>
                    </div>
                    @if (in_array('section3', $expandedSections))
                        <div class="mt-4 space-y-4 text-gray-600">
                            <p>
                                3.1. Akceptujemy płatności online przez systemy płatności elektronicznych.
                            </p>
                            <p>
                                3.2. Płatność za zamówienie następuje przed wysłaniem towaru.
                            </p>
                            <p>
                                3.3. Faktura VAT zostanie wystawiona po dokonaniu płatności.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                wire:click="toggleSection('section4')"
                :class="{ 'ring-2 ring-primary': in_array('section4', $expandedSections) }">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">§4. Dostawa</h3>
                        <span class="text-sm text-gray-500">
                            {{ in_array('section4', $expandedSections) ? 'Kliknij aby ukryć' : 'Kliknij aby rozwinąć' }}
                        </span>
                    </div>
                    @if (in_array('section4', $expandedSections))
                        <div class="mt-4 space-y-4 text-gray-600">
                            <p>
                                4.1. Towar wysyłamy w ciągu 1-2 dni roboczych od potwierdzenia zamówienia.
                            </p>
                            <p>
                                4.2. Koszty dostawy są określone w zakładce "Dostawa".
                            </p>
                            <p>
                                4.3. Dostawa na terenie całej Polski.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                wire:click="toggleSection('section5')"
                :class="{ 'ring-2 ring-primary': in_array('section5', $expandedSections) }">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">§5. Zwroty i reklamacje</h3>
                        <span class="text-sm text-gray-500">
                            {{ in_array('section5', $expandedSections) ? 'Kliknij aby ukryć' : 'Kliknij aby rozwinąć' }}
                        </span>
                    </div>
                    @if (in_array('section5', $expandedSections))
                        <div class="mt-4 space-y-4 text-gray-600">
                            <p>
                                5.1. Klient ma prawo do zwrotu towaru w ciągu 14 dni od otrzymania.
                            </p>
                            <p>
                                5.2. Towar zwracany musi być w stanie nienaruszonym.
                            </p>
                            <p>
                                5.3. Reklamacje rozpatrujemy w ciągu 14 dni od otrzymania.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                wire:click="toggleSection('section6')"
                :class="{ 'ring-2 ring-primary': in_array('section6', $expandedSections) }">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">§6. Ochrona danych osobowych</h3>
                        <span class="text-sm text-gray-500">
                            {{ in_array('section6', $expandedSections) ? 'Kliknij aby ukryć' : 'Kliknij aby rozwinąć' }}
                        </span>
                    </div>
                    @if (in_array('section6', $expandedSections))
                        <div class="mt-4 space-y-4 text-gray-600">
                            <p>
                                6.1. Dane osobowe klientów są chronione zgodnie z RODO.
                            </p>
                            <p>
                                6.2. Dane są wykorzystywane wyłącznie do realizacji zamówień.
                            </p>
                            <p>
                                6.3. Klient ma prawo do wglądu, poprawiania i usuwania swoich danych.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Expand All Button -->
            <div class="mt-8 text-center">
                <flux:button
                    wire:click="$set('expandedSections', ['section1', 'section2', 'section3', 'section4', 'section5', 'section6'])"
                    variant="outline">
                    Rozwiń wszystkie sekcje
                </flux:button>
                <flux:button wire:click="$set('expandedSections', [])" variant="outline" class="ml-2">
                    Zwiń wszystkie sekcje
                </flux:button>
            </div>
        </div>
    </section>
</div>
