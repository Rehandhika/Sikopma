<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * ScheduleNotification Mailable
 *
 * Specialized email notification for schedule-related events.
 * Supports multiple notification types:
 * - assignment_added: User assigned to a new slot
 * - assignment_removed: User removed from a slot
 * - assignment_updated: User's assignment changed
 * - schedule_published: New schedule published with user's assignments
 */
class ScheduleNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;

    public string $message;

    public array $data;

    public string $notificationType;

    public string $priority;

    /**
     * Create a new message instance.
     *
     * @param  string  $title  Email subject/title
     * @param  string  $message  Main notification message
     * @param  array  $data  Additional data for the notification
     * @param  string  $notificationType  Type of notification (assignment_added, assignment_removed, etc.)
     * @param  string  $priority  Email priority (normal, high, urgent, low)
     */
    public function __construct(
        string $title,
        string $message,
        array $data = [],
        string $notificationType = 'general',
        string $priority = 'normal'
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->notificationType = $notificationType;
        $this->priority = $priority;
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
            view: 'emails.schedule-notification',
            with: [
                'title' => $this->title,
                'message' => $this->message,
                'data' => $this->data,
                'notificationType' => $this->notificationType,
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
            ->view('emails.schedule-notification');

        // Add priority based on notification type and priority setting
        switch ($this->priority) {
            case 'urgent':
                $email->priority(1);
                break;
            case 'high':
                $email->priority(2);
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
