{{--
    Partial: cart.partials.summary
    Ringkasan keranjang: jumlah item, total berat, subtotal, dan tombol checkout.
    Ongkir baru dihitung di FASE 7 (checkout).
--}}
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-24">
    <h2 class="text-base font-semibold text-brand-text">Ringkasan keranjang</h2>

    <dl class="mt-4 space-y-2 text-sm">
        <div class="flex items-center justify-between gap-3">
            <dt class="text-slate-500">Total item</dt>
            <dd class="font-medium text-brand-text">{{ number_format($jumlahItem) }} item</dd>
        </div>

        <div class="flex items-center justify-between gap-3">
            <dt class="text-slate-500">Total berat</dt>
            <dd class="font-medium text-brand-text">{{ number_format($totalBerat) }} gram</dd>
        </div>

        <div class="flex items-center justify-between gap-3">
            <dt class="text-slate-500">Pengiriman</dt>
            <dd class="font-medium text-brand-text">Dihitung saat checkout</dd>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <dt class="text-base font-semibold text-brand-text">Subtotal</dt>
            <dd class="text-base font-bold text-brand-primary">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
        </div>
    </dl>

    <p class="mt-3 text-xs leading-relaxed text-slate-500">
        Subtotal memakai harga saat produk dimasukkan ke keranjang. Harga bisa berubah bila penjual memperbarui
        harga — total akhir dikunci saat checkout.
    </p>

    <button type="button" disabled title="Checkout dibuka pada FASE 7"
            class="mt-4 w-full cursor-not-allowed rounded-lg bg-slate-300 px-4 py-3 text-sm font-semibold text-white">
        Lanjut ke checkout (segera hadir)
    </button>

    <a href="{{ route('products.index') }}"
       class="mt-2 block text-center text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
        Lanjut belanja
    </a>
</div>
