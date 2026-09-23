<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    /* ------------------------------ Statuses ---------------------------- */

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_AWAITING_VERIFICATION = 'awaiting_verification';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_REJECTED = 'rejected';

    public const PAYMENT_METHOD_COD = 'cod';

    public const PAYMENT_METHOD_QRIS = 'qris';

    public const SHIPPING_PICKUP_POINT = 'pickup_point';

    public const SHIPPING_CUSTOM_DELIVERY = 'custom_delivery';

    /**
     * Peta transisi status yang sah (Rancangan §5.2 — jangan percaya input form admin).
     *
     * @var array<string, list<string>>
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_READY],
        self::STATUS_READY => [self::STATUS_SHIPPED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    /**
     * Label status untuk badge UI.
     *
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_CONFIRMED => 'Dikonfirmasi',
        self::STATUS_PROCESSING => 'Diproses',
        self::STATUS_READY => 'Siap',
        self::STATUS_SHIPPED => 'Dikirim',
        self::STATUS_DELIVERED => 'Terkirim',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /**
     * @var array<string, string>
     */
    public const PAYMENT_STATUS_LABELS = [
        self::PAYMENT_UNPAID => 'Belum Bayar',
        self::PAYMENT_AWAITING_VERIFICATION => 'Menunggu Verifikasi',
        self::PAYMENT_PAID => 'Lunas',
        self::PAYMENT_REJECTED => 'Ditolak',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_point_id',
        'shipping_method',
        'shipping_address',
        'shipping_lat',
        'shipping_lng',
        'shipping_distance_km',
        'shipping_fee',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'status',
        'customer_note',
        'admin_note',
        'verified_by',
        'verified_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_lat' => 'float',
            'shipping_lng' => 'float',
            'shipping_distance_km' => 'float',
            'shipping_fee' => 'integer',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'verified_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Nomor pesanan `EWD-YYYYMMDD-XXXX` (Rancangan §0 & §3.1).
     * Dipakai `OrderFactory`, nantinya juga `OrderObserver`/`OrderService`.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'EWD-'.now()->format('Ymd').'-';

        $lastNumber = static::withTrashed()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function labelFor(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst($status);
    }

    /* ----------------------------- Relations ---------------------------- */

    /**
     * Null bila guest checkout.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<DeliveryPoint, $this>
     */
    public function deliveryPoint(): BelongsTo
    {
        return $this->belongsTo(DeliveryPoint::class);
    }

    /**
     * Admin yang memverifikasi pembayaran.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    /* ------------------------------ Scopes ------------------------------ */

    public function scopeStatus(Builder $query, string ...$statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopePaymentStatus(Builder $query, string ...$statuses): Builder
    {
        return $query->whereIn('payment_status', $statuses);
    }

    public function scopeAwaitingVerification(Builder $query): Builder
    {
        return $query->where('payment_status', self::PAYMENT_AWAITING_VERIFICATION);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /* ----------------------------- Helpers ------------------------------ */

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Batal hanya boleh dari `pending` / `confirmed` (Rancangan §5.2).
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function statusLabel(): string
    {
        return self::labelFor($this->status);
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-700',
            self::STATUS_CANCELLED => 'bg-rose-100 text-rose-700',
            self::STATUS_PENDING => 'bg-amber-100 text-amber-700',
            default => 'bg-sky-100 text-sky-700',
        };
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID => 'bg-emerald-100 text-emerald-700',
            self::PAYMENT_REJECTED => 'bg-rose-100 text-rose-700',
            self::PAYMENT_AWAITING_VERIFICATION => 'bg-amber-100 text-amber-700',
            default => 'bg-slate-200 text-slate-700',
        };
    }

    public function shippingMethodLabel(): string
    {
        return $this->shipping_method === self::SHIPPING_CUSTOM_DELIVERY
            ? 'Alamat Kustom'
            : 'Titik Jemput Gratis';
    }

    public function paymentMethodLabel(): string
    {
        return $this->payment_method === self::PAYMENT_METHOD_COD ? 'COD (Bayar di Tempat)' : 'QRIS';
    }

    /* ---------------------------- Accessors ----------------------------- */

    protected function totalFormatted(): Attribute
    {
        return Attribute::get(fn (): string => 'Rp '.number_format((int) $this->total, 0, ',', '.'));
    }

    protected function subtotalFormatted(): Attribute
    {
        return Attribute::get(fn (): string => 'Rp '.number_format((int) $this->subtotal, 0, ',', '.'));
    }

    protected function shippingFeeFormatted(): Attribute
    {
        return Attribute::get(fn (): string => $this->shipping_fee === 0
            ? 'Gratis'
            : 'Rp '.number_format((int) $this->shipping_fee, 0, ',', '.'));
    }

    protected function itemsCount(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->items->sum('qty'));
    }
}
