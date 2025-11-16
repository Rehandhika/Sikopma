# Design Document - Project Audit & Fixes

## Overview

Design ini menjelaskan pendekatan sistematis untuk memperbaiki masalah-masalah yang ditemukan dalam audit proyek SIKOPMA. Fokus utama adalah menghilangkan duplikasi kode, mengkonsolidasi sistem autentikasi, membersihkan struktur proyek, dan memastikan konfigurasi yang konsisten.

### Key Principles

1. **Safety First**: Semua perubahan harus dilakukan dengan backup dan rollback plan
2. **Incremental Changes**: Perubahan dilakukan secara bertahap untuk meminimalkan risiko
3. **Testing**: Setiap perubahan harus divalidasi dengan testing
4. **Documentation**: Semua perubahan harus didokumentasikan dengan jelas

## Architecture

### Current State Analysis

#### Authentication Systems (KONFLIK!)

Saat ini terdapat 3 implementasi autentikasi yang berbeda:

1. **LoginForm Livewire** (app/Livewire/Auth/LoginForm.php)
   - Status: ✅ AKTIF dan digunakan di routes/web.php
   - Fitur: Rate limiting ✅, Login history ✅, Status check ✅
   - Route: GET /login

2. **SimpleLoginController** (app/Http/Controllers/SimpleLoginController.php)
   - Status: ⚠️ TIDAK DIGUNAKAN (hanya logout yang digunakan)
   - Fitur: Rate limiting ✅, Login history ✅
   - Route: Tidak ada route login yang aktif, hanya POST /logout

3. **AuthController** (app/Http/Controllers/Auth/AuthController.php)
   - Status: ⚠️ TIDAK DIGUNAKAN (API endpoint)
   - Fitur: Rate limiting ✅, JSON response
   - Route: POST /auth/login, POST /auth/logout (di routes/auth.php)

**Masalah**: Duplikasi kode dan kebingungan tentang sistem mana yang aktif.

**Solusi**: Konsolidasi ke LoginForm Livewire, hapus yang lain.

#### Route Configuration

**routes/web.php**:
- Login: `Route::get('/login', \App\Livewire\Auth\LoginForm::class)` ✅
- Logout: `Route::post('/logout', [SimpleLoginController::class, 'logout'])` ⚠️

**routes/auth.php**:
- POST /auth/login (AuthController) - TIDAK DIGUNAKAN
- POST /auth/logout (AuthController) - TIDAK DIGUNAKAN

**Masalah**: Route logout masih menggunakan SimpleLoginController, routes/auth.php tidak digunakan.

**Solusi**: Pindahkan logout ke LoginForm Livewire atau buat LogoutController terpisah, hapus routes/auth.php.

#### Folder Structure

