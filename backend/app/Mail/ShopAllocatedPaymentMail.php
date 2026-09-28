<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\ShopAllocation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopAllocatedPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public ShopAllocation $allocation;
    public array $system_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(ShopAllocation $allocation)
    {
        $this->allocation = $allocation->loadMissing(['market', 'shop']);
        $this->system_settings = Setting::getAllSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $platformName = $this->system_settings['platform_name'] ?? 'Yola South LGA';

        return new Envelope(
            subject: "Shop Unit Allocated & Payment Notice - Ref: {$this->allocation->application_no} - {$platformName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.shop-allocated-payment',
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
