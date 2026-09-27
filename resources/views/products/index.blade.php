@extends('layouts.public')

@section('title', 'Katalog Produk UMKM Magelang — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Cari produk UMKM Magelang: filter kategori, rentang harga, dan penjual terverifikasi. Harga jelas, stok real-time, gratis ongkir di 4 titik pengiriman.')

@section('content')
    <x-page-header eyebrow="Katalog produk" title="Produk UMKM Magelang"
                   subtitle="Semua produk di halaman ini berasal dari mitra UMKM terverifikasi. Pakai filter untuk menemukan kebutuhanmu lebih cepat."
                   :breadcrumbs="[
                       ['label' => 'Beranda', 'url' => route('home')],
                       ['label' => 'Katalog'],
                   ]" />

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @include('products.partials.filter')

        <p class="mt-5 text-sm text-slate-600">
            Menampilkan <span class="font-semibold text-brand-text">{{ number_format($products->total()) }}</span> produk
            @if ($filter['q'] !== '')
                untuk pencarian &ldquo;{{ $filter['q'] }}&rdquo;
            @endif
            @if ($kategoriAktif)
                pada kategori &ldquo;{{ $kategoriAktif->name }}&rdquo;
            @endif
            @if ($umkmAktif)
                dari mitra &ldquo;{{ $umkmAktif->business_name }}&rdquo;
            @endif
        </p>

        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <x-empty-state icon="search" title="Produk tidak ditemukan"
                                   description="Coba ubah kata kunci, longgarkan rentang harga, atau jelajahi seluruh produk mitra UMKM.">
                        <a href="{{ route('products.index') }}"
                           class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                            Lihat semua produk
                        </a>
                        <a href="{{ route('umkm.index') }}"
                           class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                            Jelajahi mitra UMKM
                        </a>
                    </x-empty-state>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    </div>
@endsection
