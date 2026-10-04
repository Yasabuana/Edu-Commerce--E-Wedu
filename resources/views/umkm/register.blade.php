@extends('layouts.public')

@section('title', 'Daftar Mitra UMKM - '.config('app.name', 'E-Wedu'))
@section('meta_description', 'Daftarkan bisnis UMKM Anda di E-Wedu untuk menjangkau lebih banyak pelanggan.')

@section('content')
    <x-page-header eyebrow="Pendaftaran Mitra" title="Daftar Menjadi Mitra UMKM"
                   subtitle="Isi formulir di bawah untuk mendaftarkan usaha Anda ke E-Wedu. Tim kami akan memverifikasi dalam 1x24 jam."
                   :breadcrumbs="[
                       ['label' => 'Beranda', 'url' => route('home')],
                       ['label' => 'Profil UMKM', 'url' => route('umkm.index')],
                       ['label' => 'Daftar Mitra'],
                   ]" />

    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
            <form method="POST" action="{{ route('umkm.register.store') }}" class="space-y-6">
                @csrf

                {{-- Nama Bisnis --}}
                <div>
                    <x-input-label for="business_name" value="Nama Bisnis / Usaha" />
                    <input type="text" id="business_name" name="business_name"
                           value="{{ old('business_name') }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                           placeholder="Misal: Kedai Kopi Senja" required>
                    <x-input-error :messages="$errors->get('business_name')" class="mt-1" />
                </div>

                {{-- Nama Pemilik --}}
                <div>
                    <x-input-label for="owner_name" value="Nama Pemilik" />
                    <input type="text" id="owner_name" name="owner_name"
                           value="{{ old('owner_name') }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                           placeholder="Nama lengkap pemilik usaha" required>
                    <x-input-error :messages="$errors->get('owner_name')" class="mt-1" />
                </div>

                {{-- Email --}}
                <div>
                    <x-input-label for="email" value="Email" />
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                           placeholder="bisnis@contoh.com" required>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                {{-- No. Telepon / WhatsApp --}}
                <div>
                    <x-input-label for="phone" value="Nomor Telepon / WhatsApp" />
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone') }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                           placeholder="0812xxxxxxx" required>
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>

                {{-- Kategori Usaha --}}
                <div>
                    <x-input-label for="category_label" value="Kategori Usaha" />
                    <select id="category_label" name="category_label"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary" required>
                        <option value="">- Pilih Kategori -</option>
                        <option value="Kuliner" @selected(old('category_label') === 'Kuliner')>Kuliner</option>
                        <option value="Minuman" @selected(old('category_label') === 'Minuman')>Minuman</option>
                        <option value="Fashion & Batik" @selected(old('category_label') === 'Fashion & Batik')>Fashion & Batik</option>
                        <option value="Kriya & Kerajinan" @selected(old('category_label') === 'Kriya & Kerajinan')>Kriya & Kerajinan</option>
                        <option value="Pertanian & Olahan" @selected(old('category_label') === 'Pertanian & Olahan')>Pertanian & Olahan</option>
                        <option value="Jasa & Layanan" @selected(old('category_label') === 'Jasa & Layanan')>Jasa & Layanan</option>
                    </select>
                    <x-input-error :messages="$errors->get('category_label')" class="mt-1" />
                </div>

                {{-- Alamat --}}
                <div>
                    <x-input-label for="address" value="Alamat Usaha" />
                    <textarea id="address" name="address" rows="3"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                              placeholder="Alamat lengkap usaha Anda" required>{{ old('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>

                {{-- Deskripsi --}}
                <div>
                    <x-input-label for="description" value="Deskripsi Bisnis" />
                    <textarea id="description" name="description" rows="4"
                              class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                              placeholder="Ceritakan tentang bisnis Anda..." required>{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 pt-6">
                    <a href="{{ route('umkm.index') }}"
                       class="text-sm font-medium text-slate-600 transition hover:text-brand-primary">
                        &larr; Kembali ke daftar mitra
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900">
                        Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
