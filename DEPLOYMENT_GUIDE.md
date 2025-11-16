# SIKOPMA - Deployment & Testing Guide

## 🎯 System Overview
**SIKOPMA** (Sistem Informasi Koperasi Mahasiswa) is a comprehensive cooperative attendance and management system built with Laravel 12, Livewire 3, and Tailwind CSS v4.

## ✅ System Status: FULLY OPERATIONAL

### What Has Been Fixed:
1. ✅ **Frontend Assets**: Vite + Tailwind CSS v4 configured and built successfully
2. ✅ **Database**: All migrations run, relationships established
3. ✅ **Authentication**: Role-based access control with Spatie Permissions
4. ✅ **Livewire Components**: All 28 components created and functional
5. ✅ **Routing**: Complete route structure with middleware protection
6. ✅ **Styling**: Tailwind CSS v4 with custom components and utilities

---

## 🚀 Quick Start

### 1. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env (SQLite is default)
DB_CONNECTION=sqlite
```

### 2. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed database with test data
php artisan db:seed
```

### 3. Build Frontend Assets
```bash
# Install dependencies (if not already done)
npm install

# Build for production
npm run build

# OR run development server with hot reload
npm run dev
```

### 4. Start Application
```bash
# Start Laravel development server
php artisan serve

# Application will be available at: http://localhost:8000
```

---

## 👥 Test Accounts

All test accounts use password: **`password`**

### Administrative Accounts:
| Role | NIM | Name | Email |
|------|-----|------|-------|
| Super Admin | 00000000 | Super Administrator | admin@sikopma.test |
| Ketua | 11111111 | Ketua KOPMA | ketua@sikopma.test |
| Wakil Ketua | 22222222 | Wakil Ketua KOPMA | wakil@sikopma.test |

### BPH (Board Members):
| NIM | Name | Email |
|-----|------|-------|
| 33333333 | BPH 1 | bph1@sikopma.test |
| 44444444 | BPH 2 | bph2@sikopma.test |
| 55555555 | BPH 3 | bph3@sikopma.test |

### Regular Members:
| NIM | Name | Email |
|-----|------|-------|
| 66666666 | Anggota 1 | anggota1@sikopma.test |
| 77777777 | Anggota 2 | anggota2@sikopma.test |
| 88888888 | Anggota 3 | anggota3@sikopma.test |
| 99999999 | Anggota 4 | anggota4@sikopma.test |
| 10101010 | Anggota 5 | anggota5@sikopma.test |

---

## 🎨 Frontend Architecture

### Tailwind CSS v4 Configuration
- **CSS Framework**: Tailwind CSS v4.1.16
- **Build Tool**: Vite 7.1.12
- **Plugin**: @tailwindcss/vite for seamless integration

### Custom Components Available:
- **Buttons**: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`, etc.
- **Cards**: `.card`, `.card-header`, `.card-body`, `.card-footer`
- **Badges**: `.badge`, `.badge-primary`, `.badge-danger`, etc.
- **Alerts**: `.alert`, `.alert-success`, `.alert-danger`, etc.
- **Tables**: `.table`, `.table-hover`
- **Forms**: Pre-styled form elements with focus states

### JavaScript Libraries:
- **Alpine.js**: v3.15.1 (reactive components)
- **Chart.js**: v4.5.1 (data visualization)
- **Flatpickr**: v4.6.13 (date picker)
- **Tom Select**: v2.4.3 (enhanced select)
- **FilePond**: v4.32.10 (file uploads)
- **Sortable.js**: v1.15.6 (drag & drop)

---

## 📁 Project Structure

```
SIKOPMA/
├── app/
│   ├── Livewire/           # 28 Livewire components
│   │   ├── Attendance/     # Check-in/out system
│   │   ├── Auth/           # Login component
│   │   ├── Cashier/        # POS system
│   │   ├── Dashboard/      # Main dashboard
│   │   ├── Kopma/          # Kopma homepage
│   │   ├── Leave/          # Leave management
│   │   ├── Penalty/        # Penalty tracking
│   │   ├── Product/        # Product management
│   │   ├── Profile/        # User profile
│   │   ├── Purchase/       # Purchase orders
│   │   ├── Report/         # Reporting system
│   │   ├── Schedule/       # Schedule management
│   │   ├── Settings/       # System settings
│   │   ├── Stock/          # Inventory management
│   │   ├── Swap/           # Shift swap requests
│   │   └── User/           # User management
│   └── Models/             # Eloquent models
├── database/
│   ├── migrations/         # 28 database migrations
│   └── seeders/            # Test data seeders
├── resources/
│   ├── css/
│   │   └── app.css         # Tailwind CSS v4 with custom styles
│   ├── js/
│   │   ├── app.js          # Alpine.js setup
│   │   ├── charts.js       # Chart.js configuration
│   │   ├── flatpickr-config.js
│   │   ├── tom-select-config.js
│   │   ├── sortable-config.js
│   │   ├── filepond-config.js
│   │   └── utils.js
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php    # Main layout
│       │   └── guest.blade.php  # Auth layout
│       ├── components/
│       │   └── navigation.blade.php  # Sidebar navigation
│       └── livewire/       # 28 Blade templates
└── routes/
    └── web.php             # All application routes
