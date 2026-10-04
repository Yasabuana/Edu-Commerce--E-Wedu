@extends('layouts.umkm')

@section('title', 'Pesanan Masuk')
@section('page_title', 'Pesanan Masuk')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            Menampilkan <span class="font-semibold">{{ $items->total() }}</span> item pesanan untuk produk Anda
        </p>

        <form method="GET" action="{{ route('umkm.orders.index') }}" class="flex items-center gap-2">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">Semua status</option>
                <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>Dikonfirmasi</option>
                <option value="processing" @selected(request('status') === 'processing')>Diproses</option>
                <option value="shipped" @selected(request('status') === 'shipped')>Dikirim</option>
                <option value="delivered" @selected(request('status') === 'delivered')>Terkirim</option>
                <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
            </select>
            <button type="submit"
                    class="rounded-lg bg-brand-primary px-3 py-1.5 text-sm font-medium text-white transition hover:bg-blue-900">Filter</button>
        </form>
    </div>

    @if ($items->isEmpty())
        <x-empty-state icon="box" title="Belum ada pesanan masuk"
                       description="Belum ada pembeli yang memesan produk Anda. Pastikan produk aktif dan stok tersedia." />
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Pesanan</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Produk</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Pembeli</th>
                        <th scope="col" class="px-4 py-3 text-center font-semibold text-slate-700">Qty</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-700">Subtotal</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Tanggal</th>
                        <th scope="col" class="text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('umkm.orders.show', $item) }}"
                                   class="font-medium text-brand-primary hover:underline">#{{ $item->order->order_number }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $item->product_name }}</p>
                                <p class="text-xs text-slate-500">{{ $item->product_sku }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p>{{ $item->order->customer_name ?: $item->order?->user?->name }}</p>
                                <p class="text-xs text-slate-500">{{ $item->order->customer_phone ?: $item->order?->user?->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">{{ $item->qty }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $item->subtotal_formatted }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded px-2 py-0.5 text-xs font-medium {{ $item->order->statusBadgeClass() }}">
                                    {{ $item->order->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $item->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('umkm.orders.show', $item) }}"
                                   class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $items->links() }}</div>
    @endif
@endsection
