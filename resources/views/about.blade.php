@extends('layouts.public')

@section('title', 'Tentang E-Wedu — Edu-Commerce UMKM Magelang')
@section('meta_description', 'E-Wedu menghubungkan produk UMKM Magelang dengan mahasiswa dan masyarakat lewat katalog daring, literasi kewirausahaan, dan logistik mandiri.')

@section('content')
    <x-page-header eyebrow="Tentang Kami" title="Edu-Commerce untuk UMKM Magelang"
                   subtitle="{{ $tagline ?: 'Marketplace & Literasi UMKM Magelang' }} — satu platform untuk berbelanja produk lokal sekaligus belajar berwirausaha."
                   :breadcrumbs="[
                       ['label' => 'Beranda', 'url' => route('home')],
                       ['label' => 'Tentang'],
                   ]" />

    {{-- ============================ MISI ============================ --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">
                    Kenapa {{ $siteName }} hadir?
                </h2>

                <div class="mt-4 space-y-4 text-sm leading-relaxed text-slate-700 sm:text-base">
                    <p>
                        UMKM Magelang punya produk berkualitas — kopi, batik, anyaman bambu, sampai olahan rempah —
                        tetapi sering terkendala jangkauan pemasaran dan pengetahuan digital. Di sisi lain, mahasiswa
                        dan masyarakat sekitar kesulitan menemukan produk lokal dalam satu tempat terpercaya.
                    </p>
                    <p>
                        <strong>{{ $siteName }}</strong> menjembatani keduanya: katalog produk mitra terverifikasi,
                        pengiriman logistik mandiri dengan titik ambil gratis ongkir, dan kanal literasi kewirausahaan
                        yang praktis. Setiap transaksi menjadi pembelajaran nyata tentang pengelolaan usaha.
                    </p>
                </div>

                <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['label' => 'Produk aktif', 'nilai' => $stats['produk']],
                        ['label' => 'Mitra UMKM', 'nilai' => $stats['mitra']],
                        ['label' => 'Artikel literasi', 'nilai' => $stats['artikel']],
                        ['label' => 'Titik gratis ongkir', 'nilai' => $stats['titik']],
                    ] as $item)
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                            <dt class="text-[11px] uppercase tracking-wider text-slate-500">{{ $item['label'] }}</dt>
                            <dd class="mt-1 text-2xl font-bold text-brand-primary">{{ number_format((int) $item['nilai']) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    [
                        'judul' => 'Belanja produk lokal',
                        'teks' => 'Katalog rapi per kategori dengan harga, stok, dan asal UMKM yang jelas.',
                    ],
                    [
                        'judul' => 'Literasi kewirausahaan',
                        'teks' => 'Artikel praktis: foto produk, pembukuan sederhana, hingga strategi pemasaran.',
                    ],
                    [
                        'judul' => 'Logistik mandiri',
                        'teks' => 'Titik pengambilan gratis ongkir dan kalkulasi tarif otomatis berbasis jarak.',
                    ],
                    [
                        'judul' => 'Mitra terverifikasi',
                        'teks' => 'Profil UMKM diverifikasi admin agar pembeli berbelanja dengan tenang.',
                    ],
                ] as $fitur)
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-sm font-semibold text-brand-text">{{ $fitur['judul'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $fitur['teks'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========================= ALUR PEMESANAN ========================= --}}
    <section class="border-y border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Cara kerja" title="Empat langkah dari pesan sampai pesanan diambil"
                               subtitle="Alur belanja dirancang sederhana agar pembeli baru dan pelaku usaha sama-sama mudah memakainya." />

            <ol class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['judul' => 'Pilih produk', 'teks' => 'Jelajahi katalog UMKM Magelang, cek stok, harga, dan asal usahanya.'],
                    ['judul' => 'Checkout', 'teks' => 'Pilih titik pengambilan gratis ongkir atau alamat kustom dengan tarif jarak.'],
                    ['judul' => 'Bayar', 'teks' => 'Bayar melalui QRIS atau COD, lalu unggah bukti pembayaran untuk diverifikasi admin.'],
                    ['judul' => 'Ambil pesanan', 'teks' => 'Terima notifikasi, ambil pesanan di titik pengiriman, dan lacak statusnya.'],
                ] as $index => $langkah)
                    <li class="rounded-xl border border-slate-200 bg-brand-background p-5">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-primary text-sm font-bold text-white">
                            {{ $index + 1 }}
                        </span>
                        <h3 class="mt-3 text-sm font-semibold text-brand-text">{{ $langkah['judul'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $langkah['teks'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ====================== TITIK PENGIRIMAN & TARIF ====================== --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-section-heading eyebrow="Logistik mandiri" title="Titik pengambilan gratis ongkir"
                                   subtitle="Titik pengiriman resmi E-Wedu di Kota &amp; Kabupaten Magelang — tanpa biaya kirim." />

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @forelse ($points as $point)
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-sm font-semibold text-brand-text">{{ $point->name }}</h3>
                                <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700">
                                    Gratis
                                </span>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $point->address }}</p>
                            <p class="mt-2 text-[11px] text-slate-500">
                                Jam layanan: {{ $point->operation_hours ?: 'Sesuai jadwal loket' }}
                            </p>
                            @if ($point->notes)
                                <p class="mt-1 text-[11px] text-slate-500">{{ $point->notes }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="sm:col-span-2">
                            <x-empty-state title="Titik pengiriman belum tersedia"
                                           description="Admin belum mengisi data titik pengiriman." />
                        </div>
                    @endforelse
                </div>
            </div>

            <x-card title="Tarif pengiriman" subtitle="Otomatis dari jarak Kampus Tuguran">
                <dl class="divide-y divide-slate-100 text-sm">
                    @foreach ([
                        ['label' => 'Titik gratis ongkir', 'nilai' => 'Rp 0'],
                        ['label' => 'Biaya dasar', 'nilai' => 'Rp '.number_format($shipping['base_fee'], 0, ',', '.')],
                        ['label' => 'Tarif per km', 'nilai' => 'Rp '.number_format($shipping['per_km'], 0, ',', '.')],
                        ['label' => 'Radius gratis', 'nilai' => $shipping['free_radius'].' km'],
                        ['label' => 'Jarak maksimum', 'nilai' => $shipping['max_km'].' km'],
                    ] as $baris)
                        <div class="flex items-center justify-between gap-3 py-2">
                            <dt class="text-slate-600">{{ $baris['label'] }}</dt>
                            <dd class="font-semibold text-brand-text">{{ $baris['nilai'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    Belanja dalam radius {{ $shipping['free_radius'] }} km dari Kampus Tuguran bebas biaya kirim.
                    Di luar itu, tarif dihitung dari biaya dasar + jarak &times; tarif per km (rumus Haversine).
                </p>
            </x-card>
        </div>
    </section>

    {{-- ============================== FAQ ============================== --}}
    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8" x-data="{ buka: 0 }">
            <x-section-heading eyebrow="FAQ" title="Pertanyaan yang sering diajukan" />

            <div class="mt-6 space-y-3">
                @foreach ([
                    ['tanya' => 'Apakah berbelanja di E-Wedu dikenakan biaya kirim?',
                     'jawab' => 'Pengambilan di '.$stats['titik'].' titik resmi E-Wedu gratis. Hanya pengiriman ke alamat kustom yang dihitung dari jarak.'],
                    ['tanya' => 'Bagaimana saya tahu produk berasal dari UMKM asli?',
                     'jawab' => 'Setiap mitra memiliki halaman profil berisi data pemilik, alamat usaha, dan status verifikasi admin.'],
                    ['tanya' => 'Metode pembayaran apa saja yang tersedia?',
                     'jawab' => 'QRIS (unggah bukti transfer untuk diverifikasi admin) dan COD untuk titik pengambilan tertentu.'],
                    ['tanya' => 'Apakah artikel literasinya gratis?',
                     'jawab' => 'Ya. Seluruh artikel literasi UMKM bisa dibaca bebas, tanpa perlu membuat akun.'],
                    ['tanya' => 'Bagaimana cara menjadi mitra UMKM?',
                     'jawab' => 'Daftar akun UMKM, lengkapi profil usaha beserta titik lokasi, lalu tunggu verifikasi admin sebelum produk tayang.'],
                ] as $index => $faq)
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <button type="button" @click="buka = (buka === {{ $index }} ? null : {{ $index }})"
                                class="flex w-full items-center justify-between gap-4 bg-white px-5 py-4 text-left text-sm font-semibold text-brand-text transition hover:text-brand-primary">
                            {{ $faq['tanya'] }}
                            <svg class="h-4 w-4 shrink-0 transition-transform"
                                 :class="buka === {{ $index }} ? 'rotate-180 text-brand-accent' : 'text-slate-400'"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="buka === {{ $index }}" x-cloak x-transition
                             class="border-t border-slate-100 bg-brand-background px-5 py-4">
                            <p class="text-sm leading-relaxed text-slate-600">{{ $faq['jawab'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================== CTA ============================== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-brand-primary to-slate-900 px-6 py-10 text-white sm:px-10">
            <div class="flex flex-wrap items-center justify-between gap-8">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Mulai belanja atau mulai berjualan</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-200">
                        {{ $siteName }} tumbuh bersama UMKM Magelang. Lihat produk mitra terverifikasi, atau
                        daftarkan usahamu hari ini.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('products.index') }}"
                       class="rounded-lg bg-brand-accent px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-600">
                        Jelajahi Katalog
                    </a>

                    <x-whatsapp-button :number="$whatsappAdmin" label="Tanya Admin" variant="light"
                                       message="Halo admin E-Wedu, saya ingin bertanya tentang platform ini." />

                    <a href="{{ route('umkm.index') }}"
                       class="rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold transition hover:border-white hover:bg-white/10">
                        Lihat Mitra UMKM
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection


