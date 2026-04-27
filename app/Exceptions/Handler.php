<?php

namespace App\Exceptions;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types that are not reported.
     *
     * @var array<class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
            //
        });

        $this->renderable(function (DecryptException $e, Request $request) {
            return $this->handleInvalidPayload($request, $e);
        });
    }

    protected function handleInvalidPayload(Request $request, DecryptException $exception): Response
    {
        $cookieNames = array_keys($request->cookies->all());

        Log::warning('DecryptException caught: invalid payload, clearing cookies.', [
            'path' => $request->path(),
            'cookies' => $cookieNames,
        ]);

        $response = redirect('/');

        foreach ($cookieNames as $cookieName) {
            $response = $response->withCookie(Cookie::forget($cookieName));
        }

        return $response;
    }
}
