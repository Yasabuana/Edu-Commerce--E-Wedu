# ðŸ—ï¸ RANCANGAN ARSITEKTUR â€” E-Wedu

**Edu-Commerce UMKM Magelang** Â· Laravel 12 + Tailwind CSS + MySQL 8

> Dokumen ini adalah acuan arsitektur MVP (Minimum Viable Product) yang disusun dari SRS berikut:
> 1. Landing Page & Literasi UMKM (Hero banner, artikel/profil UMKM)
> 2. Katalog Produk & Shopping Cart
> 3. Checkout & Pengiriman Logistik Mandiri (4 titik gratis ongkir + 1 alamat kustom dengan kalkulasi jarak otomatis dari Kampus Tuguran)
> 4. Pembayaran (Cash COD & QRIS + upload bukti transfer)
> 5. Dashboard Admin (Validasi pesanan, verifikasi QRIS, atur status pengiriman)

---

## 0. Keputusan Arsitektur (High Level)

| Aspek | Keputusan | Alasan |
|---|---|---|
| Framework | Laravel 12 (latest stable) | Butuh PHP >= 8.2, sudah tersedia |
| Auth scaffold | Laravel Breeze (Blade + Alpine.js) | Sudah include Tailwind + Alpine, cocok untuk interaktif cart tanpa SPA |
| CSS | Tailwind CSS (v3.4 dari Breeze; opsi upgrade v4) | v3.4 lebih stabil & dokumentasi melimpah untuk MVP |
| DB | MySQL 8.0 | Laragon `mysql-8.0.30-winx64`, datadir `C:\laragon\data\mysql-8` |
| Role | Kolom `role` enum di `users` (bukan spatie/permission) | MVP cukup, hindari dependency berat |
| Uang | `unsignedBigInteger` rupiah tanpa desimal | Hindari floating point error |
| Koordinat | `decimal(10,7)`, jarak `decimal(6,2)` km | Presisi cukup untuk kalkulasi jarak |
| Kalkulasi jarak | Haversine (service sendiri) + Nominatim/OSM geocoding, dengan cache | Gratis, tanpa API key; rate limit 1 req/s |
| QRIS | QRIS statis (gambar di tabel `settings`) + upload bukti, verifikasi manual admin | Sesuai SRS; skema `payments` siap dimigrasi ke gateway |
| Panel UMKM | Fase 2 (MVP admin-only) | Mengejar waktu MVP; skema DB sudah siap |
| Gambar | Disk `public` (produk/artikel), disk **private** (bukti pembayaran) | Bukti transfer tidak boleh diakses publik |
| Order number | `EWD-YYYYMMDD-XXXX` + kolom `id` autoincrement | Mudah dilacak customer & admin |

### Palet Warna â€” "Local Trust & Energetic"
| Token | Hex | Makna |
|---|---|---|
| `brand-primary` | `#1E3A8A` | Biru tua â€” kepercayaan, institusi |
| `brand-accent` | `#F97316` | Oranye â€” energi, ajakan aksi |
| `brand-background` | `#F8FAFC` | Latar terang netral |
| `brand-text` | `#0F172A` | Teks utama kontras tinggi |

---

## 1. SKEMA DATABASE

### 1.1 ERD Ringkas

```
users --1:1-- umkm_profiles --1:N-- products --1:N-- product_images
  |             |                     |
  |             +--1:N-- articles     +--N:M (via cart_items)-- carts -- users/session
  |                                                               |
  +--1:N-- orders --1:N-- order_items --(product_id nullable, snapshot nama/harga)
              |
              +--1:N-- payments
              +--1:N-- order_status_histories -- users (changed_by)
              +--N:1-- delivery_points
                          categories --1:N-- products
                          settings (key-value)
```

### 1.2 Detail Tabel

#### `users` (modifikasi migration default Laravel)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | id | PK |
| name, email, email_verified_at, password | default Laravel | |
| role | enum(`customer`,`umkm`,`admin`) default `customer` | diberi index |
| phone | string(20) nullable | WA untuk konfirmasi COD |
| address | text nullable | alamat default customer |
| avatar | string nullable | |
| is_active | boolean default true | blokir akun |
| remember_token, timestamps, softDeletes | default Laravel | |

#### `umkm_profiles` â€” profil & literasi UMKM
`id, user_id (FK users, cascade), business_name, slug (unique), owner_name, category_label, description (longText), logo, cover_image, phone, whatsapp, address, latitude decimal(10,7), longitude decimal(10,7), instagram, website, is_verified bool, verified_at, published_at, timestamps, softDeletes`

#### `article_categories` + `articles` â€” konten literasi UMKM
- `article_categories`: `id, name, slug (unique), description, timestamps`
- `articles`: `id, user_id (FK author), article_category_id (FK nullable), umkm_profile_id (FK nullable), title, slug (unique), excerpt varchar(255), body longText, cover_image, status enum(draft,published), is_featured bool, views unsignedInteger default 0, published_at, timestamps, softDeletes`

