# Quick Start Guide

## 🚀 Setup Development Environment

### Prerequisites
- **PHP 8.2+** dengan ekstensi: `pdo_mysql`, `mbstring`, `xml`, `bcmath`, `curl`, `zip`
- **Composer 2.0+**
- **Node.js 18+** dan **npm**
- **MySQL 8.0+** atau **MariaDB 10.3+**
- **Git**

### 1. Clone Repository
```bash
git clone https://github.com/username/kopma.git
cd kopma
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kopma_dev
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Database Setup
```bash
# Create database (gunakan MySQL CLI atau phpMyAdmin)
CREATE DATABASE kopma_dev;

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

### 6. Build Assets
```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Start Development Server
```bash
# Start Laravel development server
php artisan serve

# Start Vite development server (separate terminal)
npm run dev
```

Aplikasi akan tersedia di `http://localhost:8000`

## 🔑 Default Login

### Super Admin
- **Email**: `admin@kopma.test`
- **Password**: `password`

### Pengguna Test
- **Email**: `user@kopma.test`
- **Password**: `password`

## 🛠️ Development Tools

### Code Quality
```bash
# Format code dengan Laravel Pint
composer lint:fix

# Cek code style
composer lint
```

### Testing
```bash
# Run semua tests
php artisan test

# Run tests dengan coverage
php artisan test --coverage

# Run specific test
php artisan test --filter ScheduleTest
```

### Database
```bash
# Fresh migration dengan seeding
php artisan migrate:fresh --seed

# Buat migration baru
php artisan make:migration create_table_name

# Buat seeder baru
php artisan make:seeder TableNameSeeder
```

### Livewire Development
```bash
# Buat Livewire component baru
php artisan make:livewire ComponentName

# Buat component dengan view terpisah
php artisan make:livewire pages/component-name --test
```

## 📁 Project Structure

```
kopma/
├── app/
│   ├── Http/Controllers/     # HTTP Controllers
│   ├── Livewire/            # Livewire Components
│   ├── Models/              # Eloquent Models
│   ├── Policies/            # Authorization Policies
│   └── Services/            # Business Logic
├── database/
│   ├── migrations/          # Database Migrations
│   ├── seeders/            # Database Seeders
│   └── factories/          # Model Factories
├── resources/
│   ├── views/              # Blade Templates
│   ├── js/                 # JavaScript Files
│   └── css/                # CSS Files
├── routes/
│   ├── web.php             # Web Routes
│   └── api.php             # API Routes
├── tests/
│   ├── Feature/            # Feature Tests
│   └── Unit/               # Unit Tests
└── docs/                   # Documentation
```

## 🎯 Common Development Tasks

### Membuat Model Baru
```bash
# Buat model dengan migration dan factory
php artisan make:model ModelName -m -f

# Buat model dengan policy
php artisan make:model ModelName -p
```

### Membuat Controller
```bash
# Buat controller dengan resource methods
php artisan make:controller ModelNameController --resource

# Buat controller dengan form requests
php artisan make:controller ModelNameController --api
```

### Membuat Form Request
```bash
php artisan make:request StoreModelRequest
php artisan make:request UpdateModelRequest
```

### Membuat Test
```bash
# Feature test
php artisan make:test ModelTest

# Unit test
php artisan make:test ModelUnitTest --unit
```

## 🔧 Configuration

### Environment Variables
Key environment variables untuk development:
```env
APP_NAME=SIKOPMA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kopma_dev
DB_USERNAME=root
DB_PASSWORD=

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

### Cache Management
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Re-generate optimized files
php artisan optimize:clear
```

## 🐛 Debugging

### Laravel Telescope (Development)
```bash
# Install Telescope
composer require laravel/telescope --dev

# Publish assets
php artisan telescope:install

# Run migration
php artisan migrate

# Publish configuration
php artisan vendor:publish --tag=telescope-config
```

### Debug Bar
```bash
# Install Laravel Debug Bar
composer require barryvdh/laravel-debugbar --dev

# Publish configuration
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

### Logging
```bash
# Tampilkan log real-time
tail -f storage/logs/laravel.log

# Log custom message
Log::info('Custom message', ['data' => $data]);
```

## 🚀 Deployment Preparation

### Build untuk Production
```bash
# Install dependencies tanpa dev
composer install --no-dev --optimize-autoloader

# Build assets
npm ci && npm run build

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### Environment Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=kopma_prod
DB_USERNAME=prod_user
DB_PASSWORD=secure_password

LOG_CHANNEL=stack
LOG_LEVEL=warning
```

## 📚 Useful Commands

### Artisan Commands
```bash
# List semua routes
php artisan route:list

# List semua middleware
php artisan route:middleware

# Tampilkan schedule list
php artisan schedule:list

# Buat storage link
php artisan storage:link

# Clear compiled files
php artisan clear-compiled
```

### Composer Commands
```bash
# Update dependencies
composer update

# Install specific package
composer require package/name

# Remove package
composer remove package/name

# Show installed packages
composer show
```

### NPM Commands
```bash
# Watch for changes
npm run watch

# Build untuk production
npm run build

# Optimize assets
npm run prod
```

## 🔒 Security Notes

### Jangan Lupa
- Jangan commit `.env` file ke version control
- Gunakan password yang kuat untuk production
- Enable HTTPS di production
- Set proper file permissions
- Backup database secara rutin

### Best Practices
- Validasi semua input user
- Gunakan HTTPS untuk semua requests
- Implement rate limiting
- Log security events
- Update dependencies secara rutin

## 🤝 Contributing

1. Fork repository
2. Buat branch baru: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -am 'Add new feature'`
4. Push branch: `git push origin feature/new-feature`
5. Submit pull request

## 📞 Getting Help

### Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [TailwindCSS Documentation](https://tailwindcss.com/docs)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)

### Troubleshooting
- Check logs: `storage/logs/laravel.log`
- Clear cache: `php artisan optimize:clear`
- Check permissions: `chmod -R 775 storage bootstrap/cache`
- Verify database connection: `php artisan tinker`

---

*Untuk panduan pengembangan lengkap, lihat [MASTER_DEVELOPMENT_GUIDE.md](../MASTER_DEVELOPMENT_GUIDE.md)*

*Last updated: 2025-11-14*
