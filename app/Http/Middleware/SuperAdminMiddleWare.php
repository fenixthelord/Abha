<?php

namespace App\Http\Middleware;

use App\Http\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleWare
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     *
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {

            if (auth('sanctum')->user()->hasRole('Master') ) {
                return $next($request);
            } else return $this->Forbidden('Access denied');
        } else {
            return $this->Unauthorized('unauthorized');
        }
    }
}
