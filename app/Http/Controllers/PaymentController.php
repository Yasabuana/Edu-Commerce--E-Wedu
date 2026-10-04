<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Halaman & logika pembayaran QRIS Manual + Upload Bukti Bayar (FASE 7).
 *
 * Setelah user checkout dengan QRIS, dia diarahkan ke halaman ini untuk:
 *   1. Melihat gambar QRIS statis E-Wedu.
 *   2. Melihat total belanja dengan jelas.
 *   3. Mengunggah screenshot bukti transfer.
 *
 * Setelah upload sukses, status pesanan berubah menjadi 'awaiting_verification'
 * dan admin akan memverifikasi secara manual.
 */
class PaymentController extends Controller
{
    /**
     * Tampilkan halaman instruksi pembayaran QRIS + form upload bukti.
     */
    public function pay(Order $order): View|RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 404);

        // Hanya untuk pesanan QRIS yang belum dibayar
        if (! $order->needsPayment()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('info', 'Pesanan ini sudah dibayar atau tidak memerlukan pembayaran QRIS.');
        }

        $order->load(['items']);

        return view('orders.pay', [
            'order' => $order,
            'qrisImage' => Setting::get('qris_image', ''),
            'qrisMerchantName' => Setting::get('qris_merchant_name', 'E-Wedu UMKM Magelang'),
            'whatsappAdmin' => Setting::get('whatsapp_admin', '6288225435927'),
        ]);
    }

    /**
     * Proses upload bukti bayar QRIS.
     */
    public function uploadReceipt(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 404);

        if (! $order->needsPayment()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('info', 'Pesanan ini sudah dibayar atau tidak memerlukan pembayaran QRIS.');
        }

        $validated = $request->validate([
            'receipt' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // max 2MB
        ]);

        // Hapus bukti lama jika ada
        if ($order->payment_receipt) {
            Storage::disk('public')->delete($order->payment_receipt);
        }

        $path = $validated['receipt']->store('receipts', 'public');

        $order->update([
            'payment_receipt' => $path,
            'payment_status' => Order::PAYMENT_AWAITING_VERIFICATION,
        ]);

        $order->statusHistories()->create([
            'status' => Order::STATUS_PENDING,
            'note' => 'Bukti pembayaran QRIS berhasil diunggah, menunggu verifikasi admin.',
            'changed_by' => auth()->id(),
        ]);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Bukti pembayaran berhasil diunggah. Tim E-Wedu akan memverifikasinya segera.');
    }
}