<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            foreach (glob(base_path('routes/api/v1/*.php')) as $file) {
                Route::middleware('api')
                    ->prefix('api/v1')
                    ->group($file);
            }

            foreach (glob(base_path('routes/api/v2/*.php')) as $file) {
                Route::middleware('api')
                    ->prefix('api/v2')
                    ->group($file);
            }

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
