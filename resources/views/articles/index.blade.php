@extends('layouts.public')

@section('title', 'Artikel Literasi UMKM — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Kumpulan panduan praktis pemasaran digital, pembukuan, pengemasan, dan cerita mitra UMKM Magelang untuk pelaku usaha kecil.')

@section('content')
    <x-page-header eyebrow="Literasi UMKM" title="Artikel Literasi &amp; Cerita Mitra"
                   subtitle="Panduan praktis dan pengalaman nyata mitra UMKM Magelang — bebas dibaca siapa saja, tanpa biaya."
                   :breadcrumbs="[
                       ['label' => 'Beranda', 'url' => route('home')],
                       ['label' => 'Literasi'],
                   ]" />

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @include('articles.partials.filter')

                <p class="mt-5 text-sm text-slate-600">
                    Menampilkan <span class="font-semibold text-brand-text">{{ $articles->total() }}</span> artikel
                    @if ($kataKunci !== '')
                        untuk pencarian &ldquo;{{ $kataKunci }}&rdquo;
                    @endif
                </p>

                <div class="mt-5 grid gap-6 sm:grid-cols-2">
                    @forelse ($articles as $article)
                        <x-article-card :article="$article" />
                    @empty
                        <div class="sm:col-span-2">
                            <x-empty-state icon="search" title="Artikel tidak ditemukan"
                                           description="Coba kata kunci lain atau jelajahi seluruh kategori literasi.">
                                <a href="{{ route('articles.index') }}"
                                   class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                                    Lihat semua artikel
                                </a>
                            </x-empty-state>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">{{ $articles->links() }}</div>
            </div>

            @include('articles.partials.sidebar')
        </div>
    </div>
@endsection
