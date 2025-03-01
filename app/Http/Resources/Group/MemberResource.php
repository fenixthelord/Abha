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
        // Use either 'user' or 'customer' key from the resource
        $data = [];
        if (isset($this->resource['user'])) {
            $data = $this->resource['user']['data'] ?? [];
        } elseif (isset($this->resource['customer'])) {
            $data = $this->resource['customer']['data'] ?? [];
        }

        $firstName = '';
        $lastName  = '';

        // Check member type if available
        if (($this->resource['member_type'] ?? '') === 'customer') {
            // For customer, the data should be under the "customer" key.
            // Sometimes it might be an indexed array, sometimes an associative array.
            if (isset($data['customer'])) {
                // If it's indexed (i.e. multiple customers), take the first element.
                $customerData = isset($data['customer'][0]) ? $data['customer'][0] : $data['customer'];
                $firstName = $customerData['first_name'] ?? '';
                $lastName  = $customerData['last_name'] ?? '';
            }
        } else {
            // For a user, assume the fields are in the "user" key.
            if (isset($data['user'])) {
                $userData = isset($data['user'][0]) ? $data['user'][0] : $data['user'];
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
