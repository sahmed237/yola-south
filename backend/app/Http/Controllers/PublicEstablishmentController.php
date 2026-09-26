<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Establishment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSplit;
use App\Models\Payment;
use App\Models\PaymentSplit;
use App\Models\RevenueHead;
use App\Models\RevenueRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicEstablishmentController extends Controller
{
    protected $revenueService;

    public function __construct(\App\Services\Revenue\RevenueService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * Public portal landing page.
     */
    public function landing(Request $request)
    {
        $totalRules           = RevenueHead::active()->count();
        $totalEstablishments  = Establishment::where('status', 'approved')->count();
        $totalRevenue         = Payment::where('status', 'success')->sum('amount');

        return view('public.landing', compact('totalRules', 'totalEstablishments', 'totalRevenue'));
    }

    /**
     * Display the public How to Pay tutorial page.
     */
    public function howToPay()
    {
        return view('public.how_to_pay');
    }

    /**
     * Display the public FAQs page.
     */
    public function faq()
    {
        $faqs = \App\Models\Faq::where('is_published', true)->orderBy('sort_order', 'asc')->get();
        return view('public.faq', compact('faqs'));
    }

    /**
     * Search and lookup active establishments.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return redirect()->route('public.landing')->with('error', 'Please enter a search query.');
        }

        $establishments = Establishment::where('status', 'approved')
            ->where(function ($q) use ($query) {
                $q->where('unique_id', $query)
                  ->orWhere('name', 'LIKE', "%{$query}%")
                  ->orWhereHas('occupant', function ($sub) use ($query) {
                      $sub->where('name', 'LIKE', "%{$query}%");
                  })
                  ->orWhereHas('owner', function ($sub) use ($query) {
                      $sub->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->with(['occupant', 'establishmentType', 'establishmentSize', 'images'])
            ->get();

        return view('public.search', compact('establishments', 'query'));
    }

    /**
     * Display all approved establishments on a map.
     */
    public function map(Request $request)
    {
        $establishments = Establishment::where('status', 'approved')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['establishmentType', 'establishmentSize', 'occupant', 'owner'])
            ->get();

        return view('public.map', compact('establishments'));
    }

    /**
     * Show tax details for a specific public establishment.
     */
    public function show($uniqueId)
    {
        $establishment = Establishment::where('unique_id', $uniqueId)
            ->where('status', 'approved')
            ->with(['occupant', 'establishmentType', 'establishmentSize', 'owner'])
            ->firstOrFail();

        $taxStatus = $this->revenueService->getEstablishmentTaxStatus($establishment);
        $serviceFeeAgency = Agency::where('is_service_fee', true)->where('status', true)->first();

        return view('public.establishment', compact('establishment', 'taxStatus', 'serviceFeeAgency'));
    }

    /**
     * Initialise checkout: create Invoice + InvoiceItems + InvoiceSplits, then show gateway page.
     *
     * Flow:
     *   1. Validate & decode selected items from the frontend.
     *   2. Verify the submitted total matches the sum of item amounts.
     *   3. Group items by agency → compute split amount & ratio per agency.
     *   4. Resolve each agency's gateway subaccount code.
     *   5. Persist Invoice, InvoiceItems, and InvoiceSplits inside a transaction.
     *   6. Return the checkout/gateway view (no Payment rows yet).
     */
    public function pay(Request $request)
    {
        $validated = $request->validate([
            'establishment_id' => 'required|exists:establishments,id',
            'items'            => 'required|json',
            'email'            => 'required|email',
            'phone'            => 'required|string',
            'gateway'          => 'required|string|in:Paystack,Monnify',
            'amount'           => 'required|numeric|min:0.01',
        ]);

        $establishment = Establishment::findOrFail($validated['establishment_id']);
        $items         = json_decode($validated['items'], true);
        $gateway       = $validated['gateway'];

        // --- 1. Verify total -------------------------------------------------
        $taxItemsSum = (float) collect($items)->sum('amount');
        
        $serviceFeeAgency = Agency::where('is_service_fee', true)->where('status', true)->first();
        $serviceFeeAmount = $serviceFeeAgency ? (float) $serviceFeeAgency->service_fee_amount : 0.0;
        
        $calculatedTotal = $taxItemsSum + $serviceFeeAmount;

        if (abs($calculatedTotal - (float) $validated['amount']) > 0.01) {
            return back()->withErrors(['amount' => 'Checkout total mismatch. Please retry selection.']);
        }

        // Adjust calculatedTotal and serviceFeeAmount for Paystack to cover the processing fee
        if (strtolower($gateway) === 'paystack' && $serviceFeeAgency && $serviceFeeAmount > 0) {
            // Determine unique Paystack recipients count
            $uniqueSubaccounts = [];
            $hasNoSubaccount = false;
            
            foreach ($items as $item) {
                $agency = Agency::with('paystackSubAccount')->find($item['agency_id']);
                $subaccountCode = $agency->paystackSubAccount->subaccount_code ?? null;
                if ($subaccountCode && $subaccountCode !== 'NO_SUBACCOUNT') {
                    $uniqueSubaccounts[$subaccountCode] = true;
                } else {
                    $hasNoSubaccount = true;
                }
            }
            
            $sfSubaccountCode = $serviceFeeAgency->paystackSubAccount->subaccount_code ?? null;
            if ($sfSubaccountCode && $sfSubaccountCode !== 'NO_SUBACCOUNT') {
                $uniqueSubaccounts[$sfSubaccountCode] = true;
            } else {
                $hasNoSubaccount = true;
            }
            
            $recipientsCount = count($uniqueSubaccounts);
            if ($hasNoSubaccount) {
                $recipientsCount += 1;
            }
            
            if ($recipientsCount > 0) {
                // Try assuming T >= 2500
                $adjustedTotal = ($taxItemsSum + $serviceFeeAmount + (100.0 / $recipientsCount)) / (1.0 - (0.015 / $recipientsCount));
                
                // Calculate fee for this T
                $fee = $adjustedTotal * 0.015 + 100.0;
                
                if ($fee > 2500.0) {
                    $adjustedTotal = $taxItemsSum + $serviceFeeAmount + (2500.0 / $recipientsCount);
                } elseif ($adjustedTotal < 2500.0) {
                    // Recalculate using under-2500 formula (no flat 100 NGN fee)
                    $adjustedTotal = ($taxItemsSum + $serviceFeeAmount) / (1.0 - (0.015 / $recipientsCount));
                }
                
                $calculatedTotal = round($adjustedTotal, 2);
                $serviceFeeAmount = round($calculatedTotal - $taxItemsSum, 2);
            }
        }

        // --- 2. Group by agency & compute splits -----------------------------
        $agencyGroups = [];
        foreach ($items as $item) {
            $aid = $item['agency_id'];
            if (!isset($agencyGroups[$aid])) {
                $agencyGroups[$aid] = [
                    'agency_id'   => $aid,
                    'agency_name' => $item['agency_name'],
                    'amount'      => 0.0,
                ];
            }
            $agencyGroups[$aid]['amount'] += (float) $item['amount'];
        }

        $splits = [];
        foreach ($agencyGroups as $agId => $group) {
            $agency = Agency::with(['paystackSubAccount', 'monnifySubAccount'])->find($agId);
            $ratio  = ($group['amount'] / $calculatedTotal) * 100;

            $subaccountCode = null;
            if ($agency) {
                $subaccountCode = $gateway === 'Paystack'
                    ? ($agency->paystackSubAccount->subaccount_code ?? null)
                    : ($agency->monnifySubAccount->subaccount_code  ?? null);
            }

            $splits[] = [
                'agency_id'      => $agId,
                'agency_name'    => $group['agency_name'],
                'amount'         => $group['amount'],
                'ratio'          => round($ratio, 4),
                'subaccount'     => $subaccountCode ?? 'NO_SUBACCOUNT',
                'is_service_fee' => false,
            ];
        }

        // Add service fee split if applicable
        if ($serviceFeeAgency && $serviceFeeAmount > 0) {
            $sfSubaccountCode = $gateway === 'Paystack'
                ? ($serviceFeeAgency->paystackSubAccount->subaccount_code ?? null)
                : ($serviceFeeAgency->monnifySubAccount->subaccount_code  ?? null);

            $sfRatio = ($serviceFeeAmount / $calculatedTotal) * 100;

            $splits[] = [
                'agency_id'      => $serviceFeeAgency->id,
                'agency_name'    => $serviceFeeAgency->name,
                'amount'         => $serviceFeeAmount,
                'ratio'          => round($sfRatio, 4),
                'subaccount'     => $sfSubaccountCode ?? 'NO_SUBACCOUNT',
                'is_service_fee' => true,
            ];
        }

        // --- 3. Persist Invoice, Items, and Splits in one transaction --------
        $invoice = DB::transaction(function () use ($validated, $establishment, $items, $splits, $calculatedTotal, $gateway) {
            $reference = 'INV-' . strtoupper($gateway) . '-' . time() . '-' . rand(1000, 9999);

            $invoice = Invoice::create([
                'establishment_id' => $establishment->id,
                'reference'        => $reference,
                'gateway'          => $gateway,
                'email'            => $validated['email'],
                'total_amount'     => $calculatedTotal,
                'status'           => 'pending',
                'metadata'         => [
                    'phone' => $validated['phone']
                ]
            ]);

            // One row per revenue head line
            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'revenue_head_id' => $item['head_id'] ?? $item['rule_id'],
                    'agency_id'       => $item['agency_id'],
                    'period'          => $item['period'],
                    'amount'          => (float) $item['amount'],
                ]);
            }

            // One row per agency receiving funds
            foreach ($splits as $split) {
                InvoiceSplit::create([
                    'invoice_id'     => $invoice->id,
                    'agency_id'      => $split['agency_id'],
                    'amount'         => $split['amount'],
                    'ratio'          => $split['ratio'],
                    'subaccount_code'=> $split['subaccount'],
                    'is_service_fee' => $split['is_service_fee'],
                ]);
            }

            return $invoice;
        });

        // Eager-load relations for the checkout view
        $invoice->load(['items.revenueHead', 'items.revenueRule', 'items.agency', 'splits.agency', 'establishment.occupant']);

        // --- 4. Initialize Payment Gateway ----------------------------------
        try {
            $gatewayInstance = \App\Services\Payment\Gateways\PaymentGatewayFactory::create(strtolower($gateway));
            // Generate callback URL
            $callbackUrl = route('public.payment-success', ['reference' => $invoice->reference]);
            // Call initialize
            $paymentInitResult = $gatewayInstance->initialize($invoice, $validated['email'], $validated['phone'], $callbackUrl);

            // Update invoice metadata with gateway details
            $invoice->update([
                'metadata' => array_merge($invoice->metadata ?? [], [
                    'phone' => $validated['phone'],
                    'gateway_reference' => $paymentInitResult->reference,
                    'redirect_url' => $paymentInitResult->redirectUrl,
                    'raw_response' => $paymentInitResult->rawResponse,
                ])
            ]);

            $redirectUrl = $paymentInitResult->redirectUrl;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Unified Payment initialization failed: " . $e->getMessage());
            $redirectUrl = route('public.payment-success', ['reference' => $invoice->reference, 'simulate' => 'true']);
        }

        return view('public.checkout', compact('invoice', 'establishment', 'splits', 'redirectUrl'));
    }

    /**
     * Gateway callback / success handler.
     *
     * Flow (only runs once because we guard on invoice status):
     *   1. Mark the Invoice as "success".
     *   2. For each InvoiceItem → insert one Payment row.
     *   3. For each InvoiceSplit → insert one PaymentSplit row (referencing the first payment,
     *      or we could store splits per-payment – see note below).
     *   4. Log activity.
     */
    public function success(Request $request, $reference)
    {
        $invoice       = Invoice::with(['items.revenueHead', 'items.revenueRule', 'items.agency', 'splits.agency', 'establishment.occupant'])->where('reference', $reference)->firstOrFail();
        $establishment = $invoice->establishment;

        // If the request wants JSON, perform the actual verification
        if ($request->wantsJson() || $request->ajax() || $request->query('action') === 'verify') {
            try {
                if ($invoice->status === 'pending') {
                    $isSimulated = ($request->query('simulate') === 'true' || ($invoice->metadata['simulate'] ?? false) === true) && config('app.env') !== 'production';
                    $fee = 0.0;

                    if (!$isSimulated) {
                        $gatewayInstance = \App\Services\Payment\Gateways\PaymentGatewayFactory::create(strtolower($invoice->gateway));
                        $gatewayRef = $invoice->metadata['gateway_reference'] ?? $invoice->reference;
                        $verifyResult = $gatewayInstance->verify($gatewayRef);

                        if ($verifyResult->amountPaid < $invoice->total_amount * 0.99) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Payment verification failed: Amount paid does not match invoice total.'
                            ], 400);
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
                            // Calculate simulated transaction fee (e.g. 1.5% capped at 2000 NGN)
                            $fee = $invoice->total_amount * 0.015;
                            if ($fee > 2000) {
                                $fee = 2000.0;
                            }
                        }
                    }

                    DB::transaction(function () use ($invoice, $establishment, $fee) {
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

                        // 1. Mark invoice settled and save the payment fee
                        $invoice->update([
                            'status' => 'success',
                            'payment_fee' => $actualFee
                        ]);

                        // 2. Insert one Payment per invoice item (one per revenue head line)
                        $payments = [];
                        foreach ($invoice->items as $item) {
                            $payment = Payment::create([
                                'invoice_id'      => $invoice->id,
                                'establishment_id'=> $establishment->id,
                                'revenue_head_id' => $item->revenue_head_id ?? $item->revenue_rule_id,
                                'amount'          => $item->amount,
                                'status'          => 'success',
                                'reference'       => $invoice->reference . '-' . $item->id,
                                'gateway'         => $invoice->gateway,
                            ]);
                            $payments[] = $payment;
                        }

                        // 3. Update InvoiceSplits with net amounts, and insert PaymentSplits
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
                                PaymentSplit::create([
                                    'payment_id' => $primaryPayment->id,
                                    'agency_id'  => $split->agency_id,
                                    'amount'     => $split->amount,
                                    'net_amount' => $netAmount,
                                    'is_service_fee' => $split->is_service_fee,
                                ]);
                            }
                        }

                        // 4. Audit log
                        \App\Models\ActivityLog::create([
                            'establishment_id' => $establishment->id,
                            'action_type'      => 'payment_recorded',
                            'remarks'          => sprintf(
                                'Public online unified payment of ₦%s (Fee: ₦%s, Net: ₦%s) settled via %s. Invoice: %s (%d rule(s) paid).',
                                number_format($invoice->total_amount, 2),
                                number_format($actualFee, 2),
                                number_format($invoice->total_amount - $actualFee, 2),
                                $invoice->gateway,
                                $invoice->reference,
                                $invoice->items->count()
                            ),
                        ]);
                    });
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified and recorded successfully.'
                ]);

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("AJAX Payment verification error for reference {$reference}: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Verification failed: ' . $e->getMessage()
                ], 500);
            }
        }

        // Fetch system settings for custom theme coloring
        $system_settings = \App\Models\Setting::getAllSettings();

        return view('public.success', compact('invoice', 'establishment', 'system_settings'));
    }

    /**
     * Public page to verify an already settled receipt.
     */
    public function verifyReceipt($reference)
    {
        $invoice = Invoice::with(['items.revenueHead', 'items.revenueRule', 'items.agency', 'splits.agency', 'establishment.occupant'])
            ->where('reference', $reference)
            ->first();

        // Check if invoice is null or not successful
        $isValid = $invoice && $invoice->status === 'success';
        
        $establishment = $invoice ? $invoice->establishment : null;
        
        // Fetch system settings for custom theme coloring
        $system_settings = \App\Models\Setting::getAllSettings();

        return view('public.verify', compact('invoice', 'establishment', 'isValid', 'system_settings'));
    }
}
