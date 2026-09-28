@extends('layouts.email')

@section('title', 'Shop Unit Allocated - ' . $allocation->application_no)

@section('content')
    <h1 style="color: #0f172a; font-size: 22px; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em;">
        Commercial Shop Unit Allocated &amp; Payment Notice
    </h1>

    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 16px;">
        Dear <strong>{{ $allocation->applicant_name }}</strong>,
    </p>

    <p style="font-size: 15px; color: {{ $system_settings['email_text_color'] ?? '#334155' }}; line-height: 1.6; margin-bottom: 20px;">
        We are pleased to inform you that following verification and approval by the Revenue Directorate, a commercial shop unit has been officially assigned to you at <strong>{{ $allocation->market->name }}</strong>.
    </p>

    <!-- Allocated Unit Card -->
    <div style="background-color: #ecfdf5; border: 1.5px solid #a7f3d0; border-left: 5px solid #059669; border-radius: 12px; padding: 20px; margin: 24px 0;">
        <div style="font-size: 11px; font-weight: 800; color: #065f46; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">
            Assigned Commercial Unit Details
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; line-height: 1.6;">
            <tr>
                <td style="color: #047857; width: 40%; padding: 4px 0;">Market Facility:</td>
                <td style="font-weight: 700; color: #064e3b; padding: 4px 0;">{{ $allocation->market->name }} ({{ $allocation->market->ward_name ?? 'Yola South' }})</td>
            </tr>
            <tr>
                <td style="color: #047857; padding: 4px 0;">Assigned Unit:</td>
                <td style="font-weight: 700; color: #064e3b; font-family: monospace; font-size: 14px; padding: 4px 0;">
                    {{ $allocation->shop->block_name }} &middot; Unit {{ $allocation->shop->shop_number }}
                </td>
            </tr>
            <tr>
                <td style="color: #047857; padding: 4px 0;">Unit Code &amp; Size:</td>
                <td style="font-weight: 600; color: #064e3b; padding: 4px 0;">
                    {{ $allocation->shop->shop_code }} ({{ $allocation->shop->size }})
                </td>
            </tr>
            <tr>
                <td style="color: #047857; padding: 4px 0;">Line of Trade:</td>
                <td style="font-weight: 600; color: #064e3b; padding: 4px 0;">{{ $allocation->trade_type }}</td>
            </tr>
        </table>
    </div>

    <!-- Financial Breakdown & Payment Due -->
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; margin: 24px 0;">
        <div style="background-color: #f8fafc; padding: 12px 18px; border-bottom: 1px solid #e2e8f0;">
            <strong style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #475569;">
                Invoice &amp; Settlement Details
            </strong>
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; line-height: 1.5;">
            <tr>
                <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b;">Allocation &amp; Documentation Fee:</td>
                <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; text-align: right; font-family: monospace; font-weight: bold; color: #1e293b;">
                    ₦{{ number_format($allocation->allocation_fee, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; color: #64748b;">Monthly Rent Rate:</td>
                <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; text-align: right; font-family: monospace; font-weight: bold; color: #1e293b;">
                    ₦{{ number_format($allocation->rent_amount, 2) }}
                </td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td style="padding: 14px 18px; font-weight: 800; color: #0f172a; font-size: 14px;">Total Amount Due:</td>
                <td style="padding: 14px 18px; text-align: right; font-family: monospace; font-weight: 800; color: #059669; font-size: 16px;">
                    {{ $allocation->formatted_total_fee }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Important Handover Notice -->
    <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; margin: 20px 0; font-size: 12.5px; color: #92400e; line-height: 1.5;">
        <strong>Important Regulation Notice:</strong> In accordance with Local Government commercial regulations, no shop or stall may be handed over until the invoice raised at allocation has been cleared in full and reconciled.
    </div>

    <!-- Call to Action -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('public.shop-application.track-status', ['application_no' => $allocation->application_no, 'token' => $allocation->tracking_hash]) }}" 
           class="button" 
           style="background-color: {{ $system_settings['email_primary_color'] ?? ($system_settings['theme_primary_color'] ?? '#0b6b3a') }}; color: #ffffff !important; padding: 14px 32px; border-radius: 12px; text-decoration: none; display: inline-block; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            Track Status &amp; View Invoice &rarr;
        </a>
        <p style="font-size: 12px; color: #64748b; margin-top: 10px;">
            Tracking ID: <strong style="font-family: monospace; color: #0f172a;">{{ $allocation->application_no }}</strong>
        </p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 12.5px; color: #64748b; line-height: 1.5; margin-bottom: 0;">
        To complete settlement or submit a bank receipt, you may visit the Revenue Directorate at the Yola South Local Government Secretariat or contact our commercial desk quoting reference <strong>{{ $allocation->application_no }}</strong>.
    </p>
@endsection
