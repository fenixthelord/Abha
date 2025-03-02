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
        $firstName = '';
        $lastName  = '';


        if (($this->resource['member_type'] ?? '') === 'customers') {
            // Handle customer case
            $customerData = $this->resource['user']['data']['customer'] ?? [];
            if (!empty($customerData)) {
                $firstName = $customerData['first_name'] ?? '';
                $lastName  = $customerData['last_name'] ?? '';
            }
        } elseif (($this->resource['member_type'] ?? '') === 'users') {

            $userData = $this->resource['user']['data']['user'] ?? $this->resource['user']['data'] ?? [];
            if (!empty($userData)) {
                $firstName = $userData['first_name'] ?? '';
                $lastName  = $userData['last_name'] ?? '';
            }
        }

        $fullName = trim("{$firstName} {$lastName}");

        return [
            'id'   => $this->resource['member_id'] ?? null,
            'name' => $fullName,
        ];
    }}
