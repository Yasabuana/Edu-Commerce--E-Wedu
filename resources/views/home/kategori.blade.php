{{-- Seksi landing page: pintasan kategori produk (menuju katalog FASE 5). --}}
<section class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-3 px-4 py-5 sm:px-6 lg:px-8">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jelajahi kategori</span>

        @forelse ($categories as $kategori)
            <a href="{{ url('/katalog?kategori='.$kategori->slug) }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                {{ $kategori->name }}
                <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-500">{{ $kategori->products_count }}</span>
            </a>
        @empty
            <span class="text-xs text-slate-500">Kategori belum tersedia.</span>
        @endforelse
    </div>
</section>
