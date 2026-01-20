<?php

namespace App\Services\Storage\Exceptions;

/**
 * Exception untuk error pemrosesan file (resize, convert, etc).
 */
class FileProcessingException extends FileStorageException
{
    protected string $operation;

    public function __construct(
        string $message = '',
        string $operation = '',
        int $code = 0,
        ?\Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->operation = $operation;
    }

    /**
     * Get the operation that failed.
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Create exception for resize failure.
     */
    public static function resizeFailed(string $path, ?\Exception $previous = null): static
    {
        return new static(
            message: __('filestorage.processing.resize_failed'),
            operation: 'resize',
            previous: $previous,
            context: ['path' => $path]
        );
    }

    /**
     * Create exception for format conversion failure.
     */
    public static function conversionFailed(string $path, string $targetFormat, ?\Exception $previous = null): static
    {
        return new static(
            message: __('filestorage.processing.convert_failed'),
            operation: 'convert',
            previous: $previous,
            context: ['path' => $path, 'target_format' => $targetFormat]
        );
    }

    /**
     * Create exception for variant generation failure.
     */
    public static function variantFailed(string $path, string $variant, ?\Exception $previous = null): static
    {
        return new static(
            message: __('filestorage.processing.variant_failed'),
            operation: 'variant',
            previous: $previous,
            context: ['path' => $path, 'variant' => $variant]
        );
    }

    /**
     * Create exception for thumbnail generation failure.
     */
    public static function thumbnailFailed(string $path, ?\Exception $previous = null): static
    {
        return new static(
            message: __('filestorage.processing.thumbnail_failed'),
            operation: 'thumbnail',
            previous: $previous,
            context: ['path' => $path]
        );
    }

    /**
     * Get user-friendly error message in Indonesian.
     */
    public function getUserMessage(): string
    {
        return match ($this->operation) {
            'resize' => __('filestorage.processing.resize_failed'),
            'convert' => __('filestorage.processing.convert_failed'),
            'variant' => __('filestorage.processing.variant_failed'),
            'thumbnail' => __('filestorage.processing.thumbnail_failed'),
            default => $this->getMessage(),
        };
    }
}