#### `categories` â€” kategori produk
`id, name, slug (unique), icon, description, sort_order, is_active, timestamps`

#### `products`
`id, umkm_profile_id (FK), category_id (FK nullable), name, slug (unique), sku varchar(50) nullable unique, description longText, price unsignedBigInteger, stock unsignedInteger, weight_gram unsignedInteger default 0, unit varchar(20) default 'pcs', thumbnail, is_active bool default true, is_featured bool, sold_count unsignedInteger default 0, timestamps, softDeletes`

#### `product_images`
`id, product_id (FK cascade), path, alt_text, sort_order, is_primary bool, timestamps`

#### `carts` + `cart_items`
- `carts`: `id, user_id (FK nullable), session_id varchar nullable index, timestamps`
- `cart_items`: `id, cart_id (FK cascade), product_id (FK), qty unsignedInteger, price_snapshot unsignedBigInteger, umkm_profile_id (FK), timestamps` â€” unique `(cart_id, product_id)`

#### `delivery_points` â€” inti fitur logistik mandiri
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | id | PK |
| name | string | contoh "Kampus Tuguran Untidar" |
| code | string(20) unique | contoh `TGR-01` |
| type | enum(`free_point`,`custom`) default `free_point` | pembeda 4 titik gratis vs alamat kustom |
| address | text | alamat lengkap titik |
| latitude, longitude | decimal(10,7) | koordinat titik jemput/pengiriman |
| is_free_shipping | boolean default true | |
| free_radius_km | decimal(5,2) default 0 | radius gratis untuk alamat kustom |
| operation_hours | string nullable | "08.00-16.00 WIB" |
| notes | text nullable | instruksi untuk pembeli |
| sort_order, is_active, timestamps, softDeletes | | |

**Seed:** 4 baris `type=free_point` (Kampus Tuguran, UMKM Center Magelang, Balai Kota Magelang, Sentra UMKM) + 1 baris master rule `type=custom`.

#### `orders`
```
id, order_number varchar(30) unique,
user_id FK nullable,                       -- null bila guest checkout
customer_name, customer_phone, customer_email nullable,
delivery_point_id FK nullable,             -- terisi bila memilih titik gratis
shipping_method enum(pickup_point, custom_delivery),
shipping_address text nullable,            -- terisi bila alamat kustom
shipping_lat, shipping_lng decimal(10,7) nullable,
shipping_distance_km decimal(6,2) default 0,
shipping_fee unsignedBigInteger default 0,
subtotal, discount default 0, total,
payment_method enum(cod, qris),
payment_status enum(unpaid, awaiting_verification, paid, rejected) default unpaid,
status enum(pending, confirmed, processing, ready, shipped, delivered, completed, cancelled) default pending,
customer_note text nullable,
admin_note text nullable,
verified_by FK users nullable, verified_at timestamp nullable,
delivered_at, completed_at, cancelled_at nullable,
cancel_reason text nullable, timestamps, softDeletes
```
Index: `status`, `payment_status`, `created_at`, `user_id`.

#### `order_items`
`id, order_id (FK cascade), product_id (FK nullable, nullOnDelete), umkm_profile_id (FK nullable), product_name (snapshot), product_sku, price unsignedBigInteger, qty, subtotal, product_thumbnail, timestamps`
> Snapshot nama/harga agar histori pesanan tidak berubah meski produk diedit/dihapus.

#### `payments` â€” COD & QRIS dalam satu tabel
`id, order_id (FK cascade), method enum(cod,qris), amount unsignedBigInteger, proof_path (disk private), sender_name, sender_bank, qris_reference, status enum(pending,verified,rejected) default pending, verified_by FK users nullable, verified_at, rejection_reason text nullable, gateway_response json nullable, timestamps`
> `gateway_response` disiapkan untuk migrasi ke Midtrans/Duitku tanpa ubah struktur.

#### `order_status_histories` â€” audit trail dashboard admin
`id, order_id (FK cascade), status varchar(30), note text nullable, changed_by FK users nullable, timestamps`

#### `settings` â€” konfigurasi key-value (hindari hardcode)
Seed awal: `site_name`, `site_tagline`, `campus_origin_lat`, `campus_origin_lng`, `shipping_base_fee`, `shipping_cost_per_km`, `shipping_free_radius_km`, `shipping_max_distance_km`, `whatsapp_admin`, `qris_image`, `qris_merchant_name`, `cod_enabled`.
`id, key (unique), value text, type enum(string,integer,boolean,json), group, timestamps`

