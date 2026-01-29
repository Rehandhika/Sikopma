<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
            // Log all exceptions with context
            $this->logException($e);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            return $this->handleException($e, $request);
        });
    }

    /**
     * Log exception with detailed context
     */
    protected function logException(Throwable $e): void
    {
        $context = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->user()?->id,
            'user_email' => auth()->user()?->email,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Log based on exception type
        if ($e instanceof ValidationException) {
            Log::warning('Validation Exception', array_merge($context, [
                'errors' => $e->errors(),
                'failed_rules' => $e->failed(),
            ]));
        } elseif ($e instanceof AuthenticationException) {
            Log::info('Authentication Exception', $context);
        } elseif ($e instanceof AuthorizationException) {
            Log::warning('Authorization Exception', $context);
        } elseif ($e instanceof ModelNotFoundException) {
            Log::warning('Model Not Found Exception', $context);
        } else {
            Log::error('Application Exception', $context);
        }
    }

    /**
     * Handle exception and return appropriate response
     */
    protected function handleException(Throwable $e, Request $request): Response|JsonResponse|null
    {
        // Handle API requests
        if ($request->expectsJson()) {
            return $this->handleApiException($e, $request);
        }

        // Handle specific exceptions for web requests
        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e, $request);
        }

        if ($e instanceof AuthenticationException) {
            return $this->handleAuthenticationException($e, $request);
        }

        if ($e instanceof AuthorizationException) {
            return $this->handleAuthorizationException($e, $request);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($e, $request);
        }

        return null; // Let Laravel handle other exceptions
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException(Throwable $e, Request $request): JsonResponse
    {
        $status = 500;
        $message = 'Internal Server Error';

        if ($e instanceof ValidationException) {
            $status = 422;
            $message = 'Validation failed';

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $e->errors(),
                'timestamp' => now()->toISOString(),
            ], $status);
        }

        if ($e instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated';
        } elseif ($e instanceof AuthorizationException) {
            $status = 403;
            $message = 'Unauthorized';
        } elseif ($e instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong',
            'timestamp' => now()->toISOString(),
        ], $status);
    }

    /**
     * Handle validation exceptions for web requests
     */
    protected function handleValidationException(ValidationException $e, Request $request): Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Flash old input and errors to session
        $request->flash();

        // Redirect back with errors
        return redirect()
            ->back()
            ->withErrors($e->errors())
            ->withInput()
            ->with('error', 'Please check your input and try again.');
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException(AuthenticationException $e, Request $request): Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return redirect()->guest(route('login'))
            ->with('error', 'Please login to continue.');
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleAuthorizationException(AuthorizationException $e, Request $request): Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        abort(403, 'You are not authorized to perform this action.');
    }

    /**
     * Handle model not found exceptions
     */
    protected function handleModelNotFoundException(ModelNotFoundException $e, Request $request): Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        }

        abort(404, 'The requested resource was not found.');
    }
}
