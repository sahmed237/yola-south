<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\ShopAllocation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopApplicationUpdateRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public ShopAllocation $allocation;
    public string $requestNotes;
    public array $system_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(ShopAllocation $allocation, string $requestNotes)
    {
        $this->allocation = $allocation->loadMissing(['market', 'shop']);
        $this->requestNotes = $requestNotes;
        $this->system_settings = Setting::getAllSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $platformName = $this->system_settings['platform_name'] ?? 'YSLG Revenue Portal';

        return new Envelope(
            subject: "Action Required: Update Requested for Application {$this->allocation->application_no} - {$platformName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.shop-application-update-request',
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
