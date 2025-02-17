<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseTrait;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */



   public function register(): void
    {
        $this->reportable(function (Throwable $e) {

        });
    }
    public function render($request, Throwable $e)
    {

        if ($e instanceof UnauthorizedException || $e instanceof AuthorizationException) {
            return $this->Forbidden("you don't have permission to access this page");
        }


        // Handle MissingAbilityException (token exists but lacks permissions)
        if ($e instanceof MissingAbilityException) {


                    return response()->json([
                        'status' => false,
                        'code' =>403,
                        'message' => 'Unauthorized:you are Not Allowed to do this action',
                        'data' => null

            ], 403); // 403 Forbidden is more appropriate here
        }

        // Handle AuthenticationException (no valid token)
        if ($e instanceof AuthenticationException) {
            return response()->json([
                 'status' => false,
                'code' =>401,
                'message' => 'Unauthenticated: Please log in first',
                'data' => null
            ], 401); // 401 Unauthorized
        }

        return parent::render($request, $e);
    }

}
