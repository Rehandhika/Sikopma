<?php

namespace Tests\Feature\ShuPoint;

use App\Livewire\Cashier\PosEntry;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PosEntryStudentNimAwardPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_all_with_student_nim_assigns_student_and_awards_points(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        // Set conversion: 100 rupiah = 1 point
        // So 10000 rupiah = 100 points
        Setting::set('shu_point_conversion_amount', '100');
        Cache::forget('shu_point_conversion_amount');

        $student = Student::factory()->create(['nim' => '123456789', 'points_balance' => 0]);

        $product = Product::factory()->create([
            'name' => 'Produk Test',
            'sku' => 'SKU-TEST',
            'price' => 10000,
            'stock' => 100,
            'status' => 'active',
        ]);

        $rows = [
            [
                'product_id' => $product->id, 
                'product_name' => $product->name,
                'student_nim' => $student->nim, 
                'qty' => 1, 
                'price' => 10000,
                'payment_method' => 'cash'
            ]
        ];

        Livewire::actingAs($user)
            ->test(PosEntry::class)
            ->call('submitAll', $rows)
            ->assertDispatched('toast', type: 'success');

        $student->refresh();
        $this->assertEquals(100, $student->points_balance);

        $this->assertDatabaseHas('sales', [
            'student_id' => $student->id,
            'shu_points_earned' => 100,
            'conversion_rate' => 100,
        ]);

        $this->assertDatabaseHas('shu_point_transactions', [
            'student_id' => $student->id,
            'type' => 'earn',
            'points' => 100,
            'conversion_rate' => 100,
        ]);
    }
}
