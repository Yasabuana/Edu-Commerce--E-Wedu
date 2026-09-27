<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /* --------------------- Opsi pengurutan katalog (FASE 5) ------------- */

    public const SORT_TERBARU = 'terbaru';

    public const SORT_TERMURAH = 'termurah';

    public const SORT_TERMAHAL = 'termahal';

    public const SORT_TERLARIS = 'terlaris';

    public const SORT_RATING = 'rating';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'umkm_profile_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'discount_price',
        'stock',
        'weight_gram',
        'unit',
        'thumbnail',
        'is_active',
        'is_featured',
        'sold_count',
        'views',
        'rating_avg',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'discount_price' => 'integer',
            'stock' => 'integer',
            'weight_gram' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sold_count' => 'integer',
            'views' => 'integer',
            'rating_avg' => 'float',
        ];
    }

    /**
     * URL memakai slug (Rancangan §3.1).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ----------------------------- Relations ---------------------------- */

    /**
     * @return BelongsTo<UmkmProfile, $this>
     */
    public function umkmProfile(): BelongsTo
    {
        return $this->belongsTo(UmkmProfile::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /* ------------------------------ Scopes ------------------------------ */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Produk aktif milik UMKM terverifikasi & sudah terbit — satu-satunya
     * kombinasi yang boleh tampil di katalog publik (FASE 5).
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->active()->whereHas(
            'umkmProfile',
            fn (Builder $umkm) => $umkm->verified()->published(),
        );
    }

    /**
     * Pencarian katalog: nama produk, deskripsi, atau nama UMKM (FASE 5).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('umkmProfile', fn (Builder $u) => $u->where('business_name', 'like', "%{$term}%"));
        });
    }

    /**
     * Filter kategori katalog berdasarkan slug kategori.
     */
    public function scopeCategorySlug(Builder $query, ?string $slug): Builder
    {
        $slug = trim((string) $slug);

        if ($slug === '') {
            return $query;
        }

        return $query->whereHas('category', fn (Builder $category) => $category->where('slug', $slug));
    }

    /**
     * Filter katalog berdasarkan slug UMKM penjual.
     */
    public function scopeUmkmSlug(Builder $query, ?string $slug): Builder
    {
        $slug = trim((string) $slug);

        if ($slug === '') {
            return $query;
        }

        return $query->whereHas('umkmProfile', fn (Builder $umkm) => $umkm->where('slug', $slug));
    }

    /**
     * Filter rentang harga memakai harga efektif (memperhitungkan diskon).
     */
    public function scopePriceBetween(Builder $query, ?int $min, ?int $max): Builder
    {
        if ($min === null && $max === null) {
            return $query;
        }

        $expression = 'COALESCE(discount_price, price)';

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        if ($min !== null) {
            $query->whereRaw("{$expression} >= ?", [$min]);
        }

        if ($max !== null) {
            $query->whereRaw("{$expression} <= ?", [$max]);
        }

        return $query;
    }

    /**
     * Pengurutan katalog; nilai tak dikenal otomatis jatuh ke `terbaru`.
     */
    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match (static::normalizeSort($sort)) {
            self::SORT_TERMURAH => $query->orderByRaw('COALESCE(discount_price, price) asc')->orderBy('name'),
            self::SORT_TERMAHAL => $query->orderByRaw('COALESCE(discount_price, price) desc')->orderBy('name'),
            self::SORT_TERLARIS => $query->orderByDesc('sold_count')->orderByDesc('views'),
            self::SORT_RATING => $query->orderByDesc('rating_avg')->orderByDesc('sold_count'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /* ----------------------------- Helpers ------------------------------ */

    /**
     * Daftar opsi pengurutan katalog untuk dropdown/tautan filter.
     *
     * @return array<string, string>
     */
    public static function sortOptions(): array
    {
        return [
            self::SORT_TERBARU => 'Terbaru',
            self::SORT_TERMURAH => 'Harga termurah',
            self::SORT_TERMAHAL => 'Harga tertinggi',
            self::SORT_TERLARIS => 'Terlaris',
            self::SORT_RATING => 'Rating tertinggi',
        ];
    }

    /**
     * Normalisasi nilai `urut` dari query string (murni, tanpa query DB).
     */
    public static function normalizeSort(?string $sort): string
    {
        $sort = trim((string) $sort);

        return array_key_exists($sort, static::sortOptions()) ? $sort : self::SORT_TERBARU;
    }

    /**
     * Harga efektif (memperhitungkan diskon).
     */
    public function finalPrice(): int
    {
        return (int) ($this->discount_price ?? $this->price);
    }

    /**
     * Harga tampil terformat, mis. "Rp 15.000" (accessor `price_formatted`).
     */
    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn (): string => 'Rp '.number_format($this->finalPrice(), 0, ',', '.'));
    }

    protected function originalPriceFormatted(): Attribute
    {
        return Attribute::get(fn (): string => 'Rp '.number_format((int) $this->price, 0, ',', '.'));
    }

    /**
     * Persentase diskon (0 bila tidak ada diskon).
     */
    protected function discountPercent(): Attribute
    {
        return Attribute::get(function (): int {
            if (! $this->discount_price || $this->price <= 0 || $this->discount_price >= $this->price) {
                return 0;
            }

            return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
        });
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }

    public function isAvailable(): bool
    {
        return (bool) $this->is_active && $this->stock > 0;
    }

    /**
     * URL thumbnail (disk public) — fallback ke gambar utama galeri.
     */
    public function thumbnailUrl(): ?string
    {
        $path = $this->thumbnail ?: $this->images->firstWhere('is_primary', true)?->path;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Daftar URL galeri untuk halaman detail: gambar utama lebih dulu, lalu
     * seluruh foto galeri (tanpa duplikat).
     *
     * @return list<string>
     */
    public function galleryUrls(): array
    {
        $paths = $this->thumbnail ? [$this->thumbnail] : [];

        foreach ($this->images as $image) {
            if ($image->path !== null && ! in_array($image->path, $paths, true)) {
                $paths[] = $image->path;
            }
        }

        return array_values(array_map(
            fn (string $path): string => Storage::disk('public')->url($path),
            $paths,
        ));
    }

    /**
     * Boleh tampil di katalog publik? (produk aktif + UMKM terverifikasi & terbit)
     */
    public function isPubliclyVisible(): bool
    {
        $umkm = $this->umkmProfile;

        return (bool) $this->is_active
            && $umkm !== null
            && $umkm->isVerified()
            && $umkm->published_at !== null
            && $umkm->published_at->isPast();
    }
}
