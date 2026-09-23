{{--
    Komponen: x-footer
    Footer publik E-Wedu dengan 4 titik pengiriman logistik mandiri.
--}}
<footer class="mt-16 bg-brand-primary text-slate-200">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
            <p class="text-lg font-semibold text-white">{{ config('app.name', 'E-Wedu') }}</p>
            <p class="mt-3 text-sm leading-relaxed text-slate-300">
                Platform edu-commerce yang menghubungkan produk UMKM Magelang dengan mahasiswa dan
                masyarakat, lengkap dengan literasi kewirausahaan.
            </p>
        </div>

        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-accent">Jelajahi</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ url('/katalog') }}" class="transition hover:text-white">Katalog Produk</a></li>
                <li><a href="{{ route('umkm.index') }}" class="transition hover:text-white">Profil UMKM</a></li>
                <li><a href="{{ route('articles.index') }}" class="transition hover:text-white">Artikel Literasi</a></li>
                <li><a href="{{ url('/lacak') }}" class="transition hover:text-white">Lacak Pesanan</a></li>
                <li><a href="{{ route('about') }}" class="transition hover:text-white">Tentang E-Wedu</a></li>
            </ul>
        </div>

        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-accent">Titik Pengiriman</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-300">
                <li>Kampus Tuguran</li>
                <li>UMKM Center Magelang</li>
                <li>Balai Kota Magelang</li>
                <li>Sentra UMKM</li>
            </ul>
            <p class="mt-3 text-xs text-slate-400">
                Titik di atas gratis ongkir. Alamat lain dihitung otomatis dari Kampus Tuguran.
            </p>
        </div>
    </div>

    <div class="border-t border-white/10 py-4 text-center text-xs text-slate-300">
        &copy; {{ date('Y') }} {{ config('app.name', 'E-Wedu') }} &mdash; Praktik Kewirausahaan. Seluruh hak dilindungi.
    </div>
</footer>
