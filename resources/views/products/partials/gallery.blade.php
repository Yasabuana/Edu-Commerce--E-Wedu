{{--
    Partial: products.partials.gallery
    Galeri foto produk: satu foto besar + thumbnail pemilih (Alpine.js).
--}}
@php
    $galeri = $product->galleryUrls();
    $jumlahGaleri = count($galeri);
@endphp

<div x-data="{ aktif: 0 }" class="space-y-3">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @if ($jumlahGaleri === 0)
            <div class="flex aspect-[4/3] w-full flex-col items-center justify-center gap-2 bg-gradient-to-br from-brand-primary via-brand-primary to-slate-900">
                <span class="text-5xl font-black tracking-tight text-white">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                <span class="px-3 text-center text-[11px] uppercase tracking-[0.2em] text-white/70">
                    {{ $product->category->name ?? 'Produk UMKM' }}
                </span>
            </div>
        @else
            @foreach ($galeri as $index => $url)
                <img src="{{ $url }}" alt="Foto {{ $index + 1 }} produk {{ $product->name }}"
                     x-show="aktif === {{ $index }}" {{ $index > 0 ? 'x-cloak' : '' }}
                     class="aspect-[4/3] w-full object-cover"
                     loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
            @endforeach
        @endif
    </div>

    @if ($jumlahGaleri > 1)
        <div class="flex gap-3 overflow-x-auto pb-1">
            @foreach ($galeri as $index => $url)
                <button type="button" @click="aktif = {{ $index }}"
                        :class="aktif === {{ $index }}
                            ? 'border-brand-primary ring-2 ring-brand-primary/40'
                            : 'border-slate-200'"
                        class="h-16 w-20 shrink-0 overflow-hidden rounded-lg border bg-slate-100 transition"
                        aria-label="Tampilkan foto {{ $index + 1 }}">
                    <img src="{{ $url }}" alt="" class="h-full w-full object-cover">
                </button>
            @endforeach
        </div>
    @endif

    @if ($product->is_featured)
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-accent">Produk unggulan pilihan kurator</p>
    @endif
</div>
