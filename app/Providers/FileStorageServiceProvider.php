<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Storage\CacheManager;
use App\Services\Storage\CacheManagerInterface;
use App\Services\Storage\FileCleanupService;
use App\Services\Storage\FileCleanupServiceInterface;
use App\Services\Storage\FileSecurityService;
use App\Services\Storage\FileSecurityServiceInterface;
use App\Services\Storage\FileStorageService;
use App\Services\Storage\FileStorageServiceInterface;
use App\Services\Storage\ImageProcessingService;
use App\Services\Storage\ImageProcessingServiceInterface;
use App\Services\Storage\MigrationTool;
use App\Services\Storage\MigrationToolInterface;
use App\Services\Storage\StorageMonitor;
use App\Services\Storage\StorageMonitorInterface;
use App\Services\Storage\StorageOrganizer;
use App\Services\Storage\StorageOrganizerInterface;
use App\Services\Storage\ThumbnailGenerator;
use App\Services\Storage\ThumbnailGeneratorInterface;

/**
 * Service Provider untuk File Storage System
 * 
 * Mendaftarkan semua services yang terkait dengan file storage:
 * - StorageOrganizer: Mengatur struktur direktori penyimpanan
 * - CacheManager: Mengelola cache URL file
 * - ImageProcessingService: Memproses gambar (resize, compress, convert)
 * - ThumbnailGenerator: Membuat thumbnail dari gambar
 * - FileSecurityService: Menangani keamanan file (signed URLs, access control)
 * - FileStorageService: Entry point utama untuk semua operasi file
 * - FileCleanupService: Membersihkan file orphan dan temporary
 * - StorageMonitor: Monitoring penggunaan storage
 * - MigrationTool: Migrasi file existing ke struktur baru
 */
class FileStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerCoreServices();
        $this->registerMainService();
        $this->registerSupportServices();
    }

    /**
     * Register core storage services
     */
    protected function registerCoreServices(): void
    {
        // StorageOrganizer - Mengatur struktur path file
        $this->app->singleton(StorageOrganizerInterface::class, function ($app) {
            return new StorageOrganizer();
        });

        // CacheManager - Mengelola cache URL
        $this->app->singleton(CacheManagerInterface::class, function ($app) {
            return new CacheManager(
                $app->make(StorageOrganizerInterface::class)
            );
        });

        // ImageProcessingService - Memproses gambar
        $this->app->singleton(ImageProcessingServiceInterface::class, function ($app) {
            return new ImageProcessingService();
        });

        // ThumbnailGenerator - Membuat thumbnail
        $this->app->singleton(ThumbnailGeneratorInterface::class, function ($app) {
            return new ThumbnailGenerator(
                $app->make(StorageOrganizerInterface::class)
            );
        });

        // FileSecurityService - Keamanan file
        $this->app->singleton(FileSecurityServiceInterface::class, function ($app) {
            return new FileSecurityService();
        });
    }

    /**
     * Register main FileStorageService
     */
    protected function registerMainService(): void
    {
        // FileStorageService - Entry point utama
        $this->app->singleton(FileStorageServiceInterface::class, function ($app) {
            return new FileStorageService(
                $app->make(ImageProcessingServiceInterface::class),
                $app->make(StorageOrganizerInterface::class),
                $app->make(CacheManagerInterface::class),
                $app->make(ThumbnailGeneratorInterface::class),
                $app->make(FileSecurityServiceInterface::class)
            );
        });
    }

    /**
     * Register support services
     */
    protected function registerSupportServices(): void
    {
        // FileCleanupService - Membersihkan file orphan
        $this->app->singleton(FileCleanupServiceInterface::class, function ($app) {
            return new FileCleanupService(
                $app->make(StorageOrganizerInterface::class)
            );
        });

        // StorageMonitor - Monitoring storage
        $this->app->singleton(StorageMonitorInterface::class, function ($app) {
            return new StorageMonitor();
        });

        // MigrationTool - Migrasi file existing
        $this->app->singleton(MigrationToolInterface::class, function ($app) {
            return new MigrationTool(
                $app->make(StorageOrganizerInterface::class),
                $app->make(ImageProcessingServiceInterface::class),
                $app->make(FileStorageServiceInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../../config/filestorage.php' => config_path('filestorage.php'),
        ], 'filestorage-config');

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/filestorage.php',
            'filestorage'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            StorageOrganizerInterface::class,
            CacheManagerInterface::class,
            ImageProcessingServiceInterface::class,
            ThumbnailGeneratorInterface::class,
            FileSecurityServiceInterface::class,
            FileStorageServiceInterface::class,
            FileCleanupServiceInterface::class,
            StorageMonitorInterface::class,
            MigrationToolInterface::class,
        ];
    }
}
