<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $system_settings['platform_name'] ?? 'Revenue Collection System')</title>
    <style>
        body { 
            font-family: 'Inter', Helvetica, Arial, sans-serif; 
            background-color: {{ $system_settings['email_wrapper_bg'] ?? '#f8fafc' }}; 
            margin: 0; 
            padding: 0; 
            -webkit-text-size-adjust: none; 
            width: 100% !important; 
        }
        .wrapper { 
            width: 100%; 
            table-layout: fixed; 
            background-color: {{ $system_settings['email_wrapper_bg'] ?? '#f8fafc' }}; 
            padding: 40px 0;
        }
        .main { 
            background-color: {{ $system_settings['email_body_bg'] ?? '#ffffff' }}; 
            margin: 0 auto; 
            width: 100%; 
            max-width: 600px; 
            border-collapse: collapse; 
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header { 
            background-color: {{ $system_settings['email_header_bg'] ?? ($system_settings['theme_sidebar_bg'] ?? '#0f172a') }}; 
            padding: 40px 30px; 
            text-align: center; 
        }
        .content { 
            padding: 40px 30px; 
            color: {{ $system_settings['email_text_color'] ?? '#334155' }}; 
            line-height: 1.6; 
            font-size: 16px;
        }
        .footer { 
            background-color: {{ $system_settings['email_footer_bg'] ?? '#f1f5f9' }}; 
            padding: 30px; 
            text-align: center; 
            color: {{ $system_settings['email_footer_text_color'] ?? '#64748b' }}; 
            font-size: 12px; 
        }
        .button { 
            background-color: {{ $system_settings['email_primary_color'] ?? ($system_settings['theme_primary_color'] ?? '#2563eb') }}; 
            color: #ffffff !important; 
            padding: 14px 30px; 
            border-radius: 12px; 
            text-decoration: none; 
            display: inline-block; 
            font-weight: bold; 
            margin: 24px 0;
        }
        .box { 
            background-color: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 24px; 
            margin: 24px 0; 
        }
        h1 { font-size: 24px; color: #1e293b; margin-top: 0; margin-bottom: 20px; }
        p { margin-top: 0; margin-bottom: 16px; }
        .divider { height: 1px; background-color: #e2e8f0; margin: 24px 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" role="presentation">
            <!-- Header -->
            <tr>
                <td class="header">
                    @if(!empty($system_settings['platform_logo']))
                        <img src="{{ $system_settings['platform_logo'] }}" alt="{{ $system_settings['platform_name'] ?? 'Logo' }}" height="45" style="display: block; margin: 0 auto;">
                    @else
                        <h2 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.02em;">
                            {{ $system_settings['platform_name'] ?? 'URCS' }}
                        </h2>
                    @endif
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td class="content">
                    @yield('content')
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p style="margin-bottom: 8px; font-weight: bold;">{{ $system_settings['platform_name'] ?? 'Revenue Collection System' }}</p>
                    <p style="margin-bottom: 16px;">{{ $system_settings['address'] ?? 'Official Revenue Management Portal' }}</p>
                    <div class="divider"></div>
                    <p style="font-size: 11px; margin-top: 16px;">
                        This is an automated system notification. Please do not reply directly to this email.<br>
                        Contact support at <a href="mailto:{{ $system_settings['support_email'] ?? 'support@urcs.gov.ng' }}" style="color: {{ $system_settings['email_primary_color'] ?? '#2563eb' }}; text-decoration: none;">{{ $system_settings['support_email'] ?? 'support@urcs.gov.ng' }}</a> for assistance.
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
