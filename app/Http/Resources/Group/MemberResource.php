<?php

namespace App\Http\Resources\Group;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstName = $this->resource['user']['first_name'] ?? '';
        $lastName = $this->resource['user']['last_name'] ?? '';
        $fullName = trim("{$firstName}.{$lastName}", '.');
        return [
            'id'=>$this->resource['member_id'],
            'name'=>$fullName,
        ];

    }
}
