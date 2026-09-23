{{--
    Komponen: x-umkm-card
    Kartu mitra UMKM. `products_count` diisi pemanggil via withCount().

    Contoh: <x-umkm-card :umkm="$profile" />
--}}
@props(['umkm'])

@php
    $logo = $umkm->logoUrl();
    $jumlahProduk = $umkm->products_count ?? $umkm->products->count() ?? 0;
@endphp

<article class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/40 hover:shadow-md">

    <div class="flex items-start gap-4">
        @if ($logo)
            <img src="{{ $logo }}" alt="Logo {{ $umkm->business_name }}"
                 class="h-14 w-14 shrink-0 rounded-xl border border-slate-200 object-cover">
        @else
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-primary to-slate-900 text-lg font-black text-white">
                {{ mb_strtoupper(mb_substr($umkm->business_name, 0, 2)) }}
            </span>
        @endif

        <div class="min-w-0">
            <h3 class="text-base font-semibold leading-snug text-brand-text">
                <a href="{{ route('umkm.show', $umkm) }}" class="transition hover:text-brand-primary">{{ $umkm->business_name }}</a>
            </h3>

            <p class="mt-1 text-xs text-slate-500">
                {{ $umkm->category_label ?: 'UMKM Magelang' }} &middot; {{ $umkm->owner_name }}
            </p>

            @if ($umkm->is_verified)
                <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Terverifikasi
                </span>
            @endif
        </div>
    </div>

    <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-600">
        {{ $umkm->description ?: 'Mitra UMKM binaan E-Wedu yang menjual produk lokal Magelang.' }}
    </p>

    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
        <span class="font-medium text-slate-700">{{ number_format((int) $jumlahProduk) }} produk aktif</span>
        <span class="line-clamp-1 max-w-[60%] text-right">{{ $umkm->address ?: 'Magelang, Jawa Tengah' }}</span>
    </div>
</article>
