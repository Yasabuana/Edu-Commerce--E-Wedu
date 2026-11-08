<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\UmkmProfileController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\UmkmVerificationController;
use App\Http\Controllers\Umkm\OrderController as UmkmOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik (Rancangan Â§2.1) â€” FASE 4 & 5
|--------------------------------------------------------------------------
| Rute lacak pesanan menyusul di FASE 6, checkout di FASE 7.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [PageController::class, 'about'])->name('about');

Route::get('/umkm', [UmkmProfileController::class, 'index'])->name('umkm.index');
Route::get('/umkm/daftar', [UmkmProfileController::class, 'registerForm'])->name('umkm.register');
Route::post('/umkm/daftar', [UmkmProfileController::class, 'registerStore'])->name('umkm.register.store');
Route::get('/umkm/{umkmProfile:slug}', [UmkmProfileController::class, 'show'])->name('umkm.show');

Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

/* FASE 5 â€” katalog produk */
Route::get('/katalog', [ProductController::class, 'index'])->name('products.index');
Route::get('/katalog/{product:slug}', [ProductController::class, 'show'])->name('products.show');

/* FASE 5 â€” keranjang (guest & user login) */
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

/* FASE 6 â€” logistik mandiri: daftar titik & hitung ongkir (fetch + Alpine) */
Route::get('/ongkir/titik', [ShippingController::class, 'points'])->name('shipping.points');
Route::post('/ongkir/hitung', [ShippingController::class, 'calculate'])
    ->middleware('throttle:30,1')
    ->name('shipping.calculate');

/* FASE 6 â€” checkout & pesanan (wajib login) */
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/pesanan-saya', [OrderController::class, 'index'])->name('orders.index');

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');

    /* FASE 7 â€” QRIS Manual: halaman bayar & upload bukti */
    Route::get('/pesanan/{order:order_number}/bayar', [PaymentController::class, 'pay'])->name('orders.pay');
    Route::post('/pesanan/{order:order_number}/bayar', [PaymentController::class, 'uploadReceipt'])->name('orders.pay.upload');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
/*
|--------------------------------------------------------------------------
| FASE 8 — Panel Admin & UMKM
|--------------------------------------------------------------------------
*/

/* Panel Admin — middleware auth + role:admin */
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.payments.index');
    })->name('dashboard');

    /* Verifikasi Pembayaran (QRIS) */
    Route::prefix('pembayaran')->name('payments.')->group(function () {
        Route::get('/', [PaymentVerificationController::class, 'index'])->name('index');
        Route::get('/{order}', [PaymentVerificationController::class, 'show'])->name('show');
        Route::post('/{order}/setujui', [PaymentVerificationController::class, 'approve'])->name('approve');
        Route::post('/{order}/tolak', [PaymentVerificationController::class, 'reject'])->name('reject');
    });

    /* Verifikasi Mitra UMKM */
    Route::prefix('umkm')->name('umkm.')->group(function () {
        Route::get('/', [UmkmVerificationController::class, 'index'])->name('index');
        Route::get('/{umkmProfile}', [UmkmVerificationController::class, 'show'])->name('show');
        Route::post('/{umkmProfile}/setujui', [UmkmVerificationController::class, 'approve'])->name('approve');
    });
});

/* Panel UMKM — middleware auth + role:umkm */
Route::middleware(['auth', 'role:umkm'])->prefix('umkm-panel')->name('umkm.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('umkm.orders.index');
    })->name('dashboard');

    /* Pesanan Masuk */
    Route::prefix('pesanan')->name('orders.')->group(function () {
        Route::get('/', [UmkmOrderController::class, 'index'])->name('index');
        Route::get('/{orderItem}', [UmkmOrderController::class, 'show'])->name('show');
    });
});



require __DIR__.'/auth.php';

Route::get('/cek-db', function () {
    try {
        $host = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
        $port = "4000";
        $db   = "ewedu";
        $user = "USERNAME_TIDB_LU"; // ganti username tidb lu
        $pass = "PASSWORD_TIDB_LU"; // ganti password tidb lu
        
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        
        $pdo = new PDO($dsn, $user, $pass, $options);
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return response()->json([
            'status' => 'BERHASIL TEMBUS KE TIDB!',
            'tabel_di_tidb' => $tables
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'GAGAL KONEK KARENA INI',
            'error' => $e->getMessage()
        ], 500);
    }
});