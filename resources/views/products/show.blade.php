@extends('layouts.public')

@section('title', $product->name.' — '.config('app.name', 'E-Wedu'))
@section('meta_description', \Illuminate\Support\Str::limit(trim(strip_tags((string) $product->description)), 150) ?: 'Produk UMKM Magelang di katalog E-Wedu.')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('products.index') }}" class="transition hover:text-brand-primary">Katalog</a></li>
                @if ($product->category)
                    <li aria-hidden="true">/</li>
                    <li>
                        <a href="{{ route('products.index', ['kategori' => $product->category->slug]) }}"
                           class="transition hover:text-brand-primary">{{ $product->category->name }}</a>
                    </li>
                @endif
                <li aria-hidden="true">/</li>
                <li class="max-w-[16rem] truncate font-medium text-brand-text">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-5">
            <div class="lg:col-span-3">
                @include('products.partials.gallery')
            </div>

            <div class="lg:col-span-2">
                @include('products.partials.buy-box')
            </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-3">
            <section class="lg:col-span-2">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-brand-text">Deskripsi produk</h2>

                    <div class="mt-3 text-sm leading-relaxed text-slate-600">
                        @if (trim((string) $product->description) !== '')
                            {!! nl2br(e($product->description)) !!}
                        @else
                            <p>
                                Penjual belum menambahkan deskripsi rinci untuk produk ini. Silakan hubungi penjual
                                lewat WhatsApp untuk menanyakan bahan, ukuran, atau ketersediaan stok.
                            </p>
                        @endif
                    </div>

                    <dl class="mt-6 grid gap-3 border-t border-slate-100 pt-5 text-sm sm:grid-cols-2">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">SKU</dt>
                            <dd class="font-medium text-brand-text">{{ $product->sku ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Kategori</dt>
                            <dd class="font-medium text-brand-text">{{ $product->category->name ?? 'Tanpa kategori' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Satuan</dt>
                            <dd class="font-medium text-brand-text">{{ $product->unit ?: 'pcs' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Berat</dt>
                            <dd class="font-medium text-brand-text">{{ number_format((int) $product->weight_gram) }} gram</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Stok</dt>
                            <dd class="font-medium text-brand-text">
                                {{ number_format((int) $product->stock) }} {{ $product->unit ?: 'pcs' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Terjual</dt>
                            <dd class="font-medium text-brand-text">{{ number_format((int) $product->sold_count) }} unit</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <aside class="lg:col-span-1">
                @include('products.partials.seller')
            </aside>
        </div>
    </div>

    @include('products.partials.related')
@endsection
