@extends('layouts.email')

@section('title', 'Email Verified Successfully')

@section('content')
    <div style="text-align: center; margin-bottom: 32px;">
        <div style="display: inline-block; width: 64px; height: 64px; background-color: #f0fdf4; border-radius: 50%; line-height: 64px; text-align: center; margin-bottom: 24px;">
            <span style="font-size: 32px; color: #16a34a;">✓</span>
        </div>
        <h1 style="margin: 0; color: #1e293b;">Verification Complete!</h1>
    </div>

    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    <p>Thank you for verifying your email address. Your account on the <strong>{{ $system_settings['platform_name'] ?? 'Revenue Collection System' }}</strong> is now fully active and secure.</p>
    
    <p>You can now access all features of the platform using your credentials.</p>

    <div style="text-align: center; margin-top: 32px;">
        <a href="{{ url('/login') }}" class="button">Log In to Your Account</a>
    </div>

    <p style="margin-top: 32px; border-top: 1px solid #f1f5f9; pt-24; font-size: 13px; color: #64748b;">
        If you did not perform this action, please contact our security team immediately.
    </p>
@endsection
