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
- **CSS Framework**: Tailwind CSS v4
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

### Prerequisites

- PHP 8.3 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+
- Git

### Installation

```bash
# Clone repository
git clone https://github.com/[your-org]/sikopma.git
cd sikopma

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_DATABASE=sikopma
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve

# In another terminal, start Vite
npm run dev
```

### Default Credentials

After seeding, you can login with:

- **Super Admin**: NIM `00000000` / Password `password`
- **Test User**: NIM `12345678` / Password `password123`

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

### 🎯 Start Here

**[📘 Master Development Guide](MASTER_DEVELOPMENT_GUIDE.md)**  
Complete reference for building SIKOPMA features with consistency and quality.

### 🔐 Authentication & Security
- **[AUTH_SYSTEM_GUIDE.md](AUTH_SYSTEM_GUIDE.md)** - Authentication system overview
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Debugging and common issues
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and changes

### 🎨 UI/UX Documentation
- **[UI_IMPROVEMENTS.md](UI_IMPROVEMENTS.md)** - UI/UX improvements and design system
- **[FINAL_UI_UPDATE_SUMMARY.md](FINAL_UI_UPDATE_SUMMARY.md)** - Latest UI updates summary

### Essential Guides

| Document | Purpose | Read Time |
|----------|---------|-----------|
| **[🚀 Quick Start](docs/QUICK_START.md)** | Get building in 5 minutes | 10 min |
| **[📋 Feature Backlog](FEATURE_BACKLOG.md)** | All features documented | Reference |
| **[💻 Implementation Templates](docs/IMPLEMENTATION_TEMPLATES.md)** | Copy-paste ready code | Reference |
| **[🔄 Development Workflow](docs/DEVELOPMENT_WORKFLOW.md)** | Step-by-step process | 20 min |
| **[🧪 Testing Guide](docs/TESTING_GUIDE.md)** | Testing patterns | 15 min |
| **[🛠️ Troubleshooting](docs/TROUBLESHOOTING.md)** | Common issues & solutions | As needed |

### For AI Assistants

Use the **Master Prompt** in [MASTER_DEVELOPMENT_GUIDE.md](MASTER_DEVELOPMENT_GUIDE.md) to ensure AI-generated code follows SIKOPMA standards.

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

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm run build`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Set up proper file permissions
- [ ] Configure queue workers (if using queues)
- [ ] Set up SSL certificate
- [ ] Configure backups

### Server Requirements

- PHP 8.3+
- MySQL 8.0+
- Nginx or Apache
- Composer
- Node.js (for builds)
- Supervisor (for queue workers)
- Redis (optional, for caching)

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
