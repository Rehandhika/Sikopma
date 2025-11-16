# 🚀 SIKOPMA - Quick Reference

**Last Updated:** 16 November 2025

---

## 🔐 Login

**URL:** `http://127.0.0.1:8000/login`

**Test Credentials:**
- NIM: `00000000`
- Password: `password`

---

## 🧭 Navigation Menu

### Main Features
| Icon | Menu | Route | Component |
|------|------|-------|-----------|
| 🏠 | Dashboard | `/dashboard` | Dashboard\Index |
| ✅ | Absensi | `/attendance` | Attendance\Index |
| 📅 | Jadwal | `/schedule` | Schedule\Index |
| 💰 | Kasir/POS | `/cashier/pos` | Cashier\Pos |
| 📦 | Produk | `/products` | Product\Index |
| 📊 | Stok | `/stock` | Stock\Index |
| 📝 | Izin/Cuti | `/leave` | Leave\Index |
| 🔄 | Tukar Jadwal | `/swap` | Swap\Index |
| ⚠️ | Sanksi | `/penalties` | Penalty\Index |
| 📈 | Laporan | `/reports/attendance` | Report\AttendanceReport |
| 📊 | Analytics | `/analytics/dashboard` | Analytics\BIDashboard |

### Management
| Icon | Menu | Route | Component |
|------|------|-------|-----------|
| 👥 | Manajemen User | `/users` | User\Index |
| 🛡️ | Role & Permission | `/roles` | Role\Index |
| ⚙️ | Pengaturan | `/settings/general` | Settings\General |
| 👤 | Profil Saya | `/profile/edit` | Profile\Edit |

---

## 🛣️ All Routes

### Attendance
```
GET /attendance/check-in-out  → Check In/Out
GET /attendance               → Daftar Absensi
GET /attendance/history       → Riwayat Absensi
```

### Schedule
```
GET /schedule                 → Kalender Jadwal
GET /schedule/my-schedule     → Jadwal Saya
GET /schedule/availability    → Ketersediaan
GET /schedule/calendar        → Kalender
GET /schedule/generator       → Generator Jadwal
```

### Cashier
```
GET /cashier/pos             → Point of Sale
GET /cashier/sales           → Daftar Penjualan
```

### Products
```
GET /products                → Daftar Produk
GET /products/list           → List Produk
```

### Stock
```
GET /stock                   → Manajemen Stok
GET /stock/adjustment        → Penyesuaian Stok
```

### Leave Requests
```
GET /leave                   → Daftar Izin
GET /leave/my-requests       → Pengajuan Saya
GET /leave/create            → Ajukan Izin Baru
GET /leave/approvals         → Persetujuan Izin
```

### Swap Requests
```
GET /swap                    → Daftar Tukar Jadwal
GET /swap/my-requests        → Pengajuan Saya
GET /swap/create             → Ajukan Tukar Jadwal
GET /swap/approvals          → Persetujuan Tukar
```

### Penalties
```
GET /penalties               → Daftar Sanksi
GET /penalties/my-penalties  → Sanksi Saya
GET /penalties/manage        → Kelola Sanksi
```

### Reports
```
GET /reports/attendance      → Laporan Absensi
GET /reports/sales           → Laporan Penjualan
GET /reports/penalties       → Laporan Sanksi
```

---

## 💻 Development Commands

### Start Development
```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite
npm run dev
```

### Clear Cache
```bash
php artisan optimize:clear
```

### Check Routes
```bash
php artisan route:list
```

### Run Tests
```bash
php artisan test
```

### Database
```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fresh migration with seed
php artisan migrate:fresh --seed
```

---

## 🎨 Tailwind CSS Classes

### Colors
```
Primary:   bg-indigo-600, text-indigo-700
Secondary: bg-purple-600, text-purple-700
Success:   bg-green-50, text-green-700
Error:     bg-red-50, text-red-700
Warning:   bg-yellow-50, text-yellow-700
```

### Spacing
```
Padding:  p-2, p-3, p-4, p-6, p-8
Margin:   m-2, m-3, m-4
Gap:      gap-1, gap-2, gap-4
```

