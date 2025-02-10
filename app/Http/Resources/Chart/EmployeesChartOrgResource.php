<?php

namespace App\Http\Resources\Chart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeesChartOrgResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "org_id" => $this->id, 
            "id" => $this->user?->id,
            "department_name" => $this->department?->getTranslations("name"),
            "department_id" => $this->department?->id,

            "first_name" => $this->user?->first_name,
            "last_name" => $this->user?->last_name,
            "image" => $this->user?->image,
            "position" => $this->getTranslations("position"),

            'employees' => $this->whenLoaded('employees', fn() => EmployeesChartOrgResource::collection($this->employees->each->load('employees'))),
        ];
    }
}
