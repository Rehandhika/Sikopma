<?php

namespace App\Models;

use App\Services\Storage\FileStorageServiceInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'content',
        'link',
        'image_path',
        'priority',
        'is_active',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('published_at')
                ->orWhere('published_at', '<=', now());
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc')
            ->orderBy('published_at', 'desc');
    }

    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return $this->getStorageUrl($this->image_path, 'desktop');
        }

        return null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return $this->getStorageUrl($this->image_path, 'mobile');
        }

        return null;
    }

    public function getTabletUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return $this->getStorageUrl($this->image_path, 'tablet');
        }

        return null;
    }

    public function getDesktopUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return $this->getStorageUrl($this->image_path, 'desktop');
        }

        return null;
    }

    public function getMobileUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return $this->getStorageUrl($this->image_path, 'mobile');
        }

        return null;
    }

    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Get storage URL for image with fallback support.
     */
    protected function getStorageUrl(string $path, ?string $variant = null): ?string
    {
        try {
            // Try using FileStorageService
            $fileStorageService = app(FileStorageServiceInterface::class);
            $url = $fileStorageService->getUrl($path, $variant);

            if ($url) {
                return $url;
            }
        } catch (\Exception $e) {
            // Fallback to legacy method
        }

        // Fallback: check if it's old format (news/uuid_size.jpg)
        if (str_starts_with($path, 'news/')) {
            return $this->getLegacyUrl($path, $variant);
        }

        // Fallback: direct storage URL
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return null;
    }

    /**
     * Get URL for legacy news format (news/uuid_size.jpg).
     */
    protected function getLegacyUrl(string $path, ?string $variant = null): ?string
    {
        $pathInfo = pathinfo($path);
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';
        $directory = $pathInfo['dirname'];

        // Map variant to legacy size
        $sizeMap = [
            'desktop' => 1920,
            'tablet' => 768,
            'mobile' => 480,
        ];

        $size = $sizeMap[$variant] ?? 480;
        $legacyPath = "{$directory}/{$filename}_{$size}.{$extension}";

        if (Storage::disk('public')->exists($legacyPath)) {
            return asset('storage/'.$legacyPath);
        }

        // Fallback to original path
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return null;
    }
}
