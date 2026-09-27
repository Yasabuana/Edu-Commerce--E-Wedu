{{--
    Partial: products.partials.related
    Rekomendasi produk lain dari kategori yang sama.
--}}
@if ($related->isNotEmpty())
    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Kamu mungkin suka" title="Produk lain di kategori ini"
                               subtitle="Pilihan lain dari mitra UMKM Magelang dengan kategori serupa."
                               action-label="Lihat semua katalog"
                               :action-href="route('products.index', array_filter(['kategori' => $product->category?->slug]))" />

            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $produkLain)
                    <x-product-card :product="$produkLain" />
                @endforeach
            </div>
        </div>
    </section>
@endif
