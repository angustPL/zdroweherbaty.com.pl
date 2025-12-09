<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('googletagmanager::head')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        use Artesaos\SEOTools\Facades\SEOMeta;
        use Artesaos\SEOTools\Facades\OpenGraph;
        use Artesaos\SEOTools\Facades\TwitterCard;
        use Artesaos\SEOTools\Facades\JsonLd;
    @endphp

    <!-- SEO Meta Tags -->
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! TwitterCard::generate() !!}
    {!! JsonLd::generate() !!}

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
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
    @include('googletagmanager::body')

    <!-- Floating Buttons (Share & Scroll to Top) - poza głównym kontenerem dla poprawnego fixed positioning -->
    <x-floating-buttons />

    @auth
        <!-- Pływająca belka administracyjna po lewej stronie -->
        <div class="fixed left-0 top-1/2 -translate-y-1/2 z-[10000]">
            <div class="bg-black text-white rounded-r-lg shadow-lg overflow-hidden">
                <div class="flex flex-col gap-0">
                    @stack('admin-bar-actions')
                    <flux:modal.trigger name="promotions-modal">
                        <flux:tooltip content="Promocje" position="right">
                            <button type="button" class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                            </button>
                        </flux:tooltip>
                    </flux:modal.trigger>
                    @if (Auth::user()->hasRole('admin'))
                        <flux:modal.trigger name="users-modal">
                            <flux:tooltip content="Użytkownicy" position="right">
                                <button type="button" class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                </button>
                            </flux:tooltip>
                        </flux:modal.trigger>
                        <flux:tooltip content="Ustawienia" position="right">
                            <a href="{{ route('settings.profile') }}"
                                class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                        </flux:tooltip>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:tooltip content="Wyloguj się" position="right">
                            <button type="submit"
                                class="p-2 hover:bg-gray-800 transition-colors block w-full cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </flux:tooltip>
                    </form>
                </div>
            </div>
        </div>
    @endauth

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
                    <flux:navbar.item href="{{ route('delivery') }}"
                        class="hover:font-bold {{ request()->routeIs('delivery') ? 'current font-bold text-xl' : '' }}">
                        Dostawa</flux:navbar.item>
                    <flux:navbar.item href="{{ route('terms') }}"
                        class="hover:font-bold {{ request()->routeIs('terms') ? 'current font-bold text-xl' : '' }}">
                        Regulamin</flux:navbar.item>
                    <flux:navbar.item href="{{ route('contact') }}"
                        class="hover:font-bold {{ request()->routeIs('contact') ? 'current font-bold text-xl' : '' }}">
                        Kontakt</flux:navbar.item>
                    {{-- <flux:navbar.item href="{{ route('cart') }}"
                        class="hover:font-bold {{ request()->routeIs('cart') ? 'current font-bold text-xl' : '' }}">
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

        <!-- Dynamic Header Banner -->
        @stack('header-banner')

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
        <footer class="bg-primary dark:bg-primary text-white py-12 font-marcellus">
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

    @auth
        <livewire:components.promotions-modal />
        <livewire:components.promotion-form-modal />
    @endauth

    @if (Auth::user()?->hasRole('admin'))
        <livewire:components.users-modal />
        <livewire:components.user-form-modal />
    @endif

    <!-- Newsletter Modal (dla wszystkich) -->
    <livewire:newsletter-modal />

    <script>
        document.addEventListener('livewire:error', (event) => {
            const detail = event.detail || {};
            const status = detail.status;

            // Typowe przypadki "page expired" / konflikt stanu Livewire
            // 419 – wygasła sesja / CSRF
            // 409 – konflikt snapshotu (np. powrót "wstecz")
            if (status === 419 || status === 409) {
                event.preventDefault();
                window.location.reload();
            }
        });
    </script>

    @livewireScripts
    @fluxScripts
    <!-- Trix Editor -->
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
</body>

</html>
