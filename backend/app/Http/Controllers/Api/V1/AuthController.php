<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Authenticate an officer and return their Sanctum API token and profile.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->username)
                    ->orWhere('phone', $request->username)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password.'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'This user account has been deactivated.'
            ], 403);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email address is not verified. Please verify your email to access the platform.',
                'error_code' => 'email_unverified',
                'email' => $user->email,
            ], 403);
        }

        if ($user->require_password_change) {
            return response()->json([
                'message' => 'You are required to change your password. Please log in to the web portal to update it.',
                'error_code' => 'password_change_required',
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        $lgas = $user->getDesignatedLgasAndWards();
        $establishmentTypes = EstablishmentType::where('status', true)->orderBy('value')->get();
        $establishmentSizes = EstablishmentSize::where('status', true)->orderBy('value')->get();

        return response()->json([
            'token' => $token,
            'name' => $user->name,
            'email' => $user->email,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'lgas' => $lgas,
            'establishment_types' => $establishmentTypes,
            'establishment_sizes' => $establishmentSizes,
        ]);
    }
}
