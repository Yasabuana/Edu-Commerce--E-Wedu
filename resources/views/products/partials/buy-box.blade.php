{{--
    Partial: products.partials.buy-box
    Harga, info stok, form "Tambah ke keranjang", dan kontak penjual.
--}}
@php
    $satuan = $product->unit ?: 'pcs';
    $umkm = $product->umkmProfile;
    $stokTersedia = (int) $product->stock;
@endphp

<div class="space-y-5">
    <div>
        @if ($product->hasDiscount())
            <span class="inline-flex items-center rounded-full bg-brand-accent px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white">
                Diskon {{ $product->discount_percent }}%
            </span>
        @elseif ($product->is_featured)
            <span class="inline-flex items-center rounded-full bg-brand-primary/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-primary">
                Unggulan
            </span>
        @endif

        <h1 class="mt-3 text-2xl font-bold leading-snug tracking-tight text-brand-text sm:text-3xl">{{ $product->name }}</h1>

        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-500">
            <span class="inline-flex items-center gap-1">
                <svg class="h-4 w-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 15l-5.2 2.6 1-5.8L1.5 7.7l5.9-.9z" />
                </svg>
                <span class="font-semibold text-slate-700">{{ number_format((float) $product->rating_avg, 1) }}</span>
            </span>
            <span aria-hidden="true">&middot;</span>
            <span>{{ number_format((int) $product->sold_count) }} terjual</span>
            <span aria-hidden="true">&middot;</span>
            <span>{{ number_format((int) $product->views) }} kali dilihat</span>
        </div>

        @if ($umkm)
            <p class="mt-3 text-sm text-slate-600">
                Dijual oleh
                <a href="{{ route('umkm.show', $umkm) }}"
                   class="font-semibold text-brand-primary transition hover:text-brand-accent">{{ $umkm->business_name }}</a>

                @if ($umkm->isVerified())
                    <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700">
                        Terverifikasi
                    </span>
                @endif
            </p>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-3xl font-black tracking-tight text-brand-primary">{{ $product->price_formatted }}</p>

        @if ($product->hasDiscount())
            <p class="mt-1 text-sm text-slate-400">
                Harga normal <span class="line-through">{{ $product->original_price_formatted }}</span>
            </p>
        @endif

        <p class="mt-3 text-sm {{ $stokTersedia > 0 ? 'text-slate-600' : 'font-semibold text-red-600' }}">
            @if ($stokTersedia > 0)
                Stok tersedia {{ number_format($stokTersedia) }} {{ $satuan }}
            @else
                Stok sedang habis
            @endif
            &middot; {{ number_format((int) $product->weight_gram) }} gram/{{ $satuan }}
        </p>

        @if ($stokTersedia > 0)
            <form method="POST" action="{{ route('cart.store') }}" class="mt-5 space-y-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-slate-600">Jumlah</span>
                    <x-qty-stepper :value="1" :max="$stokTersedia" />
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-brand-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-900">
                    Tambah ke keranjang
                </button>
            </form>
        @else
            <div class="mt-5 rounded-lg bg-slate-100 px-4 py-3 text-sm text-slate-600">
                Produk ini belum bisa dipesan. Cek lagi nanti atau lihat produk lain dari penjual ini.
            </div>
        @endif

        <p class="mt-3 text-xs leading-relaxed text-slate-500">
            Gratis ongkir untuk pengambilan di 4 titik pengiriman E-Wedu; alamat lain dihitung otomatis saat checkout.
        </p>
    </div>

    @if ($umkm && $umkm->whatsappUrl())
        <x-whatsapp-button :number="$umkm->whatsapp ?: $umkm->phone" variant="outline"
                           label="Tanya penjual via WhatsApp" class="w-full justify-center"
                           :message="'Halo '.$umkm->business_name.', saya ingin bertanya tentang produk '.$product->name.' di E-Wedu.'" />
    @endif
</div>
