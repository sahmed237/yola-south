@extends('layouts.public')

@section('title', 'Securing Payment via ' . $invoice->gateway)

@section('content')
    <!-- Content -->
    <main class="w-full max-w-7xl mx-auto px-6 py-12 flex-grow flex items-center justify-center relative z-10 animate-fade-in">
        <div class="max-w-lg w-full bg-white rounded-[2.5rem] p-8 shadow-2xl relative border border-slate-100 {{ $invoice->gateway === 'Paystack' ? 'hover:border-orange-500/10' : 'hover:border-blue-500/10' }} transition-all duration-300">
            <!-- Gateway Badge -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-slate-900 mt-4 tracking-tight">Settle Unified Revenue</h2>
                <p class="text-xs text-slate-500 mt-1">Unified collection — split routing active across agencies.</p>
            </div>

            <!-- Invoice Header Details -->
            <div class="space-y-3 bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-6 font-semibold">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Business</span>
                    <span class="text-slate-800 font-bold">{{ $establishment->name }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Invoice No.</span>
                    <span class="text-slate-800 font-mono font-bold">{{ $invoice->reference }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Taxpayer Email</span>
                    <span class="text-slate-800 font-bold">{{ $invoice->email }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Taxpayer Phone</span>
                    <span class="text-slate-800 font-bold">{{ $invoice->metadata['phone'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Revenue Lines</span>
                    <span class="text-slate-800 font-bold">{{ $invoice->items->count() }} rule(s)</span>
                </div>
                <div class="pt-3 border-t border-slate-200/60 flex justify-between items-center">
                    <span class="text-slate-500 font-black uppercase tracking-widest text-[10px]">Total Amount</span>
                    <span class="text-lg font-black text-slate-800">₦{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Line Items -->
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3 text-slate-600">
                    <i data-lucide="list" class="w-4 h-4 shrink-0"></i>
                    <h4 class="text-[10px] font-black uppercase tracking-widest">Revenue Items Being Settled</h4>
                </div>
                <div class="space-y-2">
                    @foreach($invoice->items as $item)
                        <div class="flex justify-between items-center text-xs bg-slate-50 px-4 py-3 rounded-xl border border-slate-100">
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $item->revenueRule->name ?? '—' }}</span>
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">{{ $item->period }} · {{ $item->agency->name ?? '—' }}</span>
                            </div>
                            <span class="font-black text-slate-800">₦{{ number_format($item->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Split Routing Table -->
            <div class="bg-emerald-50/50 border border-emerald-100 p-6 rounded-2xl mb-8">
                <div class="flex items-center gap-2 mb-4 text-emerald-700">
                    <i data-lucide="split" class="w-4 h-4 shrink-0"></i>
                    <h4 class="text-[10px] font-black uppercase tracking-widest">Live Split Routing Table</h4>
                </div>
                @if(count($splits) > 0)
                    <div class="space-y-3">
                        @foreach($splits as $split)
                            <div class="flex justify-between items-start text-xs leading-relaxed">
                                <div>
                                    <span class="text-slate-800 font-bold block">{{ $split['agency_name'] }}</span>
                                    <span class="text-[9px] font-mono text-emerald-600 font-bold uppercase tracking-wider block mt-0.5">
                                        {{ $split['subaccount'] }} &mdash; {{ number_format($split['ratio'], 1) }}%
                                    </span>
                                </div>
                                <span class="text-emerald-700 font-black">₦{{ number_format($split['amount'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                        No subaccounts configured — funds will settle to consolidated pool.
                    </div>
                @endif
            </div>

            <!-- Payment Actions -->
            <div class="space-y-4">
                <a href="{{ $redirectUrl }}"
                    class="w-full py-4 text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-xl flex items-center justify-center gap-2 text-center primary-btn text-white">
                    <i data-lucide="credit-card" class="w-4 h-4 shrink-0"></i>
                    Pay Now via {{ $invoice->gateway }}
                </a>

                @if(config('app.env') !== 'production')
                    <form action="{{ route('public.payment-success', $invoice->reference) }}" method="GET">
                        <input type="hidden" name="simulate" value="true">
                        <button type="submit"
                            class="w-full py-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black uppercase tracking-widest rounded-2xl transition-all flex items-center justify-center gap-2 border border-slate-200/60 shadow-sm">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
                            Simulate Payment Success (Sandbox Override)
                        </button>
                    </form>
                @endif

                <a href="{{ route('public.establishment.show', $establishment->unique_id) }}"
                    class="w-full py-4 bg-slate-50 border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-black uppercase tracking-widest rounded-2xl transition-all text-center block font-bold">
                    Cancel Payment
                </a>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <div class="flex items-center justify-center gap-1.5 text-[9px] text-slate-400 uppercase tracking-widest font-semibold">
                    <i data-lucide="lock" class="w-3.5 h-3.5 primary-text"></i>
                    Sandbox Gateway — Encryption Active
                </div>
            </div>
        </div>
    </main>
@endsection