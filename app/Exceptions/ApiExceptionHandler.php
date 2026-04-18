<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionHandler
{
    use ApiResponse;

    /**
     * Handle API exceptions.
     */
    public static function handle(Throwable $e, Request $request)
    {
        if (!$request->is('api/*')) {
            return null;
        }

        $isDebug = config('app.debug');

        // 1. Specialized Exceptions
        if ($e instanceof ValidationException) {
            return self::errorResponse($e->getMessage(), 422, $e->errors());
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return self::errorResponse('Unauthenticated.', 401);
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return self::errorResponse($e->getMessage() ?: 'This action is unauthorized.', 403);
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $model = str_replace('App\\Models\\', '', $e->getModel());
            return self::errorResponse("{$model} not found.", 404);
        }

        // 2. Status & Message Logic
        $status = 500;
        if ($e instanceof ApiException || $e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: 'Resource not found.';
        } else {
            $message = $isDebug ? $e->getMessage() : 'An unexpected error occurred.';
        }

        // 3. Debug Details
        $errors = null;
        if ($isDebug) {
            $errors = [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTrace(),
            ];
        }

        return self::errorResponse($message, $status, $errors);
    }
}
