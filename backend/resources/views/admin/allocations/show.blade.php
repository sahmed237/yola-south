@extends('layouts.admin')

@section('title', 'Application ' . $allocation->application_no)

@section('content')
<div x-data="{ rejectModal: false }">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <a href="{{ route('admin.allocations.index') }}" class="hover:text-emerald-700">Shop allocations</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold font-mono">{{ $allocation->application_no }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight font-mono">{{ $allocation->application_no }}</h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $allocation->stage === 7 ? 'bg-emerald-100 text-emerald-800' : ($allocation->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                    Stage {{ $allocation->stage }} of 7 &middot; {{ ucfirst($allocation->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Submitted on {{ $allocation->created_at->format('d M Y, h:i A') }} &middot; {{ $allocation->market->name }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($allocation->stage === 7)
            <a href="{{ route('admin.allocations.card', $allocation) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="badge-check" class="w-4 h-4"></i>
                Official Allocation Card & Letter
            </a>
            @endif
            @if($allocation->status !== 'rejected' && $allocation->stage < 7)
            <button @click="rejectModal = true" class="px-3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 text-xs font-semibold rounded-lg">
                Reject Application
            </button>
            @endif
            <a href="{{ route('admin.allocations.index') }}" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 text-xs font-semibold rounded-lg">
                Back to list
            </a>
        </div>
    </div>

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
            @if($allocation->officer_recommendation || $allocation->approval_notes)
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Audit & Officer Endorsements</h3>
                <div class="space-y-4 text-xs">
                    @if($allocation->officer_recommendation)
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/60">
                        <div class="font-bold text-slate-900 flex items-center justify-between">
                            <span>Market Officer Recommendation</span>
                            <span class="text-slate-400 font-normal">{{ $allocation->reviewed_at?->format('d M Y') }}</span>
                        </div>
                        <p class="text-slate-700 mt-1 leading-relaxed">{{ $allocation->officer_recommendation }}</p>
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
                <form action="{{ route('admin.allocations.review', $allocation) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Market Officer Review Note *</label>
                        <textarea name="officer_recommendation" required rows="3" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Confirm applicant ID, trade viability, and suitability for market..."></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="adv_rec" name="advance_to_recommend" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="adv_rec" class="text-xs text-slate-700">Recommend to Revenue Directorate (Stage 3)</label>
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                        Submit Recommendation
                    </button>
                </form>
                @else
                <p class="text-xs text-slate-500">Awaiting Market Officer verification and recommendation.</p>
                @endcan

                {{-- Stage 3: Revenue Directorate Approval --}}
                @elseif($allocation->stage === 3 && $allocation->status !== 'rejected')
                @can('approve allocation')
                <form action="{{ route('admin.allocations.approve', $allocation) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="p-3 bg-amber-50 rounded-lg text-amber-900 text-xs mb-2">
                        Recommended by Market Officer. Revenue Director approval required to advance to unit allocation.
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Approval Notes / Conditions *</label>
                        <textarea name="approval_notes" required rows="3" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Approved subject to payment of standard allocation fee and compliance with market hygiene rules..."></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                        Approve Allocation (Stage 4)
                    </button>
                </form>
                @else
                <p class="text-xs text-slate-500">Awaiting Director approval.</p>
                @endcan

                {{-- Stage 4: Assign Shop Unit --}}
                @elseif($allocation->stage === 4 && $allocation->status !== 'rejected')
                @can('execute allocation')
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
                        Assign Shop Unit (Stage 5)
                    </button>
                </form>
                @else
                <p class="text-xs text-slate-500">Awaiting unit assignment.</p>
                @endcan

                {{-- Stage 5: Invoice & Payment Confirmation --}}
                @elseif($allocation->stage === 5 && $allocation->status !== 'rejected')
                @can('execute allocation')
                <form action="{{ route('admin.allocations.payment', $allocation) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="p-3 bg-slate-50 rounded-lg text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Monthly Rent:</span>
                            <span class="font-mono font-bold">₦{{ number_format($allocation->rent_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Allocation Fee:</span>
                            <span class="font-mono font-bold">₦{{ number_format($allocation->allocation_fee, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-200 font-bold text-slate-900">
                            <span>Total Due:</span>
                            <span>{{ $allocation->formatted_total_fee }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Receipt / Bank Reference</label>
                        <input type="text" name="payment_reference" placeholder="e.g. REC-YSLG-2026-002890" class="w-full text-xs font-mono rounded-lg border-slate-200">
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                        Confirm Payment (Stage 6)
                    </button>
                </form>
                @else
                <p class="text-xs text-slate-500">Awaiting invoice settlement.</p>
                @endcan

                {{-- Stage 6: Finalize & Issue Allocation Card --}}
                @elseif($allocation->stage === 6 && $allocation->status !== 'rejected')
                @can('execute allocation')
                <form action="{{ route('admin.allocations.complete', $allocation) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="p-3 bg-emerald-50 rounded-lg text-emerald-900 text-xs">
                        Payment verified. Ready to issue official allocation certificate and card.
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Certificate Endorsement Conditions</label>
                        <textarea name="conditions" rows="3" class="w-full text-xs rounded-lg border-slate-200">Rent falls due on the 5th of each month. The unit may not be sublet or transferred without the written approval of the Council. Three consecutive months in arrears is grounds for revocation. The allottee is responsible for the sanitation of the unit and its frontage.</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                        Generate Letter & Card (Stage 7)
                    </button>
                </form>
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
                <h3 class="text-sm font-bold text-red-900 uppercase tracking-wider">Reject Application</h3>
                <button @click="rejectModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.allocations.reject', $allocation) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reason for Rejection *</label>
                    <textarea name="rejection_reason" required rows="3" placeholder="Provide specific reason (e.g. invalid documentation, trade prohibited in market, unit unavailable)..." class="w-full text-xs rounded-lg border-slate-200 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-sm">Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
