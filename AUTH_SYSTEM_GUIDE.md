# 🔐 SIKOPMA - Authentication System Guide

**Last Updated:** 16 November 2025  
**Laravel Version:** 12.36.1  
**Status:** ✅ PRODUCTION READY

---

## 📋 OVERVIEW

SIKOPMA menggunakan sistem autentikasi Laravel standar dengan SimpleLoginController untuk login tradisional yang reliable dan mudah di-maintain.

### Current Authentication System
- **Controller:** `SimpleLoginController` (Traditional Laravel)
- **Method:** Form POST dengan `Auth::attempt()`
- **Session:** Database driver dengan middleware lengkap
- **Security:** CSRF, Rate limiting, Status validation

---

## 🎯 ROOT CAUSE ANALYSIS - LOGIN ISSUE (RESOLVED)

### Problem Timeline

#### 1. Initial Problem: Livewire Component Issues
- Livewire Login component memiliki masalah session handling
- Component state tidak sync dengan session
- Keputusan: Switch ke traditional controller

#### 2. Critical Problem: Laravel 11 Session Middleware
**ROOT CAUSE:** Laravel 11 mengubah struktur middleware - web middleware group TIDAK otomatis include session middleware.

**Error:** "Session store not set on request"

**Symptoms:**
- `Auth::attempt()` returns TRUE ✅
- `Auth::check()` returns TRUE ✅
- Database connection working ✅
- Password valid ✅
- **BUT** session tidak persist ❌

#### 3. Solution Applied

**File:** `bootstrap/app.php`

**Before (BROKEN):**
```php
$middleware->group('web', [
    \App\Http\Middleware\SanitizeInput::class,
]);
```

**After (FIXED):**
```php
$middleware->group('web', [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,  // ← CRITICAL FIX
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\SanitizeInput::class,
]);
```

**Key Learning:** Di Laravel 11, web middleware group harus didefinisikan secara eksplisit di `bootstrap/app.php`.

---

## 🚀 CURRENT LOGIN FLOW

### Routes
```php
// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [SimpleLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SimpleLoginController::class, 'login'])->name('login.post');
});

// Protected routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    Route::post('/logout', [SimpleLoginController::class, 'logout'])->name('logout');
});
```

### Login Process
```
User Request
    ↓
SimpleLoginController@showLoginForm
    ↓
User Submits Form
    ↓
SimpleLoginController@login
    ↓
Validate Input (nim, password)
    ↓
Auth::attempt(['nim' => $nim, 'password' => $password, 'status' => 'active'])
    ↓
Success? → Yes
    ↓
    session()->regenerate()
    ↓
    Redirect to Dashboard
    
Success? → No
    ↓
    Return Error Message
```

---

## 🔐 SECURITY FEATURES

### 1. CSRF Protection
- Otomatis via `ValidateCsrfToken` middleware
- Token di-generate di setiap form

### 2. Password Hashing
- Menggunakan bcrypt (Laravel default)
- Hash verification via `Auth::attempt()`

### 3. Session Security
- Session regeneration setelah login
- Session invalidation setelah logout
- Database session driver

### 4. Status Validation
- Hanya user dengan `status='active'` yang bisa login
- Middleware `EnsureUserIsActive` untuk auto-logout jika status berubah

### 5. Input Sanitization
- Custom middleware `SanitizeInput`
- Membersihkan input dari XSS

### 6. Security Headers
- Custom middleware `SecurityHeaders`
- X-Frame-Options, X-Content-Type-Options, dll

---

## 💻 CODE REFERENCE

### SimpleLoginController
```php
public function login(Request $request)
{
    $validated = $request->validate([
        'nim' => 'required|string|min:8|max:20',
        'password' => 'required|string|min:6',
    ]);

    $credentials = [
        'nim' => $validated['nim'],
        'password' => $validated['password'],
        'status' => 'active',
    ];

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->with('error', 'NIM atau password salah');
}
```

### Logout
```php
public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect()->route('login');
}
```

---

## 🧪 TESTING

### Test Credentials
```
Super Admin:
NIM: 00000000
Password: password
Status: active
```

### Manual Test Checklist
- ✅ Login dengan credentials benar
- ✅ Login dengan credentials salah
- ✅ Login dengan user inactive/suspended
- ✅ Akses dashboard tanpa login (harus redirect)
- ✅ Akses login setelah login (harus redirect ke dashboard)
- ✅ Logout dan verify session cleared
- ✅ Session regeneration setelah login

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
```

---

## 📝 BEST PRACTICES

### ✅ DO
- Gunakan `Auth::attempt()` dengan status validation
- Selalu regenerate session setelah login
- Invalidate session setelah logout
- Validate input dengan Laravel validation
- Log security events

### ❌ DON'T
- Jangan gunakan manual `Hash::check()` untuk login
- Jangan skip session regeneration
- Jangan hardcode credentials
- Jangan expose error details ke user

---

## 🎯 FUTURE IMPROVEMENTS

### Priority 1 (Recommended)
- [ ] Add rate limiting ke SimpleLoginController
- [ ] Add LoginHistory logging
- [ ] Add "Remember Me" functionality

### Priority 2 (Optional)
- [ ] Add "Forgot Password" feature
- [ ] Add email verification
- [ ] Add 2FA (Two-Factor Authentication)

### Priority 3 (Advanced)
- [ ] Add device management
- [ ] Add login notifications
- [ ] Add suspicious activity detection

---

## 📚 REFERENCES

- [Laravel 11 Authentication](https://laravel.com/docs/11.x/authentication)
- [Laravel 11 Middleware](https://laravel.com/docs/11.x/middleware)
- [Laravel 11 Session](https://laravel.com/docs/11.x/session)

---

**Maintained by:** SIKOPMA Development Team  
**Support:** Check `TROUBLESHOOTING.md` for common issues
