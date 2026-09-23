<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'E-Wedu'))</title>
    <meta name="description" content="@yield('meta_description', 'E-Wedu — edu-commerce UMKM Magelang: katalog produk lokal, literasi UMKM, dan pengiriman logistik mandiri.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-brand-background text-brand-text antialiased min-h-screen flex flex-col">

    <a href="#konten-utama"
       class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50 focus:rounded focus:bg-brand-primary focus:px-4 focus:py-2 focus:text-white">
        Lewati ke konten utama
    </a>

    {{-- ================= NAVBAR PUBLIK (komponen) ================= --}}
    <x-navbar />

    {{-- ================= KONTEN UTAMA ================= --}}
    <main id="konten-utama" class="flex-1">
        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
            {{-- Flash session (status/success/error/...) & error validasi tampil otomatis --}}
            <x-alert />
        </div>

        @yield('content')
    </main>

    {{-- ================= FOOTER (komponen) ================= --}}
    <x-footer />

    @stack('scripts')
</body>
</html>
