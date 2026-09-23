{{-- Seksi landing page: mitra UMKM terverifikasi (scope verified + published). --}}
<section class="border-y border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <x-section-heading eyebrow="Direktori mitra" title="UMKM Terverifikasi"
                           subtitle="Setiap mitra telah diverifikasi admin E-Wedu: data usaha jelas dan produk sesuai aslinya."
                           action-label="Semua mitra" action-href="{{ route('umkm.index') }}" />

        @if ($featuredUmkms->isNotEmpty())
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featuredUmkms as $mitra)
                    <x-umkm-card :umkm="$mitra" />
                @endforeach
            </div>
        @else
            <div class="mt-6">
                <x-empty-state title="Belum ada mitra terverifikasi"
                               description="Profil mitra akan muncul di sini setelah diverifikasi dan dipublikasikan admin.">
                    <a href="{{ route('register') }}"
                       class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                        Daftar sebagai mitra
                    </a>
                </x-empty-state>
            </div>
        @endif
    </div>
</section>
