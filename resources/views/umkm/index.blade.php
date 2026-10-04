@extends('layouts.public')

@section('title', 'Direktori UMKM Magelang — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Direktori mitra UMKM terverifikasi E-Wedu di Kota & Kabupaten Magelang: profil usaha, produk lokal, dan kontak langsung ke pemiliknya.')

@section('content')
    <x-page-header eyebrow="Direktori mitra" title="Mitra UMKM Magelang"
                   subtitle="Semua profil di halaman ini sudah diverifikasi admin E-Wedu — data pemilik, alamat usaha, dan produknya jelas."
                   :breadcrumbs="[
                       ['label' => 'Beranda', 'url' => route('home')],
                       ['label' => 'Mitra UMKM'],
                   ]">
        <a href="{{ route('umkm.register') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-accent px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Daftar Mitra UMKM
        </a>
    </x-page-header>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @include('umkm.partials.filter')

        <p class="mt-5 text-sm text-slate-600">
            Menampilkan <span class="font-semibold text-brand-text">{{ $profiles->total() }}</span> mitra UMKM
            @if ($kataKunci !== '')
                untuk pencarian &ldquo;{{ $kataKunci }}&rdquo;
            @endif
            @if ($kategori !== '')
                pada kategori &ldquo;{{ $kategori }}&rdquo;
            @endif
        </p>

        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($profiles as $profile)
                <x-umkm-card :umkm="$profile" />
            @empty
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-empty-state icon="search" title="Mitra tidak ditemukan"
                                   description="Coba kata kunci lain, atau lihat seluruh mitra terverifikasi.">
                        <a href="{{ route('umkm.index') }}"
                           class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                            Lihat semua mitra
                        </a>
                    </x-empty-state>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $profiles->links() }}</div>
    </div>

    {{-- Ajakan bergabung sebagai mitra --}}
    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-brand-primary to-slate-900 px-6 py-10 text-white sm:px-10">
            <div class="flex flex-wrap items-center justify-between gap-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Gabung sebagai mitra</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Usahamu belum terdaftar?</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-200">
                        Daftarkan usaha lokalmu, lengkapi profil dan titik lokasi, lalu tunggu verifikasi admin
                        sebelum produk tayang di katalog E-Wedu.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('register') }}"
                       class="rounded-lg bg-brand-accent px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-600">
                        Daftar Mitra UMKM
                    </a>
                    <a href="{{ route('articles.index') }}"
                       class="rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold transition hover:border-white hover:bg-white/10">
                        Baca Literasi UMKM
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
