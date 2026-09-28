<?php

namespace App\Services;

use App\Models\ShopApplicationOtp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate and dispatch a 6-digit OTP to the given email
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

        // Attempt sending email
        try {
            Mail::raw("Your Yola South Local Government verification code for {$purpose} is: {$otpCode}. It expires in 15 minutes. Do not share this code with anyone.", function ($message) use ($email, $purpose) {
                $message->to($email)
                    ->subject("YSLG-IMRS: Your Verification Code ({$purpose})");
            });
            $mailSent = true;
        } catch (\Throwable $e) {
            Log::warning("Failed to send OTP email to {$email}: " . $e->getMessage());
            $mailSent = false;
        }

        return [
            'success' => true,
            'message' => 'Verification code sent to your email.',
            'expires_at' => $expiresAt->toIso8601String(),
            'otp' => app()->environment(['local', 'testing', 'development']) ? $otpCode : null, // Dev helper
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
