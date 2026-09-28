<?php

namespace App\Http\Controllers;

use App\Mail\ShopApplicationSubmittedMail;
use App\Models\Invoice;
use App\Models\Market;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\ShopAllocation;
use App\Services\OtpService;
use App\Services\Payment\Gateways\PaymentGatewayFactory;
use App\Services\ShopAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        // Dynamically apply database SMTP configuration
        try {
            Setting::configureMailer();
        } catch (\Throwable $e) {
            Log::warning("Could not apply dynamic mail settings: " . $e->getMessage());
        }

        // Send submission confirmation email with details and tracking ID
        try {
            Mail::to($email)->send(new ShopApplicationSubmittedMail($allocation));
        } catch (\Throwable $e) {
            Log::warning("Failed to send application confirmation email to {$email}: " . $e->getMessage());
        }

        // Pre-authorize tracking in session for this new application
        session()->put("verified_tracking_{$allocation->application_no}", true);

        return redirect()->route('public.shop-application.track-status', $allocation->application_no)
            ->with('success', "Application successfully submitted! A confirmation email with your Tracking Reference ({$allocation->application_no}) and details has been sent to your email.");
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

        if (!$this->isTrackingAuthorized($allocation)) {
            return redirect()->route('public.shop-application.track-verify', [
                'application_no' => $applicationNo,
                'redirect' => request()->fullUrl(),
            ]);
        }

        $allocation->load(['market', 'shop', 'reviewer', 'approver']);

        return view('public.shop-application.status', compact('allocation'));
    }

    /**
     * Show Security Verification Challenge for Application Tracking
     */
    public function showVerifyTracking(Request $request, string $applicationNo)
    {
        $allocation = ShopAllocation::where('application_no', $applicationNo)->first();

        if (!$allocation) {
            return redirect()->route('public.shop-application.track')
                ->with('error', "No application found with Tracking Reference {$applicationNo}.");
        }

        // Ensure allocation has a tracking_hash
        if (empty($allocation->tracking_hash)) {
            $allocation->tracking_hash = hash('sha256', $allocation->application_no . \Illuminate\Support\Str::random(32));
            $allocation->saveQuietly();
        }

        // If already authorized, redirect to target
        if ($this->isTrackingAuthorized($allocation)) {
            $redirectUrl = $request->query('redirect') ?: route('public.shop-application.track-status', $allocation->application_no);
            return redirect($redirectUrl);
        }

        $maskedEmail = self::maskEmail($allocation->applicant_email);
        $redirect = $request->query('redirect', route('public.shop-application.track-status', $allocation->application_no));

        return view('public.shop-application.verify', compact('allocation', 'maskedEmail', 'redirect'));
    }

    /**
     * Send OTP for Application Tracking Access
     */
    public function sendTrackingOtp(Request $request, string $applicationNo)
    {
        $allocation = ShopAllocation::where('application_no', $applicationNo)->first();

        if (!$allocation) {
            return response()->json([
                'success' => false,
                'message' => 'Application reference not found.',
            ], 404);
        }

        $res = $this->otpService->generateAndSendOtp(
            $allocation->applicant_email,
            "Security Verification for Application {$allocation->application_no}"
        );

        return response()->json([
            'success' => true,
            'message' => "A 6-digit verification code has been sent to " . self::maskEmail($allocation->applicant_email) . ".",
            'expires_at' => $res['expires_at'] ?? now()->addMinutes(15)->toIso8601String(),
        ]);
    }

    /**
     * Verify Tracking OTP and Grant Access
     */
    public function verifyTrackingOtp(Request $request, string $applicationNo)
    {
        $request->validate([
            'otp' => 'required|string',
            'redirect' => 'nullable|string',
        ]);

        $allocation = ShopAllocation::where('application_no', $applicationNo)->first();

        if (!$allocation) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Application reference not found.'], 404);
            }
            return redirect()->route('public.shop-application.track')->with('error', 'Application reference not found.');
        }

        $isValid = $this->otpService->verifyOtp($allocation->applicant_email, trim($request->otp));

        if (!$isValid) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The verification code entered is invalid or has expired. Please try again.',
                ], 422);
            }
            return back()->with('error', 'The verification code entered is invalid or has expired.');
        }

        // Grant session authorization
        session()->put("verified_tracking_{$allocation->application_no}", true);

        $redirectUrl = $request->input('redirect') ?: route('public.shop-application.track-status', $allocation->application_no);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Verification successful! Redirecting...',
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl)->with('success', 'Security verification successful. Access granted.');
    }

    /**
     * Check if visitor is authorized to view or edit this application
     */
    protected function isTrackingAuthorized(ShopAllocation $allocation): bool
    {
        // 1. Authenticated administrators/officers
        if (auth()->check() && (auth()->user()->can('view allocations') || auth()->user()->hasRole('super-admin'))) {
            return true;
        }

        // 2. Direct token verification (e.g. from official notification email)
        $token = request('token');
        if (!empty($token) && !empty($allocation->tracking_hash) && hash_equals($allocation->tracking_hash, $token)) {
            session()->put("verified_tracking_{$allocation->application_no}", true);
            return true;
        }

        // 3. Current session verification
        return session()->get("verified_tracking_{$allocation->application_no}") === true;
    }

    /**
     * Mask email address for user privacy (e.g. s***d@gmail.com)
     */
    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0] ?? '';
        $domain = $parts[1] ?? '';

        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } elseif ($len <= 4) {
            $maskedName = substr($name, 0, 1) . str_repeat('*', max(1, $len - 2)) . substr($name, -1);
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(1, $len - 4)) . substr($name, -2);
        }

        return $maskedName . '@' . $domain;
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

    /**
     * Public Application Update Form (Invoked via email request or tracking page)
     */
    public function edit(string $applicationNo)
    {
        $allocation = ShopAllocation::where('application_no', $applicationNo)->firstOrFail();

        if (!$this->isTrackingAuthorized($allocation)) {
            return redirect()->route('public.shop-application.track-verify', [
                'application_no' => $applicationNo,
                'redirect' => request()->fullUrl(),
            ]);
        }

        $allocation->load(['market', 'shop']);

        $markets = Market::where('status', 'active')
            ->with(['shops' => function ($q) {
                $q->where('status', 'vacant');
            }])
            ->get();

        return view('public.shop-application.edit', compact('allocation', 'markets'));
    }

    /**
     * Public Application Update Submission
     */
    public function update(Request $request, string $applicationNo)
    {
        $allocation = ShopAllocation::where('application_no', $applicationNo)->firstOrFail();

        if (!$this->isTrackingAuthorized($allocation)) {
            return redirect()->route('public.shop-application.track-verify', [
                'application_no' => $applicationNo,
                'redirect' => route('public.shop-application.edit', $applicationNo),
            ]);
        }

        $validated = $request->validate([
            'applicant_name' => 'required|string|max:255',
            'applicant_phone' => 'required|string|max:50',
            'applicant_nin_bvn' => 'nullable|string|max:50',
            'applicant_address' => 'required|string|max:500',
            'market_id' => 'nullable|exists:markets,id',
            'trade_type' => 'required|string|max:100',
            'requested_size' => 'nullable|string|max:50',
            'passport_photo' => 'nullable|image|max:3072',
            'id_document' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ]);

        $updateData = [
            'applicant_name' => $validated['applicant_name'],
            'applicant_phone' => $validated['applicant_phone'],
            'applicant_nin_bvn' => $validated['applicant_nin_bvn'] ?? null,
            'applicant_address' => $validated['applicant_address'],
            'market_id' => $validated['market_id'] ?? $allocation->market_id,
            'trade_type' => $validated['trade_type'],
            'requested_size' => $validated['requested_size'] ?? $allocation->requested_size,
        ];

        if ($request->hasFile('passport_photo')) {
            $updateData['passport_photo'] = $request->file('passport_photo')->store('allocations/passports', 'public');
        }

        if ($request->hasFile('id_document')) {
            $updateData['id_document'] = $request->file('id_document')->store('allocations/documents', 'public');
        }

        $this->allocationService->updateByApplicant($allocation, $updateData);

        return redirect()->route('public.shop-application.track-status', $allocation->application_no)
            ->with('success', 'Your application updates have been successfully submitted! Council officers have been notified and review has resumed.');
    }

    /**
     * Show Public Checkout Page for Allocated Shop Unit
     */
    public function checkout(Request $request, string $applicationNo)
    {
        $allocation = ShopAllocation::with(['market', 'shop'])->where('application_no', $applicationNo)->firstOrFail();

        if (!$this->isTrackingAuthorized($allocation)) {
            return redirect()->route('public.shop-application.track-verify', [
                'application_no' => $applicationNo,
                'redirect' => route('public.shop-application.checkout', $applicationNo),
            ]);
        }

        if ($allocation->payment_status === 'paid' || $allocation->stage >= 7) {
            return redirect()->route('public.shop-application.track-status', $allocation->application_no)
                ->with('info', 'Payment has already been confirmed and reconciled for this allocation.');
        }

        $gateway = strtolower($request->query('gateway', 'monnify'));
        $invoice = $allocation->getOrCreateAllocationInvoice($gateway);
        $invoice->load('genericItems');

        $redirectUrl = null;
        $gatewayError = null;

        if (!empty($invoice->metadata['redirect_url']) && !empty($invoice->metadata['gateway_reference'])) {
            $redirectUrl = $invoice->metadata['redirect_url'];
        } else {
            try {
                $gatewayInstance = PaymentGatewayFactory::create(strtolower($invoice->gateway));
                $callbackUrl = route('public.shop-application.payment-callback', [
                    'application_no' => $allocation->application_no,
                    'reference' => $invoice->reference,
                ]);

                $applicantEmail = $allocation->applicant_email 
                    ?: ($invoice->email ?? null) 
                    ?: ('taxpayer.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $allocation->applicant_phone ?: $allocation->application_no)) . '@yolasouth.lg.gov.ng');

                $paymentInit = $gatewayInstance->initialize(
                    $invoice,
                    $applicantEmail,
                    $allocation->applicant_phone,
                    $callbackUrl
                );

                $redirectUrl = $paymentInit->redirectUrl;
                $invoice->update([
                    'metadata' => array_merge($invoice->metadata ?? [], [
                        'gateway_reference' => $paymentInit->reference,
                        'redirect_url' => $paymentInit->redirectUrl,
                    ])
                ]);
            } catch (\Throwable $e) {
                $gatewayError = $e->getMessage();
                Log::warning("Shop allocation payment gateway initialization failed: " . $e->getMessage());
            }
        }

        $system_settings = Setting::getAllSettings();

        return view('public.shop-application.checkout', compact('allocation', 'invoice', 'redirectUrl', 'gatewayError', 'system_settings'));
    }

    /**
     * Handle Payment Gateway Callback / Success
     */
    public function paymentCallback(Request $request, string $applicationNo)
    {
        $allocation = ShopAllocation::where('application_no', $applicationNo)->firstOrFail();
        $reference = $request->query('reference', $allocation->invoice_no);

        $invoice = Invoice::where('reference', $reference)->first();
        if (!$invoice) {
            $invoice = $allocation->invoices()->latest()->first();
        }

        if ($invoice && $invoice->status === 'pending') {
            $isSimulated = app()->environment('testing') && $request->query('simulate') === 'true';
            $fee = 0.0;

            if (!$isSimulated) {
                try {
                    $gatewayInstance = PaymentGatewayFactory::create(strtolower($invoice->gateway));
                    $gatewayRef = $request->query('paymentReference') ?? $invoice->metadata['gateway_reference'] ?? $invoice->reference;
                    $verifyResult = $gatewayInstance->verify($gatewayRef);

                    if ($verifyResult->amountPaid < $invoice->total_amount * 0.99) {
                        return redirect()->route('public.shop-application.track-status', $allocation->application_no)
                            ->with('error', 'Payment verification failed: Amount paid does not match invoice total.');
                    }
                    $fee = $verifyResult->fee;
                } catch (\Throwable $e) {
                    Log::error("Live payment verification failed: " . $e->getMessage());
                    return redirect()->route('public.shop-application.track-status', $allocation->application_no)
                        ->with('error', 'Could not verify payment with gateway: ' . $e->getMessage());
                }
            } else {
                $fee = round($invoice->total_amount * 0.015, 2);
            }

            DB::transaction(function () use ($invoice, $allocation, $fee) {
                $invoice->update([
                    'status' => 'success',
                    'payment_fee' => $fee,
                ]);

                // Create Payment record with polymorphic link
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'establishment_id' => null,
                    'payable_type' => ShopAllocation::class,
                    'payable_id' => $allocation->id,
                    'amount' => $invoice->total_amount,
                    'status' => 'success',
                    'reference' => $invoice->reference,
                    'gateway' => $invoice->gateway,
                    'metadata' => [
                        'paid_by' => $allocation->applicant_name,
                        'application_no' => $allocation->application_no,
                        'channel' => 'public_portal',
                    ],
                ]);

                // Advance allocation workflow to Stage 7 (completed)
                $this->allocationService->advanceStage($allocation, 7, [
                    'payment_status' => 'paid',
                    'payment_reference' => $invoice->reference,
                ]);
            });
        }

        // Grant tracking session authorization
        session()->put("verified_tracking_{$allocation->application_no}", true);

        return redirect()->route('public.shop-application.track-status', $allocation->application_no)
            ->with('success', 'Payment settled successfully! Your commercial unit allocation is now fully confirmed. You can view and download your Certificate & Handover Card below.');
    }
}
