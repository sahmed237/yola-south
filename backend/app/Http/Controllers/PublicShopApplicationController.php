<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Shop;
use App\Models\ShopAllocation;
use App\Services\OtpService;
use App\Services\ShopAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicShopApplicationController extends Controller
{
    protected OtpService $otpService;
    protected ShopAllocationService $allocationService;

    public function __construct(OtpService $otpService, ShopAllocationService $allocationService)
    {
        $this->otpService = $otpService;
        $this->allocationService = $allocationService;
    }

    /**
     * Public Application Landing & Form
     */
    public function index()
    {
        $markets = Market::where('status', 'active')
            ->with(['shops' => function ($q) {
                $q->where('status', 'vacant');
            }])
            ->get();

        $verifiedEmail = session('shop_app_verified_email');

        return view('public.shop-application.index', compact('markets', 'verifiedEmail'));
    }

    /**
     * Send OTP to Email (Kanogis-style initiation)
     */
    public function initiateOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->email));
        $res = $this->otpService->generateAndSendOtp($email, 'Shop Allocation Application');

        return response()->json($res);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'otp' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $isValid = $this->otpService->verifyOtp($email, $request->otp);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code provided is invalid or has expired. Please request a new code.',
            ], 422);
        }

        session(['shop_app_verified_email' => $email]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully! You may now proceed with your application.',
            'email' => $email,
        ]);
    }

    /**
     * Submit Application
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'applicant_name' => 'required|string|max:255',
            'applicant_phone' => 'required|string|max:50',
            'applicant_nin_bvn' => 'nullable|string|max:50',
            'applicant_address' => 'required|string|max:500',
            'market_id' => 'required|exists:markets,id',
            'shop_id' => 'nullable|exists:shops,id',
            'trade_type' => 'required|string|max:100',
            'requested_size' => 'nullable|string|max:50',
            'passport_photo' => 'nullable|image|max:3072', // 3MB max
            'id_document' => 'nullable|file|mimes:jpeg,png,pdf|max:5120', // 5MB max
        ]);

        $email = strtolower(trim($validated['email']));

        // Verify that this email was verified via OTP
        if (session('shop_app_verified_email') !== $email && !app()->environment(['local', 'testing'])) {
            return back()->withInput()->with('error', 'Please verify your email address using the verification code before submitting.');
        }

        // Handle File Uploads
        $passportPath = null;
        if ($request->hasFile('passport_photo')) {
            $passportPath = $request->file('passport_photo')->store('allocations/passports', 'public');
        }

        $idPath = null;
        if ($request->hasFile('id_document')) {
            $idPath = $request->file('id_document')->store('allocations/documents', 'public');
        }

        $market = Market::findOrFail($validated['market_id']);
        $shop = !empty($validated['shop_id']) ? Shop::find($validated['shop_id']) : null;

        $allocation = $this->allocationService->createApplication([
            'market_id' => $market->id,
            'shop_id' => $shop?->id,
            'applicant_name' => $validated['applicant_name'],
            'applicant_phone' => $validated['applicant_phone'],
            'applicant_email' => $email,
            'applicant_nin_bvn' => $validated['applicant_nin_bvn'] ?? null,
            'applicant_address' => $validated['applicant_address'],
            'trade_type' => $validated['trade_type'],
            'requested_size' => $validated['requested_size'] ?? ($shop?->size ?? '3.0 × 4.0 m'),
            'rent_amount' => $shop?->monthly_rent ?? 15000.00,
            'passport_photo' => $passportPath,
            'id_document' => $idPath,
        ]);

        // Clear session OTP flag
        session()->forget('shop_app_verified_email');

        return redirect()->route('public.shop-application.track-status', $allocation->application_no)
            ->with('success', "Application successfully submitted! Your Tracking Reference is {$allocation->application_no}.");
    }

    /**
     * Tracking Portal Search
     */
    public function track(Request $request)
    {
        if ($request->filled('ref')) {
            $ref = trim($request->ref);
            return redirect()->route('public.shop-application.track-status', $ref);
        }

        return view('public.shop-application.track');
    }

    /**
     * View Tracking Status & 7-Stage Stepper
     */
    public function trackStatus(string $applicationNo)
    {
        $allocation = ShopAllocation::where('application_no', $applicationNo)->first();

        if (!$allocation) {
            return redirect()->route('public.shop-application.track')
                ->with('error', "No application found with Tracking Reference {$applicationNo}. Please verify the reference number.");
        }

        $allocation->load(['market', 'shop', 'reviewer', 'approver']);

        return view('public.shop-application.status', compact('allocation'));
    }

    /**
     * Public Certificate & Verification
     */
    public function certificate(string $applicationNo)
    {
        $allocation = ShopAllocation::where('application_no', $applicationNo)
            ->where('stage', 7)
            ->firstOrFail();

        $allocation->load(['market', 'shop', 'reviewer', 'approver']);

        return view('public.shop-application.certificate', compact('allocation'));
    }
}
