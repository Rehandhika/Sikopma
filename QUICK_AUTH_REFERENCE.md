# 🚀 QUICK AUTHENTICATION REFERENCE

## 📍 ROUTES

### Public Routes
```
GET  /              → Redirect ke /login atau /dashboard
GET  /login         → Login page (Livewire component)
```

### Protected Routes (Requires: auth + active)
```
GET  /dashboard     → Dashboard (hanya user active)
POST /logout        → Logout
```

### API Routes
```
POST /auth/login    → Login API endpoint (rate limited)
POST /auth/logout   → Logout API endpoint
```

---

## 🔐 LOGIN CREDENTIALS

### Super Admin (Default)
```
NIM: 00000000
Password: password
Status: active
```

### Test User
```
NIM: 12345678
Password: password123
Status: active
```

---

## 🛡️ SECURITY FEATURES

### Rate Limiting
- **Login**: 5 attempts per minute per IP
- **Lockout**: 60 seconds after 5 failed attempts

### Session Security
- Session regenerated after login
- Session invalidated after logout
- CSRF protection enabled

### User Status Validation
- Only `status='active'` can login
- Suspended users auto-logout
- Inactive users cannot login

---

## 💻 CODE EXAMPLES

### Login (Livewire)
```php
// app/Livewire/Auth/Login.php
public function login()
{
    $this->validate();
    
    $credentials = [
        'nim' => $this->nim,
        'password' => $this->password,
        'status' => 'active',
    ];
    
    if (Auth::attempt($credentials, $this->remember)) {
        request()->session()->regenerate();
        return redirect()->route('dashboard');
    }
    
    $this->addError('nim', 'NIM atau password salah.');
}
```

### Login (API)
```php
// app/Http/Controllers/Auth/AuthController.php
public function login(Request $request)
{
    $credentials = $request->validate([
        'nim' => 'required|string|min:8|max:20',
        'password' => 'required|string|min:6',
    ]);
    
    $credentials['status'] = 'active';
    
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        
        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }
    
    throw ValidationException::withMessages([
        'nim' => 'NIM atau password salah.',
    ]);
}
```

### Logout
```php
public function logout()
{
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    
    return redirect()->route('login');
}
```

### Check Authentication
```php
// In controller/component
if (Auth::check()) {
    $user = Auth::user();
}

// In blade
@auth
    <p>Welcome, {{ auth()->user()->name }}</p>
@endauth

@guest
    <a href="{{ route('login') }}">Login</a>
@endguest
```

### Protect Routes
```php
// routes/web.php
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardIndex::class);
    Route::get('/profile', ProfileIndex::class);
});
```

---

## 🧪 TESTING

### Manual Test Checklist
```
□ Login dengan credentials benar
□ Login dengan credentials salah
□ Login dengan user suspended
□ Login 6x salah (test rate limiting)
□ Akses /dashboard tanpa login
□ Akses /login setelah login
□ Logout dan verify session cleared
□ Check session regeneration
```

### cURL Examples
```bash
# Login
curl -X POST http://kopma.test/auth/login \
  -H "Content-Type: application/json" \
  -d '{"nim":"00000000","password":"password"}'

# Logout
curl -X POST http://kopma.test/auth/logout \
  -H "Authorization: Bearer {token}"
```

---

## 🐛 TROUBLESHOOTING

### "Too many login attempts"
```bash
# Clear rate limiter
php artisan cache:clear
```

### "Unauthenticated" error
```bash
# Check session driver
php artisan config:clear

# Verify session table exists
php artisan migrate
```

### Redirect loop
```bash
# Clear all caches
php artisan optimize:clear

# Check middleware in routes
php artisan route:list
```

### Session not persisting
```env
# Check .env
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Run migration
php artisan session:table
php artisan migrate
```

---

## 📝 VALIDATION RULES

### Login Form
```php
'nim' => 'required|string|min:8|max:20'
'password' => 'required|string|min:6'
```

### User Status
```php
'status' => 'in:active,suspended,inactive'
```

---

## 🔧 CONFIGURATION

### Session (.env)
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

### Auth (config/auth.php)
```php
'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

---

## 🚨 COMMON MISTAKES

### ❌ DON'T
```php
// Manual hash check
if (Hash::check($password, $user->password)) {
    Auth::login($user);
}

// No status validation
Auth::attempt(['nim' => $nim, 'password' => $password]);

// No session regeneration
Auth::login($user);
return redirect('/dashboard');
```

### ✅ DO
```php
// Use Auth::attempt with status
Auth::attempt([
    'nim' => $nim, 
    'password' => $password,
    'status' => 'active'
]);

// Always regenerate session
if (Auth::attempt($credentials)) {
    request()->session()->regenerate();
    return redirect('/dashboard');
}
```

---

## 📊 MONITORING

### Check Active Sessions
```sql
SELECT * FROM sessions WHERE user_id IS NOT NULL;
```

### Check Login Attempts (Activity Log)
```php
activity()
    ->where('description', 'User logged in successfully')
    ->latest()
    ->get();
```

### Check Rate Limiting
```php
use Illuminate\Support\Facades\RateLimiter;

$key = 'login-attempt|127.0.0.1';
$attempts = RateLimiter::attempts($key);
$remaining = RateLimiter::remaining($key, 5);
```

---

## 🎯 QUICK COMMANDS

```bash
# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list

# Run tests
php artisan test --filter=AuthenticationSecurityTest

# Check logs
tail -f storage/logs/laravel.log

# Create user
php artisan tinker
>>> User::create(['nim' => '12345678', 'password' => Hash::make('password'), 'status' => 'active'])
```

---

**Last Updated**: 16 November 2025  
**Version**: 2.0 (Security Hardened)
