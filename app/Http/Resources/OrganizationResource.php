<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            "department_name" => $this->department?->getTranslations("name"),
            "department_id" => $this->department?->id,
            "manger_id" => $this->manger?->id,
            "manger_first_name" => $this->manger?->first_name,
            "manger_last_name" => $this->manger?->last_name,
            "employee_id" => $this->user?->id,
            "employee_first_name" => $this->user?->first_name,
            "employee_last_name" => $this->user?->last_name,
            "position" => $this->getTranslations("position"),
        ];
    }
}
