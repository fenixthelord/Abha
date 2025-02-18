<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAcceptJsonHeader
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
        // Set the Accept header to application/json
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
