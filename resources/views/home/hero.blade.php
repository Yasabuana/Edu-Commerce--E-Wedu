{{-- Seksi landing page: Hero banner + CTA utama (sorotan produk di home/sorotan). --}}
<section class="relative overflow-hidden bg-gradient-to-br from-brand-primary via-brand-primary to-slate-900 text-white">
    <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-accent/20 blur-3xl" aria-hidden="true"></div>

    <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-20">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium">
                <span class="h-2 w-2 rounded-full bg-brand-accent" aria-hidden="true"></span>
                Edu-commerce UMKM Magelang
            </span>

            <h1 class="mt-5 text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                Belanja Produk Lokal,
                <span class="text-brand-accent">Naik Kelas</span> lewat Literasi UMKM
            </h1>

            <p class="mt-4 max-w-xl text-sm leading-relaxed text-slate-200 sm:text-base">
                {{ $tagline ?: 'Marketplace & Literasi UMKM Magelang' }} — belanja langsung dari mitra terverifikasi,
                baca panduan kewirausahaan, lalu ambil pesanan di titik gratis ongkir terdekat.
            </p>

            {{-- CTA utama: katalog (FASE 5) & literasi --}}
            <div class="mt-7 flex flex-wrap items-center gap-3">
                <a href="{{ url('/katalog') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-accent px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-accent/30 transition hover:bg-orange-600">
                    Jelajahi Katalog
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" />
                    </svg>
                </a>

                <a href="{{ route('articles.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold transition hover:border-white hover:bg-white/10">
                    Baca Literasi UMKM
                </a>
            </div>

            <dl class="mt-9 grid max-w-lg grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([
                    ['label' => 'Produk', 'nilai' => $stats['produk']],
                    ['label' => 'Mitra UMKM', 'nilai' => $stats['mitra']],
                    ['label' => 'Artikel', 'nilai' => $stats['artikel']],
                    ['label' => 'Titik gratis', 'nilai' => $stats['titik']],
                ] as $item)
                    <div class="rounded-lg border border-white/10 bg-white/5 px-3 py-2">
                        <dt class="text-[11px] uppercase tracking-wider text-slate-300">{{ $item['label'] }}</dt>
                        <dd class="mt-1 text-xl font-bold">{{ number_format((int) $item['nilai']) }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        @include('home.sorotan')
    </div>
</section>
