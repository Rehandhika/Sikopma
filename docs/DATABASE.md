# 🗄️ Database Structure - DEPLOY SIKOPMA

> Clean, efficient, and well-organized database architecture
>
> **Last Cleanup:** 2026-02-22 | **Migrations:** 69 files (consolidated from 90+)

---

## 📋 Quick Start

```bash
# Run migrations
php artisan migrate

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Check status
php artisan migrate:status
```

---

## 📁 Migration Organization

Migrations are organized by **business domain**, not just chronologically:

| Prefix | Module | Tables | Count |
|--------|--------|--------|-------|
| `0001_01_01_*` | **Laravel System** | cache, jobs | 2 |
| `2025_11_03_052*` | **Core** | users, products, sales, purchases | 8 |
| `2025_11_03_053*` | **Settings** | notifications, settings | 3 |
| `2025_11_03_054*` | **Schedule** | schedules, assignments, availabilities | 8 |
| `2025_11_03_055*` | **Attendance** | attendances, penalties | 5 |
| `2025_11_03_10*` | **System** | permissions, sessions | 2 |
| `2025_11_04_*` | **Enhancements** | Additional fields | 5 |
| `2025_11_16_*` | **Schedule+** | Templates, history, config | 4 |
| `2025_11_23_*` | **Features** | Store settings, edit history, login | 8 |
| `2025_11_24_*` | **Attendance+** | Photo evidence | 1 |
| `2025_12_23_*` | **Content** | Banners, news | 8 |
| `2025_12_24_*` | **Products+** | Images, cost price, swaps | 4 |
| `2026_01_*` | **New Features** | Variants, SHU, activity logs, news | 11 |
| `2026_02_*` | **Optimizations** | FK fixes, indexes, variants | 3 |
| `2026_02_22_15*` | **Cleanup** | Consolidated indexes, soft deletes, FK fixes | 3 |

### ✨ Consolidated Migrations

**All performance indexes** are now in ONE file:
- `2026_02_22_150000_add_performance_indexes.php`

**Replaces 11 separate index migrations** with clean, organized structure:
```php
// Inside the migration:
private function addCoreIndexes(): void { ... }      // Users, sessions
private function addScheduleIndexes(): void { ... }  // Schedules, assignments
private function addAttendanceIndexes(): void { ... }// Attendances, penalties
private function addPosIndexes(): void { ... }       // Products, sales
private function addInventoryIndexes(): void { ... } // Purchases, stock
private function addContentIndexes(): void { ... }   // Notifications, banners
```

---

## 📊 Key Tables

### Core Business Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `users` | System users | id, name, email, nim, status |
| `products` | Product catalog | id, name, sku, price, stock |
| `product_variants` | Product variations | id, product_id, variant_name, price |
| `sales` | Sales transactions | id, invoice_number, cashier_id, total |
| `sale_items` | Sale line items | id, sale_id, product_id, quantity |
| `students` | Student records | id, nim, full_name, points_balance |
| `shu_point_transactions` | SHU point tracking | id, student_id, type, points |

### Schedule Management

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `schedules` | Weekly schedules | id, week_start, status, published_at |
| `schedule_assignments` | User assignments | id, schedule_id, user_id, date, session |
| `availabilities` | User availability | id, user_id, schedule_id, status |
| `availability_details` | Daily availability | id, availability_id, day, session |

### Attendance & Penalties

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `attendances` | Daily attendance | id, user_id, date, check_in, status |
| `penalty_types` | Penalty definitions | id, code, name, points |
| `penalties` | Penalty records | id, user_id, penalty_type_id, points |
| `penalty_history` | Period summaries | id, user_id, period_start, total_points |

### Leave & Changes

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `leave_requests` | Leave applications | id, user_id, leave_type, status |
| `leave_affected_schedules` | Affected assignments | id, leave_request_id, replacement |
| `schedule_change_requests` | Swap/change requests | id, user_id, change_type, status |

### Inventory

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `purchases` | Purchase orders | id, supplier, invoice_number, total |
| `purchase_items` | Purchase line items | id, purchase_id, product_id, cost |
| `stock_adjustments` | Stock changes | id, product_id, type, quantity |

