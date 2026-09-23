@extends('layouts.public')

@section('title', 'Terjadi kesalahan — '.config('app.name', 'E-Wedu'))

@section('content')
    <section class="mx-auto flex max-w-3xl flex-col items-center px-4 py-16 text-center sm:px-6 lg:px-8">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-amber-50 text-brand-accent">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.34 3.94L1.72 18.06A1.75 1.75 0 003.23 20.7h17.54a1.75 1.75 0 001.51-2.64L13.66 3.94a1.75 1.75 0 00-3.32 0z" />
            </svg>
        </span>

        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.25em] text-brand-accent">Error 500</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-brand-text sm:text-4xl">
            Ada gangguan di sisi kami
        </h1>
        <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-600 sm:text-base">
            Permintaan Anda tidak dapat diproses saat ini. Silakan coba beberapa saat lagi; bila masih
            gagal, hubungi admin E-Wedu agar segera ditindaklanjuti.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('home') }}"
               class="rounded-lg bg-brand-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-900">
                Muat Ulang Beranda
            </a>

            @if ($whatsappAdmin ?? null)
                <x-whatsapp-button :number="$whatsappAdmin" label="Laporkan ke Admin"
                                   message="Halo admin E-Wedu, saya menemukan kendala saat membuka halaman." />
            @endif
        </div>
    </section>
@endsection
