<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    {{-- Sapaan --}}
                    <h3 class="text-2xl font-bold text-gray-900">
                        Selamat datang di E-Wedu, {{ auth()->user()->name }}!
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Temukan berbagai produk unggulan dari UMKM Magelang.
                    </p>

                    {{-- Tombol aksi --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <a href="{{ route('products.index') }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-brand-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-900">
                            Mulai Belanja
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <a href="{{ route('orders.index') }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50">
                            Riwayat Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
