<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk konfigurasi tipe file.
 */
class FileTypeConfig
{
    public function __construct(
        public readonly string $type,
        public readonly string $disk,
        public readonly string $basePath,
        public readonly int $maxSize,
        public readonly array $allowedMimes,
        public readonly array $variants,
        public readonly ?array $thumbnail,
        public readonly bool $convertToWebp,
    ) {}

    /**
     * Create from config array.
     */
    public static function fromArray(string $type, array $config): self
    {
        return new self(
            type: $type,
            disk: $config['disk'] ?? 'public',
            basePath: $config['base_path'] ?? $type,
            maxSize: $config['max_size'] ?? 5 * 1024 * 1024,
            allowedMimes: $config['allowed_mimes'] ?? [],
            variants: $config['variants'] ?? [],
            thumbnail: $config['thumbnail'] ?? null,
            convertToWebp: $config['convert_to_webp'] ?? false,
        );
    }

    /**
     * Check if MIME type is allowed.
     */
    public function isAllowedMime(string $mime): bool
    {
        return in_array($mime, $this->allowedMimes, true);
    }

    /**
     * Check if file size is within limit.
     */
    public function isWithinSizeLimit(int $size): bool
    {
        return $size <= $this->maxSize;
    }

    /**
     * Get max size in MB for display.
     */
    public function getMaxSizeMB(): float
    {
        return round($this->maxSize / (1024 * 1024), 2);
    }

    /**
     * Get allowed MIME types as string for display.
     */
    public function getAllowedMimesString(): string
    {
        return implode(', ', $this->allowedMimes);
    }

    /**
     * Check if this type is stored on private disk.
     */
    public function isPrivate(): bool
    {
        return $this->disk === 'local';
    }

    /**
     * Check if this type has variants.
     */
    public function hasVariants(): bool
    {
        return ! empty($this->variants);
    }

    /**
     * Check if this type has thumbnail config.
     */
    public function hasThumbnail(): bool
    {
        return $this->thumbnail !== null;
    }
}
