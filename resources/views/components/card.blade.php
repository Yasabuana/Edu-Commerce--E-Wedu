{{--
    Komponen: x-card
    Kartu konten standar E-Wedu. Slot: default (isi), `actions` (kanan header), `footer`.

    Contoh:
      <x-card title="Ringkasan Pesanan" subtitle="3 item">
          Konten...
          <x-slot:actions><a href="#">Lihat</a></x-slot:actions>
          <x-slot:footer>Total: Rp 150.000</x-slot:footer>
      </x-card>
--}}
@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-5 sm:p-6',
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title || isset($actions))
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                @if ($title)
                    <h3 class="text-base font-semibold text-brand-text">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-3 text-sm text-slate-600 sm:px-6">
            {{ $footer }}
        </div>
    @endisset
</div>
