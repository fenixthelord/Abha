<?php

namespace App\Http\Resources;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'department' => DepartmentResource::make($this->department),
            'category' => CategoryResource::make($this->category),
        ];
    }
}
