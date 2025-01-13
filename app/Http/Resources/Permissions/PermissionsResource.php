<?php

namespace App\Http\Resources\Permissions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//    dd($this);
        return [
            'group' => $this->group,
            'id' => $this->id,
            'name' => $this->name,
            'displaying' => $this->displaying,
            'is_admin' => $this->is_admin
        ];

    }
}
