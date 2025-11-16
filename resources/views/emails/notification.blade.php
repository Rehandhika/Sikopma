<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .logo {
            margin-bottom: 15px;
        }
        .header .logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            padding: 8px;
        }
        .content {
            padding: 30px;
        }
        .notification-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            display: block;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .notification-icon.urgent {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .notification-icon.high {
            background-color: #fef3c7;
            color: #d97706;
        }
        .notification-icon.normal {
            background-color: #dbeafe;
            color: #2563eb;
        }
        .notification-icon.low {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .message {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .message p {
            margin: 0;
            font-size: 16px;
        }
        .data-section {
            margin: 25px 0;
        }
        .data-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-item:last-child {
            border-bottom: none;
        }
        .data-label {
            font-weight: 600;
            color: #6b7280;
        }
        .data-value {
            color: #111827;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .action-button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .footer .links {
            margin-top: 10px;
        }
        .footer .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        .footer .links a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .header {
                padding: 20px;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ $appLogo }}" alt="{{ $appName }}">
                @else
                    <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; color: #667eea;">
                        {{ substr($appName, 0, 2) }}
                    </div>
                @endif
            </div>
            <h1>{{ $title }}</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Notification Icon -->
            @switch($notificationType)
                @case('urgent')
                    <div class="notification-icon urgent">🚨</div>
                    @break
                @case('high')
                    <div class="notification-icon high">⚠️</div>
                    @break
                @case('low')
                    <div class="notification-icon low">ℹ️</div>
                    @break
                @default
                    <div class="notification-icon normal">🔔</div>
            @endswitch

            <!-- Message -->
            <div class="message">
                <p>{{ $message }}</p>
            </div>

            <!-- Additional Data -->
            @if(!empty($data))
                <div class="data-section">
                    @foreach($data as $key => $value)
                        @if(!is_array($value) && !is_object($value))
                            <div class="data-item">
                                <span class="data-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                <span class="data-value">{{ $value }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <!-- Action Button -->
            @if(isset($data['action_url']))
                <div style="text-align: center;">
                    <a href="{{ $data['action_url'] }}" class="action-button">
                        {{ $data['action_text'] ?? 'Lihat Detail' }}
                    </a>
                </div>
            @endif

            <!-- Additional Information -->
            @if($notificationType === 'attendance' && isset($data['schedule_info']))
                <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; padding: 15px; border-radius: 6px; margin: 20px 0;">
                    <h4 style="margin: 0 0 10px 0; color: #0c4a6e;">📅 Informasi Jadwal</h4>
                    <p style="margin: 0; color: #0c4a6e;">{{ $data['schedule_info'] }}</p>
                </div>
            @endif

            @if($notificationType === 'swap' && isset($data['swap_info']))
                <div style="background-color: #fef3c7; border: 1px solid #fde047; padding: 15px; border-radius: 6px; margin: 20px 0;">
                    <h4 style="margin: 0 0 10px 0; color: #713f12;">🔄 Informasi Tukar Shift</h4>
                    <p style="margin: 0; color: #713f12;">{{ $data['swap_info'] }}</p>
                </div>
            @endif

            @if($notificationType === 'penalty' && isset($data['penalty_info']))
                <div style="background-color: #fee2e2; border: 1px solid #fca5a5; padding: 15px; border-radius: 6px; margin: 20px 0;">
                    <h4 style="margin: 0 0 10px 0; color: #7f1d1d;">⚠️ Informasi Penalti</h4>
                    <p style="margin: 0; color: #7f1d1d;">{{ $data['penalty_info'] }}</p>
                </div>
            @endif

            <!-- Help Text -->
            <div style="text-align: center; margin: 30px 0;">
                <p style="color: #6b7280; font-size: 14px;">
                    Jika Anda memiliki pertanyaan, silakan hubungi admin sistem.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
            <div class="links">
                <a href="{{ $appUrl }}">Website</a>
                <a href="{{ $appUrl }}/support">Support</a>
                <a href="{{ $appUrl }}/privacy">Privacy</a>
            </div>
            <p style="margin-top: 10px; font-size: 12px;">
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
            </p>
        </div>
    </div>
</body>
</html>
