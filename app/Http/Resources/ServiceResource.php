<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request) {
        return [
            'uuid' => $this->id,
            'name' => $this->getTranslations('name'),
            'details' => $this->getTranslations('details'),
            'image' => $this->image,
            'department' => [
                'uuid' => $this->department->uuid ?? null,
                'name' => $this->department->name ?? null,
            ],
        ];
    }
}
