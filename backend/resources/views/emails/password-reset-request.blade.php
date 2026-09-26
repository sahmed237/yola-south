@extends('layouts.email')

@section('title', 'Reset Password - ' . ($system_settings['platform_name'] ?? 'URCS'))

@section('content')
    <h1>Reset Password</h1>
    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $resetUrl }}" class="button">Reset Password</a>
    </div>

    <p>This password reset link will expire in 60 minutes.</p>
    
    <p>If you did not request a password reset, no further action is required.</p>

    <div class="box" style="margin-top: 32px; background-color: #f8fafc; border-color: #e2e8f0;">
        <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.6;">
            If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
            <span style="word-break: break-all; color: {{ $system_settings['email_primary_color'] ?? '#2563eb' }};">{{ $resetUrl }}</span>
        </p>
    </div>
@endsection