### Layout
```
Flex:     flex, flex-col, items-center, justify-between
Grid:     grid, grid-cols-2, gap-4
Width:    w-full, w-1/2, w-64
Height:   h-full, h-screen, h-64
```

---

## 🔧 Common Tasks

### Add New Menu Item
```php
// resources/views/components/navigation.blade.php
<a href="{{ route('your.route') }}" 
   class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('your.route') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">
    <svg class="w-5 h-5 mr-3"><!-- icon --></svg>
    Your Menu
</a>
```

### Add New Route
```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/your-route', YourComponent::class)->name('your.route');
});
```

### Create Livewire Component
```bash
php artisan make:livewire YourComponent
```

---

## 🐛 Troubleshooting

### Login Issues
```bash
# Clear cache
php artisan optimize:clear

# Check session
php artisan tinker
>>> session()->all()
```

### Route Not Found
```bash
# Clear route cache
php artisan route:clear

# List all routes
php artisan route:list
```

### Assets Not Loading
```bash
# Rebuild assets
npm run build

# Or for development
npm run dev
```

### Database Issues
```bash
# Check connection
php artisan tinker
>>> DB::connection()->getPdo()

# Run migrations
php artisan migrate
```

---

## 📝 File Locations

### Views
```
Login:      resources/views/auth/simple-login.blade.php
Layout:     resources/views/layouts/app.blade.php
Navigation: resources/views/components/navigation.blade.php
```

### Controllers
```
Login:      app/Http/Controllers/SimpleLoginController.php
```

### Livewire Components
```
Dashboard:  app/Livewire/Dashboard/Index.php
Attendance: app/Livewire/Attendance/
Schedule:   app/Livewire/Schedule/
Cashier:    app/Livewire/Cashier/
Products:   app/Livewire/Product/
```

### Routes
```
Web:        routes/web.php
API:        routes/api.php
Auth:       routes/auth.php
```

### Config
```
App:        bootstrap/app.php
Auth:       config/auth.php
Session:    config/session.php
Database:   config/database.php
```

---

## 🔐 Security

### Rate Limiting
- **Login:** 5 attempts per minute
- **Lockout:** 60 seconds

### Session
- **Driver:** Database
- **Lifetime:** 120 minutes
- **Regenerate:** After login

### Middleware
- **Auth:** Protect routes
- **Guest:** Redirect if authenticated
- **Active:** Check user status
- **CSRF:** Token validation

---

## 📊 Database

### Main Tables
```
users                  - User accounts
roles                  - User roles
permissions            - Permissions
attendances            - Attendance records
schedules              - Schedule assignments
products               - Product catalog
stocks                 - Stock levels
sales                  - Sales transactions
leave_requests         - Leave requests
swap_requests          - Swap requests
penalties              - Penalty records
login_histories        - Login attempts
```

---

## 🎯 Quick Links

### Documentation
- [README.md](README.md) - Main documentation
- [AUTH_SYSTEM_GUIDE.md](AUTH_SYSTEM_GUIDE.md) - Authentication guide
- [UI_IMPROVEMENTS.md](UI_IMPROVEMENTS.md) - UI/UX documentation
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common issues
- [CHANGELOG.md](CHANGELOG.md) - Version history

### External
- [Laravel Docs](https://laravel.com/docs/11.x)
- [Livewire Docs](https://livewire.laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev)

---

## 💡 Tips

### Performance
- Use `php artisan optimize` for production
- Enable Redis for caching
- Use queue for heavy tasks
- Optimize images before upload

### Development
- Use `php artisan tinker` for testing
- Check logs: `storage/logs/laravel.log`
- Use browser DevTools (F12)
- Test on multiple browsers

### Code Quality
- Follow PSR-12 coding standards
- Use Laravel Pint: `./vendor/bin/pint`
- Write tests for critical features
- Document complex logic

---

**Need Help?**
- Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- Check Laravel logs
- Check browser console
- Ask the team

---

**Last Updated:** 16 November 2025  
**Version:** 2.0.0
