{{--
    Komponen: x-empty-state
    Placeholder saat data kosong (katalog kosong, belum ada pesanan, dsb).

    Contoh:
      <x-empty-state title="Keranjang masih kosong" description="Yuk pilih produk UMKM favoritmu.">
          <a href="/katalog" class="...">Lihat Katalog</a>
      </x-empty-state>
--}}
@props([
    'title' => 'Belum ada data',
    'description' => null,
    'icon' => 'box', // box | cart | search | document
])

@php
    $path = match ($icon) {
        'cart' => 'M2.25 3h1.5l.75 3m0 0l1.5 7.5h9l1.5-7.5H4.5zM9 18.75a1.125 1.125 0 11-2.25 0 1.125 1.125 0 012.25 0zm8.25 0a1.125 1.125 0 11-2.25 0 1.125 1.125 0 012.25 0z',
        'search' => 'M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z',
        'document' => 'M19.5 14.25V6.75A2.25 2.25 0 0017.25 4.5H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25h6M15 18.75l2.25 2.25L21.75 16.5',
        default => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white/70 px-6 py-12 text-center']) }}>
    <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-brand-primary">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
        </svg>
    </span>

    <p class="mt-4 text-base font-semibold text-brand-text">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 max-w-md text-sm leading-relaxed text-slate-500">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