### Content & Settings

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `banners` | Homepage banners | id, title, image, priority |
| `news` | News articles | id, title, content, published_at |
| `notifications` | User notifications | id, user_id, title, is_read |
| `system_settings` | System config | id, key, value, type |
| `store_settings` | Store config | id, is_open, operating_hours |

---

## 🔍 Index Strategy

### Consolidated Index Migration
All performance indexes are in **one migration**:  
`2026_02_22_150000_add_performance_indexes.php`

### Index Naming Convention
```
idx_{table_short}_{columns}
```

**Examples:**
- `idx_users_status` - Users by status
- `idx_sa_user_date_session` - Schedule assignments by user, date, session
- `idx_att_user_date` - Attendances by user and date
- `idx_sales_cashier_date` - Sales by cashier and date

### Key Indexes by Module

**Users:**
- `idx_users_status` - Filter active users
- `idx_users_nim` - NIM lookup

**Schedule:**
- `idx_sa_user_date_session` - User schedule lookup
- `idx_schedules_status_published` - Published schedules

**Attendance:**
- `idx_att_user_date` - User attendance history
- `idx_att_date_status` - Daily attendance summary

**POS:**
- `idx_products_sku` - Product lookup
- `idx_products_category_status` - Category filtering
- `idx_sales_date` - Sales by date
- `idx_sales_cashier_date` - Cashier performance

---

## 🌱 Seeders

### Execution Order
```php
// database/seeders/DatabaseSeeder.php
[
    PenaltyTypeSeeder::class,           // Penalty definitions
    SystemSettingSeeder::class,         // System config
    ScheduleConfigurationSeeder::class, // Schedule algorithm
    RolePermissionSeeder::class,        // Roles & permissions
    UserSeeder::class,                  // Initial users
    StoreSettingSeeder::class,          // Store config
    PaymentConfigurationSeeder::class,  // Payment methods
    KatalogSeeder::class,               // Product catalog
]
```

### Available Seeders

| Seeder | Data |
|--------|------|
| `PenaltyTypeSeeder` | 13 penalty types |
| `SystemSettingSeeder` | 20 system settings |
| `ScheduleConfigurationSeeder` | 20 schedule config |
| `RolePermissionSeeder` | 10+ roles |
| `UserSeeder` | 14 initial users |
| `KatalogSeeder` | Product catalog (CSV) |

---

## 🏷️ Naming Conventions

### Tables
- **Plural snake_case**: `users`, `schedule_assignments`
- **Pivot tables**: `{model}_{model}`: `model_has_roles`

### Columns
- **Primary key**: `id` (auto-increment)
- **Foreign keys**: `{table}_id`: `user_id`, `schedule_id`
- **Timestamps**: `created_at`, `updated_at`, `deleted_at`
- **Boolean**: `is_*`, `has_*`: `is_active`, `is_public`

### Indexes
- **Format**: `idx_{table}_{columns}`
- **Examples**: `idx_users_status`, `idx_att_user_date`

### Foreign Keys
- **Format**: `{table}_{column}_foreign`
- **onDelete**: `cascade`, `set null`, or `restrict`

---

## 📈 Best Practices

### Migrations
1. **Always test rollback**: `php artisan migrate:rollback`
2. **Use Schema::hasColumn()**: Check before modifying
3. **Document breaking changes**: In migration comments
4. **One feature per migration**: Keep it atomic

### Queries
1. **Use eager loading**: Avoid N+1 queries
2. **Select needed columns**: Don't use `select(*)`
3. **Use indexes**: Query indexed columns in WHERE
4. **Batch operations**: Use `upsert()` for bulk inserts

### Data Integrity
1. **Foreign keys**: Always define relationships
2. **Soft deletes**: For transactional tables
3. **Validation**: Validate before insert/update

---

## 🐛 Troubleshooting

### Migration fails
```bash
php artisan config:clear
php artisan migrate:reset
php artisan migrate
```

### Check migration status
```bash
php artisan migrate:status
```

### Find duplicate indexes
```sql
SELECT table_name, index_name, column_name
FROM information_schema.statistics
WHERE table_schema = 'your_database'
ORDER BY table_name, index_name;
```

---

## 📚 Resources

- [Laravel Migrations](https://laravel.com/docs/migrations)
- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [MySQL Indexes](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)

---

**Last Updated:** 2026-02-22  
**Version:** 3.0 (Cleaned & Consolidated)
