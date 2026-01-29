# SIKOPMA - Sistem Informasi Koperasi Mahasiswa

> **Comprehensive Cooperative Management System**  
> Built with Laravel 12, Livewire v3, Tailwind CSS v4, Alpine.js, and Vite

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-v3-pink.svg)](https://livewire.laravel.com)
[![Tailwind](https://img.shields.io/badge/Tailwind-v4-blue.svg)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-purple.svg)](https://php.net)

---

## 📋 Table of Contents

- [About SIKOPMA](#about-sikopma)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Quick Start](#quick-start)
- [Authentication System](#authentication-system)
- [Documentation](#documentation)
- [Project Structure](#project-structure)
- [Development](#development)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

---

## 📖 About SIKOPMA

SIKOPMA (Sistem Informasi Koperasi Mahasiswa) is a comprehensive web-based management system designed for student cooperatives. It provides complete operational management including attendance tracking, scheduling, cashier operations, inventory management, financial reporting, and administrative functions.

### Key Highlights

- **🚀 Modern Stack**: Laravel 12 + Livewire v3 for reactive UI without complex JavaScript
- **🎨 Beautiful UI**: Tailwind CSS v4 with responsive, mobile-first design
- **🔐 Robust Security**: Role-based access control (RBAC) with Spatie Laravel Permission
- **📊 Real-time Updates**: Live components with polling and event broadcasting
- **📈 Comprehensive Reporting**: Charts, exports, and analytics
- **🧪 Well Tested**: Feature and unit tests with Pest PHP
- **⚡ High Performance**: Optimized queries, caching, and asset bundling

---

## ✨ Features

### Core Modules (12)

1. **👥 Attendance Management**
   - Real-time check-in/out with geolocation
   - Attendance monitoring dashboard
   - Monthly reports and statistics

2. **📅 Schedule Management**
   - Visual calendar interface
   - Shift assignment and management
   - Conflict detection

3. **🔄 Swap Requests**
   - Schedule swap workflow
   - Approval system
   - Notification integration

4. **🏖️ Leave Management**
   - Leave request submission
   - Quota tracking
   - Multi-level approval

5. **💰 Penalty System**
   - Automated penalty calculation
   - Payment tracking
   - Financial reporting

6. **🛒 Point of Sale (POS)**
   - Fast checkout interface
   - Receipt printing
   - Member discounts

7. **📊 Reports & Analytics**
   - Sales, inventory, financial reports
   - Interactive charts
   - Export to Excel/PDF

8. **📦 Stock Management**
   - Real-time inventory tracking
   - Low stock alerts
   - Stock opname

9. **🛍️ Purchase Orders**
   - Supplier management
   - Purchase workflow
   - Receiving tracking

10. **🏷️ Product Management**
    - Product CRUD with images
    - Categories and barcodes
    - Pricing management

11. **⚙️ Settings & Configuration**
    - User management
    - Role & permission control
    - System settings

12. **🔔 Notifications**
    - Real-time notifications
    - Email digests
    - Broadcast messages

---

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 12
- **UI Components**: Livewire v3
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **Database**: MySQL 8.0+
- **Caching**: Redis (optional)

### Frontend
- **CSS Framework**: Tailwind CSS v4 (Optimized - 13.53 KB gzipped)
- **Design System**: 29+ reusable Blade components
- **JavaScript**: Alpine.js v3
- **Charts**: Chart.js
- **Date Picker**: Flatpickr
- **Select Enhancement**: Tom Select
- **File Upload**: FilePond
- **Drag & Drop**: SortableJS

### Development Tools
- **Build Tool**: Vite
- **Testing**: Pest PHP
- **Code Style**: Laravel Pint
- **Version Control**: Git

---

## 🚀 Quick Start

```bash
# 1. Clone & Install
git clone https://github.com/[your-org]/sikopma.git
cd sikopma
composer install
npm install

# 2. Setup
cp .env.example .env
php artisan key:generate

# 3. Database (edit .env first)
php artisan migrate --seed

# 4. Build & Run
npm run build
php artisan serve
```

**Login:** NIM `00000000` / Password `password`

**📘 Panduan Lengkap:** Lihat [PANDUAN.md](PANDUAN.md) untuk deploy & maintenance

---

## 🔐 Authentication System

SIKOPMA uses a secure, traditional Laravel authentication system with the following features:

### Current Implementation
- **Controller**: `SimpleLoginController` (Traditional Laravel)
- **Session**: Database driver with complete middleware stack
- **Security**: CSRF protection, rate limiting, status validation
- **Login History**: Tracks all login attempts with IP and user agent

### Key Features
- ✅ Rate limiting (5 attempts per minute)
- ✅ Login history tracking
- ✅ Status validation (only active users can login)
- ✅ Session security (regeneration after login)
- ✅ Auto-logout for inactive/suspended users

### Important Note - Laravel 11 Session Middleware

In Laravel 11, the web middleware group must be explicitly defined in `bootstrap/app.php`:

```php
$middleware->group('web', [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,  // ← CRITICAL
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);
```

### Documentation
- **[AUTH_SYSTEM_GUIDE.md](AUTH_SYSTEM_GUIDE.md)** - Complete authentication system documentation
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues and solutions
- **[CHANGELOG.md](CHANGELOG.md)** - Recent changes and updates

---

## 📚 Documentation

### 🎯 Panduan Utama

**[📘 PANDUAN.md](PANDUAN.md)** - Panduan lengkap deploy & maintenance (BACA INI DULU!)

### Development Guides
- **[MASTER_DEVELOPMENT_GUIDE.md](MASTER_DEVELOPMENT_GUIDE.md)** - Development reference
- **[AUTH_SYSTEM_GUIDE.md](AUTH_SYSTEM_GUIDE.md)** - Authentication system
- **[docs/](docs/)** - Detailed documentation

---

## 📁 Project Structure

```
sikopma/
├── app/
│   ├── Http/Controllers/      # Traditional controllers (if any)
│   ├── Livewire/              # Livewire components (main logic)
│   │   ├── Attendance/
│   │   ├── Schedule/
│   │   ├── Cashier/
│   │   └── ...
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic services
│   └── Helpers/               # Helper functions
│
├── database/
│   ├── migrations/            # Database migrations
│   ├── seeders/               # Database seeders
│   └── factories/             # Model factories
│
├── resources/
│   ├── views/
│   │   ├── livewire/         # Livewire component views
│   │   ├── components/       # Blade components
│   │   └── layouts/          # Page layouts
│   ├── css/
│   │   └── app.css           # Tailwind CSS v4
│   └── js/
│       ├── app.js            # Main JavaScript
│       └── [modules]/        # JS module configs
│
├── routes/
│   └── web.php               # Web routes
│
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
│
├── docs/                     # Documentation
└── public/                   # Public assets
```

---

## 💻 Development

### Available Commands

```bash
# Development
php artisan serve              # Start Laravel server (localhost:8000)
npm run dev                    # Start Vite dev server with HMR
php artisan tinker             # Interactive REPL

# Database
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Fresh migrations (drops all tables)
php artisan db:seed            # Run seeders

# Code Generation
php artisan make:livewire [Module]/[Component]  # Create Livewire component
php artisan make:model [Name] -m                # Model with migration
php artisan make:test [Name]Test                # Create test

# Optimization
php artisan optimize           # Optimize for production
php artisan optimize:clear     # Clear all caches
php artisan config:cache       # Cache config
php artisan route:cache        # Cache routes
php artisan view:cache         # Cache views

# Assets
npm run build                  # Build for production
npm run preview                # Preview production build
```

### Development Workflow

Follow the **8-phase workflow** in [Development Workflow](docs/DEVELOPMENT_WORKFLOW.md):

1. **Planning** (10 min) - Choose and plan feature
2. **Database** (15 min) - Migration and model
3. **Component** (30 min) - Livewire logic and view
4. **Routing** (10 min) - Routes and navigation
5. **Testing** (20 min) - Write tests
6. **Manual Testing** (15 min) - Browser testing
7. **Review** (10 min) - Code quality check
8. **Commit** (5 min) - Git commit and push

**Total**: ~2 hours per feature

---

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Attendance/AttendanceTest.php

# Run with coverage
php artisan test --coverage

# Run in parallel (faster)
php artisan test --parallel

# Run only failed tests
php artisan test --failed
```

### Test Structure

```php
// tests/Feature/[Module]/[Feature]Test.php
use function Pest\Laravel\{actingAs, get};
use function Pest\Livewire\livewire;

test('user can access page', function () {
    $user = User::factory()->create();
    
    actingAs($user)
        ->get(route('module.page'))
        ->assertOk();
});
```

See [Testing Guide](docs/TESTING_GUIDE.md) for comprehensive patterns.

---

## 🚀 Deployment

**Untuk deployment production, lihat [PANDUAN.md](PANDUAN.md)**

Panduan mencakup:
- Deploy manual (upload via FTP/SFTP)
- Setup tanpa npm di server
- Maintenance & troubleshooting
- Command-command penting

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Follow** our [Development Workflow](docs/DEVELOPMENT_WORKFLOW.md)
4. **Write** tests for new features
5. **Ensure** all tests pass: `php artisan test`
6. **Commit** with descriptive messages
7. **Push** to your fork
8. **Create** a Pull Request

### Code Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed
- Use Tailwind v4 compliant CSS
- Follow existing patterns and structure

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

---

## 📄 License

SIKOPMA is open-source software licensed under the [MIT License](LICENSE).

---

## 🙏 Acknowledgments

Built with amazing open-source technologies:

- [Laravel](https://laravel.com) - The PHP Framework
- [Livewire](https://livewire.laravel.com) - Full-stack framework for Laravel
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript framework
- [Spatie](https://spatie.be) - Laravel packages ecosystem

---

## 📞 Support

- **Documentation**: See [docs/](docs/) folder
- **Issues**: [GitHub Issues](https://github.com/[your-org]/sikopma/issues)
- **Discussions**: [GitHub Discussions](https://github.com/[your-org]/sikopma/discussions)

---

**Ready to build?** Start with the [Master Development Guide](MASTER_DEVELOPMENT_GUIDE.md)! 🚀
