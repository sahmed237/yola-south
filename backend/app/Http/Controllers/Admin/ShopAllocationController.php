<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ShopAllocatedPaymentMail;
use App\Models\Invoice;
use App\Models\Market;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\ShopAllocation;
use App\Services\Payment\Gateways\PaymentGatewayFactory;
use App\Services\ShopAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ShopAllocationController extends Controller
{
    protected ShopAllocationService $allocationService;

    public function __construct(ShopAllocationService $allocationService)
    {
        $this->allocationService = $allocationService;
    }

    public function index(Request $request)
    {
        $query = ShopAllocation::with(['market', 'shop', 'reviewer', 'approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_no', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_phone', 'like', "%{$search}%")
                  ->orWhere('applicant_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('market_id')) {
            $query->where('market_id', $request->market_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('stage') && is_numeric($request->stage)) {
            $query->where('stage', $request->stage);
        }

        $allocations = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // KPIs matching UI/index.html
        $totalApplications = ShopAllocation::count();
        $underReviewCount = ShopAllocation::whereIn('stage', [1, 2, 3])->count();
        $approvedCount = ShopAllocation::where('stage', '>=', 4)->where('stage', '<', 7)->count();
        $completedCount = ShopAllocation::where('stage', 7)->count();

        $markets = Market::orderBy('name')->get();

        return view('admin.allocations.index', compact(
            'allocations',
            'markets',
            'totalApplications',
            'underReviewCount',
            'approvedCount',
            'completedCount'
        ));
    }

    public function show(ShopAllocation $allocation)
    {
        $allocation->load(['market', 'shop', 'reviewer', 'approver', 'allocator', 'latestInvoice.genericItems', 'payments']);

        $invoice = $allocation->latestInvoice;
        if (!$invoice && $allocation->stage >= 6) {
            $invoice = $allocation->getOrCreateAllocationInvoice();
        }

        // Available vacant shops in the same market for assignment
        $availableShops = Shop::where('market_id', $allocation->market_id)
            ->where(function ($q) use ($allocation) {
                $q->where('status', 'vacant')
                  ->orWhere('id', $allocation->shop_id);
            })
            ->orderBy('block_name')
            ->orderBy('shop_number')
            ->get();

        return view('admin.allocations.show', compact('allocation', 'availableShops', 'invoice'));
    }

    /**
     * Process Stage 2 (Review) or Stage 3 (Recommendation)
     */
    public function processReview(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'officer_recommendation' => 'required|string|max:1000',
            'advance_to_recommend' => 'nullable|boolean',
        ]);

        $targetStage = !empty($validated['advance_to_recommend']) ? 4 : 2;

        $this->allocationService->advanceStage(
            $allocation,
            $targetStage,
            ['officer_recommendation' => $validated['officer_recommendation']],
            auth()->id()
        );

        return back()->with('success', 'Application review updated successfully.');
    }

    /**
     * Process Stage 4 (Approval by Revenue Directorate -> Advances to Stage 5: Unit Allocation)
     */
    public function processApproval(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'approval_notes' => 'required|string|max:1000',
        ]);

        $this->allocationService->advanceStage(
            $allocation,
            5,
            ['approval_notes' => $validated['approval_notes']],
            auth()->id()
        );

        return back()->with('success', 'Application approved by Revenue Directorate. Advanced to Stage 5 (Unit Allocation).');
    }

    /**
     * Process Stage 5 (Shop Unit Allocation -> Advances to Stage 6: Invoice & Payment)
     */
    public function processAllocation(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
        ]);

        $this->allocationService->advanceStage(
            $allocation,
            6,
            ['shop_id' => $validated['shop_id']],
            auth()->id()
        );

        return back()->with('success', 'Shop unit successfully allocated to applicant. Advanced to Stage 6 (Invoice & Payment).');
    }

    /**
     * Process Stage 6 (Invoice / Payment confirmation -> Advances to Stage 7: Completed Certificate)
     */
    public function processPayment(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $this->allocationService->advanceStage(
            $allocation,
            7,
            [
                'payment_status' => 'paid',
                'payment_reference' => $validated['payment_reference'] ?? 'REC-YSLG-' . date('Y') . '-' . rand(100000, 999999),
            ],
            auth()->id()
        );

        return back()->with('success', 'Payment confirmed for allocation invoice. Advanced to Stage 7 (Certificate & Card).');
    }

    /**
     * Re-query / verify gateway payment status live from Monnify / Paystack
     */
    public function verifyPayment(Request $request, ShopAllocation $allocation)
    {
        if ($allocation->payment_status === 'paid' || $allocation->stage >= 7) {
            return back()->with('info', 'Payment has already been confirmed and settled for this allocation.');
        }

        $invoice = $allocation->latestInvoice ?? $allocation->getOrCreateAllocationInvoice();

        $isSimulated = ($request->has('simulate') || ($request->query('simulate') === 'true')) && config('app.env') !== 'production';
        $isPaid = false;
        $fee = 0.0;
        $ref = $invoice->reference;

        if ($isSimulated) {
            $isPaid = true;
            $fee = round($invoice->total_amount * 0.015, 2);
        } else {
            try {
                $gatewayInstance = PaymentGatewayFactory::create(strtolower($invoice->gateway));
                $gatewayRef = $invoice->metadata['gateway_reference'] ?? $invoice->reference;
                $verifyResult = $gatewayInstance->verify($gatewayRef);

                if ($verifyResult->amountPaid >= ($invoice->total_amount * 0.99)) {
                    $isPaid = true;
                    $fee = $verifyResult->fee;
                    $ref = $verifyResult->reference ?: $invoice->reference;
                } else {
                    return back()->with('info', 'Payment gateway reports this invoice is still pending settlement by the applicant.');
                }
            } catch (\Throwable $e) {
                Log::warning("Admin gateway verification check: " . $e->getMessage());
                return back()->with('warning', 'Gateway check returned: ' . $e->getMessage());
            }
        }

        if ($isPaid) {
            $invoice->update([
                'status' => 'success',
                'payment_fee' => $fee,
            ]);

            Payment::create([
                'invoice_id' => $invoice->id,
                'establishment_id' => null,
                'payable_type' => ShopAllocation::class,
                'payable_id' => $allocation->id,
                'amount' => $invoice->total_amount,
                'status' => 'success',
                'reference' => $ref,
                'gateway' => $invoice->gateway,
                'officer_id' => auth()->id(),
                'metadata' => [
                    'verified_by_admin' => auth()->user()?->name ?? 'Admin',
                    'verified_at' => now()->toIso8601String(),
                ],
            ]);

            $this->allocationService->advanceStage($allocation, 7, [
                'payment_status' => 'paid',
                'payment_reference' => $ref,
            ], auth()->id());

            return back()->with('success', 'Online payment verified successfully! Allocation has advanced to Stage 7 (Certificate Issued).');
        }

        return back()->with('info', 'Invoice is still pending settlement by applicant.');
    }

    /**
     * Resend official allocation payment notice and invoice email to applicant
     */
    public function resendPaymentNotice(ShopAllocation $allocation)
    {
        if (empty($allocation->applicant_email)) {
            return back()->with('error', 'Applicant does not have a registered email address on file.');
        }

        try {
            Setting::configureMailer();
        } catch (\Throwable $e) {
            Log::warning("Could not apply dynamic mail settings: " . $e->getMessage());
        }

        try {
            Mail::to($allocation->applicant_email)->send(
                new ShopAllocatedPaymentMail($allocation->fresh(['market', 'shop']))
            );
            return back()->with('success', "Official payment notice and invoice details resent to {$allocation->applicant_email}.");
        } catch (\Throwable $e) {
            Log::error("Failed to resend allocation payment mail: " . $e->getMessage());
            return back()->with('error', "Could not send email: " . $e->getMessage());
        }
    }

    /**
     * Process Stage 7 (Issue Allocation Letter & Certificate Card)
     */
    public function processComplete(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'conditions' => 'nullable|string|max:2000',
        ]);

        $this->allocationService->advanceStage(
            $allocation,
            7,
            ['conditions' => $validated['conditions'] ?? null],
            auth()->id()
        );

        return redirect()->route('admin.allocations.card', $allocation)->with('success', 'Allocation completed! Official certificate and card generated.');
    }

    /**
     * Reject Application
     */
    public function reject(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $this->allocationService->reject($allocation, $validated['rejection_reason'], auth()->id());

        return back()->with('info', 'Application has been marked as rejected.');
    }

    /**
     * Request updates / additional information from applicant
     */
    public function requestUpdate(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'update_notes' => 'required|string|max:1000',
        ]);

        $this->allocationService->requestUpdate($allocation, $validated['update_notes'], auth()->id());

        return back()->with('success', 'Update request sent to applicant via email.');
    }

    /**
     * Move application back to a previous stage
     */
    public function revert(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'target_stage' => 'required|integer|min:1|max:6',
            'revert_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['target_stage'] >= $allocation->stage) {
            return back()->with('error', 'Target stage must be earlier than the current stage.');
        }

        $this->allocationService->revertStage(
            $allocation,
            $validated['target_stage'],
            $validated['revert_notes'] ?? '',
            auth()->id()
        );

        return back()->with('success', "Application reverted back to Stage {$validated['target_stage']}.");
    }

    /**
     * Reopen rejected application
     */
    public function reopen(ShopAllocation $allocation)
    {
        $this->allocationService->reopenApplication($allocation, auth()->id());

        return back()->with('success', 'Application has been reopened and returned to Stage 1 (Pending Review).');
    }

    /**
     * Official Allocation Certificate & Card View (Replicates UI/index.html alloc-card)
     */
    public function card(ShopAllocation $allocation)
    {
        $allocation->load(['market', 'shop', 'reviewer', 'approver']);
        return view('admin.allocations.card', compact('allocation'));
    }
}
