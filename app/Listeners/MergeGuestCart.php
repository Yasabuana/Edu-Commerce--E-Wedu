<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

/**
 * Gabungkan keranjang guest ke keranjang user begitu login berhasil (FASE 5).
 *
 * Breeze memancarkan event `Login` sebelum `session()->regenerate()`, sehingga
 * session id keranjang guest masih bisa dibaca di listener ini.
 */
class MergeGuestCart
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function handle(Login $event): void
    {
        $this->cartService->mergeGuestCart($event->user);
    }
}
