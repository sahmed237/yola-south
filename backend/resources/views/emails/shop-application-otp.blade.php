@extends('layouts.email')

@section('title', 'Verification Code - ' . ($system_settings['platform_name'] ?? 'YSLG-IMRS'))

@section('content')
    <h1 style="color: #0f172a; font-size: 22px; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em;">
        Shop Allocation Verification Code
    </h1>
    
    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 16px;">
        Hello,
    </p>

    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 20px;">
        You (or someone on your behalf) initiated an online application for a commercial shop / market stall with <strong>{{ $system_settings['platform_name'] ?? 'Yola South Local Government Council' }}</strong>.
    </p>

    <p style="font-size: 14px; color: #64748b; margin-bottom: 12px;">
        Please enter the verification code below on the public portal to verify your email address and complete your application:
    </p>

    <!-- OTP Code Display Box -->
    <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px; padding: 24px; text-align: center; margin: 24px 0;">
        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">
            One-Time Verification Code
        </div>
        <div style="font-family: 'IBM Plex Mono', monospace, ui-monospace, Menlo; font-size: 36px; font-weight: 800; letter-spacing: 8px; color: {{ $system_settings['email_primary_color'] ?? '#16824a' }};">
            {{ $otp }}
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 8px;">
            This code expires in <strong style="color: #475569;">15 minutes</strong>.
        </div>
    </div>

    <!-- Security Advisory Box -->
    <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 16px; border-radius: 6px; margin: 20px 0;">
        <p style="font-size: 12.5px; color: #92400e; margin: 0; line-height: 1.5;">
            <strong>Security Note:</strong> Do not share this code with anyone. Council revenue officers will never request your verification code by phone, SMS, or in person.
        </p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 12.5px; color: #64748b; line-height: 1.5; margin-bottom: 0;">
        If you did not initiate this request on the YSLG shop allocation portal, please disregard this email. Your email remains unlinked to any application.
    </p>
@endsection
