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
        'id_toko',
        'nama_toko',
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
     * Dapatkan kode template yang digunakan pengguna dari kolom JSON value.
     */
    public function getTemplateAttribute(): string
    {
        $val = $this->value;
        if (is_array($val)) {
            $template = $val['template'] ?? $val['template_code'] ?? null;
            if (!empty($template) && is_string($template)) {
                return trim($template);
            }
        }
        return 'default';
    }

    /**
     * Set dan simpan kode template pengguna ke dalam kolom JSON value.
     */
    public function setTemplate(string $code): self
    {
        $code = trim($code);
        $val = is_array($this->value) ? $this->value : [];
        $val['template'] = $code;
        $val['template_code'] = $code;
        $this->value = $val;
        $this->save();

        return $this;
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
