<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

/**
 * COD & QRIS dalam satu tabel. Bukti transfer disimpan di disk privat
 * (storage/app/private/payments) dan disajikan lewat route admin
 * ber-middleware (Rancangan §1.2 & §5.3).
 */
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    public const METHOD_COD = 'cod';

    public const METHOD_QRIS = 'qris';

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Disk penyimpanan bukti pembayaran (bukan disk publik).
     */
    public const PROOF_DISK = 'local';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'proof_path',
        'sender_name',
        'sender_bank',
        'qris_reference',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'gateway_response',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'verified_at' => 'datetime',
            'gateway_response' => 'array',
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
     * Admin yang memverifikasi/menolak bukti.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /* ------------------------------ Scopes ------------------------------ */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeMethod(Builder $query, string $method): Builder
    {
        return $query->where('method', $method);
    }

    /* ----------------------------- Helpers ------------------------------ */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function hasProof(): bool
    {
        return $this->proof_path !== null;
    }

    public function methodLabel(): string
    {
        return $this->method === self::METHOD_COD ? 'COD (Bayar di Tempat)' : 'QRIS';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_VERIFIED => 'Terverifikasi',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Menunggu Verifikasi',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_VERIFIED => 'bg-emerald-100 text-emerald-700',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    /**
     * URL pratinjau bukti via route admin ber-middleware (file privat).
     * Null bila route belum terdaftar (FASE 8 belum dijalankan).
     */
    public function proofUrl(): ?string
    {
        if (! $this->hasProof() || ! Route::has('admin.payments.proof')) {
            return null;
        }

        return route('admin.payments.proof', $this);
    }
}
