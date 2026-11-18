<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'user_id',
        'store_website_id',
        'name',
        'slug',
        'product_type',
        'price',
        'stock',
        'is_active',
        'value',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'value' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storeWebsite(): BelongsTo
    {
        return $this->belongsTo(StoreWebsite::class, 'store_website_id');
    }

    public function transactionItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Helper to get user key from value JSON or user relation
     */
    public function getUserKeyAttribute(): ?string
    {
        return $this->value['user_key'] ?? $this->user?->key;
    }

    /**
     * Helper to get picture from value JSON
     */
    public function getImageAttribute(): ?string
    {
        return $this->value['gambar'] ?? null;
    }

    /**
     * Helper to get description from value JSON
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->value['deskripsi'] ?? null;
    }

    /**
     * Helper to get file_url for digital product from value JSON
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->value['file_url'] ?? null;
    }

    /**
     * Scope to find product safely in PostgreSQL by ID (numeric), UUID (valid uuid), or Slug (string).
     * Prevents PostgreSQL SQLSTATE[22P02] invalid input syntax for type uuid.
     */
    public function scopeWhereIdentifier($query, string|int $identifier)
    {
        return $query->where(function ($q) use ($identifier) {
            $val = trim((string) $identifier);
            if (is_numeric($val)) {
                $q->where('id', (int) $val);
            } elseif (\Illuminate\Support\Str::isUuid($val)) {
                $q->where('uuid', $val);
            } else {
                $q->where('slug', $val);
            }
        });
    }
}
