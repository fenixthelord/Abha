<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ChangeLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->header('Accept-Language'), SupportedLanguages())) {
            $lang = $request->header('Accept-Language');
        }

        $lang = 'en';

        app()->setlocale($lang);
        return $next($request);
    }
}
