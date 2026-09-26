<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject;
    public $body;
    public $system_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $subject, $body)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->body = $body;
        $this->system_settings = \App\Models\Setting::getAllSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject . ' - ' . ($this->system_settings['platform_name'] ?? 'URCS'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.general-user',
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
}
