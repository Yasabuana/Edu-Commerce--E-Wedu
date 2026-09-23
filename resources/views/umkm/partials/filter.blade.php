{{--
    Partial: filter direktori mitra UMKM (pencarian, kategori usaha, pengurutan).
    Urutan tidak valid apa pun akan dinormalkan controller menjadi `terbaru`.
--}}
<form method="GET" action="{{ route('umkm.index') }}"
      class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <label class="sm:col-span-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cari mitra</span>
            <span class="relative mt-1 block">
                <input type="search" name="q" value="{{ $kataKunci }}"
                       placeholder="Nama usaha, pemilik, atau deskripsi..."
                       class="w-full rounded-lg border-slate-300 pl-9 text-sm focus:border-brand-primary focus:ring-brand-primary">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                     fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </span>
        </label>

        <label>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori usaha</span>
            <select name="kategori"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">
                <option value="">Semua kategori</option>
                @foreach ($categoryLabels as $label)
                    <option value="{{ $label }}" @selected($kategori === $label)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Urutkan</span>
            <select name="urut"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">
                <option value="terbaru" @selected($urut === 'terbaru')>Terbaru dipublikasikan</option>
                <option value="produk" @selected($urut === 'produk')>Produk terbanyak</option>
            </select>
        </label>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
        <button type="submit"
                class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
            Terapkan filter
        </button>

        @if ($kataKunci !== '' || $kategori !== '' || $urut !== 'terbaru')
            <a href="{{ route('umkm.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-brand-primary hover:text-brand-primary">
                Reset
            </a>
        @endif

        <span class="ml-auto text-xs text-slate-500">
            Hanya profil terverifikasi &amp; sudah dipublikasikan yang tampil.
        </span>
    </div>
</form>
