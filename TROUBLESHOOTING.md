# 🔧 SIKOPMA - Troubleshooting Guide

**Last Updated:** 16 November 2025  
**Status:** Common Issues & Solutions

---

## 🚨 COMMON ISSUES

### 1. "Session store not set on request"

**Cause:** Session middleware tidak berjalan (Laravel 11 issue)

**Solution:**
```php
// bootstrap/app.php
$middleware->group('web', [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,  // ← MUST HAVE
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);
```

**Verify:**
```bash
php artisan optimize:clear
php artisan route:list
```

---

### 2. Login Form Reset Tanpa Error

**Possible Causes:**
- Middleware `guest` blocking request
- Session tidak persist
- Redirect loop

**Debug Steps:**

#### Step 1: Check Auth Status
```bash
php artisan tinker
>>> Auth::check()
>>> Auth::user()
```

#### Step 2: Check Session
```bash
# Check session table
php artisan db:table sessions

# Check session config
php artisan tinker
>>> config('session.driver')
>>> config('session.table')
```

#### Step 3: Test Simple Login
```
URL: http://kopma.test/login
NIM: 00000000
Password: password
```

**If still fails, check logs:**
```bash
tail -f storage/logs/laravel.log
```

---

### 3. "Too many login attempts"

**Cause:** Rate limiting triggered (5 attempts per minute)

**Solution:**
```bash
# Clear rate limiter cache
php artisan cache:clear

# Or wait 60 seconds
```

---

### 4. "CSRF token mismatch"

**Cause:** Token expired atau cache issue

**Solution:**
```bash
# Clear cache
php artisan optimize:clear

# Hard refresh browser
# Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)

# Check CSRF middleware
php artisan route:list --path=login
```

---

### 5. Redirect Loop (Login ↔ Dashboard)

**Cause:** Middleware conflict atau session issue

**Debug:**
```bash
# Check middleware
php artisan route:list

# Check auth status
php artisan tinker
>>> Auth::check()
```

**Solution:**
```bash
# Clear everything
php artisan optimize:clear

# Check middleware order in bootstrap/app.php
# Ensure StartSession comes before Authenticate
```

---

### 6. Session Tidak Persist

**Symptoms:**
- Login berhasil tapi langsung logout
- Auth::check() returns false setelah login
- Dashboard redirect ke login

**Debug:**

#### Check Session Driver
```env
# .env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

#### Check Session Table
```bash
php artisan migrate:status | grep sessions

# If not exists:
php artisan session:table
php artisan migrate
```

#### Check Session Middleware
```php
// bootstrap/app.php
// Ensure StartSession is in web middleware group
```

#### Test Session Manually
```bash
php artisan tinker
>>> session()->put('test', 'value')
>>> session()->get('test')
>>> session()->save()
```

---

### 7. User Tidak Bisa Login (Credentials Benar)

**Debug Steps:**

#### Step 1: Verify User Exists
```bash
php artisan tinker
>>> $user = \App\Models\User::where('nim', '00000000')->first()
>>> $user->name
>>> $user->status
```

#### Step 2: Verify Password
```bash
php artisan tinker
>>> $user = \App\Models\User::where('nim', '00000000')->first()
>>> Hash::check('password', $user->password)
```

#### Step 3: Check User Status
```bash
php artisan tinker
>>> $user = \App\Models\User::where('nim', '00000000')->first()
>>> $user->status  // Must be 'active'
```

**Solution:**
```bash
# If status is not 'active':
php artisan tinker
>>> $user = \App\Models\User::where('nim', '00000000')->first()
>>> $user->status = 'active'
>>> $user->save()
```

---

### 8. Dashboard Shows Null/Empty Data

**Cause:** Dashboard component expecting data yang tidak ada

**Solution:**
```php
// app/Livewire/Dashboard/Index.php
public function mount()
{
    // Ensure default values
    $this->totalMembers = User::count();
    $this->activeMembers = User::where('status', 'active')->count();
    // ... etc
}
```

---

## 🔍 DEBUGGING TOOLS

### 1. Check Routes
```bash
php artisan route:list
php artisan route:list --path=login
php artisan route:list --path=dashboard
```

### 2. Check Config
```bash
php artisan config:show auth
php artisan config:show session
```

### 3. Check Logs
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Last 50 lines
tail -n 50 storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log
```

