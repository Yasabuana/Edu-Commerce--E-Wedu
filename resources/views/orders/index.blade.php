@extends('layouts.public')

@section('title', 'Pesanan Saya — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Daftar pesanan Anda di E-Wedu beserta status terkini.')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li class="font-medium text-brand-text">Pesanan Saya</li>
            </ol>
        </nav>

        <h1 class="mt-4 text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">Pesanan Saya</h1>
        <p class="mt-1 text-sm text-slate-600">Daftar seluruh pesanan yang pernah Anda buat.</p>

        @if ($orders->isEmpty())
            <x-empty-state icon="box" title="Belum ada pesanan"
                           description="Anda belum membuat pesanan apapun. Yuk belanja di katalog produk UMKM Magelang!" />
        @else
            <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Pesanan</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-700">Total</th>
                            <th scope="col" class="px-4 py-3 text-center font-semibold text-slate-700">Status</th>
                            <th scope="col" class="text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($orders as $order)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium">#{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ $order->total_formatted }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded px-2 py-0.5 text-xs font-medium {{ $order->statusBadgeClass() }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('orders.show', $order) }}"
                                       class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
