<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear rate limiter before each test
        RateLimiter::clear($this->throttleKey());
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'nim' => '12345678',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->post('/auth/login', [
            'nim' => '12345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'nim' => '12345678',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->post('/auth/login', [
            'nim' => '12345678',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    /** @test */
    public function suspended_user_cannot_login()
    {
        User::factory()->create([
            'nim' => '12345678',
            'password' => Hash::make('password123'),
            'status' => 'suspended',
        ]);

        $response = $this->post('/auth/login', [
            'nim' => '12345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    /** @test */
    public function inactive_user_cannot_login()
    {
        User::factory()->create([
            'nim' => '12345678',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/auth/login', [
            'nim' => '12345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    /** @test */
    public function login_is_rate_limited_after_5_attempts()
    {
        User::factory()->create([
            'nim' => '12345678',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/auth/login', [
                'nim' => '12345678',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/auth/login', [
            'nim' => '12345678',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nim']);
        $this->assertStringContainsString('Terlalu banyak', $response->json('errors.nim.0'));
    }

    /** @test */
    public function session_is_regenerated_after_login()
    {
        $user = User::factory()->create([
            'nim' => '12345678',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $oldSessionId = session()->getId();

        $this->post('/auth/login', [
            'nim' => '12345678',
            'password' => 'password123',
        ]);

        $newSessionId = session()->getId();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /** @test */
    public function authenticated_user_cannot_access_login_page()
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /** @test */
    public function session_is_invalidated_after_logout()
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user);
        
        $oldSessionId = session()->getId();

        $this->post('/logout');

        // Try to access protected route with old session
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function active_user_can_access_dashboard()
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function suspended_user_is_logged_out_when_accessing_protected_route()
    {
        $user = User::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /** @test */
    public function login_validates_nim_format()
    {
        $response = $this->post('/auth/login', [
            'nim' => '123', // Too short
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nim']);
    }

    /** @test */
    public function login_validates_password_length()
    {
        $response = $this->post('/auth/login', [
            'nim' => '12345678',
            'password' => '123', // Too short
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function login_requires_nim_and_password()
    {
        $response = $this->post('/auth/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nim', 'password']);
    }

    /**
     * Get the throttle key for testing
     */
    protected function throttleKey(): string
    {
        return 'test-nim|127.0.0.1';
    }
}
