<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Zdrowe Herbaty') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased" x-data="{
    scrolled: false,
    adjustPadding() {
        const header = document.getElementById('header-top');
        if (header) {
            const headerHeight = header.offsetHeight;
            document.body.style.paddingTop = headerHeight + 'px';
        }
    },
    handleScroll() {
        this.scrolled = window.scrollY > 50;
        // Dodaj/usuń klasę CSS na body
        if (this.scrolled) {
            document.body.classList.add('scrolled');
        } else {
            document.body.classList.remove('scrolled');
        }
    }
}" x-init="adjustPadding()" @scroll.window="handleScroll()"
    @resize.window="adjustPadding()">
    <div class="min-h-screen bg-white">
        <!-- Floating Header -->
        <flux:header container id="header-top"
            class="fixed top-0 left-0 right-0 bg-white dark:bg-white border-b-2 border-primary dark:border-primary text-primary justify-between z-20 transition-all duration-300 header-top">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />
            <flux:spacer class="lg:hidden" />
            <!-- Logo z dynamicznym skalowaniem -->
            <div class="transition-all duration-300 max-lg:my-2 logo">
                <flux:image.logo variant="standard" href="{{ route('home') }}" />
            </div>
            <flux:spacer />
            <div class="text-center">
                <p
                    class="max-lg:hidden font-marcellus text-4xl font-bold text-primary -mb-2 mt-4 transition-all duration-300 slogan">
                    Herbaty dla całej rodziny
                </p>
                <flux:navbar
                    class="-mb-px max-lg:hidden font-marcellus text-lg -pt-0 items-end justify-between gap-x-2 menu">
                    <flux:navbar.item href="{{ route('home') }}"
                        class="hover:font-bold {{ request()->routeIs('home') ? 'current font-bold text-xl' : '' }}">
                        Strona główna</flux:navbar.item>
                    <flux:navbar.item href="{{ route('dostawa') }}"
                        class="hover:font-bold {{ request()->routeIs('dostawa') ? 'current font-bold text-xl' : '' }}">
                        Dostawa</flux:navbar.item>
                    <flux:navbar.item href="{{ route('regulamin') }}"
                        class="hover:font-bold {{ request()->routeIs('regulamin') ? 'current font-bold text-xl' : '' }}">
                        Regulamin</flux:navbar.item>
                    <flux:navbar.item href="{{ route('kontakt') }}"
                        class="hover:font-bold {{ request()->routeIs('kontakt') ? 'current font-bold text-xl' : '' }}">
                        Kontakt</flux:navbar.item>
                    {{-- <flux:navbar.item href="{{ route('koszyk') }}"
                        class="hover:font-bold {{ request()->routeIs('koszyk') ? 'current font-bold text-xl' : '' }}">
                        Koszyk</flux:navbar.item> --}}
                    <flux:separator vertical variant="subtle" class="my-2" />
                </flux:navbar>
            </div>
            <flux:spacer />
            <flux:navbar>
                <livewire:components.cart-icon />
            </flux:navbar>
        </flux:header>

        <!-- Mobile Sidebar -->
        <livewire:components.sidebar-with-groups />

        <!-- Main Content -->
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Sidebar z grupami produktów (2/6) - ukryty na mobile -->
                    <div class="max-lg:hidden lg:col-span-1">
                        <livewire:components.desktop-sidebar />
                    </div>

                    <!-- Content (3/4 na desktop, pełna szerokość na mobile) -->
                    <div class="lg:col-span-3">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-primary dark:bg-primary text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mx-6">
                    <!-- Company Info -->
                    <div>
                        <p>
                            BiFIX Wojciech Piasecki Sp.j.<br>
                            Górki Małe<br>
                            ul. Dworska 33<br>
                            95-080 Tuszyn<br>
                            fax 42 614-41-20<br>
                            <a href="mailto:bifix@bifix.pl">bifix@bifix.pl</a>
                        </p>
                    </div>

                    <div class="lg:col-span-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <p>
                                    DZIAŁ PRZYJĘĆ ZAMÓWIEŃ<br>
                                    tel. <a href="tel:+48426144058w123">
                                        42 614-40-88</a> wew. 123<br>
                                    <a href="mailto:logistyka@bifix.pl">logistyka@bifix.pl</a>
                                </p>
                            </div>
                            <div>
                                <p>
                                    DZIAŁ HANDLOWY<br>
                                    tel. <a href="tel:+48426144058w122">42 614-40-58</a> wew. 122, 125, 126<br>
                                    <a href="mailto:handel@bifix.pl">handel@bifix.pl</a>
                                </p>
                            </div>
                            <div>
                                <p>
                                    HANDEL MIĘDZYNARODOWY<br>
                                    tel. <a href="tel:+48426144058w142">42 614-40-58</a> wew. 142<br>
                                    <a href="mailto:export@bifix.pl">export@bifix.pl</a>
                                </p>
                            </div>
                            <div>
                                <p>
                                    DZIAŁ MARKETINGU<br>
                                    tel. <a href="tel:+48426144058w127">42 614-40-58</a> wew. 127<br>
                                    <a href="mailto:marketing@bifix.pl">marketing@bifix.pl</a>
                                </p>
                            </div>
                            <div>
                                <p>
                                    KSIĘGOWOŚĆ<br>
                                    tel. <a href="tel:+48426144058w129">42 614-40-58</a> wew. 129, 148<br>
                                    <a href="mailto:ksiegowosc@bifix.pl">ksiegowosc@bifix.pl</a>
                                </p>
                            </div>
                            <div>
                                <p>
                                    <a title="Polityka prywatności" href="/polityka-prywatnosci">Polityka
                                        prywatności</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white mt-8 pt-8 text-center text-gray-300">
                    <p>&copy; {{ date('Y') }} Bifix Wszystkie prawa zastrzeżone.</p>
                </div>
            </div>
        </footer>
    </div>
    @livewireScripts
    @fluxScripts
</body>

</html>
