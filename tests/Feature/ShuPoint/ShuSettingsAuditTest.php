<?php

namespace Tests\Feature\ShuPoint;

use App\Livewire\Admin\Settings\StoreSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShuSettingsAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'kelola_pengaturan', 'guard_name' => 'web']);
    }

    public function test_setting_change_creates_audit_log(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->givePermissionTo(['kelola_pengaturan']);
        $this->actingAs($user);

        // Value: 10000 rupiah = 1 point
        Setting::set('shu_point_conversion_amount', '10000');

        Livewire::actingAs($user)
            ->test(StoreSettings::class)
            ->set('shuConversionAmount', '5000') // Change to 5000 rupiah = 1 point
            ->call('saveShuSettings')
            ->assertDispatched('toast', type: 'success');

        $this->assertSame('5000', Setting::get('shu_point_conversion_amount'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update',
            'model' => \App\Models\Setting::class,
        ]);
    }
}
