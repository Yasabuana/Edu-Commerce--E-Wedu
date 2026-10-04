@extends('layouts.public')

@section('title', 'Checkout — '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Selesaikan pesananmu di E-Wedu: pilih 4 titik jemput gratis ongkir atau alamat kustom.')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
    @php
        $titikPertama = $deliveryPoints->first();
        $pilihanAwal = $titikPertama ? 'point-'.$titikPertama->id : 'custom';
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <li><a href="{{ route('home') }}" class="transition hover:text-brand-primary">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('cart.index') }}" class="transition hover:text-brand-primary">Keranjang</a></li>
                <li aria-hidden="true">/</li>
                <li class="font-medium text-brand-text">Checkout</li>
            </ol>
        </nav>

        <h1 class="mt-2 text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">Checkout</h1>
        <p class="mt-1 text-sm text-slate-600">
            Lengkapi data penerima dan pilih salah satu dari 5 opsi pengiriman E-Wedu.
        </p>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid gap-8 lg:grid-cols-3"
              x-data="{
                  choice: '{{ $pilihanAwal }}',
                  address: '',
                  lat: -7.4618,
                  lng: 110.2148,
                  fee: 0,
                  distanceKm: 0,
                  free: false,
                  outOfRange: false,
                  quoted: false,
                  loading: false,
                  error: '',
                  subtotal: {{ $subtotal }},
                  get method() {
                      return this.choice === 'custom' ? 'custom_delivery' : 'pickup_point';
                  },
                  get pointId() {
                      return this.choice.startsWith('point-') ? this.choice.replace('point-', '') : '';
                  },
                  get shippingFee() {
                      return this.method === 'custom_delivery' ? (this.outOfRange ? 0 : this.fee) : 0;
                  },
                  get total() {
                      return this.subtotal + this.shippingFee;
                  },
                  rupiah(n) {
                      return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
                  },
                  async hitung() {
                      this.loading = true;
                      this.error = '';
                      try {
                          const res = await fetch('{{ route('shipping.calculate') }}', {
                              method: 'POST',
                              headers: {
                                  'Content-Type': 'application/json',
                                  'Accept': 'application/json',
                                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                              },
                              body: JSON.stringify({ latitude: this.lat, longitude: this.lng }),
                          });
                          const data = await res.json();
                          if (! res.ok) {
                              this.error = data.message || 'Gagal menghitung ongkir.';
                              return;
                          }
                          this.distanceKm = data.distance_km;
                          this.fee = data.fee;
                          this.free = data.is_free;
                          this.outOfRange = data.out_of_range;
                          this.quoted = true;
                      } catch (e) {
                          this.error = 'Tidak dapat menghubungi server. Coba lagi.';
                      } finally {
                          this.loading = false;
                      }
                  },
              }"
              x-init="$watch('choice', value => { if(value === 'custom') { setTimeout(() => { if(typeof map !== 'undefined' && map !== null) { map.invalidateSize(); } }, 300); } })">
            @csrf

            {{-- Hidden fields yang benar-benar dikirim ke server --}}
            <input type="hidden" name="shipping_method" :value="method">
            <input type="hidden" name="delivery_point_id" :value="method === 'pickup_point' ? pointId : ''">

            {{-- ============ KOLOM KIRI: data penerima & pengiriman ============ --}}
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-brand-text">1. Data penerima</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="block text-sm font-medium text-slate-700">Nama penerima</label>
                            <input id="customer_name" name="customer_name" type="text" required maxlength="150"
                                   value="{{ old('customer_name', $pengguna?->name) }}"
                                   class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-slate-700">Nomor WhatsApp/HP
