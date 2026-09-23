{{-- Seksi landing page: artikel literasi terbaru (scope published). --}}
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <x-section-heading eyebrow="Literasi UMKM" title="Artikel Literasi Terbaru"
                       subtitle="Panduan praktis pemasaran, pembukuan, dan pengemasan produk untuk pelaku usaha kecil."
                       action-label="Semua artikel" action-href="{{ route('articles.index') }}" />

    @if ($latestArticles->isNotEmpty())
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestArticles as $artikel)
                <x-article-card :article="$artikel" />
            @endforeach
        </div>
    @else
        <div class="mt-6">
            <x-empty-state icon="document" title="Belum ada artikel terbit"
                           description="Artikel literasi akan tampil setelah redaksi menerbitkannya.">
            </x-empty-state>
        </div>
    @endif
</section>
