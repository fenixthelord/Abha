<?php

namespace App\Providers;

use App\Events\UserLogin;
use App\Events\UserRegistered;
use App\Events\Workflows\WorkflowTriggered;
use App\Listeners\SendLoginOtpEmail;
use App\Listeners\SendOtpEmail;
use App\Listeners\Workflows\HandleSystemErrorListener;
use App\Listeners\Workflows\ProcessWorkflowListener;
use App\Models\Category;
use App\Models\Event;
use App\Observers\CategoryObserver;
use App\Observers\EventObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Log\Events\MessageLogged;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegistered::class => [
            SendOtpEmail::class,
        ],
        UserLogin::class => [
            SendLoginOtpEmail::class,
        ],
        WorkflowTriggered::class => [
            ProcessWorkflowListener::class,
        ],
        MessageLogged::class => [
            HandleSystemErrorListener::class,
        ],
    ];

    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
        Event::observe(EventObserver::class);
        // $models = collect(File::allFiles(app_path('Models')))
        //     ->map(function ($file) {
        //         return 'App\\Models\\' . str_replace('.php', '', $file->getFilename());
        //     });

        // foreach ($models as $model) {
        //     $observer = 'App\\Observers\\' . class_basename($model) . 'Observer';
        //     if (class_exists($observer)) {
        //         $model::observe($observer);
        //     }
        // }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
