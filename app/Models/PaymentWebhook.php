<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payment_id',
    'gateway',
    'payload',
    'is_processed',
    'processing_error',
])]
class PaymentWebhook extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_processed' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
