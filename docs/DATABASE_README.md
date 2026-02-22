# 🗄️ DEPLOY SIKOPMA - Database Structure

> **Professional, Scalable, and Well-Documented Database Architecture**
> 
> This document provides a comprehensive overview of the database structure, migration organization, and best practices.

---

## 📋 Quick Start

### Running Migrations
```bash
# Run all migrations
php artisan migrate

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Fresh with seeders
php artisan migrate:fresh --seed

# Rollback last batch
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

### Seeding Data
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder

# Seed after fresh migration
php artisan migrate:fresh --seed
```

---

## 📁 Directory Structure

```
database/
├── migrations/                    # Database migrations
│   ├── 0001_01_01_*.php          # Laravel system tables
│   ├── 2025_11_03_*.php          # Core business tables
│   ├── 2025_11_04_*.php          # Enhancements & indexes
│   ├── 2025_11_16_*.php          # Schedule module
│   ├── 2025_11_23_*.php          # Additional features
│   ├── 2025_12_23_*.php          # POS & Content
│   ├── 2026_01_*.php             # SHU system & variants
│   └── 2026_02_*.php             # Recent enhancements
│
├── seeders/                       # Database seeders
│   ├── DatabaseSeeder.php        # Main seeder orchestrator
│   ├── RolePermissionSeeder.php  # Roles & permissions
│   ├── UserSeeder.php            # Initial users
│   ├── SystemSettingSeeder.php   # System configurations
│   ├── ScheduleConfigurationSeeder.php
│   ├── PenaltyTypeSeeder.php
│   ├── PaymentConfigurationSeeder.php
│   ├── StoreSettingSeeder.php
│   └── KatalogSeeder.php         # Product catalog
│
├── factories/                     # Model factories for testing
│   ├── UserFactory.php
│   ├── ProductFactory.php
│   ├── ScheduleFactory.php
│   ├── AttendanceFactory.php
│   └── ... (14 total)
│
├── traits/                        # Database traits
│   └── MigrationHelper.php       # Common migration helpers
│
└── Migrations/                    # Migration utilities
    └── MigrationManifest.php     # Migration registry
```

---

## 🏗️ Architecture Overview

### Module-Based Organization

The database is organized into **10 business modules**:

| Module | Tables | Description |
|--------|--------|-------------|
| **System** | 4 | Users, cache, jobs, sessions |
| **Authorization** | 5 | Spatie permissions (roles, permissions) |
| **Audit** | 3 | Audit logs, activity logs, login history |
| **Schedule** | 8 | Schedules, assignments, availability |
| **Attendance** | 4 | Attendance tracking, penalties |
| **Leave** | 4 | Leave requests, schedule changes |
| **POS** | 7 | Products, variants, sales, SHU points |
| **Inventory** | 3 | Purchases, stock adjustments |
| **Content** | 3 | Banners, news, notifications |
| **Settings** | 4 | System settings, store settings |

### Entity Relationship Overview

```
┌─────────────┐
│   Users     │
└──────┬──────┘
       │
       ├──────────────┬──────────────┬──────────────┐
       │              │              │              │
┌──────▼──────┐ ┌─────▼──────┐ ┌────▼──────┐ ┌────▼──────┐
│ Schedules   │ │ Attendances│ │  Sales    │ │ Penalties │
└──────┬──────┘ └─────┬──────┘ └────┬──────┘ └────┬──────┘
       │              │              │              │
       │         ┌────▼──────┐       │              │
       │         │Attendance │       │              │
       │         │  Photos   │       │              │
       │         └───────────┘       │              │
       │                             │              │
┌──────▼──────┐               ┌──────▼──────┐ ┌────▼──────┐
│Assignments  │               │ Sale Items  │ │Penalty    │
└──────┬──────┘               └──────┬──────┘ │ History   │
       │                             │         └───────────┘
       │                      ┌──────▼──────┐
       │                      │   Products  │
       │                      └──────┬──────┘
       │                             │
       │                      ┌──────▼──────┐
       │                      │  Variants   │
       │                      └─────────────┘
       │
┌──────▼──────┐
│Availabilities│
└─────────────┘
```

---

## 📊 Key Tables

### Core Tables

#### `users`
Main user table for all system users.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    nim VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended'),
    photo VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### `permission_*` (Spatie)
Role-based access control tables.

```
permissions
roles
model_has_permissions
model_has_roles
role_has_permissions
```

### Schedule Module

#### `schedules`
Weekly schedule definitions.

