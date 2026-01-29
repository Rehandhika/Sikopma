<?php

namespace App\Services;

use App\Models\News;
use App\Services\Storage\FileStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NewsService - Service untuk mengelola berita/pengumuman.
 *
 * Mengikuti pola yang sama dengan BannerService untuk konsistensi.
 */
class NewsService
{
    public function __construct(
        protected FileStorageServiceInterface $fileStorageService
    ) {}

    /**
     * Store a new news item.
     */
    public function store(array $data, ?UploadedFile $image = null): News
    {
        return DB::transaction(function () use ($data, $image) {
            $newsData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'link' => $data['link'] ?? null,
                'priority' => $data['priority'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'published_at' => $data['published_at'] ?? now(),
                'expires_at' => $data['expires_at'] ?? null,
                'created_by' => auth()->id(),
            ];

            // Process the image if provided
            if ($image) {
                $result = $this->fileStorageService->upload($image, 'news');
                $newsData['image_path'] = $result->path;
            }

            // Create news record
            $news = News::create($newsData);

            // Log activity
            ActivityLogService::logNewsCreated($news->title);

            return $news;
        });
    }

    /**
     * Update an existing news item.
     */
    public function update(News $news, array $data, ?UploadedFile $image = null): News
    {
        return DB::transaction(function () use ($news, $data, $image) {
            $updateData = [
                'title' => $data['title'] ?? $news->title,
                'content' => $data['content'] ?? $news->content,
                'link' => $data['link'] ?? $news->link,
                'priority' => $data['priority'] ?? $news->priority,
                'is_active' => $data['is_active'] ?? $news->is_active,
                'published_at' => $data['published_at'] ?? $news->published_at,
                'expires_at' => $data['expires_at'] ?? $news->expires_at,
            ];

            // If new image is provided, process it and delete old images
            if ($image) {
                // Upload new image (FileStorageService will handle old file deletion)
                $result = $this->fileStorageService->upload($image, 'news', [
                    'old_path' => $news->image_path,
                ]);
                $updateData['image_path'] = $result->path;
            }

            $news->update($updateData);

            // Log activity
            ActivityLogService::logNewsUpdated($news->title);

            return $news->fresh();
        });
    }

    /**
     * Delete a news item and its associated images.
     */
    public function delete(News $news): bool
    {
        return DB::transaction(function () use ($news) {
            $title = $news->title;

            // Delete image files using FileStorageService
            if ($news->image_path) {
                try {
                    $this->fileStorageService->delete($news->image_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete news image: '.$e->getMessage());
                }
            }

            // Delete news record
            $deleted = $news->delete();

            if ($deleted) {
                // Log activity
                ActivityLogService::logNewsDeleted($title);
            }

            return $deleted;
        });
    }

    /**
     * Toggle news active status.
     */
    public function toggleStatus(News $news): News
    {
        $news->update([
            'is_active' => ! $news->is_active,
        ]);

        // Log activity
        ActivityLogService::logNewsStatusChanged($news->title, $news->is_active);

        return $news->fresh();
    }

    /**
     * Get active news ordered by priority and published date.
     *
     * @param  int  $limit  Maximum number of news items to return
     */
    public function getActiveNews(int $limit = 10): Collection
    {
        return News::active()
            ->notExpired()
            ->published()
            ->ordered()
            ->limit($limit)
            ->get();
    }
}
