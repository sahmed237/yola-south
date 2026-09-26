@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-center print:hidden">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.invoices.index') }}" class="p-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-2xl transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Invoice Details</h1>
            <p class="text-slate-500 text-sm">Invoice #{{ $invoice->reference }} details, item breakdown, and split ledger audits.</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        @if($invoice->status !== 'success')
            @can('verify invoice')
            <form action="{{ route('admin.invoices.reverify', $invoice->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-250 flex items-center gap-2">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Verify Payment
                </button>
            </form>
            @endcan
        @endif
        <button onclick="window.print()" class="px-6 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-900 transition-all shadow-xl flex items-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Print / Save PDF
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Invoice Overview & Metadata -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Overview Card -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Overview</h3>
            
            <div class="space-y-6">
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Status</span>
                    @php
                        $statColor = match($invoice->status) {
                            'success' => 'bg-emerald-100 text-emerald-700',
                            'failed' => 'bg-rose-100 text-rose-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statColor }}">
                        {{ $invoice->status === 'success' ? 'Settled' : ucfirst($invoice->status) }}
                    </span>
                </div>

                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Reference ID</span>
                    <span class="text-sm font-mono font-bold text-slate-800">{{ $invoice->reference }}</span>
                </div>

                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Payment Gateway</span>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700">
                        {{ $invoice->gateway }}
                    </span>
                </div>

                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Taxpayer Email</span>
                    <span class="text-sm font-bold text-slate-800 block">{{ $invoice->email }}</span>
                    @if(!empty($invoice->metadata['phone']))
                        <span class="text-xs text-slate-400 block mt-0.5">{{ $invoice->metadata['phone'] }}</span>
                    @endif
                </div>

                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Date Created</span>
                    <span class="text-sm font-bold text-slate-800">{{ $invoice->created_at->format('M d, Y h:ia') }}</span>
                </div>
            </div>
        </div>

        <!-- Establishment Details Card -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Establishment</h3>

            @if($invoice->establishment)
                <div class="space-y-4">
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Name</span>
                        <a href="{{ route('admin.establishments.details', $invoice->establishment->id) }}" class="text-sm font-bold text-primary-600 hover:underline">
                            {{ $invoice->establishment->name }}
                        </a>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Establishment ID</span>
                        <span class="text-xs font-mono font-bold text-slate-800">{{ $invoice->establishment->unique_id }}</span>
                    </div>
                    @if($invoice->establishment->occupant)
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Occupant Name</span>
                            <span class="text-xs font-bold text-slate-700">{{ $invoice->establishment->occupant->name }}</span>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-xs text-slate-400">This establishment has been deleted.</p>
            @endif
        </div>
    </div>

    <!-- Right Column: Assessments & Split Breakdown Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Itemised Assessments -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Itemised Assessments</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue Head</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Agency</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Period</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="py-3 text-xs font-bold text-slate-800">{{ $item->revenueHead->name ?? $item->revenueRule->name ?? 'N/A' }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-black uppercase tracking-wider">
                                        {{ $item->agency->code ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-3 text-xs font-semibold text-slate-500 uppercase tracking-widest">{{ $item->period }}</td>
                                <td class="py-3 text-xs font-black text-slate-800 text-right">₦{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500">Subtotal (Assessments)</span>
                <span class="text-sm font-black text-slate-800">
                    ₦{{ number_format($invoice->items->sum('amount'), 2) }}
                </span>
            </div>
        </div>

        <!-- Settlement Splits & Ledger Records -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Settlement Ledger Splits</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Agency / Recipient</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Subaccount</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Ratio (%)</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Gross Split</th>
                            <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Net Split</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($invoice->splits as $split)
                            <tr class="group">
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
                                <td class="py-4 text-xs font-mono font-bold text-slate-700">
                                    {{ $split->subaccount_code ?? 'NO_SUBACCOUNT' }}
                                </td>
                                <td class="py-4 text-xs font-bold text-slate-500 text-right">{{ number_format($split->ratio, 2) }}%</td>
                                <td class="py-4 text-xs font-black text-slate-800 text-right">₦{{ number_format($split->amount, 2) }}</td>
                                <td class="py-4 text-xs font-black text-emerald-600 text-right">
                                    @if($invoice->status === 'success')
                                        ₦{{ number_format($split->net_amount, 2) }}
                                    @else
                                        <span class="text-slate-400 italic">Pending Settlement</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    No ledger splits recorded for this invoice reference.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Ledger Summary -->
            <div class="mt-8 pt-6 border-t border-slate-100 space-y-3">
                <div class="flex justify-between items-center text-xs text-slate-500">
                    <span>Total Checkout Amount</span>
                    <span class="font-bold text-slate-800">₦{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-500">
                    <span>Gateway Transaction Charges</span>
                    <span class="font-bold text-rose-600">
                        @if($invoice->status === 'success')
                            - ₦{{ number_format($invoice->payment_fee, 2) }}
                        @else
                            ₦0.00
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-slate-200/50">
                    <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Total Net Settled</span>
                    <span class="text-lg font-black text-emerald-600">
                        @if($invoice->status === 'success')
                            ₦{{ number_format($invoice->total_amount - $invoice->payment_fee, 2) }}
                        @else
                            ₦0.00
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
