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
            "manager_id" => $this->manager?->id,
            "manager_first_name" => $this->manager?->first_name,
            "manager_last_name" => $this->manager?->last_name,
            "employee_id" => $this->user?->id,
            "employee_first_name" => $this->user?->first_name,
            "employee_last_name" => $this->user?->last_name,
            "position" => $this->getTranslations("position"),
        ];
    }
}
