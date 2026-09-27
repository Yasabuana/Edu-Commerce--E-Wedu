{{--
    Partial: cart.partials.item
    Satu baris produk di keranjang: thumbnail, harga snapshot, ubah qty, dan hapus.
--}}
@php
    $produk = $item->product;
    $satuan = $produk?->unit ?: 'pcs';
    $gambar = $produk?->thumbnailUrl();
    $stok = (int) ($produk?->stock ?? 0);
@endphp

<article class="flex flex-wrap items-start gap-4 p-4 sm:flex-nowrap">
    @if ($produk)
        <a href="{{ route('products.show', $produk) }}"
           class="block h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
            @if ($gambar)
                <img src="{{ $gambar }}" alt="{{ $produk->name }}" class="h-full w-full object-cover" loading="lazy">
            @else
                <span class="grid h-full w-full place-items-center bg-gradient-to-br from-brand-primary to-slate-900 text-xl font-black text-white">
                    {{ mb_strtoupper(mb_substr($produk->name, 0, 1)) }}
                </span>
            @endif
        </a>
    @else
        <span class="grid h-20 w-20 shrink-0 place-items-center rounded-lg bg-slate-200 text-xl font-black text-slate-400">
            ?
        </span>
    @endif

    <div class="min-w-0 flex-1">
        <h3 class="text-sm font-semibold leading-snug text-brand-text">
            @if ($produk)
                <a href="{{ route('products.show', $produk) }}" class="transition hover:text-brand-primary">
                    {{ $produk->name }}
                </a>
            @else
                Produk tidak lagi tersedia
            @endif
        </h3>

        <p class="mt-1 text-xs text-slate-500">
            {{ $produk?->category?->name ?? 'Produk UMKM' }}
            &middot; Rp {{ number_format((int) $item->price_snapshot, 0, ',', '.') }} / {{ $satuan }}
        </p>

        @if ($produk === null)
            <p class="mt-2 text-xs font-semibold text-red-600">
                Produk ini sudah dihapus penjual. Hapus dari keranjang untuk melanjutkan.
            </p>
        @elseif (! $item->stockIsEnough())
            <p class="mt-2 text-xs font-semibold text-amber-600">
                Stok tersisa {{ number_format($stok) }} {{ $satuan }} &mdash; kurangi jumlahnya atau hapus produk ini.
            </p>
        @else
            <p class="mt-2 text-xs text-slate-500">Stok tersedia {{ number_format($stok) }} {{ $satuan }}</p>
        @endif

        <div class="mt-3 flex flex-wrap items-center gap-3">
            @if ($produk)
                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    @method('PATCH')

                    <x-qty-stepper :value="$item->qty" :max="max(1, $stok)" auto-submit />

                    <button type="submit"
                            class="text-xs font-semibold text-brand-primary transition hover:text-brand-accent">
                        Perbarui
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('cart.destroy', $item) }}">
                @csrf
                @method('DELETE')

                <button type="submit" class="text-xs font-semibold text-red-600 transition hover:text-red-700">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="text-right sm:min-w-[7rem]">
        <p class="text-sm font-bold text-brand-primary">Rp {{ number_format((int) $item->subtotal, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-500">
            {{ number_format((int) $item->qty) }} &times; Rp {{ number_format((int) $item->price_snapshot, 0, ',', '.') }}
        </p>
    </div>
</article>
