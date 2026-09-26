<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\PaystackSubAccount;
use App\Models\MonnifySubAccount;
use App\Services\Payment\PaymentApi\PaystackApi;
use App\Services\Payment\PaymentApi\MonnifyApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ServiceFeeAgencyController extends Controller
{
    public function index()
    {
        $agencies = Agency::with(['paystackSubAccount', 'monnifySubAccount'])
            ->where('is_service_fee', true)
            ->paginate(20);
        return view('admin.service_fee_agencies.index', compact('agencies'));
    }

    public function create()
    {
        $paystackBanks = [];
        $monnifyBanks = [];

        if (\App\Models\Setting::get('paystack_active', true)) {
            try {
                $paystackBanks = PaystackApi::getInstance()->getBanks();
            } catch (Exception $e) {
                Log::error('Paystack Banks Fetch Failed: ' . $e->getMessage());
            }
        }

        if (\App\Models\Setting::get('monnify_active', true)) {
            try {
                $monnifyBanks = MonnifyApi::getInstance()->getBanks();
            } catch (Exception $e) {
                Log::error('Monnify Banks Fetch Failed: ' . $e->getMessage());
            }
        }

        return view('admin.service_fee_agencies.create', compact('paystackBanks', 'monnifyBanks'));
    }

    public function store(Request $request)
    {
        $isPaystackActive = \App\Models\Setting::get('paystack_active', true);
        $isMonnifyActive = \App\Models\Setting::get('monnify_active', true);

        $rules = [
            'name' => 'required|string|max:255|unique:agencies,name',
            'code' => 'required|string|max:50|unique:agencies,code',
            'service_fee_amount' => 'required|numeric|min:0',
            'bank_name' => 'required|string|max:255',
            'account_number' => [
                'required',
                'string',
                'digits:10',
                function ($attribute, $value, $fail) use ($request, $isPaystackActive, $isMonnifyActive) {
                    $primaryBankCode = $isPaystackActive ? $request->bank_code_paystack : $request->bank_code_monnify;
                    $exists = Agency::where('account_number', $value)
                        ->where('bank_code', $primaryBankCode)
                        ->exists();
                    if ($exists) {
                        $fail('This bank account is already registered to another agency/service fee org.');
                    }
                }
            ],
            'account_name' => 'required|string|max:255',
            'email' => 'required|email',
        ];

        $rules['bank_code_paystack'] = $isPaystackActive ? 'required|string' : 'nullable|string';
        $rules['bank_code_monnify'] = $isMonnifyActive ? 'required|string' : 'nullable|string';

        $request->validate($rules);

        $primaryBankCode = $isPaystackActive ? $request->bank_code_paystack : $request->bank_code_monnify;

        // Create the agency locally first
        $agency = Agency::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'bank_name' => $request->bank_name,
            'bank_code' => $primaryBankCode, 
            'account_number' => $request->account_number,
            'account_name' => strtoupper($request->account_name),
            'email' => $request->email,
            'status' => true,
            'is_service_fee' => true,
            'service_fee_amount' => $request->service_fee_amount,
        ]);

        $errors = [];

        // 1. Create Paystack Subaccount (only if active)
        if ($isPaystackActive) {
            try {
                $paystackResponse = PaystackApi::getInstance()->createSubAccount([
                    'business_name' => $agency->name,
                    'settlement_bank' => $request->bank_code_paystack,
                    'account_number' => $agency->account_number,
                    'percentage_charge' => 0,
                ]);

                PaystackSubAccount::create([
                    'agency_id' => $agency->id,
                    'subaccount_code' => $paystackResponse['data']['subaccount_code'],
                    'active' => true,
                    'data' => $paystackResponse['data'],
                ]);
            } catch (Exception $e) {
                Log::error('Paystack Subaccount Creation Failed: ' . $e->getMessage());
                $errors[] = 'Paystack subaccount failed to initialize: ' . $e->getMessage();
            }
        }

        // 2. Create Monnify Subaccount (only if active)
        if ($isMonnifyActive) {
            try {
                $monnifyResponse = MonnifyApi::getInstance()->createSubAccount([
                    'subAccountName' => $agency->name,
                    'bankCode' => $request->bank_code_monnify,
                    'accountNumber' => $agency->account_number,
                    'email' => $agency->email,
                    'defaultSplitPercentage' => 100,
                ]);

                MonnifySubAccount::create([
                    'agency_id' => $agency->id,
                    'subaccount_code' => $monnifyResponse['subAccountCode'],
                    'active' => true,
                    'data' => $monnifyResponse,
                ]);
            } catch (Exception $e) {
                Log::error('Monnify Subaccount Creation Failed: ' . $e->getMessage());
                $errors[] = 'Monnify subaccount failed to initialize: ' . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return redirect()->route('admin.service-fee-agencies.show', $agency->id)
                ->with('warning', 'Service Fee Org created, but some gateway integrations failed.')
                ->with('gateway_errors', $errors);
        }

        return redirect()->route('admin.service-fee-agencies.index')
            ->with('success', 'Service Fee Org created and all active subaccounts initialized successfully.');
    }

    public function show(Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        $agency->load(['paystackSubAccount', 'monnifySubAccount']);
        
        $paystackBanks = [];
        $monnifyBanks = [];

        try {
            $paystackBanks = PaystackApi::getInstance()->getBanks();
        } catch (Exception $e) {
            Log::error('Paystack Banks Fetch Failed on Show: ' . $e->getMessage());
        }

        try {
            $monnifyBanks = MonnifyApi::getInstance()->getBanks();
        } catch (Exception $e) {
            Log::error('Monnify Banks Fetch Failed on Show: ' . $e->getMessage());
        }

        return view('admin.service_fee_agencies.show', compact('agency', 'paystackBanks', 'monnifyBanks'));
    }

    public function edit(Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        $paystackBanks = [];
        $monnifyBanks = [];

        if (\App\Models\Setting::get('paystack_active', true)) {
            try {
                $paystackBanks = PaystackApi::getInstance()->getBanks();
            } catch (Exception $e) {
                Log::error('Paystack Banks Fetch Failed on Edit: ' . $e->getMessage());
            }
        }

        if (\App\Models\Setting::get('monnify_active', true)) {
            try {
                $monnifyBanks = MonnifyApi::getInstance()->getBanks();
            } catch (Exception $e) {
                Log::error('Monnify Banks Fetch Failed on Edit: ' . $e->getMessage());
            }
        }

        return view('admin.service_fee_agencies.edit', compact('agency', 'paystackBanks', 'monnifyBanks'));
    }

    public function update(Request $request, Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        $formType = $request->input('form_type', 'profile');

        if ($formType === 'profile') {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:agencies,name,' . $agency->id,
                'code' => 'required|string|max:50|unique:agencies,code,' . $agency->id,
                'service_fee_amount' => 'required|numeric|min:0',
                'email' => 'required|email',
                'status' => 'required|boolean',
            ]);

            $agency->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'service_fee_amount' => $validated['service_fee_amount'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ]);

            return redirect()->route('admin.service-fee-agencies.index')
                ->with('success', 'Service Fee Org profile updated successfully.');
        }

        if ($formType === 'settlement') {
            $isPaystackActive = \App\Models\Setting::get('paystack_active', true);
            $isMonnifyActive = \App\Models\Setting::get('monnify_active', true);

            $rules = [
                'bank_name' => 'required|string|max:255',
                'account_number' => [
                    'required',
                    'string',
                    'digits:10',
                    function ($attribute, $value, $fail) use ($request, $agency, $isPaystackActive, $isMonnifyActive) {
                        $primaryBankCode = $isPaystackActive ? $request->bank_code_paystack : $request->bank_code_monnify;
                        $exists = Agency::where('account_number', $value)
                            ->where('bank_code', $primaryBankCode)
                            ->where('id', '!=', $agency->id)
                            ->exists();
                        if ($exists) {
                            $fail('This bank account is already registered.');
                        }
                    }
                ],
                'account_name' => 'required|string|max:255',
            ];

            $rules['bank_code_paystack'] = $isPaystackActive ? 'required|string' : 'nullable|string';
            $rules['bank_code_monnify'] = $isMonnifyActive ? 'required|string' : 'nullable|string';

            $request->validate($rules);

            $primaryBankCode = $isPaystackActive ? $request->bank_code_paystack : $request->bank_code_monnify;

            // Check if Settlement details changed
            $settlementChanged = 
                $request->account_number !== $agency->account_number ||
                $request->bank_name !== $agency->bank_name ||
                $request->account_name !== $agency->account_name ||
                ($isPaystackActive && $request->bank_code_paystack !== $agency->bank_code) ||
                ($isMonnifyActive && (!$agency->monnifySubAccount || ($request->bank_code_monnify !== ($agency->monnifySubAccount->data['bankCode'] ?? ''))));

            if (!$settlementChanged) {
                return redirect()->route('admin.service-fee-agencies.show', $agency->id)
                    ->with('info', 'No changes detected in settlement configuration.');
            }

            // Update agency settlement info
            $agency->update([
                'bank_name' => $request->bank_name,
                'bank_code' => $primaryBankCode,
                'account_number' => $request->account_number,
                'account_name' => strtoupper($request->account_name),
            ]);

            $errors = [];

            // Deactivate Paystack
            if ($agency->paystackSubAccount) {
                try {
                    PaystackApi::getInstance()->updateSubAccount($agency->paystackSubAccount->subaccount_code, [
                        'active' => false,
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to deactivate old Paystack subaccount: ' . $e->getMessage());
                }
                $agency->paystackSubAccount->update(['active' => false]);
                $agency->paystackSubAccount->delete();
            }

            // Delete Monnify
            if ($agency->monnifySubAccount) {
                try {
                    MonnifyApi::getInstance()->deleteSubAccount($agency->monnifySubAccount->subaccount_code);
                } catch (Exception $e) {
                    Log::error('Failed to delete old Monnify subaccount: ' . $e->getMessage());
                }
                $agency->monnifySubAccount->update(['active' => false]);
                $agency->monnifySubAccount->delete();
            }

            // Re-generate sub-accounts
            if ($isPaystackActive) {
                try {
                    $paystackResponse = PaystackApi::getInstance()->createSubAccount([
                        'business_name' => $agency->name,
                        'settlement_bank' => $request->bank_code_paystack,
                        'account_number' => $agency->account_number,
                        'percentage_charge' => 0,
                    ]);

                    PaystackSubAccount::create([
                        'agency_id' => $agency->id,
                        'subaccount_code' => $paystackResponse['data']['subaccount_code'],
                        'active' => true,
                        'data' => $paystackResponse['data'],
                    ]);
                } catch (Exception $e) {
                    Log::error('New Paystack Subaccount Creation Failed: ' . $e->getMessage());
                    $errors[] = 'New Paystack subaccount failed to initialize: ' . $e->getMessage();
                }
            }

            if ($isMonnifyActive) {
                try {
                    $monnifyResponse = MonnifyApi::getInstance()->createSubAccount([
                        'subAccountName' => $agency->name,
                        'bankCode' => $request->bank_code_monnify,
                        'accountNumber' => $agency->account_number,
                        'email' => $agency->email,
                        'defaultSplitPercentage' => 100,
                    ]);

                    MonnifySubAccount::create([
                        'agency_id' => $agency->id,
                        'subaccount_code' => $monnifyResponse['subAccountCode'],
                        'active' => true,
                        'data' => $monnifyResponse,
                    ]);
                } catch (Exception $e) {
                    Log::error('New Monnify Subaccount Creation Failed: ' . $e->getMessage());
                    $errors[] = 'New Monnify subaccount failed to initialize: ' . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                return redirect()->route('admin.service-fee-agencies.show', $agency->id)
                    ->with('warning', 'Settlement updated locally, but some gateway integrations failed.')
                    ->with('gateway_errors', $errors);
            }

            return redirect()->route('admin.service-fee-agencies.show', $agency->id)
                ->with('success', 'Settlement account rotated and updated successfully.');
        }

        return redirect()->route('admin.service-fee-agencies.index');
    }

    public function retryPaystack(Request $request, Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        if (!\App\Models\Setting::get('paystack_active', true)) {
            return back()->with('error', 'Paystack is currently disabled.');
        }

        if ($agency->paystackSubAccount()->exists()) {
            return back()->with('error', 'Paystack subaccount already exists.');
        }

        $request->validate(['bank_code_paystack' => 'required|string']);

        try {
            $paystackResponse = PaystackApi::getInstance()->createSubAccount([
                'business_name' => $agency->name,
                'settlement_bank' => $request->bank_code_paystack,
                'account_number' => $agency->account_number,
                'percentage_charge' => 0,
            ]);

            PaystackSubAccount::create([
                'agency_id' => $agency->id,
                'subaccount_code' => $paystackResponse['data']['subaccount_code'],
                'active' => true,
                'data' => $paystackResponse['data'],
            ]);

            $agency->update(['bank_code' => $request->bank_code_paystack]);

            return back()->with('success', 'Paystack subaccount initialized successfully.');
        } catch (Exception $e) {
            Log::error('Paystack Retry Failed: ' . $e->getMessage());
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function retryMonnify(Request $request, Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        if (!\App\Models\Setting::get('monnify_active', true)) {
            return back()->with('error', 'Monnify is currently disabled.');
        }

        if ($agency->monnifySubAccount()->exists()) {
            return back()->with('error', 'Monnify subaccount already exists.');
        }

        $request->validate(['bank_code_monnify' => 'required|string']);

        try {
            $monnifyResponse = MonnifyApi::getInstance()->createSubAccount([
                'subAccountName' => $agency->name,
                'bankCode' => $request->bank_code_monnify,
                'accountNumber' => $agency->account_number,
                'email' => $agency->email ?? 'support@urcs.gov.ng',
                'defaultSplitPercentage' => 100,
            ]);

            MonnifySubAccount::create([
                'agency_id' => $agency->id,
                'subaccount_code' => $monnifyResponse['subAccountCode'],
                'active' => true,
                'data' => $monnifyResponse,
            ]);

            return back()->with('success', 'Monnify subaccount initialized successfully.');
        } catch (Exception $e) {
            Log::error('Monnify Retry Failed: ' . $e->getMessage());
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function manualLinkPaystack(Request $request, Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        if ($agency->paystackSubAccount()->exists()) {
            return back()->with('error', 'Paystack subaccount already linked.');
        }

        $request->validate(['subaccount_code' => 'required|string|max:255']);

        $exists = PaystackSubAccount::where('subaccount_code', $request->subaccount_code)->exists();
        if ($exists) {
            return back()->with('error', 'This code is already linked.');
        }

        PaystackSubAccount::create([
            'agency_id' => $agency->id,
            'subaccount_code' => $request->subaccount_code,
            'active' => true,
            'data' => [
                'manual' => true,
                'linked_at' => now()->toIso8601String(),
                'linked_by' => auth()->user()->email ?? 'admin',
            ],
        ]);

        return back()->with('success', 'Paystack subaccount manually linked.');
    }

    public function manualLinkMonnify(Request $request, Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        if ($agency->monnifySubAccount()->exists()) {
            return back()->with('error', 'Monnify subaccount already linked.');
        }

        $request->validate(['subaccount_code' => 'required|string|max:255']);

        $exists = MonnifySubAccount::where('subaccount_code', $request->subaccount_code)->exists();
        if ($exists) {
            return back()->with('error', 'This code is already linked.');
        }

        MonnifySubAccount::create([
            'agency_id' => $agency->id,
            'subaccount_code' => $request->subaccount_code,
            'active' => true,
            'data' => [
                'manual' => true,
                'linked_at' => now()->toIso8601String(),
                'linked_by' => auth()->user()->email ?? 'admin',
            ],
        ]);

        return back()->with('success', 'Monnify subaccount manually linked.');
    }

    public function destroy(Agency $agency)
    {
        if (!$agency->is_service_fee) {
            abort(404);
        }

        $agency->delete();
        return redirect()->route('admin.service-fee-agencies.index')->with('success', 'Service Fee Org deleted successfully.');
    }
}
