<?php

namespace App\Http\Traits;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait HasPermissionTrait
{
    use ResponseTrait;
    private function authorizePermission(array|string $permissions)
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        if (!auth()->user()->hasAllPermissions($permissions)) {
            throw new AccessDeniedHttpException('You do not have permission to access this page.');
        }
    }
}
