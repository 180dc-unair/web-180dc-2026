<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id',
    'payment_method_id',
    'gateway',
    'idempotency_key',
    'status',
    'amount',
    'payment_url',
    'gateway_reference',
    'raw_response',
    'proof_media_id',
    'paid_at',
    'expired_at',
])]
class Payment extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'raw_response' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function proof(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'proof_media_id');
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class);
    }
}
