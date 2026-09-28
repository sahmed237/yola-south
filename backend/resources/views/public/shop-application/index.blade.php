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
    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">
        <!-- Step 1: Email Verification (Kanogis-style OTP) -->
        <div class="p-8 border-b border-slate-100 bg-slate-50/50" x-show="!isEmailVerified">
            <div class="max-w-md mx-auto text-center">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Step 1: Verify Your Email</h2>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    No account registration required. We will send a secure 6-digit verification code to verify your identity and send tracking updates.
                </p>

                <!-- Email Input Form -->
                <div x-show="!otpSent" class="mt-5 space-y-3">
                    <div class="text-left">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Your Email Address *</label>
                        <input type="email" x-model="email" placeholder="e.g. yourname@example.com" class="w-full text-sm rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <button type="button" @click="sendOtp()" :disabled="loading" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading">Send Verification Code</span>
                        <span x-show="loading">Sending Code...</span>
                    </button>
                </div>

                <!-- OTP Verification Form -->
                <div x-show="otpSent" x-cloak class="mt-5 space-y-4">
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 text-left">
                        A 6-digit code has been sent to <b x-text="email"></b>.
                        <div x-show="devOtp" class="mt-1 font-mono text-[11px] font-bold text-emerald-900 bg-emerald-200/60 p-1 rounded inline-block">
                            Demo verification code: <span x-text="devOtp"></span>
                        </div>
                    </div>

                    <div class="text-left">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Enter 6-Digit Code *</label>
                        <input type="text" maxlength="6" x-model="otpCode" placeholder="123456" class="w-full text-center tracking-widest text-lg font-mono font-bold rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <button type="button" @click="sendOtp()" class="text-xs font-semibold text-emerald-700 hover:underline">
                            Resend Code
                        </button>
                        <button type="button" @click="verifyOtp()" :disabled="loading" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md">
                            <span x-show="!loading">Verify Code</span>
                            <span x-show="loading">Verifying...</span>
                        </button>
                    </div>
                </div>

                <div x-show="errorMessage" x-text="errorMessage" class="mt-3 text-xs text-red-600 font-semibold"></div>
            </div>
        </div>

        <!-- Step 2: Main Application Form (Unlocked once email is verified) -->
        <div class="p-8" x-show="isEmailVerified">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Application Form</h2>
                    <p class="text-xs text-slate-500">Verified Email: <span class="font-bold text-emerald-700" x-text="email"></span></p>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold rounded-full flex items-center gap-1">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Verified
                </span>
            </div>

            <form action="{{ route('public.shop-application.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="email" :value="email">

                <!-- Applicant Personal Details -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">1. Personal Identification</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name (as on ID) *</label>
                            <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" required placeholder="e.g. Ibrahim Abubakar" class="w-full text-xs rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Phone Number *</label>
                            <input type="tel" name="applicant_phone" value="{{ old('applicant_phone') }}" required placeholder="e.g. 0803 123 4567" class="w-full text-xs font-mono rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">NIN / National Identity No.</label>
                            <input type="text" name="applicant_nin_bvn" value="{{ old('applicant_nin_bvn') }}" placeholder="11-digit NIN" class="w-full text-xs font-mono rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Residential Address *</label>
                            <input type="text" name="applicant_address" value="{{ old('applicant_address') }}" required placeholder="Street address, ward, Yola South" class="w-full text-xs rounded-xl border-slate-200">
                        </div>
                    </div>
                </div>

                <!-- Business & Market Selection -->
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">2. Trade & Market Preference</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Market *</label>
                            <select name="market_id" x-model="selectedMarketId" required class="w-full text-xs rounded-xl border-slate-200">
                                <option value="">-- Choose Council Market --</option>
                                @foreach($markets as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->name }} ({{ $m->ward_name }}) &mdash; {{ $m->shops->count() }} vacant units
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Line of Business / Trade *</label>
                            <select name="trade_type" required class="w-full text-xs rounded-xl border-slate-200">
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
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Preferred Unit Size</label>
                            <select name="requested_size" class="w-full text-xs rounded-xl border-slate-200">
                                <option value="3.0 × 4.0 m">Standard Lock-up (3.0 × 4.0 m)</option>
                                <option value="2.5 × 3.0 m">Compact Stall (2.5 × 3.0 m)</option>
                                <option value="4.0 × 5.0 m">Double Commercial Unit (4.0 × 5.0 m)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Document Uploads -->
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">3. Supporting Documents</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="border border-dashed border-slate-300 rounded-2xl p-4 bg-slate-50/50">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Passport Photo</label>
                            <input type="file" name="passport_photo" accept="image/*" class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <span class="text-[10px] text-slate-400 block mt-1">JPEG/PNG format, clear face portrait (max 3MB)</span>
                        </div>
                        <div class="border border-dashed border-slate-300 rounded-2xl p-4 bg-slate-50/50">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Valid ID Document</label>
                            <input type="file" name="id_document" accept="image/*,application/pdf" class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <span class="text-[10px] text-slate-400 block mt-1">NIN slip, Voters card or Driving licence (PDF/Image)</span>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="pt-4 border-t border-slate-100">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" required class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-600 leading-relaxed">
                            I declare that the information provided is accurate and true. I understand that allocation is subject to council verification, recommendation by the Market Officer, directorate approval, and prompt settlement of statutory fees.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end">
                    <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg transition-all flex items-center gap-2">
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
        devOtp: null,
        selectedMarketId: '',
        loading: false,
        errorMessage: '',

        async sendOtp() {
            if (!this.email || !this.email.includes('@')) {
                this.errorMessage = 'Please provide a valid email address.';
                return;
            }
            this.loading = true;
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
                this.loading = false;
                if (data.success) {
                    this.otpSent = true;
                    this.devOtp = data.otp;
                } else {
                    this.errorMessage = data.message || 'Failed to send verification code.';
                }
            } catch (err) {
                this.loading = false;
                this.errorMessage = 'Network error. Please try again.';
            }
        },

        async verifyOtp() {
            if (!this.otpCode || this.otpCode.length !== 6) {
                this.errorMessage = 'Please enter the 6-digit code.';
                return;
            }
            this.loading = true;
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
                this.loading = false;
                if (data.success) {
                    this.isEmailVerified = true;
                } else {
                    this.errorMessage = data.message || 'Invalid code.';
                }
            } catch (err) {
                this.loading = false;
                this.errorMessage = 'Verification error. Please try again.';
            }
        }
    };
}
</script>
@endsection
