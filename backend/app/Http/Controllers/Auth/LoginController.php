<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();
        
        // Check if user is administratively locked
        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            $seconds = now()->diffInSeconds($user->locked_until);
            return back()->withErrors([
                'email' => "Account is locked. Please try again in {$seconds} seconds or contact administrator.",
            ])->onlyInput('email');
        }

        $settings = Setting::pluck('value', 'key');
        $rateLimitingEnabled = ($settings['login_rate_limiting'] ?? '1') == '1';
        $maxAttempts = (int) ($settings['max_login_attempts'] ?? 3);
        $throttleKey = strtolower($request->input('email')) . '|' . $request->ip();

        if ($rateLimitingEnabled && \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            
            // Sync with User model if exists
            if ($user) {
                $user->update(['locked_until' => now()->addSeconds($seconds)]);
                \App\Models\AuthenticationLog::log($user, 'lockout', ['reason' => 'Rate limit exceeded']);
            }

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (! $user->hasVerifiedEmail()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('verification.notice', ['email' => $user->email])
                    ->with('error', 'Your email address is not verified. Please verify your email to access the platform.');
            }

            // Check if password change is required
            if ($user->require_password_change) {
                return redirect()->route('security.password');
            }

            // Two-Factor Authentication Check
            if (! $user->isTwoFactorExempt() && Setting::get('two_factor_auth', '0') == '1') {
                if ($user->hasTwoFactorEnabled()) {
                    Auth::logout();
                    session(['2fa_user_id' => $user->id]);
                    return redirect()->route('2fa.challenge');
                }
                
                // If forced globally but not set up yet
                return redirect()->route('security.2fa');
            }

            if ($rateLimitingEnabled) {
                \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            }
            
            $user->update(['locked_until' => null]);
            
            $request->session()->regenerate();

            return redirect()->intended('admin/dashboard');
        }

        if ($rateLimitingEnabled) {
            $lockoutDuration = (int) ($settings['lockout_duration'] ?? 5);
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, $lockoutDuration * 60); 
            
            // If just hit limit, log it
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
                if ($user) {
                    $user->update(['locked_until' => now()->addMinutes($lockoutDuration)]);
                    \App\Models\AuthenticationLog::log($user, 'lockout', ['duration' => $lockoutDuration]);
                }
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show 2FA challenge page
     */
    public function showChallenge()
    {
        if (! session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-challenge');
    }

    /**
     * Verify 2FA code
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $userId = session('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        if ($google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            Auth::login($user);
            session()->forget('2fa_user_id');
            
            \App\Models\AuthenticationLog::log($user, 'login_success', ['2fa' => 'verified']);

            return redirect()->intended('admin/dashboard');
        }

        return back()->with('error', 'Invalid verification code.');
    }

    /**
     * Verify 2FA recovery code
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate(['recovery_code' => 'required|string']);

        $userId = session('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);
        $recoveryCodes = $user->recoveryCodes();

        if (in_array($request->recovery_code, $recoveryCodes)) {
            // Remove the used recovery code
            $newCodes = array_diff($recoveryCodes, [$request->recovery_code]);
            $user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($newCodes))),
            ])->save();

            Auth::login($user);
            session()->forget('2fa_user_id');

            \App\Models\AuthenticationLog::log($user, 'login_success', ['2fa' => 'recovery_used']);

            return redirect()->intended('admin/dashboard');
        }

        return back()->with('error', 'Invalid recovery code.');
    }
}
