@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.service-fee-agencies.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
        Back to Service Fee Setup
    </a>
    <h1 class="text-2xl font-bold text-slate-800">Edit Service Fee Org: {{ $agency->name }}</h1>
    <p class="text-slate-500 text-sm">Modify service fee parameters under their respective dedicated management channels.</p>
</div>

@if($errors->any())
    <div class="mb-8 p-6 bg-red-50 border border-red-100 rounded-3xl">
        <div class="flex items-center gap-3 mb-4 text-red-700">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            <h3 class="font-bold uppercase tracking-widest text-xs">Validation Errors</h3>
        </div>
        <ul class="space-y-2">
            @foreach($errors->all() as $error)
                <li class="text-sm text-red-600 font-medium flex items-center gap-2">
                    <div class="w-1 h-1 bg-red-400 rounded-full"></div>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Form 1: Basic Profile Details -->
    <div class="space-y-8">
        <div class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 h-full flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                        <i data-lucide="percent" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Basic Information Profile</h3>
                        <p class="text-xs text-slate-400">Manage identity details and state active status.</p>
                    </div>
                </div>

                <form action="{{ route('admin.service-fee-agencies.update', $agency->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="profile">

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Organization Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $agency->name) }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="e.g. Treasury Portal Services">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Short Code / Slug <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $agency->code) }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300 uppercase"
                            placeholder="e.g. TPS-FEE">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Flat Service Fee Amount (₦) <span class="text-rose-500">*</span></label>
                        <input type="number" name="service_fee_amount" value="{{ old('service_fee_amount', (int)$agency->service_fee_amount) }}" required min="0" step="1"
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="e.g. 200">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Administrative Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $agency->email) }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="treasury-contact@government.gov">
                    </div>

                    <div class="space-y-2 pb-6">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700">
                            <option value="1" {{ old('status', $agency->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $agency->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-4 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/10 flex items-center justify-center gap-3">
                        Save Profile Details
                        <i data-lucide="save" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Form 2: Settlement Account Details -->
    <div class="space-y-8">
        <div class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 h-full flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="landmark" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Settlement Account Details</h3>
                        <p class="text-xs text-slate-400">Configure bank targets and trigger API sub-account rotation.</p>
                    </div>
                </div>

                <div class="mb-6 p-4 bg-amber-50 border border-amber-100 rounded-2xl flex gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <h5 class="text-xs font-black uppercase text-amber-800 tracking-wider">Strategic Rotation Alert</h5>
                        <p class="text-[11px] text-amber-700/90 font-medium mt-1 leading-relaxed">
                            Updating settlement parameters automatically <strong>deactivates/deletes</strong> current live subaccounts and spins up <strong>brand new subaccounts</strong>. Ensure exact mapping across gateways.
                        </p>
                    </div>
                </div>

                <form action="{{ route('admin.service-fee-agencies.update', $agency->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="settlement">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Account Number (NUBAN) <span class="text-rose-500">*</span></label>
                            <input type="text" name="account_number" value="{{ old('account_number', $agency->account_number) }}" required maxlength="10"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-xs font-bold text-slate-700 placeholder-slate-300"
                                placeholder="0123456789">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Account Holder Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="account_name" value="{{ old('account_name', $agency->account_name) }}" required 
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-xs font-bold text-slate-700 placeholder-slate-300 uppercase"
                                placeholder="OFFICIAL BANK HOLDER">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Bank Name (Standard) <span class="text-rose-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $agency->bank_name) }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="e.g. Zenith Bank">
                    </div>

                    @php
                        $paystackActive = \App\Models\Setting::get('paystack_active', true);
                        $monnifyActive = \App\Models\Setting::get('monnify_active', true);
                        $activeCount = ($paystackActive ? 1 : 0) + ($monnifyActive ? 1 : 0);
                    @endphp

                    @if($activeCount > 0)
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-4">
                        <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Gateway Bank Identifiers</h4>
                        <div class="grid grid-cols-1 {{ $activeCount == 2 ? 'md:grid-cols-2' : '' }} gap-4">
                            @if($paystackActive)
                            <div class="space-y-2 {{ $activeCount == 1 ? 'md:col-span-2' : '' }}">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Paystack Bank <span class="text-rose-500">*</span></label>
                                <select name="bank_code_paystack" required class="w-full px-4 py-3 bg-white border border-slate-100 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-xs font-bold text-slate-700">
                                    <option value="">Select Bank</option>
                                    @foreach($paystackBanks as $bank)
                                        <option value="{{ $bank['code'] }}" {{ old('bank_code_paystack', $agency->bank_code) == $bank['code'] ? 'selected' : '' }}>{{ $bank['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            @if($monnifyActive)
                            @php
                                $currentMonnifyCode = $agency->monnifySubAccount ? ($agency->monnifySubAccount->data['bankCode'] ?? '') : '';
                            @endphp
                            <div class="space-y-2 {{ $activeCount == 1 ? 'md:col-span-2' : '' }}">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Monnify Bank <span class="text-rose-500">*</span></label>
                                <select name="bank_code_monnify" required class="w-full px-4 py-3 bg-white border border-slate-100 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-xs font-bold text-slate-700">
                                    <option value="">Select Bank</option>
                                    @foreach($monnifyBanks as $bank)
                                        <option value="{{ $bank['code'] }}" {{ old('bank_code_monnify', $currentMonnifyCode) == $bank['code'] ? 'selected' : '' }}>{{ $bank['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <button type="submit" id="rotate-settlement-btn" class="w-full py-4 bg-emerald-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-500/10 flex items-center justify-center gap-3">
                        Rotate Settlement Details
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountNum = document.querySelector('input[name="account_number"]');
        const accountName = document.querySelector('input[name="account_name"]');
        const bankName = document.querySelector('input[name="bank_name"]');
        const selectPaystack = document.querySelector('select[name="bank_code_paystack"]');
        const selectMonnify = document.querySelector('select[name="bank_code_monnify"]');
        const rotateBtn = document.getElementById('rotate-settlement-btn');

        const originalValues = {
            account_number: accountNum ? accountNum.value : '',
            account_name: accountName ? accountName.value : '',
            bank_name: bankName ? bankName.value : '',
            bank_code_paystack: selectPaystack ? selectPaystack.value : '',
            bank_code_monnify: selectMonnify ? selectMonnify.value : ''
        };

        if (accountNum) {
            accountNum.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            accountNum.setAttribute('pattern', '[0-9]{10}');
            accountNum.setAttribute('minlength', '10');
            accountNum.setAttribute('maxlength', '10');
            accountNum.setAttribute('title', 'NUBAN must be exactly 10 digits');
        }

        function clearGatewaySelections() {
            if (selectPaystack) selectPaystack.value = "";
            if (selectMonnify) selectMonnify.value = "";
        }

        function checkModifications() {
            const currentValues = {
                account_number: accountNum ? accountNum.value : '',
                account_name: accountName ? accountName.value : '',
                bank_name: bankName ? bankName.value : '',
                bank_code_paystack: selectPaystack ? selectPaystack.value : '',
                bank_code_monnify: selectMonnify ? selectMonnify.value : ''
            };

            const hasChanges = 
                currentValues.account_number !== originalValues.account_number ||
                currentValues.account_name !== originalValues.account_name ||
                currentValues.bank_name !== originalValues.bank_name ||
                currentValues.bank_code_paystack !== originalValues.bank_code_paystack ||
                currentValues.bank_code_monnify !== originalValues.bank_code_monnify;

            if (rotateBtn) {
                if (hasChanges) {
                    rotateBtn.disabled = false;
                    rotateBtn.classList.remove('opacity-50', 'pointer-events-none', 'cursor-not-allowed');
                } else {
                    rotateBtn.disabled = true;
                    rotateBtn.classList.add('opacity-50', 'pointer-events-none', 'cursor-not-allowed');
                }
            }
        }

        if (accountNum) {
            accountNum.addEventListener('input', function() {
                clearGatewaySelections();
                checkModifications();
            });
        }
        if (accountName) {
            accountName.addEventListener('input', function() {
                clearGatewaySelections();
                checkModifications();
            });
        }
        if (bankName) {
            bankName.addEventListener('input', function() {
                clearGatewaySelections();
                checkModifications();
            });
        }
        if (selectPaystack) {
            selectPaystack.addEventListener('change', checkModifications);
        }
        if (selectMonnify) {
            selectMonnify.addEventListener('change', checkModifications);
        }

        checkModifications();
    });
</script>
@endsection
