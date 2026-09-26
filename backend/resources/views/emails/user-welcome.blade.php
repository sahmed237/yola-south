@extends('layouts.email')

@section('title', 'Welcome to ' . ($system_settings['platform_name'] ?? 'URCS'))

@section('content')
    <h1>Welcome to the Platform!</h1>
    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    <p>An account has been created for you on the <strong>{{ $system_settings['platform_name'] ?? 'Revenue Collection System' }}</strong>. You can now log in using the credentials below:</p>
    
    <div class="box">
        <p style="margin-bottom: 8px;"><strong style="color: #94a3b8; font-size: 11px; text-transform: uppercase;">Login Email</strong><br>
        <span style="font-size: 18px; font-weight: 600;">{{ $user->email }}</span></p>
        
        <p style="margin: 0;"><strong style="color: #94a3b8; font-size: 11px; text-transform: uppercase;">Temporary Password</strong><br>
        <span style="font-size: 18px; font-weight: 600; color: {{ $system_settings['email_primary_color'] ?? '#2563eb' }}; font-family: monospace;">{{ $password }}</span></p>
    </div>

    <p style="color: #ef4444; font-size: 14px; font-weight: 600;">Security Note: You will be required to change this password immediately after your first login.</p>
    
    <p>Please click the button below to verify your email address. This link will expire in <strong>48 hours</strong>.</p>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
    </div>

    <div style="text-align: center;">
        <a href="{{ url('/login') }}" class="button" style="background-color: #f8fafc; border: 1px solid #e2e8f0; color: #475569;">Go to Login Page</a>
    </div>

    <p style="margin-top: 32px;">If you have any questions, please feel free to reach out to our support team.</p>
@endsection
