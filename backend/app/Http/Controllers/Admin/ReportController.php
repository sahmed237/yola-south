<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSplit;
use App\Models\Payment;
use App\Models\Agency;
use App\Models\RevenueRule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display report dashboard, filtered collections list, and analytics.
     */
    public function index(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $paymentQuery = $this->buildPaymentQuery($request);

        // Fetch filter dropdown options
        $agencies = Agency::orderBy('name')->get();
        $revenueRules = RevenueRule::orderBy('name')->get();

        // 1. Calculate Aggregations
        $rawStats = (clone $query)->select(
            DB::raw('SUM(amount) as total_gross'),
            DB::raw('SUM(CASE WHEN net_amount IS NULL THEN amount ELSE net_amount END) as total_net')
        )->first();

        $totalGross = (float) ($rawStats->total_gross ?? 0.0);
        $totalNet = (float) ($rawStats->total_net ?? 0.0);
        $totalFees = max(0.0, $totalGross - $totalNet);

        // 2. Calculate Agency Breakdown (Gross/Net)
        $breakdownQuery = (clone $query)
            ->select(
                'agency_id',
                DB::raw('SUM(amount) as gross_collected'),
                DB::raw('SUM(CASE WHEN net_amount IS NULL THEN amount ELSE net_amount END) as net_collected')
            )
            ->groupBy('agency_id')
            ->with('agency')
            ->get();

        $agencyBreakdown = $breakdownQuery->map(function ($row) {
            $gross = (float) $row->gross_collected;
            $net = (float) $row->net_collected;
            return [
                'agency_name' => $row->agency->name ?? 'Deleted Agency',
                'agency_code' => $row->agency->code ?? 'N/A',
                'is_service_fee' => (bool) ($row->agency->is_service_fee ?? false),
                'gross' => $gross,
                'fees' => max(0.0, $gross - $net),
                'net' => $net,
            ];
        })->sortByDesc('gross')->values();

        // 3. Revenue Rules Breakdown (Source Collections)
        $rulesBreakdownQuery = (clone $paymentQuery)
            ->select(
                'revenue_rule_id',
                DB::raw('SUM(amount) as total_collected'),
                DB::raw('COUNT(id) as payment_count')
            )
            ->groupBy('revenue_rule_id')
            ->with('revenueRule')
            ->get();

        $rulesBreakdown = $rulesBreakdownQuery->map(function ($row) {
            return [
                'rule_name' => $row->revenueRule->name ?? 'Deleted Rule',
                'rule_code' => $row->revenueRule->code ?? 'N/A',
                'amount' => (float) $row->total_collected,
                'count' => (int) $row->payment_count,
            ];
        })->sortByDesc('amount')->values();

        // 4. Trend Over Time (Daily)
        $trendDataQuery = (clone $query)
            ->select(
                DB::raw('DATE(created_at) as trend_date'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('trend_date')
            ->orderBy('trend_date', 'ASC')
            ->get();

        $trendLabels = [];
        $trendValues = [];
        foreach ($trendDataQuery as $trendRow) {
            $trendLabels[] = Carbon::parse($trendRow->trend_date)->format('M d');
            $trendValues[] = (float) $trendRow->total_amount;
        }

        // 5. Paginated Transaction Splits
        $splits = $query->latest()->paginate(20)->withQueryString();

        return view('admin.reports.index', compact(
            'splits',
            'agencies',
            'revenueRules',
            'totalGross',
            'totalNet',
            'totalFees',
            'agencyBreakdown',
            'rulesBreakdown',
            'trendLabels',
            'trendValues'
        ));
    }

    /**
     * Export the filtered reports database to CSV.
     */
    public function export(Request $request)
    {
        $query = $this->buildBaseQuery($request)->latest();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="revenue_report_' . date('Ymd_His') . '.csv"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => '0',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Transaction Ref',
                'Payment Date',
                'Establishment Name',
                'Recipient Agency',
                'Agency Code',
                'Split Type',
                'Gateway / Channel',
                'Gross Split Amount (NGN)',
                'Gateway Fees (NGN)',
                'Net Settled Amount (NGN)'
            ]);

            // Chunk splits to avoid running out of memory
            $query->chunk(500, function ($splits) use ($file) {
                foreach ($splits as $split) {
                    $gross = (float) $split->amount;
                    $net = (float) ($split->net_amount ?? $split->amount);
                    $fee = max(0.0, $gross - $net);
                    $payment = $split->payment;

                    fputcsv($file, [
                        $payment->reference ?? 'N/A',
                        $payment ? ($payment->created_at->format('Y-m-d H:i:s')) : 'N/A',
                        $payment->establishment->name ?? 'N/A',
                        $split->agency->name ?? 'N/A',
                        $split->agency->code ?? 'N/A',
                        $split->is_service_fee ? 'Service Fee' : 'Revenue Split',
                        $payment->gateway ?? 'N/A',
                        number_format($gross, 2, '.', ''),
                        number_format($fee, 2, '.', ''),
                        number_format($net, 2, '.', '')
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Construct helper query based on filter requests.
     */
    protected function buildBaseQuery(Request $request)
    {
        $query = PaymentSplit::whereHas('payment.establishment', function($q) {
            $q->areaRestricted();
        })->with(['payment.establishment', 'agency']);

        // Filter by Date Range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0])->startOfDay();
                $end = Carbon::parse($dates[1])->endOfDay();
                $query->whereHas('payment', function($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                });
            }
        }

        // Filter by Agency
        if ($request->filled('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }

        // Filter by Revenue Rule
        if ($request->filled('revenue_rule_id')) {
            $ruleId = $request->revenue_rule_id;
            $query->whereHas('payment', function($q) use ($ruleId) {
                $q->where('revenue_rule_id', $ruleId);
            });
        }

        // Filter by Gateway / Channel
        if ($request->filled('gateway')) {
            $gateway = $request->gateway;
            $query->whereHas('payment', function($q) use ($gateway) {
                $q->where('gateway', $gateway);
            });
        }

        return $query;
    }

    /**
     * Helper query for payment records (source collections).
     */
    protected function buildPaymentQuery(Request $request)
    {
        $query = Payment::whereHas('establishment', function($q) {
            $q->areaRestricted();
        })->where('status', 'success')->with(['revenueRule', 'establishment']);

        // Filter by Date Range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0])->startOfDay();
                $end = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        // Filter by Agency (through RevenueRule)
        if ($request->filled('agency_id')) {
            $agencyId = $request->agency_id;
            $query->whereHas('revenueRule', function($q) use ($agencyId) {
                $q->where('agency_id', $agencyId);
            });
        }

        // Filter by Revenue Rule
        if ($request->filled('revenue_rule_id')) {
            $query->where('revenue_rule_id', $request->revenue_rule_id);
        }

        // Filter by Gateway / Channel
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        return $query;
    }
}
