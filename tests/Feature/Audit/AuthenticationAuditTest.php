<?php

namespace Tests\Feature\Audit;

use App\Livewire\Auth\LoginForm;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

/**
 * Authentication System Audit Tests
 *
 * Tests login flow, session management, and authentication security.
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8
 */
class AuthenticationAuditTest extends AuditTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiter before each test
        RateLimiter::clear('test|127.0.0.1');
    }

    // ==========================================
    // 3.1 Login Flow Tests
    // Requirements: 2.1, 2.2, 2.3, 2.4, 2.5
    // ==========================================

    /**
     * Test login page renders correctly with NIM and password fields.
     * Requirement 2.1: WHEN a guest accesses the login page (/admin/masuk)
     * THEN the System SHALL display the login form with NIM and password fields
     */
    public function test_login_page_renders_correctly(): void
    {
        $response = $this->get('/admin/masuk');

        $response->assertStatus(200);
        // Verify the page contains login form elements
        $response->assertSee('NIM');
        $response->assertSee('Password');
    }

    /**
     * Test successful login with valid credentials.
     * Requirement 2.2: WHEN a user submits valid credentials (NIM and password)
     * THEN the System SHALL authenticate the user and redirect to the dashboard within 2 seconds
     */
    public function test_successful_login_with_valid_credentials(): void
    {
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testlogin@test.com',
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

    /**
     * Test failed login with invalid credentials shows appropriate error.
     * Requirement 2.3: WHEN a user submits invalid credentials
     * THEN the System SHALL display an appropriate error message without revealing which field is incorrect
     */
    public function test_failed_login_with_invalid_credentials(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testlogin2@test.com',
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

    /**
     * Test failed login with non-existent NIM.
     * Requirement 2.3: Error message should not reveal which field is incorrect
     */
    public function test_failed_login_with_nonexistent_nim(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '99999999')
            ->set('password', 'anypassword')
            ->call('login')
            ->assertHasErrors(['nim']);

        $this->assertGuest();
    }

    /**
     * Test rate limiting after 5 failed attempts.
     * Requirement 2.4: WHEN a user attempts more than 5 failed logins within 1 minute
     * THEN the System SHALL implement rate limiting and display the remaining lockout time
     */
    public function test_rate_limiting_after_5_failed_attempts(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testrate@test.com',
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

        // Check that rate limiting error is shown
        $component->assertHasErrors(['nim']);

        // The error message should contain rate limiting info
        $errors = $component->errors();
        $this->assertNotEmpty($errors->get('nim'));

        // Verify the error mentions "Terlalu banyak" (too many attempts)
        $errorMessage = $errors->first('nim');
        $this->assertStringContainsString('Terlalu banyak', $errorMessage);
    }

    /**
     * Test inactive user cannot login.
     * Requirement 2.5: WHEN an inactive user attempts to login
     * THEN the System SHALL reject the login and display an appropriate message
     */
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

    /**
     * Test suspended user cannot login.
     * Additional test for status validation
     */
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

    /**
     * Test login validation requires NIM.
     */
    public function test_login_requires_nim(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['nim' => 'required']);
    }

    /**
     * Test login validation requires password.
     */
    public function test_login_requires_password(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['password' => 'required']);
    }

    /**
     * Test NIM minimum length validation.
     */
    public function test_nim_minimum_length_validation(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '123')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['nim' => 'min']);
    }

    /**
     * Test password minimum length validation.
     */
    public function test_password_minimum_length_validation(): void
    {
        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', '123')
            ->call('login')
            ->assertHasErrors(['password' => 'min']);
    }

    // ==========================================
    // 3.2 Session Management Tests
    // Requirements: 2.6, 2.7, 2.8
    // ==========================================

    /**
     * Test logout terminates session.
     * Requirement 2.6: WHEN an authenticated user clicks logout
     * THEN the System SHALL terminate the session and redirect to the login page
     */
    public function test_logout_terminates_session(): void
    {
        // Use the pre-created test user from AuditTestCase
        $response = $this->actingAs($this->anggota)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('admin.logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test session is invalidated after logout.
     * Requirement 2.6: Session should be properly terminated
     */
    public function test_session_invalidated_after_logout(): void
    {
        // Login and then logout
        $this->actingAs($this->anggota)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('admin.logout'));

        // After logout, trying to access protected route should redirect to login
        $response = $this->get('/admin/beranda');
        $response->assertRedirect('/admin/masuk');
    }

    /**
     * Test guest redirect to login for protected routes.
     * Requirement 2.7: WHEN a guest attempts to access any protected route (/admin/*)
     * THEN the System SHALL redirect to the login page
     */
    public function test_guest_redirect_to_login_for_protected_routes(): void
    {
        // Test various protected routes
        $protectedRoutes = [
            '/admin/beranda',
            '/admin/absensi',
            '/admin/jadwal',
            '/admin/produk',
            '/admin/pengguna',
            '/admin/laporan/absensi',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/admin/masuk');
        }
    }

    /**
     * Test authenticated user can access dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->anggota)->get('/admin/beranda');

        $response->assertStatus(200);
    }

    /**
     * Test authenticated user is redirected from login page.
     */
    public function test_authenticated_user_redirected_from_login_page(): void
    {
        $response = $this->actingAs($this->anggota)->get('/admin/masuk');

        $response->assertRedirect('/admin/beranda');
    }

    /**
     * Test session regeneration after successful login.
     * Security: Session should be regenerated to prevent session fixation
     */
    public function test_session_regenerated_after_login(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testregen@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $oldSessionId = session()->getId();

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'password123')
            ->call('login');

        $newSessionId = session()->getId();

        // Session ID should change after login
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /**
     * Test remember me functionality.
     */
    public function test_remember_me_functionality(): void
    {
        $user = User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testremember@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'password123')
            ->set('remember', true)
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login history is recorded on successful login.
     */
    public function test_login_history_recorded_on_successful_login(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testhistory@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'password123')
            ->call('login');

        $this->assertDatabaseHas('login_histories', [
            'status' => 'success',
        ]);
    }

    /**
     * Test login history is recorded on failed login.
     */
    public function test_login_history_recorded_on_failed_login(): void
    {
        User::create([
            'nim' => '12345678',
            'name' => 'Test User',
            'email' => 'testfailhistory@test.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        Livewire::test(LoginForm::class)
            ->set('nim', '12345678')
            ->set('password', 'wrongpassword')
            ->call('login');

        $this->assertDatabaseHas('login_histories', [
            'status' => 'failed',
        ]);
    }
}
