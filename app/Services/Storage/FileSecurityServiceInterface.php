<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;

/**
 * Interface untuk FileSecurityService.
 *
 * Menyediakan kontrak untuk:
 * - Validasi MIME content
 * - Generate signed URLs
 * - Access logging
 * - Path sanitization
 */
interface FileSecurityServiceInterface
{
    /**
     * Validate that file content matches declared MIME type.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $declaredMime  The declared MIME type
     * @return bool True if content matches declared MIME
     */
    public function validateMimeContent(UploadedFile $file, string $declaredMime): bool;

    /**
     * Validate MIME content from file path.
     *
     * @param  string  $filePath  Path to the file
     * @param  string  $declaredMime  The declared MIME type
     * @return bool True if content matches declared MIME
     */
    public function validateMimeContentFromPath(string $filePath, string $declaredMime): bool;

    /**
     * Generate a signed URL for private file access.
     *
     * @param  string  $path  File path
     * @param  string  $disk  Storage disk
     * @param  int|null  $expirationMinutes  URL expiration in minutes
     * @return string Signed URL
     */
    public function generateSignedUrl(string $path, string $disk = 'local', ?int $expirationMinutes = null): string;

    /**
     * Validate a signed URL.
     *
     * @param  string  $signature  URL signature
     * @param  string  $path  File path
     * @param  string  $disk  Storage disk
     * @return bool True if signature is valid
     */
    public function validateSignedUrl(string $signature, string $path, string $disk): bool;

    /**
     * Log file access attempt.
     *
     * @param  string  $path  File path
     * @param  string  $action  Action performed (view, download, delete, etc.)
     * @param  array  $context  Additional context
     */
    public function logFileAccess(string $path, string $action, array $context = []): void;

    /**
     * Sanitize file path to prevent path traversal attacks.
     *
     * @param  string  $path  Path to sanitize
     * @return string Sanitized path
     *
     * @throws \App\Services\Storage\Exceptions\FileValidationException If path contains traversal attempts
     */
    public function sanitizePath(string $path): string;

    /**
     * Sanitize filename to prevent malicious filenames.
     *
     * @param  string  $filename  Filename to sanitize
     * @return string Sanitized filename
     */
    public function sanitizeFilename(string $filename): string;

    /**
     * Check if file is stored on private disk.
     *
     * @param  string  $path  File path
     * @param  string  $type  File type
     * @return bool True if file is private
     */
    public function isPrivateFile(string $path, string $type): bool;

    /**
     * Check if file access requires authentication.
     *
     * @param  string  $path  File path
     * @param  string  $type  File type
     * @return bool True if authentication is required
     */
    public function requiresAuthentication(string $path, string $type): bool;
}
