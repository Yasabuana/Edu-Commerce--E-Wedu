{{--
    Komponen: x-navbar
    Navbar publik E-Wedu (sticky, responsif, menu mobile Alpine.js).
    Dipakai oleh: layouts/public.blade.php, layouts/umkm.blade.php
--}}
<header x-data="{ bukaMenu: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8" aria-label="Navigasi utama">

        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-primary font-bold text-white">E</span>
            <span class="text-lg font-semibold tracking-tight text-brand-primary">
                {{ config('app.name', 'E-Wedu') }}
            </span>
        </a>

        <ul class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
            <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
            <li><a href="{{ url('/katalog') }}" class="transition hover:text-brand-primary">Katalog</a></li>
            <li><a href="{{ route('umkm.index') }}" class="transition hover:text-brand-primary">Profil UMKM</a></li>
            <li><a href="{{ route('articles.index') }}" class="transition hover:text-brand-primary">Literasi</a></li>
            <li><a href="{{ route('about') }}" class="transition hover:text-brand-primary">Tentang</a></li>
        </ul>

        <div class="hidden items-center gap-3 md:flex">
            <a href="{{ url('/keranjang') }}"
               class="rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                Keranjang
            </a>

            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ url('/admin') }}"
                       class="rounded-md bg-brand-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                        Panel Admin
                    </a>
                @endif
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
            <li><a href="{{ route('home') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Beranda</a></li>
            <li><a href="{{ url('/katalog') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Katalog</a></li>
            <li><a href="{{ route('umkm.index') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Profil UMKM</a></li>
            <li><a href="{{ route('articles.index') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Literasi</a></li>
            <li><a href="{{ route('about') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Tentang</a></li>
            <li><a href="{{ url('/lacak') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Lacak Pesanan</a></li>
            <li><a href="{{ url('/keranjang') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Keranjang</a></li>

            @auth
                @if (auth()->user()->isAdmin())
                    <li><a href="{{ url('/admin') }}" class="block rounded px-2 py-2 font-semibold text-brand-primary">Panel Admin</a></li>
                @endif
                <li><a href="{{ route('dashboard') }}" class="block rounded px-2 py-2 font-semibold text-brand-primary">Dashboard</a></li>
            @else
                <li><a href="{{ route('login') }}" class="block rounded px-2 py-2 hover:bg-slate-50 hover:text-brand-primary">Masuk</a></li>
                <li><a href="{{ route('register') }}" class="block rounded px-2 py-2 font-semibold text-brand-accent">Daftar</a></li>
            @endauth
        </ul>
    </div>
</header>
