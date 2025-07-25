<?php

use function Livewire\Volt\{state, mount, layout};

layout('layouts.app');

state(['search' => '']);

?>

<div>
    <!-- Hero Section / Jumbotron -->
    <section class="jumbotron" id="jumbotron">
        <img src="{{ asset('img/bifix-hp-bg.jpg') }}" alt="Zdrowe Herbaty - Naturalne herbaty dla całej rodziny"
            class="w-full h-auto max-w-full">
    </section>

    <!-- About Section -->
    <section id="o-nas" class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    O nas
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Jesteśmy pasjonatami zdrowego stylu życia i naturalnych produktów.
                    Nasze herbaty pochodzą z najlepszych źródeł i są starannie selekcjonowane.
                </p>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="produkty" class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Nasze produkty
                </h2>
                <p class="text-lg text-gray-600">
                    Poznaj naszą ofertę naturalnych herbat
                </p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Product Card 1 -->
                <flux:card>
                    <flux:card.header>
                        <flux:card.title>Herbata zielona</flux:card.title>
                        <flux:card.description>Naturalna zielona herbata o delikatnym smaku</flux:card.description>
                    </flux:card.header>
                    <flux:card.content>
                        <div class="h-48 bg-gray-200 mb-4"></div>
                        <flux:button href="#" variant="ghost" class="text-primary hover:text-primary/80">
                            Dowiedz się więcej →
                        </flux:button>
                    </flux:card.content>
                </flux:card>

                <!-- Product Card 2 -->
                <flux:card>
                    <flux:card.header>
                        <flux:card.title>Herbata czarna</flux:card.title>
                        <flux:card.description>Klasyczna czarna herbata o intensywnym smaku</flux:card.description>
                    </flux:card.header>
                    <flux:card.content>
                        <div class="h-48 bg-gray-200 mb-4"></div>
                        <flux:button href="#" variant="ghost" class="text-primary hover:text-primary/80">
                            Dowiedz się więcej →
                        </flux:button>
                    </flux:card.content>
                </flux:card>

                <!-- Product Card 3 -->
                <flux:card>
                    <flux:card.header>
                        <flux:card.title>Herbata ziołowa</flux:card.title>
                        <flux:card.description>Mieszanka ziół o właściwościach prozdrowotnych</flux:card.description>
                    </flux:card.header>
                    <flux:card.content>
                        <div class="h-48 bg-gray-200 mb-4"></div>
                        <flux:button href="#" variant="ghost" class="text-primary hover:text-primary/80">
                            Dowiedz się więcej →
                        </flux:button>
                    </flux:card.content>
                </flux:card>
            </div>
        </div>
    </section>
</div>