```

---

## 🔐 Role-Based Access Control

### Roles & Permissions:
1. **Super Admin**: Full system access
2. **Ketua**: Management access, settings
3. **Wakil Ketua**: Management access
4. **BPH**: Board member access, approvals
5. **Anggota**: Regular member access

### Protected Routes:
- User Management: `super-admin|ketua|wakil-ketua|bph`
- Settings: `super-admin|ketua`
- Reports: `super-admin|ketua|wakil-ketua|bph`
- Cashier: `super-admin|ketua|wakil-ketua|bph`

---

## 🎯 Core Features

### 1. Attendance System
- **Check-in/Check-out**: Real-time attendance tracking
- **Schedule Management**: Automated schedule generation
- **Availability Input**: Members input their availability
- **Calendar View**: Visual schedule overview

### 2. Shift Management
- **Swap Requests**: Members can request shift swaps
- **Leave Requests**: Submit and approve leave requests
- **Approval Workflow**: BPH approval system

### 3. Penalty System
- **Penalty Types**: Configurable penalty categories
- **Point System**: Track penalty points
- **History Tracking**: Complete penalty history
- **Appeal Process**: Members can appeal penalties

### 4. Cashier/POS System
- **Sales Transactions**: Complete POS interface
- **Product Management**: Inventory tracking
- **Purchase Orders**: Supplier management
- **Stock Adjustments**: Inventory adjustments

### 5. Reporting System
- **Attendance Reports**: Detailed attendance analytics
- **Sales Reports**: Revenue and sales tracking
- **Penalty Reports**: Penalty statistics
- **Export Functionality**: PDF/Excel exports

### 6. Notification System
- **Real-time Notifications**: Instant updates
- **Read/Unread Status**: Track notification status
- **Multiple Types**: Info, warning, success, error

---

## 🛠️ Development Commands

### Frontend Development:
```bash
# Watch for changes (hot reload)
npm run dev

# Build for production
npm run build
```

### Backend Development:
```bash
# Start development server
php artisan serve

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create new Livewire component
php artisan make:livewire ComponentName
```

### Testing:
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter TestName
```

---

## 🐛 Troubleshooting

### CSS Not Loading:
```bash
# Rebuild assets
npm run build

# Clear Laravel cache
php artisan view:clear
php artisan cache:clear
```

### Database Issues:
```bash
# Reset database
php artisan migrate:fresh --seed
```

### Permission Issues:
```bash
# Clear permission cache
php artisan permission:cache-reset
```

### Livewire Issues:
```bash
# Clear Livewire temp files
php artisan livewire:delete-uploads
```

---

## 📊 Database Schema

### Core Tables:
- **users**: User accounts with roles
- **attendances**: Check-in/out records
- **schedules**: Schedule templates
- **schedule_assignments**: User schedule assignments
- **penalties**: Penalty records
- **penalty_types**: Penalty categories
- **sales**: Sales transactions
- **products**: Product catalog
- **notifications**: User notifications
- **swap_requests**: Shift swap requests
- **leave_requests**: Leave applications

---

## 🔄 Workflow Examples

### 1. Member Check-in Flow:
1. Member logs in
2. Navigates to "Absensi"
3. Clicks "Check In"
4. System records timestamp and location
5. Dashboard updates with attendance status

### 2. Schedule Generation Flow:
1. Admin navigates to "Generate Jadwal"
2. Selects date range
3. System analyzes member availability
4. Generates optimal schedule
5. Assigns shifts to members
6. Sends notifications

### 3. Shift Swap Flow:
1. Member requests shift swap
2. Target member receives notification
3. Target member accepts/rejects
4. BPH approves request
5. Schedule updates automatically
6. Both members notified

---

## 🎨 UI/UX Features

### Responsive Design:
- Mobile-first approach
- Breakpoints: sm, md, lg, xl, 2xl
- Touch-friendly interfaces

### Accessibility:
- ARIA labels
- Keyboard navigation
- Screen reader support
- High contrast mode

### User Experience:
- Loading states
- Error handling
- Success feedback
- Smooth transitions

---

## 📝 API Endpoints (Future)

Currently, the system uses Livewire for real-time updates. Future API endpoints can be added for:
- Mobile app integration
- Third-party integrations
- Webhook support

---

## 🔒 Security Features

1. **Authentication**: Laravel Sanctum ready
2. **Authorization**: Spatie Permission package
3. **CSRF Protection**: Enabled by default
4. **XSS Prevention**: Blade escaping
5. **SQL Injection**: Eloquent ORM protection
6. **Password Hashing**: Bcrypt with 12 rounds

---

## 📈 Performance Optimization

### Implemented:
- Vite code splitting
- Lazy loading components
- Database indexing
- Query optimization
- Asset minification

### Recommendations:
- Enable Redis for caching
- Use queue workers for jobs
- Implement CDN for assets
- Enable OPcache in production

---

## 🚀 Production Deployment

### Prerequisites:
- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MySQL/PostgreSQL (recommended for production)

### Deployment Steps:
```bash
# 1. Clone repository
git clone <repository-url>
cd Kopma

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
php artisan migrate --force
php artisan db:seed --force

# 5. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Set permissions
chmod -R 755 storage bootstrap/cache
```

### Web Server Configuration:
- Point document root to `/public`
- Enable URL rewriting
- Set appropriate PHP memory limit (256M+)

---

## 📞 Support & Documentation

### Resources:
- Laravel Documentation: https://laravel.com/docs
- Livewire Documentation: https://livewire.laravel.com
- Tailwind CSS v4: https://tailwindcss.com/docs
- Alpine.js: https://alpinejs.dev

### Common Issues:
See `TROUBLESHOOTING.md` for detailed solutions

---

## 🎉 Success Checklist

- [x] Frontend assets built successfully
- [x] Database migrations completed
- [x] Test users created
- [x] All Livewire components functional
- [x] Routing configured with middleware
- [x] Role-based access control working
- [x] Responsive design implemented
- [x] Navigation menu functional
- [x] Authentication flow working

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

**Last Updated**: November 4, 2025
**Version**: 1.0.0
**Status**: Production Ready ✅
