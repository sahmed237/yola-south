@extends('layouts.public')

@section('title', 'Application Status - ' . $allocation->application_no)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
            <a href="{{ route('public.shop-application.track') }}" class="hover:text-emerald-700">Track</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-slate-900 font-mono font-bold">{{ $allocation->application_no }}</span>
        </div>
        <a href="{{ route('public.shop-application.track') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            Check another reference
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 flex items-center gap-3">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Main Status Card -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xl mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Reference Number</span>
                <h1 class="text-2xl font-black text-slate-900 font-mono tracking-tight">{{ $allocation->application_no }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Submitted by {{ $allocation->applicant_name }} &middot; {{ $allocation->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div>
                @if($allocation->stage === 7)
                    <span class="px-4 py-1.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full inline-flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="award" class="w-4 h-4"></i> Allocated & Completed
                    </span>
                @elseif($allocation->status === 'rejected')
                    <span class="px-4 py-1.5 bg-red-100 text-red-800 text-xs font-bold rounded-full inline-flex items-center gap-1.5">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Rejected
                    </span>
                @elseif($allocation->status === 'action_required')
                    <span class="px-4 py-1.5 bg-amber-100 text-amber-900 text-xs font-bold rounded-full inline-flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-700"></i> Action Required
                    </span>
                @else
                    <span class="px-4 py-1.5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full inline-flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4"></i> Stage {{ $allocation->stage }} of 7 &mdash; {{ ucfirst($allocation->status) }}
                    </span>
                @endif
            </div>
        </div>

        <!-- 7-Stage Progress Stepper -->
        <div class="py-8">
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
                @php
                    $steps = [
                        1 => ['title' => 'Application', 'desc' => 'Received'],
                        2 => ['title' => 'Review', 'desc' => 'Verification'],
                        3 => ['title' => 'Inspection', 'desc' => 'Recommended'],
                        4 => ['title' => 'Approval', 'desc' => 'Directorate'],
                        5 => ['title' => 'Allocation', 'desc' => 'Shop Assigned'],
                        6 => ['title' => 'Payment', 'desc' => 'Settlement'],
                        7 => ['title' => 'Handover', 'desc' => 'Card Issued'],
                    ];
                @endphp

                @foreach($steps as $sNum => $sInfo)
                @php
                    $isDone = $allocation->stage > $sNum || ($allocation->stage === 7 && $sNum === 7);
                    $isCurrent = $allocation->stage === $sNum && $allocation->stage < 7;
                @endphp
                <div class="rounded-xl p-3 text-center border transition-all {{ $isDone ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900' : ($isCurrent ? 'bg-blue-50 border-blue-400 text-blue-900 ring-2 ring-blue-400/20' : 'bg-slate-50 border-slate-200 text-slate-400') }}">
                    <div class="w-6 h-6 rounded-full mx-auto mb-1 flex items-center justify-center text-xs font-bold font-mono {{ $isDone ? 'bg-emerald-600 text-white' : ($isCurrent ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                        @if($isDone)
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        @else
                            {{ $sNum }}
                        @endif
                    </div>
                    <div class="text-[11px] font-bold leading-tight">{{ $sInfo['title'] }}</div>
                    <div class="text-[10px] mt-0.5 {{ $isDone ? 'text-emerald-700' : ($isCurrent ? 'text-blue-700' : 'text-slate-400') }}">{{ $sInfo['desc'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Stage Message Banner -->
        @if($allocation->status === 'action_required')
        <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 shadow-sm">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-200 text-amber-900 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-amber-800 uppercase tracking-widest block mb-0.5">Applicant Action Required</span>
                    <h3 class="text-sm font-bold text-amber-950">Updates Requested by Verification Officer</h3>
                    <p class="text-xs text-amber-900 mt-1 leading-relaxed font-medium whitespace-pre-line">{{ $allocation->action_required_notes }}</p>
                </div>
            </div>
            <a href="{{ route('public.shop-application.edit', $allocation->application_no) }}" class="primary-btn px-6 py-3 text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                Update Application Now
            </a>
        </div>
        @elseif($allocation->stage === 7)
        <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center flex-shrink-0">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-950">Allocation Card & Certificate Ready!</h3>
                    <p class="text-xs text-emerald-800">Your unit is ready for handover. You can view, download, or print your official allocation certificate now.</p>
                </div>
            </div>
            <a href="{{ route('public.shop-application.certificate', $allocation->application_no) }}" target="_blank" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                <i data-lucide="printer" class="w-4 h-4"></i>
                View Official Card
            </a>
        </div>
        @elseif($allocation->stage === 6 && $allocation->payment_status !== 'paid')
        <div class="p-6 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-5 mb-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-950">Shop Unit Allocated &mdash; Invoice Ready for Settlement</h3>
                    <p class="text-xs text-emerald-800 mt-0.5">Assigned Unit: <b class="font-mono">{{ $allocation->shop?->block_name }} &middot; Unit {{ $allocation->shop?->shop_number }} ({{ $allocation->shop?->shop_code }})</b></p>
                    <div class="mt-2 text-xs font-semibold text-emerald-900">
                        Total Amount Due: <span class="font-black text-emerald-700 font-mono text-sm">₦{{ number_format($allocation->rent_amount + $allocation->allocation_fee, 2) }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('public.shop-application.checkout', $allocation->application_no) }}" class="primary-btn px-6 py-3 text-white font-bold text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                Pay Online Now &rarr;
            </a>
        </div>
        @elseif($allocation->stage === 5)
        <div class="p-5 bg-blue-50 border border-blue-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-950">Application Approved &mdash; Unit Allocation in Progress</h3>
                    <p class="text-xs text-blue-800">Your application has been approved by the Revenue Directorate. Council market officers are currently assigning your vacant shop unit.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Target Market</span>
                <span class="font-bold text-slate-900 text-sm block">{{ $allocation->market->name }}</span>
                <span class="text-slate-500">{{ $allocation->market->ward_name }} Ward, Yola South</span>
            </div>

            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Allocated Unit</span>
                @if($allocation->shop)
                    <span class="font-bold text-emerald-800 text-sm block font-mono">{{ $allocation->shop->block_name }} &middot; Unit {{ $allocation->shop->shop_number }}</span>
                    <span class="text-slate-500 font-mono">{{ $allocation->shop->shop_code }} ({{ $allocation->shop->size }})</span>
                @else
                    <span class="font-semibold text-slate-700 italic block">Assignment in progress</span>
                    <span class="text-slate-400">Preferred size: {{ $allocation->requested_size ?? '3.0 × 4.0 m' }}</span>
                @endif
            </div>

            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Applicant Contact</span>
                <span class="font-bold text-slate-900 block">{{ $allocation->applicant_name }}</span>
                <span class="text-slate-500 font-mono">{{ $allocation->applicant_phone }} &middot; {{ $allocation->applicant_email }}</span>
            </div>

            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Monthly Rent Standing</span>
                <span class="font-mono font-bold text-slate-900 text-sm block">₦{{ number_format($allocation->rent_amount, 2) }}</span>
                <span class="text-slate-500 font-medium">Due monthly on the 5th</span>
            </div>
        </div>
    </div>
</div>
@endsection
