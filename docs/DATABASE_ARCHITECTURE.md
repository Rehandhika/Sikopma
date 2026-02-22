# Database Architecture Documentation

## 📐 Design Principles

This database architecture follows these core principles:

1. **Modularity** - Migrations organized by business domain
2. **Scalability** - Indexes and foreign keys planned from the start
3. **Auditability** - Soft deletes and audit trails on all transactional tables
4. **Consistency** - Standardized naming conventions and column types
5. **Performance** - Strategic indexing on frequently queried columns

---

## 📁 Migration Structure

```
database/migrations/
├── 0001_01_01_*.php              # Laravel system tables
├── 0001_01_02_*.php              # Authentication & Authorization
├── 0001_01_03_*.php              # Queue & Cache system
│
├── 2024_01_01_*.php              # === CORE MODULE ===
│   ├── 001_users.php             # Users table
│   ├── 002_roles_permissions.php # Spatie permissions
│   └── 003_audit_trail.php       # Audit logs, activity logs
│
├── 2024_02_01_*.php              # === SCHEDULE MODULE ===
│   ├── 001_schedules.php         # Base schedules
│   ├── 002_schedule_assignments.php
│   ├── 003_availabilities.php
│   ├── 004_schedule_templates.php
│   ├── 005_schedule_config.php
│   └── 006_assignment_history.php
│
├── 2024_03_01_*.php              # === ATTENDANCE MODULE ===
│   ├── 001_attendances.php
│   ├── 002_penalties.php
│   └── 003_penalty_types.php
│
├── 2024_04_01_*.php              # === LEAVE & SWAP MODULE ===
│   ├── 001_leave_requests.php
│   ├── 002_leave_affected_schedules.php
│   ├── 003_schedule_change_requests.php
│   └── 004_swap_requests.php
│
├── 2024_05_01_*.php              # === POS MODULE ===
│   ├── 001_products.php
│   ├── 002_product_variants.php
│   ├── 003_sales.php
│   ├── 004_sale_items.php
│   └── 005_shu_system.php
│
├── 2024_06_01_*.php              # === INVENTORY MODULE ===
│   ├── 001_purchases.php
│   ├── 002_purchase_items.php
│   └── 003_stock_adjustments.php
│
├── 2024_07_01_*.php              # === CONTENT MODULE ===
│   ├── 001_banners.php
│   ├── 002_news.php
│   └── 003_notifications.php
│
├── 2024_08_01_*.php              # === SETTINGS MODULE ===
│   ├── 001_system_settings.php
│   ├── 002_store_settings.php
│   └── 003_academic_holidays.php
│
├── 2024_09_01_*.php              # === INDEXES MODULE ===
│   ├── 001_core_indexes.php
│   ├── 002_schedule_indexes.php
│   ├── 003_attendance_indexes.php
│   ├── 004_pos_indexes.php
│   ├── 005_inventory_indexes.php
│   └── 006_content_indexes.php
│
└── 2024_10_01_*.php              # === FINALIZATION ===
    ├── 001_foreign_keys.php      # All FK constraints
    └── 002_seed_initial_data.php # Initial data
```

---

## 📊 Entity Relationship Diagram

### Core Module
```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│     users       │     │  permission_*    │     │   audit_logs    │
├─────────────────┤     ├──────────────────┤     ├─────────────────┤
│ id              │     │  (Spatie RBAC)   │     │ id              │
│ name            │     │                  │     │ user_id (FK)    │
│ email (unique)  │     │                  │     │ action          │
│ nim (unique)    │     │                  │     │ model           │
│ password        │     │                  │     │ old_values      │
│ status          │     │                  │     │ new_values      │
│ deleted_at      │     │                  │     │ ip_address      │
│ timestamps      │     │                  │     │ timestamps      │
└─────────────────┘     └──────────────────┘     └─────────────────┘
```

### Schedule Module
```
┌─────────────────┐     ┌──────────────────────┐     ┌──────────────────┐
│   schedules     │     │ schedule_assignments │     │  availabilities  │
├─────────────────┤     ├──────────────────────┤     ├──────────────────┤
│ id              │◄────│ schedule_id (FK)     │     │ id               │
│ week_start_date │     │ user_id (FK)         │────►│ user_id (FK)     │
│ week_end_date   │     │ day                  │     │ schedule_id (FK) │
│ status          │     │ session              │     │ week_start_date  │
│ generated_by    │     │ date                 │     │ status           │
│ published_by    │     │ time_start           │     │ submitted_at     │
│ total_slots     │     │ time_end             │     │ timestamps       │
│ filled_slots    │     │ status               │     └──────────────────┘
│ coverage_rate   │     │ swapped_to_user_id   │
│ timestamps      │     │ edited_by            │     ┌──────────────────┐
│ deleted_at      │     │ timestamps           │     │availability_detail│
└─────────────────┘     │ deleted_at           │     ├──────────────────┤
                        └──────────────────────┘     │ availability_id  │
                                                     │ day              │
                                                     │ session          │
                                                     │ is_available     │
                                                     │ timestamps       │
                                                     └──────────────────┘
```

