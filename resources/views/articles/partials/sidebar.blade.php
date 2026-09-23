{{-- Partial: sidebar daftar artikel (populer, kategori, ajakan). --}}
<aside class="space-y-6">
    <x-card title="Artikel Populer" subtitle="Paling banyak dibaca">
        @forelse ($popular as $item)
            <x-article-card :article="$item" compact :nomor="$loop->iteration" />
        @empty
            <p class="text-sm text-slate-500">Belum ada artikel terbit.</p>
        @endforelse
    </x-card>

    <x-card title="Kategori Literasi">
        <ul class="space-y-2 text-sm">
            @forelse ($categories as $kategori)
                <li class="flex items-center justify-between gap-3">
                    <a href="{{ route('articles.index', ['kategori' => $kategori->slug]) }}"
                       class="text-slate-700 transition hover:text-brand-primary">{{ $kategori->name }}</a>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500">{{ $kategori->articles_count }}</span>
                </li>
            @empty
                <li class="text-slate-500">Kategori belum tersedia.</li>
            @endforelse
        </ul>
    </x-card>

    <x-card title="Punya cerita usaha?">
        <p class="text-sm leading-relaxed text-slate-600">
            Mitra UMKM E-Wedu bisa mengirim cerita perjalanan usahanya untuk dibagikan di kanal literasi ini.
        </p>
        <a href="{{ route('about') }}" class="mt-3 inline-flex text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
            Kenali E-Wedu &rarr;
        </a>
    </x-card>
</aside>
