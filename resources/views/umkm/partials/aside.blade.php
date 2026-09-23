{{-- Partial: sidebar profil mitra (kontak, literasi mitra, tautan terkait). --}}
<aside class="space-y-6">
    <x-card title="Informasi kontak">
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Telepon</dt>
                <dd class="mt-1 text-slate-700">{{ $umkmProfile->phone ?: 'Belum diisi' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">WhatsApp</dt>
                <dd class="mt-1 text-slate-700">{{ $umkmProfile->whatsapp ?: ($umkmProfile->phone ?: 'Belum diisi') }}</dd>
            </div>
            @if ($umkmProfile->instagram)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Instagram</dt>
                    <dd class="mt-1 text-slate-700">{{ $umkmProfile->instagram }}</dd>
                </div>
            @endif
            @if ($umkmProfile->website)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Website</dt>
                    <dd class="mt-1 break-all text-slate-700">{{ $umkmProfile->website }}</dd>
                </div>
            @endif
        </dl>

        @if ($umkmProfile->verified_at)
            <p class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-500">
                Diverifikasi admin pada
                {{ $umkmProfile->verified_at->locale(config('app.locale'))->translatedFormat('d F Y') }}.
            </p>
        @endif
    </x-card>

    <x-card title="Artikel mitra ini">
        @forelse ($articles as $artikel)
            <x-article-card :article="$artikel" compact :nomor="$loop->iteration" />
        @empty
            <p class="text-sm text-slate-500">Mitra ini belum menulis artikel literasi.</p>
        @endforelse

        @if ($articles->isNotEmpty())
            <a href="{{ route('articles.index') }}"
               class="mt-3 inline-flex text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
                Semua literasi &rarr;
            </a>
        @endif
    </x-card>

    <x-card title="Beli produk lokal">
        <p class="text-sm leading-relaxed text-slate-600">
            Setiap pembelian dari mitra UMKM Magelang ikut menggerakkan ekonomi warga sekitar.
        </p>
        <a href="{{ route('umkm.index') }}"
           class="mt-3 inline-flex text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
            Jelajahi mitra lain &rarr;
        </a>
    </x-card>
</aside>
