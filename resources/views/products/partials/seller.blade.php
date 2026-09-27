{{--
    Partial: products.partials.seller
    Kartu penjual (mitra UMKM) di sisi kanan halaman detail produk.
--}}
@php
    $umkm = $product->umkmProfile;
    $logo = $umkm?->logoUrl();
@endphp

@if ($umkm)
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Penjual</p>

        <div class="mt-3 flex items-start gap-3">
            @if ($logo)
                <img src="{{ $logo }}" alt="Logo {{ $umkm->business_name }}"
                     class="h-12 w-12 shrink-0 rounded-lg border border-slate-200 object-cover">
            @else
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-brand-primary to-slate-900 text-base font-black text-white">
                    {{ mb_strtoupper(mb_substr($umkm->business_name, 0, 2)) }}
                </span>
            @endif

            <div class="min-w-0">
                <h2 class="text-base font-semibold leading-snug text-brand-text">
                    <a href="{{ route('umkm.show', $umkm) }}"
                       class="transition hover:text-brand-primary">{{ $umkm->business_name }}</a>
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $umkm->category_label ?: 'UMKM Magelang' }} &middot; {{ $umkm->owner_name }}
                </p>
            </div>
        </div>

        <dl class="mt-4 space-y-2 text-xs">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Lokasi</dt>
                <dd class="text-right font-medium text-slate-700">{{ $umkm->address ?: 'Magelang, Jawa Tengah' }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Status mitra</dt>
                <dd class="font-medium {{ $umkm->isVerified() ? 'text-green-700' : 'text-slate-700' }}">
                    {{ $umkm->isVerified() ? 'Terverifikasi admin' : 'Menunggu verifikasi' }}
                </dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-col gap-2">
            <a href="{{ route('umkm.show', $umkm) }}"
               class="rounded-lg border border-brand-primary px-4 py-2 text-center text-sm font-semibold text-brand-primary transition hover:bg-brand-primary/5">
                Lihat profil mitra
            </a>
            <a href="{{ route('products.index', ['umkm' => $umkm->slug]) }}"
               class="rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                Semua produk penjual ini
            </a>
        </div>
    </div>
@endif
