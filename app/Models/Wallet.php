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
        $balanceBefore = (float) $this->balance;
        $this->balance += $amount;

        $val = $this->value ?? [];
        $mutations = $val['mutations'] ?? [];
        $now = now()->toIso8601String();
        $mutations[] = [
            'id' => (string) Str::uuid(),
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => (float) $this->balance,
            'description' => $description,
            'reference_id' => $referenceId,
            'timestamp' => $now,
            'created_at' => $now,
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

        $balanceBefore = (float) $this->balance;
        $this->balance -= $amount;

        $val = $this->value ?? [];
        $mutations = $val['mutations'] ?? [];
        $now = now()->toIso8601String();
        $mutations[] = [
            'id' => (string) Str::uuid(),
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => (float) $this->balance,
            'description' => $description,
            'reference_id' => $referenceId,
            'timestamp' => $now,
            'created_at' => $now,
        ];
        $val['mutations'] = $mutations;
        $this->value = $val;
        $this->save();

        return true;
    }
}
