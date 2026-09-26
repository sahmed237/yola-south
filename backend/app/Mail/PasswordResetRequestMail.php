<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;
    public $resetUrl;
    public $system_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $this->system_settings = \App\Models\Setting::getAllSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Request - ' . ($this->system_settings['platform_name'] ?? 'URCS'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-request',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
