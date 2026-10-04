<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UmkmProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Verifikasi pendaftaran mitra UMKM oleh admin (FASE 8).
 *
 * Admin melihat daftar UMKM yang belum diverifikasi (is_verified = false),
 * lalu menyetujui agar profil dan produk mereka tampil di publik.
 */
class UmkmVerificationController extends Controller
{
    /**
     * Daftar UMKM yang menunggu / sudah diverifikasi.
     */
    public function index(Request $request): View
    {
        $query = UmkmProfile::with(['user', 'products'])
            ->orderBy('created_at', 'desc');

        // Filter status
        $filter = $request->query('filter', 'pending');

        if ($filter === 'pending') {
            $query->where('is_verified', false);
        } elseif ($filter === 'verified') {
            $query->where('is_verified', true);
        }
        // 'all' â€” tanpa filter

        if ($search = $request->query('cari')) {
            $query->where(function ($q) use ($search): void {
                $q->where('business_name', 'like', '%'.$search.'%')
                  ->orWhere('owner_name', 'like', '%'.$search.'%');
            });
        }

        $profiles = $query->paginate(15)->withQueryString();

        return view('admin.umkm.index', compact('profiles', 'filter'));
    }

    /**
     * Setujui UMKM (is_verified = true, verified_at = now).
     */
    public function approve(UmkmProfile $umkmProfile): RedirectResponse
    {
        if ($umkmProfile->is_verified) {
            return back()->with('info', 'UMKM "'.$umkmProfile->business_name.'" sudah diverifikasi sebelumnya.');
        }

        $umkmProfile->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.umkm.index', ['filter' => 'pending'])
            ->with('success', 'UMKM "'.$umkmProfile->business_name.'" berhasil diverifikasi dan sekarang aktif.');
    }

    /**
     * Tampilkan detail profil UMKM (untuk preview admin sebelum verifikasi).
     */
    public function show(UmkmProfile $umkmProfile): View
    {
        $umkmProfile->load(['user', 'products' => fn ($q) => $q->active(), 'articles']);

        return view('admin.umkm.show', compact('umkmProfile'));
    }
}
