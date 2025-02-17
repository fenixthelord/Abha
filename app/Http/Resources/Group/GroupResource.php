<?php

namespace App\Http\Resources\Group;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
           'id' => $this->resource['id'], // Change $this->id to $this->resource['id']
           'name' => $this->resource['name'], // Adjust to $this->resource['name']
           'description' => $this->resource['description'],
           'group_model' => $this->resource['group_type'],
           'owner_id' => $this->resource['owner_id'],
           'department_id' => $this->resource['department_id'] ?? null,
           'department_name' => Department::find($this->resource['department_id'])->getTranslations("name")??null,
           'group_service' => $this->resource['group_service'],
           'users' =>MemberResource::collection($this->resource['members']),


       ];
    }
}