### Attendance Module
```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  attendances    │     │  penalty_types   │     │    penalties    │
├─────────────────┤     ├──────────────────┤     ├─────────────────┤
│ id              │     │ id               │     │ id              │
│ user_id (FK)    │     │ code (unique)    │     │ user_id (FK)    │
│ schedule_assign_│     │ name             │     │ penalty_type_id │
│   ment_id (FK)  │     │ description      │     │ reference_type  │
│ date            │     │ points           │     │ reference_id    │
│ check_in        │     │ is_active        │     │ points          │
│ check_out       │     │ timestamps       │     │ description     │
│ work_hours      │     └──────────────────┘     │ date            │
│ status          │                              │ status          │
│ notes           │                              │ appeal_*        │
│ timestamps      │                              │ reviewed_by     │
│ deleted_at      │                              │ timestamps      │
└─────────────────┘                              │ deleted_at      │
                                                 └─────────────────┘
```

### POS Module
```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│    products     │     │ product_variants │     │  product_variant│
├─────────────────┤     ├──────────────────┤     │    _options     │
│ id              │◄────│ id               │     ├─────────────────┤
│ name            │     │ product_id (FK)  │     │ id              │
│ sku (unique)    │     │ sku              │     │ product_id (FK) │
│ price           │     │ variant_name     │     │ variant_option_│
│ cost_price      │     │ price            │     │   id (FK)       │
│ stock           │     │ cost_price       │     │ timestamps      │
│ category        │     │ stock            │     └─────────────────┘
│ is_featured     │     │ min_stock        │
│ is_public       │     │ option_values    │     ┌──────────────────┐
│ timestamps      │     │ is_active        │     │ variant_options  │
│ deleted_at      │     │ timestamps       │     ├──────────────────┤
└─────────────────┘     │ deleted_at       │     │ id               │
                        └──────────────────┘     │ name             │
                                                 │ slug (unique)    │
                                                 │ display_order    │
                                                 │ timestamps       │
                                                 └──────────────────┘
```

---

## 🏷️ Naming Conventions

### Tables
- **Plural snake_case**: `users`, `schedule_assignments`, `product_variants`
- **Pivot tables**: Alphabetical order, no model name: `model_has_roles`
- **Polymorphic**: Singular with type: `reference_type`, `reference_id`

### Columns
- **Primary keys**: Always `id` (auto-increment)
- **Foreign keys**: `{table}_id` (singular): `user_id`, `schedule_id`
- **Timestamps**: `created_at`, `updated_at`, `deleted_at`
- **Soft deletes**: Always `deleted_at` for transactional tables
- **Boolean**: `is_*`, `has_*`, `can_*`: `is_active`, `is_public`
- **Dates**: `{event}_at`: `published_at`, `approved_at`

### Indexes
- **Format**: `{table}_{column(s)}_{type}_index`
- **Examples**:
  - `users_email_unique`
  - `sales_date_index`
  - `schedule_assignments_user_date_session_index`

### Foreign Keys
- **Format**: `{table}_{column}_foreign`
- **Examples**:
  - `sales_cashier_id_foreign`
  - `schedule_assignments_schedule_id_foreign`

---

## 📈 Index Strategy

### Automatic Indexes (via conventions)
- Primary keys (auto)
- Unique constraints (auto)
- Foreign keys (auto on some DBs)

### Manual Indexes (business-driven)

#### High Priority (frequently queried)
```sql
-- Users
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_nim ON users(nim);

-- Schedule
CREATE INDEX idx_schedule_assignments_user_date ON schedule_assignments(user_id, date);
CREATE INDEX idx_schedules_status_published ON schedules(status, published_at);

-- Attendance
CREATE INDEX idx_attendances_user_date ON attendances(user_id, date);
CREATE INDEX idx_attendances_date_status ON attendances(date, status);

-- POS
CREATE INDEX idx_sales_date ON sales(date);
CREATE INDEX idx_sales_cashier_date ON sales(cashier_id, date);
CREATE INDEX idx_products_category_status ON products(category, status);
```

