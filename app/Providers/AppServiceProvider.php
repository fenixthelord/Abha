<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Role\Role;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Role::created(function ($role) {
            Log::info('Role created:', ['role' => $role->toArray()]);
        });

        Role::updated(function ($role) {
            Log::info('Role updated:', ['role' => $role->toArray()]);
        });

        Role::deleted(function ($role) {
            Log::info('Role deleted:', ['role' => $role->toArray()]);
        });
    }
}
