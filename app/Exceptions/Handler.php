<?php

namespace App\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return 
     */
    public function render($request, Exception $exception)
    {
        // if ($request->expectsJson()) {
        //     // Handle JSON response for specific exceptions
        //     if ($exception instanceof ClientException) {
        //         $err = $exception->getResponse();

        //         return response()->json(json_decode($err->getBody()), $err->getStatusCode());
        //     }

        //     // Handle other exceptions or generic error response
        //     return response()->json([
        //         'error' => 'Something went wrong'
        //     ], 500);
        // }

        return parent::render($request, $exception);
    }
}