#### Tabel pendukung Laravel
`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `notifications`, `password_reset_tokens`, `sessions`.

### 1.3 Daftar File Migration (urutan eksekusi)
```
0001_01_01_000000_create_users_table.php          <- tambah kolom role/phone/address/is_active
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
2026_09_23_000001_create_settings_table.php
2026_09_23_000002_create_categories_table.php
2026_09_23_000003_create_umkm_profiles_table.php
2026_09_23_000004_create_article_categories_table.php
2026_09_23_000005_create_articles_table.php
2026_09_23_000006_create_products_table.php
2026_09_23_000007_create_product_images_table.php
2026_09_23_000008_create_carts_table.php
2026_09_23_000009_create_cart_items_table.php
2026_09_23_000010_create_delivery_points_table.php
2026_09_23_000011_create_orders_table.php
2026_09_23_000012_create_order_items_table.php
2026_09_23_000013_create_payments_table.php
2026_09_23_000014_create_order_status_histories_table.php
2026_09_23_000015_create_notifications_table.php   (php artisan notifications:table)
```
> Urutan penting: `umkm_profiles` sebelum `products`; `orders` sebelum `order_items`/`payments`.

Snippet acuan konvensi (berlaku untuk semua migration):
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('umkm_profile_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('sku', 50)->nullable()->unique();
    $table->text('description')->nullable();
    $table->unsignedBigInteger('price');           // rupiah, tanpa desimal
    $table->unsignedInteger('stock')->default(0);
    $table->unsignedInteger('weight_gram')->default(0);
    $table->string('unit', 20)->default('pcs');
    $table->string('thumbnail')->nullable();
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('sold_count')->default(0);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['is_active', 'category_id']);
});
```

---

## 2. STRUKTUR ROUTING

### 2.1 Routes Publik (`routes/web.php`)
| Method | URI | Controller@action | Name |
|---|---|---|---|
| GET | `/` | HomeController@index | `home` |
| GET | `/tentang` | PageController@about | `about` |
| GET | `/artikel` | ArticleController@index | `articles.index` |
| GET | `/artikel/{article:slug}` | ArticleController@show | `articles.show` |
| GET | `/umkm` | UmkmProfileController@index | `umkm.index` |
| GET | `/umkm/{umkmProfile:slug}` | UmkmProfileController@show | `umkm.show` |
| GET | `/katalog` | ProductController@index | `products.index` |
| GET | `/katalog/{product:slug}` | ProductController@show | `products.show` |
| GET | `/keranjang` | CartController@index | `cart.index` |
| POST | `/keranjang` | CartController@store | `cart.store` |
| PATCH | `/keranjang/{cartItem}` | CartController@update | `cart.update` |
| DELETE | `/keranjang/{cartItem}` | CartController@destroy | `cart.destroy` |
| GET | `/ongkir/titik` | ShippingController@points | `shipping.points` |
| POST | `/ongkir/hitung` | ShippingController@calculate | `shipping.calculate` (throttle:30,1) |
| GET | `/lacak` | OrderTrackingController@index | `orders.track` |
| POST | `/lacak` | OrderTrackingController@result | `orders.track.result` |

> Keranjang dapat diakses guest (cart berbasis `session_id`); saat login, cart di-merge ke `user_id`.

### 2.2 Routes Customer (`middleware: auth`)
```
GET   /checkout                             checkout.index
POST  /checkout                             checkout.store
GET   /pesanan                              orders.index
GET   /pesanan/{order:order_number}         orders.show
POST  /pesanan/{order:order_number}/bayar   payments.store    (throttle:5,1)
POST  /pesanan/{order:order_number}/batal   orders.cancel
GET   /profil                               profile.edit
PATCH /profil                               profile.update
POST  /daftar-umkm                          umkm.register     (pengajuan jadi UMKM)
```
Auth bawaan Breeze: `routes/auth.php` (`login`, `register`, `password.*`, `logout`).

### 2.3 Routes Admin (`middleware: auth + role:admin`, prefix `admin`, name `admin.`)
```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('orders/{order}/status',  [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/cancel',  [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('payments',                    [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}/proof',    [PaymentController::class, 'proof'])->name('payments.proof'); // file private
    Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::patch('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::resource('products',        ProductController::class);
    Route::resource('categories',      CategoryController::class)->except('show');
    Route::resource('umkm',            UmkmController::class)->except('destroy');
    Route::patch('umkm/{umkm}/verify', [UmkmController::class, 'verify'])->name('umkm.verify');
    Route::resource('articles',        ArticleController::class)->except('show');
    Route::resource('delivery-points', DeliveryPointController::class)->except('show');
    Route::resource('customers',       CustomerController::class)->only(['index', 'show']);

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
});
```

### 2.4 Strategi 5 Opsi Titik Pengiriman (sesuai SRS)
- **Opsi 1-4 (gratis ongkir):** 4 baris `delivery_points` dengan `type=free_point`, dipilih lewat `delivery_point_id` dan `shipping_method=pickup_point`.
- **Opsi 5 (alamat kustom):** **virtual option** â€” `shipping_method=custom_delivery`, tidak menyimpan `delivery_point_id`. Alamat, `shipping_lat/lng`, `shipping_distance_km`, dan `shipping_fee` disimpan langsung di `orders`.
- Origin (Kampus Tuguran) & tarif (`shipping_base_fee`, `shipping_cost_per_km`, `shipping_free_radius_km`, `shipping_max_distance_km`) dibaca dari tabel `settings`.
- Pendekatan hybrid ini menghindari hardcode dan membuat opsi ke-5 terhitung dinamis.
  *(Alternatif ditolak: menjadikan opsi ke-5 baris `delivery_points` `type=custom` lalu meng-update kolomnya saat checkout â€” kurang bersih.)*

