<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
   protected function unauthenticated($request, AuthenticationException $exception)
{
    // If request is for API or expects JSON, return JSON
    if ($request->is('api/*') || $request->expectsJson()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated.'
        ], 401);
    }

    // Otherwise, fallback to redirect (web)
    return redirect()->guest(route('login'));
}

}
