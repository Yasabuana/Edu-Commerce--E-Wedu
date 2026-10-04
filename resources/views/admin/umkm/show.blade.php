@extends('layouts.admin')

@section('title', 'Detail '.$umkmProfile->business_name)
@section('page_title', $umkmProfile->business_name)

@section('header_actions')
    <a href="{{ route('admin.umkm.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
        &larr; Kembali
    </a>
@endsection

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Informasi Usaha">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-500">Nama Usaha</dt><dd class="font-medium">{{ $umkmProfile->business_name }}</dd></div>
                    <div><dt class="text-slate-500">Pemilik</dt><dd class="font-medium">{{ $umkmProfile->owner_name }}</dd></div>
                    <div><dt class="text-slate-500">Kategori</dt><dd>{{ $umkmProfile->category_label ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500">Status</dt><dd>@if ($umkmProfile->is_verified) <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Terverifikasi</span> @else <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Menunggu</span> @endif</dd></div>
                    <div class="col-span-2"><dt class="text-slate-500">Deskripsi</dt><dd class="text-slate-600">{{ $umkmProfile->description ?? '-' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Kontak">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-500">Telepon</dt><dd>{{ $umkmProfile->phone ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">WhatsApp</dt><dd>{{ $umkmProfile->whatsapp ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">Instagram</dt><dd>{{ $umkmProfile->instagram ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">Website</dt><dd>{{ $umkmProfile->website ?: '-' }}</dd></div>
                </dl>
            </x-card>

            @if ($umkmProfile->products->isNotEmpty())
                <x-card title="Produk ({{ $umkmProfile->products->count() }})">
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($umkmProfile->products as $product)
                            <div class="rounded-lg border border-slate-200 p-3">
                                <p class="font-medium">{{ $product->name }}</p>
                                <p class="text-sm text-slate-500">{{ $product->price_formatted }} &middot; Stok: {{ $product->stock }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="Aksi">
                @if (! $umkmProfile->is_verified)
                    <form action="{{ route('admin.umkm.approve', $umkmProfile) }}" method="POST">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Setujui {{ $umkmProfile->business_name }} sebagai mitra terverifikasi?')"
                                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Setujui UMKM Ini
                        </button>
                    </form>
                @else
                    <div class="rounded-lg bg-emerald-50 p-4 text-center text-sm text-emerald-700">
                        Mitra sudah diverifikasi pada {{ $umkmProfile->verified_at->translatedFormat('d M Y, H:i') }}.
                    </div>
                @endif
            </x-card>

            <x-card title="Akun User">
                <dl class="text-sm">
                    <div><dt class="text-slate-500">Nama</dt><dd class="font-medium">{{ $umkmProfile->user?->name ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500">Email</dt><dd>{{ $umkmProfile->user?->email ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500">Aktif</dt><dd>{{ $umkmProfile->user?->is_active ? 'Ya' : 'Tidak' }}</dd></div>
                </dl>
            </x-card>
        </div>
    </div>
@endsection
