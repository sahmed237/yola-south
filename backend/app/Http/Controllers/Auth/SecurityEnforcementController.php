<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Setting;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class SecurityEnforcementController extends Controller
{
    /**
     * Show mandatory password change form
     */
    public function showPasswordChange()
    {
        $user = auth()->user();
        if (!$user->require_password_change) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.enforce-password');
    }

    /**
     * Handle mandatory password update
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $settings = Setting::pluck('value', 'key');
        
        $passwordRules = Password::min($settings['password_min_length'] ?? 8);
        if (($settings['password_require_uppercase'] ?? '0') == '1') $passwordRules->letters()->mixedCase();
        if (($settings['password_require_number'] ?? '0') == '1') $passwordRules->numbers();
        if (($settings['password_require_special'] ?? '0') == '1') $passwordRules->symbols();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', $passwordRules],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'require_password_change' => false,
        ])->save();

        \App\Models\AuthenticationLog::log($user, 'password_change', ['reason' => 'enforced_policy']);

        return redirect()->route('admin.dashboard')->with('success', 'Password updated successfully. You now have full access.');
    }

    /**
     * Show mandatory 2FA setup form
     */
    public function show2faSetup()
    {
        $user = auth()->user();
        
        // If already enabled and we're not showing recovery codes, get out
        if ($user->hasTwoFactorEnabled() && !session('recovery_codes')) {
            return redirect()->route('admin.dashboard');
        }

        $google2fa = new Google2FA();
        
        // Ensure secret exists
        if (!$user->two_factor_secret) {
            $user->forceFill(['two_factor_secret' => $google2fa->generateSecretKey()])->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            Setting::get('platform_name', 'URCS'),
            $user->email,
            $user->two_factor_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($qrCodeUrl);
        $secret = $user->two_factor_secret;

        return view('auth.enforce-2fa', compact('qrCode', 'secret'));
    }

    /**
     * Confirm mandatory 2FA setup
     */
    public function confirm2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        if ($google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            $user->forceFill([
                'two_factor_confirmed_at' => now(),
            ])->save();

            $recoveryCodes = $user->generateRecoveryCodes();

            \App\Models\AuthenticationLog::log($user, '2fa_enabled', ['reason' => 'enforced_policy']);

            return back()->with('success', 'Two-factor authentication enabled successfully.')
                       ->with('recovery_codes', $recoveryCodes);
        }

        return back()->with('error', 'Invalid verification code. Please try again.');
    }
}
