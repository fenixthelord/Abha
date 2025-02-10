<?php

namespace App\Http\Resources\Org;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOrgResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->user?->id,
            
            "department_name" => $this->department?->getTranslations("name"),
            "department_id" => $this->department?->id,

            "first_name" => $this->user?->first_name,
            "last_name" => $this->user?->last_name,
            "image" => $this->user?->image,
            "position" => $this->getTranslations("position"),
            'employees' => $this->whenLoaded("employees" , fn() => ChartOrgResource::collection($this->employees->load("employees")))

        ];
    }
}



