<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ApiResponser;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof HttpException) {
            $code = $e->getStatusCode();
            $message = Response::$statusTexts[$code] ?? 'Unknown error';

            return $this->errorResponse($message, $code);
        }

        if ($e instanceof ModelNotFoundException) {
            $model = strtolower(class_basename($e->getModel()));

            return $this->errorResponse("Does not exist any instance of {$model} with the given id", Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof AuthorizationException) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        }

        if ($e instanceof AuthenticationException) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof ValidationException) {
            $errors = $e->validator->errors()->getMessages();

            return $this->errorResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($e instanceof \GuzzleHttp\Exception\ClientException) {
            $response = $e->getResponse();
            $statusCode = $response ? $e->getResponse()->getStatusCode() : Response::HTTP_BAD_GATEWAY;
            $message = $response ? json_decode($response->getBody()->getContents(), true)['error'] ?? 'External service error' : 'External service unavailable';

            return $this->errorResponse($message, $statusCode);
        }

        if ($e instanceof \Illuminate\Database\QueryException) {
            // Handle unique constraint violations
            if ($e->getCode() == 23000) {
                return $this->errorResponse('Duplicate entry: Rating already exists for this book and user', Response::HTTP_CONFLICT);
            }

            return $this->errorResponse('Database error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (env('APP_DEBUG', false)) {
            return parent::render($request, $e);
        }

        return $this->errorResponse('Unexpected error. Try later', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