### 2.5 Fase 2 â€” Routes Panel UMKM
```
Route::middleware(['auth','role:umkm'])->prefix('umkm-panel')->name('umkm.')->group(...)
// products CRUD (scoped ke umkm_profile_id milik user), order masuk, statistik
```

---

## 3. MODEL, CONTROLLER & KELAS PENDUKUNG

### 3.1 Models (`app/Models`) â€” 15 model
| Model | Relasi & helper kunci |
|---|---|
| `User` | hasOne `umkmProfile`; hasMany `orders`, `articles`; helper `isAdmin()`, `isUmkm()`; scope `role()` |
| `Setting` | static `get($key, $default)` / `set($key, $value)` dengan `Cache::remember` 1 jam |
| `Category` | hasMany `products` |
| `UmkmProfile` | belongsTo `user`; hasMany `products`, `articles`; scope `verified()`; `getRouteKeyName()='slug'` |
| `ArticleCategory` | hasMany `articles` |
| `Article` | belongsTo `author(User)`, `category`, `umkmProfile`; scope `published()`; route key `slug` |
| `Product` | belongsTo `umkmProfile`, `category`; hasMany `images`; route key `slug`; accessor `price_formatted` |
| `ProductImage` | belongsTo `product` |
| `Cart` | belongsTo `user`; hasMany `items`; static `forCurrentUser()`; accessor `subtotal` |
| `CartItem` | belongsTo `cart`, `product`, `umkmProfile` |
| `DeliveryPoint` | scope `active()`, `freePoints()`; accessor `coordinates` |
| `Order` | belongsTo `user`, `deliveryPoint`, `verifier`; hasMany `items`, `payments`, `statusHistories`; **OrderObserver** generate `order_number` + tulis history |
| `OrderItem` | belongsTo `order`, `product`, `umkmProfile` |
| `Payment` | belongsTo `order`, `verifier`; scope `pending()` |
| `OrderStatusHistory` | belongsTo `order`, `changer` |

### 3.2 Controllers
**Public (`app/Http/Controllers`)**
`HomeController`, `PageController`, `ArticleController`, `UmkmProfileController`, `ProductController`, `CartController`, `CheckoutController`, `ShippingController` (JSON hitung ongkir), `PaymentController` (upload bukti + tampil QRIS), `OrderTrackingController`

**Customer**: `Customer/OrderController`, `ProfileController` (bawaan Breeze), `UmkmRegistrationController`

**Admin (`app/Http/Controllers/Admin`)**
`DashboardController`, `OrderController`, `PaymentController`, `ProductController`, `CategoryController`, `UmkmController`, `ArticleController`, `ArticleCategoryController`, `DeliveryPointController`, `CustomerController`, `SettingController`, `ReportController`

### 3.3 Services (`app/Services`)
| Service | Tanggung jawab |
|---|---|
| `DistanceService` | Haversine: `distanceKm($lat1, $lng1, $lat2, $lng2)` |
| `GeocodingService` | Nominatim/OSM geocode alamat -> lat/lng + `Cache::remember` 24 jam |
| `ShippingCalculator` | Baca `settings` -> `fee = base + (per_km x jarak)`, terapkan radius gratis & max jarak |
| `CartService` | `addToCart`, `mergeGuestCart`, `updateQty`, `clear` |
| `OrderService` | `createFromCart()` dalam **DB transaction + `lockForUpdate()`** (anti-oversell), generate `order_number`, decrement stock, snapshot item |
| `PaymentService` | Simpan bukti ke disk private, verifikasi/tolak |
| `OrderStatusService` | Validasi transisi status yang sah + catat history |
| *(fase 2)* `MidtransService` | Dynamic QRIS |

### 3.4 Middleware, FormRequest, Policy, Observer, Notification, Seeder
- **Middleware**: `RoleMiddleware` (`role:admin|umkm`), `EnsureCartNotEmpty` â€” didaftarkan sebagai alias di `bootstrap/app.php`.
- **FormRequest**: `StoreCartItemRequest`, `CalculateShippingRequest`, `StoreCheckoutRequest`, `StorePaymentProofRequest`, `StoreProductRequest`, `UpdateProductRequest`, `StoreArticleRequest`, `StoreDeliveryPointRequest`, `UpdateOrderStatusRequest`, `StoreUmkmProfileRequest`, `UpdateSettingsRequest`.
- **Policy**: `OrderPolicy` (customer hanya melihat ordernya sendiri), `PaymentPolicy`, `ProductPolicy`, `ArticlePolicy`.
- **Observer**: `OrderObserver` (generate `order_number`, log perubahan status), `ProductObserver` (generate slug).
- **Notification**: `NewOrderReceived` (-> admin), `PaymentProofUploaded` (-> admin), `OrderStatusChanged` (-> customer).
- **Seeder**: `UserSeeder`, `SettingSeeder`, `DeliveryPointSeeder` (4 titik gratis), `CategorySeeder`, `UmkmProfileSeeder`, `ProductSeeder`, `ArticleSeeder`.

