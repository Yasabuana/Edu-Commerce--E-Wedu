@extends('layouts.public')

@section('title', $umkmProfile->business_name.' — Mitra UMKM '.config('app.name', 'E-Wedu'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) ($umkmProfile->description ?: 'Profil mitra UMKM terverifikasi E-Wedu di Magelang.')), 150))

@section('content')
    @include('umkm.partials.hero')

    {{-- Isi profil: cerita usaha + katalog mitra + informasi pendukung --}}
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                @include('umkm.partials.story')
                @include('umkm.partials.products')
            </div>

            @include('umkm.partials.aside')
        </div>
    </div>
@endsection
