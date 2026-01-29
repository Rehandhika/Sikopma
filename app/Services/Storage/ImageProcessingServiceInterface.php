<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\ProcessedImage;
use App\Services\Storage\DTOs\ValidationResult;
use Illuminate\Http\UploadedFile;

/**
 * Interface untuk ImageProcessingService.
 * Menangani validasi, resize, dan konversi format gambar.
 */
interface ImageProcessingServiceInterface
{
    /**
     * Validate image file berdasarkan konfigurasi tipe.
     *
     * @param  UploadedFile  $file  File yang akan divalidasi
     * @param  string  $type  Tipe file (product, banner, attendance, profile, leave, report)
     * @return ValidationResult Hasil validasi
     */
    public function validate(UploadedFile $file, string $type): ValidationResult;

    /**
     * Process image: validate, resize, convert to WebP jika dikonfigurasi.
     *
     * @param  string  $sourcePath  Path file sumber (full path di filesystem)
     * @param  string  $type  Tipe file untuk konfigurasi processing
     * @param  string  $targetPath  Path target untuk menyimpan hasil
     * @return ProcessedImage Informasi gambar yang sudah diproses
     */
    public function process(string $sourcePath, string $type, string $targetPath): ProcessedImage;

    /**
     * Generate size variants berdasarkan konfigurasi tipe.
     *
     * @param  string  $originalPath  Path file original (full path di filesystem)
     * @param  string  $type  Tipe file untuk konfigurasi variants
     * @param  string  $baseTargetPath  Base path untuk menyimpan variants
     * @return array<string, ProcessedImage> Array of variant name => ProcessedImage
     */
    public function generateVariants(string $originalPath, string $type, string $baseTargetPath): array;

    /**
     * Check apakah file adalah gambar yang valid.
     *
     * @param  string  $path  Path file (full path di filesystem)
     * @return bool True jika file adalah gambar valid
     */
    public function isValidImage(string $path): bool;

    /**
     * Get image dimensions.
     *
     * @param  string  $path  Path file (full path di filesystem)
     * @return array{width: int, height: int}|null Dimensions atau null jika bukan gambar
     */
    public function getDimensions(string $path): ?array;

    /**
     * Get supported input formats.
     *
     * @return array<string> List of supported MIME types
     */
    public function getSupportedFormats(): array;
}
