<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $errorCode;
    protected $statusCode;

    public function __construct(string $message, string $errorCode = 'BUSINESS_ERROR', int $statusCode = 400)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    /**
     * Get error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => $this->errorCode,
                'timestamp' => now()->toISOString(),
            ], $this->statusCode);
        }

        return back()
            ->with('error', $this->getMessage())
            ->withInput();
    }
}
