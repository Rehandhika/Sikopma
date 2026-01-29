<?php

namespace App\Livewire\Admin;

use App\Models\Banner;
use App\Services\ActivityLogService;
use App\Services\BannerService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class BannerManagement extends Component
{
    use WithFileUploads, WithPagination;

    // Form properties
    #[Validate('nullable|string|max:255')]
    public $title = '';

    #[Validate('required_without:editingBannerId|image|mimes:jpg,jpeg,png|max:5120')]
    public $image;

    #[Validate('required|integer|min:0')]
    public $priority = 0;

    public $editingBannerId = null;

    public $showForm = false;

    public $imagePreview = null;

    protected BannerService $bannerService;

    public function boot(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function mount()
    {
        // Check authorization
        if (! auth()->user()->hasAnyRole(['Super Admin', 'Ketua'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id)
    {
        $banner = Banner::findOrFail($id);

        $this->editingBannerId = $banner->id;
        $this->title = $banner->title;
        $this->priority = $banner->priority;
        $this->image = null; // Reset image field for editing
        $this->imagePreview = null;
        $this->showForm = true;
    }

    public function updatedImage()
    {
        $this->validateOnly('image');

        if ($this->image) {
            $this->imagePreview = $this->image->temporaryUrl();
        }
    }

    public function removeImage()
    {
        $this->image = null;
        $this->imagePreview = null;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editingBannerId) {
                // Update existing banner
                $banner = Banner::findOrFail($this->editingBannerId);
                $this->bannerService->update($banner, [
                    'title' => $this->title,
                    'priority' => $this->priority,
                ], $this->image);

                // Log activity
                ActivityLogService::logBannerUpdated($this->title);

                $this->dispatch('toast', message: 'Banner berhasil diperbarui', type: 'success');
            } else {
                // Create new banner
                $this->bannerService->store([
                    'title' => $this->title,
                    'priority' => $this->priority,
                ], $this->image);

                // Log activity
                ActivityLogService::logBannerCreated($this->title);

                $this->dispatch('toast', message: 'Banner berhasil dibuat', type: 'success');
            }

            $this->resetForm();
            $this->showForm = false;

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal menyimpan banner: '.$e->getMessage(), type: 'error');
        }
    }

    public function delete(int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $bannerTitle = $banner->title;
            $this->bannerService->delete($banner);

            // Log activity
            ActivityLogService::logBannerDeleted($bannerTitle);

            $this->dispatch('toast', message: 'Banner berhasil dihapus', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal menghapus banner: '.$e->getMessage(), type: 'error');
        }
    }

    public function toggleStatus(int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $this->bannerService->toggleStatus($banner);

            $freshBanner = $banner->fresh();

            // Log activity
            ActivityLogService::logBannerStatusChanged($freshBanner->title, $freshBanner->is_active);

            $statusText = $freshBanner->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->dispatch('toast', message: "Banner berhasil {$statusText}", type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal mengubah status banner: '.$e->getMessage(), type: 'error');
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function resetForm()
    {
        $this->title = '';
        $this->image = null;
        $this->imagePreview = null;
        $this->priority = 0;
        $this->editingBannerId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $banners = Banner::with('creator')
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.banner-management', [
            'banners' => $banners,
        ])
            ->layout('layouts.app')
            ->title('Kelola Banner');
    }
}
