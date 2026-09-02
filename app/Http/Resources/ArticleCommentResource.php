<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'is_approved' => (bool) $this->is_approved,
            'user' => $this->when($this->relationLoaded('user'), fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
            ] : null),
            'article' => $this->when($this->relationLoaded('article'), fn () => $this->article ? [
                'id' => $this->article->id,
                'title' => $this->article->title,
                'slug' => $this->article->slug,
            ] : null),
            'replies' => $this->whenLoaded('replies', fn () => self::collection($this->replies)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
