{{-- Partial: cerita usaha mitra + data operasional. --}}
<section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <h2 class="text-lg font-bold tracking-tight text-brand-text">Cerita Usaha</h2>

    <div class="mt-4 space-y-4 text-base leading-relaxed text-slate-700">
        @if ($umkmProfile->description)
            {!! nl2br(e($umkmProfile->description)) !!}
        @else
            <p class="text-slate-500">
                {{ $umkmProfile->business_name }} belum mengisi cerita usahanya. Silakan hubungi mitra
                langsung melalui WhatsApp untuk informasi lebih lanjut.
            </p>
        @endif
    </div>

    <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama pemilik</dt>
            <dd class="mt-1 text-sm font-medium text-slate-700">{{ $umkmProfile->owner_name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori usaha</dt>
            <dd class="mt-1 text-sm font-medium text-slate-700">{{ $umkmProfile->category_label ?: 'Belum diisi' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat usaha</dt>
            <dd class="mt-1 text-sm font-medium text-slate-700">{{ $umkmProfile->address ?: 'Belum diisi' }}</dd>
        </div>
        @if ($umkmProfile->coordinates())
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Titik koordinat</dt>
                <dd class="mt-1 text-sm font-medium text-slate-700">
                    {{ number_format($umkmProfile->coordinates()[0], 6) }},
                    {{ number_format($umkmProfile->coordinates()[1], 6) }}
                    <span class="text-xs font-normal text-slate-500">(dipakai untuk perhitungan jarak pengiriman)</span>
                </dd>
            </div>
        @endif
    </dl>
</section>
