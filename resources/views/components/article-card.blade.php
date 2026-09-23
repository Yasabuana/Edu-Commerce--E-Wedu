{{--
    Komponen: x-article-card
    Kartu artikel literasi. `compact` = versi ringkas untuk sidebar daftar populer
    (properti `nomor` mengisi nomor urut).

    Contoh:
      <x-article-card :article="$article" />
      <x-article-card :article="$item" compact :nomor="$loop->iteration" />
--}}
@props([
    'article',
    'compact' => false,
    'nomor' => null,
])

@php
    $cover = $article->coverImageUrl();
    $kategori = $article->category?->name ?? 'Literasi UMKM';
    $tautan = route('articles.show', $article);
@endphp

@if ($compact)
    <article class="flex items-start gap-3 border-b border-slate-100 py-3 last:border-0 last:pb-0">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-primary/10 text-xs font-bold text-brand-primary">
            {{ $nomor ?? 1 }}
        </span>

        <div class="min-w-0">
            <h3 class="text-sm font-semibold leading-snug text-brand-text">
                <a href="{{ $tautan }}" class="transition hover:text-brand-primary">{{ $article->title }}</a>
            </h3>
            <p class="mt-1 text-[11px] text-slate-500">
                {{ number_format((int) $article->views) }} dibaca &middot; {{ $article->published_at_formatted ?? 'Belum terbit' }}
            </p>
        </div>
    </article>
@else
    <article class="group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-brand-primary/40 hover:shadow-md">

        <a href="{{ $tautan }}" class="relative block aspect-[16/9] overflow-hidden bg-slate-100"
           aria-label="Baca artikel {{ $article->title }}">
            @if ($cover)
                <img src="{{ $cover }}" alt="Ilustrasi artikel {{ $article->title }}" loading="lazy"
                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
            @else
                <span class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-800 via-brand-primary to-brand-primary/80">
                    <span class="px-4 text-center text-xs font-semibold uppercase tracking-[0.2em] text-white/80">{{ $kategori }}</span>
                </span>
            @endif
        </a>

        <div class="flex flex-1 flex-col p-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-brand-primary/10 px-2.5 py-1 text-[11px] font-semibold text-brand-primary">{{ $kategori }}</span>
                <span class="text-[11px] text-slate-500">{{ $article->published_at_formatted ?? 'Belum terbit' }}</span>
            </div>

            <h3 class="mt-3 text-base font-semibold leading-snug text-brand-text">
                <a href="{{ $tautan }}" class="transition hover:text-brand-primary">{{ $article->title }}</a>
            </h3>

            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-600">
                {{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $article->body), 140) }}
            </p>

            <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-4 text-[11px] text-slate-500">
                <span>{{ $article->author?->name ?? 'Redaksi E-Wedu' }}</span>
                <span>{{ $article->readingTimeMinutes() }} menit baca &middot; {{ number_format((int) $article->views) }} dilihat</span>
            </div>
        </div>
    </article>
@endif
