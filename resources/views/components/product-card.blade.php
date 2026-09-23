{{--
    Komponen: x-product-card
    Kartu produk untuk landing page (FASE 4) dan katalog (FASE 5).

    Catatan: `thumbnail` masih null dari seeder — selama berkas gambar belum
    diunggah admin (FASE 8), kartu memakai placeholder bergaya brand.

    Contoh: <x-product-card :product="$product" />
--}}
@props(['product'])

@php
    $thumbnail = $product->thumbnailUrl();
    $kategori = $product->category?->name ?? 'Produk UMKM';
    $toko = $product->umkmProfile?->business_name ?? 'Mitra UMKM';
    $satuan = $product->unit ?: 'pcs';
    $habis = $product->stock <= 0;
    // FASE 5 mengganti tautan ini menjadi route('products.show').
    $tautan = url('/katalog/'.$product->slug);
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/40 hover:shadow-md">

    <a href="{{ $tautan }}" class="relative block aspect-[4/3] overflow-hidden bg-slate-100"
       aria-label="Lihat detail {{ $product->name }}">
        @if ($thumbnail)
            <img src="{{ $thumbnail }}" alt="{{ $product->name }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <span class="flex h-full w-full flex-col items-center justify-center gap-1 bg-gradient-to-br from-brand-primary via-brand-primary to-slate-900">
                <span class="text-3xl font-black tracking-tight text-white">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                <span class="px-3 text-center text-[11px] uppercase tracking-[0.2em] text-white/70">{{ $kategori }}</span>
            </span>
        @endif

        @if ($product->hasDiscount())
            <span class="absolute left-2 top-2 rounded-full bg-brand-accent px-2 py-1 text-[11px] font-bold text-white shadow">
                &minus;{{ $product->discount_percent }}%
            </span>
        @elseif ($product->is_featured)
            <span class="absolute left-2 top-2 rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-brand-primary shadow">
                Unggulan
            </span>
        @endif

        @if ($habis)
            <span class="absolute inset-0 grid place-items-center bg-slate-900/60 text-sm font-semibold uppercase tracking-wider text-white">
                Stok habis
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-medium text-slate-500">{{ $toko }}</p>

        <h3 class="mt-1 line-clamp-2 text-sm font-semibold leading-snug text-brand-text">
            <a href="{{ $tautan }}" class="transition hover:text-brand-primary">{{ $product->name }}</a>
        </h3>

        <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 15l-5.2 2.6 1-5.8L1.5 7.7l5.9-.9z" />
                </svg>
                <span class="font-semibold text-slate-700">{{ number_format((float) $product->rating_avg, 1) }}</span>
            </span>
            <span aria-hidden="true">&middot;</span>
            <span>{{ number_format((int) $product->sold_count) }} terjual</span>
        </div>

        <div class="mt-auto pt-3">
            <div class="flex flex-wrap items-baseline gap-2">
                <span class="text-base font-bold text-brand-primary">{{ $product->price_formatted }}</span>

                @if ($product->hasDiscount())
                    <span class="text-xs text-slate-400 line-through">{{ $product->original_price_formatted }}</span>
                @endif
            </div>

            <p class="mt-1 text-[11px] text-slate-500">
                Stok {{ number_format((int) $product->stock) }} {{ $satuan }}
                &middot; {{ number_format((int) $product->weight_gram) }} gram
            </p>
        </div>
    </div>
</article>
