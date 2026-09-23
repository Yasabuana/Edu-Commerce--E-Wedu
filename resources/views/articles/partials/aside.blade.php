{{-- Partial: sidebar halaman baca artikel. --}}
<aside class="space-y-6">
    @if ($article->umkmProfile)
        <x-card title="Profil mitra terkait">
            <p class="text-sm font-semibold text-brand-text">{{ $article->umkmProfile->business_name }}</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">
                {{ $article->umkmProfile->description ?: 'Mitra UMKM binaan E-Wedu.' }}
            </p>
            <a href="{{ route('umkm.show', $article->umkmProfile) }}"
               class="mt-3 inline-flex text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
                Lihat profil mitra &rarr;
            </a>
        </x-card>
    @endif

    <x-card title="Artikel terkait">
        @forelse ($related as $item)
            <x-article-card :article="$item" compact :nomor="$loop->iteration" />
        @empty
            <p class="text-sm text-slate-500">Belum ada artikel lain di kategori ini.</p>
        @endforelse
    </x-card>

    <x-card title="Jelajahi literasi lain">
        <p class="text-sm leading-relaxed text-slate-600">
            Semua artikel literasi E-Wedu bebas dibaca tanpa biaya.
        </p>
        <a href="{{ route('articles.index') }}"
           class="mt-3 inline-flex text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
            Semua artikel &rarr;
        </a>
    </x-card>
</aside>
