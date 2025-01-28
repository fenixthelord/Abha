<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            "parent_name" => $this->parent?->name ?? $this->department?->name,
            'chields' => $this->whenLoaded('children', function () {
                return CategoryResource::collection($this->children->load('children'));
            }),
        ];
    }
}
