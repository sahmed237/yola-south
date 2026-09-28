@extends('layouts.public')

@section('title', 'Apply for Market Shop or Stall - Yola South Local Government')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10" x-data="shopApplication()">
    <!-- Hero / Header -->
    <div class="text-center max-w-3xl mx-auto mb-10">
        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-widest rounded-full">
            Commercial Premises Registry
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 mt-3 tracking-tight">
            Apply for Market Shop or Stall
        </h1>
        <p class="text-sm text-slate-600 mt-2 leading-relaxed">
            Direct online application for lock-up shops, stalls and commercial spaces across Yola South municipal markets. Instant verification, transparent 7-stage approval and live tracking without needing a user account.
        </p>
        <div class="mt-4 flex items-center justify-center gap-3">
            <a href="{{ route('public.shop-application.track') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-3.5 py-1.5 rounded-lg border border-emerald-200">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Already applied? Track with your reference ID
            </a>
        </div>
    </div>

    <!-- 7-Stage Workflow Explainer -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-emerald-600"></i>
                How Your Application Is Processed
            </h2>
            <span class="text-[11px] text-slate-400 font-mono">14-Day Service Charter</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
            <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-mono text-[10px] font-bold flex items-center justify-center mx-auto mb-1">1</span>
                <span class="text-xs font-bold text-slate-900 block">Apply</span>
                <span class="text-[10px] text-slate-400">Fill form & verify</span>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-mono text-[10px] font-bold flex items-center justify-center mx-auto mb-1">2</span>
                <span class="text-xs font-bold text-slate-900 block">Review</span>
                <span class="text-[10px] text-slate-400">ID & NIN check</span>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-mono text-[10px] font-bold flex items-center justify-center mx-auto mb-1">3</span>
                <span class="text-xs font-bold text-slate-900 block">Inspect</span>
                <span class="text-[10px] text-slate-400">Officer report</span>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-mono text-[10px] font-bold flex items-center justify-center mx-auto mb-1">4</span>
                <span class="text-xs font-bold text-slate-900 block">Approval</span>
                <span class="text-[10px] text-slate-400">Revenue Director</span>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-mono text-[10px] font-bold flex items-center justify-center mx-auto mb-1">5</span>
                <span class="text-xs font-bold text-slate-900 block">Allocation</span>
                <span class="text-[10px] text-slate-400">Unit assigned</span>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-mono text-[10px] font-bold flex items-center justify-center mx-auto mb-1">6</span>
                <span class="text-xs font-bold text-slate-900 block">Payment</span>
                <span class="text-[10px] text-slate-400">Fee & initial rent</span>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-mono text-[10px] font-bold flex items-center justify-center mx-auto mb-1">7</span>
                <span class="text-xs font-bold text-slate-900 block">Handover</span>
                <span class="text-[10px] text-slate-400">Certificate & card</span>
            </div>
        </div>
    </div>

    <!-- Application Container -->
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <!-- Step 1: Email Verification (Kanogis-style OTP) -->
        <div class="p-8 sm:p-10 border-b border-slate-100 bg-slate-50/50" x-show="!isEmailVerified">
            <div class="max-w-md mx-auto text-center">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800">Step 1: Verify Your Email</h2>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    No account registration required. We will send a secure 6-digit verification code to verify your identity and send tracking updates.
                </p>

                <!-- Email Input Form -->
                <div x-show="!otpSent" class="mt-6 space-y-4">
                    <div class="text-left">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Your Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" x-model="email" placeholder="e.g. yourname@example.com" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                    </div>
                    <button type="button" @click="sendOtp()" :disabled="sendingOtp" class="primary-btn w-full py-4 text-white font-bold text-xs uppercase tracking-widest shadow-md transition-all flex items-center justify-center gap-2">
                        <span x-show="sendingOtp" x-cloak class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Sending Code...</span>
                        </span>
                        <span x-show="!sendingOtp">Send Verification Code</span>
                    </button>
                </div>

                <!-- OTP Verification Form -->
                <div x-show="otpSent" x-cloak class="mt-6 space-y-4">
                    <div class="flex items-center justify-between text-xs text-slate-600 bg-white border border-slate-200 px-4 py-3 rounded-2xl text-left shadow-sm">
                        <span class="truncate">Code sent to: <strong class="text-slate-900 font-mono" x-text="email"></strong></span>
                        <button type="button" @click="otpSent = false; otpCode = ''; errorMessage = ''" class="text-xs font-semibold ml-2 shrink-0 hover:underline" style="color: var(--primary-color);">Change</button>
                    </div>

                    <div class="text-left">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                            Enter 6-Digit Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" maxlength="6" x-model="otpCode" placeholder="123456" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-lg font-mono font-bold text-center tracking-widest focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-2">
                        <button type="button" @click="sendOtp()" :disabled="sendingOtp" class="text-xs font-semibold hover:underline" style="color: var(--primary-color);">
                            <span x-show="!sendingOtp">Resend Code</span>
                            <span x-show="sendingOtp" x-cloak class="inline-flex items-center gap-1.5 text-slate-500">
                                <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Resending...</span>
                            </span>
                        </button>
                        <button type="button" @click="verifyOtp()" :disabled="verifyingOtp" class="primary-btn px-7 py-3 text-white font-bold text-xs uppercase tracking-widest shadow-md transition-all flex items-center justify-center gap-2">
                            <span x-show="verifyingOtp" x-cloak class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Verifying...</span>
                            </span>
                            <span x-show="!verifyingOtp">Verify Code</span>
                        </button>
                    </div>
                </div>

                <div x-show="errorMessage" x-cloak x-text="errorMessage" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 font-semibold text-center"></div>
            </div>
        </div>

        <!-- Step 2: Main Application Form (Unlocked once email is verified) -->
        <div class="p-8 sm:p-10" x-show="isEmailVerified">
            <div class="flex items-center justify-between pb-6 mb-8 border-b border-slate-100">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Commercial Space Application Form</h2>
                    <p class="text-xs text-slate-500 mt-1">Verified Email: <span class="font-bold font-mono text-slate-800" x-text="email"></span></p>
                </div>
                <span class="px-3.5 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> Verified
                </span>
            </div>

            <form action="{{ route('public.shop-application.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <input type="hidden" name="email" :value="email">

                <!-- Applicant Personal Details -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">1. Personal Identification</h2>
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Official applicant credentials & contact</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                Full Name (as on ID) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                                placeholder="Enter full legal name">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                Mobile Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="applicant_phone" value="{{ old('applicant_phone') }}" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                                placeholder="0803 000 0000">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                NIN / National Identity No.
                            </label>
                            <input type="text" name="applicant_nin_bvn" value="{{ old('applicant_nin_bvn') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                                placeholder="11-digit NIN Number">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                Residential Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="applicant_address" value="{{ old('applicant_address') }}" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none"
                                placeholder="Street address, ward, Yola South">
                        </div>
                    </div>
                </div>

                <!-- Business & Market Selection -->
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
                            <select name="market_id" x-model="selectedMarketId" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                                <option value="">Select Council Market</option>
                                @foreach($markets as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->name }} ({{ $m->ward_name }}) &mdash; {{ $m->shops->count() }} vacant units
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
                                <option value="">Select Trade Category</option>
                                <option value="Retail — Provisions & Groceries">Retail &mdash; Provisions & Groceries</option>
                                <option value="Textiles & Fashion Retail">Textiles & Fashion Retail</option>
                                <option value="Electronics & Appliance Repair">Electronics & Appliance Repair</option>
                                <option value="Building Materials & Hardware">Building Materials & Hardware</option>
                                <option value="Grain & Agricultural Produce">Grain & Agricultural Produce</option>
                                <option value="Pharmacy & Patent Medicine">Pharmacy & Patent Medicine</option>
                                <option value="Cosmetics & Personal Care">Cosmetics & Personal Care</option>
                                <option value="Other Commercial Trade">Other Commercial Trade</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                Preferred Unit Size
                            </label>
                            <select name="requested_size"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                                <option value="3.0 × 4.0 m">Standard Lock-up (3.0 × 4.0 m)</option>
                                <option value="2.5 × 3.0 m">Compact Stall (2.5 × 3.0 m)</option>
                                <option value="4.0 × 5.0 m">Double Commercial Unit (4.0 × 5.0 m)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Document Uploads -->
                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">3. Supporting Documents</h2>
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Identity document & passport photo</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border-2 border-dashed border-slate-200 hover:border-primary-500/50 bg-slate-50/50 rounded-2xl p-6 transition-all">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                Passport Photo
                            </label>
                            <input type="file" name="passport_photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-all">
                            <p class="mt-2 text-[11px] text-slate-400">JPEG, PNG format. Clear face portrait (Max 3MB).</p>
                        </div>

                        <div class="border-2 border-dashed border-slate-200 hover:border-primary-500/50 bg-slate-50/50 rounded-2xl p-6 transition-all">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                Valid ID Document
                            </label>
                            <input type="file" name="id_document" accept="image/*,application/pdf" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-all">
                            <p class="mt-2 text-[11px] text-slate-400">NIN slip, Voter's card or Driver's licence (PDF/Image, Max 3MB).</p>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="pt-6 border-t border-slate-100">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" required class="w-5 h-5 mt-0.5 rounded-lg border-slate-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0 transition-all cursor-pointer">
                        <span class="text-xs text-slate-600 leading-relaxed font-medium">
                            I declare that the information provided is accurate and true. I understand that allocation is subject to council verification, recommendation by the Market Officer, directorate approval, and prompt settlement of statutory fees.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end">
                    <button type="submit" class="primary-btn px-8 py-4 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all flex items-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Submit Shop Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function shopApplication() {
    return {
        email: '{{ $verifiedEmail ?? '' }}',
        isEmailVerified: {{ !empty($verifiedEmail) ? 'true' : 'false' }},
        otpSent: false,
        otpCode: '',
        selectedMarketId: '',
        sendingOtp: false,
        verifyingOtp: false,
        errorMessage: '',

        async sendOtp() {
            if (!this.email || !this.email.includes('@')) {
                this.errorMessage = 'Please provide a valid email address.';
                return;
            }
            this.sendingOtp = true;
            this.errorMessage = '';

            try {
                const res = await fetch('{{ route('public.shop-application.initiate-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: this.email })
                });
                const data = await res.json();
                this.sendingOtp = false;
                if (data.success) {
                    this.otpSent = true;
                } else {
                    this.errorMessage = data.message || 'Failed to send verification code.';
                }
            } catch (err) {
                this.sendingOtp = false;
                this.errorMessage = 'Network error. Please try again.';
            }
        },

        async verifyOtp() {
            if (!this.otpCode || this.otpCode.length !== 6) {
                this.errorMessage = 'Please enter the 6-digit code.';
                return;
            }
            this.verifyingOtp = true;
            this.errorMessage = '';

            try {
                const res = await fetch('{{ route('public.shop-application.verify-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: this.email, otp: this.otpCode })
                });
                const data = await res.json();
                this.verifyingOtp = false;
                if (data.success) {
                    this.isEmailVerified = true;
                } else {
                    this.errorMessage = data.message || 'Invalid code.';
                }
            } catch (err) {
                this.verifyingOtp = false;
                this.errorMessage = 'Verification error. Please try again.';
            }
        }
    };
}
</script>
@endsection
