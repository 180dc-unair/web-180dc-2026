<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


#[Fillable(['category_id', 'icon_id', 'title', 'slug', 'short_description', 'description', 'is_featured', 'is_active', 'sort_order'])]
class Service extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn  (string $value) => trim($value),
        );
    }

    /**
     * @return BelongsTo<ServiceCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ServiceCategory::class, 'category_id'
        );
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function icon(): BelongsTo
    {
        return $this->belongsTo(
            MediaAsset::class, 'icon_id',
        );
    }
}
