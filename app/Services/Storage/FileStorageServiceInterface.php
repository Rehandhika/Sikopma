<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\FileResult;
use Illuminate\Http\UploadedFile;

/**
 * Interface untuk FileStorageService.
 * Entry point utama untuk semua operasi penyimpanan file.
 */
interface FileStorageServiceInterface
{
    /**
     * Upload file dengan processing otomatis berdasarkan tipe.
     *
     * @param  UploadedFile  $file  File yang akan diupload
     * @param  string  $type  Tipe file (product, banner, attendance, profile, leave, report)
     * @param  array  $options  Opsi tambahan:
     *                          - 'old_path' => string|null - Path file lama untuk dihapus
     *                          - 'user_id' => int|null - ID user untuk logging
     *                          - 'day' => int|null - Hari untuk attendance (1-31)
     * @return FileResult Hasil upload dengan path, URL, dan variants
     *
     * @throws \App\Services\Storage\Exceptions\FileValidationException
     * @throws \App\Services\Storage\Exceptions\FileProcessingException
     * @throws \App\Services\Storage\Exceptions\FileStorageException
     */
    public function upload(UploadedFile $file, string $type, array $options = []): FileResult;

    /**
     * Upload dari base64 string (untuk camera capture).
     *
     * @param  string  $base64  Base64 encoded image data
     * @param  string  $type  Tipe file
     * @param  array  $options  Opsi tambahan (sama dengan upload())
     * @return FileResult Hasil upload
     *
     * @throws \App\Services\Storage\Exceptions\FileValidationException
     * @throws \App\Services\Storage\Exceptions\FileProcessingException
     * @throws \App\Services\Storage\Exceptions\FileStorageException
     */
    public function uploadFromBase64(string $base64, string $type, array $options = []): FileResult;

    /**
     * Get URL untuk file dengan size tertentu.
     *
     * @param  string  $path  Path file relatif
     * @param  string|null  $size  Nama variant (thumbnail, medium, large, etc.) atau null untuk original
     * @return string|null URL file atau null jika tidak ditemukan
     */
    public function getUrl(string $path, ?string $size = null): ?string;

    /**
     * Get signed URL untuk private file access.
     *
     * @param  string  $path  File path
     * @param  string|null  $size  Size variant
     * @param  int|null  $expirationMinutes  URL expiration in minutes
     * @return string|null Signed URL or null if file doesn't exist
     */
    public function getSignedUrl(string $path, ?string $size = null, ?int $expirationMinutes = null): ?string;

    /**
     * Check if file type uses private storage.
     *
     * @param  string  $type  File type
     * @return bool True if private
     */
    public function isPrivateFile(string $type): bool;

    /**
     * Delete file dan semua variants.
     *
     * @param  string  $path  Path file relatif
     * @return bool True jika berhasil dihapus
     */
    public function delete(string $path): bool;

    /**
     * Check apakah file exists.
     *
     * @param  string  $path  Path file relatif
     * @return bool True jika file ada
     */
    public function exists(string $path): bool;

    /**
     * Get disk untuk tipe file tertentu.
     *
     * @param  string  $type  Tipe file
     * @return string Nama disk (public, local, s3, etc.)
     */
    public function getDiskForType(string $type): string;

    /**
     * Get konfigurasi untuk tipe file tertentu.
     *
     * @param  string  $type  Tipe file
     * @return array|null Konfigurasi atau null jika tipe tidak valid
     */
    public function getTypeConfig(string $type): ?array;
}
