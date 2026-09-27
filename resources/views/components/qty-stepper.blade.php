{{--
    Komponen: x-qty-stepper
    Pengatur jumlah (minus / input / plus) berbasis Alpine.js.
    Dipakai form "Tambah ke keranjang" (halaman detail produk) dan
    form ubah qty pada halaman keranjang.

    Contoh:
      <x-qty-stepper :value="1" :max="$product->stock" />
      <x-qty-stepper name="qty" :value="$item->qty" :max="10" auto-submit />
--}}
@props([
    'name' => 'qty',
    'value' => 1,
    'min' => 1,
    'max' => 99,
    'autoSubmit' => false,
])

@php
    $min = max(1, (int) $min);
    $max = max($min, (int) $max);
    $value = min($max, max($min, (int) $value));
    $kelasTombol = 'grid h-9 w-9 place-items-center text-lg font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-300';
@endphp

<div x-data="{
        qty: {{ $value }},
        min: {{ $min }},
        max: {{ $max }},
        turun() {
            this.qty = Math.max(this.min, this.qty - 1);
            @if ($autoSubmit) this.$nextTick(() => this.$root.closest('form')?.requestSubmit()); @endif
        },
        naik() {
            this.qty = Math.min(this.max, this.qty + 1);
            @if ($autoSubmit) this.$nextTick(() => this.$root.closest('form')?.requestSubmit()); @endif
        },
     }"
     {{ $attributes->merge(['class' => 'inline-flex items-center overflow-hidden rounded-lg border border-slate-300 bg-white']) }}>

    <button type="button" @click="turun()" :disabled="qty <= min"
            class="{{ $kelasTombol }}" aria-label="Kurangi jumlah">&minus;</button>

    <input type="number" name="{{ $name }}" x-model.number="qty" :min="min" :max="max"
           inputmode="numeric" autocomplete="off"
           class="h-9 w-14 border-x border-slate-200 bg-transparent text-center text-sm font-semibold text-brand-text focus:outline-none"
           aria-label="Jumlah">

    <button type="button" @click="naik()" :disabled="qty >= max"
            class="{{ $kelasTombol }}" aria-label="Tambah jumlah">+</button>
</div>
