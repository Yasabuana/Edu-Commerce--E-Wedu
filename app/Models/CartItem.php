<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'umkm_profile_id',
        'price_snapshot',
        'qty',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_snapshot' => 'integer',
            'qty' => 'integer',
        ];
    }

    /* ----------------------------- Relations ---------------------------- */

    /**
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
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

    /* ----------------------------- Helpers ------------------------------ */

    protected function subtotal(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->price_snapshot * (int) $this->qty);
    }

    /**
     * Stok tersisa masih mencukupi qty keranjang?
     */
    public function stockIsEnough(): bool
    {
        return $this->product !== null && $this->product->stock >= $this->qty;
    }

    /**
     * Baris ini milik cart tertentu? (guard kepemilikan pada update/hapus item)
     */
    public function isOwnedBy(?Cart $cart): bool
    {
        return $cart !== null && (int) $this->cart_id === (int) $cart->getKey();
    }
}
