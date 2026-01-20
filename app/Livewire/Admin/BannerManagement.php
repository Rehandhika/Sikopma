<?php

namespace App\Livewire\Admin;

use App\Models\Banner;
use App\Services\BannerService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class BannerManagement extends Component
{
    use WithPagination, WithFileUploads;

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
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Ketua'])) {
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
                
                $this->dispatch('alert', type: 'success', message: 'Banner berhasil diperbarui');
            } else {
                // Create new banner
                $this->bannerService->store([
                    'title' => $this->title,
                    'priority' => $this->priority,
                ], $this->image);
                
                $this->dispatch('alert', type: 'success', message: 'Banner berhasil dibuat');
            }

            $this->resetForm();
            $this->showForm = false;
            
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal menyimpan banner: ' . $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $this->bannerService->delete($banner);
            
            $this->dispatch('alert', type: 'success', message: 'Banner berhasil dihapus');
            
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal menghapus banner: ' . $e->getMessage());
        }
    }

    public function toggleStatus(int $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $this->bannerService->toggleStatus($banner);
            
            $statusText = $banner->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->dispatch('alert', type: 'success', message: "Banner berhasil {$statusText}");
            
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal mengubah status banner: ' . $e->getMessage());
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