### 4. Check Database
```bash
# Check sessions table
php artisan db:table sessions

# Check users table
php artisan db:table users
```

### 5. Tinker Commands
```bash
php artisan tinker

# Check auth
>>> Auth::check()
>>> Auth::user()
>>> Auth::id()

# Check session
>>> session()->all()
>>> session()->getId()

# Check user
>>> User::where('nim', '00000000')->first()
>>> User::count()

# Test auth
>>> Auth::attempt(['nim' => '00000000', 'password' => 'password', 'status' => 'active'])
>>> Auth::check()
```

---

## 🧪 TESTING CHECKLIST

### Manual Testing
- [ ] Clear cache: `php artisan optimize:clear`
- [ ] Visit login page
- [ ] Enter valid credentials
- [ ] Submit form
- [ ] Check redirect to dashboard
- [ ] Verify dashboard displays
- [ ] Test logout
- [ ] Test login again

### Browser Testing
- [ ] Clear browser cache
- [ ] Clear cookies
- [ ] Try incognito/private mode
- [ ] Check browser console (F12)
- [ ] Check network tab for errors

### Server Testing
- [ ] Check Laravel log
- [ ] Check PHP error log
- [ ] Check web server log (nginx/apache)
- [ ] Check database connection

---

## 🔧 QUICK FIXES

### Clear All Caches
```bash
php artisan optimize:clear
# This runs:
# - config:clear
# - cache:clear
# - route:clear
# - view:clear
# - event:clear
```

### Reset Session
```bash
# Truncate sessions table
php artisan tinker
>>> DB::table('sessions')->truncate()

# Or restart session
php artisan session:table
php artisan migrate:fresh --path=database/migrations/xxxx_create_sessions_table.php
```

### Reset User Password
```bash
php artisan tinker
>>> $user = User::where('nim', '00000000')->first()
>>> $user->password = Hash::make('password')
>>> $user->save()
```

---

## 🚨 EMERGENCY PROCEDURES

### If Nothing Works

#### 1. Check Environment
```bash
# Verify .env exists
cat .env | grep SESSION
cat .env | grep DB_

# Verify config cached
php artisan config:cache
```

#### 2. Check Permissions
```bash
# Storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Owner
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

#### 3. Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Restart web server
sudo systemctl restart nginx
# or
sudo systemctl restart apache2

# Restart Laravel
php artisan serve
```

#### 4. Check Dependencies
```bash
# Update composer
composer update

# Clear composer cache
composer clear-cache

# Reinstall
composer install
```

---

## 📞 GETTING HELP

### Information to Provide

When asking for help, provide:

1. **Error Message:**
```
[Exact error message from browser or log]
```

2. **Laravel Log:**
```bash
tail -n 50 storage/logs/laravel.log
```

3. **Route List:**
```bash
php artisan route:list --path=login
```

4. **Config:**
```bash
php artisan tinker
>>> config('session.driver')
>>> config('auth.defaults.guard')
```

5. **Steps to Reproduce:**
```
1. Go to /login
2. Enter NIM: 00000000
3. Enter Password: password
4. Click submit
5. [What happens?]
```

---

## 📚 REFERENCES

- [Laravel 11 Authentication](https://laravel.com/docs/11.x/authentication)
- [Laravel 11 Session](https://laravel.com/docs/11.x/session)
- [Laravel 11 Middleware](https://laravel.com/docs/11.x/middleware)

---

**Need More Help?**
- Check `AUTH_SYSTEM_GUIDE.md` for system overview
- Check `CHANGELOG.md` for recent changes
- Check Laravel documentation
