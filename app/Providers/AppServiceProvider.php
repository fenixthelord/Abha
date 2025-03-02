<?php

namespace App\Providers;

use app\services\NotificationService;
use App\Services\UserNotificationService;
use Illuminate\Support\ServiceProvider;
use App\Models\Role\Role;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton(UserNotificationService::class, function ($app) {
            return new UserNotificationService($app->make(NotificationService::class));
        });
    }

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
