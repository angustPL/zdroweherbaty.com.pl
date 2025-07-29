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
        {{ $slot }}
    </div>
    @livewireScripts
    @fluxScripts
</body>

</html>
