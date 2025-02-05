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
            "department_name" => $this->department?->getTranslations("name"),
            "department_uuid" => $this->department?->uuid,

            "uuid" => $this->user?->uuid,
            "first_name" => $this->user?->first_name,
            "last_name" => $this->user?->last_name,
            "image" => $this->user?->image,
            "position" => $this->getTranslations("position"),

            'employees' => $this->whenLoaded('employee', function () {
                return EmployeesChartOrgResource::collection(
                    $this
                        ->employee
                        ->load('employee')
                );
            }),
        ];
    }
}
