<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk informasi path file yang di-parse.
 */
class PathInfo
{
    public function __construct(
        public readonly string $type,
        public readonly string $year,
        public readonly string $month,
        public readonly string $filename,
        public readonly string $extension,
        public readonly ?string $variant = null,
        public readonly ?string $day = null,
    ) {}

    /**
     * Get full path.
     */
    public function getFullPath(): string
    {
        $parts = [$this->type, $this->year, $this->month];

        if ($this->day !== null) {
            $parts[] = $this->day;
        }

        if ($this->variant !== null) {
            $parts[] = $this->variant;
        }

        $parts[] = $this->filename.'.'.$this->extension;

        return implode('/', $parts);
    }

    /**
     * Get directory path (without filename).
     */
    public function getDirectory(): string
    {
        $parts = [$this->type, $this->year, $this->month];

        if ($this->day !== null) {
            $parts[] = $this->day;
        }

        if ($this->variant !== null) {
            $parts[] = $this->variant;
        }

        return implode('/', $parts);
    }

    /**
     * Get base directory (type/year/month).
     */
    public function getBaseDirectory(): string
    {
        $parts = [$this->type, $this->year, $this->month];

        if ($this->day !== null) {
            $parts[] = $this->day;
        }

        return implode('/', $parts);
    }

    /**
     * Check if this is a variant path.
     */
    public function isVariant(): bool
    {
        return $this->variant !== null;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'filename' => $this->filename,
            'extension' => $this->extension,
            'variant' => $this->variant,
        ];
    }
}