#### Composite Indexes (for complex queries)
```sql
-- Schedule assignment lookup
CREATE INDEX idx_sa_user_date_session ON schedule_assignments(user_id, date, session);

-- Sales reporting
CREATE INDEX idx_sales_date_payment ON sales(date, payment_method, total_amount);

-- Penalty tracking
CREATE INDEX idx_penalties_user_status_date ON penalties(user_id, status, date);
```

---

## 🔐 Data Integrity

### Foreign Key Rules
1. **CASCADE** - When child should be deleted with parent
   - `schedule_assignments.schedule_id` → `schedules.id`
   - `sale_items.sale_id` → `sales.id`

2. **SET NULL** - When child can exist without parent
   - `banners.created_by` → `users.id`
   - `schedule_assignments.swapped_to_user_id` → `users.id`

3. **RESTRICT** - When child must not be deleted
   - Default for most FKs

### Soft Delete Policy
**Tables with soft deletes:**
- All transactional tables (sales, purchases, attendances)
- All business entities (products, schedules, users)
- All audit-related tables (penalties, leave_requests)

**Tables without soft deletes:**
- System tables (cache, jobs, sessions)
- Pure audit logs (audit_logs, activity_logs, login_histories)
- Configuration tables (settings, system_settings)

---

## 🌱 Seeder Architecture

### Seeder Classes
```
database/seeders/
├── DatabaseSeeder.php           # Main orchestrator
├── Core/
│   ├── UserSeeder.php
│   ├── RolePermissionSeeder.php
│   └── SystemSettingSeeder.php
├── Schedule/
│   ├── ScheduleConfigurationSeeder.php
│   └── AcademicCalendarSeeder.php
├── Pos/
│   ├── ProductCatalogSeeder.php
│   └── PaymentConfigurationSeeder.php
└── Content/
    ├── BannerSeeder.php
    └── NewsSeeder.php
```

### Seeder Execution Order
```php
[
    // Phase 1: Foundation (no dependencies)
    PenaltyTypeSeeder::class,
    SystemSettingSeeder::class,
    ScheduleConfigurationSeeder::class,
    
    // Phase 2: Users & Access
    RolePermissionSeeder::class,
    UserSeeder::class,
    
    // Phase 3: Business Data (depends on users)
    StoreSettingSeeder::class,
    PaymentConfigurationSeeder::class,
    
    // Phase 4: Content
    ProductCatalogSeeder::class,
    BannerSeeder::class,
    NewsSeeder::class,
]
```

---

## 🔄 Migration Workflow

### Creating New Migrations
```bash
# Module-based naming
php artisan make:migration create_{module}_{table}_table

# Example
php artisan make:migration create_schedule_templates_table
```

### Migration Best Practices
1. **Always up() and down()** - Must be reversible
2. **Use Schema::hasColumn()** - Check before modifying
3. **Batch related changes** - One feature per migration
4. **Document breaking changes** - In migration comments
5. **Test rollback** - Always test down() method

### Rollback Strategy
```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback specific step
php artisan migrate:rollback --step=3

# Fresh migration (with seed)
php artisan migrate:fresh --seed
```

---

## 📝 Version Control

### Migration Versioning
- **Date-based**: `YYYY_MM_DD_HHMMSS_*.php`
- **Sequence**: Within same day, use sequence numbers
- **Example**:
  - `2024_01_01_000001_create_users_table.php`
  - `2024_01_01_000002_create_roles_table.php`

### Breaking Changes
For breaking schema changes:
1. Create new migration (don't modify old)
2. Document in CHANGELOG.md
3. Update this documentation
4. Test on staging first

---

## 🎯 Performance Optimization

### Query Optimization Guidelines
1. **Use eager loading**: `with()` for relationships
2. **Select only needed columns**: `select('id', 'name')`
3. **Use indexes**: Always query indexed columns in WHERE
4. **Avoid N+1**: Use `withCount()`, `withSum()`
5. **Batch operations**: Use `upsert()`, `insert()` for bulk

### Caching Strategy
```php
// Cache expensive queries
Cache::remember('schedule.current_week', 3600, function () {
    return Schedule::current()->with('assignments.user')->get();
});

// Tag related caches
Cache::tags(['schedule', 'week-52'])->put('key', $value);
```

---

## 📚 Reference

- [Laravel Migrations Documentation](https://laravel.com/docs/migrations)
- [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Laravel Database Queries](https://laravel.com/docs/queries)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

---

**Last Updated:** 2024-02-22  
**Version:** 2.0  
**Maintained By:** Development Team
