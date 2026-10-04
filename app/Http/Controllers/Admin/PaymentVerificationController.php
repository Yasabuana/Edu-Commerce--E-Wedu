<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Verifikasi bukti pembayaran QRIS dari sisi admin (FASE 8).
 *
 * Admin melihat daftar pesanan dengan payment_status = awaiting_verification,
 * meninjau bukti transfer, lalu menyetujui atau menolak.
 */
class PaymentVerificationController extends Controller
{
    /**
     * Daftar pesanan menunggu verifikasi pembayaran.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['items', 'user'])
            ->where('payment_status', Order::PAYMENT_AWAITING_VERIFICATION)
            ->where('payment_method', Order::PAYMENT_METHOD_QRIS);

        // Filter tambahan: cari berdasarkan nomor pesanan
        if ($search = $request->query('cari')) {
            $query->where('order_number', 'like', '%'.$search.'%');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.payments.index', compact('orders'));
    }

    /**
     * Tampilkan detail bukti pembayaran untuk satu pesanan.
     */
    public function show(Order $order): View
    {
        abort_if($order->payment_status !== Order::PAYMENT_AWAITING_VERIFICATION, 404);

        $order->load(['items', 'user', 'statusHistories' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.payments.show', compact('order'));
    }

    /**
     * Setujui pembayaran: order → confirmed, payment_status → paid.
     */
    public function approve(Order $order): RedirectResponse
    {
        abort_if($order->payment_status !== Order::PAYMENT_AWAITING_VERIFICATION, 404);

        $order->update([
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        $order->statusHistories()->create([
            'status' => Order::STATUS_CONFIRMED,
            'note' => 'Pembayaran QRIS diverifikasi dan disetujui oleh admin.',
            'changed_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Pembayaran pesanan '.$order->order_number.' telah disetujui. Pesanan dikonfirmasi.');
    }

    /**
     * Tolak pembayaran: payment_status → rejected.
     */
    public function reject(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->payment_status !== Order::PAYMENT_AWAITING_VERIFICATION, 404);

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
        ]);

        $order->update([
            'payment_status' => Order::PAYMENT_REJECTED,
        ]);

        $order->statusHistories()->create([
            'status' => $order->status,
            'note' => 'Pembayaran ditolak admin. Alasan: '.$validated['alasan'],
            'changed_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Pembayaran pesanan '.$order->order_number.' ditolak.');
    }
}
