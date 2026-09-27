{{--
    Partial: products.partials.filter
    Filter katalog (pencarian, kategori, penjual, rentang harga, urutan).
    Semua kontrol berada dalam satu form GET agar filter ikut terbawa saat paginasi.
--}}
@php
    $adaFilter = $filter['q'] !== ''
        || $filter['kategori'] !== ''
        || $filter['umkm'] !== ''
        || $filter['min'] !== null
        || $filter['max'] !== null;

    $kelasKontrol = 'mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary';
@endphp

<form method="GET" action="{{ route('products.index') }}"
      class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">

        <div class="lg:col-span-2">
            <label for="filter-q" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari produk</label>
            <input type="search" id="filter-q" name="q" value="{{ $filter['q'] }}"
                   placeholder="mis. kopi, batik, tas rajut" class="{{ $kelasKontrol }}">
        </div>

        <div>
            <label for="filter-kategori" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</label>
            <select id="filter-kategori" name="kategori" class="{{ $kelasKontrol }}">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($filter['kategori'] === $category->slug)>
                        {{ $category->name }} ({{ number_format((int) $category->products_count) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter-umkm" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Penjual</label>
            <select id="filter-umkm" name="umkm" class="{{ $kelasKontrol }}">
                <option value="">Semua mitra UMKM</option>
                @foreach ($umkmOptions as $umkm)
                    <option value="{{ $umkm->slug }}" @selected($filter['umkm'] === $umkm->slug)>
                        {{ $umkm->business_name }} ({{ number_format((int) $umkm->products_count) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter-min" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Rentang harga</label>
            <div class="mt-1 flex items-center gap-2">
                <input type="number" id="filter-min" name="min" min="0" step="1000" inputmode="numeric"
                       value="{{ $filter['min'] }}" placeholder="{{ $hargaMinimum }}"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                       aria-label="Harga minimum">
                <span class="text-slate-400" aria-hidden="true">&ndash;</span>
                <input type="number" id="filter-max" name="max" min="0" step="1000" inputmode="numeric"
                       value="{{ $filter['max'] }}" placeholder="{{ $hargaMaksimum }}"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                       aria-label="Harga maksimum">
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
            Terapkan filter
        </button>

        @if ($adaFilter)
            <a href="{{ route('products.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                Reset filter
            </a>
        @endif

        <div class="ml-auto flex items-center gap-2">
            <label for="filter-urut" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Urutkan</label>
            <select id="filter-urut" name="urut"
                    class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                @foreach ($sortOptions as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($filter['urut'] === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($adaFilter)
        @php
            $parameterAktif = array_filter([
                'q' => $filter['q'] !== '' ? $filter['q'] : null,
                'kategori' => $filter['kategori'] !== '' ? $filter['kategori'] : null,
                'umkm' => $filter['umkm'] !== '' ? $filter['umkm'] : null,
                'min' => $filter['min'],
                'max' => $filter['max'],
                'urut' => $filter['urut'] !== \App\Models\Product::SORT_TERBARU ? $filter['urut'] : null,
            ], fn ($nilai) => $nilai !== null && $nilai !== '');

            $tautanChip = fn (array $dibuang): string => route(
                'products.index',
                \Illuminate\Support\Arr::except($parameterAktif, $dibuang),
            );

            $kelasChip = 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700 transition hover:bg-slate-200';

            $labelHarga = match (true) {
                $filter['min'] !== null && $filter['max'] !== null => 'Rp '.number_format($filter['min'], 0, ',', '.')
                    .' – Rp '.number_format($filter['max'], 0, ',', '.'),
                $filter['min'] !== null => 'Minimal Rp '.number_format($filter['min'], 0, ',', '.'),
                default => 'Maksimal Rp '.number_format((int) $filter['max'], 0, ',', '.'),
            };
        @endphp

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 text-xs">
            <span class="font-semibold uppercase tracking-wide text-slate-500">Filter aktif</span>

            @if ($filter['q'] !== '')
                <a href="{{ $tautanChip(['q']) }}" class="{{ $kelasChip }}">
                    &ldquo;{{ $filter['q'] }}&rdquo; <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Hapus filter pencarian</span>
                </a>
            @endif

            @if ($filter['kategori'] !== '')
                <a href="{{ $tautanChip(['kategori']) }}" class="{{ $kelasChip }}">
                    {{ $kategoriAktif->name ?? $filter['kategori'] }} <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Hapus filter kategori</span>
                </a>
            @endif

            @if ($filter['umkm'] !== '')
                <a href="{{ $tautanChip(['umkm']) }}" class="{{ $kelasChip }}">
                    {{ $umkmAktif->business_name ?? $filter['umkm'] }} <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Hapus filter penjual</span>
                </a>
            @endif

            @if ($filter['min'] !== null || $filter['max'] !== null)
                <a href="{{ $tautanChip(['min', 'max']) }}" class="{{ $kelasChip }}">
                    {{ $labelHarga }} <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Hapus filter rentang harga</span>
                </a>
            @endif

            @if ($filter['urut'] !== \App\Models\Product::SORT_TERBARU)
                <a href="{{ $tautanChip(['urut']) }}" class="{{ $kelasChip }}">
                    Urut: {{ $sortOptions[$filter['urut']] }} <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Hapus filter pengurutan</span>
                </a>
            @endif

            <a href="{{ route('products.index') }}"
               class="ml-auto font-semibold text-brand-primary transition hover:text-brand-accent">
                Hapus semua
            </a>
        </div>
    @endif
</form>