label>
                            <input id="customer_phone" name="customer_phone" type="text" required maxlength="20"
                                   value="{{ old('customer_phone', $pengguna?->phone) }}"
                                   placeholder="08xxxxxxxxxx"
                                   class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">
                        </div>

                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-slate-700">Email (opsional)</label>
                            <input id="customer_email" name="customer_email" type="email" maxlength="255"
                                   value="{{ old('customer_email', $pengguna?->email) }}"
                                   class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">
                        </div>
                    </div>
                </section>

                {{-- ==================== 5 OPSI PENGIRIMAN ==================== --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <header class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-base font-semibold text-brand-text">2. Opsi pengiriman</h2>
                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-[11px] font-semibold text-green-700">
                            4 titik gratis ongkir
                        </span>
                    </header>

                    <div class="mt-4 space-y-3">
                        @foreach ($deliveryPoints as $point)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-brand-primary/50"
                                   :class="choice === 'point-{{ $point->id }}' ? 'border-brand-primary ring-1 ring-brand-primary/40 bg-brand-primary/5' : ''">
                                <input type="radio" name="shipping_choice" value="point-{{ $point->id }}"
                                       x-model="choice" class="mt-1 border-slate-300 text-brand-primary focus:ring-brand-primary">

                                <span class="flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-semibold text-brand-text">{{ $point->name }}</span>
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-600">{{ $point->code }}</span>
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-green-700">Gratis</span>
                                    </span>
                                    <span class="mt-1 block text-xs leading-relaxed text-slate-500">{{ $point->address }}</span>
                                    @if ($point->operation_hours)
                                        <span class="mt-1 block text-[11px] text-slate-400">Jam layanan {{ $point->operation_hours }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach

                        {{-- Opsi ke-5: alamat kustom --}}
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-brand-primary/50"
                               :class="choice === 'custom' ? 'border-brand-primary ring-1 ring-brand-primary/40 bg-brand-primary/5' : ''">
                            <input type="radio" name="shipping_choice" value="custom"
                                   x-model="choice" class="mt-1 border-slate-300 text-brand-primary focus:ring-brand-primary">

                            <span class="flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-brand-text">Alamat Kustom (dikirim ke rumah)</span>
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">Ongkir otomatis</span>
                                </span>
                                <span class="mt-1 block text-xs leading-relaxed text-slate-500">
                                    Diantar ke alamatmu. Ongkir dihitung dari jarak terhadap Kampus Untidar.
                                </span>
                            </span>
                        </label>
                    </div>

                    {{-- Detail alamat kustom (tampil bila opsi ke-5 dipilih) --}}
                    <div x-show="choice === 'custom'" x-cloak class="mt-4 space-y-4 rounded-lg border border-dashed border-brand-primary/40 bg-brand-primary/5 p-4">
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-slate-700">Alamat lengkap</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" maxlength="500"
                                      x-model="address" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan…"
                                      class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"></textarea>
                        </div>

                        {{-- Hidden lat/lng — diisi otomatis oleh peta Leaflet --}}
                        <input id="shipping_lat" name="shipping_lat" type="hidden" x-model.number="lat">
                        <input id="shipping_lng" name="shipping_lng" type="hidden" x-model.number="lng">

                        {{-- Peta Leaflet interaktif — marker drag update lat/lng otomatis --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Seret penanda ke alamatmu</label>
                            <div id="map-custom"
                                 style="height: 300px; width: 100%; position: relative; z-index: 10; background-color: #f3f4f6; border-radius: 8px; border: 1px solid #d1d5db;"></div>
                            <p class="mt-1 text-xs text-slate-500">
                                Seret penanda (marker) ke lokasi rumahmu. Peta akan otomatis menghitung ongkir.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button" @click="hitung()" :disabled="loading"
                                    class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900 disabled:opacity-60">
                                <span x-show="! loading">Hitung ulang ongkir</span>
                                <span x-show="loading" x-cloak>Menghitung…</span>
                            </button>

                            <p class="text-xs text-slate-500">
                                Koordinat terdeteksi: <span x-text="lat"></span>, <span x-text="lng"></span>
                            </p>
                        </div>

                        <p x-show="error" x-cloak class="text-xs font-semibold text-red-600" x-text="error"></p>

                        <p x-show="quoted && ! outOfRange" x-cloak class="text-xs font-semibold text-green-700">
                            Jarak <span x-text="distanceKm"></span> km —
                            <span x-show="free" x-cloak>Gratis ongkir (masih dalam radius).</span>
                            <span x-show="! free" x-cloak>Ongkir <span x-text="rupiah(fee)"></span>.</span>
                        </p>

                        <p x-show="quoted && outOfRange" x-cloak class="text-xs font-semibold text-red-600">
                            Maaf, alamat berada di luar jangkauan pengiriman. Silakan pilih titik jemput gratis atau gunakan alamat lain yang lebih dekat.
                        </p>
                    </div>
                </section>

                {{-- ==================== 3. METODE PEMBAYARAN ==================== --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-brand-text">3. Metode pembayaran</h2>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-brand-primary/50">
                            <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod')
                                   class="mt-1 border-slate-300 text-brand-primary focus:ring-brand-primary">
                            <span>
                                <span class="text-sm font-semibold text-brand-text">COD (Bayar di Tempat)</span>
                                <span class="mt-1 block text-xs text-slate-500">Bayar tunai saat barang diterima/diambil.</span>
                            </span>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-brand-primary/50">
                            <input type="radio" name="payment_method" value="qris" @checked(old('payment_method') === 'qris')
                                   class="mt-1 border-slate-300 text-brand-primary focus:ring-brand-primary">
                            <span>
                                <span class="text-sm font-semibold text-brand-text">QRIS</span>
                                <span class="mt-1 block text-xs text-slate-500">Scan QR & unggah bukti bayar di tahap berikutnya.</span>
                            </span>
                        </label>
                    </div>
                </section>

                {{-- ==================== 4. CATATAN ==================== --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-brand-text">4. Catatan (opsional)</h2>
                    <textarea name="customer_note" rows="3" maxlength="1000"
                              placeholder="Contoh: tolong pisahkan kemasan getuk dan wedang."
                              class="mt-3 w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">{{ old('customer_note') }}</textarea>
                </section>
            </div>

            {{-- ============ KOLOM KANAN: RINGKASAN PESANAN ============ --}}
            <aside class="lg:col-span-1">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                    <h2 class="text-base font-semibold text-brand-text">Ringkasan pesanan</h2>

                    <ul class="mt-4 max-h-64 divide-y divide-slate-100 overflow-y-auto">
                        @foreach ($items as $item)
                            <li class="flex items-start justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-brand-text">{{ $item->product?->name ?? 'Produk' }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ number_format((int) $item->qty) }} &times; Rp {{ number_format((int) $item->price_snapshot, 0, ',', '.') }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-brand-text">
                                    Rp {{ number_format((int) $item->subtotal, 0, ',', '.') }}
                                </p>
                            </li>
                        @endforeach
                    </ul>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Total item</dt>
                            <dd class="font-medium text-brand-text">{{ number_format($jumlahItem) }} item</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Total berat</dt>
                            <dd class="font-medium text-brand-text">{{ number_format($totalBerat) }} gram</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Subtotal</dt>
                            <dd class="font-medium text-brand-text">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Ongkir</dt>
                            <dd class="font-medium text-brand-text">
                                <span x-show="method === 'pickup_point'" x-cloak class="text-green-700">Gratis</span>
                                <span x-show="method === 'custom_delivery'" x-cloak x-text="outOfRange ? '—' : rupiah(shippingFee)"></span>
                            </dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <dt class="text-base font-semibold text-brand-text">Total</dt>
                            <dd class="text-base font-bold text-brand-primary" x-text="rupiah(total)"></dd>
                        </div>
                    </dl>

                    <p class="mt-3 text-xs leading-relaxed text-slate-500">
                        Total akhir dikunci saat pesanan dibuat. Stok dipotong langsung agar tidak terjadi kelebihan pesanan.
                    </p>

                    <button type="submit"
                            class="mt-4 w-full rounded-lg bg-brand-accent px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-600">
                        Buat Pesanan
                    </button>

                    <a href="{{ route('cart.index') }}"
                       class="mt-2 block text-center text-sm font-semibold text-brand-primary transition hover:text-brand-accent">
                        Kembali ke keranjang
                    </a>
                </div>
            </aside>
        </form>
    </div>
@endsection

<script>
    var map;
    var marker;
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('map-custom');
        if (!el) return;

        map = L.map('map-custom').setView([-7.4618, 110.2148], 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        marker = L.marker([-7.4618, 110.2148], {draggable: true}).addTo(map);
        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            var lat = Number(position.lat.toFixed(7));
            var lng = Number(position.lng.toFixed(7));
            document.getElementById('shipping_lat').value = lat;
            document.getElementById('shipping_lng').value = lng;
            document.getElementById('shipping_lat').dispatchEvent(new Event('input', { bubbles: true }));
            document.getElementById('shipping_lng').dispatchEvent(new Event('input', { bubbles: true }));

            // Panggil hitung ongkir via Alpine
            var alpineForm = document.querySelector('form[x-data]');
            if (alpineForm && alpineForm.__x) {
                alpineForm.__x.$data.lat = lat;
                alpineForm.__x.$data.lng = lng;
                alpineForm.__x.$data.hitung();
            }
        });

        setTimeout(function() { map.invalidateSize(); }, 500);
    });
</script>