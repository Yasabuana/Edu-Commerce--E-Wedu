<?php

namespace App\Providers;

use App\Listeners\MergeGuestCart;
use App\Models\Cart;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keranjang guest digabung ke keranjang user tepat setelah login (FASE 5).
        Event::listen(Login::class, MergeGuestCart::class);

        // Badge jumlah item navbar — read-only, tidak membuat baris cart baru.
        View::composer('components.navbar', function (\Illuminate\View\View $view): void {
            $view->with('cartItemCount', Cart::currentItemCount());
        });
    }
}

