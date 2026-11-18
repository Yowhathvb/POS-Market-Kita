<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_users',
        'nama',
        'username',
        'email',
        'password',
        'role',
        'key',
        'value',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'value' => 'array',
        ];
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function storeWebsites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreWebsite::class);
    }

    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function withdrawals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function sellerTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_user_id');
    }

    /**
     * Dapatkan wallet user atau buat otomatis jika belum ada.
     */
    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet()->firstOrCreate([], [
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'balance' => 0.00,
            'pending_balance' => 0.00,
            'currency' => 'IDR',
            'value' => [
                'bank_account' => null,
                'mutations' => [],
            ],
        ]);
    }
}
