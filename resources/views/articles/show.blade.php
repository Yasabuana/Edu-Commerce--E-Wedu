@extends('layouts.public')

@section('title', $article->title.' — '.config('app.name', 'E-Wedu'))
@section('meta_description', $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $article->body), 150))

@section('content')
    {{-- Kepala artikel: breadcrumb + judul + meta penulis --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <nav aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('articles.index') }}" class="transition hover:text-brand-primary">Literasi</a></li>
                    @if ($article->category)
                        <li aria-hidden="true">/</li>
                        <li>
                            <a href="{{ route('articles.index', ['kategori' => $article->category->slug]) }}"
                               class="transition hover:text-brand-primary">{{ $article->category->name }}</a>
                        </li>
                    @endif
                </ol>
            </nav>

            <span class="mt-4 inline-block rounded-full bg-brand-primary/10 px-3 py-1 text-[11px] font-semibold text-brand-primary">
                {{ $article->category?->name ?? 'Literasi UMKM' }}
            </span>

            <h1 class="mt-3 text-3xl font-bold leading-tight tracking-tight text-brand-text sm:text-4xl">
                {{ $article->title }}
            </h1>

            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                <span>Oleh <span class="font-semibold text-slate-700">{{ $article->author?->name ?? 'Redaksi E-Wedu' }}</span></span>
                <span aria-hidden="true">&middot;</span>
                <span>{{ $article->published_at_formatted ?? 'Belum terbit' }}</span>
                <span aria-hidden="true">&middot;</span>
                <span>{{ $article->readingTimeMinutes() }} menit baca</span>
                <span aria-hidden="true">&middot;</span>
                <span>{{ number_format((int) $article->views) }} kali dibaca</span>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-3">
            <article class="lg:col-span-2">
                @if ($article->coverImageUrl())
                    <img src="{{ $article->coverImageUrl() }}" alt="Ilustrasi artikel {{ $article->title }}"
                         class="mb-6 w-full rounded-xl border border-slate-200 object-cover">
                @endif

                @if ($article->excerpt)
                    <p class="border-l-4 border-brand-accent bg-white px-4 py-3 text-base font-medium italic leading-relaxed text-slate-700">
                        {{ $article->excerpt }}
                    </p>
                @endif

                {{-- Isi artikel: diketik admin, di-escape lebih dulu lalu baris baru dipertahankan --}}
                <div class="mt-6 space-y-4 text-base leading-relaxed text-slate-700">
                    {!! nl2br(e($article->body)) !!}
                </div>

                @include('articles.partials.share')
            </article>

            @include('articles.partials.aside')
        </div>
    </div>
@endsection
