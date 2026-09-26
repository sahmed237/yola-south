@extends('layouts.public')

@section('title', 'Receipt – Payment Settled – ' . ($system_settings['platform_name'] ?? 'Unified Revenue Portal'))

@section('head_scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection

@section('styles')
    <style>
        body { 
            background: linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%) !important; 
        }
        @media print {
            body { background: #ffffff !important; color: #000 !important; }
            .print-hidden { display: none !important; }
            .receipt-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
@endsection

@section('body_class', 'min-h-screen flex flex-col justify-between py-12 px-4 select-none relative overflow-x-hidden')

@section('body_attributes')
    x-data="{ verifying: true, success: false, errorMsg: '', attempt: 0 }"
    x-init="
        const params = new URLSearchParams(window.location.search);
        const simulate = params.get('simulate') || '{{ ($invoice->metadata['simulate'] ?? false) ? 'true' : 'false' }}';
        
        const verifyPayment = () => {
            verifying = true;
            errorMsg = '';
            
            fetch(window.location.pathname + '?action=verify&simulate=' + simulate, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.message || 'Verification failed') });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        verifying = false;
                        success = true;
                        $nextTick(() => lucide.createIcons());
                    }, 1500); // Keep spinner for premium feel
                } else {
                    verifying = false;
                    success = false;
                    errorMsg = data.message || 'Payment verification failed.';
                }
            })
            .catch(err => {
                verifying = false;
                success = false;
                errorMsg = err.message || 'A network error occurred. Please try again.';
            });
        };
        
        verifyPayment();
    "
@endsection

<!-- Disable layout header for custom receipt design -->
@section('header', '')

@section('content')
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl -z-10 print-hidden"></div>
    <div class="absolute bottom-20 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl -z-10 print-hidden"></div>

    <div class="max-w-2xl w-full mx-auto my-auto relative z-10">

        <!-- LOADING STATE -->
        <template x-if="verifying">
            <div class="bg-white rounded-[2.5rem] p-10 text-center shadow-2xl space-y-6">
                <div class="flex justify-center py-6">
                    <div class="relative w-20 h-20">
                        <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
                        <div class="absolute inset-0 rounded-full border-4 primary-border border-t-transparent animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <i data-lucide="shield-alert" class="w-8 h-8 text-slate-400 animate-pulse"></i>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Verifying Secure Payment</h3>
                    <p class="text-xs text-slate-500 font-semibold leading-relaxed max-w-sm mx-auto">
                        Please hold on while we query the gateway database and lock the settlement channels. Do not refresh this page.
                    </p>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 flex items-center justify-center gap-3">
                    <span class="w-2 h-2 rounded-full primary-btn animate-ping"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Querying {{ $invoice->gateway }}
                    </span>
                </div>
            </div>
        </template>

        <!-- ERROR STATE -->
        <template x-if="!verifying && !success">
            <div class="bg-white rounded-[2.5rem] p-10 text-center shadow-2xl space-y-6">
                <div class="flex justify-center py-4">
                    <div class="w-16 h-16 bg-rose-50 border border-rose-100 rounded-full flex items-center justify-center text-rose-500 shadow-lg shadow-rose-500/10">
                        <i data-lucide="x-circle" class="w-8 h-8"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Verification Incomplete</h3>
                    <p class="text-xs text-rose-600 font-bold bg-rose-50 px-4 py-3 rounded-xl border border-rose-100/60 max-w-md mx-auto" x-text="errorMsg"></p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                    <button @click="verifyPayment()" class="px-6 py-3.5 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all flex items-center justify-center gap-2">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Retry Verification
                    </button>
                    <a href="{{ route('public.establishment.show', $establishment->unique_id) }}" class="px-6 py-3.5 bg-slate-50 border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-black uppercase tracking-widest rounded-xl transition-all text-center block">
                        Cancel &amp; Go Back
                    </a>
                </div>
            </div>
        </template>

        <!-- SUCCESS STATE / RECEIPT CARD -->
        <template x-if="!verifying && success">
            <div class="space-y-6">
                <!-- Toolbar -->
                <div class="flex items-center justify-between print-hidden animate-fade-in">
                    <a href="{{ route('public.establishment.show', $establishment->unique_id) }}" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-500 transition-colors group">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                        Portal Home
                    </a>
                    <button onclick="window.print()" class="px-5 py-2.5 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all flex items-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print Receipt
                    </button>
                </div>

                <!-- Receipt Card -->
                <div class="bg-white text-slate-800 rounded-[2.5rem] shadow-2xl p-10 relative overflow-hidden receipt-card">
                    <div class="absolute top-0 left-0 right-0 h-2 primary-btn"></div>

                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 pb-8 border-b-2 border-dashed border-slate-100 mb-8">
                        <div>
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-7 h-7 primary-btn rounded-lg flex items-center justify-center text-white shrink-0">
                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs font-extrabold text-slate-900 tracking-wider uppercase">Official Unified Revenue Receipt</span>
                            </div>
                            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Tax Clearance Receipt</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Secure Transaction Reference Log</p>
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
                                        <th class="py-3 px-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Revenue Rule</th>
                                        <th class="py-3 px-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Period</th>
                                        <th class="py-3 px-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Agency</th>
                                        <th class="py-3 px-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($invoice->items as $item)
                                        <tr>
                                            <td class="py-3 px-4 font-bold text-slate-800">{{ $item->revenueRule->name ?? '—' }}</td>
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
                    <div class="flex flex-col md:flex-row items-center gap-6 pt-6 border-t border-slate-100">
                        <div class="w-24 h-24 bg-white p-1 rounded-2xl flex items-center justify-center shrink-0 border border-slate-200 shadow-sm relative overflow-hidden">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('public.payment-verify', $invoice->reference)) }}" 
                                 alt="Verification QR Code" 
                                 class="w-full h-full object-contain">
                        </div>
                        <div class="text-center md:text-left">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">Instant Security Verification</h4>
                            <p class="text-[10px] text-slate-400 font-semibold leading-relaxed mt-1">
                                Scan this QR code to securely verify this transaction against the state unified revenue database.
                            </p>
                        </div>
                    </div>

                    <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center -z-10 opacity-30 select-none pointer-events-none">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-200">VERIFIED</span>
                    </div>
                </div>
            </div>
        </template>

    </div>
@endsection

@section('footer')
    <footer class="w-full max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 relative z-10 print-hidden">
        <p class="font-medium">&copy; {{ date('Y') }} {{ $system_settings['platform_name'] ?? 'Unified Revenue Collection System' }}. All rights reserved.</p>
        <p class="flex items-center gap-1.5 font-semibold text-slate-400 mt-2 md:mt-0 uppercase tracking-wider">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
            Receipt Verified Secure
        </p>
    </footer>
@endsection
