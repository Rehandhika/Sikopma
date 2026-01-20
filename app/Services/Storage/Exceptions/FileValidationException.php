<?php

namespace App\Services\Storage\Exceptions;

/**
 * Exception untuk error validasi file.
 */
class FileValidationException extends FileStorageException
{
    protected array $validationErrors = [];

    public function __construct(
        string $message = '',
        array $validationErrors = [],
        int $code = 0,
        ?\Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->validationErrors = $validationErrors;
    }

    /**
     * Get validation errors.
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Create exception for file too large.
     */
    public static function fileTooLarge(int $maxSize, int $actualSize): static
    {
        $maxMB = round($maxSize / (1024 * 1024), 2);
        $actualMB = round($actualSize / (1024 * 1024), 2);
        
        return new static(
            message: __('filestorage.validation.file_too_large', ['max' => $maxMB]),
            validationErrors: ['size' => __('filestorage.validation.file_too_large', ['max' => $maxMB])],
            context: ['max_size' => $maxSize, 'actual_size' => $actualSize]
        );
    }

    /**
     * Create exception for invalid file type.
     */
    public static function invalidType(string $mime, array $allowedMimes): static
    {
        $allowed = implode(', ', $allowedMimes);
        
        return new static(
            message: __('filestorage.validation.invalid_type', ['types' => $allowed]),
            validationErrors: ['type' => __('filestorage.validation.invalid_type', ['types' => $allowed])],
            context: ['mime' => $mime, 'allowed_mimes' => $allowedMimes]
        );
    }

    /**
     * Create exception for invalid image.
     */
    public static function invalidImage(): static
    {
        return new static(
            message: __('filestorage.validation.invalid_image'),
            validationErrors: ['file' => __('filestorage.validation.invalid_image')]
        );
    }

    /**
     * Create exception for MIME mismatch.
     */
    public static function mimeMismatch(string $declared, string $actual): static
    {
        return new static(
            message: __('filestorage.validation.mime_mismatch'),
            validationErrors: ['mime' => __('filestorage.validation.mime_mismatch')],
            context: ['declared_mime' => $declared, 'actual_mime' => $actual]
        );
    }

    /**
     * Get user-friendly error message in Indonesian.
     */
    public function getUserMessage(): string
    {
        if (!empty($this->validationErrors)) {
            return implode(' ', $this->validationErrors);
        }
        
        return $this->getMessage();
    }
}
