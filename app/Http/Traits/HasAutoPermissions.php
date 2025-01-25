<?php

namespace App\Http\Traits;

use App\Models\Role\Permission;
use App\Models\Role\Role;

trait HasAutoPermissions
{
    protected static function bootHasAutoPermissions()
    {
        static::created(function ($model) {
            $modelName = strtolower(class_basename($model));
            $actions = ['create', 'edit', 'show', 'delete', 'restore'];
            $ownerRole = Role::where('name', 'Master_Owner')->first();

            foreach ($actions as $action) {
              $permission =  Permission::firstOrCreate([
                    'name' => "{$modelName}.{$action}",
                    'displaying' => "{$modelName}.{$action}",
                    'group' => "{$modelName}",
                    'guard_name' => 'sanctum',
                ]);

            }
            /*if ($ownerRole) {
                $ownerRole->givePermissionTo($permission);
            }*/
        });

    }
}
