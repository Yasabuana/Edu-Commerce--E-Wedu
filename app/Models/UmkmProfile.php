<?php

namespace App\Models;

use Database\Factories\UmkmProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UmkmProfile extends Model
{
    /** @use HasFactory<UmkmProfileFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'owner_name',
        'category_label',
        'description',
        'logo',
        'cover_image',
        'phone',
        'whatsapp',
        'address',
        'latitude',
        'longitude',
        'instagram',
        'website',
        'is_verified',
        'verified_at',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /* ------------------------------ Scopes ------------------------------ */

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Profil yang sudah dipublikasikan (tampil di halaman publik).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Pencarian mitra: nama usaha, nama pemilik, atau deskripsi (FASE 4).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('business_name', 'like', "%{$term}%")
                ->orWhere('owner_name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /* ----------------------------- Helpers ------------------------------ */

    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }

    /**
     * URL logo (disk public) atau placeholder bila belum ada.
     */
    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    /**
     * Tautan chat WhatsApp (dipakai CTA halaman profil UMKM).
     */
    public function whatsappUrl(): ?string
    {
        $number = $this->whatsapp ?: $this->phone;

        if (! $number) {
            return null;
        }

        $number = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $number));

        return "https://wa.me/{$number}";
    }

    /**
     * Koordinat [lat, lng] untuk kalkulasi jarak/peta Leaflet.
     *
     * @return array{0: float, 1: float}|null
     */
    public function coordinates(): ?array
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return [(float) $this->latitude, (float) $this->longitude];
    }
}
