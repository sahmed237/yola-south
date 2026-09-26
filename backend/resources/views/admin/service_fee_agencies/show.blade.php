@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.service-fee-agencies.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
        Back to Service Fee Setup
    </a>
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $agency->name }}</h1>
            <p class="text-slate-500 text-sm">Service Fee Organization details, configuration and payment gateway status.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.service-fee-agencies.edit', $agency->id) }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-all flex items-center gap-2">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
                Edit Info / Fee
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center shadow-sm">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="mb-6 p-6 bg-amber-50 border border-amber-100 rounded-3xl shadow-sm">
        <div class="flex items-center gap-3 text-amber-800 mb-3">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            <h4 class="font-bold uppercase tracking-wider text-xs">{{ session('warning') }}</h4>
        </div>
        @if(session('gateway_errors'))
            <ul class="space-y-2 ml-9 list-disc text-xs font-medium text-amber-700/80">
                @foreach(session('gateway_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl flex items-center shadow-sm">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3"></i>
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Info (Left Column) -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Details Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                    <i data-lucide="info" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Organization Identification</h3>
                    <p class="text-xs text-slate-400">Core parameters and profile parameters.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Organization Name</span>
                    <p class="text-sm font-bold text-slate-700">{{ $agency->name }}</p>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Short Code</span>
                    <p class="text-sm font-bold text-slate-700 font-mono">{{ $agency->code }}</p>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Portal Service Fee Amount</span>
                    <p class="text-sm font-bold text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg border border-primary-100 inline-block">₦{{ number_format($agency->service_fee_amount, 2) }}</p>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Contact Email</span>
                    <p class="text-sm font-bold text-slate-700">{{ $agency->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Status</span>
                    @if($agency->status)
                        <span class="inline-flex px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-wider rounded border border-emerald-100">Active</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-wider rounded border border-slate-100">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bank Details Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                    <i data-lucide="landmark" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Settlement Destination</h3>
                    <p class="text-xs text-slate-400">Where service fee revenue payouts are distributed.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Settlement Bank</span>
                    <p class="text-sm font-bold text-slate-700">{{ $agency->bank_name }}</p>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Account Number</span>
                    <p class="text-sm font-bold text-slate-700 font-mono">{{ $agency->account_number }}</p>
                </div>
                <div class="md:col-span-2">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Account Holder Name</span>
                    <p class="text-sm font-bold text-slate-700 uppercase tracking-tight">{{ $agency->account_name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gateway Connections (Right Column / Sidebar) -->
    <div class="space-y-8">
        <!-- Paystack Status Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-full -mr-16 -mt-16 -z-10"></div>
            
            <div class="flex items-center justify-between mb-6">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Paystack Integration</span>
                @if($agency->paystackSubAccount)
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase rounded border border-emerald-100">Connected</span>
                @else
                    <span class="px-2 py-0.5 bg-rose-50 text-rose-600 text-[10px] font-black uppercase rounded border border-rose-100">Action Required</span>
                @endif
            </div>

            @if($agency->paystackSubAccount)
                <div class="space-y-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Subaccount Code</span>
                        <p class="text-sm font-bold text-slate-800 font-mono">{{ $agency->paystackSubAccount->subaccount_code }}</p>
                    </div>

                    <details class="group bg-slate-50 border border-slate-100 rounded-2xl overflow-hidden [&_summary::-webkit-details-marker]:hidden shadow-sm">
                        <summary class="flex items-center justify-between px-4 py-3 cursor-pointer select-none">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Gateway Metadata</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <div class="px-4 pb-4 pt-1 border-t border-slate-100/50 space-y-2 max-h-48 overflow-y-auto">
                            @if($agency->paystackSubAccount->data)
                                @foreach($agency->paystackSubAccount->data as $key => $val)
                                    @if(is_array($val))
                                        <div class="flex flex-col text-[11px] py-1 border-b border-slate-100/50 last:border-0">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ Str::snake($key, ' ') }}</span>
                                            <span class="font-mono text-slate-600 break-all leading-normal text-[10px] mt-0.5 bg-white p-2 rounded-lg border border-slate-100">{{ json_encode($val) }}</span>
                                        </div>
                                    @else
                                        <div class="flex justify-between items-center text-[11px] py-1 border-b border-slate-100/50 last:border-0">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ Str::snake($key, ' ') }}</span>
                                            <span class="font-mono text-slate-700 break-all font-bold text-[10px] text-right">{{ is_bool($val) ? ($val ? 'true' : 'false') : $val }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-[10px] text-slate-400 italic">No stored gateway payload metadata available.</p>
                            @endif
                        </div>
                    </details>

                    <div class="text-[10px] text-slate-400 leading-relaxed font-medium">
                        Connected on {{ $agency->paystackSubAccount->created_at->format('M d, Y H:i') }}. Automatic split settlement is live.
                    </div>
                </div>
            @else
                @if(\App\Models\Setting::get('paystack_active', true))
                <div class="space-y-6">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        No active Paystack sub-account found. Select the correct bank code to initialize connection.
                    </p>
                    
                    <form action="{{ route('admin.service-fee-agencies.retry-paystack', $agency->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Paystack Bank</label>
                            <select name="bank_code_paystack" required class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-primary-500/20 outline-none">
                                <option value="">Select paystack bank</option>
                                @foreach($paystackBanks as $bank)
                                    <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-primary-500/10 flex items-center justify-center gap-2">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            Connect Paystack
                        </button>
                    </form>

                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-slate-100"></div>
                        <span class="flex-shrink mx-4 text-[9px] font-black text-slate-300 uppercase tracking-widest">or</span>
                        <div class="flex-grow border-t border-slate-100"></div>
                    </div>

                    <button type="button" onclick="openManualLinkModal('paystack')" class="w-full py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 border border-slate-100">
                        <i data-lucide="link" class="w-4 h-4 text-slate-400"></i>
                        Link Existing Subaccount
                    </button>
                </div>
                @else
                <div class="space-y-4 text-center py-6">
                    <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gateway Disabled</p>
                    <p class="text-[11px] text-slate-400 leading-normal max-w-xs mx-auto">
                        Paystack integration is currently disabled in system settings. Enable it to initialize sub-accounts.
                    </p>
                </div>
                @endif
            @endif
        </div>

        <!-- Monnify Status Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-full -mr-16 -mt-16 -z-10"></div>
            
            <div class="flex items-center justify-between mb-6">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Monnify Integration</span>
                @if($agency->monnifySubAccount)
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase rounded border border-emerald-100">Connected</span>
                @else
                    <span class="px-2 py-0.5 bg-rose-50 text-rose-600 text-[10px] font-black uppercase rounded border border-rose-100">Action Required</span>
                @endif
            </div>

            @if($agency->monnifySubAccount)
                <div class="space-y-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Subaccount Code</span>
                        <p class="text-sm font-bold text-slate-800 font-mono">{{ $agency->monnifySubAccount->subaccount_code }}</p>
                    </div>

                    <details class="group bg-slate-50 border border-slate-100 rounded-2xl overflow-hidden [&_summary::-webkit-details-marker]:hidden shadow-sm">
                        <summary class="flex items-center justify-between px-4 py-3 cursor-pointer select-none">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Gateway Metadata</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <div class="px-4 pb-4 pt-1 border-t border-slate-100/50 space-y-2 max-h-48 overflow-y-auto">
                            @if($agency->monnifySubAccount->data)
                                @foreach($agency->monnifySubAccount->data as $key => $val)
                                    @if(is_array($val))
                                        <div class="flex flex-col text-[11px] py-1 border-b border-slate-100/50 last:border-0">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ Str::snake($key, ' ') }}</span>
                                            <span class="font-mono text-slate-600 break-all leading-normal text-[10px] mt-0.5 bg-white p-2 rounded-lg border border-slate-100">{{ json_encode($val) }}</span>
                                        </div>
                                    @else
                                        <div class="flex justify-between items-center text-[11px] py-1 border-b border-slate-100/50 last:border-0">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ Str::snake($key, ' ') }}</span>
                                            <span class="font-mono text-slate-700 break-all font-bold text-[10px] text-right">{{ is_bool($val) ? ($val ? 'true' : 'false') : $val }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-[10px] text-slate-400 italic">No stored gateway payload metadata available.</p>
                            @endif
                        </div>
                    </details>

                    <div class="text-[10px] text-slate-400 leading-relaxed font-medium">
                        Connected on {{ $agency->monnifySubAccount->created_at->format('M d, Y H:i') }}. Automatic split settlement is live.
                    </div>
                </div>
            @else
                @if(\App\Models\Setting::get('monnify_active', true))
                <div class="space-y-6">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        No active Monnify sub-account found. Select the correct bank code to initialize connection.
                    </p>
                    
                    <form action="{{ route('admin.service-fee-agencies.retry-monnify', $agency->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Monnify Bank</label>
                            <select name="bank_code_monnify" required class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-primary-500/20 outline-none">
                                <option value="">Select monnify bank</option>
                                @foreach($monnifyBanks as $bank)
                                    <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-primary-500/10 flex items-center justify-center gap-2">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            Connect Monnify
                        </button>
                    </form>

                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-slate-100"></div>
                        <span class="flex-shrink mx-4 text-[9px] font-black text-slate-300 uppercase tracking-widest">or</span>
                        <div class="flex-grow border-t border-slate-100"></div>
                    </div>

                    <button type="button" onclick="openManualLinkModal('monnify')" class="w-full py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 border border-slate-100">
                        <i data-lucide="link" class="w-4 h-4 text-slate-400"></i>
                        Link Existing Subaccount
                    </button>
                </div>
                @else
                <div class="space-y-4 text-center py-6">
                    <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gateway Disabled</p>
                    <p class="text-[11px] text-slate-400 leading-normal max-w-xs mx-auto">
                        Monnify integration is currently disabled in system settings. Enable it to initialize sub-accounts.
                    </p>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Manual Linking Modal -->
<div id="manual-link-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-[2rem] p-8 max-w-md w-full mx-4 shadow-2xl border border-slate-100 relative animate-in fade-in zoom-in duration-200">
        <button type="button" onclick="closeManualLinkModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                <i data-lucide="link" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800" id="modal-title">Link Existing Subaccount</h3>
                <p class="text-xs text-slate-400">Manually link a pre-configured subaccount.</p>
            </div>
        </div>

        <form id="manual-link-form" action="" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1" id="subaccount-label">Subaccount Code <span class="text-rose-500">*</span></label>
                <input type="text" name="subaccount_code" required 
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                    placeholder="e.g. ACCT_xxxxxxxx">
                <p class="text-[10px] text-slate-400 font-medium ml-1">Must exist on the gateway dashboard.</p>
            </div>

            <button type="submit" class="w-full py-4 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                Verify and Link Subaccount
                <i data-lucide="check-square" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>

<script>
    function openManualLinkModal(gateway) {
        const modal = document.getElementById('manual-link-modal');
        const form = document.getElementById('manual-link-form');
        const title = document.getElementById('modal-title');
        const label = document.getElementById('subaccount-label');
        const input = form.querySelector('input[name="subaccount_code"]');

        if (gateway === 'paystack') {
            title.textContent = "Link Existing Paystack Subaccount";
            label.textContent = "Paystack Subaccount Code";
            input.placeholder = "e.g. ACCT_xxxxxxxx";
            form.action = "{{ route('admin.service-fee-agencies.manual-link-paystack', $agency->id) }}";
        } else if (gateway === 'monnify') {
            title.textContent = "Link Existing Monnify Subaccount";
            label.textContent = "Monnify Subaccount Code";
            input.placeholder = "e.g. ACCT_xxxxxxxx";
            form.action = "{{ route('admin.service-fee-agencies.manual-link-monnify', $agency->id) }}";
        }

        modal.classList.remove('hidden');
    }

    function closeManualLinkModal() {
        const modal = document.getElementById('manual-link-modal');
        modal.classList.add('hidden');
    }
</script>
@endsection
