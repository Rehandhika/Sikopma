<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\PathInfo;
use App\Services\Storage\Exceptions\FileValidationException;
use Illuminate\Support\Str;

/**
 * StorageOrganizer - Mengatur struktur direktori dan path file.
 *
 * Bertanggung jawab untuk:
 * - Generate path dengan format konsisten: {type}/{year}/{month}/{uuid}.{ext}
 * - Generate path untuk variants dan thumbnails
 * - Parse path untuk extract metadata
 * - Sanitize filename untuk keamanan
 */
class StorageOrganizer implements StorageOrganizerInterface
{
    /**
     * Valid file types dari konfigurasi.
     */
    protected array $validTypes;

    public function __construct()
    {
        $this->validTypes = array_keys(config('filestorage.types', []));
    }

    /**
     * {@inheritdoc}
     */
    public function generatePath(string $type, string $extension, array $options = []): string
    {
        $this->validateType($type);

        $extension = $this->sanitizeExtension($extension);
        $uuid = Str::uuid()->toString();
        $year = date('Y');
        $month = date('m');

        $parts = [$type, $year, $month];

        // Untuk attendance, tambahkan day
        if ($type === 'attendance' && isset($options['day'])) {
            $parts[] = str_pad((string) $options['day'], 2, '0', STR_PAD_LEFT);
        }

        $parts[] = "{$uuid}.{$extension}";

        return implode('/', $parts);
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantPath(string $originalPath, string $size): string
    {
        $pathInfo = $this->parsePath($originalPath);

        $parts = [$pathInfo->type, $pathInfo->year, $pathInfo->month];

        if ($pathInfo->day !== null) {
            $parts[] = $pathInfo->day;
        }

        $parts[] = $size;
        $parts[] = "{$pathInfo->filename}.{$pathInfo->extension}";

        return implode('/', $parts);
    }

    /**
     * {@inheritdoc}
     */
    public function getThumbnailPath(string $originalPath, int $width, int $height): string
    {
        $pathInfo = $this->parsePath($originalPath);

        $parts = [$pathInfo->type, $pathInfo->year, $pathInfo->month];

        if ($pathInfo->day !== null) {
            $parts[] = $pathInfo->day;
        }

        $parts[] = 'thumbnails';
        $parts[] = "{$pathInfo->filename}_{$width}x{$height}.{$pathInfo->extension}";

        return implode('/', $parts);
    }

    /**
     * {@inheritdoc}
     */
    public function parsePath(string $path): PathInfo
    {
        // Sanitize path terlebih dahulu
        $path = $this->sanitizePath($path);

        // Split path menjadi parts
        $parts = explode('/', $path);

        if (count($parts) < 4) {
            throw new FileValidationException(
                __('filestorage.validation.invalid_path', ['path' => $path])
            );
        }

        $type = $parts[0];
        $year = $parts[1];
        $month = $parts[2];

        // Determine if there's a day component (for attendance)
        // and/or variant component
        $day = null;
        $variant = null;
        $filenameWithExt = null;

        // Pattern: type/year/month/filename.ext (4 parts)
        // Pattern: type/year/month/day/filename.ext (5 parts, attendance)
        // Pattern: type/year/month/variant/filename.ext (5 parts, with variant)
        // Pattern: type/year/month/day/variant/filename.ext (6 parts, attendance with variant)
        // Pattern: type/year/month/thumbnails/filename_WxH.ext (5 parts, thumbnail)
        // Pattern: type/year/month/day/thumbnails/filename_WxH.ext (6 parts, attendance thumbnail)

        $remainingParts = array_slice($parts, 3);

        if (count($remainingParts) === 1) {
            // Simple case: type/year/month/filename.ext
            $filenameWithExt = $remainingParts[0];
        } elseif (count($remainingParts) === 2) {
            // Could be day/filename or variant/filename
            if ($this->isDay($remainingParts[0])) {
                $day = $remainingParts[0];
                $filenameWithExt = $remainingParts[1];
            } else {
                $variant = $remainingParts[0];
                $filenameWithExt = $remainingParts[1];
            }
        } elseif (count($remainingParts) === 3) {
            // day/variant/filename
            $day = $remainingParts[0];
            $variant = $remainingParts[1];
            $filenameWithExt = $remainingParts[2];
        } else {
            throw new FileValidationException(
                __('filestorage.validation.invalid_path', ['path' => $path])
            );
        }

        // Parse filename and extension
        $lastDotPos = strrpos($filenameWithExt, '.');
        if ($lastDotPos === false) {
            throw new FileValidationException(
                __('filestorage.validation.invalid_path', ['path' => $path])
            );
        }

        $filename = substr($filenameWithExt, 0, $lastDotPos);
        $extension = substr($filenameWithExt, $lastDotPos + 1);

        return new PathInfo(
            type: $type,
            year: $year,
            month: $month,
            filename: $filename,
            extension: $extension,
            variant: $variant,
            day: $day
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove path traversal characters
        $filename = str_replace(['../', '..\\', '../', '..\\'], '', $filename);

        // Remove null bytes
        $filename = str_replace("\0", '', $filename);

        // Remove other dangerous characters
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '', $filename);

        // Remove leading/trailing dots and spaces
        $filename = trim($filename, '. ');

        // If filename is empty after sanitization, generate a random one
        if (empty($filename)) {
            $filename = Str::uuid()->toString();
        }

        return $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidType(string $type): bool
    {
        return in_array($type, $this->validTypes, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidTypes(): array
    {
        return $this->validTypes;
    }

    /**
     * Validate type dan throw exception jika tidak valid.
     *
     * @throws FileValidationException
     */
    protected function validateType(string $type): void
    {
        if (! $this->isValidType($type)) {
            throw new FileValidationException(
                __('filestorage.validation.invalid_file_type', [
                    'type' => $type,
                    'valid_types' => implode(', ', $this->validTypes),
                ])
            );
        }
    }

    /**
     * Sanitize extension.
     */
    protected function sanitizeExtension(string $extension): string
    {
        // Remove leading dot if present
        $extension = ltrim($extension, '.');

        // Only allow alphanumeric characters
        $extension = preg_replace('/[^a-zA-Z0-9]/', '', $extension);

        // Convert to lowercase
        return strtolower($extension);
    }

    /**
     * Sanitize path untuk mencegah path traversal.
     */
    protected function sanitizePath(string $path): string
    {
        // Remove path traversal sequences
        $path = str_replace(['../', '..\\', '..'], '', $path);

        // Remove null bytes
        $path = str_replace("\0", '', $path);

        // Normalize slashes
        $path = str_replace('\\', '/', $path);

        // Remove leading slash
        $path = ltrim($path, '/');

        // Remove double slashes
        $path = preg_replace('#/+#', '/', $path);

        return $path;
    }

    /**
     * Check if a string looks like a day (01-31).
     */
    protected function isDay(string $value): bool
    {
        // Day is 2 digits between 01 and 31
        if (! preg_match('/^[0-3][0-9]$/', $value)) {
            return false;
        }

        $day = (int) $value;

        return $day >= 1 && $day <= 31;
    }
}
