@extends('layouts.email')

@section('title', 'Action Required - ' . $allocation->application_no)

@section('content')
    <h1 style="color: #0f172a; font-size: 22px; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em;">
        Action Required on Your Shop Application
    </h1>

    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 16px;">
        Dear <strong>{{ $allocation->applicant_name }}</strong>,
    </p>

    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 20px;">
        During the review of your commercial shop / market stall application (Tracking Reference: <strong>{{ $allocation->application_no }}</strong>) at <strong>{{ $allocation->market->name }}</strong>, our verification officers noted that additional information or updated documentation is needed before your application can proceed.
    </p>

    <!-- Officer Remarks / Instructions Box -->
    <div style="background-color: #fffbeb; border: 1.5px solid #fde68a; border-left: 5px solid #f59e0b; border-radius: 12px; padding: 20px; margin: 24px 0;">
        <div style="font-size: 11px; font-weight: 800; color: #92400e; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">
            Officer's Request & Instructions
        </div>
        <div style="font-size: 14.5px; color: #78350f; line-height: 1.6; font-weight: 500;">
            {!! nl2br(e($requestNotes)) !!}
        </div>
    </div>

    <!-- Call to Action -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('public.shop-application.edit', ['application_no' => $allocation->application_no, 'token' => $allocation->tracking_hash]) }}" 
           class="button" 
           style="background-color: {{ $system_settings['email_primary_color'] ?? ($system_settings['theme_primary_color'] ?? '#0b6b3a') }}; color: #ffffff !important; padding: 14px 32px; border-radius: 12px; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            Update Your Application Now &rarr;
        </a>
        <p style="font-size: 12px; color: #64748b; margin-top: 10px;">
            Tracking ID: <strong style="font-family: monospace; color: #0f172a;">{{ $allocation->application_no }}</strong>
        </p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 12.5px; color: #64748b; line-height: 1.5; margin-bottom: 0;">
        If you have questions or encounter any issues updating your details, please reply directly to this email or visit the Council Commercial Revenue Department citing reference <strong>{{ $allocation->application_no }}</strong>.
    </p>
@endsection
