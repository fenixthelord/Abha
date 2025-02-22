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
        $user = $this->resource['user'] ?? null;
        $firstName = $user['data']['first_name'] ?? '';
        $lastName = $user['data']['last_name'] ?? '';

        $fullName = trim("{$firstName} {$lastName}");;
        return [
            'id'=>$this->resource['member_id'],
            'name'=>$fullName,
        ];

    }
}
