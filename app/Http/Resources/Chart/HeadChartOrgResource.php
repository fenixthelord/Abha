<?php

namespace App\Http\Resources\Chart;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeadChartOrgResource extends JsonResource
{

    private const HEAD_MANAGER_POSITION = [
        'en' => "head manager",
        'ar' => "رئيس القسم"
    ];

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
            "id" => $this->id,

            "department_id" => $this->department?->id,
            "department_name" => $this->department?->getTranslations("name"),

            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "image" => $this->image,
            "position" => self::HEAD_MANAGER_POSITION,

            'employees' => $this->whenLoaded(
                'employees',
                function () {
                    return EmployeesChartOrgResource::collection($this->employees->each->load("employee"));
                }

                // fn() => EmployeesChartOrgResource::collection($this->employees->each->load('employee'))
                // fn() => EmployeesChartOrgResource::collection($this->employees->transform(fn($employee) => $employee->load('employee')))
            ),
        ];
    }

}
