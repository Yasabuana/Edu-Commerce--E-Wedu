{{--
    Komponen: x-page-header
    Kepala halaman publik (band biru brand + breadcrumb + judul).

    Contoh:
      <x-page-header eyebrow="Literasi" title="Artikel Literasi UMKM"
          subtitle="Panduan praktis untuk pelaku usaha."
          :breadcrumbs="[['label' => 'Beranda', 'url' => route('home')]]">
          <form>...</form>
      </x-page-header>
--}}
@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
])

<section class="border-b border-brand-primary/20 bg-gradient-to-br from-brand-primary via-brand-primary to-slate-900 text-white">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">

        @if (! empty($breadcrumbs))
            <nav aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-xs text-white/70">
                    @foreach ($breadcrumbs as $index => $crumb)
                        <li class="flex items-center gap-2">
                            @if (! empty($crumb['url']) && ! $loop->last)
                                <a href="{{ $crumb['url'] }}" class="transition hover:text-white">{{ $crumb['label'] }}</a>
                            @else
                                <span class="font-medium text-white">{{ $crumb['label'] }}</span>
                            @endif

                            @unless ($loop->last)
                                <span aria-hidden="true">/</span>
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        @if ($eyebrow)
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">{{ $eyebrow }}</p>
        @endif

        <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-200 sm:text-base">{{ $subtitle }}</p>
        @endif

        @if (! $slot->isEmpty())
            <div class="mt-6">{{ $slot }}</div>
        @endif
    </div>
</section>
