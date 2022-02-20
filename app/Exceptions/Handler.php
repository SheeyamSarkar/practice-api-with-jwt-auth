<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // dd($e);
        });
    }

    public function handleException($request, Exception $exception){
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'status' => false,
                'errors' => 'Data Not Found'
            ], Response::HTTP_NOT_FOUND);
        }
        if ($exception instanceof QueryException) {
            return response()->json([
                'status' => false,
                'errors' => 'Qurey Error'
            ], Response::HTTP_NOT_FOUND);
        }
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => false,
                'errors' => 'Invalid Token'
            ], Response::HTTP_NOT_FOUND);
        }
        if ($request->wantsJson()) {
            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => false,
                    'errors' => 'Data Not Found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'status' => false,
                    'errors' => 'Invalid Token'
                ], Response::HTTP_NOT_FOUND);
            }
            if ($exception instanceof QueryException) {
                return response()->json([
                    'status' => false,
                    'errors' => 'Qurey Error'
                ], Response::HTTP_NOT_FOUND);
            }
        }
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'status' => false,
                'error' => 'Data not found.'
            ]);
        }
    }

    // public function register() { 
    //     $this->renderable(function (NotFoundHttpException $e) { 
    //         return response()->json("your massage here"); 
    //     }); 
    // }
}
