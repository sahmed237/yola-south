@extends('layouts.public')

@section('title', 'Verify Payment – ' . ($system_settings['platform_name'] ?? 'Unified Revenue Portal'))

@section('styles')
    <style>
        body { 
            background: linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%) !important; 
        }
    </style>
@endsection

@section('body_class', 'min-h-screen flex flex-col justify-between py-12 px-4 select-none relative overflow-x-hidden')

<!-- Disable layout header for custom receipt design -->
@section('header', '')

@section('content')
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-20 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl -z-10"></div>

    <div class="max-w-2xl w-full mx-auto my-auto relative z-10">

        <!-- Toolbar -->
        <div class="mb-6 flex items-center justify-between">
            <a href="/" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-500 transition-colors group">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Portal Home
            </a>
            @if($isValid)
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Verified Authentic
                </span>
            @else
                <span class="text-[10px] font-black uppercase tracking-widest text-rose-400 flex items-center gap-1">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-rose-400"></i>
                    Verification Error
                </span>
            @endif
        </div>

        @if($isValid)
            <!-- Receipt Card -->
            <div class="bg-white text-slate-800 rounded-[2.5rem] shadow-2xl p-10 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-2 primary-btn"></div>

                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 pb-8 border-b-2 border-dashed border-slate-100 mb-8">
                    <div>
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="w-7 h-7 primary-btn rounded-lg flex items-center justify-center text-white shrink-0">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-extrabold text-slate-900 tracking-wider uppercase">Official Unified Revenue Verification</span>
                        </div>
                        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Receipt Authenticity Confirmed</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Unified Revenue Database Record Lookup</p>
                    </div>
                    <div class="text-left md:text-right">
                        <span class="px-3.5 py-1.5 bg-emerald-50 border border-emerald-100 rounded-full text-[10px] font-black text-emerald-600 uppercase tracking-widest inline-flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            Paid &amp; Settled
                        </span>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-2.5">
                            Settlement Date: {{ $invoice->updated_at->format('M d, Y @ h:i A') }}
                        </p>
                    </div>
                </div>

                <!-- Business & Invoice Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Establishment Profile</span>
                        <h3 class="text-base font-black text-slate-900">{{ $establishment->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1 font-semibold">Unique ID: {{ $establishment->unique_id }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $establishment->address }}</p>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Payment Details</span>
                        <p class="text-xs font-bold text-slate-800">Invoice: {{ $invoice->reference }}</p>
                        <p class="text-xs text-slate-500 mt-1 font-bold">Gateway: {{ $invoice->gateway }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 font-bold">Email: {{ $invoice->email }}</p>
                    </div>
                </div>

                <!-- Line Items Breakdown -->
                <div class="mb-8">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-3">Revenue Lines Settled</span>
                    <div class="rounded-3xl border border-slate-100 overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Revenue Head</th>
                                    <th class="py-3 px-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Period</th>
                                    <th class="py-3 px-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Agency</th>
                                    <th class="py-3 px-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="py-3 px-4 font-bold text-slate-800">{{ $item->revenueHead->name ?? $item->revenueRule->name ?? '—' }}</td>
                                        <td class="py-3 px-4 text-slate-500 capitalize">{{ $item->period }}</td>
                                        <td class="py-3 px-4 text-slate-500">{{ $item->agency->name ?? '—' }}</td>
                                        <td class="py-3 px-4 text-right font-black text-slate-800">₦{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-200">
                                <tr>
                                    <td colspan="3" class="py-4 px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Grand Total Settled</td>
                                    <td class="py-4 px-4 text-right text-lg font-black text-slate-900">₦{{ number_format($invoice->total_amount, 2) }}</td>
                                </tr>
                                @if($invoice->payment_fee > 0)
                                <tr class="border-t border-slate-100">
                                    <td colspan="3" class="py-2.5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Processing Fee</td>
                                    <td class="py-2.5 px-4 text-right text-xs font-bold text-slate-500">-₦{{ number_format($invoice->payment_fee, 2) }}</td>
                                </tr>
                                <tr class="border-t border-slate-100 bg-slate-50/60">
                                    <td colspan="3" class="py-3 px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Net Revenue Settled</td>
                                    <td class="py-3 px-4 text-right text-sm font-extrabold text-slate-800">₦{{ number_format($invoice->total_amount - $invoice->payment_fee, 2) }}</td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Agency Split Summary -->
                @if($invoice->splits->count() > 0)
                <div class="mb-8 bg-emerald-50/40 p-6 rounded-3xl border border-emerald-100">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-3">Agency Split Distribution (Net Payouts)</span>
                    <div class="space-y-3">
                        @foreach($invoice->splits as $split)
                            <div class="flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-slate-800 block">{{ $split->agency->name ?? '—' }}</span>
                                    <span class="text-[9px] font-mono text-emerald-600 uppercase tracking-wider">
                                        {{ number_format($split->ratio, 1) }}% Share &middot; Gross: ₦{{ number_format($split->amount, 2) }}
                                    </span>
                                </div>
                                <span class="font-black text-emerald-700">₦{{ number_format($split->net_amount ?? $split->amount, 2) }} Net</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Verification Block -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4 primary-text animate-pulse"></i>
                            Authentic State Record
                        </h4>
                        <p class="text-[10px] text-slate-400 font-semibold leading-relaxed mt-1">
                            This transaction is securely signed and archived in the central treasury log.
                        </p>
                    </div>
                    <div class="text-[9px] font-black px-3 py-1 bg-slate-100 rounded-full text-slate-500 uppercase tracking-wider">
                        Secure SSL
                    </div>
                </div>

                <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center -z-10 opacity-30 select-none pointer-events-none">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-200">VERIFIED</span>
                </div>
            </div>
        @else
            <!-- Error Card -->
            <div class="bg-white text-slate-800 rounded-[2.5rem] shadow-2xl p-10 relative overflow-hidden text-center space-y-6">
                <div class="absolute top-0 left-0 right-0 h-2 bg-rose-500"></div>
                <div class="flex justify-center py-4">
                    <div class="w-16 h-16 bg-rose-50 border border-rose-100 rounded-full flex items-center justify-center text-rose-500 shadow-lg shadow-rose-500/10">
                        <i data-lucide="shield-alert" class="w-8 h-8"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Receipt Verification Failed</h3>
                    <p class="text-xs text-rose-600 font-bold bg-rose-50 px-4 py-3 rounded-xl border border-rose-100/60 max-w-md mx-auto">
                        We could not find an authentic, settled payment matching this reference in our database system.
                    </p>
                </div>
                <div class="text-[10px] text-slate-400 font-semibold max-w-sm mx-auto leading-relaxed">
                    If you believe this is a system sync delay, please contact support or attempt verification in a few minutes.
                </div>
                <div class="pt-2">
                    <a href="/" class="px-6 py-3.5 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all inline-block">
                        Return to Portal Home
                    </a>
                </div>
            </div>
        @endif

    </div>
@endsection

@section('footer')
    <footer class="w-full max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 relative z-10">
        <p class="font-medium">&copy; {{ date('Y') }} {{ $system_settings['platform_name'] ?? 'Unified Revenue Collection System' }}. All rights reserved.</p>
        <p class="flex items-center gap-1.5 font-semibold text-slate-400 mt-2 md:mt-0 uppercase tracking-wider">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
            Database Verification Server Active
        </p>
    </footer>
@endsection
