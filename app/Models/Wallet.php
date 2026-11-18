<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'balance',
        'pending_balance',
        'currency',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'pending_balance' => 'decimal:2',
            'value' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Wallet $wallet) {
            if (empty($wallet->uuid)) {
                $wallet->uuid = (string) Str::uuid();
            }
            if (empty($wallet->currency)) {
                $wallet->currency = 'IDR';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Tambahkan saldo (credit) ke wallet dan catat mutasi di kolom JSON value.
     */
    public function credit(float $amount, string $description, ?string $referenceId = null): void
    {
        $this->balance += $amount;

        $val = $this->value ?? [];
        $mutations = $val['mutations'] ?? [];
        $mutations[] = [
            'id' => (string) Str::uuid(),
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'reference_id' => $referenceId,
            'balance_after' => (float) $this->balance,
            'created_at' => now()->toIso8601String(),
        ];
        $val['mutations'] = $mutations;
        $this->value = $val;
        $this->save();
    }

    /**
     * Kurangi saldo (debit) dari wallet dan catat mutasi.
     */
    public function debit(float $amount, string $description, ?string $referenceId = null): bool
    {
        if ($this->balance < $amount) {
            return false;
        }

        $this->balance -= $amount;

        $val = $this->value ?? [];
        $mutations = $val['mutations'] ?? [];
        $mutations[] = [
            'id' => (string) Str::uuid(),
            'type' => 'debit',
            'amount' => $amount,
            'description' => $description,
            'reference_id' => $referenceId,
            'balance_after' => (float) $this->balance,
            'created_at' => now()->toIso8601String(),
        ];
        $val['mutations'] = $mutations;
        $this->value = $val;
        $this->save();

        return true;
    }
}
