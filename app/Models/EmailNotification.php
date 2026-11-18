<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'transaction_id',
        'recipient_email',
        'subject',
        'notif_type',
        'status',
        'value',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EmailNotification $email) {
            if (empty($email->uuid)) {
                $email->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
