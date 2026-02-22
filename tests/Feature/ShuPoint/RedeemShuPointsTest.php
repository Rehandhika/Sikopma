<?php

namespace Tests\Feature\ShuPoint;

use App\Livewire\ShuPoint\StudentDetail;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RedeemShuPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $permission1 = Permission::firstOrCreate(['name' => 'lihat_poin_shu', 'guard_name' => 'web']);
        $permission2 = Permission::firstOrCreate(['name' => 'kelola_poin_shu', 'guard_name' => 'web']);
    }

    public function test_redeem_creates_transaction_and_decreases_balance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->givePermissionTo(['lihat_poin_shu', 'kelola_poin_shu']);
        $this->actingAs($user);

        $student = Student::factory()->create(['points_balance' => 500]);

        // StudentDetail component has properties to bind to and a redeem method
        Livewire::actingAs($user)
            ->test(StudentDetail::class, ['student' => $student])
            ->set('redeemPoints', 200)
            ->set('redeemCashAmount', 10000)
            ->set('redeemNotes', 'Test pencairan')
            ->call('redeem')
            ->assertDispatched('toast', type: 'success');

        $student->refresh();
        $this->assertSame(300, $student->points_balance);

        $this->assertDatabaseHas('shu_point_transactions', [
            'student_id' => $student->id,
            'type' => 'redeem',
            'points' => -200,
            'cash_amount' => 10000,
            'notes' => 'Test pencairan',
        ]);
    }

    public function test_redeem_fails_when_balance_insufficient(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->givePermissionTo(['lihat_poin_shu', 'kelola_poin_shu']);
        $this->actingAs($user);

        $student = Student::factory()->create(['points_balance' => 50]);

        Livewire::actingAs($user)
            ->test(StudentDetail::class, ['student' => $student])
            ->set('redeemPoints', 200)
            ->call('redeem')
            ->assertDispatched('toast', type: 'error'); // Should show error toast for insufficient balance

        $student->refresh();
        $this->assertSame(50, $student->points_balance);
        $this->assertDatabaseCount('shu_point_transactions', 0);
    }
}
