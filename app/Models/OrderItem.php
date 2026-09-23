<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Snapshot nama/harga produk agar histori pesanan tidak berubah
 * meski produk diedit atau dihapus (Rancangan §1.2).
 */
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'umkm_profile_id',
        'product_name',
        'product_sku',
        'product_thumbnail',
        'price',
        'qty',
        'subtotal',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'qty' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    /* ----------------------------- Relations ---------------------------- */

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Null bila produk sudah dihapus permanen.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<UmkmProfile, $this>
     */
    public function umkmProfile(): BelongsTo
    {
        return $this->belongsTo(UmkmProfile::class);
    }

    /* ---------------------------- Accessors ----------------------------- */

    protected function subtotalFormatted(): Attribute
    {
        return Attribute::get(fn (): string => 'Rp '.number_format((int) $this->subtotal, 0, ',', '.'));
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn (): string => 'Rp '.number_format((int) $this->price, 0, ',', '.'));
    }
}
