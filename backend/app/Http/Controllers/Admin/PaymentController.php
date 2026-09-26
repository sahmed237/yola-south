<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentSplit;
use App\Models\Establishment;
use App\Models\RevenueHead;
use App\Models\RevenueRule;
use App\Models\Agency;
use App\Services\Revenue\RevenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    protected $revenueService;

    public function __construct(RevenueService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * Display the interactive payments dashboard.
     */
    public function index(Request $request)
    {
        $query = Payment::whereHas('establishment', function($q) {
            $q->areaRestricted();
        })->with(['establishment', 'revenueHead', 'revenueHead.agency']);

        // Search & Filter options
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                  ->orWhereHas('establishment', function($estQuery) use ($search) {
                      $estQuery->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('revenueHead', function($ruleQuery) use ($search) {
                      $ruleQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0])->startOfDay();
                $end = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        // 1. Calculate Real-time Interactive Statistics
        $totalRevenue = (float) Payment::whereHas('establishment', function($q) { $q->areaRestricted(); })->where('status', 'success')->sum('amount');
        $cashRevenue = (float) Payment::whereHas('establishment', function($q) { $q->areaRestricted(); })->where('status', 'success')->where('gateway', 'Cash')->sum('amount');
        $electronicRevenue = (float) Payment::whereHas('establishment', function($q) { $q->areaRestricted(); })->where('status', 'success')->whereIn('gateway', ['Paystack', 'Monnify', 'Bank Transfer'])->sum('amount');

        // Total Projected Due & Outstanding across all Approved Establishments
        $approvedEstablishments = Establishment::areaRestricted()->where('status', 'approved')->get();
        $totalOutstanding = 0;
        $totalProjectedDue = 0;
        foreach ($approvedEstablishments as $est) {
            $tax = $this->revenueService->getEstablishmentTaxStatus($est);
            $totalOutstanding += $tax['totals']['outstanding'];
            $totalProjectedDue += $tax['totals']['due'];
        }

        $collectionRate = ($totalProjectedDue > 0) ? ($totalRevenue / $totalProjectedDue) * 100 : 0;

        // Daily collection trends for the last 7 days
        $trends = Payment::whereHas('establishment', function($q) { $q->areaRestricted(); })
            ->where('status', 'success')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        $trendLabels = [];
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $trendLabels[] = Carbon::now()->subDays($i)->format('M d');
            $trendData[] = (float) ($trends[$dateStr] ?? 0.0);
        }

        // Establishments with outstanding balances for the direct collection form
        $debtorEstablishments = [];
        foreach ($approvedEstablishments as $est) {
            $tax = $this->revenueService->getEstablishmentTaxStatus($est);
            if ($tax['totals']['outstanding'] > 0) {
                $debtorEstablishments[] = [
                    'id' => $est->id,
                    'name' => $est->name,
                    'unique_id' => $est->unique_id,
                    'outstanding' => $tax['totals']['outstanding'],
                    'rules' => array_values(array_filter($tax['fees'], function($fee) {
                        return $fee['outstanding_amount'] > 0;
                    }))
                ];
            }
        }

        return view('admin.payments.index', compact(
            'payments',
            'totalRevenue',
            'cashRevenue',
            'electronicRevenue',
            'totalOutstanding',
            'collectionRate',
            'trendLabels',
            'trendData',
            'debtorEstablishments'
        ));
    }

    /**
     * Record payment for a specific revenue rule from the details page.
     */
    public function payRule(Request $request, $id)
    {
        if (!config('services.manual_payment')) {
            abort(403, 'Manual payment collection is disabled in system configuration.');
        }

        $establishment = Establishment::areaRestricted()->findOrFail($id);

        $validated = $request->validate([
            'revenue_head_id' => 'required_without:revenue_rule_id|nullable|exists:revenue_heads,id',
            'revenue_rule_id' => 'nullable|exists:revenue_heads,id',
            'amount' => 'required|numeric|min:0.01',
            'gateway' => 'required|string|in:Bank Transfer',
            'reference' => 'required|string|unique:payments,reference',
        ]);

        $headId = $validated['revenue_head_id'] ?? $validated['revenue_rule_id'];
        $head = RevenueHead::findOrFail($headId);

        return DB::transaction(function() use ($validated, $establishment, $head) {
            // 1. Create the Payment
            $payment = Payment::create([
                'establishment_id' => $establishment->id,
                'revenue_head_id' => $head->id,
                'amount' => $validated['amount'],
                'status' => 'success',
                'reference' => $validated['reference'],
                'gateway' => $validated['gateway'],
                'officer_id' => auth()->id()
            ]);

            // 2. Create the Payment Split for the associated Agency
            PaymentSplit::create([
                'payment_id' => $payment->id,
                'agency_id' => $head->agency_id,
                'amount' => $validated['amount'],
                'is_service_fee' => false,
            ]);

            // 3. Log this action in the establishment audit trail
            \App\Models\ActivityLog::create([
                'establishment_id' => $establishment->id,
                'user_id' => auth()->id(),
                'action_type' => 'payment_recorded',
                'remarks' => "Recorded tax payment of ₦" . number_format($validated['amount'], 2) . " for '" . $head->name . "' via " . $validated['gateway'] . ". Ref: " . $validated['reference']
            ]);

            return redirect()->back()->with('success', 'Tax payment of ₦' . number_format($validated['amount'], 2) . ' successfully recorded for ' . $head->name . '!');
        });
    }

    /**
     * Settle direct payment from the payments dashboard utility.
     */
    public function directPay(Request $request)
    {
        if (!config('services.manual_payment')) {
            abort(403, 'Manual payment collection is disabled in system configuration.');
        }

        $validated = $request->validate([
            'establishment_id' => 'required|exists:establishments,id',
            'revenue_head_id' => 'required_without:revenue_rule_id|nullable|exists:revenue_heads,id',
            'revenue_rule_id' => 'nullable|exists:revenue_heads,id',
            'amount' => 'required|numeric|min:0.01',
            'gateway' => 'required|string|in:Bank Transfer',
            'reference' => 'required|string|unique:payments,reference',
        ]);

        $establishment = Establishment::areaRestricted()->findOrFail($validated['establishment_id']);
        $headId = $validated['revenue_head_id'] ?? $validated['revenue_rule_id'];
        $head = RevenueHead::findOrFail($headId);

        return DB::transaction(function() use ($validated, $establishment, $head) {
            $payment = Payment::create([
                'establishment_id' => $establishment->id,
                'revenue_head_id' => $head->id,
                'amount' => $validated['amount'],
                'status' => 'success',
                'reference' => $validated['reference'],
                'gateway' => $validated['gateway'],
                'officer_id' => auth()->id()
            ]);

            PaymentSplit::create([
                'payment_id' => $payment->id,
                'agency_id' => $rule->agency_id,
                'amount' => $validated['amount'],
                'is_service_fee' => false,
            ]);

            \App\Models\ActivityLog::create([
                'establishment_id' => $establishment->id,
                'user_id' => auth()->id(),
                'action_type' => 'payment_recorded',
                'remarks' => "Direct dashboard payment of ₦" . number_format($validated['amount'], 2) . " recorded for '" . $rule->name . "' via " . $validated['gateway'] . ". Ref: " . $validated['reference']
            ]);

            return redirect()->back()->with('success', 'Direct payment of ₦' . number_format($validated['amount'], 2) . ' successfully recorded!');
        });
    }
}
