@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Revenue Reports & Analytics</h1>
        <p class="text-slate-500 text-sm">Interactive audit ledger tracking agency revenue allocations and portal service fees.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.reports.export', request()->query()) }}" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-200 flex items-center gap-2">
            <i data-lucide="download" class="w-4 h-4"></i>
            Export Filtered CSV
        </a>
        <button onclick="window.print()" class="px-6 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-900 transition-all shadow-xl flex items-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Print Report
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1: Gross Collections -->
    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary-50 rounded-full blur-xl"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600 shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-black text-primary-600 bg-primary-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Gross</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Gross Revenue Collected</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">₦{{ number_format($totalGross, 2) }}</h3>
    </div>

    <!-- Card 2: Gateway Fees -->
    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full blur-xl"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600 shrink-0">
                <i data-lucide="percent" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Charges</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Gateway/Portal Fees</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">₦{{ number_format($totalFees, 2) }}</h3>
    </div>

    <!-- Card 3: Net Settled -->
    <div class="bg-white rounded-[2.5rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full blur-xl"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Net</span>
        </div>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Net Settled</p>
        <h3 class="text-2xl font-black text-slate-800 mt-1">₦{{ number_format($totalNet, 2) }}</h3>
    </div>
</div>

<!-- Interactive Analytics Charts Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Trend Line Chart -->
    <div class="lg:col-span-2 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Collections Trend</h3>
                <p class="text-xs text-slate-400">Total daily unified collection volumes over time.</p>
            </div>
            <div class="w-3.5 h-3.5 rounded-full bg-primary-500"></div>
        </div>
        <div class="h-80 relative w-full">
            <canvas id="collectionsTrendChart"></canvas>
        </div>
    </div>

    <!-- Agency Allocations Doughnut Chart -->
    <div class="lg:col-span-1 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Share Distributions</h3>
                <p class="text-xs text-slate-400">Relative allocation split per agency.</p>
            </div>
            <div class="w-3.5 h-3.5 rounded-full bg-emerald-500"></div>
        </div>
        <div class="h-80 relative w-full flex items-center justify-center">
            @if($totalGross > 0)
                <canvas id="agencyShareChart"></canvas>
            @else
                <div class="text-slate-400 text-xs text-center">
                    <i data-lucide="pie-chart" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                    No distribution data
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Filters, Breakdown & Performance Details -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Filters & Settings Panel -->
    <div class="lg:col-span-1 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 print:hidden">
        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Filter Ledger</h3>

        <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-6">
            <!-- Date Range Selector -->
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Date Range</label>
                <input type="text" name="date_range" value="{{ request('date_range') }}" placeholder="Select date range..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-semibold text-slate-700">
            </div>

            <!-- Recipient Agency -->
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Recipient Agency</label>
                <select name="agency_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-600">
                    <option value="">-- All Agencies --</option>
                    @foreach($agencies as $agency)
                        <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                            {{ $agency->name }} ({{ $agency->code }}) @if($agency->is_service_fee) [SF] @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Revenue Rule -->
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Revenue Rule</label>
                <select name="revenue_rule_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-600">
                    <option value="">-- All Revenue Rules --</option>
                    @foreach($revenueRules as $rule)
                        <option value="{{ $rule->id }}" {{ request('revenue_rule_id') == $rule->id ? 'selected' : '' }}>
                            {{ $rule->name }} ({{ $rule->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Gateway Channel -->
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Channel / Gateway</label>
                <select name="gateway" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-600">
                    <option value="">-- All Channels --</option>
                    <option value="Cash" {{ request('gateway') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank Transfer" {{ request('gateway') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Paystack" {{ request('gateway') === 'Paystack' ? 'selected' : '' }}>Paystack</option>
                    <option value="Monnify" {{ request('gateway') === 'Monnify' ? 'selected' : '' }}>Monnify</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 transition-all text-center">
                    Apply Filter
                </button>
                @if(request()->anyFilled(['date_range', 'agency_id', 'revenue_rule_id', 'gateway']))
                    <a href="{{ route('admin.reports.index') }}" class="px-5 py-3 bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all text-center font-bold">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Agency Allocation Splits list -->
    <div class="lg:col-span-1 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Agency Share Splits</h3>

        <div class="space-y-5 max-h-[26rem] overflow-y-auto fancy-scroll pr-1">
            @forelse($agencyBreakdown as $agencyRow)
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <div class="overflow-hidden mr-2">
                            <span class="text-xs font-black text-slate-800 block truncate" title="{{ $agencyRow['agency_name'] }}">
                                {{ $agencyRow['agency_name'] }}
                            </span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">{{ $agencyRow['agency_code'] }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-black text-slate-800 block">₦{{ number_format($agencyRow['gross'], 2) }}</span>
                            <span class="text-[10px] font-bold text-emerald-600 block">Net: ₦{{ number_format($agencyRow['net'], 2) }}</span>
                        </div>
                    </div>

                    @php
                        $percent = $totalGross > 0 ? ($agencyRow['gross'] / $totalGross) * 100 : 0;
                        $barColor = $agencyRow['is_service_fee'] ? 'bg-rose-500' : 'bg-primary-600';
                    @endphp
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="{{ $barColor }} h-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @empty
                <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                    <i data-lucide="bar-chart-2" class="w-10 h-10 mb-3 opacity-20"></i>
                    <p class="text-xs font-bold uppercase tracking-widest">No allocations recorded</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Revenue Rules Performance list -->
    <div class="lg:col-span-1 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Revenue Rules Ranking</h3>

        <div class="space-y-5 max-h-[26rem] overflow-y-auto fancy-scroll pr-1">
            @forelse($rulesBreakdown as $ruleRow)
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <div class="overflow-hidden mr-2">
                            <span class="text-xs font-black text-slate-800 block truncate" title="{{ $ruleRow['rule_name'] }}">
                                {{ $ruleRow['rule_name'] }}
                            </span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">
                                {{ $ruleRow['rule_code'] }} &bull; {{ $ruleRow['count'] }} payment(s)
                            </span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-black text-slate-800 block">₦{{ number_format($ruleRow['amount'], 2) }}</span>
                        </div>
                    </div>

                    @php
                        $rulePercent = $totalGross > 0 ? ($ruleRow['amount'] / $totalGross) * 100 : 0;
                    @endphp
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-indigo-600 h-full transition-all duration-500" style="width: {{ $rulePercent }}%"></div>
                    </div>
                </div>
            @empty
                <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                    <i data-lucide="calculator" class="w-10 h-10 mb-3 opacity-20"></i>
                    <p class="text-xs font-bold uppercase tracking-widest">No rule payments found</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Ledger Audit Records -->
<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <h3 class="text-lg font-black text-slate-800">Ledger Allocations Audit</h3>
            <p class="text-xs text-slate-400">Audit trail of individual transaction allocations and gateway fee breakdowns.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 text-left">
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reference</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Establishment</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Recipient Agency</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Split Type</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Gateway</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Gross Split</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Gateway Fees</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Net Split</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($splits as $split)
                    @php
                        $payment = $split->payment;
                        $gross = (float) $split->amount;
                        $net = (float) ($split->net_amount ?? $split->amount);
                        $fee = max(0.0, $gross - $net);
                    @endphp
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 text-xs font-mono font-bold text-slate-700">
                            @if($payment && $payment->invoice_id)
                                <a href="{{ route('admin.invoices.show', $payment->invoice_id) }}" class="hover:text-primary-600 underline">
                                    {{ $payment->reference }}
                                </a>
                            @else
                                {{ $payment->reference ?? 'Manual Split' }}
                            @endif
                        </td>
                        <td class="py-4 text-xs font-medium text-slate-500">
                            {{ $payment ? $payment->created_at->format('M d, Y h:ia') : 'N/A' }}
                        </td>
                        <td class="py-4 text-xs font-bold text-slate-800">
                            {{ $payment->establishment->name ?? 'N/A' }}
                        </td>
                        <td class="py-4">
                            <span class="text-xs font-bold text-slate-800 block">{{ $split->agency->name ?? 'N/A' }}</span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $split->agency->code ?? 'N/A' }}</span>
                        </td>
                        <td class="py-4">
                            @if($split->is_service_fee)
                                <span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-md text-[9px] font-black uppercase tracking-wider">
                                    Service Fee
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-md text-[9px] font-black uppercase tracking-wider">
                                    Revenue
                                </span>
                            @endif
                        </td>
                        <td class="py-4">
                            @php
                                $gtColor = $payment ? match($payment->gateway) {
                                    'Cash' => 'bg-emerald-50 text-emerald-600',
                                    'Bank Transfer' => 'bg-blue-50 text-blue-600',
                                    'Paystack' => 'bg-orange-50 text-orange-600',
                                    'Monnify' => 'bg-purple-50 text-purple-600',
                                    default => 'bg-slate-50 text-slate-600',
                                } : 'bg-slate-50 text-slate-600';
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $gtColor }}">
                                {{ $payment->gateway ?? 'Manual' }}
                            </span>
                        </td>
                        <td class="py-4 text-xs font-black text-slate-800 text-right">₦{{ number_format($gross, 2) }}</td>
                        <td class="py-4 text-xs font-black text-rose-600 text-right">₦{{ number_format($fee, 2) }}</td>
                        <td class="py-4 text-xs font-black text-emerald-600 text-right">₦{{ number_format($net, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-12 flex flex-col items-center justify-center text-slate-400 text-center">
                            <i data-lucide="receipt" class="w-12 h-12 mb-4 opacity-20 mx-auto"></i>
                            <p class="text-sm font-bold uppercase tracking-widest">No transaction splits found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 print:hidden">
        {{ $splits->links() }}
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar {
        border-radius: 1.5rem !important;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
        border: 1px solid rgba(241, 245, 249, 1) !important;
        font-family: inherit !important;
    }
    .flatpickr-day.selected {
        background: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Flatpickr range selector
        flatpickr('input[name="date_range"]', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            maxDate: 'today',
            locale: {
                rangeSeparator: ' to '
            }
        });

        // 1. Collections Trend Line Chart
        const trendCtx = document.getElementById('collectionsTrendChart').getContext('2d');
        const trendLabels = @json($trendLabels);
        const trendValues = @json($trendValues);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels.length ? trendLabels : ['No Data'],
                datasets: [{
                    label: 'Collection Amount (₦)',
                    data: trendValues.length ? trendValues : [0],
                    borderColor: 'rgb(37, 99, 235)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(37, 99, 235)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            },
                            font: { size: 10, weight: 'bold' }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });

        // 2. Agency Share Doughnut Chart
        const shareCanvas = document.getElementById('agencyShareChart');
        if (shareCanvas) {
            const shareCtx = shareCanvas.getContext('2d');
            const agencyBreakdown = @json($agencyBreakdown);
            
            const agencyLabels = agencyBreakdown.map(r => r.agency_name);
            const agencyValues = agencyBreakdown.map(r => r.gross);
            
            // Premium Palette Colors
            const colors = [
                '#2563eb', // Indigo
                '#ec4899', // Pink
                '#10b981', // Emerald
                '#f59e0b', // Amber
                '#8b5cf6', // Violet
                '#06b6d4', // Cyan
                '#ef4444', // Red
                '#64748b'  // Slate
            ];

            new Chart(shareCtx, {
                type: 'doughnut',
                data: {
                    labels: agencyLabels,
                    datasets: [{
                        data: agencyValues,
                        backgroundColor: colors.slice(0, agencyLabels.length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 9, weight: 'bold' }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endpush
