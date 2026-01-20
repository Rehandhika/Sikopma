<?php

namespace App\Services\Storage\Exceptions;

/**
 * Exception untuk file yang tidak ditemukan.
 */
class FileNotFoundException extends FileStorageException
{
    protected string $path;

    public function __construct(
        string $path,
        string $message = '',
        int $code = 0,
        ?\Exception $previous = null
    ) {
        $this->path = $path;
        
        if (empty($message)) {
            $message = __('filestorage.storage.file_not_found', ['path' => $path]);
        }
        
        parent::__construct($message, $code, $previous, ['path' => $path]);
    }

    /**
     * Get the path that was not found.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Create exception for missing file.
     */
    public static function forPath(string $path): static
    {
        return new static($path);
    }

    /**
     * Create exception for missing variant.
     */
    public static function forVariant(string $path, string $variant): static
    {
        return new static(
            path: $path,
            message: __('filestorage.storage.variant_not_found', ['path' => $path, 'variant' => $variant])
        );
    }

    /**
     * Get user-friendly error message in Indonesian.
     */
    public function getUserMessage(): string
    {
        return __('filestorage.storage.file_not_found', ['path' => basename($this->path)]);
    }
}
