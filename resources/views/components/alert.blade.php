@props([
    'type' => null,          // success | error | warning | info — null = mode otomatis
    'title' => null,
    'dismissible' => true,
])

@php
    // Pemakaian:
    //   <x-alert type="success">Pesanan tersimpan.</x-alert>
    //   <x-alert />  -> otomatis menampilkan flash session (success/error/warning/info/status)
    //                  dan ringkasan error validasi.
    $gaya = [
        'success' => ['kotak' => 'border-green-200 bg-green-50 text-green-800', 'ikon' => 'text-green-600'],
        'error' => ['kotak' => 'border-red-200 bg-red-50 text-red-800', 'ikon' => 'text-red-600'],
        'warning' => ['kotak' => 'border-amber-200 bg-amber-50 text-amber-900', 'ikon' => 'text-amber-600'],
        'info' => ['kotak' => 'border-blue-200 bg-blue-50 text-blue-800', 'ikon' => 'text-blue-600'],
    ];

    $pesan = [];

    if ($type) {
        $pesan[] = [
            'type' => isset($gaya[$type]) ? $type : 'info',
            'title' => $title,
            'text' => trim((string) ($slot ?? '')),
        ];
    } else {
        foreach (['success', 'error', 'warning', 'info'] as $kunci) {
            if (session()->has($kunci)) {
                $pesan[] = ['type' => $kunci, 'title' => null, 'text' => session($kunci)];
            }
        }

        if (session()->has('status')) {
            $pesan[] = ['type' => 'info', 'title' => null, 'text' => session('status')];
        }

        // $errors dibagikan middleware ShareErrorsFromSession; null-safe agar aman
        // saat view dirender di luar HTTP request (console/queue/testing).
        $errorBag = $errors ?? null;

        if ($errorBag && $errorBag->any()) {
            $pesan[] = [
                'type' => 'error',
                'title' => 'Periksa kembali isian Anda',
                'text' => implode(' ', $errorBag->all()),
            ];
        }
    }
@endphp

@foreach ($pesan as $item)
    @php $s = $gaya[$item['type']]; @endphp

    <div x-data="{ tampil: true }" x-show="tampil" x-cloak role="alert"
         class="mb-4 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm shadow-sm {{ $s['kotak'] }}">

        <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $s['ikon'] }}" fill="none" stroke="currentColor" stroke-width="1.8"
             viewBox="0 0 24 24" aria-hidden="true">
            @if ($item['type'] === 'success')
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            @elseif ($item['type'] === 'error')
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.34 3.94l-8.1 14.02A1.5 1.5 0 003.54 20.2h16.92a1.5 1.5 0 001.3-2.24l-8.1-14.02a1.5 1.5 0 00-2.6 0z" />
            @elseif ($item['type'] === 'warning')
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.5 19.5h15a1.5 1.5 0 001.3-2.25l-7.5-13a1.5 1.5 0 00-2.6 0l-7.5 13A1.5 1.5 0 004.5 19.5z" />
            @else
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25h.008v5.25h-.008V11.25zM12 21a9 9 0 100-18 9 9 0 000 18z" />
            @endif
        </svg>

        <div class="flex-1">
            @if ($item['title'])
                <p class="font-semibold">{{ $item['title'] }}</p>
            @endif
            <p class="{{ $item['title'] ? 'mt-1' : '' }} leading-relaxed">{{ $item['text'] }}</p>
        </div>

        @if ($dismissible)
            <button type="button" @click="tampil = false"
                    class="rounded p-1 opacity-60 transition hover:bg-black/5 hover:opacity-100"
                    aria-label="Tutup notifikasi">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
@endforeach
