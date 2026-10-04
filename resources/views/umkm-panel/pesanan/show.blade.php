@extends('layouts.umkm')

@section('title', 'Detail Pesanan #'.$orderItem->order->order_number)
@section('page_title', 'Detail Pesanan — #'.$orderItem->order->order_number)

@section('header_actions')
    <a href="{{ route('umkm.orders.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
        &larr; Kembali
    </a>
@endsection

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Informasi Pesanan">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-500">Nomor Pesanan</dt><dd class="font-medium">#{{ $orderItem->order->order_number }}</dd></div>
                    <div><dt class="text-slate-500">Status</dt><dd><span class="rounded px-2 py-0.5 text-xs font-medium {{ $orderItem->order->statusBadgeClass() }}">{{ $orderItem->order->statusLabel() }}</span></dd></div>
                    <div><dt class="text-slate-500">Pembeli</dt><dd class="font-medium">{{ $orderItem->order->customer_name ?: $orderItem->order->user?->name }}</dd></div>
                    <div><dt class="text-slate-500">Kontak</dt><dd>{{ $orderItem->order->customer_phone ?: $orderItem->order->user?->phone }}</dd></div>
                    <div><dt class="text-slate-500">Tanggal Pesan</dt><dd>{{ $orderItem->order->created_at->translatedFormat('d M Y, H:i') }}</dd></div>
                    <div><dt class="text-slate-500">Metode Pembayaran</dt><dd>{{ $orderItem->order->paymentMethodLabel() }}</dd></div>
                    <div><dt class="text-slate-500">Metode Kirim</dt><dd>{{ $orderItem->order->shippingMethodLabel() }}</dd></div>
                    @if ($orderItem->order->shipping_address)
                    <div class="col-span-2"><dt class="text-slate-500">Alamat Kirim</dt><dd>{{ $orderItem->order->shipping_address }}</dd></div>
                    @endif
                </dl>
            </x-card>

            <x-card title="Detail Produk">
                <div class="flex items-start gap-4">
                    @if ($orderItem->product_thumbnail)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($orderItem->product_thumbnail) }}"
                             alt="{{ $orderItem->product_name }}" class="h-20 w-20 rounded-lg object-cover">
                    @endif
                    <div class="flex-1">
                        <p class="font-semibold">{{ $orderItem->product_name }}</p>
                        <p class="text-sm text-slate-500">SKU: {{ $orderItem->product_sku ?: '-' }}</p>
                        <div class="mt-2 grid grid-cols-3 gap-4 text-sm">
                            <div><span class="text-slate-500">Harga:</span> <span class="font-medium">{{ $orderItem->price_formatted }}</span></div>
                            <div><span class="text-slate-500">Jumlah:</span> <span class="font-medium">{{ $orderItem->qty }}</span></div>
                            <div><span class="text-slate-500">Subtotal:</span> <span class="font-medium">{{ $orderItem->subtotal_formatted }}</span></div>
                        </div>
                    </div>
                </div>
            </x-card>

            @if ($orderItem->order->customer_note)
                <x-card title="Catatan Pembeli">
                    <p class="text-sm text-slate-600">{{ $orderItem->order->customer_note }}</p>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="Ringkasan Pesanan">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt>Subtotal</dt><dd class="font-medium">{{ $orderItem->order->subtotal_formatted }}</dd></div>
                    <div class="flex justify-between"><dt>Ongkos Kirim</dt><dd class="font-medium">{{ $orderItem->order->shipping_fee_formatted }}</dd></div>
                    <hr class="border-slate-200">
                    <div class="flex justify-between text-base"><dt class="font-semibold">Total</dt><dd class="font-bold">{{ $orderItem->order->total_formatted }}</dd></div>
                </dl>
            </x-card>

            @if ($orderItem->order->payment_status === \App\Models\Order::PAYMENT_AWAITING_VERIFICATION && $orderItem->order->receipt_url)
                <x-card title="Bukti Pembayaran">
                    <a href="{{ $orderItem->order->receipt_url }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $orderItem->order->receipt_url }}"
                             alt="Bukti bayar" class="w-full rounded-lg border object-contain">
                    </a>
                </x-card>
            @endif

            @if ($orderItem->order->statusHistories->isNotEmpty())
                <x-card title="Riwayat Status">
                    <div class="space-y-2">
                        @foreach ($orderItem->order->statusHistories as $history)
                            <div class="text-xs text-slate-600">
                                <span class="font-medium">{{ $history->statusLabel() }}</span>
                                &mdash; {{ $history->created_at->diffForHumans() }}
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>
@endsection
x
