<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

/**
 * Halaman pesanan customer (Rancangan §2.2 — FASE 6 & 9).
 *
 * Customer hanya boleh melihat pesanannya sendiri (guard kepemilikan sederhana).
 */
class OrderController extends Controller
{
    /**
     * Daftar semua pesanan milik user yang sedang login.
     */
    public function index(): View
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Detail satu pesanan milik user yang sedang login.
     */
    public function show(Order $order): View
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 404);

        $order->load(['items.umkmProfile', 'deliveryPoint', 'statusHistories']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }
}