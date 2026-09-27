<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UmkmProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik (Rancangan §2.1) — FASE 4 & 5
|--------------------------------------------------------------------------
| Rute lacak pesanan menyusul di FASE 6, checkout di FASE 7.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [PageController::class, 'about'])->name('about');

Route::get('/umkm', [UmkmProfileController::class, 'index'])->name('umkm.index');
Route::get('/umkm/{umkmProfile:slug}', [UmkmProfileController::class, 'show'])->name('umkm.show');

Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

/* FASE 5 — katalog produk */
Route::get('/katalog', [ProductController::class, 'index'])->name('products.index');
Route::get('/katalog/{product:slug}', [ProductController::class, 'show'])->name('products.show');

/* FASE 5 — keranjang (guest & user login) */
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
