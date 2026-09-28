<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopApplicationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $emailAddress;
    public string $purpose;
    public array $system_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $emailAddress, string $purpose = 'Shop Allocation Application')
    {
        $this->otp = $otp;
        $this->emailAddress = $emailAddress;
        $this->purpose = $purpose;
        $this->system_settings = Setting::getAllSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $platformName = $this->system_settings['platform_name'] ?? 'YSLG-IMRS';

        return new Envelope(
            subject: "Verification Code: {$this->otp} - {$this->purpose} ({$platformName})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.shop-application-otp',
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
