@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end print:hidden" x-data="{ openDirectModal: false }">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Tax Payments & Collections</h1>
        <p class="text-slate-500 text-sm">Real-time revenue monitoring, interactive billing analytics, and direct tax collection tool.</p>
    </div>
    @if(config('services.manual_payment'))
    <div>
        <button @click="$dispatch('open-modal', 'direct-collection')" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-200 flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Collect Direct Payment
        </button>
    </div>
    @endif
</div>

<!-- Real-time Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Card 1: Total Collected -->
    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full blur-xl"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Active</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Collected</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">₦{{ number_format($totalRevenue, 2) }}</h3>
    </div>

    <!-- Card 2: Total Outstanding -->
    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full blur-xl"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600 shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Receivables</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Outstanding</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">₦{{ number_format($totalOutstanding, 2) }}</h3>
    </div>

    <!-- Card 3: Collection Rate -->
    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full blur-xl"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 shrink-0">
                <i data-lucide="percent" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Efficiency</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Collection Rate</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($collectionRate, 1) }}%</h3>
    </div>

    <!-- Card 4: Channel Split -->
    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-50 rounded-full blur-xl"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Channels</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Electronic vs Cash</p>
        <h3 class="text-base font-bold text-slate-800 mt-1">
            ₦{{ number_format($electronicRevenue, 0) }} / ₦{{ number_format($cashRevenue, 0) }}
        </h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Daily Trend Graph Card (CSS/SVG) -->
    <div class="lg:col-span-2 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 flex flex-col justify-between">
        <div>
            <h3 class="text-lg font-black text-slate-800 mb-2">Daily Revenue Trend</h3>
            <p class="text-xs text-slate-400">Total collections across all payment modes over the past 7 days.</p>
        </div>

        <div class="h-48 flex items-end gap-3 mt-8 pt-4 border-b border-slate-100">
            @foreach($trendData as $index => $total)
                <div class="flex-1 flex flex-col items-center group h-full justify-end">
                    <!-- Tooltip -->
                    <span class="opacity-0 group-hover:opacity-100 bg-slate-800 text-white text-[9px] font-bold px-2 py-1 rounded-lg mb-2 transition-opacity shadow-lg">
                        ₦{{ number_format($total, 0) }}
                    </span>
                    @php
                        $maxTrend = max($trendData);
                        $percent = ($maxTrend > 0) ? ($total / $maxTrend) * 80 : 0;
                        if($percent < 5 && $total > 0) $percent = 5; // minimum height indicator
                    @endphp
                    <!-- Bar -->
                    <div style="height: {{ $percent }}%" class="w-full bg-primary-600 rounded-t-xl transition-all duration-500 group-hover:bg-primary-500 shadow-md"></div>
                    <!-- Label -->
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-2 truncate">{{ $trendLabels[$index] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Settle Billing Utility -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
        <div>
            <h3 class="text-lg font-black text-slate-800 mb-2">Direct Collection Assistant</h3>
            <p class="text-xs text-slate-400">Collect due/outstanding tax rules immediately from approved establishments.</p>
        </div>
        <div class="mt-8 space-y-4">
            @if(config('services.manual_payment'))
                <button @click="$dispatch('open-modal', 'direct-collection')" class="w-full py-4 bg-slate-50 border-2 border-dashed border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-100 hover:border-slate-300 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-slate-400"></i>
                    Choose Debtor Establishment
                </button>
            @else
                <div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-4 text-rose-700 shadow-sm">
                    <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center shrink-0 text-rose-600">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-rose-800">Direct Payments Disabled</p>
                        <p class="text-[10px] text-rose-600 font-medium mt-0.5">Manual direct collection is currently disabled in system configuration.</p>
                    </div>
                </div>
            @endif
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-3 text-slate-500">
                    <i data-lucide="info" class="w-5 h-5 shrink-0 text-slate-400"></i>
                    <p class="text-[10px] leading-relaxed italic">
                        Tip: Select direct payment to credit specific agency subaccounts automatically using live split configuration.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payments Transactions List -->