---

## 4. ROADMAP EKSEKUSI (Step by Step)

### FASE 0 â€” Setup Environment & Toolchain (0.5 hari)
1. Enable ekstensi PHP di `C:\xampp\php\php.ini`: `extension=zip`, `extension=gd`, `extension=intl` -> verifikasi `php -m`.
2. Sediakan Composer di PATH (memakai `C:\laragon\bin\composer\composer.phar` atau installer resmi).
3. Jalankan MySQL 8.0.30 (Laragon, datadir `C:\laragon\data\mysql-8`) pada port 3306.
4. Buat database `ewedu` (utf8mb4 / utf8mb4_unicode_ci).
5. `git init` + `.gitignore` + commit pertama `Rancangan.md`.

### FASE 1 â€” Bootstrap Laravel + Tailwind (0.5 hari)
```
composer create-project laravel/laravel . --prefer-dist
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
php artisan migrate
```

### FASE 2 â€” Auth, Role & Layout Dasar (1 hari)
- Modifikasi `users` (+`role/phone/address/avatar/is_active`), buat `RoleMiddleware`, daftarkan alias di `bootstrap/app.php`.
- Layout: `layouts/public.blade.php`, `layouts/admin.blade.php`, `layouts/umkm.blade.php` + komponen Blade (`x-navbar`, `x-footer`, `x-alert`, `x-card`, `x-empty-state`).
- `UserSeeder` admin + helper `isAdmin()`.

### FASE 3 â€” Database & Domain Layer (2 hari)
- Buat 15 migration sesuai Â§1.3, jalankan `php artisan migrate:fresh`.
- Buat seluruh model + relasi + casts + accessor, factory, dan seeder. `php artisan storage:link`.
- Unit test Haversine (koordinat Kampus Tuguran -> titik referensi).

### FASE 4 â€” Halaman Publik & Literasi UMKM (2-3 hari)
Landing page (hero, produk unggulan, artikel terbaru, profil UMKM, CTA), daftar & detail artikel, daftar & detail profil UMKM, halaman 404/500, meta SEO dasar.

### FASE 5 â€” Katalog & Keranjang (2-3 hari)
Katalog dengan filter kategori/UMKM/harga + search + sorting + pagination; detail produk (galeri, stok, tombol tambah cart via Alpine); `CartController` + `CartService` (guest session cart -> merge saat login); halaman keranjang dengan update qty.

### FASE 6 â€” Checkout & Logistik Mandiri (3-4 hari) â€” inti SRS
- Halaman checkout: data penerima, radio 5 opsi pengiriman, alamat kustom + peta Leaflet (opsional klik pin) atau input alamat -> geocode.
- `POST /ongkir/hitung` (fetch + Alpine) -> `ShippingCalculator` -> `{distance_km, fee, is_free, out_of_range}`.
- `OrderService::createFromCart()` dengan transaction + `lockForUpdate`, kurangi stok, snapshot item, generate `EWD-YYYYMMDD-XXXX`.
- Halaman sukses order + ringkasan ongkir.

### FASE 7 â€” Pembayaran COD & QRIS (2 hari)
- COD: `payment_method=cod`, `status=pending`. QRIS: tampilkan QR dari `settings`, upload bukti (<=2MB, jpg/png/webp).
- `StorePaymentProofRequest` validasi mime + size; simpan ke disk **private**; set `payment_status=awaiting_verification`; notifikasi admin.

### FASE 8 â€” Dashboard Admin (3-4 hari)
- Dashboard: kartu statistik (order hari ini, menunggu verifikasi, pendapatan, produk terjual) + grafik 7 hari.
- Manajemen Order: list + filter status/tanggal, detail, validasi/tolak, update status pengiriman (transisi divalidasi `OrderStatusService`), cetak invoice/label.
- Verifikasi Pembayaran: antrian bukti, preview via route `admin.payments.proof`, Verify/Reject + alasan.
- CRUD Produk, Kategori, UMKM (+verifikasi), Artikel, Titik Pengiriman, Customer, Pengaturan (upload QRIS, tarif/km), Laporan penjualan.

### FASE 9 â€” QA, Polish & Testing (2 hari)
- **Feature test (Pest)**: add-to-cart, merge guest cart, checkout stok habis, titik pengiriman invalid, transisi status ilegal -> 403, upload bukti bukan gambar -> 422, customer tidak bisa akses admin.
- **Unit test**: `DistanceService`, `ShippingCalculator` (radius gratis, max jarak, pembulatan).
- `php artisan test`, `vendor/bin/pint`, cek responsive, `php artisan optimize`.

