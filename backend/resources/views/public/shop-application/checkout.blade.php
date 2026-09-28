@extends('layouts.public')

@section('title', 'Settle Commercial Unit Invoice - ' . $allocation->application_no)

@section('content')
<main class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-10 flex-grow flex items-center justify-center relative z-10 animate-fade-in">
    <div class="max-w-xl w-full bg-white rounded-3xl p-6 sm:p-10 shadow-2xl relative border border-slate-100 transition-all duration-300">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl primary-bg-light primary-text mb-3 border primary-border shadow-sm">
                <i data-lucide="receipt" class="w-7 h-7"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Settle Unit Allocation Invoice</h2>
            <p class="text-xs text-slate-500 mt-1">Yola South Local Government &middot; Directorate of Revenue</p>
        </div>

        <!-- Invoice & Allocation Summary Card -->
        <div class="space-y-3 bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-6 text-xs font-semibold">
            <div class="flex justify-between items-center">
                <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Application Ref</span>
                <span class="text-slate-800 font-mono font-bold">{{ $allocation->application_no }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Invoice Reference</span>
                <span class="primary-text font-mono font-bold">{{ $invoice->reference }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Market Facility</span>
                <span class="text-slate-800 font-bold">{{ $allocation->market->name }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Assigned Unit</span>
                <span class="text-slate-800 font-mono font-bold">{{ $allocation->shop?->block_name }} &middot; Unit {{ $allocation->shop?->shop_number }} ({{ $allocation->shop?->shop_code }})</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Applicant Name</span>
                <span class="text-slate-800 font-bold">{{ $allocation->applicant_name }}</span>
            </div>
            <div class="pt-3 border-t border-slate-200/70 flex justify-between items-center">
                <span class="text-slate-600 font-black uppercase tracking-widest text-[11px]">Total Invoice Amount</span>
                <span class="text-xl font-black primary-text">₦{{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Generic Line Items -->
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-3 text-slate-700">
                <i data-lucide="layers" class="w-4 h-4 primary-text"></i>
                <h4 class="text-[11px] font-black uppercase tracking-widest">Statutory Invoice Items</h4>
            </div>
            <div class="space-y-2.5">
                @forelse($invoice->genericItems as $item)
                    <div class="flex justify-between items-start text-xs bg-slate-50/80 px-4 py-3.5 rounded-xl border border-slate-100">
                        <div>
                            <span class="font-bold text-slate-900 block">{{ $item->item_name }}</span>
                            @if($item->description)
                                <span class="text-[10px] text-slate-500 block mt-0.5">{{ $item->description }}</span>
                            @endif
                        </div>
                        <span class="font-black text-slate-900 font-mono whitespace-nowrap ml-4">₦{{ number_format($item->amount, 2) }}</span>
                    </div>
                @empty
                    <div class="flex justify-between items-center text-xs bg-slate-50 px-4 py-3.5 rounded-xl border border-slate-100">
                        <span class="font-bold text-slate-900">Total Statutory Allocation Fee &amp; Initial Rent</span>
                        <span class="font-black text-slate-900 font-mono">₦{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Gateway Switcher -->
        <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Select Payment Channel</span>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('public.shop-application.checkout', ['application_no' => $allocation->application_no, 'gateway' => 'monnify']) }}" 
                   class="px-4 py-3 text-center font-bold text-xs flex items-center justify-center gap-2 transition-all {{ strtolower($invoice->gateway) === 'monnify' ? 'primary-btn text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:border-slate-300' }}"
                   style="border-radius: var(--btn-radius, 0.75rem);">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 {{ strtolower($invoice->gateway) === 'monnify' ? 'opacity-100' : 'opacity-0' }}"></i>
                    Monnify
                </a>
                <a href="{{ route('public.shop-application.checkout', ['application_no' => $allocation->application_no, 'gateway' => 'paystack']) }}" 
                   class="px-4 py-3 text-center font-bold text-xs flex items-center justify-center gap-2 transition-all {{ strtolower($invoice->gateway) === 'paystack' ? 'primary-btn text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:border-slate-300' }}"
                   style="border-radius: var(--btn-radius, 0.75rem);">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 {{ strtolower($invoice->gateway) === 'paystack' ? 'opacity-100' : 'opacity-0' }}"></i>
                    Paystack
                </a>
            </div>
        </div>

        @if(!empty($gatewayError))
            <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                    Payment Gateway Connection Notice
                </div>
                <p class="text-slate-600 leading-relaxed">{{ $gatewayError }}</p>
                <p class="text-slate-500 mt-1">Please try selecting another payment channel above or contact the revenue department.</p>
            </div>
        @endif

        <!-- Payment Actions -->
        <div class="space-y-3.5">
            @if($redirectUrl)
                <a href="{{ $redirectUrl }}" 
                   class="primary-btn w-full py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl flex items-center justify-center gap-2 text-center transition-all">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    Pay ₦{{ number_format($invoice->total_amount, 2) }} via {{ strtoupper($invoice->gateway) }}
                </a>
            @else
                <button type="button" disabled 
                   class="w-full py-4 text-xs font-black uppercase tracking-widest text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed flex items-center justify-center gap-2 text-center"
                   style="border-radius: var(--btn-radius, 0.75rem);">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-slate-400"></i>
                    Channel Temporarily Unavailable
                </button>
            @endif

            <a href="{{ route('public.shop-application.track-status', $allocation->application_no) }}" 
               class="w-full py-3.5 bg-slate-50 border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider transition-all text-center block"
               style="border-radius: var(--btn-radius, 0.75rem);">
                &larr; Return to Application Tracking
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <div class="flex items-center justify-center gap-1.5 text-[10px] text-slate-400 uppercase tracking-widest font-semibold">
                <i data-lucide="shield" class="w-3.5 h-3.5 primary-text"></i>
                Encrypted &amp; Secured by Central Revenue Service
            </div>
        </div>
    </div>
</main>
@endsection
