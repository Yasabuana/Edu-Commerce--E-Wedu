{{-- Partial: kepala halaman profil mitra UMKM (cover, logo, identitas, kontak cepat). --}}
<section class="border-b border-slate-200 bg-white">
    @if ($umkmProfile->coverImageUrl())
        <img src="{{ $umkmProfile->coverImageUrl() }}" alt="Sampul {{ $umkmProfile->business_name }}"
             class="h-40 w-full object-cover sm:h-56">
    @else
        <div class="h-24 w-full bg-gradient-to-r from-brand-primary to-slate-900 sm:h-32"></div>
    @endif

    <div class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="pt-4">
            <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('umkm.index') }}" class="transition hover:text-brand-primary">Mitra UMKM</a></li>
                <li aria-hidden="true">/</li>
                <li class="font-medium text-slate-700">{{ $umkmProfile->business_name }}</li>
            </ol>
        </nav>

        <div class="mt-5 flex flex-wrap items-start gap-5">
            @if ($umkmProfile->logoUrl())
                <img src="{{ $umkmProfile->logoUrl() }}" alt="Logo {{ $umkmProfile->business_name }}"
                     class="h-20 w-20 shrink-0 rounded-2xl border border-slate-200 bg-white object-cover shadow-sm">
            @else
                <span class="grid h-20 w-20 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand-primary to-slate-900 text-2xl font-black text-white shadow-sm">
                    {{ mb_strtoupper(mb_substr($umkmProfile->business_name, 0, 2)) }}
                </span>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">
                        {{ $umkmProfile->business_name }}
                    </h1>

                    @if ($umkmProfile->isVerified())
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-[11px] font-semibold text-green-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Terverifikasi
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $umkmProfile->category_label ?: 'UMKM Magelang' }}
                    &middot; Pemilik {{ $umkmProfile->owner_name }}
                </p>

                @if ($umkmProfile->address)
                    <p class="mt-2 flex items-start gap-1.5 text-sm text-slate-600">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        {{ $umkmProfile->address }}
                    </p>
                @endif

                <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate-500">
                    <div>
                        <dt class="inline">Produk aktif: </dt>
                        <dd class="inline font-semibold text-slate-700">{{ number_format((int) ($umkmProfile->products_count ?? 0)) }}</dd>
                    </div>
                    <div>
                        <dt class="inline">Bergabung: </dt>
                        <dd class="inline font-semibold text-slate-700">
                            {{ $umkmProfile->published_at?->locale(config('app.locale'))->translatedFormat('F Y') ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-whatsapp-button :number="$umkmProfile->whatsapp ?: $umkmProfile->phone" label="Hubungi Mitra"
                                   :message="'Halo '.$umkmProfile->business_name.', saya menemukan usaha Anda di E-Wedu.'" />

                @if ($umkmProfile->instagram)
                    <a href="https://instagram.com/{{ ltrim($umkmProfile->instagram, '@') }}" target="_blank" rel="noopener noreferrer"
                       class="rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                        Instagram
                    </a>
                @endif

                @if ($umkmProfile->website)
                    <a href="{{ $umkmProfile->website }}" target="_blank" rel="noopener noreferrer"
                       class="rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                        Website
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
