<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $message;
    public $data;
    public $notificationType;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, string $message, array $data = [], string $notificationType = 'general')
    {
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->notificationType = $notificationType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'title' => $this->title,
                'message' => $this->message,
                'data' => $this->data,
                'notificationType' => $this->notificationType,
                'appLogo' => asset('images/logo.png'),
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($this->title)
                    ->view('emails.notification');

        // Add priority based on notification type
        switch ($this->notificationType) {
            case 'urgent':
                $email->priority(1);
                break;
            case 'high':
                $email->priority(3);
                break;
            case 'low':
                $email->priority(5);
                break;
            default:
                $email->priority(3);
        }

        return $email;
    }
}
