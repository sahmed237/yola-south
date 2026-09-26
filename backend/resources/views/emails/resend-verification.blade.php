@extends('layouts.email')

@section('title', 'New Verification Link')

@section('content')
    <h1>New Verification Link Requested</h1>
    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    <p>You (or someone on your behalf) requested a new verification link for your account on <strong>{{ $system_settings['platform_name'] ?? 'Revenue Collection System' }}</strong>.</p>
    
    <p>Please click the button below to verify your email address. This link will expire in <strong>48 hours</strong>.</p>

    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
    </div>

    <p style="font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 24px;">
        If you did not request this link, you can safely ignore this email. Your account remains secure.
    </p>
@endsection
