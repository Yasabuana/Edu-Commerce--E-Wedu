@php
    // Sorotan produk: slider ringan Alpine.js (auto-putar 6 detik, tanpa library tambahan).
    $sorotan = $featuredProducts->take(3);
@endphp

<div class="w-full max-w-md lg:justify-self-end"
     x-data="{ aktif: 0, total: {{ max($sorotan->count(), 1) }} }"
     x-init="setInterval(() => aktif = (aktif + 1) % total, 6000)">
    @if ($sorotan->isNotEmpty())
        <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Sorotan Produk</p>

            @foreach ($sorotan as $index => $produk)
                <article x-show="aktif === {{ $index }}"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-cloak class="mt-4">
                    <div class="flex items-start gap-4">
                        <span class="grid h-16 w-16 shrink-0 place-items-center rounded-xl bg-white/15 text-xl font-black">
                            {{ mb_strtoupper(mb_substr($produk->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-300">{{ $produk->umkmProfile?->business_name ?? 'Mitra UMKM' }}</p>
                            <h2 class="mt-1 text-base font-semibold leading-snug">{{ $produk->name }}</h2>
                            <p class="mt-2 text-lg font-bold text-brand-accent">{{ $produk->price_formatted }}</p>
                            <p class="mt-1 text-[11px] text-slate-300">
                                Stok {{ number_format((int) $produk->stock) }} {{ $produk->unit ?: 'pcs' }}
                                &middot; {{ number_format((int) $produk->sold_count) }} terjual
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach

            <div class="mt-5 flex items-center justify-between border-t border-white/10 pt-4">
                <div class="flex items-center gap-2">
                    @foreach ($sorotan as $index => $produk)
                        <button type="button" @click="aktif = {{ $index }}"
                                class="h-2 rounded-full transition-all"
                                :class="aktif === {{ $index }} ? 'w-6 bg-brand-accent' : 'w-2 bg-white/40'"
                                aria-label="Tampilkan sorotan produk {{ $index + 1 }}"></button>
                    @endforeach
                </div>

                <a href="{{ route('products.index') }}" class="text-xs font-semibold text-white/80 transition hover:text-white">
                    Lihat katalog &rarr;
                </a>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-white/15 bg-white/10 p-6 text-sm leading-relaxed text-slate-200 backdrop-blur">
            Sorotan produk akan tampil di sini begitu mitra UMKM mengunggah katalognya.
        </div>
    @endif
</div>
