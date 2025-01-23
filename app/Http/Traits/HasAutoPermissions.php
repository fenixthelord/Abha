<?php

namespace App\Http\Traits;

use App\Models\Role\Permission;

trait HasAutoPermissions
{
    protected static function bootHasAutoPermissions()
    {
        static::created(function ($model) {
            $modelName = strtolower(class_basename($model));
            $actions = ['create', 'edit', 'show', 'delete', 'restore'];

            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$modelName}.{$action}",
                    'displaying' => "{$modelName}.{$action}",
                    'group' => "{$modelName}",
                    'guard_name' => 'sanctum',
                ]);
            }
        });
    }
}
