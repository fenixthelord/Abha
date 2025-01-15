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
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'groups' => $this->permissions->groupBy('group')->map(function ($permissions, $group) {
                return [
                    'group' => $group,
                    'permissions' => $permissions->map(function ($permission) {
                        return [
                            'name' => $permission->name,
                            'displaying' => $permission->displaying, // Assuming you need this too
                        ];
                    }),
                ];
            })->values(), // Reset numeric keys for a clean array
        ];
    }
}

