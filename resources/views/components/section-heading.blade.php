{{--
    Komponen: x-section-heading
    Judul + subjudul seksi dengan tautan aksi di kanan (dipakai landing page
    dan halaman daftar). Slot opsional: `actions`.

    Contoh:
      <x-section-heading eyebrow="Kurasi mingguan" title="Produk Unggulan"
          subtitle="Pilihan produk terbaik dari mitra UMKM Magelang."
          action-label="Lihat semua" action-href="/katalog" />
--}}
@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">{{ $eyebrow }}</p>
        @endif

        <h2 class="mt-1 text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">{{ $title }}</h2>

        @if ($subtitle)
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        @isset($actions)
            {{ $actions }}
        @endisset

        @if ($actionLabel && $actionHref)
            <a href="{{ $actionHref }}"
               class="inline-flex items-center gap-1 text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
                {{ $actionLabel }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        @endif
    </div>
</div>
