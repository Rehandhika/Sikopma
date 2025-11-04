# SIKOPMA Feature Backlog

> **Stack**: Laravel 12 + Livewire v3 + Tailwind v4 + Alpine.js  
> **Last Updated**: 2025-11-04

## Module Status

| Module | Priority | Status | Pages | Progress |
|--------|----------|--------|-------|----------|
| [Attendance](#1-attendance-module) | High | In Progress | 5 | 60% |
| [Schedule](#2-schedule-module) | High | Planned | 4 | 0% |
| [Swap](#3-swap-module) | Medium | Planned | 3 | 0% |
| [Leave](#4-leave-module) | High | Planned | 4 | 0% |
| [Penalty](#5-penalty-module) | Medium | Planned | 4 | 0% |
| [Cashier](#6-cashier-module) | High | Planned | 3 | 0% |
| [Reports](#7-reports-module) | High | Planned | 8 | 0% |
| [Stock](#8-stock-module) | Medium | Planned | 5 | 0% |
| [Purchase](#9-purchase-module) | Medium | Planned | 4 | 0% |
| [Product](#10-product-module) | Medium | Planned | 4 | 0% |
| [Settings](#11-settings-module) | Low | Planned | 6 | 0% |
| [Notifications](#12-notifications-module) | Medium | In Progress | 3 | 40% |

---

# 1. ATTENDANCE MODULE

## 1.1 Attendance Dashboard
- **Route**: `/attendance` → `attendance.index`
- **Component**: `App\Livewire\Attendance\Index`
- **RBAC**: `auth`
- **Features**: Today status, quick check-in/out, geolocation, recent history, monthly stats, calendar heatmap
- **JS**: Geolocation API, Flatpickr
- **Models**: `Attendance` (user_id, check_in, check_out, status, location_lat, location_lng)

## 1.2 Attendance History
- **Route**: `/attendance/history` → `attendance.history`
- **Component**: `App\Livewire\Attendance\History`
- **RBAC**: `auth`
- **Features**: Personal history table, filters (date range, status), export Excel, search, sort, pagination
- **JS**: Flatpickr (date range)
- **Actions**: Export filtered data

## 1.3 Attendance Report (Admin)
- **Route**: `/attendance/report` → `attendance.report`
- **Component**: `App\Livewire\Attendance\Report`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: All members view, filters, statistics, trend chart, export, bulk mark absent
- **JS**: Tom Select (member picker), Chart.js (line chart)
- **Actions**: Bulk update, export PDF/Excel

## 1.4 Attendance Settings
- **Route**: `/attendance/settings` → `attendance.settings`
- **Component**: `App\Livewire\Attendance\Settings`
- **RBAC**: `role:super-admin|ketua`
- **Features**: Configure check-in window, late threshold, location radius, auto-absent time
- **JS**: Flatpickr (time picker)
- **Models**: `Setting` (key-value)

## 1.5 Live Attendance Monitor
- **Route**: `/attendance/monitor` → `attendance.monitor`
- **Component**: `App\Livewire\Attendance\Monitor`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: Real-time grid, auto-refresh (30s polling), status indicators, quick reminder notifications
- **Livewire**: `wire:poll.30s`, `#[On('attendanceUpdated')]`
- **Actions**: Send bulk reminders

---

# 2. SCHEDULE MODULE

## 2.1 Schedule Calendar
- **Route**: `/schedule` → `schedule.index`
- **Component**: `App\Livewire\Schedule\Index`
- **RBAC**: `auth`
- **Features**: Calendar view (month/week/day), color-coded shifts, click for details, filters, mobile-responsive
- **Models**: `Schedule` (user_id, date, shift_type, start_time, end_time), `ShiftType`
- **UI**: Alpine.js calendar or table grid

## 2.2 My Schedule
- **Route**: `/schedule/my-schedule` → `schedule.my`
- **Component**: `App\Livewire\Schedule\MySchedule`
- **RBAC**: `auth`
- **Features**: Personal upcoming schedules, filters, request swap button, export iCal, pagination
- **Actions**: Export iCal/Google Calendar

## 2.3 Schedule Management (Admin)
- **Route**: `/schedule/manage` → `schedule.manage`
- **Component**: `App\Livewire\Schedule\Manage`
- **RBAC**: `role:super-admin|ketua|wakil-ketua`
- **Features**: CRUD for all members, bulk weekly generation, drag-drop assign, conflict detection, copy prev week
- **JS**: Tom Select, SortableJS (drag-drop)
- **Actions**: Send assignment notifications

## 2.4 Shift Types
- **Route**: `/schedule/shift-types` → `schedule.shift-types`
- **Component**: `App\Livewire\Schedule\ShiftTypes`
- **RBAC**: `role:super-admin|ketua`
- **Features**: CRUD shift types, color picker, time validation, usage count, set default
- **Models**: `ShiftType` (name, color, start_time, end_time, max_members, is_default)

---

# 3. SWAP MODULE

## 3.1 Swap Requests List
- **Route**: `/swap` → `swap.index`
- **Component**: `App\Livewire\Swap\Index`
- **RBAC**: `auth`
- **Features**: Tabs (my requests, received, all admin), filters, approve/reject/cancel actions, status badges
- **Models**: `SwapRequest` (requester_id, target_user_id, original_schedule_id, target_schedule_id, status, reason)
- **Actions**: Approve, reject, cancel with authorization

## 3.2 Create Swap Request
- **Route**: `/swap/create` → `swap.create`
- **Component**: `App\Livewire\Swap\Create`
- **RBAC**: `auth`
- **Features**: Select my schedule, select target member, optional target schedule, reason, validation
- **JS**: Tom Select (member picker)
- **Actions**: Send notification to target

## 3.3 Swap Approval (Admin)
- **Route**: `/swap/approval` → `swap.approval`
- **Component**: `App\Livewire\Swap\Approval`
- **RBAC**: `role:super-admin|ketua|wakil-ketua`
- **Features**: Pending requests list, side-by-side comparison, approve (swap schedules), reject with notes, bulk actions
- **Actions**: Swap schedule assignments, send notifications

---

# 4. LEAVE MODULE

## 4.1 Leave Requests List
- **Route**: `/leave` → `leave.index`
- **Component**: `App\Livewire\Leave\Index`
- **RBAC**: `auth`
- **Features**: Personal leave table, filters, status badges, cancel action, quota summary
- **Models**: `LeaveRequest` (user_id, leave_type_id, date_from, date_to, days, status), `LeaveType`

## 4.2 Create Leave Request
- **Route**: `/leave/create` → `leave.create`
- **Component**: `App\Livewire\Leave\Create`
- **RBAC**: `auth`
- **Features**: Form with leave type, date range, auto-calculate days, quota check, upload documents, validation
- **JS**: Flatpickr (dates), FilePond (uploads)
- **Validation**: No overlaps, within quota, weekends excluded

## 4.3 Leave Approval (Admin)
- **Route**: `/leave/approval` → `leave.approval`
- **Component**: `App\Livewire\Leave\Approval`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: Pending list, view details, approve/reject, bulk actions, filter
- **Actions**: Update schedules, send notifications

## 4.4 Leave Types & Quota
- **Route**: `/leave/types` → `leave.types`
- **Component**: `App\Livewire\Leave\Types`
- **RBAC**: `role:super-admin|ketua`
- **Features**: CRUD leave types, quotas, approval settings, usage stats
- **Models**: `LeaveType` (name, quota_days, requires_approval, allow_half_day, color)

---

# 5. PENALTY MODULE

## 5.1 Penalties List
- **Route**: `/penalty` → `penalty.index`
- **Component**: `App\Livewire\Penalty\Index`
- **RBAC**: `auth`
- **Features**: Personal penalty table, filters, status badges, payment history, summary

## 5.2 Penalty Management (Admin)
- **Route**: `/penalty/manage` → `penalty.manage`
- **Component**: `App\Livewire\Penalty\Manage`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: CRUD for all members, bulk issue, mark paid/waived, filters, export
- **JS**: Tom Select (member picker)
- **Actions**: Send penalty notifications

## 5.3 Penalty Types
- **Route**: `/penalty/types` → `penalty.types`
- **Component**: `App\Livewire\Penalty\Types`
- **RBAC**: `role:super-admin|ketua`
- **Features**: CRUD types, default amounts, auto-issue rules, usage stats
- **Models**: `PenaltyType` (name, default_amount, description, auto_issue)

## 5.4 Penalty Report
- **Route**: `/penalty/report` → `penalty.report`
- **Component**: `App\Livewire\Penalty\Report`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: Financial stats, trend charts, breakdown by type/member, export
- **JS**: Chart.js (trends)

---

# 6. CASHIER MODULE

## 6.1 Cashier Dashboard
- **Route**: `/cashier` → `cashier.index`
- **Component**: `App\Livewire\Cashier\Index`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|kasir`
- **Features**: Today's sales, quick stats, recent transactions, low stock alerts, sales chart
- **JS**: Chart.js (daily sales)

## 6.2 Point of Sale (POS)
- **Route**: `/cashier/pos` → `cashier.pos`
- **Component**: `App\Livewire\Cashier\Pos`
- **RBAC**: `role:super-admin|ketua|kasir`
- **Features**: Product search/scan, cart management, payment methods, print receipt, member discount
- **JS**: Barcode scanner integration, Tom Select
- **Models**: `Transaction`, `TransactionItem`, `Product`, `User`

## 6.3 Transaction History
- **Route**: `/cashier/transactions` → `cashier.transactions`
- **Component**: `App\Livewire\Cashier\Transactions`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|kasir`
- **Features**: Transaction list, filters (date, cashier, payment method), view details, void/refund, export
- **Actions**: Void transaction (admin only)

---

# 7. REPORTS MODULE

## 7.1 Sales Report
- **Route**: `/reports/sales` → `reports.sales`
- **Component**: `App\Livewire\Report\SalesReport`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: Date range filter, product filter, sales chart, breakdown by product/category/cashier, export
- **JS**: Flatpickr (date range), Tom Select, Chart.js (bar chart)

## 7.2 Inventory Report
- **Route**: `/reports/inventory` → `reports.inventory`
- **Component**: `App\Livewire\Report\InventoryReport`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: Current stock levels, low stock alerts, stock movement history, valuation, export

## 7.3 Financial Report
- **Route**: `/reports/financial` → `reports.financial`
- **Component**: `App\Livewire\Report\FinancialReport`
- **RBAC**: `role:super-admin|ketua|wakil-ketua`
- **Features**: Revenue vs expenses, profit/loss, penalty collections, cash flow, monthly trends
- **JS**: Chart.js (multi-line chart)

## 7.4 Attendance Report
- **Route**: `/reports/attendance` → `reports.attendance`
- **Component**: `App\Livewire\Report\AttendanceReport`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: Member attendance summary, on-time %, late/absent counts, trends, export

## 7.5 Member Activity Report
- **Route**: `/reports/member-activity` → `reports.member-activity`
- **Component**: `App\Livewire\Report\MemberActivity`
- **RBAC**: `role:super-admin|ketua|wakil-ketua`
- **Features**: Login history, transaction count, attendance rate, penalties, contributions

## 7.6 Purchase Report
- **Route**: `/reports/purchase` → `reports.purchase`
- **Component**: `App\Livewire\Report\PurchaseReport`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: Purchase orders by period, supplier analysis, cost trends, pending orders

## 7.7 Profit Analysis
- **Route**: `/reports/profit` → `reports.profit`
- **Component**: `App\Livewire\Report\ProfitAnalysis`
- **RBAC**: `role:super-admin|ketua|wakil-ketua`
- **Features**: Margin by product/category, best/worst sellers, pricing recommendations, trends

## 7.8 Custom Report Builder
- **Route**: `/reports/custom` → `reports.custom`
- **Component**: `App\Livewire\Report\CustomBuilder`
- **RBAC**: `role:super-admin|ketua`
- **Features**: Select data sources, choose fields, apply filters, save templates, schedule email reports

---

# 8. STOCK MODULE

## 8.1 Stock List
- **Route**: `/stock` → `stock.index`
- **Component**: `App\Livewire\Stock\Index`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph|kasir`
- **Features**: Current stock table, search, filters, low stock badges, quick reorder, export

## 8.2 Stock Adjustment
- **Route**: `/stock/adjustment` → `stock.adjustment`
- **Component**: `App\Livewire\Stock\Adjustment`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: Adjust stock (add/subtract), reason (required), bulk adjustments, audit trail
- **Models**: `StockAdjustment` (product_id, type, quantity, reason, adjusted_by)

## 8.3 Stock Movement History
- **Route**: `/stock/movements` → `stock.movements`
- **Component**: `App\Livewire\Stock\Movements`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: All movements (sales, purchases, adjustments), filters, export, audit trail

## 8.4 Stock Opname
- **Route**: `/stock/opname` → `stock.opname`
- **Component**: `App\Livewire\Stock\Opname`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: Physical count form, compare with system, generate adjustment records, print checklist
- **Models**: `StockOpname` (date, products_counted, discrepancies, status, verified_by)

## 8.5 Stock Alerts Settings
- **Route**: `/stock/alerts` → `stock.alerts`
- **Component**: `App\Livewire\Stock\Alerts`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: Set low stock thresholds per product, enable/disable notifications, alert recipients

---

# 9. PURCHASE MODULE

## 9.1 Purchase Orders List
- **Route**: `/purchase` → `purchase.index`
- **Component**: `App\Livewire\Purchase\Index`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph`
- **Features**: PO table, filters (status, supplier, date), view details, approve/receive, export

## 9.2 Create Purchase Order
- **Route**: `/purchase/create` → `purchase.create`
- **Component**: `App\Livewire\Purchase\Create`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: Select supplier, add products (multi-row form), quantities, unit prices, calculate total, notes
- **JS**: Tom Select (supplier, products), dynamic row addition
- **Models**: `PurchaseOrder` (supplier_id, total, status, notes), `PurchaseOrderItem` (product_id, quantity, unit_price)

## 9.3 Receive Purchase Order
- **Route**: `/purchase/{id}/receive` → `purchase.receive`
- **Component**: `App\Livewire\Purchase\Receive`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: Mark items as received, partial receiving, update stock, mark PO complete, upload delivery note
- **JS**: FilePond (upload)
- **Actions**: Increment product stock

## 9.4 Suppliers Management
- **Route**: `/purchase/suppliers` → `purchase.suppliers`
- **Component**: `App\Livewire\Purchase\Suppliers`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: CRUD suppliers, contact info, payment terms, rating, purchase history
- **Models**: `Supplier` (name, contact_person, phone, email, address, payment_terms)

---

# 10. PRODUCT MODULE

## 10.1 Products List
- **Route**: `/products` → `products.index`
- **Component**: `App\Livewire\Product\Index`
- **RBAC**: `role:super-admin|ketua|wakil-ketua|bph|kasir`
- **Features**: Product table, search, filters (category, status), quick edit price/stock, export

## 10.2 Create/Edit Product
- **Route**: `/products/create`, `/products/{id}/edit` → `products.create`, `products.edit`
- **Component**: `App\Livewire\Product\Form`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: Form (name, SKU, barcode, category, price, cost, stock, min_stock, image), validation, upload image
- **JS**: FilePond (image upload), barcode generator
- **Models**: `Product` (name, sku, barcode, category_id, price, cost, stock, min_stock, image)

## 10.3 Product Categories
- **Route**: `/products/categories` → `products.categories`
- **Component**: `App\Livewire\Product\Categories`
- **RBAC**: `role:super-admin|ketua|bph`
- **Features**: CRUD categories, nested categories, drag-sort order, product count
- **JS**: SortableJS (reorder)
- **Models**: `ProductCategory` (name, parent_id, order)

## 10.4 Barcode Generator
- **Route**: `/products/barcodes` → `products.barcodes`
- **Component**: `App\Livewire\Product\Barcodes`
- **RBAC**: `role:super-admin|ketua|bph|kasir`
- **Features**: Select products, generate barcode labels (PDF), print settings (size, format)
- **JS**: JsBarcode library
- **Actions**: Generate PDF for printing

---

# 11. SETTINGS MODULE

## 11.1 General Settings
- **Route**: `/settings` → `settings.index`
- **Component**: `App\Livewire\Settings\General`
- **RBAC**: `role:super-admin|ketua`
- **Features**: Cooperative name, logo, address, contact, business hours, currency, timezone

## 11.2 User Management
- **Route**: `/settings/users` → `settings.users`
- **Component**: `App\Livewire\Settings\Users`
- **RBAC**: `role:super-admin|ketua`
- **Features**: CRUD users, assign roles, activate/deactivate, reset password, profile completion

## 11.3 Role & Permission Management
- **Route**: `/settings/roles` → `settings.roles`
- **Component**: `App\Livewire\Settings\Roles`
- **RBAC**: `role:super-admin`
- **Features**: CRUD roles, assign permissions (checkboxes), role descriptions, member count

## 11.4 System Settings
- **Route**: `/settings/system` → `settings.system`
- **Component**: `App\Livewire\Settings\System`
- **RBAC**: `role:super-admin`
- **Features**: Maintenance mode, cache management, log viewer, backup database, queue status

## 11.5 Notification Settings
- **Route**: `/settings/notifications` → `settings.notifications`
- **Component**: `App\Livewire\Settings\Notifications`
- **RBAC**: `role:super-admin|ketua`
- **Features**: Enable/disable notification types, email templates, push settings, defaults

## 11.6 Audit Log
- **Route**: `/settings/audit` → `settings.audit`
- **Component**: `App\Livewire\Settings\Audit`
- **RBAC**: `role:super-admin|ketua`
- **Features**: Activity log, filters (user, action, date), export, retention settings
- **Models**: `AuditLog` (user_id, action, model, old_values, new_values, ip_address)

---

# 12. NOTIFICATIONS MODULE

## 12.1 Notifications List
- **Route**: `/notifications` → `notifications.index`
- **Component**: `App\Livewire\Notification\Index`
- **RBAC**: `auth`
- **Status**: ✅ Partially Complete (40%)
- **Features**: List notifications, mark as read, filter (all/unread), delete, pagination

## 12.2 Notification Settings (Personal)
- **Route**: `/notifications/settings` → `notifications.settings`
- **Component**: `App\Livewire\Notification\Settings`
- **RBAC**: `auth`
- **Features**: Toggle notification types, email digest frequency, push preferences, quiet hours

## 12.3 Broadcast Notifications (Admin)
- **Route**: `/notifications/broadcast` → `notifications.broadcast`
- **Component**: `App\Livewire\Notification\Broadcast`
- **RBAC**: `role:super-admin|ketua|wakil-ketua`
- **Features**: Send to all/role/specific users, rich text editor, schedule send, preview, draft save

---

# Implementation Priority Queue

## Phase 1: Core Operations (Sprint 1-2)
1. ✅ Attendance Dashboard (60% complete)
2. Schedule Calendar & My Schedule
3. POS System (Cashier)
4. Product Management
5. Stock List & Adjustments

## Phase 2: Administrative (Sprint 3-4)
6. Leave Management (create, approval)
7. Attendance Report (admin)
8. Schedule Management (admin)
9. Purchase Orders
10. Sales Report

## Phase 3: Advanced Features (Sprint 5-6)
11. Swap Requests System
12. Penalty System
13. Financial Reports
14. Stock Opname
15. Custom Report Builder

## Phase 4: Configuration & Polish (Sprint 7)
16. All Settings modules
17. Audit Logging
18. Notification System complete
19. Barcode Generator
20. System Optimization

---

# Quick Reference: Common Patterns

## Route Pattern
```php
Route::get('/module/page', Component::class)->name('module.page')->middleware(['auth', 'role:...']);
```

## Component Pattern
```php
class ComponentName extends Component {
    public function render() {
        return view('livewire.module.component-name')->layout('layouts.app');
    }
}
```

## Navigation Pattern
```blade
@can('role', 'role-name')
<a href="{{ route('module.page') }}" @class(['active' => request()->routeIs('module.page')])>
    <span>Page Name</span>
</a>
@endcan
```

## Test Pattern
```php
test('authorized user can access page', function () {
    $user = User::factory()->create();
    $user->assignRole('role-name');
    
    $this->actingAs($user)
        ->get(route('module.page'))
        ->assertOk();
});
```

---

**Next Steps**: Choose a feature from the backlog and use the detailed specification to implement it following the Master Prompt guidelines.
