<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\ShopApplicationOtp;
use App\Mail\ShopApplicationOtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate and dispatch a 6-digit OTP to the given email
     * Uses the database-configured SMTP settings from http://yls.local/admin/settings?group=email
     */
    public function generateAndSendOtp(string $email, string $purpose = 'Shop Allocation Application'): array
    {
        // Invalidate old unverified OTPs for this email
        ShopApplicationOtp::where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        $otpCode = (string) rand(100000, 999999);
        $expiresAt = now()->addMinutes(15);

        $otpRecord = ShopApplicationOtp::create([
            'email' => $email,
            'otp' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Dynamically apply database SMTP configuration
        try {
            Setting::configureMailer();
        } catch (\Throwable $e) {
            Log::warning("Could not apply dynamic mail settings: " . $e->getMessage());
        }

        // Attempt sending email with official Mailable & template
        try {
            Mail::to($email)->send(new ShopApplicationOtpMail($otpCode, $email, $purpose));
            $mailSent = true;
        } catch (\Throwable $e) {
            Log::warning("Failed to send OTP email to {$email}: " . $e->getMessage());
            $mailSent = false;
        }

        return [
            'success' => true,
            'message' => 'Verification code sent to your email address.',
            'expires_at' => $expiresAt->toIso8601String(),
            'mail_sent' => $mailSent,
        ];
    }

    /**
     * Verify the OTP provided
     */
    public function verifyOtp(string $email, string $otp): bool
    {
        $record = ShopApplicationOtp::where('email', $email)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$record) {
            return false;
        }

        $record->update(['verified_at' => now()]);
        return true;
    }
}
