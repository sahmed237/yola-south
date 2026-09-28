@extends('layouts.email')

@section('title', 'Application Received - ' . $allocation->application_no)

@section('content')
    <h1 style="color: #0f172a; font-size: 22px; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em;">
        Shop Allocation Application Received
    </h1>

    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 16px;">
        Dear <strong>{{ $allocation->applicant_name }}</strong>,
    </p>

    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 20px;">
        Thank you for applying for a commercial shop / market stall space with <strong>{{ $system_settings['platform_name'] ?? 'Yola South Local Government Council' }}</strong>. Your application has been successfully submitted and registered in our Commercial Premises Registry.
    </p>

    <!-- Prominent Tracking Reference Box -->
    <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 24px; text-align: center; margin: 24px 0;">
        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">
            Application Tracking Reference
        </div>
        <div style="font-family: 'IBM Plex Mono', monospace, ui-monospace, Menlo, Consolas; font-size: 26px; font-weight: 800; letter-spacing: 2px; color: {{ $system_settings['email_primary_color'] ?? ($system_settings['theme_primary_color'] ?? '#0b6b3a') }};">
            {{ $allocation->application_no }}
        </div>
        <p style="font-size: 12px; color: #64748b; margin-top: 8px; margin-bottom: 16px;">
            Keep this tracking reference safe to check real-time approval progress and print your allocation card.
        </p>
        <div style="text-align: center;">
            <a href="{{ route('public.shop-application.track-status', ['application_no' => $allocation->application_no, 'token' => $allocation->tracking_hash]) }}" 
               class="button" 
               style="background-color: {{ $system_settings['email_primary_color'] ?? ($system_settings['theme_primary_color'] ?? '#0b6b3a') }}; color: #ffffff !important; padding: 12px 28px; border-radius: 10px; text-decoration: none; display: inline-block; font-weight: bold; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">
                Track Live Status Online &rarr;
            </a>
        </div>
    </div>

    <!-- Application Summary Table -->
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; margin: 24px 0;">
        <div style="background-color: #f1f5f9; padding: 12px 18px; border-bottom: 1px solid #e2e8f0;">
            <strong style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #475569;">Application Summary Details</strong>
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; line-height: 1.5;">
            <tr>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b; width: 40%;">Tracking Reference:</td>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-family: monospace; color: #0f172a;">{{ $allocation->application_no }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b;">Applicant Full Name:</td>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #0f172a;">{{ $allocation->applicant_name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b;">Mobile Phone:</td>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-family: monospace; color: #0f172a;">{{ $allocation->applicant_phone }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b;">Target Market:</td>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #0f172a;">
                    {{ $allocation->market->name }} ({{ $allocation->market->ward_name }} Ward)
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b;">Line of Trade:</td>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #0f172a;">{{ $allocation->trade_type }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b;">Requested Space / Unit:</td>
                <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; color: #0f172a;">
                    @if($allocation->shop)
                        {{ $allocation->shop->block_name }} &middot; Unit {{ $allocation->shop->shop_number }} ({{ $allocation->shop->shop_code }})
                    @else
                        Standard Unit ({{ $allocation->requested_size ?? '3.0 × 4.0 m' }})
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 18px; color: #64748b;">Submission Timestamp:</td>
                <td style="padding: 10px 18px; color: #0f172a;">{{ $allocation->created_at->format('d M Y, h:i A') }}</td>
            </tr>
        </table>
    </div>

    <!-- 7-Stage Process Advisory -->
    <div style="background-color: #f8fafc; border-left: 4px solid {{ $system_settings['email_primary_color'] ?? ($system_settings['theme_primary_color'] ?? '#0b6b3a') }}; padding: 14px 16px; border-radius: 6px; margin: 20px 0;">
        <div style="font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 6px;">
            What Happens Next? (7-Stage Processing Pipeline)
        </div>
        <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.6;">
            1. <strong>Application Received</strong> (Completed) &rarr; 
            2. <strong>Identity & NIN Review</strong> &rarr; 
            3. <strong>Officer Field Inspection</strong> &rarr; 
            4. <strong>Directorate Approval</strong> &rarr; 
            5. <strong>Formal Unit Assignment</strong> &rarr; 
            6. <strong>Statutory Settlement</strong> &rarr; 
            7. <strong>Certificate & Inspection Card Handover</strong>.
        </p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin-bottom: 0;">
        For questions or inquiries regarding your shop application, please contact the Commercial Directorate at <a href="mailto:{{ $system_settings['support_email'] ?? 'support@urcs.gov.ng' }}" style="color: {{ $system_settings['email_primary_color'] ?? '#0b6b3a' }};">{{ $system_settings['support_email'] ?? 'support@urcs.gov.ng' }}</a> or call <strong>{{ $system_settings['contact_phone'] ?? '08123456789' }}</strong> citing your Tracking Reference <strong>{{ $allocation->application_no }}</strong>.
    </p>
@endsection
