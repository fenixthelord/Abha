<?php

namespace App\Http\Resources\Permissions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewPermissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Group permissions by 'group' and transform into an array
        $groupedPermissions = collect($this->resource)
            ->groupBy('group')
            ->map(function ($permissions, $group) {
                return [
                    'group' => $group,
                    'permissions' => $permissions->map(function ($permission) {
                        return [
                            'name' => $permission->name,
                            'displaying' => $permission->displaying, // Adjust based on your database fields
                        ];
                    })->values()->toArray(), // Convert to array
                ];
            })->values()->toArray(); // Ensure the final structure is an array

        return $groupedPermissions;
    }
}
