# Pengaturan Waktu & Tanggal Sistem

Sistem ini menyediakan pengaturan waktu, tanggal, dan timezone yang dapat dikonfigurasi melalui admin panel dan bekerja di seluruh aplikasi (backend dan frontend).

## Fitur

- **Zona Waktu Dinamis**: WIB, WITA, WIT
- **Format Tanggal Kustom**: d/m/Y, d-m-Y, d M Y, Y-m-d, dll
- **Format Waktu Kustom**: 24 jam atau 12 jam (AM/PM)
- **Locale**: Indonesia (id) atau English (en)
- **Caching**: Settings di-cache untuk performa optimal

## Penggunaan di Backend (PHP)

### Helper Functions

```php
// Format tanggal
echo format_date($date);           // 17/01/2026
echo format_time($time);           // 08:30
echo format_datetime($datetime);   // 17/01/2026 08:30

// Format human readable
echo format_date_human($date);     // Sabtu, 17 Januari 2026
echo format_datetime_human($dt);   // Sabtu, 17 Januari 2026 08:30

// Relative time
echo diff_for_humans($datetime);   // 2 jam yang lalu

// Get current time in system timezone
$now = system_now();

// Get system timezone
$tz = system_timezone();           // Asia/Jakarta

// Parse date with system timezone
$date = parse_date('2026-01-17');
```

### Blade Directives

```blade
{{-- Format tanggal --}}
@formatDate($date)
@formatTime($time)
@formatDateTime($datetime)

{{-- Human readable --}}
@formatDateHuman($date)
@formatDateTimeHuman($datetime)

{{-- Relative time --}}
@diffForHumans($datetime)

{{-- System info --}}
@systemTimezone
@systemNow('Y-m-d H:i:s')
```

### Blade Component

```blade
{{-- System clock component --}}
<x-ui.system-clock />
<x-ui.system-clock :showDate="true" :showTime="true" :showTimezone="true" />
<x-ui.system-clock format="human" />
```

### Service Injection

```php
use App\Services\DateTimeSettingsService;

class MyController
{
    public function index(DateTimeSettingsService $dateTimeService)
    {
        $now = $dateTimeService->now();
        $formatted = $dateTimeService->formatDateTime($now);
        $settings = $dateTimeService->getAll();
    }
}
```

### Helper Class

```php
use App\Helpers\DateTimeHelper;

$date = DateTimeHelper::formatDate($date);
$now = DateTimeHelper::now();
$tz = DateTimeHelper::getTimezone();
```

## Penggunaan di Frontend (React)

### Hook

```jsx
import { useDateTimeSettings } from '@/react/hooks/useDateTimeSettings';

function MyComponent() {
    const {
        settings,
        loading,
        formatDate,
        formatTime,
        formatDateTime,
        formatDateHuman,
        formatDateTimeHuman,
        diffForHumans,
        timezone,
        locale,
    } = useDateTimeSettings();

    return (
        <div>
            <p>Tanggal: {formatDate(new Date())}</p>
            <p>Waktu: {formatTime(new Date())}</p>
            <p>Human: {formatDateHuman(new Date())}</p>
            <p>Relative: {diffForHumans(someDate)}</p>
        </div>
    );
}
```

### Context Provider

```jsx
import { DateTimeProvider, useDateTime } from '@/react/context/DateTimeContext';

// Wrap app with provider
function App() {
    return (
        <DateTimeProvider>
            <MyApp />
        </DateTimeProvider>
    );
}

// Use in components
function MyComponent() {
    const { formatDate, formatDateTime, timezone } = useDateTime();
    // ...
}
```

### Utility Functions

```jsx
import { dateTimeUtils } from '@/react/hooks/useDateTimeSettings';

// Direct usage without hook
const formatted = dateTimeUtils.formatDate(date, 'd/m/Y', 'id');
const human = dateTimeUtils.formatDateHuman(date, 'id');
const relative = dateTimeUtils.diffForHumans(date, 'id');
```

## API Endpoint

```
GET /api/public/datetime-settings
```

Response:
```json
{
    "data": {
        "timezone": "Asia/Jakarta",
        "timezone_offset": 7,
        "timezone_name": "WIB - Waktu Indonesia Barat",
        "date_format": "d/m/Y",
        "time_format": "H:i",
        "datetime_format": "d/m/Y H:i",
        "use_24_hour": true,
        "first_day_of_week": 1,
        "locale": "id",
        "current_time": "2026-01-17T08:30:00+07:00",
        "current_time_formatted": "17/01/2026 08:30"
    }
}
```

## Admin Panel

Pengaturan dapat diubah melalui:
- **URL**: `/admin/settings/system`
- **Menu**: Settings > Pengaturan Sistem > Pengaturan Waktu & Tanggal

## Settings yang Tersedia

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| timezone | string | Asia/Jakarta | Zona waktu sistem |
| date_format | string | d/m/Y | Format tampilan tanggal |
| time_format | string | H:i | Format tampilan waktu |
| datetime_format | string | d/m/Y H:i | Format tampilan tanggal & waktu |
| use_24_hour | boolean | true | Gunakan format 24 jam |
| first_day_of_week | integer | 1 | Hari pertama minggu (0=Minggu, 1=Senin) |
| locale | string | id | Bahasa untuk format tanggal |

## Format Tanggal yang Didukung

| Format | Contoh |
|--------|--------|
| d/m/Y | 17/01/2026 |
| d-m-Y | 17-01-2026 |
| d M Y | 17 Jan 2026 |
| d F Y | 17 Januari 2026 |
| Y-m-d | 2026-01-17 |
| m/d/Y | 01/17/2026 |

## Format Waktu yang Didukung

| Format | Contoh |
|--------|--------|
| H:i | 08:30 |
| H:i:s | 08:30:45 |
| h:i A | 08:30 AM |
| h:i:s A | 08:30:45 AM |
