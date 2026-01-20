<?php

namespace App\Services\Storage\Exceptions;

use Exception;

/**
 * Base exception untuk semua error file storage.
 */
class FileStorageException extends Exception
{
    protected array $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context for the exception.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create exception with context.
     */
    public static function withContext(string $message, array $context = []): static
    {
        return new static($message, 0, null, $context);
    }

    /**
     * Get user-friendly error message in Indonesian.
     */
    public function getUserMessage(): string
    {
        return __('filestorage.storage.general_error', ['message' => $this->getMessage()]);
    }
}
