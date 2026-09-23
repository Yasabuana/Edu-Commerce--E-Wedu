{{--
    Komponen: x-whatsapp-button
    Tombol chat WhatsApp. Nomor dinormalisasi ke format internasional (62...).
    Tidak merender apa pun bila nomor belum tersedia di `settings`.

    Contoh: <x-whatsapp-button :number="$whatsappAdmin" label="Tanya Admin" variant="light" />
--}}
@props([
    'number' => null,
    'label' => 'Chat WhatsApp',
    'message' => null,
    'variant' => 'primary', // primary | light | outline
])

@php
    $bersih = $number ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', (string) $number)) : null;
    $tautan = $bersih ? 'https://wa.me/'.$bersih.($message ? '?text='.rawurlencode($message) : '') : null;

    $kelas = match ($variant) {
        'light' => 'bg-white text-brand-primary hover:bg-slate-100',
        'outline' => 'border border-brand-primary text-brand-primary hover:bg-brand-primary/5',
        default => 'bg-brand-accent text-white hover:bg-orange-600',
    };
@endphp

@if ($tautan)
    <a href="{{ $tautan }}" target="_blank" rel="noopener noreferrer"
       {{ $attributes->merge(['class' => "inline-flex items-center gap-2 rounded-lg px-5 py-3 text-sm font-semibold transition {$kelas}"]) }}>
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 004.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91A9.85 9.85 0 0012.04 2zm0 18.02c-1.5 0-2.97-.4-4.25-1.16l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 01-1.26-4.36c0-4.54 3.7-8.23 8.25-8.23a8.23 8.23 0 018.25 8.24c0 4.54-3.7 8.22-8.2 8.22zm4.52-6.16c-.25-.12-1.47-.72-1.7-.81-.23-.08-.4-.12-.56.13-.17.25-.65.81-.8.98-.14.16-.29.19-.54.06a6.7 6.7 0 01-1.97-1.21 7.4 7.4 0 01-1.36-1.7c-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.43.12-.14.16-.25.25-.41.08-.17.04-.31-.02-.44-.06-.12-.56-1.35-.77-1.84-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.42.06-.65.31-.22.25-.85.83-.85 2.02 0 1.19.87 2.34.99 2.5.12.17 1.7 2.68 4.12 3.67.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.46-.07 1.42-.58 1.62-1.15.2-.56.2-1.04.14-1.14-.06-.11-.22-.17-.47-.29z" />
        </svg>
        {{ $label }}
    </a>
@endif
