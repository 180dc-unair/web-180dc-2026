<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'linkedin_url' => $this->linkedin_url,
            'instagram_url' => $this->instagram_url,
            'bio' => $this->bio,
            'is_active' => (bool) $this->is_active,
            'image' => $this->whenLoaded('image', fn () => $this->image ? [
                'id' => $this->image->id,
                'url' => $this->image->url,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
