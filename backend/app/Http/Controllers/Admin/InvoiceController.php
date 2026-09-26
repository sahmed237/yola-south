<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::whereHas('establishment', function($q) {
            $q->areaRestricted();
        })->with(['establishment', 'items']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('establishment', function($estQuery) use ($search) {
                      $estQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Gateway filter
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0])->startOfDay();
                $end = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Display the specified invoice details.
     */
    public function show($id)
    {
        $invoice = Invoice::whereHas('establishment', function($q) {
            $q->areaRestricted();
        })->with([
            'establishment.occupant',
            'items.revenueHead',
            'items.agency',
            'splits.agency'
        ])->findOrFail($id);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Manually reverify invoice payment status.
     */
    public function reverify($id)
    {
        $invoice = Invoice::whereHas('establishment', function($q) {
            $q->areaRestricted();
        })->with([
            'establishment.occupant',
            'items.revenueHead',
            'items.agency',
            'splits.agency'
        ])->findOrFail($id);

        if ($invoice->status === 'success') {
            return redirect()->back()->with('info', 'This invoice has already been settled.');
        }

        try {
            $isSimulated = ($invoice->metadata['simulate'] ?? false) === true && config('app.env') !== 'production';
            $fee = 0.0;

            if (!$isSimulated) {
                $gatewayInstance = \App\Services\Payment\Gateways\PaymentGatewayFactory::create(strtolower($invoice->gateway));
                $gatewayRef = $invoice->metadata['gateway_reference'] ?? $invoice->reference;
                $verifyResult = $gatewayInstance->verify($gatewayRef);

                if ($verifyResult->amountPaid <= 0.0) {
                    return redirect()->back()->with('warning', 'The gateway reported that this payment is still unpaid or abandoned.');
                }

                if ($verifyResult->amountPaid < $invoice->total_amount * 0.99) {
                    return redirect()->back()->with('error', sprintf(
                        'Payment verification failed: Amount paid (₦%s) does not match the invoice total (₦%s).',
                        number_format($verifyResult->amountPaid, 2),
                        number_format($invoice->total_amount, 2)
                    ));
                }
                $fee = $verifyResult->fee;
            } else {
                $isPaystack = strtolower($invoice->gateway) === 'paystack';
                if ($isPaystack) {
                    $fee = $invoice->total_amount * 0.015;
                    if ($invoice->total_amount >= 2500) {
                        $fee += 100;
                    }
                    if ($fee > 2500) {
                        $fee = 2500.0;
                    }
                } else {
                    // Calculate simulated fee (e.g. 1.5% capped at 2000 NGN)
                    $fee = $invoice->total_amount * 0.015;
                    if ($fee > 2000) {
                        $fee = 2000.0;
                    }
                }
            }

            DB::transaction(function () use ($invoice, $fee) {
                $isPaystack = strtolower($invoice->gateway) === 'paystack';
                $feePerRecipient = 0.0;
                $noSubaccountSplitsSum = 0.0;
                $actualFee = $fee;

                if ($isPaystack) {
                    $uniqueSubaccounts = [];
                    $hasNoSubaccount = false;
                    
                    foreach ($invoice->splits as $split) {
                        if ($split->subaccount_code && $split->subaccount_code !== 'NO_SUBACCOUNT') {
                            $uniqueSubaccounts[$split->subaccount_code] = true;
                        } else {
                            $hasNoSubaccount = true;
                            $noSubaccountSplitsSum += $split->amount;
                        }
                    }
                    
                    $recipientsCount = count($uniqueSubaccounts);
                    if ($hasNoSubaccount) {
                        $recipientsCount += 1;
                    }
                    if ($recipientsCount <= 0) {
                        $recipientsCount = 1;
                    }
                    
                    // Calculate exact Paystack fee per recipient in kobo
                    $totalAmountKobo = (int) round($invoice->total_amount * 100);
                    $exactFeeKobo = $totalAmountKobo * 0.015;
                    if ($totalAmountKobo >= 250000) {
                        $exactFeeKobo += 10000;
                    }
                    if ($exactFeeKobo > 250000) {
                        $exactFeeKobo = 250000.0;
                    }
                    
                    $feePerRecipientKobo = (int) ceil($exactFeeKobo / $recipientsCount);
                    $feePerRecipient = $feePerRecipientKobo / 100.0;
                    
                    // Re-calculate total fee based on actual deductions
                    $actualFee = ($feePerRecipientKobo * $recipientsCount) / 100.0;
                }

                // 1. Mark invoice settled and save payment fee
                $invoice->update([
                    'status' => 'success',
                    'payment_fee' => $actualFee
                ]);

                // 2. Insert one Payment per invoice item
                $payments = [];
                foreach ($invoice->items as $item) {
                    $payment = \App\Models\Payment::create([
                        'invoice_id'      => $invoice->id,
                        'establishment_id'=> $invoice->establishment_id,
                        'revenue_head_id' => $item->revenue_head_id,
                        'amount'          => $item->amount,
                        'status'          => 'success',
                        'reference'       => $invoice->reference . '-' . $item->id,
                        'gateway'         => $invoice->gateway,
                    ]);
                    $payments[] = $payment;
                }

                // 3. Update InvoiceSplits and insert PaymentSplits
                $primaryPayment = $payments[0] ?? null;
                foreach ($invoice->splits as $split) {
                    if ($isPaystack) {
                        if ($split->subaccount_code && $split->subaccount_code !== 'NO_SUBACCOUNT') {
                            $netAmount = max(0.00, $split->amount - $feePerRecipient);
                        } else {
                            $splitFeeShare = $noSubaccountSplitsSum > 0 
                                ? ($split->amount / $noSubaccountSplitsSum) * $feePerRecipient 
                                : 0.0;
                            $netAmount = max(0.00, $split->amount - $splitFeeShare);
                        }
                    } else {
                        // Calculate net amount for this subaccount split (proportional for other gateways)
                        $netAmount = max(0.00, $split->amount - ($actualFee * ($split->ratio / 100.0)));
                    }
                    
                    $split->update([
                        'net_amount' => $netAmount
                    ]);

                    if ($primaryPayment) {
                        \App\Models\PaymentSplit::create([
                            'payment_id' => $primaryPayment->id,
                            'agency_id'  => $split->agency_id,
                            'amount'     => $split->amount,
                            'net_amount' => $netAmount,
                            'is_service_fee' => $split->is_service_fee,
                        ]);
                    }
                }

                // 4. Audit Log
                \App\Models\ActivityLog::create([
                    'establishment_id' => $invoice->establishment_id,
                    'user_id'          => auth()->id(),
                    'action_type'      => 'payment_recorded',
                    'remarks'          => sprintf(
                        'Admin manually verified and settled online payment of ₦%s (Gateway Fee: ₦%s, Net: ₦%s) via %s. Invoice: %s.',
                        number_format($invoice->total_amount, 2),
                        number_format($actualFee, 2),
                        number_format($invoice->total_amount - $actualFee, 2),
                        $invoice->gateway,
                        $invoice->reference
                    ),
                ]);
            });

            return redirect()->back()->with('success', 'Payment verified and recorded successfully!');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gateway verification failed: ' . $e->getMessage());
        }
    }
}
