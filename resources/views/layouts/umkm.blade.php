@php
    /*
     | Menu sidebar Panel UMKM (kerangka FASE 2; fungsional penuh pada fase lanjutan).
     | `href` memakai string URL agar tidak memicu RouteNotFoundException sebelum route dibuat.
     */
    $menuUmkm = [
        ['label' => 'Dashboard', 'href' => '/umkm-panel', 'pola' => 'umkm-panel'],
        ['label' => 'Produk Saya', 'href' => '/umkm-panel/produk', 'pola' => 'umkm-panel/produk*'],
        ['label' => 'Pesanan Masuk', 'href' => '/umkm-panel/pesanan', 'pola' => 'umkm-panel/pesanan*'],
        ['label' => 'Profil Usaha', 'href' => '/umkm-panel/profil', 'pola' => 'umkm-panel/profil*'],
        ['label' => 'Statistik', 'href' => '/umkm-panel/statistik', 'pola' => 'umkm-panel/statistik*'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Panel UMKM') &middot; {{ config('app.name', 'E-Wedu') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-brand-background text-brand-text antialiased" x-data="{ sidebarTerbuka: false }">

<div class="flex min-h-screen">

    <div x-show="sidebarTerbuka" x-cloak @click="sidebarTerbuka = false"
         class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" aria-hidden="true"></div>

    {{-- ================= SIDEBAR ================= --}}
    <aside x-show="true" :class="sidebarTerbuka ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 flex-col bg-brand-primary transition-transform duration-200 lg:static lg:translate-x-0">

        <div class="flex items-center gap-2 px-5 py-4">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-accent font-bold text-white">U</span>
            <div class="leading-tight">
                <p class="text-sm font-semibold text-white">{{ config('app.name', 'E-Wedu') }}</p>
                <p class="text-xs text-slate-300">Panel Mitra UMKM</p>
            </div>
        </div>

        <nav class="mt-2 flex-1 space-y-1 overflow-y-auto px-3 pb-4" aria-label="Menu UMKM">
            @foreach ($menuUmkm as $item)
                <x-sidebar-link :href="url($item['href'])" :active="request()->is($item['pola'])">
                    {{ $item['label'] }}
                </x-sidebar-link>
            @endforeach
        </nav>

        <div class="border-t border-white/10 px-5 py-4">
            <a href="{{ url('/') }}" class="text-xs font-medium text-slate-300 transition hover:text-white">
                &larr; Lihat situs publik
            </a>
        </div>
    </aside>

    {{-- ================= AREA KONTEN ================= --}}
    <div class="flex min-w-0 flex-1 flex-col">

        <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 sm:px-6 lg:px-8">
            <button type="button" @click="sidebarTerbuka = true"
                    class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <h1 class="min-w-0 flex-1 truncate text-base font-semibold text-brand-text">
                @yield('page_title', 'Dashboard UMKM')
            </h1>

            @yield('header_actions')

            @auth
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium leading-tight">{{ auth()->user()->name }}</p>
                        <span class="inline-block rounded px-1.5 py-0.5 text-[11px] font-semibold {{ auth()->user()->roleBadgeClass() }}">
                            {{ auth()->user()->roleLabel() }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-red-300 hover:text-red-600">
                            Keluar
                        </button>
                    </form>
                </div>
            @endauth
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <x-alert />
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 bg-white px-4 py-3 text-center text-xs text-slate-500 sm:px-6 lg:px-8">
            {{ config('app.name', 'E-Wedu') }} &mdash; Panel Mitra UMKM &copy; {{ date('Y') }}
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>
