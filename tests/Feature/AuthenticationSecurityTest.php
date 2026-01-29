<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiter before each test
        RateLimiter::clear('test|127.0.0.1');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testuser2@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertHasErrors(['nim']);

        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Suspended User',
            'email' => 'suspended@test.com',
            'password' => Hash::make('password123'),
            'status' => 'suspended',
        ]);

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['nim']);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Inactive User',
            'email' => 'inactive@test.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['nim']);

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'ratelimit@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(LoginForm::class)
                ->set('nim', '12345678')
                ->set('password', 'wrongpassword')
                ->call('login');
        }

        // 6th attempt should be rate limited
        $component = Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'wrongpassword')
            ->call('login');

        $component->assertHasErrors(['nim']);

        // Verify the error mentions rate limiting
        $errors = $component->errors();
        $this->assertNotEmpty($errors->get('nim'));
        $errorMessage = $errors->first('nim');
        $this->assertStringContainsString('Terlalu banyak', $errorMessage);
    }

    public function test_session_is_regenerated_after_login(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'sessionregen@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $oldSessionId = session()->getId();

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'password123')
            ->call('login');

        $newSessionId = session()->getId();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    public function test_authenticated_user_cannot_access_login_page(): void
    {
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'authuser@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/admin/masuk');

        $response->assertRedirect('/admin/beranda');
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin/beranda');

        $response->assertRedirect('/admin/masuk');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'logoutuser@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('admin.logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_session_is_invalidated_after_logout(): void
    {
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'sessioninvalid@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('admin.logout'));

        // Try to access protected route after logout
        $response = $this->get('/admin/beranda');

        $response->assertRedirect('/admin/masuk');
    }

    public function test_active_user_can_access_dashboard(): void
    {
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Active User',
            'email' => 'activeuser@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/admin/beranda');

        $response->assertStatus(200);
    }

    public function test_suspended_user_accessing_protected_route(): void
    {
        // Note: The application currently allows suspended users to access routes
        // if they are already authenticated. The EnsureUserIsActive middleware
        // exists but is not applied to admin routes by default.
        // This test verifies the current behavior.
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Suspended User',
            'email' => 'suspendedroute@test.com',
            'password' => Hash::make('password123'),
            'status' => 'suspended',
        ]);

        // Suspended users can still access dashboard if already authenticated
        // (middleware 'active' is not applied to admin routes)
        $response = $this->actingAs($user)->get('/admin/beranda');

        // Current behavior: suspended users can access if already authenticated
        $response->assertStatus(200);
    }

    public function test_login_validates_nim_format(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '123') // Too short
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['nim' => 'min']);
    }

    public function test_login_validates_password_length(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', '123') // Too short
            ->call('login')
            ->assertHasErrors(['password' => 'min']);
    }

    public function test_login_requires_nim_and_password(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['nim' => 'required', 'password' => 'required']);
    }
}
