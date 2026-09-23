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

    {{-- ================= NAVBAR PUBLIK ================= --}}
    <header x-data="{ bukaMenu: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8" aria-label="Navigasi utama">

            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-primary font-bold text-white">E</span>
                <span class="text-lg font-semibold tracking-tight text-brand-primary">
                    {{ config('app.name', 'E-Wedu') }}
                </span>
            </a>

            <ul class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                <li><a href="{{ url('/') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                <li><a href="{{ url('/katalog') }}" class="transition hover:text-brand-primary">Katalog</a></li>
                <li><a href="{{ url('/umkm') }}" class="transition hover:text-brand-primary">Profil UMKM</a></li>
                <li><a href="{{ url('/artikel') }}" class="transition hover:text-brand-primary">Literasi</a></li>
                <li><a href="{{ url('/lacak') }}" class="transition hover:text-brand-primary">Lacak Pesanan</a></li>
            </ul>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ url('/keranjang') }}"
                   class="rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                    Keranjang
                </a>

                @auth
                    <a href="{{ route('dashboard') }}"
                       class="rounded-md bg-brand-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 transition hover:text-brand-primary">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded-md bg-brand-accent px-3 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                        Daftar
                    </a>
                @endauth
            </div>

            <button type="button" @click="bukaMenu = !bukaMenu"
                    class="inline-flex items-center rounded-md p-2 text-slate-600 transition hover:bg-slate-100 hover:text-brand-primary md:hidden"
                    :aria-expanded="bukaMenu.toString()" aria-controls="menu-mobile" aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </nav>

        {{-- Menu mobile --}}
        <div id="menu-mobile" x-show="bukaMenu" x-cloak class="border-t border-slate-200 bg-white md:hidden">
            <ul class="space-y-1 px-4 py-3 text-sm font-medium text-slate-700">
                <li><a href="{{ url('/') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Beranda</a></li>
                <li><a href="{{ url('/katalog') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Katalog</a></li>
                <li><a href="{{ url('/umkm') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Profil UMKM</a></li>
                <li><a href="{{ url('/artikel') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Literasi</a></li>
                <li><a href="{{ url('/lacak') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Lacak Pesanan</a></li>
                <li><a href="{{ url('/keranjang') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Keranjang</a></li>

                @auth
                    <li>
                        <a href="{{ route('dashboard') }}" class="block rounded px-2 py-2 font-semibold text-brand-primary">Dashboard</a>
                    </li>
                @else
                    <li><a href="{{ route('login') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Masuk</a></li>
                    <li><a href="{{ route('register') }}" class="block rounded px-2 py-2 font-semibold text-brand-accent">Daftar</a></li>
                @endauth
            </ul>
        </div>
    </header>

    {{-- ================= KONTEN UTAMA ================= --}}
    <main id="konten-utama" class="flex-1">
        @yield('content')
    </main>

    {{-- ================= FOOTER ================= --}}
    <footer class="mt-16 bg-brand-primary text-slate-200">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8">
            <div>
                <p class="text-lg font-semibold text-white">{{ config('app.name', 'E-Wedu') }}</p>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    Platform edu-commerce yang menghubungkan produk UMKM Magelang dengan mahasiswa dan
                    masyarakat, lengkap dengan literasi kewirausahaan.
                </p>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-accent">Jelajahi</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ url('/katalog') }}" class="transition hover:text-white">Katalog Produk</a></li>
                    <li><a href="{{ url('/umkm') }}" class="transition hover:text-white">Profil UMKM</a></li>
                    <li><a href="{{ url('/artikel') }}" class="transition hover:text-white">Artikel Literasi</a></li>
                    <li><a href="{{ url('/lacak') }}" class="transition hover:text-white">Lacak Pesanan</a></li>
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-accent">Titik Pengiriman</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-300">
                    <li>Kampus Tuguran</li>
                    <li>UMKM Center Magelang</li>
                    <li>Balai Kota Magelang</li>
                    <li>Sentra UMKM</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 py-4 text-center text-xs text-slate-300">
            &copy; {{ date('Y') }} {{ config('app.name', 'E-Wedu') }} &mdash; Praktik Kewirausahaan. Seluruh hak dilindungi.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
