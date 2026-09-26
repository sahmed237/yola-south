@extends('layouts.email')

@section('title', 'Password Reset - ' . ($system_settings['platform_name'] ?? 'URCS'))

@section('content')
    <h1>Password Reset</h1>
    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    <p>Your account password has been reset by an administrator. Please use the temporary credentials below to log in to your account.</p>
    
    <div class="box">
        <p style="margin-bottom: 8px;"><strong style="color: #94a3b8; font-size: 11px; text-transform: uppercase;">Login Email</strong><br>
        <span style="font-size: 18px; font-weight: 600;">{{ $user->email }}</span></p>
        
        <p style="margin: 0;"><strong style="color: #94a3b8; font-size: 11px; text-transform: uppercase;">New Temporary Password</strong><br>
        <span style="font-size: 18px; font-weight: 600; color: {{ $system_settings['email_primary_color'] ?? '#2563eb' }}; font-family: monospace;">{{ $password }}</span></p>
    </div>

    <p style="color: #ef4444; font-size: 14px; font-weight: 600;">Important: You will be required to change this password immediately after your first login for security purposes.</p>

    <div style="text-align: center;">
        <a href="{{ url('/login') }}" class="button">Go to Login</a>
    </div>

    <p style="margin-top: 32px;">If you did not request this change, please contact your system administrator immediately.</p>
@endsection
