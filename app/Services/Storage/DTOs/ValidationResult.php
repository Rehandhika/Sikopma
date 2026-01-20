<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk hasil validasi file.
 */
class ValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors = [],
        public readonly ?string $detectedMime = null,
        public readonly ?int $fileSize = null,
    ) {}

    /**
     * Create a valid result.
     */
    public static function valid(?string $detectedMime = null, ?int $fileSize = null): self
    {
        return new self(
            valid: true,
            errors: [],
            detectedMime: $detectedMime,
            fileSize: $fileSize,
        );
    }

    /**
     * Create an invalid result with errors.
     */
    public static function invalid(array $errors): self
    {
        return new self(
            valid: false,
            errors: $errors,
        );
    }

    /**
     * Check if validation passed.
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Check if validation failed.
     */
    public function isFailed(): bool
    {
        return !$this->valid;
    }

    /**
     * Get first error message.
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Get all error messages as string.
     */
    public function getErrorsString(): string
    {
        return implode(', ', $this->errors);
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'errors' => $this->errors,
            'detected_mime' => $this->detectedMime,
            'file_size' => $this->fileSize,
        ];
    }
}
