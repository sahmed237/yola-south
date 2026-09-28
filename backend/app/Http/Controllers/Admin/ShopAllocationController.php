<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopAllocation;
use App\Models\Shop;
use App\Models\Market;
use App\Services\ShopAllocationService;
use Illuminate\Http\Request;

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
        $allocation->load(['market', 'shop', 'reviewer', 'approver', 'allocator']);

        // Available vacant shops in the same market for assignment
        $availableShops = Shop::where('market_id', $allocation->market_id)
            ->where(function ($q) use ($allocation) {
                $q->where('status', 'vacant')
                  ->orWhere('id', $allocation->shop_id);
            })
            ->orderBy('block_name')
            ->orderBy('shop_number')
            ->get();

        return view('admin.allocations.show', compact('allocation', 'availableShops'));
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

        $targetStage = !empty($validated['advance_to_recommend']) ? 3 : 2;

        $this->allocationService->advanceStage(
            $allocation,
            $targetStage,
            ['officer_recommendation' => $validated['officer_recommendation']],
            auth()->id()
        );

        return back()->with('success', 'Application review updated successfully.');
    }

    /**
     * Process Stage 4 (Approval by Revenue Directorate)
     */
    public function processApproval(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'approval_notes' => 'required|string|max:1000',
        ]);

        $this->allocationService->advanceStage(
            $allocation,
            4,
            ['approval_notes' => $validated['approval_notes']],
            auth()->id()
        );

        return back()->with('success', 'Application approved by Revenue Directorate.');
    }

    /**
     * Process Stage 5 (Shop Unit Allocation)
     */
    public function processAllocation(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
        ]);

        $this->allocationService->advanceStage(
            $allocation,
            5,
            ['shop_id' => $validated['shop_id']],
            auth()->id()
        );

        return back()->with('success', 'Shop unit successfully allocated to applicant.');
    }

    /**
     * Process Stage 6 (Invoice / Payment confirmation)
     */
    public function processPayment(Request $request, ShopAllocation $allocation)
    {
        $validated = $request->validate([
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $this->allocationService->advanceStage(
            $allocation,
            6,
            [
                'payment_status' => 'paid',
                'payment_reference' => $validated['payment_reference'] ?? 'REC-YSLG-' . date('Y') . '-' . rand(100000, 999999),
            ],
            auth()->id()
        );

        return back()->with('success', 'Payment confirmed for allocation invoice.');
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
     * Official Allocation Certificate & Card View (Replicates UI/index.html alloc-card)
     */
    public function card(ShopAllocation $allocation)
    {
        $allocation->load(['market', 'shop', 'reviewer', 'approver']);
        return view('admin.allocations.card', compact('allocation'));
    }
}
