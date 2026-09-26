<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\UserWelcomeMail;
use App\Mail\EmailVerifiedMail;
use App\Mail\UserPasswordResetMail;
use App\Mail\GeneralUserMail;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // View Filter (Normal vs Trash)
        if ($request->get('view') === 'trash') {
            $query->onlyTrashed();
        }

        // Exclude Super Admin from list
        $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'super-admin');
        });

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role Filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'suspended') {
                $query->where('is_active', false);
            } elseif ($request->status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->with('roles')->paginate(10)->withQueryString();

        // Stats
        $stats = [
            'total' => User::whereDoesntHave('roles', fn($q) => $q->where('name', 'super-admin'))->count(),
            'active' => User::where('is_active', true)->whereDoesntHave('roles', fn($q) => $q->where('name', 'super-admin'))->count(),
            'suspended' => User::where('is_active', false)->whereDoesntHave('roles', fn($q) => $q->where('name', 'super-admin'))->count(),
            'trashed' => User::onlyTrashed()->count(),
        ];

        $roles = Role::where('name', '!=', 'super-admin')->get();

        return view('admin.users.index', compact('users', 'stats', 'roles'));
    }

    public function show(User $user)
    {
        $user->load('designatedAreas.lga', 'designatedAreas.ward');
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'super-admin')->get();
        $lgas = \App\Models\Lga::with('wards')->orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'lgas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'passport' => 'nullable|image|max:2048',
            'password' => 'required|string|min:8',
            'roles' => 'required|array'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'require_password_change' => true,
        ];

        if ($request->hasFile('passport')) {
            $path = $request->file('passport')->store('passports', 'public');
            $data['passport'] = asset('storage/' . $path);
        }

        $user = User::create($data);

        $user->assignRole($request->roles);

        if ($request->has('designated_areas')) {
            foreach ($request->designated_areas as $lgaId => $areaData) {
                if (isset($areaData['full']) && $areaData['full'] == '1') {
                    \App\Models\UserDesignatedArea::create([
                        'user_id' => $user->id,
                        'lga_id' => $lgaId,
                        'ward_id' => null
                    ]);
                } elseif (isset($areaData['wards']) && is_array($areaData['wards'])) {
                    foreach ($areaData['wards'] as $wardId) {
                        \App\Models\UserDesignatedArea::create([
                            'user_id' => $user->id,
                            'lga_id' => $lgaId,
                            'ward_id' => $wardId
                        ]);
                    }
                }
            }
        }

        // Generate Verification Link (Expires in 48 hours)
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(48),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Send Welcome Email
        try {
            \App\Models\Setting::configureMailer();
            Mail::to($user->email)->send(new UserWelcomeMail($user, $request->password, $verificationUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully and notification sent.');
    }

    /**
     * Show verification notice page
     */
    public function showVerificationNotice(Request $request)
    {
        return view('auth.verify-notice', ['email' => $request->email]);
    }

    /**
     * Resend verification link (Public)
     */
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', 'No account found with this email address.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('info', 'Email already verified. Please login.');
        }

        // Check if user is allowed to request verification
        $canRequest = \App\Models\Setting::get('user_can_request_new_email_verification', '1') == '1';
        if (! $canRequest) {
            return back()->with('error', 'Self-service verification requests are currently disabled. Please contact support.');
        }

        $this->sendVerificationLink($user);

        return back()->with('success', 'A new verification link has been sent to your email address.');
    }

    /**
     * Resend verification link (Admin)
     */
    public function resendVerificationAdmin($id)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return back()->with('error', 'User is already verified.');
        }

        $this->sendVerificationLink($user);

        return back()->with('success', "New verification link sent to {$user->name}.");
    }

    /**
     * Reset 2FA for user
     */
    public function resetTwoFactor($id)
    {
        $user = User::findOrFail($id);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        \App\Models\AuthenticationLog::log($user, '2fa_reset', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name
        ]);

        return back()->with('success', "Two-factor authentication for {$user->name} has been reset.");
    }

    /**
     * Helper to generate and send link
     */
    private function sendVerificationLink($user)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(48),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        try {
            \App\Models\Setting::configureMailer();
            Mail::to($user->email)->send(new \App\Mail\ResendVerificationMail($user, $verificationUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resend Mail failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle email verification from link
     */
    public function verifyEmailLink(Request $request, $id, $hash)
    {
        if (! $request->hasValidSignature()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'The verification link has expired or is invalid.'], 403);
            }
            abort(403, 'The verification link has expired or is invalid.');
        }

        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Invalid verification hash.'], 403);
            }
            abort(403, 'Invalid verification hash.');
        }

        // Handle GET Request: Show Animated Processing Page
        if ($request->isMethod('GET')) {
            return view('auth.verify-processing', [
                'verifyUrl' => URL::full()
            ]);
        }

        // Handle POST Request: Perform Actual Verification
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        if ($user->markEmailAsVerified()) {
            \App\Models\AuthenticationLog::log($user, 'email_verified', ['via' => 'signed_link']);

            // Send Confirmation Email
            try {
                \App\Models\Setting::configureMailer();
                Mail::to($user->email)->send(new EmailVerifiedMail($user));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Confirmation Mail failed: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Email verified successfully!']);
    }

    public function edit(User $user)
    {
        $roles = Role::where('name', '!=', 'super-admin')->get();
        $lgas = \App\Models\Lga::with('wards')->orderBy('name')->get();
        $user->load('designatedAreas');
        $designatedLgaIds = $user->designatedAreas->pluck('lga_id')->unique()->toArray();
        $designatedWardIds = $user->designatedAreas->pluck('ward_id')->filter()->unique()->toArray();
        $fullySelectedLgaIds = $user->designatedAreas->whereNull('ward_id')->pluck('lga_id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'lgas', 'designatedLgaIds', 'designatedWardIds', 'fullySelectedLgaIds'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'passport' => 'nullable|image|max:2048',
            'roles' => 'required|array'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->hasFile('passport')) {
            if ($user->passport) {
                $oldPath = str_replace(asset('storage/'), '', $user->passport);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('passport')->store('passports', 'public');
            $data['passport'] = asset('storage/' . $path);
        }

        $user->update($data);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles($request->roles);

        \App\Models\UserDesignatedArea::where('user_id', $user->id)->delete();
        if ($request->has('designated_areas')) {
            foreach ($request->designated_areas as $lgaId => $areaData) {
                if (isset($areaData['full']) && $areaData['full'] == '1') {
                    \App\Models\UserDesignatedArea::create([
                        'user_id' => $user->id,
                        'lga_id' => $lgaId,
                        'ward_id' => null
                    ]);
                } elseif (isset($areaData['wards']) && is_array($areaData['wards'])) {
                    foreach ($areaData['wards'] as $wardId) {
                        \App\Models\UserDesignatedArea::create([
                            'user_id' => $user->id,
                            'lga_id' => $lgaId,
                            'ward_id' => $wardId
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'User profile updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User moved to trash.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'suspended';
        return redirect()->back()->with('success', "Account for {$user->name} has been {$status} successfully.");
    }

    public function resendWelcome($id)
    {
        $user = User::findOrFail($id);
        $password = \Illuminate\Support\Str::random(10);
        $user->update(['password' => Hash::make($password), 'require_password_change' => true]);

        try {
            \App\Models\Setting::configureMailer();
            Mail::to($user->email)->send(new UserWelcomeMail($user, $password));
            return redirect()->back()->with('success', "New login credentials sent to {$user->name} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function verifyEmail($id)
    {
        $user = User::findOrFail($id);
        $user->update(['email_verified_at' => now()]);
        return redirect()->back()->with('success', "Email for {$user->name} verified successfully.");
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $password = \Illuminate\Support\Str::random(10);
        $user->update(['password' => Hash::make($password), 'require_password_change' => true]);

        try {
            \App\Models\Setting::configureMailer();
            Mail::to($user->email)->send(new UserPasswordResetMail($user, $password));
            return redirect()->back()->with('success', "Password for {$user->name} reset successfully. New credentials sent to user email.");
        } catch (\Exception $e) {
            return redirect()->back()->with('success', "Password for {$user->name} reset to: {$password} (Email failed)");
        }
    }

    public function sendCustomEmail(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $user = User::findOrFail($id);

        try {
            \App\Models\Setting::configureMailer();
            Mail::to($user->email)->send(new GeneralUserMail($user, $request->subject, $request->body));
            return redirect()->back()->with('success', "Email sent to {$user->name} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|string',
        ]);

        $ids = json_decode($request->ids);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No users selected.');
        }

        $users = User::whereIn('id', $ids)->get();
        $count = $users->count();

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $users) {
            foreach ($users as $user) {
                switch ($request->action) {
                    case 'activate':
                        $user->update(['is_active' => true]);
                        break;
                    case 'suspend':
                        $user->update(['is_active' => false]);
                        break;
                    case 'require_password':
                        $user->update(['require_password_change' => true]);
                        break;
                    case 'remove_password_req':
                        $user->update(['require_password_change' => false]);
                        break;
                    case 'delete':
                        $user->delete();
                        break;
                }
            }
        });

        $actionName = str_replace('_', ' ', $request->action);
        return redirect()->back()->with('success', "Bulk action '{$actionName}' completed for {$count} users.");
    }

    public function unlock($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['locked_until' => null]);
            
            \App\Models\AuthenticationLog::log($user, 'unlock', [
                'unlocked_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'User account has been unlocked.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to unlock user.');
        }
    }

    public function authLogs($id)
    {
        $user = User::findOrFail($id);
        $logs = \App\Models\AuthenticationLog::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.auth-logs', compact('user', 'logs'));
    }

    public function allAuthLogs()
    {
        $logs = \App\Models\AuthenticationLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.users.all-auth-logs', compact('logs'));
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->route('admin.users.index')->with('success', "Account for {$user->name} has been restored successfully.");
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $name = $user->name;
        $user->forceDelete();
        return redirect()->route('admin.users.index', ['view' => 'trash'])->with('success', "Account for {$name} has been permanently deleted.");
    }
}
