<?php

namespace App\Services;

use App\Jobs\SendInitialCredentialsJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola kredensial user
 *
 * Menyediakan method untuk:
 * - Generate password random
 * - Kirim email kredensial awal
 * - Bulk send untuk multiple users
 */
class CredentialService
{
    /**
     * Default password length
     */
    protected int $passwordLength = 10;

    /**
     * Generate random password yang aman
     */
    public function generatePassword(?int $length = null): string
    {
        $length = $length ?? $this->passwordLength;

        // Generate password dengan kombinasi karakter
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Tanpa I, O (mirip 1, 0)
        $lowercase = 'abcdefghjkmnpqrstuvwxyz'; // Tanpa i, l, o
        $numbers = '23456789'; // Tanpa 0, 1 (mirip O, l)
        $special = '!@#$%&*';

        // Pastikan minimal ada 1 dari setiap jenis
        $password = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Isi sisa dengan random dari semua karakter
        $allChars = $uppercase.$lowercase.$numbers.$special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle password agar tidak predictable
        return str_shuffle($password);
    }

    /**
     * Generate password sederhana (tanpa special char)
     * Lebih mudah diketik user
     */
    public function generateSimplePassword(int $length = 8): string
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $numbers = '23456789';

        $password = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];

        $allChars = $uppercase.$lowercase.$numbers;
        for ($i = 3; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }

    /**
     * Kirim email kredensial awal ke user
     *
     * @param  User  $user  User yang akan dikirim email
     * @param  string|null  $plainPassword  Password plain text (jika null, akan di-generate)
     * @param  bool  $updatePassword  Apakah update password user di database
     * @return array ['success' => bool, 'password' => string, 'message' => string]
     */
    public function sendInitialCredentials(
        User $user,
        ?string $plainPassword = null,
        bool $updatePassword = true
    ): array {
        try {
            // Validasi email
            if (empty($user->email)) {
                return [
                    'success' => false,
                    'password' => null,
                    'message' => 'User tidak memiliki email',
                ];
            }

            // Generate password jika tidak disediakan
            $plainPassword = $plainPassword ?? $this->generateSimplePassword();

            // Update password di database jika diminta
            if ($updatePassword) {
                $user->update([
                    'password' => Hash::make($plainPassword),
                ]);
            }

            // Dispatch job ke queue
            SendInitialCredentialsJob::dispatch($user, $plainPassword);

            Log::info('Initial credentials job dispatched', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return [
                'success' => true,
                'password' => $plainPassword,
                'message' => 'Email kredensial berhasil dijadwalkan untuk dikirim',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to dispatch credentials job', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'password' => null,
                'message' => 'Gagal mengirim email: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Bulk send kredensial ke multiple users
     *
     * @param  array  $userIds  Array of user IDs
     * @param  int  $delayBetween  Delay antar email dalam detik (untuk rate limiting)
     * @return array ['total' => int, 'success' => int, 'failed' => int, 'results' => array]
     */
    public function bulkSendCredentials(array $userIds, int $delayBetween = 5): array
    {
        $results = [
            'total' => count($userIds),
            'success' => 0,
            'failed' => 0,
            'results' => [],
        ];

        $delay = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);

            if (! $user) {
                $results['failed']++;
                $results['results'][] = [
                    'user_id' => $userId,
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ];

                continue;
            }

            $plainPassword = $this->generateSimplePassword();

            try {
                // Update password
                $user->update([
                    'password' => Hash::make($plainPassword),
                ]);

                // Dispatch dengan delay untuk rate limiting
                SendInitialCredentialsJob::dispatch($user, $plainPassword)
                    ->delay(now()->addSeconds($delay));

                $results['success']++;
                $results['results'][] = [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'success' => true,
                    'message' => 'Dijadwalkan',
                ];

                $delay += $delayBetween;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['results'][] = [
                    'user_id' => $userId,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        Log::info('Bulk credentials send completed', [
            'total' => $results['total'],
            'success' => $results['success'],
            'failed' => $results['failed'],
        ]);

        return $results;
    }

    /**
     * Resend kredensial ke user (reset password + kirim email)
     */
    public function resendCredentials(User $user): array
    {
        return $this->sendInitialCredentials($user, null, true);
    }
}
