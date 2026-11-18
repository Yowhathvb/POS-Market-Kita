<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'invoice_number',
        'magic_token',
        'store_website_id',
        'seller_user_id',
        'buyer_email',
        'buyer_google_sub',
        'total_amount',
        'status',
        'value',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'value' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $trx) {
            if (empty($trx->uuid)) {
                $trx->uuid = (string) Str::uuid();
            }
            if (empty($trx->invoice_number)) {
                $trx->invoice_number = static::generateNextInvoiceNumber();
            }
            if (empty($trx->magic_token)) {
                $trx->magic_token = Str::random(32);
            }
        });
    }

    /**
     * Dapatkan URL Magic Link gateway untuk produk digital transaksi ini.
     * Format: {domain}/{id_users}/produk/{magic_token}
     */
    public function getMagicLinkUrl(): string
    {
        $idUsers = $this->relationLoaded('seller')
            ? ($this->seller?->id_users ?? 'POS-DEFAULT')
            : (User::where('id', $this->seller_user_id)->value('id_users') ?? 'POS-DEFAULT');

        return url("/{$idUsers}/produk/{$this->magic_token}");
    }

    /**
     * Menghasilkan nomor invoice berurutan (N+1 Sequence) secara otomatis untuk
     * mencegah duplikasi / collision ketika terjadi klik checkout bersamaan.
     */
    public static function generateNextInvoiceNumber(): string
    {
        $datePrefix = 'INV-' . date('Ymd') . '-';

        // Hitung transaksi hari ini (N)
        $todayCount = static::whereDate('created_at', today())
            ->orWhere('invoice_number', 'like', $datePrefix . '%')
            ->count();

        // Format N+1 berurutan: INV-YYYYMMDD-0001-XXX, INV-YYYYMMDD-0002-XXX, dst.
        $nextSequence = str_pad((string) ($todayCount + 1), 4, '0', STR_PAD_LEFT);
        $randomSuffix = strtoupper(Str::random(3));

        return "{$datePrefix}{$nextSequence}-{$randomSuffix}";
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function storeWebsite(): BelongsTo
    {
        return $this->belongsTo(StoreWebsite::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class);
    }
}
