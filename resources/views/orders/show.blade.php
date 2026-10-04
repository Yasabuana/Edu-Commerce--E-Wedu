@extends('layouts.public')

@section('title', 'Pesanan '.$order->order_number.' — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Ringkasan pesanan E-Wedu beserta detail pengiriman dan status pembayaran.')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li class="font-medium text-brand-text">Pesanan</li>
            </ol>
        </nav>

        <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Pesanan dibuat</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">{{ $order->order_number }}</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Dibuat {{ $order->created_at?->translatedFormat('d F Y, H:i') }} WIB
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $order->statusBadgeClass() }}">
                    {{ $order->statusLabel() }}
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $order->paymentStatusBadgeClass() }}">
                    {{ $order->paymentStatusLabel() }}
                </span>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            {{-- ============ ITEM PESANAN ============ --}}
            <section class="lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <header class="border-b border-slate-100 px-5 py-3">
                        <h2 class="text-base font-semibold text-brand-text">Produk ({{ (int) $order->items_count }} item)</h2>
                    </header>

                    <ul class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <li class="flex items-start justify-between gap-4 p-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-brand-text">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $item->umkmProfile?->business_name ?? 'Mitra UMKM' }}
                                        &middot; SKU {{ $item->product_sku ?: '—' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ number_format((int) $item->qty) }} &times; {{ $item->price_formatted }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-brand-text">{{ $item->subtotal_formatted }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- ============ RIWAYAT STATUS ============ --}}
                @if ($order->statusHistories->isNotEmpty())
                    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <header class="border-b border-slate-100 px-5 py-3">
                            <h2 class="text-base font-semibold text-brand-text">Riwayat pesanan</h2>
                        </header>

                        <ol class="divide-y divide-slate-100">
                            @foreach ($order->statusHistories as $history)
                                <li class="flex items-start gap-3 p-4">
                                    <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-primary"></span>
                                    <div>
                                        <p class="text-sm font-semibold text-brand-text">{{ $history->statusLabel() }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $history->created_at?->translatedFormat('d F Y, H:i') }} WIB
                                        </p>
                                        @if ($history->note)
                                            <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $history->note }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </section>

            {{-- ============ RINGKASAN & PENGIRIMAN ============ --}}
            <aside class="space-y-6 lg:col-span-1">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-brand-text">Ringkasan pembayaran</h2>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Subtotal</dt>
                            <dd class="font-medium text-brand-text">{{ $order->subtotal_formatted }}</dd>
                        </div>

                        @if ((int) $order->discount > 0)
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">Diskon</dt>
                                <dd class="font-medium text-green-700">- Rp {{ number_format((int) $order->discount, 0, ',', '.') }}</dd>
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Ongkir</dt>
                            <dd class="font-medium text-brand-text">{{ $order->shipping_fee_formatted }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <dt class="text-base font-semibold text-brand-text">Total</dt>
                            <dd class="text-base font-bold text-brand-primary">{{ $order->total_formatted }}</dd>
                        </div>
                    </dl>

                    <p class="mt-3 text-xs text-slate-500">Metode pembayaran: {{ $order->paymentMethodLabel() }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-brand-text">Pengiriman</h2>
                    <p class="mt-2 text-sm font-semibold text-brand-text">{{ $order->shippingMethodLabel() }}</p>

                    @if ($order->deliveryPoint)
                        <p class="mt-1 text-sm text-slate-600">{{ $order->deliveryPoint->name }} ({{ $order->deliveryPoint->code }})</p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $order->shipping_address }}</p>
                        @if ($order->deliveryPoint->operation_hours)
                            <p class="mt-1 text-xs text-slate-400">Jam layanan {{ $order->deliveryPoint->operation_hours }}</p>
                        @endif
                    @else
                        <p class="mt-1 text-sm text-slate-600">{{ $order->shipping_address }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Jarak {{ number_format((float) $order->shipping_distance_km, 2) }} km dari Kampus Untidar
                        </p>
                    @endif

                    <div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                        <p><span class="font-semibold text-brand-text">Penerima:</span> {{ $order->customer_name }}</p>
                        <p class="mt-1"><span class="font-semibold text-brand-text">Kontak:</span> {{ $order->customer_phone }}</p>
                        @if ($order->customer_email)
                            <p class="mt-1"><span class="font-semibold text-brand-text">Email:</span> {{ $order->customer_email }}</p>
                        @endif
                    </div>

                    @if ($order->customer_note)
                        <p class="mt-3 text-xs leading-relaxed text-slate-500">Catatan: {{ $order->customer_note }}</p>
                    @endif
                </div>

                <div class="rounded-xl border border-dashed border-brand-primary/40 bg-brand-primary/5 p-5 text-xs leading-relaxed text-slate-600">
                    <p class="font-semibold text-brand-text">Langkah selanjutnya</p>
                    <p class="mt-1">
                        @if ($order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD)
                            Siapkan pembayaran tunai saat pesanan diterima/diambil.
                        @elseif ($order->needsPayment())
                            <a href="{{ route('orders.pay', $order) }}"
                               class="inline-block rounded-lg bg-brand-accent px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                                Bayar Sekarang (QRIS)
                            </a>
                        @else
                            Bukti pembayaran sudah diunggah, menunggu verifikasi admin.
                        @endif
                    </p>
                    <p class="mt-2">
                        Tim E-Wedu akan mengonfirmasi pesananmu secepatnya.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('products.index') }}"
                       class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900">
                        Lanjut belanja
                    </a>
                    <a href="{{ route('home') }}"
                       class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                        Beranda
                    </a>
                </div>
            </aside>
        </div>
    </div>
@endsection