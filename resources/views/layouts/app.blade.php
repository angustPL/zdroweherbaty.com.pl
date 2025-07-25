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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Marcellus+SC&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Marcellus+SC&display=swap">
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
        this.adjustPadding();
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
        <flux:header id="header-top" container
            class="fixed top-0 left-0 right-0 bg-white dark:bg-white border-b-2 border-primary dark:border-primary text-primary justify-between z-50 transition-all duration-300">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <!-- Logo z dynamicznym skalowaniem -->
            <div class="transition-all duration-300 logo-dynamic">
                <flux:image.logo variant="standard" size="md" href="{{ route('home') }}" />
            </div>
            <flux:spacer />
            <div class="text-center">
                <p
                    class="font-marcellus text-4xl font-bold text-primary -mb-2 mt-4 transition-all duration-300 slogan-dynamic">
                    Herbaty dla całej rodziny
                </p>
                <flux:navbar class="-mb-px max-lg:hidden font-marcellus text-lg -pt-0 items-end justify-center">
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
                    <flux:separator vertical variant="subtle" class="my-2" />
                </flux:navbar>
            </div>
            <flux:spacer />
            <flux:navbar class="me-4">
                <flux:navbar.item icon="shopping-cart" href="#" badge="3" label="Koszyk" />
            </flux:navbar>
        </flux:header>
        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-primary dark:bg-primary text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Company Info -->
                    <div class="col-span-1 md:col-span-2">
                        <h3 class="text-lg font-semibold mb-4">Zdrowe Herbaty</h3>
                        <p class="text-gray-300 mb-4">
                            Jesteśmy pasjonatami zdrowego stylu życia i naturalnych produktów.
                            Nasze herbaty pochodzą z najlepszych źródeł i są starannie selekcjonowane.
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-300 hover:text-white transition-colors">
                                <span class="sr-only">Facebook</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-300 hover:text-white transition-colors">
                                <span class="sr-only">Instagram</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987 6.62 0 11.987-5.367 11.987-11.987C24.014 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.708 13.744 3.708 12.447s.49-2.448 1.297-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.807.875 1.297 2.026 1.297 3.323s-.49 2.448-1.297 3.323c-.875.807-2.026 1.297-3.323 1.297z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Szybkie linki</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('home') }}"
                                    class="text-gray-300 hover:text-white transition-colors">Strona główna</a></li>
                            <li><a href="{{ route('dostawa') }}"
                                    class="text-gray-300 hover:text-white transition-colors">Dostawa</a></li>
                            <li><a href="{{ route('regulamin') }}"
                                    class="text-gray-300 hover:text-white transition-colors">Regulamin</a></li>
                            <li><a href="{{ route('kontakt') }}"
                                    class="text-gray-300 hover:text-white transition-colors">Kontakt</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Kontakt</h3>
                        <ul class="space-y-2 text-gray-300">
                            <li>ul. Przykładowa 1</li>
                            <li>00-000 Warszawa</li>
                            <li>+48 123 456 789</li>
                            <li>info@zdroweherbaty.com.pl</li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                    <p>&copy; {{ date('Y') }} Zdrowe Herbaty. Wszystkie prawa zastrzeżone.</p>
                </div>
            </div>
        </footer>
    </div>
    @livewireScripts


</body>

</html>
