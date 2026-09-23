{{--
    Partial: filter daftar artikel (pencarian + kategori).
    Panel filter dilipat di layar kecil memakai Alpine.js (`lanjutan`).
--}}
<form method="GET" action="{{ route('articles.index') }}"
      class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
      x-data="{ lanjutan: {{ ($kataKunci !== '' || $kategoriSlug !== '') ? 'true' : 'false' }} }">
    <div class="flex flex-wrap gap-2">
        <label class="relative min-w-[220px] flex-1">
            <span class="sr-only">Cari artikel</span>
            <input type="search" name="q" value="{{ $kataKunci }}" placeholder="Cari judul atau isi artikel..."
                   class="w-full rounded-lg border-slate-300 pl-9 text-sm focus:border-brand-primary focus:ring-brand-primary">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                 fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
        </label>

        <button type="submit"
                class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
            Cari
        </button>

        <button type="button" @click="lanjutan = !lanjutan" :aria-expanded="lanjutan.toString()"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-brand-primary hover:text-brand-primary lg:hidden">
            Filter
        </button>
    </div>

    <div x-show="lanjutan" x-cloak class="mt-4 border-t border-slate-100 pt-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</p>

        <div class="mt-2 flex flex-wrap gap-2">
            <a href="{{ route('articles.index', array_filter(['q' => $kataKunci])) }}"
               class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $kategoriSlug === '' ? 'bg-brand-primary text-white' : 'border border-slate-200 text-slate-700 hover:border-brand-primary hover:text-brand-primary' }}">
                Semua
            </a>

            @foreach ($categories as $kategori)
                <a href="{{ route('articles.index', array_filter(['q' => $kataKunci, 'kategori' => $kategori->slug])) }}"
                   class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $kategoriSlug === $kategori->slug ? 'bg-brand-primary text-white' : 'border border-slate-200 text-slate-700 hover:border-brand-primary hover:text-brand-primary' }}">
                    {{ $kategori->name }} ({{ $kategori->articles_count }})
                </a>
            @endforeach
        </div>
    </div>
</form>
