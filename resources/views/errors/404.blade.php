@extends('layouts.public')

@section('title', 'Halaman tidak ditemukan — '.config('app.name', 'E-Wedu'))

@section('content')
    <section class="mx-auto flex max-w-3xl flex-col items-center px-4 py-16 text-center sm:px-6 lg:px-8">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-brand-primary/10 text-brand-primary">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
        </span>

        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.25em] text-brand-accent">Error 404</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-brand-text sm:text-4xl">
            Halaman tidak ditemukan
        </h1>
        <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-600 sm:text-base">
            Tautan yang Anda buka sudah dipindahkan, dihapus, atau memang belum tersedia. Produk dan
            profil mitra hanya tampil setelah diverifikasi admin, sedangkan artikel hanya tampil
            setelah terbit.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('home') }}"
               class="rounded-lg bg-brand-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-900">
                Kembali ke Beranda
            </a>
            <a href="{{ route('products.index') }}"
               class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                Jelajahi Katalog
            </a>
            <a href="{{ route('articles.index') }}"
               class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                Baca Literasi UMKM
            </a>
        </div>

        <div class="mt-10 grid w-full gap-4 sm:grid-cols-2">
            <a href="{{ route('umkm.index') }}"
               class="rounded-xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-primary/40 hover:shadow-md">
                <p class="text-sm font-semibold text-brand-text">Direktori Mitra UMKM</p>
                <p class="mt-1 text-sm text-slate-600">Temukan usaha lokal terverifikasi di Magelang.</p>
            </a>

            <a href="{{ route('about') }}"
               class="rounded-xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-primary/40 hover:shadow-md">
                <p class="text-sm font-semibold text-brand-text">Tentang E-Wedu</p>
                <p class="mt-1 text-sm text-slate-600">Alur pemesanan, tarif kirim, dan titik gratis ongkir.</p>
            </a>
        </div>
    </section>
@endsection
