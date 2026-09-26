<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class EnforceSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // 1. Enforce Password Change
        if ($user->require_password_change) {
            $allowedRoutes = [
                'security.password',
                'security.password.update',
                'logout'
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('security.password');
            }
            
            return $next($request);
        }

        // 2. Enforce 2FA Setup (If globally required and user is not exempt)
        if (Setting::get('two_factor_auth', '0') == '1' && !$user->isTwoFactorExempt()) {
            if (!$user->hasTwoFactorEnabled()) {
                $allowedRoutes = [
                    'security.2fa',
                    'security.2fa.confirm',
                    'logout'
                ];

                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    return redirect()->route('security.2fa');
                }
            }
        }

        return $next($request);
    }
}
