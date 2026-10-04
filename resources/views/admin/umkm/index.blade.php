@extends('layouts.admin')

@section('title', 'Verifikasi Mitra UMKM')
@section('page_title', 'Verifikasi Mitra UMKM')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <p class="text-sm text-slate-600">
                Menampilkan <span class="font-semibold">{{ $profiles->total() }}</span> mitra
            </p>

            <div class="flex gap-1 rounded-lg border border-slate-200 bg-white p-0.5">
                <a href="{{ route('admin.umkm.index', ['filter' => 'pending']) }}"
                   class="rounded-md px-3 py-1 text-xs font-medium transition {{ $filter === 'pending' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    Menunggu
                </a>
                <a href="{{ route('admin.umkm.index', ['filter' => 'verified']) }}"
                   class="rounded-md px-3 py-1 text-xs font-medium transition {{ $filter === 'verified' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    Terverifikasi
                </a>
                <a href="{{ route('admin.umkm.index', ['filter' => 'all']) }}"
                   class="rounded-md px-3 py-1 text-xs font-medium transition {{ $filter === 'all' ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    Semua
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.umkm.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="text" name="cari" placeholder="Cari nama usaha..."
                   value="{{ request('cari') }}"
                   class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
            <button type="submit"
                    class="rounded-lg bg-brand-primary px-3 py-1.5 text-sm font-medium text-white transition hover:bg-blue-900">Cari</button>
        </form>
    </div>

    @if ($profiles->isEmpty())
        <x-empty-state icon="search" title="Tidak ada mitra {{ $filter === 'pending' ? 'menunggu verifikasi' : '' }}"
                       description="{{ $filter === 'pending' ? 'Semua pendaftar sudah diverifikasi.' : 'Belum ada UMKM yang terdaftar.' }}" />
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Nama Usaha</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Pemilik</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Kontak</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Produk</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Daftar</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-700">Aksi</th>
                    </tr>
                    @foreach ($profiles as $profile)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $profile->business_name }}</td>
                            <td class="px-4 py-3">{{ $profile->owner_name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $profile->phone ?: $profile->user?->phone }}</td>
                            <td class="px-4 py-3 text-center">{{ $profile->products->count() }}</td>
                            <td class="px-4 py-3">
                                @if ($profile->is_verified)
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Terverifikasi</span>
                                @else
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Menunggu</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $profile->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.umkm.show', $profile) }}"
                                       class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                                        Detail
                                    </a>
                                    @if (! $profile->is_verified)
                                        <form action="{{ route('admin.umkm.approve', $profile) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Setujui {{ $profile->business_name }} sebagai mitra terverifikasi?')"
                                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-700">
                                                Setujui
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $profiles->links() }}</div>
    @endif
@endsection
                </thead>
                <tbody class="divide-y divide-slate-100">
