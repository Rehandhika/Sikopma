<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk hasil operasi upload file.
 */
class FileResult
{
    public function __construct(
        public readonly string $path,
        public readonly string $url,
        public readonly array $variants,
        public readonly int $size,
        public readonly string $mimeType,
        public readonly string $checksum,
    ) {}

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'url' => $this->url,
            'variants' => $this->variants,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'checksum' => $this->checksum,
        ];
    }

    /**
     * Get variant URL by size name.
     */
    public function getVariantUrl(string $size): ?string
    {
        return $this->variants[$size]['url'] ?? null;
    }

    /**
     * Get variant path by size name.
     */
    public function getVariantPath(string $size): ?string
    {
        return $this->variants[$size]['path'] ?? null;
    }
}
