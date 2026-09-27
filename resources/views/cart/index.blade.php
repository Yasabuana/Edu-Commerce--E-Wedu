@extends('layouts.public')

@section('title', 'Keranjang Belanja — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Keranjang belanja E-Wedu: cek produk UMKM Magelang pilihanmu sebelum checkout.')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                        <li aria-hidden="true">/</li>
                        <li class="font-medium text-brand-text">Keranjang</li>
                    </ol>
                </nav>

                <h1 class="mt-2 text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">Keranjang Belanja</h1>
                <p class="mt-1 text-sm text-slate-600">
                    @if ($jumlahItem > 0)
                        {{ number_format($jumlahItem) }} item siap diproses.
                    @else
                        Belum ada produk di keranjangmu.
                    @endif
                </p>
            </div>

            <a href="{{ route('products.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                Lanjut belanja
            </a>
        </div>

        @if ($cart === null || $cart->isEmpty())
            <div class="mt-6">
                <x-empty-state icon="cart" title="Keranjang masih kosong"
                               description="Yuk pilih produk lokal dari mitra UMKM Magelang — harga jelas, stok real-time, dan bisa diambil di 4 titik pengiriman.">
                    <a href="{{ route('products.index') }}"
                       class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                        Jelajahi katalog
                    </a>
                    <a href="{{ route('umkm.index') }}"
                       class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                        Lihat mitra UMKM
                    </a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    @foreach ($groups as $group)
                        @php $penjual = $group->first()->umkmProfile; @endphp

                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-8 w-8 place-items-center rounded-md bg-brand-primary/10 text-xs font-bold text-brand-primary">
                                        {{ mb_strtoupper(mb_substr($penjual?->business_name ?? 'UM', 0, 2)) }}
                                    </span>

                                    <div>
                                        @if ($penjual)
                                            <a href="{{ route('umkm.show', $penjual) }}"
                                               class="text-sm font-semibold text-brand-text transition hover:text-brand-primary">
                                                {{ $penjual->business_name }}
                                            </a>
                                        @else
                                            <span class="text-sm font-semibold text-brand-text">Mitra UMKM</span>
                                        @endif

                                        <p class="text-xs text-slate-500">
                                            {{ number_format((int) $group->sum('qty')) }} item dari penjual ini
                                        </p>
                                    </div>
                                </div>

                                @if ($penjual)
                                    <a href="{{ route('products.index', ['umkm' => $penjual->slug]) }}"
                                       class="text-xs font-semibold text-brand-primary transition hover:text-brand-accent">
                                        Lihat produk lain
                                    </a>
                                @endif
                            </header>

                            <div class="divide-y divide-slate-100">
                                @foreach ($group as $item)
                                    @include('cart.partials.item', ['item' => $item])
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                <aside class="lg:col-span-1">
                    @include('cart.partials.summary')
                </aside>
            </div>
        @endif

        @if ($rekomendasi->isNotEmpty())
            <section class="mt-12">
                <x-section-heading eyebrow="Rekomendasi" title="Produk lain yang mungkin kamu suka"
                                   subtitle="Produk terlaris mitra UMKM yang belum ada di keranjangmu."
                                   action-label="Lihat katalog"
                                   :action-href="route('products.index')" />

                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($rekomendasi as $produkRekomendasi)
                        <x-product-card :product="$produkRekomendasi" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
