<?php

namespace App\Http\Resources\Position;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "parent_id" => $this->parent_id,
            "name" => $this->getTranslations("name"),
            "children"  => $this->whenLoaded("children", fn() => PositionResource::collection($this->children->loadMissing('children')))
        ];
    }
}
