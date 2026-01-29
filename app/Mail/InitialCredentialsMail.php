<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * InitialCredentialsMail
 *
 * Email untuk mengirim kredensial awal (NIM & password) ke user baru.
 * Implements ShouldQueue untuk pengiriman async via queue.
 */
class InitialCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Jumlah percobaan pengiriman jika gagal
     */
    public int $tries = 3;

    /**
     * Waktu timeout untuk job (dalam detik)
     */
    public int $timeout = 60;

    /**
     * Delay antar percobaan (dalam detik)
     */
    public int $backoff = 30;

    public User $user;

    public string $plainPassword;

    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $plainPassword)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
        $this->loginUrl = config('app.url').'/login';

        // Set queue name untuk prioritas
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Selamat Datang! Kredensial Akun '.config('app.name').' Anda',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.initial-credentials',
            with: [
                'user' => $this->user,
                'nim' => $this->user->nim,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => $this->loginUrl,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
                'supportEmail' => config('mail.from.address'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log error untuk monitoring
        \Log::error('Failed to send initial credentials email', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
