@extends('layouts.admin')

@section('title', 'Verifikasi #'.$order->order_number)
@section('page_title', 'Verifikasi Pembayaran — #'.$order->order_number)

@section('header_actions')
    <a href="{{ route('admin.payments.index') }}"
       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
        &larr; Kembali
    </a>
@endsection

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Informasi Pesanan">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-500">Status Pesanan</dt><dd><span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">{{ $order->statusLabel() }}</span></dd></div>
                    <div><dt class="text-slate-500">Status Pembayaran</dt><dd><span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">{{ $order->paymentStatusLabel() }}</span></dd></div>
                    <div><dt class="text-slate-500">Pembeli</dt><dd class="font-medium">{{ $order->customer_name ?: $order->user?->name }}</dd></div>
                    <div><dt class="text-slate-500">Kontak</dt><dd class="font-medium">{{ $order->customer_phone ?: $order->user?->phone }}</dd></div>
                    <div><dt class="text-slate-500">Tanggal Pesan</dt><dd>{{ $order->created_at->translatedFormat('d M Y, H:i') }}</dd></div>
                    <div><dt class="text-slate-500">Metode Bayar</dt><dd>{{ $order->paymentMethodLabel() }}</dd></div>
                    <div><dt class="text-slate-500">Total</dt><dd class="text-base font-bold">{{ $order->total_formatted }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Item Pesanan">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium">Produk</th><th class="px-3 py-2 text-left font-medium">Harga</th><th class="px-3 py-2 text-center font-medium">Qty</th><th class="px-3 py-2 text-right font-medium">Subtotal</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($order->items as $item)
                                <tr><td class="px-3 py-2 font-medium">{{ $item->product_name }}</td><td class="px-3 py-2">{{ $item->price_formatted }}</td><td class="px-3 py-2 text-center">{{ $item->qty }}</td><td class="px-3 py-2 text-right font-medium">{{ $item->subtotal_formatted }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50"><tr><td colspan="3" class="px-3 py-2 text-right font-semibold">Total</td><td class="px-3 py-2 text-right font-bold">{{ $order->total_formatted }}</td></tr></tfoot>
                    </table>
                </div>
            </x-card>
        </div>
        {{-- Kanan: Bukti bayar & Aksi --}}
        <div class="space-y-6">
            <x-card title="Bukti Pembayaran">
                @if ($order->receipt_url)
                    <div class="overflow-hidden rounded-lg border border-slate-200">
                        <a href="{{ $order->receipt_url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $order->receipt_url }}"
                                 alt="Bukti bayar {{ $order->order_number }}"
                                 class="w-full cursor-zoom-in object-contain transition hover:opacity-90">
                        </a>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Klik gambar untuk ukuran penuh.</p>
                @else
                    <p class="text-sm text-slate-500 italic">Belum ada bukti pembayaran.</p>
                @endif
            </x-card>

            <x-card title="Aksi Verifikasi">
                <div class="space-y-4">
                    <form action="{{ route('admin.payments.approve', $order) }}" method="POST">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Setujui pembayaran ini? Pesanan akan dikonfirmasi.')"
                                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Setujui Pembayaran
                        </button>
                    </form>

                    <hr class="border-slate-200">

                    <form action="{{ route('admin.payments.reject', $order) }}" method="POST"
                          x-data="{ alasan: '' }">
                        @csrf
                        <label for="alasan" class="block text-sm font-medium text-slate-700">Alasan penolakan</label>
                        <textarea id="alasan" name="alasan" rows="2" x-model="alasan" required
                                  placeholder="Contoh: Bukti tidak jelas..."
                                  class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-400 focus:ring-1 focus:ring-red-400"></textarea>
                        <button type="submit"
                                x-show="alasan.trim().length > 0"
                                onclick="return confirm('Tolak pembayaran ini?')"
                                class="mt-2 w-full rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                            Tolak Pembayaran
                        </button>
                    </form>
                </div>
            </x-card>

            @if ($order->statusHistories->isNotEmpty())
                <x-card title="Riwayat">
                    <div class="space-y-2">
                        @foreach ($order->statusHistories as $history)
                            <div class="text-xs text-slate-600">
                                <span class="font-medium">{{ $history->statusLabel() }}</span>
                                &mdash;
                                @if ($history->note)<span>{{ $history->note }}</span>@endif
                                <span class="text-slate-400">({{ $history->created_at->diffForHumans() }})</span>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>
@endsection
