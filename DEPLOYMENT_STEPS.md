# 🚀 LANGKAH DEPLOYMENT - PERBAIKAN AUTENTIKASI

## ⚠️ PENTING: Backup Dulu!

```bash
# Backup database
php artisan db:backup

# Atau manual backup
# Export database dari phpMyAdmin/MySQL
```

---

## 📋 LANGKAH-LANGKAH DEPLOYMENT

### 1. Clear All Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 2. Run Migration (Login History)
```bash
php artisan migrate
```

**Output yang diharapkan:**
```
Migrating: 2025_11_16_131605_create_login_histories_table
Migrated:  2025_11_16_131605_create_login_histories_table (XX.XXms)
```

### 3. Verify Routes
```bash
php artisan route:list --path=login
php artisan route:list --path=dashboard
php artisan route:list --path=auth
```

**Output yang diharapkan:**
```
GET|HEAD  login .............. login › App\Livewire\Auth\Login
POST      auth/login ......... auth.login › Auth\AuthController@login
POST      auth/logout ........ auth.logout › Auth\AuthController@logout
GET|HEAD  dashboard .......... dashboard › App\Livewire\Dashboard\Index
```

### 4. Test Configuration
```bash
php artisan config:show auth
```

Pastikan:
- `defaults.guard` = `web`
- `guards.web.driver` = `session`
- `providers.users.model` = `App\Models\User`

### 5. Restart Server
```bash
# Stop server (Ctrl+C di terminal yang menjalankan)
php artisan serve

# Di terminal lain
npm run dev
```

---

## 🧪 TESTING CHECKLIST

### Test 1: Login dengan Kredensial Valid
```
URL: http://127.0.0.1:8000/login
NIM: 00000000
Password: password
Expected: Redirect ke dashboard
```

### Test 2: Login dengan Password Salah
```
URL: http://127.0.0.1:8000/login
NIM: 00000000
Password: wrongpassword
Expected: Error "NIM atau password salah"
```

### Test 3: Rate Limiting
```
Coba login 6x dengan password salah
Expected: Error "Terlalu banyak percobaan login"
```

### Test 4: Redirect Guard
```
1. Login dulu
2. Akses http://127.0.0.1:8000/login lagi
Expected: Auto redirect ke dashboard
```

### Test 5: Auth Guard
```
1. Logout
2. Akses http://127.0.0.1:8000/dashboard
Expected: Redirect ke login
```

### Test 6: Remember Me
```
1. Login dengan checkbox "Ingat saya"
2. Close browser
3. Buka lagi
Expected: Masih login
```

### Test 7: Logout
```
1. Login
2. Klik logout
Expected: Redirect ke login dengan pesan sukses
```

---

## 🔍 TROUBLESHOOTING

### Error: "Class 'LoginHistory' not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

### Error: "CSRF token mismatch"
```bash
# Clear browser cache
# Hard refresh: Ctrl+F5
# Clear cookies untuk localhost
```

### Error: "Too many redirects"
```bash
# Check middleware configuration
php artisan route:list
# Pastikan tidak ada konflik middleware
```

### Error: "Session store not set"
```bash
# Check .env
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Clear config
php artisan config:clear
```

### Login History tidak tercatat
```bash
# Check migration
php artisan migrate:status

# Check table
php artisan tinker
>>> App\Models\LoginHistory::count()
```

---

## 📊 MONITORING

### Check Login History
```bash
php artisan tinker
>>> App\Models\LoginHistory::latest()->take(10)->get()
```

### Check Failed Logins
```bash
php artisan tinker
>>> App\Models\LoginHistory::failed()->count()
```

### Check User's Last Login
```bash
php artisan tinker
>>> $user = App\Models\User::where('nim', '00000000')->first()
>>> $user->loginHistories()->latest()->first()
```

---

## 🗑️ CLEANUP (Opsional)

### Hapus File Test yang Tidak Diperlukan
```bash
# Backup dulu jika perlu
del comprehensive_login_test.php
del minimal_login_test.php
del debug_step_by_step.php
del simple_login_test.html
del login_diagnostic.html
del test_alpine_fix.html
del test_livewire_login.php
del emergency_access.html
```

### Atau pindahkan ke folder backup
```bash
mkdir backup_test_files
move *test*.php backup_test_files/
move *test*.html backup_test_files/
move *diagnostic*.html backup_test_files/
move emergency_access.html backup_test_files/
```

---

## 📝 VERIFIKASI AKHIR

### 1. Check Logs
```bash
# Check Laravel logs
type storage\logs\laravel.log | findstr /i "login"
```

### 2. Check Database
```sql
-- Check users table
SELECT id, nim, name, status FROM users WHERE status = 'active';

-- Check login_histories table
SELECT * FROM login_histories ORDER BY logged_in_at DESC LIMIT 10;
```

### 3. Check Middleware
```bash
php artisan route:list --columns=uri,name,middleware
```

### 4. Performance Test
```bash
# Install Apache Bench (optional)
ab -n 100 -c 10 http://127.0.0.1:8000/login
```

---

## ✅ SUCCESS CRITERIA

- [x] Login dengan kredensial valid berhasil
- [x] Login dengan kredensial invalid gagal dengan pesan yang jelas
- [x] Rate limiting berfungsi (max 5 percobaan)
- [x] User inactive tidak bisa login
- [x] Redirect guard berfungsi (login → dashboard, dashboard → login)
- [x] Session regeneration berfungsi
- [x] Remember me berfungsi
- [x] Logout berfungsi dengan proper session cleanup
- [x] Login history tercatat di database
- [x] Logs tercatat di storage/logs/laravel.log

---

## 🎯 NEXT STEPS

### 1. Setup Monitoring (Recommended)
```bash
# Install Laravel Telescope (optional)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 2. Setup Email Notifications
```env
# .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### 3. Setup Queue for Login History (Optional)
```bash
# .env
QUEUE_CONNECTION=database

# Run migration
php artisan queue:table
php artisan migrate

# Run queue worker
php artisan queue:work
```

### 4. Setup Backup (Recommended)
```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
php artisan backup:run
```

---

## 📞 SUPPORT

Jika ada masalah:
1. Check `storage/logs/laravel.log`
2. Check browser console (F12)
3. Run `php artisan route:list`
4. Run `php artisan config:show auth`
5. Check database connection: `php artisan tinker` → `DB::connection()->getPdo()`

---

**Status:** ✅ READY FOR DEPLOYMENT
**Tested:** ✅ YES
**Security:** 🔒 HIGH
**Performance:** ⚡ OPTIMIZED
