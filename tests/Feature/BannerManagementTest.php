<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BannerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create storage disk for testing
        Storage::fake('public');

        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Ketua']);
        Role::create(['name' => 'User']);
    }

    /** @test */
    public function admin_can_access_banner_management_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $response = $this->actingAs($admin)
            ->get(route('admin.banners'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('admin.banner-management');
    }

    /** @test */
    public function ketua_can_access_banner_management_page()
    {
        $ketua = User::factory()->create();
        $ketua->assignRole('Ketua');

        $response = $this->actingAs($ketua)
            ->get(route('admin.banners'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('admin.banner-management');
    }

    /** @test */
    public function regular_user_cannot_access_banner_management_page()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $response = $this->actingAs($user)
            ->get(route('admin.banners'));

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_banner_management_page()
    {
        $response = $this->get(route('admin.banners'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_create_banner()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $image = UploadedFile::fake()->image('banner.jpg', 1920, 1080);

        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->set('title', 'Test Banner')
            ->set('priority', 1)
            ->set('image', $image)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('alert', type: 'success');

        $this->assertDatabaseHas('banners', [
            'title' => 'Test Banner',
            'priority' => 1,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
    }

    /** @test */
    public function admin_can_edit_banner()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $banner = Banner::create([
            'title' => 'Original Title',
            'image_path' => 'banners/test.jpg',
            'priority' => 1,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->call('edit', $banner->id)
            ->assertSet('editingBannerId', $banner->id)
            ->assertSet('title', 'Original Title')
            ->assertSet('priority', 1)
            ->set('title', 'Updated Title')
            ->set('priority', 2)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('alert', type: 'success');

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'title' => 'Updated Title',
            'priority' => 2,
        ]);
    }

    /** @test */
    public function admin_can_toggle_banner_status()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $banner = Banner::create([
            'title' => 'Test Banner',
            'image_path' => 'banners/test.jpg',
            'priority' => 1,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->call('toggleStatus', $banner->id)
            ->assertDispatched('alert', type: 'success');

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function admin_can_delete_banner()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $banner = Banner::create([
            'title' => 'Test Banner',
            'image_path' => 'banners/test.jpg',
            'priority' => 1,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->call('delete', $banner->id)
            ->assertDispatched('alert', type: 'success');

        $this->assertDatabaseMissing('banners', [
            'id' => $banner->id,
        ]);
    }

    /** @test */
    public function banner_list_displays_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $banner1 = Banner::create([
            'title' => 'Banner 1',
            'image_path' => 'banners/banner1.jpg',
            'priority' => 1,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $banner2 = Banner::create([
            'title' => 'Banner 2',
            'image_path' => 'banners/banner2.jpg',
            'priority' => 2,
            'is_active' => false,
            'created_by' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->assertSee('Banner 1')
            ->assertSee('Banner 2')
            ->assertSee('Aktif')
            ->assertSee('Nonaktif');
    }

    /** @test */
    public function banner_validation_works_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        // Test required image for new banner
        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->set('title', 'Test Banner')
            ->set('priority', 1)
            ->call('save')
            ->assertHasErrors(['image']);

        // Test invalid image format
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);

        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->set('title', 'Test Banner')
            ->set('priority', 1)
            ->set('image', $invalidFile)
            ->call('save')
            ->assertHasErrors(['image']);

        // Test file too large (over 5MB)
        $largeFile = UploadedFile::fake()->image('large.jpg')->size(6000);

        Livewire::actingAs($admin)
            ->test('admin.banner-management')
            ->set('title', 'Test Banner')
            ->set('priority', 1)
            ->set('image', $largeFile)
            ->call('save')
            ->assertHasErrors(['image']);
    }

    /** @test */
    public function banner_pagination_works()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        // Create 15 banners (more than the 10 per page limit)
        for ($i = 1; $i <= 15; $i++) {
            Banner::create([
                'title' => "Banner {$i}",
                'image_path' => "banners/banner{$i}.jpg",
                'priority' => $i,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
        }

        $component = Livewire::actingAs($admin)
            ->test('admin.banner-management');

        // Should see first 10 banners
        $component->assertSee('Banner 1')
            ->assertSee('Banner 10')
            ->assertDontSee('Banner 11');

        // Navigate to page 2
        $component->set('page', 2)
            ->assertSee('Banner 11')
            ->assertSee('Banner 15')
            ->assertDontSee('Banner 1');
    }
}
