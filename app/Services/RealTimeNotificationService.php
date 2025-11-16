<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Mail\NotificationEmail;
use Carbon\Carbon;

class RealTimeNotificationService
{
    protected $pusherAppKey;
    protected $pusherAppSecret;
    protected $pusherAppId;
    protected $pusherCluster;

    public function __construct()
    {
        $this->pusherAppKey = config('broadcasting.connections.pusher.key');
        $this->pusherAppSecret = config('broadcasting.connections.pusher.secret');
        $this->pusherAppId = config('broadcasting.connections.pusher.app_id');
        $this->pusherCluster = config('broadcasting.connections.pusher.options.cluster');
    }

    /**
     * Send real-time notification
     */
    public function sendRealTimeNotification(User $user, string $type, string $title, string $message, array $data = [], array $options = []): bool
    {
        try {
            // Create database notification
            $notification = NotificationService::send($user, $type, $title, $message, $data, $options['action_url'] ?? null);

            // Send real-time push notification
            $pushResult = $this->sendPushNotification($user, [
                'id' => $notification->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'created_at' => $notification->created_at->toISOString(),
            ]);

            // Send email if enabled
            if ($options['send_email'] ?? false) {
                $this->queueEmailNotification($user, $title, $message, $data);
            }

            // Send SMS if enabled and phone number exists
            if ($options['send_sms'] ?? false && $user->phone) {
                $this->queueSMSNotification($user, $message);
            }

            Log::info('Real-time notification sent', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'type' => $type,
                'push_result' => $pushResult,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send real-time notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send push notification via Pusher
     */
    protected function sendPushNotification(User $user, array $notificationData): bool
    {
        try {
            $channel = 'user-' . $user->id;
            $event = 'notification';

            $response = Http::post("https://api-{$this->pusherCluster}.pusherapp.com/apps/{$this->pusherAppId}/events", [
                'name' => $event,
                'channel' => $channel,
                'data' => json_encode($notificationData),
            ], [
                'Authorization' => 'Basic ' . base64_encode($this->pusherAppKey . ':' . $this->pusherAppSecret),
                'Content-Type' => 'application/json',
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Queue email notification
     */
    protected function queueEmailNotification(User $user, string $title, string $message, array $data): void
    {
        try {
            Queue::push(function ($job) use ($user, $title, $message, $data) {
                try {
                    Mail::to($user->email)->send(new NotificationEmail($title, $message, $data));
                    Log::info('Email notification sent', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'title' => $title,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send email notification', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
                $job->delete();
            });

        } catch (\Exception $e) {
            Log::error('Failed to queue email notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Queue SMS notification
     */
    protected function queueSMSNotification(User $user, string $message): void
    {
        try {
            Queue::push(function ($job) use ($user, $message) {
                try {
                    $this->sendSMS($user->phone, $message);
                    Log::info('SMS notification sent', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS notification', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'error' => $e->getMessage()
                    ]);
                }
                $job->delete();
            });

        } catch (\Exception $e) {
            Log::error('Failed to queue SMS notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS via provider (simulated)
     */
    protected function sendSMS(string $phoneNumber, string $message): bool
    {
        try {
            // In real implementation, integrate with SMS provider (Twilio, Vonage, etc.)
            // For now, simulate SMS sending
            
            // Validate phone number
            if (!preg_match('/^[0-9]{10,15}$/', $phoneNumber)) {
                throw new BusinessException('Nomor telepon tidak valid');
            }

            // Simulate API call
            $response = Http::post('https://api.sms-provider.com/send', [
                'to' => $phoneNumber,
                'message' => $message,
                'api_key' => config('services.sms.api_key'),
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send bulk notifications
     */
    public function sendBulkNotifications(array $userIds, string $type, string $title, string $message, array $data = [], array $options = []): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($userIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                $success = $this->sendRealTimeNotification($user, $type, $title, $message, $data, $options);
                
                if ($success) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "User {$userId}: " . $e->getMessage();
            }
        }

        Log::info('Bulk notifications completed', [
            'total_users' => count($userIds),
            'sent' => $results['sent'],
            'failed' => $results['failed'],
        ]);

        return $results;
    }

    /**
     * Send scheduled notification
     */
    public function scheduleNotification(User $user, string $type, string $title, string $message, Carbon $scheduleAt, array $data = [], array $options = []): bool
    {
        try {
            Queue::later($scheduleAt, function ($job) use ($user, $type, $title, $message, $data, $options) {
                try {
                    $this->sendRealTimeNotification($user, $type, $title, $message, $data, $options);
                    Log::info('Scheduled notification sent', [
                        'user_id' => $user->id,
                        'scheduled_at' => $scheduleAt->toISOString(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send scheduled notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
                $job->delete();
            });

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to schedule notification', [
                'user_id' => $user->id,
                'scheduled_at' => $scheduleAt->toISOString(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send location-based notification
     */
    public function sendLocationNotification(array $userIds, string $type, string $title, string $message, float $latitude, float $longitude, float $radius, array $data = []): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Get users within radius
        $nearbyUsers = $this->getUsersWithinRadius($userIds, $latitude, $longitude, $radius);

        foreach ($nearbyUsers as $user) {
            try {
                $enhancedData = array_merge($data, [
                    'location' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'radius' => $radius,
                    ]
                ]);

                $success = $this->sendRealTimeNotification($user, $type, $title, $message, $enhancedData);
                
                if ($success) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "User {$user->id}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get users within radius (simplified)
     */
    protected function getUsersWithinRadius(array $userIds, float $latitude, float $longitude, float $radius): array
    {
        // In real implementation, use spatial queries or geolocation service
        // For now, return all users (simplified)
        return User::whereIn('id', $userIds)
            ->where('status', 'active')
            ->get()
            ->toArray();
    }

    /**
     * Send role-based notification
     */
    public function sendRoleNotification(array $roles, string $type, string $title, string $message, array $data = [], array $options = []): array
    {
        $users = User::whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('name', $roles);
        })->where('status', 'active')->get();

        $userIds = $users->pluck('id')->toArray();
        
        return $this->sendBulkNotifications($userIds, $type, $title, $message, $data, $options);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        try {
            $notifications = Notification::whereBetween('created_at', [$startDate, $endDate])->get();

            return [
                'total_sent' => $notifications->count(),
                'total_read' => $notifications->whereNotNull('read_at')->count(),
                'read_rate' => $notifications->count() > 0 
                    ? round(($notifications->whereNotNull('read_at')->count() / $notifications->count()) * 100, 2)
                    : 0,
                'by_type' => $notifications->groupBy('type')->map->count(),
                'by_date' => $notifications->groupBy(function ($notification) {
                    return $notification->created_at->format('Y-m-d');
                })->map->count(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get notification stats', [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'error' => $e->getMessage()
            ]);
            return [
                'total_sent' => 0,
                'total_read' => 0,
                'read_rate' => 0,
                'by_type' => [],
                'by_date' => [],
            ];
        }
    }

    /**
     * Cleanup old notifications
     */
    public function cleanupOldNotifications(int $daysOld = 90): int
    {
        try {
            $deletedCount = NotificationService::cleanupOldNotifications($daysOld);
            
            Log::info('Old notifications cleaned up', [
                'deleted_count' => $deletedCount,
                'days_old' => $daysOld,
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Failed to cleanup old notifications', [
                'days_old' => $daysOld,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Test notification system
     */
    public function testSystem(): array
    {
        $results = [
            'push_notification' => false,
            'email_notification' => false,
            'sms_notification' => false,
            'database_notification' => false,
        ];

        try {
            // Test database notification
            $testUser = User::first();
            if ($testUser) {
                $notification = NotificationService::send($testUser, 'test', 'Test Notification', 'This is a test notification');
                $results['database_notification'] = $notification->id ? true : false;
            }

        } catch (\Exception $e) {
            Log::error('Notification system test failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }
}
