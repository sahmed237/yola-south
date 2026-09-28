@extends('layouts.public')

@section('title', 'Update Shop Application - ' . $allocation->application_no)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10" x-data="{ updating: false }">
    <!-- Top Breadcrumb -->
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
            <a href="{{ route('public.shop-application.track-status', $allocation->application_no) }}" class="hover:text-emerald-700 flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Tracking
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-slate-900 font-mono font-bold">{{ $allocation->application_no }}</span>
        </div>
        <span class="px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold rounded-full">
            Action Requested
        </span>
    </div>

    <!-- Officer Request Callout Banner -->
    @if($allocation->action_required_notes)
    <div class="mb-8 p-6 bg-amber-50 border border-amber-200 rounded-[2rem] shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-amber-100 text-amber-800 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] font-black text-amber-800 uppercase tracking-widest block mb-1">
                    Officer Instructions &middot; Requested {{ $allocation->action_requested_at?->diffForHumans() ?? 'recently' }}
                </span>
                <h3 class="text-base font-bold text-slate-900 mb-1">Updates Requested by Verification Officer</h3>
                <p class="text-sm text-amber-950/90 leading-relaxed font-medium whitespace-pre-line">
                    {{ $allocation->action_required_notes }}
                </p>
                <div class="mt-3 text-xs text-amber-800/80">
                    Please modify the necessary fields below and attach updated documents if requested, then click <strong>Submit Application Updates</strong>.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Update Form Container -->
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
        <div class="flex items-center justify-between pb-6 mb-8 border-b border-slate-100">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Update Application Details</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Reference ID: <strong class="font-mono text-slate-900">{{ $allocation->application_no }}</strong> &middot; Verified Email: <strong class="font-mono text-slate-900">{{ $allocation->applicant_email }}</strong>
                </p>
            </div>
            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                <i data-lucide="edit-3" class="w-5 h-5"></i>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-8 p-6 bg-red-50 border border-red-100 text-red-700 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                <h3 class="font-bold text-sm">Please correct the following errors:</h3>
            </div>
            <ul class="list-disc ml-8 text-xs space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('public.shop-application.update', $allocation->application_no) }}" method="POST" enctype="multipart/form-data" @submit="updating = true" class="space-y-8">
            @csrf

            <!-- 1. Personal Identification -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">1. Personal Identification</h2>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Applicant credentials & contact</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Full Name (as on ID) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="applicant_name" value="{{ old('applicant_name', $allocation->applicant_name) }}" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                            placeholder="Enter full legal name">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Mobile Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="applicant_phone" value="{{ old('applicant_phone', $allocation->applicant_phone) }}" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                            placeholder="0803 000 0000">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            NIN / National Identity No.
                        </label>
                        <input type="text" name="applicant_nin_bvn" value="{{ old('applicant_nin_bvn', $allocation->applicant_nin_bvn) }}"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                            placeholder="11-digit NIN Number">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Residential Address <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="applicant_address" value="{{ old('applicant_address', $allocation->applicant_address) }}" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                            placeholder="Street address, ward, Yola South">
                    </div>
                </div>
            </div>

            <!-- 2. Trade & Market Preference -->
            <div class="pt-6 border-t border-slate-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">2. Trade & Market Preference</h2>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Market location & commercial trade line</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Target Market <span class="text-red-500">*</span>
                        </label>
                        <select name="market_id" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                            @foreach($markets as $m)
                            <option value="{{ $m->id }}" {{ old('market_id', $allocation->market_id) == $m->id ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->ward_name }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Line of Business / Trade <span class="text-red-500">*</span>
                        </label>
                        <select name="trade_type" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                            @php
                                $trades = [
                                    'Retail — Provisions & Groceries',
                                    'Textiles & Fashion Retail',
                                    'Electronics & Appliance Repair',
                                    'Building Materials & Hardware',
                                    'Grain & Agricultural Produce',
                                    'Pharmacy & Patent Medicine',
                                    'Cosmetics & Personal Care',
                                    'Other Commercial Trade'
                                ];
                            @endphp
                            @foreach($trades as $trade)
                            <option value="{{ $trade }}" {{ old('trade_type', $allocation->trade_type) == $trade ? 'selected' : '' }}>
                                {{ $trade }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Preferred Unit Size
                        </label>
                        <select name="requested_size"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                            <option value="3.0 × 4.0 m" {{ old('requested_size', $allocation->requested_size) == '3.0 × 4.0 m' ? 'selected' : '' }}>Standard Lock-up (3.0 × 4.0 m)</option>
                            <option value="2.5 × 3.0 m" {{ old('requested_size', $allocation->requested_size) == '2.5 × 3.0 m' ? 'selected' : '' }}>Compact Stall (2.5 × 3.0 m)</option>
                            <option value="4.0 × 5.0 m" {{ old('requested_size', $allocation->requested_size) == '4.0 × 5.0 m' ? 'selected' : '' }}>Double Commercial Unit (4.0 × 5.0 m)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. Supporting Documents -->
            <div class="pt-6 border-t border-slate-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">3. Supporting Documents</h2>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Upload updated documents if requested</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border-2 border-dashed border-slate-200 hover:border-primary-500/50 bg-slate-50/50 rounded-2xl p-6 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">
                                Passport Photo
                            </label>
                            @if($allocation->passport_photo)
                            <a href="{{ asset('storage/' . $allocation->passport_photo) }}" target="_blank" class="text-[11px] text-emerald-700 font-bold hover:underline flex items-center gap-1">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View Current
                            </a>
                            @endif
                        </div>
                        <input type="file" name="passport_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-all">
                        <p class="mt-2 text-[11px] text-slate-400">Upload new photo to replace current (JPEG, PNG &middot; Max 3MB).</p>
                    </div>

                    <div class="border-2 border-dashed border-slate-200 hover:border-primary-500/50 bg-slate-50/50 rounded-2xl p-6 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">
                                Valid ID Document
                            </label>
                            @if($allocation->id_document)
                            <a href="{{ asset('storage/' . $allocation->id_document) }}" target="_blank" class="text-[11px] text-emerald-700 font-bold hover:underline flex items-center gap-1">
                                <i data-lucide="file-check" class="w-3.5 h-3.5"></i> View Current
                            </a>
                            @endif
                        </div>
                        <input type="file" name="id_document" accept="image/*,application/pdf" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-all">
                        <p class="mt-2 text-[11px] text-slate-400">NIN slip, Voter's card or Driver's licence (PDF/Image &middot; Max 3MB).</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between gap-4">
                <a href="{{ route('public.shop-application.track-status', $allocation->application_no) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    Cancel & Return
                </a>
                <button type="submit" :disabled="updating" class="primary-btn px-8 py-4 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all flex items-center gap-2">
                    <span x-show="updating" x-cloak class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Submitting Updates...</span>
                    </span>
                    <span x-show="!updating" class="inline-flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Submit Application Updates</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
