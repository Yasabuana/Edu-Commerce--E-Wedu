{{--
    Komponen: x-sidebar-link
    Item menu sidebar untuk layouts/admin.blade.php & layouts/umkm.blade.php.

    Contoh: <x-sidebar-link href="/admin/pesanan" :active="request()->is('admin/pesanan*')">Pesanan</x-sidebar-link>
--}}
@props([
    'href' => '#',
    'active' => false,
])

<a href="{{ $href }}"
   @class([
       'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
       'bg-white/15 text-white shadow-sm' => $active,
       'text-slate-300 hover:bg-white/10 hover:text-white' => ! $active,
   ])
   @if ($active) aria-current="page" @endif>
    <span @class([
        'h-1.5 w-1.5 rounded-full',
        'bg-brand-accent' => $active,
        'bg-slate-500 group-hover:bg-brand-accent' => ! $active,
    ])></span>
    {{ $slot }}
</a>
