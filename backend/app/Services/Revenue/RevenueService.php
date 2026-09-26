<?php

namespace App\Services\Revenue;

use App\Models\Establishment;
use App\Models\RevenueHead;
use App\Models\RevenueRule;
use App\Models\Agency;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class RevenueService
{
    /**
     * Calculate all applicable revenue for an establishment.
     */
    public function calculateFees(Establishment $establishment): array
    {
        $rules = RevenueHead::active()->with('agency')->get();
        $fees = [];
        $total = 0;

        foreach ($rules as $rule) {
            $amount = $rule->calculateAmount($establishment);
            if ($amount > 0) {
                $fees[] = [
                    'rule_id' => $rule->id,
                    'agency_id' => $rule->agency_id,
                    'agency_name' => $rule->agency->name,
                    'rule_name' => $rule->name,
                    'amount' => $amount,
                ];
                $total += $amount;
            }
        }

        return [
            'fees' => $fees,
            'total' => $total,
        ];
    }

    public function getEstablishmentTaxStatus(Establishment $establishment): array
    {
        $rules = RevenueHead::active()->with('agency')->get();
        $feeDetails = [];
        $totalDue = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $now = Carbon::now();

        $baseYear = (int) ($establishment->base_year ?? config('app.revenue_base_year', $now->year));
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $currentQuarter = (int) ceil($currentMonth / 3);

        foreach ($rules as $rule) {
            $dueAmount = (float) $rule->calculateAmount($establishment);
            if ($dueAmount <= 0) {
                continue;
            }

            // Generate periods based on frequency starting from base year
            $periods = [];
            
            if ($rule->frequency === 'annual') {
                for ($year = $baseYear; $year <= $currentYear; $year++) {
                    $periods[] = [
                        'start' => Carbon::create($year, 1, 1)->startOfYear()->startOfDay(),
                        'end' => Carbon::create($year, 12, 31)->endOfYear()->endOfDay(),
                        'label' => "Year " . $year,
                    ];
                }
            } elseif ($rule->frequency === 'quarterly') {
                for ($year = $baseYear; $year <= $currentYear; $year++) {
                    $maxQuarter = ($year === $currentYear) ? $currentQuarter : 4;
                    for ($quarter = 1; $quarter <= $maxQuarter; $quarter++) {
                        $startMonth = (($quarter - 1) * 3) + 1;
                        $endMonth = $quarter * 3;
                        $periods[] = [
                            'start' => Carbon::create($year, $startMonth, 1)->startOfMonth()->startOfDay(),
                            'end' => Carbon::create($year, $endMonth, 1)->endOfMonth()->endOfDay(),
                            'label' => "Quarter " . $quarter . ", " . $year,
                        ];
                    }
                }
            } elseif ($rule->frequency === 'monthly') {
                for ($year = $baseYear; $year <= $currentYear; $year++) {
                    $maxMonth = ($year === $currentYear) ? $currentMonth : 12;
                    for ($month = 1; $month <= $maxMonth; $month++) {
                        $periods[] = [
                            'start' => Carbon::create($year, $month, 1)->startOfMonth()->startOfDay(),
                            'end' => Carbon::create($year, $month, 1)->endOfMonth()->endOfDay(),
                            'label' => Carbon::create($year, $month, 1)->format('F Y'),
                        ];
                    }
                }
            } elseif ($rule->frequency === 'daily') {
                // For daily frequency, start from current year (or base year if it matches) to prevent excessive overhead
                $dayYear = max($baseYear, $currentYear);
                $current = Carbon::create($dayYear, 1, 1)->startOfDay();
                $endLimit = $now->copy()->endOfDay();
                while ($current->lte($endLimit)) {
                    $periods[] = [
                        'start' => $current->copy()->startOfDay(),
                        'end' => $current->copy()->endOfDay(),
                        'label' => $current->format('M d, Y'),
                    ];
                    $current->addDay();
                }
            } else {
                $periods[] = [
                    'start' => null,
                    'end' => null,
                    'label' => "Current Billing",
                ];
            }

            // Fetch payments for each period via invoice_items.period label
            // Payments are matched by:
            //   1. establishment_id on the invoice
            //   2. revenue_rule_id on the invoice_item
            //   3. period label on the invoice_item (e.g. "Year 2024", "Quarter 1, 2024")
            //   4. invoice status = success (ensures only settled payments count)
            foreach ($periods as $period) {
                $paidAmount = (float) \App\Models\InvoiceItem::query()
                    ->whereHas('invoice', function ($q) use ($establishment) {
                        $q->where('establishment_id', $establishment->id)
                          ->where('status', 'success');
                    })
                    ->where('revenue_head_id', $rule->id)
                    ->where('period', $period['label'])
                    ->sum('amount');

                $outstandingAmount = max(0.0, $dueAmount - $paidAmount);

                $status = 'Unpaid';
                if ($paidAmount >= $dueAmount) {
                    $status = 'Paid';
                } elseif ($paidAmount > 0) {
                    $status = 'Partially Paid';
                }

                $feeDetails[] = [
                    'head_id'            => $rule->id,
                    'head_name'          => $rule->name,
                    'rule_id'            => $rule->id,
                    'rule_name'          => $rule->name,
                    'agency_id'          => $rule->agency_id,
                    'agency_name'        => $rule->agency->name ?? 'N/A',
                    'frequency'          => ucfirst($rule->frequency),
                    'due_amount'         => $dueAmount,
                    'paid_amount'        => $paidAmount,
                    'outstanding_amount' => $outstandingAmount,
                    'status'             => $status,
                    'period'             => $period['label'],
                ];

                $totalDue         += $dueAmount;
                $totalPaid        += $paidAmount;
                $totalOutstanding += $outstandingAmount;
            }
        }

        return [
            'fees' => $feeDetails,
            'totals' => [
                'due' => $totalDue,
                'paid' => $totalPaid,
                'outstanding' => $totalOutstanding,
            ]
        ];
    }

    /**
     * Get split configuration for gateways.
     */
    public function getSplitConfig(array $calculatedFees): array
    {
        $total = $calculatedFees['total'];
        if ($total <= 0) return [];

        $paystackSplits = [];
        $monnifySplits = [];

        foreach ($calculatedFees['fees'] as $fee) {
            $agency = Agency::with(['paystackSubAccount', 'monnifySubAccount'])->find($fee['agency_id']);
            
            $ratio = ($fee['amount'] / $total) * 100;

            if ($agency->paystackSubAccount) {
                $paystackSplits[] = [
                    'subaccount' => $agency->paystackSubAccount->subaccount_code,
                    'share' => $ratio, // percentage
                ];
            }

            if ($agency->monnifySubAccount) {
                $monnifySplits[] = [
                    'subAccountCode' => $agency->monnifySubAccount->subaccount_code,
                    'splitPercentage' => $ratio,
                    'feeBearer' => false,
                ];
            }
        }

        return [
            'paystack' => $paystackSplits,
            'monnify' => $monnifySplits,
            'total' => $total,
        ];
    }
}