**app/Livewire/**:
- Report/ - ✅ Berisi 3 file (AttendanceReport, PenaltyReport, SalesReport)
- Reports/ - ❌ KOSONG

**Masalah**: Folder Reports kosong dan membingungkan.

**Solusi**: Hapus folder Reports yang kosong.

### Target State

#### Single Authentication System

```
Authentication Flow:
1. User mengakses /login
2. LoginForm Livewire component ditampilkan
3. User submit form
4. LoginForm::login() method dijalankan
5. Rate limiting check
6. Auth::attempt() dengan status check
7. Login history dicatat
8. Session regenerated
9. Redirect ke dashboard

Logout Flow:
1. User klik logout
2. POST /logout
3. Logout method dijalankan (di LoginForm atau controller terpisah)
4. Auth::logout()
5. Session invalidated
6. Redirect ke login
```

#### Clean Route Structure

```php
// routes/web.php
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\LoginForm::class)->name('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    // ... other routes
});
```

#### Clean Folder Structure

```
app/Livewire/
├── Auth/
│   └── LoginForm.php (single auth component)
├── Report/ (keep - has files)
│   ├── AttendanceReport.php
│   ├── PenaltyReport.php
│   └── SalesReport.php
└── (no Reports folder)
```

## Components and Interfaces

### 1. Authentication Component

**LoginForm Livewire Component** (KEEP & ENHANCE)

```php
class LoginForm extends Component
{
    // Properties
    public string $nim = '';
    public string $password = '';
    public bool $remember = false;
    
    // Methods
    public function login(): void
    {
        // 1. Validate input
        // 2. Check rate limiting
        // 3. Attempt authentication
        // 4. Log login history
        // 5. Regenerate session
        // 6. Redirect
    }
    
    protected function throttleKey(): string
    {
        // Generate unique key for rate limiting
    }
}
```

**Logout Handling** (NEW - Separate Controller)

```php
class LogoutController extends Controller
{
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
```

### 2. Files to Remove

#### Controllers
- `app/Http/Controllers/SimpleLoginController.php` - Tidak digunakan untuk login
- `app/Http/Controllers/Auth/AuthController.php` - API endpoint tidak digunakan

#### Routes
- `routes/auth.php` - Tidak digunakan

#### Folders
- `app/Livewire/Reports/` - Folder kosong

### 3. Files to Update

#### routes/web.php
```php
// BEFORE
Route::post('/logout', [SimpleLoginController::class, 'logout'])->name('logout');

// AFTER
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
```

#### bootstrap/app.php
```php
// BEFORE
Route::middleware('web')->group(base_path('routes/auth.php'));

// AFTER
// Remove this line (routes/auth.php will be deleted)
```

## Data Models

Tidak ada perubahan pada data models. Models yang ada sudah benar:

- **User**: Model untuk user authentication
- **LoginHistory**: Model untuk mencatat riwayat login

## Error Handling

### Backup Strategy

1. **Git Commit Before Changes**
   ```bash
   git add .
   git commit -m "Backup before audit fixes"
   git tag backup-before-audit-fixes
   ```

2. **File Backup**
   - Simpan copy dari semua file yang akan dihapus ke folder `backup/`
   - Format: `backup/YYYY-MM-DD/[original-path]`

### Rollback Plan

Jika terjadi masalah setelah perubahan:

1. **Rollback via Git**
   ```bash
   git reset --hard backup-before-audit-fixes
   ```

2. **Restore Individual Files**
   ```bash
   cp backup/YYYY-MM-DD/[file-path] [original-path]
   ```

### Error Scenarios

| Scenario | Detection | Rollback Action |
|----------|-----------|-----------------|
| Login tidak berfungsi | Manual test gagal | Restore auth files dari backup |
| Route tidak ditemukan | 404 error | Restore routes/web.php |
| Middleware error | 500 error | Restore bootstrap/app.php |
| Session error | Auth tidak persist | Restore session config |

## Testing Strategy

### Pre-Change Testing

1. **Baseline Test**
   ```bash
   php artisan test
   ```
   - Catat hasil test sebagai baseline
   - Pastikan semua test pass sebelum perubahan

2. **Manual Testing Checklist**
   - [ ] Login dengan credentials valid
   - [ ] Login dengan credentials invalid
   - [ ] Rate limiting (6+ failed attempts)
   - [ ] Logout functionality
   - [ ] Session persistence
   - [ ] Remember me functionality

### Post-Change Testing

1. **Automated Tests**
   ```bash
   php artisan test
   php artisan test --coverage
   ```
   - Pastikan semua test masih pass
   - Tidak ada penurunan coverage

2. **Manual Testing** (Same checklist as pre-change)

3. **Integration Testing**
   - Test full user journey: login → dashboard → logout
   - Test dengan berbagai browser
   - Test dengan berbagai role (admin, user, etc.)

### Test Cases to Add

```php
// tests/Feature/Auth/LoginTest.php
test('user can login with valid credentials')
test('user cannot login with invalid credentials')
test('user is rate limited after 5 failed attempts')
test('login history is recorded on success')
test('login history is recorded on failure')
test('user can logout successfully')
test('inactive user cannot login')
```

## Implementation Phases

### Phase 1: Preparation & Backup (CRITICAL)

**Duration**: 10 minutes

**Tasks**:
1. Create git commit and tag
2. Create backup folder structure
3. Copy files to backup
4. Document current state
5. Run baseline tests

**Success Criteria**:
- Git tag created
- All files backed up
- Baseline test results documented

### Phase 2: Remove Unused Authentication Code

**Duration**: 15 minutes

**Tasks**:
1. Remove SimpleLoginController.php
2. Remove AuthController.php
3. Remove routes/auth.php
4. Update bootstrap/app.php (remove auth.php reference)
5. Run tests

**Success Criteria**:
- Files removed successfully
- No references to removed files in codebase
- Tests still pass

### Phase 3: Create Logout Controller

**Duration**: 10 minutes

**Tasks**:
1. Create LogoutController.php
2. Implement logout method
3. Update routes/web.php
4. Test logout functionality

**Success Criteria**:
- LogoutController created
- Logout works correctly
- Session invalidated properly

### Phase 4: Clean Folder Structure

**Duration**: 5 minutes

**Tasks**:
1. Remove app/Livewire/Reports/ folder
2. Verify app/Livewire/Report/ still works
3. Update any references if needed

**Success Criteria**:
- Reports folder removed
- Report components still accessible
- No broken imports

### Phase 5: Validation & Testing

**Duration**: 20 minutes

**Tasks**:
1. Run full test suite
2. Manual testing checklist
3. Check for any errors in logs
4. Verify all routes work
5. Test authentication flow end-to-end

**Success Criteria**:
- All tests pass
- Manual tests pass
- No errors in logs
- Authentication works correctly

### Phase 6: Documentation Update

**Duration**: 15 minutes

**Tasks**:
1. Update README.md if needed
2. Update AUTH_SYSTEM_GUIDE.md
3. Update CHANGELOG.md
4. Document changes made
5. Create summary report

**Success Criteria**:
- Documentation updated
- Changes documented
- Summary report created

## Configuration Changes

### bootstrap/app.php

```php
// REMOVE THIS LINE:
Route::middleware('web')->group(base_path('routes/auth.php'));

// Keep everything else as is
```

### routes/web.php

```php
// UPDATE LOGOUT ROUTE:
// Before:
Route::post('/logout', [SimpleLoginController::class, 'logout'])->name('logout');

// After:
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Add import:
use App\Http\Controllers\LogoutController;
```

## Security Considerations

### Authentication Security

1. **Rate Limiting**: Tetap aktif di LoginForm Livewire
2. **Login History**: Tetap dicatat untuk audit
3. **Session Security**: Session regeneration tetap dilakukan
4. **CSRF Protection**: Tetap aktif via middleware
5. **Status Check**: Hanya user aktif yang bisa login

### Code Security

1. **No Sensitive Data in Backup**: Backup tidak menyimpan .env
2. **Git History**: Sensitive data tidak di-commit
3. **Access Control**: Backup folder tidak accessible via web

## Performance Considerations

### Impact Analysis

| Change | Performance Impact | Mitigation |
|--------|-------------------|------------|
| Remove unused controllers | ✅ Positive (less autoload) | None needed |
| Remove unused routes | ✅ Positive (less route cache) | None needed |
| Remove empty folder | ✅ Neutral | None needed |
| Add LogoutController | ✅ Neutral (same as before) | None needed |

### Optimization Opportunities

1. **Route Caching**
   ```bash
   php artisan route:cache
   ```
   - Setelah cleanup, route cache akan lebih efisien

2. **Autoload Optimization**
   ```bash
   composer dump-autoload -o
   ```
   - Menghapus unused classes dari autoload

## Monitoring & Validation

### Post-Deployment Checks

1. **Application Health**
   - Check /up endpoint
   - Monitor error logs
   - Check session storage

2. **Authentication Metrics**
   - Login success rate
   - Login failure rate
   - Rate limiting triggers
   - Session duration

3. **Performance Metrics**
   - Response time
   - Memory usage
   - Database queries

### Rollback Triggers

Rollback jika:
- Login success rate < 95%
- Error rate > 5%
- Session issues reported
- Critical functionality broken

## Success Criteria

### Technical Success

- [ ] All unused authentication code removed
- [ ] Single authentication system (LoginForm Livewire)
- [ ] All tests passing
- [ ] No errors in logs
- [ ] Clean folder structure
- [ ] Updated documentation

### Business Success

- [ ] Users can login successfully
- [ ] Users can logout successfully
- [ ] No disruption to existing users
- [ ] Improved code maintainability
- [ ] Reduced confusion for developers

## Risks & Mitigation

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Login breaks | Low | Critical | Backup & rollback plan |
| Session issues | Low | High | Test thoroughly before deploy |
| Route not found | Medium | Medium | Update all route references |
| Forgotten dependency | Low | Medium | Search codebase for references |
| User disruption | Low | High | Deploy during low traffic |

## Conclusion

Design ini memberikan pendekatan sistematis dan aman untuk membersihkan duplikasi kode dan konflik dalam proyek SIKOPMA. Dengan mengikuti fase-fase yang telah ditentukan dan melakukan testing yang menyeluruh, kita dapat meningkatkan kualitas kode tanpa mengganggu fungsionalitas yang ada.

Key benefits:
- ✅ Single source of truth untuk autentikasi
- ✅ Codebase lebih bersih dan mudah di-maintain
- ✅ Menghilangkan kebingungan developer
- ✅ Performa sedikit lebih baik (less autoload)
- ✅ Dokumentasi lebih jelas
