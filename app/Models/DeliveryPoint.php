<?php

namespace App\Models;

use Database\Factories\DeliveryPointFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Inti fitur logistik mandiri: 4 titik gratis ongkir + 1 master rule
 * `type=custom` untuk opsi alamat kustom (Rancangan §2.4).
 */
class DeliveryPoint extends Model
{
    /** @use HasFactory<DeliveryPointFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_FREE_POINT = 'free_point';

    public const TYPE_CUSTOM = 'custom';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'latitude',
        'longitude',
        'is_free_shipping',
        'free_radius_km',
        'operation_hours',
        'notes',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_free_shipping' => 'boolean',
            'free_radius_km' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /* ------------------------------ Scopes ------------------------------ */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * 4 titik gratis ongkir yang bisa dipilih pembeli.
     */
    public function scopeFreePoints(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_FREE_POINT);
    }

    /**
     * Master rule alamat kustom (menyimpan origin & radius default).
     */
    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CUSTOM);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /* ----------------------------- Helpers ------------------------------ */

    public function isFreePoint(): bool
    {
        return $this->type === self::TYPE_FREE_POINT;
    }

    /**
     * Koordinat [lat, lng] untuk Haversine & peta Leaflet (accessor `coordinates`).
     */
    public function coordinates(): array
    {
        return [(float) $this->latitude, (float) $this->longitude];
    }

    /**
     * Label singkat untuk radio pilihan pengiriman di checkout.
     */
    public function shippingLabel(): string
    {
        $fee = $this->is_free_shipping ? 'Gratis Ongkir' : 'Berbayar';

        return "{$this->name} ({$this->code}) — {$fee}";
    }
}
