{{--
    Seksi landing page: Produk Unggulan (`is_featured` + aktif + stok tersedia).
    Filter kategori dijalankan di sisi klien dengan Alpine.js agar instan.
--}}
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" x-data="{ kategori: 'semua' }">
    <x-section-heading eyebrow="Kurasi mingguan" title="Produk Unggulan"
                       subtitle="Produk terlaris pilihan dari mitra UMKM Magelang yang stoknya siap dikirim."
                       action-label="Lihat katalog lengkap" action-href="{{ route('products.index') }}" />

    @if ($featuredProducts->isNotEmpty())
        <div class="mt-6 flex flex-wrap gap-2">
            <button type="button" @click="kategori = 'semua'"
                    :class="kategori === 'semua' ? 'bg-brand-primary text-white' : 'border border-slate-200 text-slate-700 hover:border-brand-primary hover:text-brand-primary'"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition">
                Semua
            </button>

            @foreach ($featuredProducts->pluck('category')->filter()->unique('id') as $kategori)
                <button type="button" @click="kategori = '{{ $kategori->slug }}'"
                        :class="kategori === '{{ $kategori->slug }}' ? 'bg-brand-primary text-white' : 'border border-slate-200 text-slate-700 hover:border-brand-primary hover:text-brand-primary'"
                        class="rounded-full px-3 py-1.5 text-xs font-medium transition">
                    {{ $kategori->name }}
                </button>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($featuredProducts as $produk)
                <div x-show="kategori === 'semua' || kategori === '{{ $produk->category?->slug }}'"
                     x-transition.opacity x-cloak>
                    <x-product-card :product="$produk" />
                </div>
            @endforeach
        </div>

        <p class="mt-6 text-xs text-slate-500">
            Harga dan stok diperbarui langsung oleh mitra UMKM. Detail pembelian tersedia di halaman katalog.
        </p>
    @else
        <div class="mt-6">
            <x-empty-state title="Belum ada produk unggulan"
                           description="Produk unggulan akan tampil setelah mitra UMKM mengunggah katalog dan admin menandainya sebagai unggulan.">
                <a href="{{ route('umkm.index') }}"
                   class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                    Lihat mitra UMKM
                </a>
            </x-empty-state>
        </div>
    @endif
</section>
