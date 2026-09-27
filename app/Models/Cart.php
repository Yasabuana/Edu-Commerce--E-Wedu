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
     * Kunci session penyimpan id session keranjang guest.
     *
     * Diperlukan karena `SessionGuard::updateSession()` memindahkan (migrate)
     * session id **sebelum** event `Login` dipancarkan, sehingga id lama harus
     * diingat agar keranjang guest bisa digabung ke keranjang user (FASE 5).
     */
    public const GUEST_SESSION_KEY = 'e_wedu.guest_cart_session';

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

        // Jejak session guest dipakai `CartService::mergeGuestCart()` saat login.
        session()->put(self::GUEST_SESSION_KEY, $sessionId);

        return static::query()->firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Cart yang sedang aktif **tanpa membuat baris baru** (null bila belum ada).
     *
     * Dipakai halaman keranjang publik & badge navbar: membuka halaman tidak
     * boleh meninggalkan baris cart kosong di database.
     */
    public static function currentCart(): ?self
    {
        $userId = auth()->id();

        if ($userId !== null) {
            return static::query()->forUser($userId)->first();
        }

        if (! app()->bound('session')) {
            return null;
        }

        $sessionId = (string) session()->getId();

        if ($sessionId === '') {
            return null;
        }

        return static::query()->forSession($sessionId)->first();
    }

    /**
     * Total qty keranjang berjalan untuk badge navbar (0 bila belum ada cart).
     *
     * Aman dipanggil pada setiap render halaman: read-only, tidak membuat cart,
     * dan tidak melempar error bila tabel belum siap (mis. halaman error 500).
     */
    public static function currentItemCount(): int
    {
        try {
            return (int) static::currentCart()?->total_qty;
        } catch (\Throwable) {
            return 0;
        }
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
