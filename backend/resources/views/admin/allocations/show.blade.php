@extends('layouts.admin')

@section('title', 'Application ' . $allocation->application_no)

@section('content')
<div x-data="{ rejectModal: false, updateModal: false, revertModal: false, reopenModal: false }">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <a href="{{ route('admin.allocations.index') }}" class="hover:text-emerald-700">Shop allocations</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold font-mono">{{ $allocation->application_no }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight font-mono">{{ $allocation->application_no }}</h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $allocation->stage === 7 ? 'bg-emerald-100 text-emerald-800' : ($allocation->status === 'rejected' ? 'bg-red-100 text-red-800' : ($allocation->status === 'action_required' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-800')) }}">
                    Stage {{ $allocation->stage }} of 7 &middot; {{ $allocation->status === 'action_required' ? 'Action Required' : ucfirst($allocation->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Submitted on {{ $allocation->created_at->format('d M Y, h:i A') }} &middot; {{ $allocation->market->name }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($allocation->stage === 7)
            <a href="{{ route('admin.allocations.card', $allocation) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="badge-check" class="w-4 h-4"></i>
                Official Allocation Card & Letter
            </a>
            @endif

            @if($allocation->status === 'rejected')
            <button type="button" @click="reopenModal = true" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 text-xs font-bold rounded-lg flex items-center gap-1.5 transition-colors shadow-sm">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                Reopen Application
            </button>
            @elseif($allocation->stage < 7)
                {{-- Request Update from Applicant --}}
                <button type="button" @click="updateModal = true" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition-colors">
                    <i data-lucide="mail-question" class="w-3.5 h-3.5"></i>
                    Request Applicant Update
                </button>

                {{-- Move Back / Revert Stage (Available if stage > 1) --}}
                @if($allocation->stage > 1)
                <button type="button" @click="revertModal = true" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition-colors">
                    <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                    Move Back / Revert
                </button>
                @endif

                {{-- Reject Application --}}
                <button type="button" @click="rejectModal = true" class="px-3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition-colors">
                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                    Reject Application
                </button>
            @endif

            <a href="{{ route('admin.allocations.index') }}" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 text-xs font-semibold rounded-lg">
                Back to list
            </a>
        </div>
    </div>

    <!-- Prominent Status Alerts -->
    @if($allocation->status === 'action_required')
    <div class="mb-6 p-4 bg-amber-50/90 border border-amber-300 rounded-xl shadow-sm flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
            <i data-lucide="mail-question" class="w-5 h-5"></i>
        </div>
        <div class="flex-1 text-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <span class="font-bold text-amber-950 text-sm">Action Required by Applicant</span>
                @if($allocation->action_requested_at)
                <span class="text-amber-800 text-[11px] font-mono">Notice Sent: {{ $allocation->action_requested_at->format('d M Y, h:i A') }}</span>
                @endif
            </div>
            <p class="text-amber-900 mt-1 leading-relaxed">
                An email notice was sent to <strong>{{ $allocation->applicant_email }}</strong> requesting corrections or additional details. The applicant can update their record using their tracking ID.
            </p>
            @if($allocation->action_required_notes)
            <div class="mt-2.5 p-3 bg-white/95 border border-amber-200 rounded-lg text-slate-800 font-medium whitespace-pre-line text-xs">
                <span class="text-[10px] uppercase font-bold text-amber-900 block mb-1">Officer Instructions Sent to Applicant:</span>
                {{ $allocation->action_required_notes }}
            </div>
            @endif
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <span class="text-slate-600 font-mono text-[11px]">Tracking ID: <strong>{{ $allocation->application_no }}</strong></span>
                @if($allocation->action_responded_at)
                <span class="text-emerald-700 font-bold inline-flex items-center gap-1 text-[11px]">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    Applicant responded on {{ $allocation->action_responded_at->format('d M Y, h:i A') }}
                </span>
                @endif
            </div>
        </div>
    </div>
    @elseif($allocation->status === 'rejected')
    <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-xl shadow-sm flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-lg bg-red-200 text-red-900 flex items-center justify-center shrink-0 mt-0.5">
            <i data-lucide="x-circle" class="w-5 h-5"></i>
        </div>
        <div class="flex-1 text-xs">
            <div class="flex items-center justify-between">
                <span class="font-bold text-red-950 text-sm">Application Rejected</span>
            </div>
            <p class="text-red-900 mt-1 font-medium leading-relaxed">
                Reason: {{ $allocation->rejection_reason ?? 'Application did not meet municipal requirements.' }}
            </p>
            <div class="mt-3">
                <button type="button" @click="reopenModal = true" class="px-3.5 py-1.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-lg text-xs inline-flex items-center gap-1.5 transition-colors shadow-sm">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    Reopen & Restore to Stage 1
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- 7-Stage Workflow Progress Stepper (Interactive / Live) -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm mb-6">
        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Stage Progress Tracker</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @php
                $steps = [
                    1 => ['name' => 'Application', 'sub' => 'Received'],
                    2 => ['name' => 'Review', 'sub' => 'Doc check'],
                    3 => ['name' => 'Recommendation', 'sub' => 'Market Officer'],
                    4 => ['name' => 'Approval', 'sub' => 'Revenue Director'],
                    5 => ['name' => 'Allocation', 'sub' => 'Unit assigned'],
                    6 => ['name' => 'Invoice & Pay', 'sub' => 'Rent cleared'],
                    7 => ['name' => 'Certificate', 'sub' => 'Handover card'],
                ];
            @endphp

            @foreach($steps as $sNum => $sInfo)
            @php
                $isDone = $allocation->stage > $sNum || ($allocation->stage === 7 && $sNum === 7);
                $isCurrent = $allocation->stage === $sNum && $allocation->stage < 7;
            @endphp
            <div class="rounded-lg p-3 text-center border transition-all {{ $isDone ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900' : ($isCurrent ? 'bg-blue-50 border-blue-400 text-blue-900 ring-2 ring-blue-400/20' : 'bg-slate-50 border-slate-200 text-slate-400') }}">
                <div class="w-6 h-6 rounded-full mx-auto mb-1.5 flex items-center justify-center text-xs font-bold font-mono {{ $isDone ? 'bg-emerald-600 text-white' : ($isCurrent ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                    @if($isDone)
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    @else
                        {{ $sNum }}
                    @endif
                </div>
                <div class="text-[11px] font-bold leading-tight">{{ $sInfo['name'] }}</div>
                <div class="text-[10px] mt-0.5 {{ $isDone ? 'text-emerald-700' : ($isCurrent ? 'text-blue-700' : 'text-slate-400') }}">{{ $sInfo['sub'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Main Content: Left Column (Details) & Right Column (Actions) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Details Column (Span 2) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Applicant & Business Details -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Applicant Profile & Business</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block">Full Name:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $allocation->applicant_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Phone Number:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $allocation->applicant_phone }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Email Address:</span>
                        <span class="font-medium text-slate-900">{{ $allocation->applicant_email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">NIN / BVN:</span>
                        <span class="font-mono text-slate-900">{{ $allocation->applicant_nin_bvn ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Line of Trade / Business:</span>
                        <span class="font-bold text-slate-900">{{ $allocation->trade_type }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Residential Address:</span>
                        <span class="font-medium text-slate-800">{{ $allocation->applicant_address ?? 'Yola South' }}</span>
                    </div>
                </div>
            </div>

            <!-- Unit Request & Allocation Standing -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Unit Requested & Financials</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block">Target Market:</span>
                        <span class="font-bold text-slate-900">{{ $allocation->market->name }}</span>
                        <span class="text-[11px] text-slate-400 block">{{ $allocation->market->ward_name ?? 'Ward' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Assigned Unit:</span>
                        @if($allocation->shop)
                            <span class="font-mono font-bold text-emerald-800 text-sm">{{ $allocation->shop->block_name }} &middot; Unit {{ $allocation->shop->shop_number }}</span>
                            <span class="text-[11px] text-slate-400 block font-mono">{{ $allocation->shop->shop_code }} ({{ $allocation->shop->size }})</span>
                        @else
                            <span class="text-amber-700 font-semibold italic">Pending Unit Assignment (Stage 5)</span>
                            <span class="text-[11px] text-slate-400 block">Preferred size: {{ $allocation->requested_size ?? '3.0 × 4.0 m' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 block">Monthly Rent Rate:</span>
                        <span class="font-mono font-bold text-slate-900">₦{{ number_format($allocation->rent_amount, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Application & Processing Fee:</span>
                        <span class="font-mono font-bold text-slate-900">₦{{ number_format($allocation->allocation_fee, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Payment Status:</span>
                        @if($allocation->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1 font-bold text-emerald-700">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Paid &middot; {{ $allocation->payment_reference }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 font-bold text-amber-700">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i> Unpaid &middot; Invoice Pending
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Uploaded Documents Preview -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Uploaded Verification Documents</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Passport Photo -->
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 flex items-center gap-3">
                        <div class="w-14 h-14 rounded-lg bg-slate-200 overflow-hidden flex items-center justify-center text-slate-400 font-mono text-xs">
                            @if($allocation->passport_photo)
                                <img src="{{ asset('storage/' . $allocation->passport_photo) }}" alt="Passport" class="w-full h-full object-cover">
                            @else
                                <i data-lucide="user" class="w-6 h-6"></i>
                            @endif
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Passport Photograph</div>
                            <div class="text-[11px] text-slate-400">
                                {{ $allocation->passport_photo ? 'Uploaded & attached' : 'Standard on file' }}
                            </div>
                            @if($allocation->passport_photo)
                            <a href="{{ asset('storage/' . $allocation->passport_photo) }}" target="_blank" class="text-[11px] text-emerald-700 font-semibold hover:underline">View photo</a>
                            @endif
                        </div>
                    </div>

                    <!-- ID Document -->
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 flex items-center gap-3">
                        <div class="w-14 h-14 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Valid ID Document</div>
                            <div class="text-[11px] text-slate-400">
                                {{ $allocation->id_document ? 'National ID / Voters Card' : 'Government ID verified' }}
                            </div>
                            @if($allocation->id_document)
                            <a href="{{ asset('storage/' . $allocation->id_document) }}" target="_blank" class="text-[11px] text-emerald-700 font-semibold hover:underline">View document</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Audit Trail -->
            @if($allocation->officer_recommendation || $allocation->approval_notes || $allocation->action_required_notes)
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Audit & Officer Endorsements</h3>
                <div class="space-y-4 text-xs">
                    @if($allocation->action_required_notes)
                    <div class="p-3 bg-amber-50/70 rounded-lg border border-amber-200">
                        <div class="font-bold text-amber-900 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="mail-question" class="w-3.5 h-3.5 text-amber-600"></i>
                                Applicant Update Notice
                            </span>
                            <span class="text-amber-700 font-normal">{{ $allocation->action_requested_at?->format('d M Y, h:i A') }}</span>
                        </div>
                        <p class="text-slate-700 mt-1 leading-relaxed">{{ $allocation->action_required_notes }}</p>
                        @if($allocation->action_responded_at)
                        <div class="mt-2 pt-2 border-t border-amber-200 text-[11px] text-emerald-800 font-semibold flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                            Applicant updated and re-submitted on {{ $allocation->action_responded_at->format('d M Y, h:i A') }}
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($allocation->officer_recommendation)
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/60">
                        <div class="font-bold text-slate-900 flex items-center justify-between">
                            <span>Market Officer Recommendation</span>
                            <span class="text-slate-400 font-normal">{{ $allocation->reviewed_at?->format('d M Y') }}</span>
                        </div>
                        <p class="text-slate-700 mt-1 leading-relaxed whitespace-pre-line">{{ $allocation->officer_recommendation }}</p>
                    </div>
                    @endif

                    @if($allocation->approval_notes)
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/60">
                        <div class="font-bold text-slate-900 flex items-center justify-between">
                            <span>Revenue Directorate Approval Note</span>
                            <span class="text-slate-400 font-normal">{{ $allocation->approved_at?->format('d M Y') }}</span>
                        </div>
                        <p class="text-slate-700 mt-1 leading-relaxed">{{ $allocation->approval_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Action Column (Span 1) -->
        <div class="space-y-6">
            <!-- Current Stage Action Panel -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2 flex items-center gap-1.5">
                    <i data-lucide="play-circle" class="w-4 h-4 text-emerald-600"></i>
                    Workflow Actions
                </h3>

                {{-- Stage 1 or 2: Market Officer Review & Recommendation --}}
                @if($allocation->stage <= 2 && $allocation->status !== 'rejected')
                @can('review allocation')
                <div class="space-y-4">
                    <form action="{{ route('admin.allocations.review', $allocation) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Market Officer Review Note *</label>
                            <textarea name="officer_recommendation" required rows="3" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Confirm applicant ID, trade viability, and suitability for market...">{{ $allocation->officer_recommendation }}</textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="adv_rec" name="advance_to_recommend" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <label for="adv_rec" class="text-xs text-slate-700">Recommend to Revenue Directorate (Stage 4)</label>
                        </div>
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center justify-center gap-1.5 transition-colors">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            Approve & Recommend (Stage 4)
                        </button>
                    </form>

                    <div class="pt-3 border-t border-slate-200">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Stage 1 Decision Options</span>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="updateModal = true" class="py-2 px-2 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 text-xs font-bold rounded-lg text-center flex items-center justify-center gap-1 transition-colors">
                                <i data-lucide="mail-question" class="w-3.5 h-3.5"></i>
                                Request Update
                            </button>
                            <button type="button" @click="rejectModal = true" class="py-2 px-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold rounded-lg text-center flex items-center justify-center gap-1 transition-colors">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-500">Awaiting Market Officer verification and recommendation.</p>
                @endcan

                {{-- Stage 3 or 4: Revenue Directorate Approval --}}
                @elseif(($allocation->stage === 3 || $allocation->stage === 4) && $allocation->status !== 'rejected')
                @can('approve allocation')
                <div class="space-y-4">
                    <form action="{{ route('admin.allocations.approve', $allocation) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="p-3 bg-amber-50 rounded-lg text-amber-900 text-xs mb-2">
                            Recommended by Market Officer. Revenue Director approval required to advance to unit allocation (Stage 5).
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Approval Notes / Conditions *</label>
                            <textarea name="approval_notes" required rows="3" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Approved subject to payment of standard allocation fee and compliance with market hygiene rules..."></textarea>
                        </div>
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                            Approve Allocation (Stage 5)
                        </button>
                    </form>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                        <button type="button" @click="revertModal = true" class="font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5"></i> Move back to Stage 1/2
                        </button>
                        <button type="button" @click="updateModal = true" class="font-semibold text-amber-700 hover:text-amber-900 flex items-center gap-1">
                            <i data-lucide="mail-question" class="w-3.5 h-3.5"></i> Request update
                        </button>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-500">Awaiting Director approval.</p>
                @endcan

                {{-- Stage 5: Assign Shop Unit --}}
                @elseif($allocation->stage === 5 && $allocation->status !== 'rejected')
                @can('execute allocation')
                <div class="space-y-4">
                    <form action="{{ route('admin.allocations.allocate', $allocation) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Select Vacant Unit in {{ $allocation->market->name }} *</label>
                            <select name="shop_id" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Choose Unit to Assign --</option>
                                @foreach($availableShops as $shop)
                                <option value="{{ $shop->id }}" {{ $allocation->shop_id == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->block_name }} &middot; Unit {{ $shop->shop_number }} ({{ $shop->size }}) &mdash; {{ $shop->formatted_monthly_rent }}/mo
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                            Assign Shop Unit (Stage 6)
                        </button>
                    </form>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                        <button type="button" @click="revertModal = true" class="font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5"></i> Revert Stage
                        </button>
                        <button type="button" @click="updateModal = true" class="font-semibold text-amber-700 hover:text-amber-900 flex items-center gap-1">
                            <i data-lucide="mail-question" class="w-3.5 h-3.5"></i> Request update
                        </button>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-500">Awaiting unit assignment.</p>
                @endcan

                {{-- Stage 6: Invoice & Payment (Online Gateway Status) --}}
                @elseif($allocation->stage === 6 && $allocation->status !== 'rejected')
                @can('execute allocation')
                <div class="space-y-4">
                    <!-- Live Online Invoice Status Box -->
                    <div class="p-4 bg-gradient-to-br from-amber-50 to-orange-50/40 border border-amber-200 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Awaiting Online Payment
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Strictly Online</span>
                        </div>

                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Invoice Reference:</span>
                                <span class="font-mono font-bold text-slate-900">{{ $allocation->latestInvoice?->reference ?? $allocation->invoice_no ?? 'INV-SHP-PENDING' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Allocation Fee:</span>
                                <span class="font-mono font-bold text-slate-900">₦{{ number_format($allocation->allocation_fee, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Monthly Rent Rate:</span>
                                <span class="font-mono font-bold text-slate-900">₦{{ number_format($allocation->rent_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-amber-200/80 font-bold text-slate-900">
                                <span class="text-xs uppercase tracking-wide text-slate-700">Total Invoice Due:</span>
                                <span class="font-mono text-sm text-emerald-700 font-extrabold">{{ $allocation->formatted_total_fee }}</span>
                            </div>
                        </div>

                        <p class="text-[11px] text-amber-900/90 leading-relaxed bg-white/70 p-2.5 rounded-lg border border-amber-200/60">
                            The applicant was emailed the payment link. When they settle online, this application automatically advances to <strong>Stage 7</strong> and activates the certificate.
                        </p>
                    </div>

                    <!-- Online Gateway Actions -->
                    <div class="space-y-2">
                        <!-- Verify Gateway Status Button -->
                        <form action="{{ route('admin.allocations.verify-payment', $allocation) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center justify-center gap-1.5 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                Re-query / Verify Gateway Status
                            </button>
                        </form>

                        <!-- Resend Notice & Copy Checkout Link -->
                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('admin.allocations.resend-payment-notice', $allocation) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg flex items-center justify-center gap-1 transition-colors">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-500"></i>
                                    Resend Email
                                </button>
                            </form>

                            <button type="button" 
                                    onclick="navigator.clipboard.writeText('{{ route('public.shop-application.checkout', $allocation->application_no) }}'); alert('Direct payment URL copied to clipboard!');" 
                                    class="w-full py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg flex items-center justify-center gap-1 transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5 text-slate-500"></i>
                                Copy Pay Link
                            </button>
                        </div>
                    </div>

                    {{-- Offline / Treasury Exception Override (Disabled by default; enable via ALLOW_OFFLINE_ALLOCATION_OVERRIDE=true in .env) --}}
                    @if(env('ALLOW_OFFLINE_ALLOCATION_OVERRIDE', false))
                    <div x-data="{ openManual: false }" class="pt-2 border-t border-slate-200 text-xs">
                        <button type="button" @click="openManual = !openManual" class="text-[11px] text-slate-500 hover:text-slate-800 flex items-center justify-between w-full font-medium">
                            <span>Offline / Treasury Exception Override</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="openManual ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openManual" x-cloak class="mt-2.5 p-3 bg-slate-50 rounded-lg border border-slate-200 space-y-2">
                            <p class="text-[10px] text-slate-500">Only use if approved by Directorate under exceptional administrative waiver or direct Council treasury reconciliation.</p>
                            <form action="{{ route('admin.allocations.payment', $allocation) }}" method="POST" class="space-y-2">
                                @csrf
                                <input type="text" name="payment_reference" placeholder="Treasury Receipt Ref (e.g. REC-YSLG-2026-0001)" class="w-full text-xs font-mono rounded-lg border-slate-200">
                                <button type="submit" class="w-full py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-[11px] font-bold rounded-lg">
                                    Force Confirm Settlement (Stage 7)
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <div class="pt-2 flex items-center justify-between text-xs">
                        <button type="button" @click="revertModal = true" class="font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5"></i> Revert Stage
                        </button>
                        <button type="button" @click="updateModal = true" class="font-semibold text-amber-700 hover:text-amber-900 flex items-center gap-1">
                            <i data-lucide="mail-question" class="w-3.5 h-3.5"></i> Request update
                        </button>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-500">Awaiting online invoice settlement by applicant.</p>
                @endcan

                {{-- Stage 7: Completed --}}
                @elseif($allocation->stage === 7)
                <div class="space-y-3 text-center">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center mx-auto">
                        <i data-lucide="award" class="w-6 h-6"></i>
                    </div>
                    <div class="font-bold text-slate-900 text-sm">Allocation Fully Active</div>
                    <p class="text-xs text-slate-500">Shop unit handed over. Official certificate and allocation card issued.</p>
                    <a href="{{ route('admin.allocations.card', $allocation) }}" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center justify-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print Certificate & Card
                    </a>
                </div>

                {{-- Rejected State in Action Panel --}}
                @elseif($allocation->status === 'rejected')
                <div class="space-y-3 text-center">
                    <div class="w-12 h-12 bg-red-100 text-red-700 rounded-full flex items-center justify-center mx-auto">
                        <i data-lucide="x-circle" class="w-6 h-6"></i>
                    </div>
                    <div class="font-bold text-slate-900 text-sm">Application Rejected</div>
                    <p class="text-xs text-slate-500">This application is inactive. You may reopen it to re-evaluate or update.</p>
                    <button type="button" @click="reopenModal = true" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center justify-center gap-1.5 transition-colors">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        Reopen Application
                    </button>
                </div>
                @endif
            </div>

            <!-- Allottee Conditions Notice -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-600">
                <div class="font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-slate-500"></i>
                    Council Regulation Note
                </div>
                No unit is handed over until the invoice raised at approval has been paid in full and reconciled.
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="rejectModal = false" class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-red-50">
                <h3 class="text-sm font-bold text-red-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="x-circle" class="w-4 h-4 text-red-600"></i>
                    Reject Application
                </h3>
                <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.allocations.reject', $allocation) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reason for Rejection *</label>
                    <textarea name="rejection_reason" required rows="3" placeholder="Provide specific reason (e.g. invalid documentation, trade prohibited in market, unit unavailable)..." class="w-full text-xs rounded-lg border-slate-200 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg text-[11px] text-slate-500 leading-relaxed">
                    Rejecting sets application status to Rejected and releases any reserved units. You can reopen this application later if needed.
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-sm">Reject Application</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Update Modal -->
    <div x-show="updateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="updateModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-amber-50">
                <h3 class="text-sm font-bold text-amber-950 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="mail-question" class="w-4 h-4 text-amber-600"></i>
                    Request Update from Applicant
                </h3>
                <button type="button" @click="updateModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.allocations.request-update', $allocation) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Instructions for Applicant *</label>
                    <textarea name="update_notes" required rows="4" placeholder="Specify clearly what the applicant needs to change or re-upload (e.g. Please upload a clear photo of your voter card, provide a valid NIN, or update business description)..." class="w-full text-xs rounded-lg border-slate-200 focus:ring-amber-500 focus:border-amber-500"></textarea>
                </div>
                <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-lg text-xs text-amber-900 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <i data-lucide="send" class="w-3.5 h-3.5 text-amber-700"></i>
                        Automated Notification Delivery
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        An email will be dispatched to <strong>{{ $allocation->applicant_email }}</strong> containing these instructions, their tracking ID (<strong>{{ $allocation->application_no }}</strong>), and a direct link to edit their application.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="updateModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        Send Update Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move Back / Revert Stage Modal -->
    <div x-show="revertModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="revertModal = false" class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="undo-2" class="w-4 h-4 text-slate-700"></i>
                    Move Back / Revert Stage
                </h3>
                <button type="button" @click="revertModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.allocations.revert', $allocation) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Stage *</label>
                    <select name="target_stage" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-slate-500 focus:border-slate-500">
                        @for($s = 1; $s < $allocation->stage; $s++)
                        <option value="{{ $s }}" {{ $s === ($allocation->stage - 1) ? 'selected' : '' }}>
                            Stage {{ $s }} &mdash; {{ $steps[$s]['name'] ?? 'Stage ' . $s }} ({{ $steps[$s]['sub'] ?? '' }})
                        </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reason for Moving Back</label>
                    <textarea name="revert_notes" rows="3" placeholder="Provide note or justification for reverting this application to an earlier stage..." class="w-full text-xs rounded-lg border-slate-200 focus:ring-slate-500 focus:border-slate-500"></textarea>
                </div>
                @if($allocation->stage >= 5)
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-900 leading-relaxed">
                    <strong>Note:</strong> Reverting before Stage 5 will unassign and release the shop unit back to available inventory.
                </div>
                @endif
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="revertModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg shadow-sm flex items-center gap-1.5">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Revert Stage
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reopen Modal -->
    <div x-show="reopenModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="reopenModal = false" class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-emerald-50">
                <h3 class="text-sm font-bold text-emerald-950 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-700"></i>
                    Reopen Application
                </h3>
                <button type="button" @click="reopenModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.allocations.reopen', $allocation) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="text-xs text-slate-700 leading-relaxed">
                    Are you sure you want to reopen application <strong>{{ $allocation->application_no }}</strong>?
                    <p class="mt-2 text-slate-500">
                        This will clear the rejection status and restore the application to <strong>Stage 1 (Application Received - Pending Review)</strong> for re-evaluation.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="reopenModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        Confirm Reopen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
