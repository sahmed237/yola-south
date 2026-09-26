<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $verificationUrl;
    public $system_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(\App\Models\User $user, $password, $verificationUrl)
    {
        $this->user = $user;
        $this->password = $password;
        $this->verificationUrl = $verificationUrl;
        $this->system_settings = \App\Models\Setting::getAllSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . ($this->system_settings['platform_name'] ?? 'URCS'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-welcome',
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
