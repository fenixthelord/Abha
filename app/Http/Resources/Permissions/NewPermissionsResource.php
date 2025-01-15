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
    public function toArray(Request $request): array
    {
        // Group permissions by 'group' field
        $groupedPermissions = $this->resource->groupBy('group');

        // Prepare the response structure
        $response = [
            'status' => true,
            'code' => 200,
            'msg' => '',
            'permission' => []
        ];

        // Iterate over the grouped permissions
        foreach ($groupedPermissions as $group => $permissions) {
            // Get the name and description of each permission in the group
            $response['permission'][] = [
                'group' => $group, // the group name
                'permissions' => $permissions->map(function ($permission) {
                    return [
                        'name' => $permission->name,           // permission name
                        'displaying' => $permission->displaying, // permission description
                    ];
                })
            ];
        }

        return $response;
    }
}
