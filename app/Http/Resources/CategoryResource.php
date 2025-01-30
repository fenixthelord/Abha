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
        // dd($this->children);

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            "parent_name" => $this->parent?->name,
            "parent_uuid" => $this->parent?->uuid,
            "department" => $this->department?->name,
            "department_uuid" => $this->department?->uuid,
            'chields' => $this->whenLoaded('children', function () {
                return CategoryResource::collection($this->children->load('children'));
            }),
        ];
    }
}
