<?php

namespace App\Jobs;

use App\Mail\InitialCredentialsMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job untuk mengirim email kredensial awal
 *
 * Best practice:
 * - Menggunakan queue untuk async processing
 * - Retry mechanism dengan backoff
 * - Logging untuk monitoring
 * - Rate limiting untuk mencegah spam
 */
class SendInitialCredentialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal
     */
    public int $tries = 3;

    /**
     * Timeout dalam detik
     */
    public int $timeout = 120;

    /**
     * Delay antar retry (dalam detik)
     */
    public int $backoff = 60;

    /**
     * Delete job jika model tidak ditemukan
     */
    public bool $deleteWhenMissingModels = true;

    protected User $user;

    protected string $plainPassword;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $plainPassword)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;

        // Set queue name
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Validasi user masih ada dan punya email
        if (! $this->user->email) {
            Log::warning('Cannot send credentials: User has no email', [
                'user_id' => $this->user->id,
            ]);

            return;
        }

        try {
            Mail::to($this->user->email)
                ->send(new InitialCredentialsMail($this->user, $this->plainPassword));

            Log::info('Initial credentials email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send initial credentials email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw untuk trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendInitialCredentialsJob failed permanently', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Optional: Kirim notifikasi ke admin
        // Notification::send(User::admins()->get(), new JobFailedNotification($this->user, $exception));
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(24);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'email',
            'credentials',
            'user:'.$this->user->id,
        ];
    }
}
