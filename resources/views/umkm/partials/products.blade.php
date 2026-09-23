{{-- Partial: katalog produk milik mitra (maksimal 6 produk aktif dari controller). --}}
<section>
    <x-section-heading eyebrow="Katalog mitra" title="Produk dari {{ $umkmProfile->business_name }}"
                       subtitle="Harga dan stok diperbarui langsung oleh mitra. Pembelian dilakukan lewat halaman katalog."
                       action-label="Lihat katalog" action-href="{{ url('/katalog') }}" />

    @if ($products->isNotEmpty())
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>

        @if (($umkmProfile->products_count ?? 0) > $products->count())
            <p class="mt-4 text-xs text-slate-500">
                Menampilkan {{ $products->count() }} dari {{ number_format((int) $umkmProfile->products_count) }} produk aktif.
            </p>
        @endif
    @else
        <div class="mt-6">
            <x-empty-state title="Belum ada produk aktif"
                           description="Mitra ini belum menayangkan produk, atau stoknya sedang kosong. Coba hubungi mitra lewat WhatsApp.">
                <x-whatsapp-button :number="$umkmProfile->whatsapp ?: $umkmProfile->phone" label="Tanya Mitra"
                                   variant="outline"
                                   :message="'Halo '.$umkmProfile->business_name.', apakah ada produk yang tersedia?'" />
            </x-empty-state>
        </div>
    @endif
</section>
