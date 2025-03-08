<?php

namespace App\Http\Resources\Position;

use App\Http\Resources\UserResource;
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
        $baseArray = [
            "id" => $this->id,
            "parent_id" => $this->parent_id,
            'parent_name' => [
                'en' => $this->parent?->getTranslation('name', 'en'),
                'ar' => $this->parent?->getTranslation('name', 'ar'),
            ],
            "name" => $this->getTranslations("name"),
            "employees" => $this->whenLoaded("users", fn() => UserResource::collection($this->users)->map->onlyName()),
            "children" => $this->whenLoaded('children', function () {
                $with = ['children'];
                if ($this->relationLoaded('users')) {
                    $with[] = 'users';
                }
                return PositionResource::collection($this->children->loadMissing($with));
            }),
        ];

        return $baseArray;
    }
}