```sql
CREATE TABLE schedules (
    id BIGINT UNSIGNED PRIMARY KEY,
    week_start_date DATE,
    week_end_date DATE,
    status ENUM('draft', 'published', 'archived'),
    generated_by BIGINT UNSIGNED (FK -> users),
    published_by BIGINT UNSIGNED (FK -> users),
    total_slots INTEGER,
    filled_slots INTEGER,
    coverage_rate DECIMAL(5,2),
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### `schedule_assignments`
User assignments to specific schedule slots.

```sql
CREATE TABLE schedule_assignments (
    id BIGINT UNSIGNED PRIMARY KEY,
    schedule_id BIGINT UNSIGNED (FK),
    user_id BIGINT UNSIGNED (FK),
    day ENUM('monday', 'tuesday', 'wednesday', 'thursday'),
    session INTEGER,
    date DATE,
    time_start TIME,
    time_end TIME,
    status ENUM('scheduled', 'completed', 'missed'),
    swapped_to_user_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    edited_by BIGINT UNSIGNED NULL,
    edited_at TIMESTAMP NULL,
    edit_reason TEXT NULL,
    previous_values JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### Attendance Module

#### `attendances`
Daily attendance records.

```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED PRIMARY KEY,
    user_id BIGINT UNSIGNED (FK),
    schedule_assignment_id BIGINT UNSIGNED (FK),
    date DATE,
    check_in TIMESTAMP NULL,
    check_out TIMESTAMP NULL,
    check_in_photo VARCHAR(255) NULL,
    work_hours DECIMAL(5,2) NULL,
    status ENUM('present', 'late', 'absent', 'excused'),
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_user_date (user_id, date, schedule_assignment_id)
);
```

### POS Module

#### `products`
Product catalog.

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255),
    sku VARCHAR(100) UNIQUE,
    price DECIMAL(15,2),
    cost_price DECIMAL(15,2),
    stock INTEGER DEFAULT 0,
    min_stock INTEGER DEFAULT 5,
    category VARCHAR(100),
    description TEXT NULL,
    status ENUM('active', 'inactive'),
    has_variants BOOLEAN DEFAULT FALSE,
    slug VARCHAR(255) UNIQUE,
    image VARCHAR(255) NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    is_public BOOLEAN DEFAULT TRUE,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### `sales`
Sales transactions.

```sql
CREATE TABLE sales (
    id BIGINT UNSIGNED PRIMARY KEY,
    cashier_id BIGINT UNSIGNED (FK -> users),
    student_id BIGINT UNSIGNED (FK -> students),
    invoice_number VARCHAR(100) UNIQUE,
    date DATE,
    total_amount DECIMAL(15,2),
    payment_method ENUM('cash', 'debit', 'credit', 'qris'),
    payment_amount DECIMAL(15,2),
    change_amount DECIMAL(15,2),
    shu_points_earned INTEGER NULL,
    conversion_rate INTEGER NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

---

## 🔍 Index Strategy

### Primary Indexes
All tables have automatic primary key indexes on `id`.

### Unique Indexes
```sql
users.email
users.nim
products.sku
products.slug
sales.invoice_number
penalty_types.code
system_settings.key
```

### Performance Indexes

#### High-Traffic Queries
```sql
-- User lookups
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_status_created ON users(status, created_at);

-- Schedule queries
CREATE INDEX idx_schedule_assignments_user_date ON schedule_assignments(user_id, date);
CREATE INDEX idx_schedules_status_published ON schedules(status, published_at);

-- Attendance queries
CREATE INDEX idx_attendances_user_date ON attendances(user_id, date);
CREATE INDEX idx_attendances_date_status ON attendances(date, status);

-- Sales queries
CREATE INDEX idx_sales_date ON sales(date);
CREATE INDEX idx_sales_cashier_date ON sales(cashier_id, date);
CREATE INDEX idx_sales_date_payment ON sales(date, payment_method);

-- Product queries
CREATE INDEX idx_products_category_status ON products(category, status);
CREATE INDEX idx_products_status_is_public ON products(status, is_public);
```

#### Composite Indexes
```sql
-- Complex schedule lookups
CREATE INDEX idx_sa_user_date_session ON schedule_assignments(user_id, date, session);
CREATE INDEX idx_schedules_user_date_status ON schedule_assignments(schedule_id, date, status);

-- Penalty tracking
CREATE INDEX idx_penalties_user_status ON penalties(user_id, status);
CREATE INDEX idx_penalties_user_status_date ON penalties(user_id, status, date);

