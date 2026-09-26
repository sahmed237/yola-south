@php
    $tableRows = [];
    foreach ($taxStatus['fees'] as $fee) {
        $tableRows[] = [
            'rule_id' => $fee['rule_id'],
            'rule_name' => $fee['rule_name'],
            'agency_id' => $fee['agency_id'],
            'agency_name' => $fee['agency_name'],
            'frequency' => $fee['frequency'],
            'period' => $fee['period'],
            'due_amount' => (float) $fee['due_amount'],
            'paid_amount' => (float) $fee['paid_amount'],
            'outstanding_amount' => (float) $fee['outstanding_amount'],
            'status' => $fee['outstanding_amount'] > 0 ? 'outstanding' : 'settled',
        ];
    }
@endphp

@extends('layouts.public')

@section('title', 'Settle Taxes - ' . $establishment->name . ' - ' . ($system_settings['platform_name'] ?? 'Unified Revenue'))

@section('head_scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection

@section('body_attributes')
    x-data="pageApp({{ json_encode($tableRows ?? []) }})"
@endsection

@section('content')
    <!-- Main Container -->
    <main class="w-full max-w-7xl mx-auto px-6 py-12 flex-grow relative z-10">

        <!-- Back Link & Title -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="/"
                    class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors group">
                    <i data-lucide="arrow-left"
                        class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    Back to Home
                </a>
                <h1 class="text-3xl font-black text-slate-900 mt-4 tracking-tight">{{ $establishment->name }}</h1>
                <p class="text-slate-500 text-xs mt-1">Verify details and securely settle outstanding tax liabilities.</p>
            </div>
            <div>
                <span
                    class="px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-full text-xs font-black text-emerald-600 uppercase tracking-widest flex items-center gap-1.5 shadow-sm">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                    Official Verified
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Info Panel -->
            <div class="space-y-6">
                <!-- Registration Info Card -->
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-100/50">
                    <h3 class="text-xs font-black primary-text uppercase tracking-widest mb-6">Establishment Details</h3>

                    <div class="space-y-6">
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Registration ID</span>
                            <span class="text-sm font-mono font-bold text-slate-800">{{ $establishment->unique_id }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Registered Occupant</span>
                            <span class="text-sm font-bold text-slate-800">{{ $establishment->occupant->name ?? 'None' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Business Classification</span>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-50 border border-slate-100 px-2.5 py-1.5 rounded-lg inline-block mt-0.5">
                                {{ $establishment->establishmentType->value }}
                                ({{ $establishment->establishmentSize->value }})
                            </span>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Physical Address</span>
                            <span class="text-xs font-semibold text-slate-600 block leading-relaxed">
                                {{ $establishment->address }}, {{ $establishment->ward }} Ward,
                                {{ $establishment->lga }} LGA
                            </span>
                        </div>
                        @if($establishment->inside_metro)
                            <div>
                                <span class="px-3 py-1.5 bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                    Inside Metro Zone
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Tax Rules Panel -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Assessment Billing Metrics Summary -->
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-100/50">
                    <h3 class="text-xs font-black primary-text uppercase tracking-widest mb-6">Tax Assessment & Billing Summary</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Total Assessment</span>
                            <span class="text-base font-black text-slate-800">₦{{ number_format($taxStatus['totals']['due'], 2) }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Total Settled</span>
                            <span class="text-base font-black text-emerald-600">₦{{ number_format($taxStatus['totals']['paid'], 2) }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Outstanding Balance</span>
                            <span class="text-base font-black text-rose-600">₦{{ number_format($taxStatus['totals']['outstanding'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-100/50">

                    <!-- Header row: title + filter pills -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <h3 class="text-xs font-black primary-text uppercase tracking-widest">Evaluation Details &amp; Outstanding Taxes</h3>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button @click="setFilter('all')"
                                :class="filter==='all' ? 'primary-btn text-white shadow' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all">
                                All&nbsp;<span x-text="'('+rows.length+')'"></span>
                            </button>
                            <button @click="setFilter('outstanding')"
                                :class="filter==='outstanding' ? 'bg-amber-500 text-white shadow shadow-amber-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all">
                                Outstanding&nbsp;<span x-text="'('+rows.filter(r=>r.status==='outstanding').length+')'"></span>
                            </button>
                            <button @click="setFilter('settled')"
                                :class="filter==='settled' ? 'bg-emerald-500 text-white shadow shadow-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all">
                                Settled&nbsp;<span x-text="'('+rows.filter(r=>r.status==='settled').length+')'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Records info bar -->
                    <div class="flex items-center justify-between mb-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span>
                            Showing <span x-text="filtered.length ? pageStart+1 : 0"></span>–<span x-text="Math.min(pageStart+perPage, filtered.length)"></span> of <span x-text="filtered.length"></span>
                        </span>
                        <span x-show="totalPages > 1">Page <span x-text="page"></span> / <span x-text="totalPages"></span></span>
                    </div>

                    @if(count($tableRows) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th class="py-4 text-center text-[9px] font-black text-slate-400 uppercase tracking-widest w-12">
                                            <input type="checkbox" :checked="allPageOutstandingSelected"
                                                @change="togglePageOutstanding($event)"
                                                class="rounded border-slate-300 w-4 h-4 cursor-pointer">
                                        </th>
                                        <th class="py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Revenue Code &amp; Rule</th>
                                        <th class="py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Period</th>
                                        <th class="py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Assessment</th>
                                        <th class="py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Paid</th>
                                        <th class="py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Balance</th>
                                        <th class="py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <template x-for="row in pageRows" :key="row.rule_id+'-'+row.period">
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-5 text-center pr-4">
                                                <template x-if="row.status==='outstanding'">
                                                    <input type="checkbox" :checked="isSelected(row)"
                                                        @change="toggleRow($event,row)"
                                                        class="rounded border-slate-300 w-4 h-4 cursor-pointer">
                                                </template>
                                                <template x-if="row.status==='settled'">
                                                    <span class="inline-flex items-center justify-center w-4 h-4 text-emerald-500">
                                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                    </span>
                                                </template>
                                            </td>
                                            <td class="py-5 pr-4">
                                                <span class="text-xs font-bold text-slate-800 block" x-text="row.rule_name"></span>
                                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider block mt-0.5" x-text="row.agency_name"></span>
                                            </td>
                                            <td class="py-5 pr-4 text-xs font-bold text-slate-600 capitalize" x-text="row.period"></td>
                                            <td class="py-5 pr-4 text-xs font-bold text-slate-800 text-right" x-text="'₦'+fmt(row.due_amount)"></td>
                                            <td class="py-5 pr-4 text-xs font-bold text-emerald-600 text-right" x-text="'₦'+fmt(row.paid_amount)"></td>
                                            <td class="py-5 pr-4 text-xs font-black text-rose-600 text-right" x-text="'₦'+fmt(row.outstanding_amount)"></td>
                                            <td class="py-5 text-center">
                                                <template x-if="row.status==='outstanding'">
                                                    <span class="px-2.5 py-1 bg-amber-50 border border-amber-100 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-lg">Outstanding</span>
                                                </template>
                                                <template x-if="row.status==='settled'">
                                                    <span class="px-3 py-1 bg-emerald-50 border border-emerald-100 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-lg">Settled</span>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="filtered.length===0">
                                        <td colspan="7" class="py-10 text-center text-slate-400 text-xs font-bold uppercase tracking-wider">
                                            No records match the selected filter.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div x-show="totalPages > 1"
                            class="mt-5 flex flex-col sm:flex-row items-center justify-between gap-3 pt-5 border-t border-slate-100">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                <span x-text="filtered.length"></span> total records
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button @click="goTo(page-1)" :disabled="page===1"
                                    :class="page===1?'text-slate-300 cursor-not-allowed':'text-slate-600 hover:bg-slate-100'"
                                    class="px-3 py-2 text-[10px] font-black bg-slate-50 rounded-xl transition-colors">&laquo;</button>
                                <template x-for="p in pageRange" :key="p">
                                    <button @click="goTo(p)"
                                        :class="p===page?'primary-btn text-white':'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                        class="px-3 py-2 text-[10px] font-black rounded-xl transition-all"
                                        x-text="p"></button>
                                </template>
                                <button @click="goTo(page+1)" :disabled="page===totalPages"
                                    :class="page===totalPages?'text-slate-300 cursor-not-allowed':'text-slate-600 hover:bg-slate-100'"
                                    class="px-3 py-2 text-[10px] font-black bg-slate-50 rounded-xl transition-colors">&raquo;</button>
                            </div>
                        </div>

                        <!-- Unified Checkout Panel -->
                        <div x-show="selectedItems.length > 0" x-transition
                            class="mt-6 p-6 bg-slate-50 border border-slate-100 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-0.5">Selected Items</span>
                                <span class="text-xs font-black text-slate-800"><span x-text="selectedItems.length"></span> Outstanding Tax(es) Selected</span>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-0.5">Total Amount</span>
                                    <span class="text-xl font-black primary-text">₦<span x-text="formatMoney(totalSelectedAmount)"></span></span>
                                </div>
                                <button @click="checkoutModal = true"
                                    class="px-6 py-4 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg flex items-center gap-2">
                                    Pay Selected
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                    @else
                        <div class="text-center py-8 text-slate-400 text-xs font-bold uppercase tracking-wider">
                            No revenue rules currently evaluated for this establishment.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Payment Checkout Modal overlay -->
    <div x-show="checkoutModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition
        x-cloak>
        <div class="w-full max-w-md bg-white border border-slate-100 rounded-[2.5rem] shadow-2xl p-8 relative max-h-[90vh] overflow-y-auto overflow-x-hidden fancy-scrollbar"
            @click.outside="checkoutModal = false">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full blur-xl"></div>

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-black text-slate-950">Initialize Secure Checkout</h3>
                <button @click="checkoutModal = false" class="text-slate-400 hover:text-slate-950 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="{{ route('public.pay') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="establishment_id" value="{{ $establishment->id }}">
                <input type="hidden" name="items" :value="JSON.stringify(selectedItems)">
                <input type="hidden" name="amount" :value="grandTotalSelectedAmount">

                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">Payment Breakdown</span>
                    <div class="space-y-3 bg-slate-50 p-4 rounded-3xl border border-slate-100">
                        <!-- Assessments Subtotal -->
                        <div class="flex justify-between items-center text-xs border-b border-slate-200/50 pb-2">
                            <span class="font-bold text-slate-600">Subtotal (Assessments)</span>
                            <span class="font-bold text-slate-800">₦<span x-text="formatMoney(totalSelectedAmount)"></span></span>
                        </div>
                        
                        <!-- Selected Assessments Details Accordion -->
                        <details class="group bg-white border border-slate-100 rounded-xl overflow-hidden [&_summary::-webkit-details-marker]:hidden shadow-sm">
                            <summary class="flex items-center justify-between px-3 py-2 cursor-pointer select-none">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Show Details (<span x-text="selectedItems.length"></span>)</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 group-open:rotate-180 transition-transform"></i>
                            </summary>
                            <div class="px-3 pb-3 pt-1 border-t border-slate-50 space-y-2 max-h-32 overflow-y-auto fancy-scrollbar">
                                <template x-for="item in selectedItems" :key="item.rule_id + '-' + item.period">
                                    <div class="flex justify-between items-center text-[10px] text-slate-600">
                                        <div class="max-w-[70%] truncate">
                                            <span class="font-bold block truncate" x-text="item.rule_name"></span>
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest" x-text="item.period"></span>
                                        </div>
                                        <span class="font-bold text-slate-700">₦<span x-text="formatMoney(item.amount)"></span></span>
                                    </div>
                                </template>
                            </div>
                        </details>

                        <!-- Service Fee -->
                        <template x-if="serviceFee > 0">
                            <div class="flex justify-between items-center text-xs text-slate-600 pt-1">
                                <div>
                                    <span class="font-bold block">Processing / Portal Fee</span>
                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Flat Service Charge</span>
                                </div>
                                <span class="font-bold text-slate-800">₦<span x-text="formatMoney(serviceFee)"></span></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Total Checkout Amount</span>
                    <p class="text-2xl font-black primary-text">₦<span x-text="formatMoney(grandTotalSelectedAmount)"></span></p>
                    <p class="text-[10px] text-slate-500 font-medium leading-relaxed mt-2 flex items-start gap-1.5 bg-slate-50 border border-slate-100 p-3 rounded-2xl">
                        <i data-lucide="info" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span>Please note: The checkout total may include an additional payment gateway processing fee added on top of the service fee.</span>
                    </p>
                </div>

                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">Taxpayer Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="name@example.com"
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 focus:border-emerald-500/40 rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all">
                </div>

                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">Taxpayer Phone Number <span class="text-rose-500">*</span></label>
                    <input type="tel" name="phone" required placeholder="e.g. 08012345678"
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 focus:border-emerald-500/40 rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all">
                </div>

                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">Select Gateway <span class="text-rose-500">*</span></label>
                    <select name="gateway" required
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500/20 text-xs font-bold text-slate-800 focus:outline-none">
                        @if(\App\Models\Setting::get('paystack_active', true))
                            <option value="Paystack">Paystack</option>
                        @endif
                        @if(\App\Models\Setting::get('monnify_active', true))
                            <option value="Monnify">Monnify</option>
                        @endif
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full py-4 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-xl flex items-center justify-center gap-2">
                        Proceed to Settle
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function pageApp(rows) {
            return {
                // Table data
                rows,
                filter: 'all',
                page: 1,
                perPage: 10,

                // Selection + checkout state (all in one scope — no $root needed)
                selectedItems: [],
                checkoutModal: false,
                serviceFee: {{ $serviceFeeAgency ? $serviceFeeAgency->service_fee_amount : 0 }},

                // ── Filtering & Pagination ─────────────────────────────
                get filtered() {
                    if (this.filter === 'all') return this.rows;
                    return this.rows.filter(r => r.status === this.filter);
                },
                get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
                get pageStart() { return (this.page - 1) * this.perPage; },
                get pageRows() { return this.filtered.slice(this.pageStart, this.pageStart + this.perPage); },
                get pageRange() {
                    const cur = this.page, last = this.totalPages;
                    const lo = Math.max(1, cur - 2), hi = Math.min(last, cur + 2);
                    return Array.from({ length: hi - lo + 1 }, (_, i) => lo + i);
                },
                setFilter(f) { this.filter = f; this.page = 1; },
                goTo(p) { if (p >= 1 && p <= this.totalPages) this.page = p; },

                // ── Select-all for current page outstanding rows ───────
                get allPageOutstandingSelected() {
                    const outstanding = this.pageRows.filter(r => r.status === 'outstanding');
                    return outstanding.length > 0 && outstanding.every(r => this.isSelected(r));
                },
                togglePageOutstanding(e) {
                    const outstanding = this.pageRows.filter(r => r.status === 'outstanding');
                    if (e.target.checked) {
                        outstanding.forEach(r => {
                            if (!this.isSelected(r)) {
                                this.selectedItems = [...this.selectedItems, this.toItem(r)];
                            }
                        });
                    } else {
                        this.selectedItems = this.selectedItems.filter(
                            s => !outstanding.some(r => r.rule_id === s.rule_id && r.period === s.period)
                        );
                    }
                },

                // ── Row checkbox ──────────────────────────────────────
                isSelected(row) {
                    return this.selectedItems.some(s => s.rule_id === row.rule_id && s.period === row.period);
                },
                toggleRow(e, row) {
                    if (e.target.checked) {
                        if (!this.isSelected(row)) {
                            this.selectedItems = [...this.selectedItems, this.toItem(row)];
                        }
                    } else {
                        this.selectedItems = this.selectedItems.filter(
                            s => !(s.rule_id === row.rule_id && s.period === row.period)
                        );
                    }
                },
                toItem(row) {
                    return {
                        rule_id: row.rule_id,
                        rule_name: row.rule_name,
                        agency_id: row.agency_id,
                        agency_name: row.agency_name,
                        period: row.period,
                        amount: row.outstanding_amount,
                    };
                },

                // ── Totals & formatting ───────────────────────────────
                get totalSelectedAmount() {
                    return this.selectedItems.reduce((sum, i) => sum + i.amount, 0);
                },
                get grandTotalSelectedAmount() {
                    if (this.selectedItems.length === 0) return 0;
                    return this.totalSelectedAmount + this.serviceFee;
                },
                formatMoney(v) {
                    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
                },
                fmt(v) {
                    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
                },
            };
        }
    </script>
@endsection