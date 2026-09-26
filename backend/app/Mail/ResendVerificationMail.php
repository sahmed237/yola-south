<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResendVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;
    public $system_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
        $this->system_settings = \App\Models\Setting::getAllSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Verification Link - ' . ($this->system_settings['platform_name'] ?? 'URCS'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.resend-verification',
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
