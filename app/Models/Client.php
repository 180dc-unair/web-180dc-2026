<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['logo_id', 'name', 'slug', 'type', 'website_url', 'is_featured', 'sort_order'])]
class Client extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => trim($value),
        );
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function logo(): BelongsTo
    {
        return $this->belongsTo(
            MediaAsset::class, 'logo_id'
        );
    }
}