-- Sales reporting
CREATE INDEX idx_sales_amount_created ON sales(total_amount, created_at);
```

---

## 🌱 Seeder Guide

### Seeder Execution Order

```php
// database/seeders/DatabaseSeeder.php
[
    // Phase 1: Foundation (no dependencies)
    PenaltyTypeSeeder::class,
    SystemSettingSeeder::class,
    ScheduleConfigurationSeeder::class,
    
    // Phase 2: Users & Access
    RolePermissionSeeder::class,
    UserSeeder::class,
    
    // Phase 3: Business Configuration
    StoreSettingSeeder::class,
    PaymentConfigurationSeeder::class,
    
    // Phase 4: Content
    KatalogSeeder::class,
]
```

### Available Seeders

| Seeder | Description | Data Count |
|--------|-------------|------------|
| `PenaltyTypeSeeder` | Penalty type definitions | 13 types |
| `SystemSettingSeeder` | System configurations | 20 settings |
| `ScheduleConfigurationSeeder` | Schedule algorithm config | 20 settings |
| `RolePermissionSeeder` | Roles and permissions | 10+ roles |
| `UserSeeder` | Initial users (Wirus 66) | 14 users |
| `StoreSettingSeeder` | Store operational settings | 1 record |
| `PaymentConfigurationSeeder` | Payment method config | Multiple |
| `KatalogSeeder` | Product catalog from CSV | Variable |

### Using Factories

```php
// Create single model
$user = User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);

// Create with relationships
$sale = Sale::factory()
    ->hasItems(3)
    ->handledBy($cashier)
    ->forStudent($student)
    ->today()
    ->create();

// Create multiple
Product::factory()->count(10)->create();

// With states
Product::factory()
    ->active()
    ->lowStock()
    ->hasVariants(3)
    ->create();
```

---

## 🔧 Best Practices

### Migration Guidelines

1. **Always test rollback**
   ```bash
   php artisan migrate:rollback
   ```

2. **Use schema builder**
   ```php
   Schema::create('table', function (Blueprint $table) {
       $table->id();
       // ...
   });
   ```

3. **Add comments for complex changes**
   ```php
   // Breaking change: Renamed column for consistency
   $table->renameColumn('percentage_bps', 'conversion_rate');
   ```

4. **Use transactions for data migrations**
   ```php
   DB::transaction(function () {
       // Data migration
   });
   ```

### Query Optimization

1. **Use eager loading**
   ```php
   // Bad: N+1 query
   foreach (ScheduleAssignment::all() as $assignment) {
       echo $assignment->user->name;
   }
   
   // Good: Eager loading
   $assignments = ScheduleAssignment::with('user')->get();
   ```

2. **Select only needed columns**
   ```php
   // Bad
   User::all();
   
   // Good
   User::select('id', 'name', 'email')->get();
   ```

3. **Use indexes in WHERE clauses**
   ```php
   // Uses index
   Attendance::where('user_id', $userId)
       ->where('date', '>=', $startDate)
       ->get();
   ```

### Data Integrity

1. **Use foreign key constraints**
   ```php
   $table->foreignId('user_id')
       ->constrained()
       ->onDelete('cascade');
   ```

2. **Soft delete transactional data**
   ```php
   $table->softDeletes();
   ```

3. **Validate before insert**
   ```php
   $validated = $request->validate([
       'email' => 'required|email|unique:users',
   ]);
   ```

---

## 📈 Performance Tips

### Caching Strategy

```php
// Cache expensive queries
$schedule = Cache::remember(
    "schedule.week.{$weekStart}",
    3600, // 1 hour
    fn() => Schedule::with('assignments.user')->get()
);

// Tag related caches
Cache::tags(['schedule', 'week-52'])->flush();
```

### Database Configuration

```env
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikopma
DB_USERNAME=root
DB_PASSWORD=

# Connection pooling
DB_POOL_MIN_CONNECTIONS=5
DB_POOL_MAX_CONNECTIONS=20
```

---

## 🐛 Troubleshooting

### Common Issues

#### Migration fails
```bash
# Clear config cache
php artisan config:clear

# Reset migration table
php artisan migrate:reset
php artisan migrate
```

#### Foreign key error
```sql
-- Check for orphan records
SELECT * FROM schedule_assignments 
WHERE user_id NOT IN (SELECT id FROM users);
```

#### Duplicate entry
```sql
-- Find duplicates
SELECT email, COUNT(*) 
FROM users 
GROUP BY email 
HAVING COUNT(*) > 1;
```

---

## 📚 Additional Resources

- [Laravel Migrations](https://laravel.com/docs/migrations)
- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Laravel Database](https://laravel.com/docs/database)
- [MySQL Index Documentation](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)

---

**Last Updated:** 2024-02-22  
**Version:** 2.0  
**Maintained By:** Development Team
