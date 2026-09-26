<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Email Settings
            ['key' => 'mail_mailer', 'value' => 'smtp', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_host', 'value' => 'journal.ang.org.ng', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_port', 'value' => '465', 'group' => 'email', 'type' => 'integer'],
            ['key' => 'mail_username', 'value' => 'support@journal.ang.org.ng', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_password', 'value' => 'X1r*,uZYrZ;2gB7C', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_address', 'value' => 'support@journal.ang.org.ng', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_name', 'value' => 'MY APP', 'group' => 'email', 'type' => 'string'],
            ['key' => 'email_wrapper_bg', 'value' => '#ffffff', 'group' => 'email', 'type' => 'string'],
            ['key' => 'email_body_bg', 'value' => '#ffffff', 'group' => 'email', 'type' => 'string'],
            ['key' => 'email_primary_color', 'value' => '#940000', 'group' => 'email', 'type' => 'string'],
            ['key' => 'email_text_color', 'value' => '#334155', 'group' => 'email', 'type' => 'string'],
            ['key' => 'email_footer_text_color', 'value' => '#94a3b8', 'group' => 'email', 'type' => 'string'],
            ['key' => 'email_header_bg', 'value' => '#79a2b4', 'group' => 'email', 'type' => 'string'],
            ['key' => 'email_footer_bg', 'value' => '#ffebeb', 'group' => 'email', 'type' => 'string'],

            // General Settings
            ['key' => 'platform_logo', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'platform_name', 'value' => 'URCS', 'group' => 'general', 'type' => 'string'],
            ['key' => 'support_email', 'value' => 'support@urcs.gov.ng', 'group' => 'general', 'type' => 'string'],
            ['key' => 'contact_phone', 'value' => '08123456789', 'group' => 'general', 'type' => 'string'],
            ['key' => 'address', 'value' => '8 high court crescent', 'group' => 'general', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'general', 'type' => 'boolean'],
            ['key' => 'theme_font_family', 'value' => 'Inter', 'group' => 'general', 'type' => 'string'],
            ['key' => 'theme_primary_color', 'value' => '#58c6a5', 'group' => 'general', 'type' => 'string'],
            ['key' => 'theme_secondary_color', 'value' => '#f59e0b', 'group' => 'general', 'type' => 'string'],
            ['key' => 'theme_success_color', 'value' => '#29a352', 'group' => 'general', 'type' => 'string'],
            ['key' => 'theme_error_color', 'value' => '#de2424', 'group' => 'general', 'type' => 'string'],
            ['key' => 'theme_button_radius', 'value' => '0.75rem', 'group' => 'general', 'type' => 'string'],
            ['key' => 'theme_sidebar_bg', 'value' => '#0f172a', 'group' => 'general', 'type' => 'string'],
            ['key' => 'theme_sidebar_scrollbar_color', 'value' => 'rgba(255, 255, 255, 0.1)', 'group' => 'general', 'type' => 'string'],

            // Notifications
            ['key' => 'notify_new_user', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
            ['key' => 'notify_weekly_report', 'value' => '0', 'group' => 'notifications', 'type' => 'boolean'],

            // Payment Settings
            ['key' => 'paystack_active', 'value' => '1', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'paystack_mode_live', 'value' => '1', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'paystack_public_key', 'value' => 'pk_live_...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'paystack_secret_key', 'value' => 'sk_live_...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'monnify_active', 'value' => '1', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'monnify_mode_live', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'monnify_api_key', 'value' => 'MK_TEST_...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'monnify_secret_key', 'value' => 'WE6T2...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'monnify_contract_code', 'value' => 'WE6T2...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'remita_active', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'remita_mode_live', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'remita_api_key', 'value' => 'MK_TEST_...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'remita_secret_key', 'value' => 'WE6T2...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'zainpay_active', 'value' => '1', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'zainpay_mode_live', 'value' => '1', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'zainpay_token', 'value' => 'MK_TEST_...', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'zainpay_zainbox_code', 'value' => 'WE6T2...', 'group' => 'payments', 'type' => 'string'],

            // Security
            ['key' => 'two_factor_auth', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'login_rate_limiting', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'max_login_attempts', 'value' => '3', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'lockout_duration', 'value' => '5', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'session_timeout', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_min_length', 'value' => '6', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'password_require_uppercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_lowercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_number', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_special', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'user_can_request_new_email_verification', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'user_can_forget_password', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],

        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
