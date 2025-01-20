<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'image' => $this->image,
            'alt' => $this->alt,
            'job' => $this->job,
            'job_id' => $this->job_id,
            'active' => $this->active,
            'role' => $this->role,
            'user-role'=>$this->getRoleNames(),
            'user-Permission'=> $this->getAllPermissions()->pluck("name")


        ];
    }
}
