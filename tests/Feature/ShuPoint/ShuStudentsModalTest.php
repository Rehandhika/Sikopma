<?php

namespace Tests\Feature\ShuPoint;

use App\Livewire\ShuPoint\Monitoring;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShuStudentsModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'kelola_poin_shu', 'guard_name' => 'web']);
    }

    public function test_create_opens_modal_state(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->givePermissionTo(['kelola_poin_shu']);
        $this->actingAs($user);

        // Monitoring component handles students and has createStudent method
        Livewire::actingAs($user)
            ->test(Monitoring::class)
            ->call('createStudent')
            ->assertSet('showStudentModal', true);
    }
}
