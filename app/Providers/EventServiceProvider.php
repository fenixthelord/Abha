<?php

namespace App\Providers;

use App\Events\UserLogin;
use App\Models\Role\Permission;
use App\Events\UserRegistered;
use App\Listeners\SendLoginOtpEmail;
use App\Listeners\SendOtpEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserRegistered::class => [
            SendOtpEmail::class,
        ],
        UserLogin::class => [
            SendLoginOtpEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {

    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
