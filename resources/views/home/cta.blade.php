{{-- Seksi landing page: ajakan bergabung sebagai mitra / tanya admin lewat WhatsApp. --}}
<section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-brand-primary to-slate-900 px-6 py-10 text-white sm:px-10">
        <div class="flex flex-wrap items-center justify-between gap-8">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Gabung sebagai mitra</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">
                    Punya produk lokal? Jual di {{ config('app.name', 'E-Wedu') }}.
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-200">
                    Mitra mendapatkan halaman profil usaha, katalog produk, dan kanal literasi gratis.
                    Gratis ongkir tersedia di {{ number_format((int) $stats['titik']) }} titik pengambilan di Magelang.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('register') }}"
                   class="rounded-lg bg-brand-accent px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-600">
                    Daftar Mitra UMKM
                </a>

                <x-whatsapp-button :number="$whatsappAdmin" label="Tanya Admin" variant="light"
                                   message="Halo admin E-Wedu, saya ingin bertanya tentang bergabung sebagai mitra UMKM." />

                <a href="{{ route('about') }}"
                   class="rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold transition hover:border-white hover:bg-white/10">
                    Tentang E-Wedu
                </a>
            </div>
        </div>
    </div>
</section>
