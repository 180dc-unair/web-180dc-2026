<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'author_id',
    'category_id',
    'thumbnail_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'status',
    'view_count',
    'published_at',
])]
class Article extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => trim($value),
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'thumbnail_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ArticleComment::class, 'article_id');
    }
}
