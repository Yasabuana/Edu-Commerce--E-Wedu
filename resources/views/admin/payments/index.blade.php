@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')
@section('page_title', 'Verifikasi Pembayaran QRIS')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            Menampilkan <span class="font-semibold">{{ $orders->total() }}</span> pesanan menunggu verifikasi
        </p>

        <form method="GET" action="{{ route('admin.payments.index') }}" class="flex items-center gap-2">
            <input type="text" name="cari" placeholder="Cari nomor pesanan..."
                   value="{{ request('cari') }}"
                   class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
            <button type="submit"
                    class="rounded-lg bg-brand-primary px-3 py-1.5 text-sm font-medium text-white transition hover:bg-blue-900">
                Cari
            </button>
            @if (request('cari'))
                <a href="{{ route('admin.payments.index') }}"
                   class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
            @endif
        </form>
    </div>

    @if ($orders->isEmpty())
        <x-empty-state icon="document" title="Tidak ada pesanan menunggu verifikasi"
                       description="Semua pembayaran QRIS sudah diverifikasi atau belum ada yang mengunggah bukti." />
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Pesanan</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Pembeli</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Total</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Metode</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Tanggal</th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($orders as $order)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.payments.show', $order) }}"
                                   class="font-medium text-brand-primary hover:underline">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $order->customer_name ?: $order->user?->name }}</p>
                                <p class="text-xs text-slate-500">{{ $order->customer_phone ?: $order->user?->phone }}</p>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $order->total_formatted }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700">
                                    {{ $order->paymentMethodLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.payments.show', $order) }}"
                                   class="rounded-lg bg-brand-primary px-3 py-1.5 text-xs font-medium text-white transition hover:bg-blue-900">
                                    Lihat & Verifikasi
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
@endsection