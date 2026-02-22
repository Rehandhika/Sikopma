<?php

namespace Tests\Feature\ShuPoint;

use App\Livewire\Cashier\PosEntry;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Sale;
use App\Models\ShuPointTransaction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PosAwardsShuPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed necessary roles/permissions
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);
    }

    public function test_pos_awards_shu_points_when_student_nim_provided(): void
    {
        // Set conversion amount: 10000 rupiah = 1 point
        Setting::set('shu_point_conversion_amount', '10000');
        Cache::forget('shu_point_conversion_amount');

        $student = Student::factory()->create([
            'nim' => '222413550',
            'full_name' => 'Test Student',
            'points_balance' => 0,
        ]);

        $product = Product::factory()->create([
            'name' => 'Produk Test',
            'price' => 20000, // Should earn 2 points
            'stock' => 10,
            'status' => 'active',
        ]);

        $rows = [
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'qty' => 1,
                'price' => 20000,
                'student_nim' => $student->nim,
                'payment_method' => 'cash',
            ]
        ];

        Livewire::test(PosEntry::class)
            ->call('submitAll', $rows)
            ->assertDispatched('toast', type: 'success');

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals($student->id, $sale->student_id);
        $this->assertEquals(2, $sale->shu_points_earned);
        $this->assertEquals(10000, $sale->conversion_rate);

        $this->assertDatabaseHas('shu_point_transactions', [
            'student_id' => $student->id,
            'sale_id' => $sale->id,
            'type' => 'earn',
            'amount' => 20000,
            'conversion_rate' => 10000, 
            'points' => 2,
        ]);

        $student->refresh();
        $this->assertEquals(2, $student->points_balance);
    }

    public function test_pos_does_not_award_points_when_student_nim_empty(): void
    {
        // Set conversion amount: 10000 rupiah = 1 point
        Setting::set('shu_point_conversion_amount', '10000');
        Cache::forget('shu_point_conversion_amount');

        $product = Product::factory()->create([
            'name' => 'Produk Test 2',
            'price' => 20000,
            'stock' => 10,
            'status' => 'active',
        ]);

        $rows = [
            [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'qty' => 1,
                'price' => 20000,
                'student_nim' => '', // Empty NIM
                'payment_method' => 'cash',
            ]
        ];

        Livewire::test(PosEntry::class)
            ->call('submitAll', $rows)
            ->assertDispatched('toast', type: 'success');

        $this->assertEquals(0, ShuPointTransaction::count());
        
        $sale = Sale::first();
        $this->assertNull($sale->student_id);
        $this->assertEquals(0, $sale->shu_points_earned);
    }
}