<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 print:block">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h3 class="text-lg font-black text-slate-800">Transactions History</h3>
            <p class="text-xs text-slate-400">Search and audit live tax transactions recorded on the system.</p>
        </div>

        <form action="{{ route('admin.payments.index') }}" method="GET" class="flex flex-wrap items-center gap-3 print:hidden">
            <div class="relative w-64">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-4 top-3.5"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Ref, Est, Rule..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
            </div>

            <select name="gateway" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-600">
                <option value="">All Channels</option>
                <option value="Cash" {{ request('gateway') === 'Cash' ? 'selected' : '' }}>Cash</option>
                <option value="Bank Transfer" {{ request('gateway') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="Paystack" {{ request('gateway') === 'Paystack' ? 'selected' : '' }}>Paystack</option>
                <option value="Monnify" {{ request('gateway') === 'Monnify' ? 'selected' : '' }}>Monnify</option>
            </select>

            <button type="submit" class="px-5 py-2.5 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 transition-all">Filter</button>
            @if(request()->anyFilled(['search', 'gateway']))
                <a href="{{ route('admin.payments.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all">Clear</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 text-left">
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reference</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Establishment</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue Head</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Agency</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Channel</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($payments as $payment)
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 text-xs font-mono font-bold text-slate-700">{{ $payment->reference }}</td>
                        <td class="py-4">
                            @if($payment->establishment)
                                <a href="{{ route('admin.establishments.details', $payment->establishment->id) }}" class="text-xs font-bold text-slate-800 hover:text-primary-600 transition-colors">
                                    {{ $payment->establishment->name }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Deleted Establishment</span>
                            @endif
                        </td>
                        <td class="py-4">
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-[9px] font-black text-slate-600 uppercase tracking-wider">
                                {{ $payment->revenueHead->name ?? $payment->revenueRule->name ?? 'Direct Split' }}
                            </span>
                        </td>
                        <td class="py-4">
                            <span class="px-2.5 py-1 bg-indigo-50 rounded-lg text-[9px] font-black text-indigo-600 uppercase tracking-wider">
                                {{ $payment->revenueHead->agency->code ?? $payment->revenueRule->agency->code ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-4 text-xs font-black text-slate-800 text-right">₦{{ number_format($payment->amount, 2) }}</td>
                        <td class="py-4">
                            @php
                                $chanColor = match($payment->gateway) {
                                    'Cash' => 'bg-emerald-50 text-emerald-600',
                                    'Bank Transfer' => 'bg-blue-50 text-blue-600',
                                    'Paystack' => 'bg-orange-50 text-orange-600',
                                    'Monnify' => 'bg-purple-50 text-purple-600',
                                    default => 'bg-slate-50 text-slate-600',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $chanColor }}">
                                {{ $payment->gateway }}
                            </span>
                        </td>
                        <td class="py-4 text-xs font-medium text-slate-500">{{ $payment->created_at->format('M d, Y h:ia') }}</td>
                        <td class="py-4">
                            @php
                                $statColor = match($payment->status) {
                                    'success' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider {{ $statColor }}">
                                {{ $payment->status === 'success' ? 'Successful' : ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 flex flex-col items-center justify-center text-slate-400">
                            <i data-lucide="credit-card" class="w-12 h-12 mb-4 opacity-20"></i>
                            <p class="text-sm font-bold uppercase tracking-widest">No payment records found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 print:hidden">
        {{ $payments->links() }}
    </div>
</div>

<!-- Global Direct Collection Modal -->
<div x-data="{ 
        open: false, 
        selectedEstId: '', 
        selectedRuleId: '', 
        amount: 0, 
        outstanding: 0, 
        reference: '', 
        debtors: @js($debtorEstablishments),
        get activeEst() {
            return this.debtors.find(d => d.id == this.selectedEstId) || null;
        },
        updateOutstanding() {
            const rule = this.activeEst ? this.activeEst.rules.find(r => r.rule_id == this.selectedRuleId) : null;
            this.outstanding = rule ? rule.outstanding_amount : 0;
            this.amount = this.outstanding;
        }
     }" 
     @open-modal.window="if($event.detail === 'direct-collection') { open = true; reference = ''; }" 
     class="relative z-[60]" 
     x-show="open" 
     style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl">
                <form action="{{ route('admin.payments.direct-pay') }}" method="POST">
                    @csrf
                    <div class="bg-white p-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                                <i data-lucide="receipt" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">Collect Tax Payment</h3>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider font-mono">Manual Collection Assistant</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Payment Reference -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Payment Reference <span class="text-rose-500">*</span></label>
                                <input type="text" name="reference" x-model="reference" required placeholder="e.g. TRF/9820491024/UBA" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-mono font-bold text-slate-800">
                            </div>
                            <!-- Select Debtor Establishment -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Debtor Establishment</label>
                                <select name="establishment_id" x-model="selectedEstId" @change="selectedRuleId = ''; outstanding = 0; amount = 0" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-700">
                                    <option value="">-- Choose Debtor Establishment --</option>
                                    <template x-for="est in debtors" :key="est.id">
                                        <option :value="est.id" x-text="est.name + ' (' + est.unique_id + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Select Outstanding Head -->
                            <div x-show="activeEst" style="display: none;">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Unpaid / Outstanding Heads</label>
                                <select name="revenue_head_id" x-model="selectedRuleId" @change="updateOutstanding()" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-700">
                                    <option value="">-- Choose Outstanding Head --</option>
                                    <template x-if="activeEst">
                                        <template x-for="rule in activeEst.rules" :key="rule.rule_id">
                                            <option :value="rule.rule_id" x-text="rule.rule_name + ' (Outstanding: ₦' + rule.outstanding_amount.toLocaleString() + ')'"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>

                            <!-- Display balance details -->
                            <div x-show="outstanding > 0" class="grid grid-cols-2 gap-4 bg-slate-50 p-6 rounded-2xl border border-slate-100" style="display: none;">
                                <div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Outstanding Balance</span>
                                    <span class="text-base font-black text-slate-800" x-text="'₦' + outstanding.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Status Period</span>
                                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest" x-text="activeEst && selectedRuleId ? activeEst.rules.find(r => r.rule_id == selectedRuleId).period : ''"></span>
                                </div>
                            </div>

                            <!-- Amount and Payment Mode -->
                            <div x-show="outstanding > 0" class="grid grid-cols-2 gap-6" style="display: none;">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Amount to Pay (₦)</label>
                                    <input type="number" step="0.01" name="amount" :max="outstanding" x-model="amount" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-black text-slate-800">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Payment Mode</label>
                                    <select name="gateway" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-700">
                                        <option value="Bank Transfer" selected>Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-10 py-6 flex flex-row-reverse gap-3">
                        <button type="submit" x-show="outstanding > 0" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-200" style="display: none;">Post Settle Transaction</button>
                        <button type="button" @click="open = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
