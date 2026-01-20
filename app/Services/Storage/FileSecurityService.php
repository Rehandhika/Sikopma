<?php

namespace App\Services\Storage;

use App\Services\Storage\Exceptions\FileValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * FileSecurityService - Menangani keamanan file storage.
 * 
 * Bertanggung jawab untuk:
 * - Validasi MIME content (actual content vs declared MIME)
 * - Generate signed URLs untuk private files
 * - Access logging untuk audit
 * - Path traversal prevention
 */
class FileSecurityService implements FileSecurityServiceInterface
{
    /**
     * MIME type signatures (magic bytes).
     * Format: [offset => [bytes => mime_type]]
     */
    protected const MIME_SIGNATURES = [
        // JPEG: FF D8 FF
        'image/jpeg' => [
            'offset' => 0,
            'bytes' => [0xFF, 0xD8, 0xFF],
        ],
        // PNG: 89 50 4E 47 0D 0A 1A 0A
        'image/png' => [
            'offset' => 0,
            'bytes' => [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A],
        ],
        // GIF87a or GIF89a: 47 49 46 38 (37|39) 61
        'image/gif' => [
            'offset' => 0,
            'bytes' => [0x47, 0x49, 0x46, 0x38],
        ],
        // WebP: 52 49 46 46 ... 57 45 42 50
        'image/webp' => [
            'offset' => 0,
            'bytes' => [0x52, 0x49, 0x46, 0x46],
            'secondary_offset' => 8,
            'secondary_bytes' => [0x57, 0x45, 0x42, 0x50],
        ],
        // PDF: 25 50 44 46 (%PDF)
        'application/pdf' => [
            'offset' => 0,
            'bytes' => [0x25, 0x50, 0x44, 0x46],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function validateMimeContent(UploadedFile $file, string $declaredMime): bool
    {
        $filePath = $file->getRealPath();
        
        if (!$filePath || !file_exists($filePath)) {
            return false;
        }

        $actualMime = $this->detectMimeFromContent($filePath);
        
        if ($actualMime === null) {
            // Cannot detect, use PHP's finfo as fallback
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $actualMime = $finfo->file($filePath);
        }

        // Normalize MIME types for comparison
        $declaredMime = $this->normalizeMime($declaredMime);
        $actualMime = $this->normalizeMime($actualMime);

        $isValid = $declaredMime === $actualMime;

        if (!$isValid) {
            $this->logSecurityEvent('mime_mismatch', [
                'declared_mime' => $declaredMime,
                'actual_mime' => $actualMime,
                'filename' => $file->getClientOriginalName(),
            ]);
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function validateMimeContentFromPath(string $filePath, string $declaredMime): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $actualMime = $this->detectMimeFromContent($filePath);
        
        if ($actualMime === null) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $actualMime = $finfo->file($filePath);
        }

        $declaredMime = $this->normalizeMime($declaredMime);
        $actualMime = $this->normalizeMime($actualMime);

        return $declaredMime === $actualMime;
    }


    /**
     * {@inheritdoc}
     */
    public function generateSignedUrl(string $path, string $disk = 'local', ?int $expirationMinutes = null): string
    {
        $expirationMinutes = $expirationMinutes ?? config('filestorage.security.signed_url_expiration', 60);
        
        // Log access attempt
        $this->logFileAccess($path, 'signed_url_generated');

        // Generate signed URL using Laravel's URL::temporarySignedRoute
        return URL::temporarySignedRoute(
            'file.download',
            now()->addMinutes($expirationMinutes),
            [
                'path' => base64_encode($path),
                'disk' => $disk,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateSignedUrl(string $signature, string $path, string $disk): bool
    {
        // This is handled by Laravel's signed middleware
        // This method is for additional custom validation if needed
        
        $isValid = URL::hasValidSignature(request());
        
        if (!$isValid) {
            $this->logSecurityEvent('invalid_signed_url', [
                'path' => $path,
                'disk' => $disk,
                'ip' => request()->ip(),
            ]);
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function logFileAccess(string $path, string $action, array $context = []): void
    {
        $logData = array_merge([
            'path' => $path,
            'action' => $action,
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $context);

        Log::channel('file_access')->info("File access: {$action}", $logData);
    }

    /**
     * {@inheritdoc}
     */
    public function sanitizePath(string $path): string
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
        
        // Check for remaining traversal attempts
        if (str_contains($path, '..')) {
            $this->logSecurityEvent('path_traversal_attempt', [
                'original_path' => $path,
                'ip' => request()->ip(),
            ]);
            
            throw new FileValidationException(
                __('filestorage.security.path_traversal_detected')
            );
        }

        return $path;
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
        
        // Remove dangerous characters
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '', $filename);
        
        // Remove leading/trailing dots and spaces
        $filename = trim($filename, '. ');
        
        // If filename is empty after sanitization, generate a random one
        if (empty($filename)) {
            $filename = bin2hex(random_bytes(16));
        }

        return $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function isPrivateFile(string $path, string $type): bool
    {
        $config = config("filestorage.types.{$type}");
        
        if (!$config) {
            return false;
        }

        return ($config['disk'] ?? 'public') === 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresAuthentication(string $path, string $type): bool
    {
        return $this->isPrivateFile($path, $type);
    }


    /**
     * Detect MIME type from file content using magic bytes.
     */
    protected function detectMimeFromContent(string $filePath): ?string
    {
        $handle = @fopen($filePath, 'rb');
        if ($handle === false) {
            return null;
        }

        // Read first 16 bytes for signature detection
        $bytes = fread($handle, 16);
        fclose($handle);

        if ($bytes === false || strlen($bytes) < 4) {
            return null;
        }

        foreach (self::MIME_SIGNATURES as $mime => $signature) {
            if ($this->matchesSignature($bytes, $signature, $filePath)) {
                return $mime;
            }
        }

        return null;
    }

    /**
     * Check if bytes match a MIME signature.
     */
    protected function matchesSignature(string $bytes, array $signature, string $filePath): bool
    {
        $offset = $signature['offset'] ?? 0;
        $expectedBytes = $signature['bytes'];

        // Check primary signature
        for ($i = 0; $i < count($expectedBytes); $i++) {
            $pos = $offset + $i;
            if ($pos >= strlen($bytes)) {
                return false;
            }
            if (ord($bytes[$pos]) !== $expectedBytes[$i]) {
                return false;
            }
        }

        // Check secondary signature if exists (for WebP)
        if (isset($signature['secondary_offset']) && isset($signature['secondary_bytes'])) {
            $handle = @fopen($filePath, 'rb');
            if ($handle === false) {
                return false;
            }
            
            fseek($handle, $signature['secondary_offset']);
            $secondaryBytes = fread($handle, count($signature['secondary_bytes']));
            fclose($handle);

            if ($secondaryBytes === false) {
                return false;
            }

            for ($i = 0; $i < count($signature['secondary_bytes']); $i++) {
                if ($i >= strlen($secondaryBytes)) {
                    return false;
                }
                if (ord($secondaryBytes[$i]) !== $signature['secondary_bytes'][$i]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Normalize MIME type for comparison.
     */
    protected function normalizeMime(?string $mime): string
    {
        if ($mime === null) {
            return '';
        }

        $mime = strtolower(trim($mime));
        
        // Handle common variations
        $normalizations = [
            'image/jpg' => 'image/jpeg',
            'image/pjpeg' => 'image/jpeg',
        ];

        return $normalizations[$mime] ?? $mime;
    }

    /**
     * Log security event.
     */
    protected function logSecurityEvent(string $event, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $context);

        Log::channel('security')->warning("Security event: {$event}", $logData);
    }
}
