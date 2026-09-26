@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.agencies.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
        Back to Agencies
    </a>
    <h1 class="text-2xl font-bold text-slate-800">Register New Agency</h1>
    <p class="text-slate-500 text-sm">Onboard a government agency and initialize payment sub-accounts.</p>
</div>

@if($errors->any())
    <div class="mb-8 p-6 bg-red-50 border border-red-100 rounded-3xl">
        <div class="flex items-center gap-3 mb-4 text-red-700">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            <h3 class="font-bold uppercase tracking-widest text-xs">Registration Errors</h3>
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

<form action="{{ route('admin.agencies.store') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Basic Info -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Agency Identification</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Agency Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="e.g. State Internal Revenue Service">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Short Code / Slug <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300 uppercase"
                            placeholder="e.g. SIRS">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Administrative Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="agency-contact@government.gov">
                        <p class="text-[10px] text-slate-400 font-medium ml-1">Required for gateway sub-account identification.</p>
                    </div>
                </div>
            </div>

            <!-- Settlement Config -->
            <div class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="landmark" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Settlement Account</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Account Number (NUBAN) <span class="text-rose-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" required maxlength="10"
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="0123456789">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Account Holder Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="account_name" value="{{ old('account_name') }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300 uppercase"
                            placeholder="AGENCY OFFICIAL BANK ACCOUNT NAME">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Bank Name (Standard) <span class="text-rose-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="e.g. Zenith Bank">
                    </div>

                    @php
                        $paystackActive = \App\Models\Setting::get('paystack_active', true);
                        $monnifyActive = \App\Models\Setting::get('monnify_active', true);
                        $activeCount = ($paystackActive ? 1 : 0) + ($monnifyActive ? 1 : 0);
                    @endphp

                    @if($activeCount > 0)
                    <div class="space-y-6 md:col-span-2 pt-6">
                        <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-4">Gateway Specific Mapping</h4>
                            <div class="grid grid-cols-1 {{ $activeCount == 2 ? 'md:grid-cols-2' : '' }} gap-6">
                                @if($paystackActive)
                                <div class="space-y-2 {{ $activeCount == 1 ? 'md:col-span-2' : '' }}">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Paystack Bank Selection <span class="text-rose-500">*</span></label>
                                    <select name="bank_code_paystack" required class="w-full px-5 py-3 bg-white border border-slate-100 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-xs font-bold text-slate-700">
                                        <option value="">Select Paystack Bank</option>
                                        @foreach($paystackBanks as $bank)
                                            <option value="{{ $bank['code'] }}" {{ old('bank_code_paystack') == $bank['code'] ? 'selected' : '' }}>{{ $bank['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                @if($monnifyActive)
                                <div class="space-y-2 {{ $activeCount == 1 ? 'md:col-span-2' : '' }}">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Monnify Bank Selection <span class="text-rose-500">*</span></label>
                                    <select name="bank_code_monnify" required class="w-full px-5 py-3 bg-white border border-slate-100 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-xs font-bold text-slate-700">
                                        <option value="">Select Monnify Bank</option>
                                        @foreach($monnifyBanks as $bank)
                                            <option value="{{ $bank['code'] }}" {{ old('bank_code_monnify') == $bank['code'] ? 'selected' : '' }}>{{ $bank['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar / Actions -->
        <div class="space-y-8">
            <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-slate-900/20">
                <h3 class="text-lg font-bold mb-4">Initialization</h3>
                <p class="text-white/60 text-sm leading-relaxed mb-8">
                    Registering this agency will automatically trigger API calls to Paystack and Monnify to create unified sub-accounts for revenue splitting.
                </p>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-emerald-500 rounded-full flex items-center justify-center">
                            <i data-lucide="check" class="w-3 h-3 text-white"></i>
                        </div>
                        <span class="text-xs font-bold text-white/80">Automated Sub-accounts</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-emerald-500 rounded-full flex items-center justify-center">
                            <i data-lucide="check" class="w-3 h-3 text-white"></i>
                        </div>
                        <span class="text-xs font-bold text-white/80">Real-time Validation</span>
                    </div>
                </div>
                
                <button type="submit" class="w-full mt-10 px-8 py-5 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-500/20 flex items-center justify-center gap-3">
                    Initialize Agency
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
            
            <div class="bg-amber-50 rounded-[2.5rem] p-8 border border-amber-100">
                <div class="flex items-center gap-3 mb-4 text-amber-700">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                    <h4 class="font-black uppercase tracking-widest text-[10px]">Bank Mapping Note</h4>
                </div>
                <p class="text-amber-800/70 text-xs leading-relaxed font-medium">
                    Gateways often use different internal codes for the same bank. Please ensure you select the correct bank in both dropdowns to prevent settlement failures.
                </p>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountNumInput = document.querySelector('input[name="account_number"]');
        if (accountNumInput) {
            accountNumInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            accountNumInput.setAttribute('pattern', '[0-9]{10}');
            accountNumInput.setAttribute('minlength', '10');
            accountNumInput.setAttribute('maxlength', '10');
            accountNumInput.setAttribute('title', 'NUBAN must be exactly 10 digits');
        }
    });
</script>
@endsection