### FASE 10 â€” Deployment (opsional, 1 hari)
`.env` produksi, `php artisan migrate --force`, `npm run build`, symlink storage, queue `database` + cron scheduler, backup DB harian.

**Total estimasi: +/- 18-22 hari kerja** untuk 1 developer.

---

## 5. REKOMENDASI TEKNIS (WAJIB DIPERHATIKAN)

1. **Anti-oversell**: wajib `DB::transaction` + `lockForUpdate()` saat checkout â€” bug tersering di e-commerce.
2. **Validasi transisi status** lewat map eksplisit (`pending -> confirmed -> processing -> shipped -> delivered -> completed`; cancel hanya dari `pending`/`confirmed`). Jangan percaya input form admin.
3. **Bukti pembayaran di disk private** (`storage/app/private/payments`), disajikan via route `admin.payments.proof` ber-middleware â€” jangan di `storage/app/public`.
4. **Geocoding Nominatim**: set `User-Agent` yang jelas, cache 24 jam, sediakan fallback input lat/lng manual / klik peta agar demo tidak gagal saat kuota habis.
5. **Tanpa package permission berat** â€” enum + middleware cukup dan lebih mudah dijelaskan saat presentasi.
6. **Uang = integer rupiah**, hindari `float`/`decimal` untuk harga.
7. **`order_items.umkm_profile_id` disiapkan sejak awal** untuk split laporan/komisi per UMKM & panel UMKM fase 2 tanpa refactor.
8. **Rate limit**: `throttle:30,1` pada hitung ongkir, `throttle:5,1` pada upload bukti.
9. **Konfigurasi tarif di tabel `settings`**, bukan config/env, agar admin bisa ubah tanpa deploy ulang.
10. **Notifikasi** pakai driver queue `database` (fase 2); `sync` aman untuk demo MVP.
11. **Testing** memakai Pest (bawaan Laravel 11/12), jangan menambah konfigurasi PHPUnit terpisah.

---

## 6. CATATAN LINGKUNGAN DEV (HASIL EKSEKUSI)

| Item | Nilai terverifikasi |
|---|---|
| PHP CLI | `C:\xampp\php\php.exe` v8.2.12 (php.ini: `C:\xampp\php\php.ini`, backup di `php.ini.bak-ewedu`) |
| Ekstensi PHP | `zip`, `gd`, `intl` diaktifkan (sebelumnya `;extension=...`) |
| Composer | `C:\laragon\bin\composer\composer.phar` v2.4.1 (belum di PATH) |
| MySQL server | Laragon `mysql-8.0.30-winx64` â€” port 3306, datadir `C:\laragon\data\mysql-8`, user `root` tanpa password |
| Database | `ewedu` (utf8mb4 / utf8mb4_unicode_ci) |
| Node / npm | v22.11.0 / 10.9.0 |
| Git | 2.46.0 â€” repo diinisialisasi di root proyek |
| Catatan | Laragon MySQL juga memuat DB lain milik user (`greenhouse_lokal`) â€” **jangan** dijalankan `migrate:fresh` ke DB tersebut |

---

## 7. CATATAN EKSEKUSI (log pembangunan)

### FASE 0 — Setup Environment ✅
Ekstensi `zip`/`gd`/`intl` aktif, Composer tersedia, MySQL Laragon port 3306, DB `ewedu` (utf8mb4_unicode_ci), `git init` + commit awal `Rancangan.md`.

### FASE 1 — Bootstrap Laravel + Tailwind ✅
- Laravel **12.69.2**, Breeze **2.4.2** (stack Blade), Alpine.js aktif.
- Tailwind yang dipakai Breeze adalah **v3.4** → `tailwind.config.js` dibaca otomatis, direktif `@config` (v4) **tidak** diperlukan.
- Palet brand (`primary/accent/background/text`) digabung ke config Breeze tanpa menghapus font Figtree & `@tailwindcss/forms`.
- `npm run build` sukses; `php artisan migrate` awal sukses.

### FASE 2 — Auth, Role & Layout Dasar ✅
- `users` ditambah: `role` enum(`customer`,`umkm`,`admin`) index, `phone`, `address`, `avatar`, `is_active`, `deleted_at` (softDeletes).
- `app/Http/Middleware/RoleMiddleware.php` + alias `role` di `bootstrap/app.php`. Bentuk yang didukung: `role:admin`, `role:admin,umkm`, `role:admin|umkm`. Tamu → redirect login; role salah / akun nonaktif → 403.
- Model `User`: konstanta `ROLE_*`, scope `role()/active()/admins()/umkmOwners()`, helper `isAdmin()`, `isUmkm()`, `isCustomer()`, `hasRole()`, `isActive()`, `roleLabel()`, `roleBadgeClass()`.
- Layout: `layouts/public.blade.php` (di-refactor memakai komponen), `layouts/admin.blade.php`, `layouts/umkm.blade.php`.
- Komponen Blade: `x-navbar`, `x-footer`, `x-alert` (flash session + error validasi otomatis), `x-card`, `x-empty-state`, `x-sidebar-link`.
- `UserSeeder` (idempoten) + state `admin()/umkm()/inactive()` pada `UserFactory`; `DatabaseSeeder` memanggil `UserSeeder`.
- Test: `tests/Feature/RoleAccessTest.php` (8 kasus) + `tests/Unit/UserRoleHelperTest.php` (4 kasus) → `php artisan test` **37 test lulus**. `tests/Feature/ProfileTest.php` disesuaikan ke `assertSoftDeleted` karena `users` memakai soft delete.
- Relasi domain pada `User` (`umkmProfile`, `orders`, `articles`) sengaja ditunda ke FASE 3 mengikuti pembuatan model terkait.

