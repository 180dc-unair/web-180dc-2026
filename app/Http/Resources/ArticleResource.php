<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'status' => $this->status,
            'view_count' => (int) $this->view_count,
            'published_at' => $this->published_at?->toISOString(),
            'author' => $this->when($this->relationLoaded('author'), fn () => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'username' => $this->author->username,
            ] : null),
            'category' => $this->when($this->relationLoaded('category'), fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'thumbnail' => $this->when($this->relationLoaded('thumbnail'), fn () => $this->thumbnail ? [
                'id' => $this->thumbnail->id,
                'url' => $this->thumbnail->url,
            ] : null),
            'comments' => $this->whenLoaded('comments', fn () => ArticleCommentResource::collection($this->comments)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
