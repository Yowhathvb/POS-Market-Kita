<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreWebsite extends Model
{
    use HasFactory;

    protected $table = 'store_websites';

    protected $fillable = [
        'uuid',
        'user_id',
        'template_id',
        'store_name',
        'slug',
        'custom_domain',
        'is_published',
        'value',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'value' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'store_website_id');
    }
}
