<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk hasil pemrosesan gambar.
 */
class ProcessedImage
{
    public function __construct(
        public readonly string $path,
        public readonly int $width,
        public readonly int $height,
        public readonly int $size,
        public readonly string $mimeType,
        public readonly string $format,
        public readonly bool $wasConverted,
        public readonly bool $wasResized,
    ) {}

    /**
     * Get aspect ratio.
     */
    public function getAspectRatio(): float
    {
        if ($this->height === 0) {
            return 0;
        }
        
        return round($this->width / $this->height, 4);
    }

    /**
     * Get dimensions as string.
     */
    public function getDimensionsString(): string
    {
        return "{$this->width}x{$this->height}";
    }

    /**
     * Get size in KB.
     */
    public function getSizeKB(): float
    {
        return round($this->size / 1024, 2);
    }

    /**
     * Get size in MB.
     */
    public function getSizeMB(): float
    {
        return round($this->size / (1024 * 1024), 2);
    }

    /**
     * Check if image is landscape.
     */
    public function isLandscape(): bool
    {
        return $this->width > $this->height;
    }

    /**
     * Check if image is portrait.
     */
    public function isPortrait(): bool
    {
        return $this->height > $this->width;
    }

    /**
     * Check if image is square.
     */
    public function isSquare(): bool
    {
        return $this->width === $this->height;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'format' => $this->format,
            'was_converted' => $this->wasConverted,
            'was_resized' => $this->wasResized,
            'aspect_ratio' => $this->getAspectRatio(),
        ];
    }
}
