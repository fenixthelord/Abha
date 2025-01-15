<?php

namespace App\Http\Resources\Roles;

use App\Http\Resources\Permissions\PermissionsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RolesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->permissions->groupBy('group')->map(function ($permissions, $group) {
                return [
                    'group' => $group,
                    'permissions' => $permissions->map(function ($permission) {
                        return [

                            'name' => $permission->name,
                            'displaying'=>$permission->displaying,
                        ];
                    }),
                ];
            })->values(), // Reset numeric keys for a clean array
        ];
    }
}
