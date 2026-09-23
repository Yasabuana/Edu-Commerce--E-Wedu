<?php

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Keranjang: milik user login (`user_id`) atau guest (`session_id`).
 * Penggabungan cart guest → user terjadi saat login (FASE 5, `CartService`).
 */
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /* ----------------------------- Relations ---------------------------- */

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /* ------------------------------ Scopes ------------------------------ */

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /* ----------------------------- Helpers ------------------------------ */

    /**
     * Cart milik user login atau guest pada session berjalan (Rancangan §3.1).
     */
    public static function forCurrentUser(): self
    {
        $userId = auth()->id();

        if ($userId !== null) {
            return static::query()->firstOrCreate(['user_id' => $userId]);
        }

        $sessionId = app()->bound('session') ? session()->getId() : null;

        if ($sessionId === null) {
            throw new \RuntimeException('Keranjang guest memerlukan session aktif.');
        }

        return static::query()->firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Subtotal seluruh item keranjang.
     */
    protected function subtotal(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->items->sum(
            fn (CartItem $item): int => $item->price_snapshot * $item->qty
        ));
    }

    protected function totalQty(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->items->sum('qty'));
    }

    protected function itemCount(): Attribute
    {
        return Attribute::get(fn (): int => $this->items->count());
    }

    public function isEmpty(): bool
    {
        return $this->itemCount === 0;
    }
}
