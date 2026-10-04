@extends('layouts.public')

@section('title', 'Bayar '.$order->order_number.' — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Lakukan pembayaran QRIS untuk pesanan E-Wedu dan unggah bukti transfer.')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('orders.show', $order) }}" class="transition hover:text-brand-primary">Pesanan</a></li>
                <li aria-hidden="true">/</li>
                <li class="font-medium text-brand-text">Pembayaran</li>
            </ol>
        </nav>

        <h1 class="mt-2 text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">Pembayaran QRIS</h1>
        <p class="mt-1 text-sm text-slate-600">
            Pesanan <strong>{{ $order->order_number }}</strong> — Lakukan transfer sesuai total belanja, lalu unggah bukti bayar.
        </p>

        {{-- ============ TOTAL BELANJA (BESAR) ============ --}}
        <div class="mt-6 rounded-xl border-2 border-brand-primary bg-brand-primary/5 p-6 text-center shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-600">Total yang harus dibayar</p>
            <p class="mt-2 text-4xl font-extrabold tracking-tight text-brand-text sm:text-5xl">
                Rp {{ number_format((int) $order->total, 0, ',', '.') }}
            </p>
            <p class="mt-2 text-xs text-slate-500">
                Transfer tepat nominal di atas agar verifikasi berjalan lancar.
            </p>
        </div>

        {{-- ============ QRIS STATIS (ASLI) ============ --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-brand-text">Scan QRIS ini untuk membayar</h2>
            <p class="mt-1 text-xs text-slate-500">
                Merchant: <strong>E-Wedu UMKM Magelang</strong>
            </p>

            <div class="mx-auto mt-4 flex max-w-sm items-center justify-center">
                <img src="{{ asset('images/qris-ewedu.png') }}"
                     alt="QRIS E-Wedu UMKM Magelang"
                     class="h-auto w-full max-w-[280px] rounded-lg border border-slate-200 object-contain shadow-sm sm:max-w-[320px]">
            </div>

            <p class="mt-4 text-center text-xs text-slate-500">
                Atau hubungi
                <a href="https://wa.me/{{ $whatsappAdmin ?? '6288225435927' }}?text=Halo%20E-Wedu%2C%20saya%20ingin%20transfer%20untuk%20pesanan%20{{ $order->order_number }}"
                   class="font-semibold text-brand-primary underline transition hover:text-brand-accent"
                   target="_blank" rel="noopener noreferrer">
                    WhatsApp Admin
                </a>
                jika mengalami kendala saat scan.
            </p>
        </div>
        {{-- ============ FORM UPLOAD BUKTI BAYAR ============ --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-brand-text">Unggah Bukti Pembayaran</h2>
            <p class="mt-1 text-xs text-slate-500">
                Screenshot atau foto bukti transfer dari m-banking / ATM. Format JPG/PNG, maksimal 2 MB.
            </p>

            <form method="POST" action="{{ route('orders.pay.upload', $order) }}" enctype="multipart/form-data" class="mt-4">
                @csrf

                <div class="rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-brand-primary/50">
                    <label for="receipt" class="flex cursor-pointer flex-col items-center gap-2">
                        <svg class="h-10 w-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span class="text-sm font-semibold text-brand-text">Klik untuk pilih file</span>
                        <span class="text-xs text-slate-400">JPG atau PNG, maks 2 MB</span>
                    </label>
                    <input id="receipt" name="receipt" type="file" accept="image/jpeg,image/png"
                           class="hidden"
                           onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Belum ada file dipilih'">
                    <p id="file-name" class="mt-2 text-xs font-medium text-brand-primary">Belum ada file dipilih</p>
                </div>

                @error('receipt')
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="mt-4 w-full rounded-lg bg-brand-accent px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-600">
                    Unggah Bukti Pembayaran
                </button>
            </form>
        </div>

        {{-- ============ LANGKAH BERIKUTNYA ============ --}}
        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-xs leading-relaxed text-slate-600">
            <p class="font-semibold text-brand-text">Setelah mengunggah bukti:</p>
            <ol class="mt-2 list-inside list-decimal space-y-1">
                <li>Tim E-Wedu akan memverifikasi pembayaranmu (1x24 jam kerja).</li>
                <li>Status pesanan berubah menjadi <strong>"Lunas"</strong> setelah diverifikasi.</li>
                <li>Kamu akan mendapat notifikasi melalui WhatsApp/email (jika diisi).</li>
            </ol>
            <p class="mt-2">
                Ada kendala? Hubungi
                <a href="https://wa.me/{{ $whatsappAdmin ?? '6288225435927' }}?text=Halo%20E-Wedu%2C%20saya%20butuh%20bantuan%20pesanan%20{{ $order->order_number }}"
                   class="font-semibold text-brand-primary underline transition hover:text-brand-accent"
                   target="_blank" rel="noopener noreferrer">
                    WhatsApp Admin
                </a>.
            </p>
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
            <a href="{{ route('orders.show', $order) }}"
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
                Kembali ke detail pesanan
            </a>
        </div>
    </div>
@endsection