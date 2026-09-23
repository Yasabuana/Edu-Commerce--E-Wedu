{{-- Partial: tombol bagikan artikel (Alpine.js + Clipboard API). --}}
<div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6" x-data="{ tersalin: false }">
    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Bagikan</span>

    <a href="https://wa.me/?text={{ rawurlencode($article->title.' — '.url()->current()) }}"
       target="_blank" rel="noopener noreferrer"
       class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
        WhatsApp
    </a>

    <button type="button"
            data-tautan="{{ url()->current() }}"
            @click="navigator.clipboard.writeText($el.dataset.tautan).then(() => { tersalin = true; setTimeout(() => tersalin = false, 2000) })"
            class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-brand-primary hover:text-brand-primary">
        Salin tautan
    </button>

    <span x-show="tersalin" x-cloak x-transition class="text-xs font-semibold text-green-600">
        Tautan tersalin!
    </span>
</div>
