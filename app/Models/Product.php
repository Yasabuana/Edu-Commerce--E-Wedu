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

    /* ----------------------------- Helpers ------------------------------ */

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
}
