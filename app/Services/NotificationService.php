<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send notification to user
     */
    public static function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null
    ): Notification {
        try {
            $notification = DB::transaction(function () use ($user, $type, $title, $message, $data, $actionUrl) {
                return Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data ? json_encode($data) : null,
                    'action_url' => $actionUrl,
                ]);
            });

            Log::info('Notification sent', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title
            ]);

            return $notification;

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new BusinessException('Gagal mengirim notifikasi', 'NOTIFICATION_SEND_FAILED');
        }
    }

    /**
     * Send notification to multiple users with bulk insert
     */
    public static function sendToMany(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null
    ): void {
        try {
            $notifications = [];
            $now = now();
            
            foreach ($userIds as $userId) {
                $notifications[] = [
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data ? json_encode($data) : null,
                    'action_url' => $actionUrl,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            
            DB::transaction(function () use ($notifications) {
                Notification::insert($notifications);
            });

            Log::info('Bulk notifications sent', [
                'count' => count($notifications),
                'type' => $type,
                'title' => $title
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send bulk notifications', [
                'user_ids' => $userIds,
                'title' => $title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new BusinessException('Gagal mengirim notifikasi bulk', 'BULK_NOTIFICATION_SEND_FAILED');
        }
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(Notification $notification): void
    {
        try {
            if ($notification->read_at) {
                return; // Already read
            }

            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            Log::info('Notification marked as read', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark all user notifications as read
     */
    public static function markAllAsRead(User $user): void
    {
        try {
            $updated = $user->notifications()
                ->whereNull('read_at')
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            Log::info('All notifications marked as read', [
                'user_id' => $user->id,
                'count' => $updated
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(User $user): int
    {
        try {
            return $user->notifications()
                ->whereNull('read_at')
                ->count();
        } catch (\Exception $e) {
            Log::error('Failed to get unread count', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Create attendance-related notification
     */
    public static function createAttendanceNotification(User $user, string $type, array $data = []): Notification
    {
        $messages = [
            'check_in' => [
                'title' => 'Check-in Berhasil',
                'message' => 'Anda telah berhasil check-in pada ' . now()->format('H:i'),
                'type' => 'success'
            ],
            'late' => [
                'title' => 'Check-in Terlambat',
                'message' => 'Anda check-in terlambat. Penalti telah diterapkan.',
                'type' => 'warning'
            ],
            'absent' => [
                'title' => 'Absen Otomatis',
                'message' => 'Anda tidak check-in pada jadwal yang ditentukan.',
                'type' => 'error'
            ],
        ];

        $notificationData = $messages[$type] ?? $messages['check_in'];
        
        return self::send(
            $user,
            $notificationData['type'],
            $notificationData['title'],
            $notificationData['message'],
            array_merge($data, ['category' => 'attendance'])
        );
    }

    /**
     * Create swap request notification
     */
    public static function createSwapNotification(User $user, string $type, array $data = []): Notification
    {
        $messages = [
            'request_created' => [
                'title' => 'Permintaan Tukar Shift Dibuat',
                'message' => 'Permintaan tukar shift Anda telah dibuat dan menunggu persetujuan.',
                'type' => 'info'
            ],
            'request_received' => [
                'title' => 'Permintaan Tukar Shift Masuk',
                'message' => 'Ada permintaan tukar shift yang menunggu persetujuan Anda.',
                'type' => 'info'
            ],
            'request_approved' => [
                'title' => 'Permintaan Tukar Shift Disetujui',
                'message' => 'Permintaan tukar shift telah disetujui.',
                'type' => 'success'
            ],
            'request_rejected' => [
                'title' => 'Permintaan Tukar Shift Ditolak',
                'message' => 'Permintaan tukar shift telah ditolak.',
                'type' => 'error'
            ],
        ];

        $notificationData = $messages[$type] ?? $messages['request_created'];
        
        return self::send(
            $user,
            $notificationData['type'],
            $notificationData['title'],
            $notificationData['message'],
            array_merge($data, ['category' => 'swap'])
        );
    }

    /**
     * Create leave request notification
     */
    public static function createLeaveNotification(User $user, string $type, array $data = []): Notification
    {
        $messages = [
            'request_created' => [
                'title' => 'Pengajuan Cuti Dibuat',
                'message' => 'Pengajuan cuti Anda telah dibuat dan menunggu persetujuan.',
                'type' => 'info'
            ],
            'request_approved' => [
                'title' => 'Pengajuan Cuti Disetujui',
                'message' => 'Pengajuan cuti Anda telah disetujui.',
                'type' => 'success'
            ],
            'request_rejected' => [
                'title' => 'Pengajuan Cuti Ditolak',
                'message' => 'Pengajuan cuti Anda telah ditolak.',
                'type' => 'error'
            ],
        ];

        $notificationData = $messages[$type] ?? $messages['request_created'];
        
        return self::send(
            $user,
            $notificationData['type'],
            $notificationData['title'],
            $notificationData['message'],
            array_merge($data, ['category' => 'leave'])
        );
    }

    /**
     * Cleanup old notifications
     */
    public static function cleanupOldNotifications(int $daysOld = 30): int
    {
        try {
            $cutoffDate = Carbon::now()->subDays($daysOld);
            
            $deletedCount = Notification::where('created_at', '<', $cutoffDate)
                ->whereNotNull('read_at') // Only delete read notifications
                ->delete();

            Log::info('Old notifications cleaned up', [
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->toDateString()
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Failed to cleanup old notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }
}
