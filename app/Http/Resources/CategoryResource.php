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
            'chields' => $this->when($this->showChildren, CategoryResource::collection($this->children)),
        ];
    }
    public function hideChildren()
    {
        $this->showChildren = false;
        return $this;
    }
}
