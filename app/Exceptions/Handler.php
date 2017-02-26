<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Bugsnag\BugsnagLaravel\BugsnagExceptionHandler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler {
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     *
     * @return void
     */
    public function report(Exception $e) {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e) {
//
//        if ($e instanceof ModelNotFoundException) {
//            $request->session()->flash('status', "user not found ");
//
//            return redirect('/');
//        }


        return parent::render($request, $e);
    }
}
