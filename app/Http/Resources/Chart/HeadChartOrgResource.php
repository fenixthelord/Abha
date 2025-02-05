<?php

namespace App\Http\Resources\Chart;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeadChartOrgResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**  
         *  In this resource tou MUST pass : 
         * @param User object
         * 
         */
        return [
            "department_name" => $this->department?->getTranslations("name"),
            "department_uuid" => $this->department?->uuid,

            "uuid" => $this->uuid,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "image" => $this->image,
            // "position" => $this->organization?->position?->getTranslations("position"),
            "position" => [
                'en' => "head manger",
                'ar' => "الرئيس"
            ],

            'employees' => $this->whenLoaded('employees', function () {
                return EmployeesChartOrgResource::collection(
                    $this
                        ->employees
                        ->each
                        ->load('employee')
                );
            }),
        ];
    }
}
