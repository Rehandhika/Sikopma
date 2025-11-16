<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RouteAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles used in the app
        foreach (['super-admin', 'ketua', 'wakil-ketua', 'bph', 'member'] as $role) {
            Role::findOrCreate($role);
        }
    }

    private function makeUserWithRole(string $role): User
    {
        $user = User::create([
            'name' => ucfirst($role) . ' User',
            'nim' => 'NIM' . strtoupper(substr($role, 0, 3)) . rand(1000, 9999),
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $user->assignRole($role);
        return $user;
    }

    public function test_admin_can_access_admin_only_routes(): void
    {
        $admin = $this->makeUserWithRole('ketua');

        $this->actingAs($admin)
            ->get('/users/manage')->assertOk();
        $this->actingAs($admin)
            ->get('/settings')->assertOk();
        $this->actingAs($admin)
            ->get('/reports/attendance')->assertOk();
        $this->actingAs($admin)
            ->get('/reports/sales')->assertOk();
        $this->actingAs($admin)
            ->get('/reports/penalty')->assertOk();
        $this->actingAs($admin)
            ->get('/stock/adjustment')->assertOk();
        $this->actingAs($admin)
            ->get('/purchase/list')->assertOk();
        $this->actingAs($admin)
            ->get('/product/list')->assertOk();
    }

    public function test_member_cannot_access_admin_only_routes(): void
    {
        $member = $this->makeUserWithRole('Anggota');

        $this->actingAs($member)
            ->get('/users/manage')->assertForbidden();
        $this->actingAs($member)
            ->get('/settings')->assertForbidden();
        $this->actingAs($member)
            ->get('/reports/attendance')->assertForbidden();
        $this->actingAs($member)
            ->get('/reports/sales')->assertForbidden();
        $this->actingAs($member)
            ->get('/reports/penalty')->assertForbidden();
        $this->actingAs($member)
            ->get('/stock/adjustment')->assertForbidden();
        $this->actingAs($member)
            ->get('/purchase/list')->assertForbidden();
        $this->actingAs($member)
            ->get('/product/list')->assertForbidden();
    }
}