**Kredensial seeder (password: `password`)**

| Email | Role |
|---|---|
| `admin@ewedu.test` | admin |
| `umkm@ewedu.test` | umkm |
| `pembeli@ewedu.test` | customer |

### FASE 3 — Database & Domain Layer ✅
- 14 migration domain (§1.3) + `notifications` (skeleton Laravel 12): `settings`, `categories`, `umkm_profiles`, `article_categories`, `articles`, `products`, `product_images`, `carts`, `cart_items`, `delivery_points`, `orders`, `order_items`, `payments`, `order_status_histories`.
- Kebijakan FK: `cascadeOnDelete` untuk data anak yang tidak bermakna tanpa induk (order → items/payments/histories, cart → items, product → images) dan `nullOnDelete` untuk referensi historis (`orders.user_id`, `order_items.product_id`, `order_items.umkm_profile_id`, `payments.verified_by`) supaya snapshot pesanan tetap utuh.
- 14 model domain: konstanta status/enum (`Order::TRANSITIONS`, `STATUS_LABELS`, `PAYMENT_*`), relasi, scope (`active`, `featured`, `published`, `search`, `freePoints`, `custom`, `ordered`, `awaitingVerification`, `verified`), accessor (`price_formatted`, `discount_percent`, `total_formatted`, `shipping_fee_formatted`, `items_count`, `subtotal` keranjang) dan helper (`finalPrice()`, `hasDiscount()`, `isAvailable()`, `canTransitionTo()`, `canBeCancelled()`, `statusLabel()`, `whatsappUrl()`, `coordinates()`, `thumbnailUrl()`, `generateOrderNumber()` → `EWD-YYYYMMDD-XXXX`).
- Soft delete: `umkm_profiles`, `articles`, `products`, `delivery_points`, `orders` (+ `users` dari FASE 2). Relasi domain pada `User` (`umkmProfile`, `orders`, `articles`) kini aktif.
- 14 factory beserta state siap pakai: `admin()/umkm()/inactive()` (user), `custom()/inactive()` (titik kirim), `discounted()/outOfStock()/featured()` (produk), `unverified()/unpublished()` (UMKM), `verified()/rejected()/qris()` (pembayaran), `guest()/qris()/paid()/completed()/cancelled()/customDelivery()/pickupAt()` (pesanan).
- 7 seeder idempoten (dijalankan ulang aman, `updateOrCreate` + `withTrashed()`): `SettingSeeder` (12 baris), `CategorySeeder` (6), `ArticleCategorySeeder` (4), `DeliveryPointSeeder` (4 titik gratis + 1 master rule alamat kustom), `UmkmProfileSeeder` (4 mitra terverifikasi), `ProductSeeder` (8 produk), `ArticleSeeder` (6 artikel terbit, 1 di antaranya cerita profil `kopi-tuguran`). `DatabaseSeeder` memanggil semuanya sesuai urutan FK.
- `app/Services/DistanceService.php`: Haversine stateless (`distanceKm`, `distanceBetweenPoints`, `distanceFromOrigin` memakai `settings.campus_origin_lat/lng`, `isWithinRadius`, `exceedsMaxDistance`) sebagai dasar kalkulasi ongkir FASE 6.
- `thumbnail`/`logo`/`cover` **sengaja `null`** (UI memakai placeholder) — upload nyata baru di FASE 8.
- Test: `tests/Unit/DistanceServiceTest.php` (8 kasus) + `tests/Feature/DomainRelationTest.php` (9) + `tests/Feature/DomainSeederTest.php` (4) → `php artisan test` **63 test lulus (229 assertion)**. `public/storage` sudah tertaut, `php artisan db:seed` diverifikasi idempoten di MySQL `ewedu`, dan `php artisan view:cache` sukses.
- ⚠️ **Deviasi testing**: Pest **tidak** terpasang (repo memakai PHPUnit 11.5 dari skeleton Laravel 12) → seluruh test ditulis bergaya kelas PHPUnit (`test_snake_case`). Keputusan ini ditinjau ulang pada FASE 9 (lihat §5 poin 11).

**Akun mitra seeder tambahan (password: `password`)**

| Email | UMKM | Slug |
|---|---|---|
| `batik.srikandi@ewedu.test` | Batik Srikandi Magelang | `batik-srikandi-magelang` |
| `kriya.getas@ewedu.test` | Kriya Bambu Getas | `kriya-bambu-getas` |
| `dapur.sari@ewedu.test` | Dapur Bunda Sari | `dapur-bunda-sari` |

### FASE 4 — Halaman Publik & Literasi UMKM ✅
- Routes publik (§2.1): `home` (`/`), `about` (`/tentang`), `umkm.index` (`/umkm`), `umkm.show` (`/umkm/{slug}`), `articles.index` (`/artikel`), `articles.show` (`/artikel/{slug}`). `/` tidak lagi memakai `welcome` → `resources/views/welcome.blade.php` (skeleton) dihapus karena sudah tidak dirujuk siapa pun.
- Controller: `HomeController@index` (produk unggulan, artikel terbaru, mitra terverifikasi, kategori, statistik hero, `whatsapp_admin` dari `settings`), `ArticleController@index/show`, `UmkmProfileController@index/show`, `PageController@about`. Semua query memakai scope domain FASE 3 (`active`, `featured`, `inStock`, `published`, `verified`, `search`, `categorySlug`, `active/ordered` kategori) + eager loading agar bebas N+1; halaman detail memakai `abort_unless(..., 404)` sehingga draft, artikel terjadwal, profil belum terverifikasi, dan profil belum terbit dianggap tidak ada.
- Helper model: `Article::scopeSearch` (judul/ringkasan/isi), `scopeCategorySlug`, `readingTimeMinutes()` (200 kata/menit, minimal 1), accessor `published_at_formatted` (locale `id`, mis. `05 Januari 2026`); `UmkmProfile::scopeSearch` (nama usaha/nama pemilik/deskripsi).
- View: `home.blade.php` + partial `home/{hero,sorotan,kategori,produk-unggulan,mitra,literasi,cta}`, `articles/{index,show}` + partial `articles/partials/{filter,sidebar,share,aside}`, `umkm/{index,show}` + partial `umkm/partials/{filter,hero,story,products,aside}`, `about.blade.php`, dan `errors/{404,500}.blade.php` — semuanya memakai `layouts.public`.
- Komponen baru: `x-section-heading`, `x-page-header`, `x-product-card`, `x-article-card`, `x-umkm-card`, `x-whatsapp-button` (nomor dinormalisasi ke `62...`, tidak merender apa pun bila `settings.whatsapp_admin` kosong). `x-navbar`/`x-footer` kini memakai `route()` untuk menu publik (`home`, `umkm.index`, `articles.index`, `about`).
- Alpine.js: menu mobile, slider "Sorotan Produk" (auto-putar 6 detik), filter kategori produk sisi klien, panel filter yang bisa dilipat, accordion FAQ, dan tombol "salin tautan" artikel (Clipboard API).
- Meta SEO dasar: `@section('title')` + `@section('meta_description')` pada setiap halaman publik.
- Filter daftar: `/artikel?q=&kategori=`, `/umkm?q=&kategori=&urut=terbaru|produk` — input dinormalkan (`trim` + dipotong 100 karakter) dan `urut` tak dikenal otomatis kembali ke `terbaru`. `product-card` masih menautkan ke `/katalog/{slug}` (URL nyata menyusul di FASE 5).
- Test: `tests/Feature/PublicPageTest.php` (5), `ArticlePageTest.php` (8), `UmkmPageTest.php` (8), `ArticleModelTest.php` (4) + `tests/Unit/ArticleHelperTest.php` (3) → `php artisan test` **90 test lulus (347 assertion)**. `tests/Feature/ExampleTest.php` bawaan Breeze dihapus karena `/` kini menyentuh database (butuh `RefreshDatabase` + seeder).
- Verifikasi manual: `/`, `/tentang`, `/artikel`, `/artikel/{slug}`, `/umkm`, `/umkm/{slug}`, `/umkm?kategori=Kuliner`, `/artikel?q=kopi` semuanya `200` pada MySQL `ewedu` (data hasil seeder FASE 3); slug tidak dikenal → `404`.
- ⚠️ **Gotcha Blade**: direktif yang menempel langsung pada kata (mis. `UMKM@if (...)`) **tidak** dikompilasi Blade karena butuh batas kata, sehingga muncul `syntax error, unexpected token "endif" (View: ...)`. Semua direktif kini ditulis dipisah spasi/baris.
- ⚠️ **Toolchain (ditunda ke FASE 9)**: `npm run build` dan `npm run dev` gagal di Node v22.11.0 karena Vite 7.3.6 mensyaratkan Node ≥ 20.19 / ≥ 22.12. Bundle CSS FASE 4 sementara dibangun lewat Tailwind CLI (`npx tailwindcss -i resources/css/app.css -o public/build/assets/app-*.css --minify`) sehingga aset publik tetap sinkron dengan view; perbaikan permanen = upgrade Node atau pin `vite@^6`.


