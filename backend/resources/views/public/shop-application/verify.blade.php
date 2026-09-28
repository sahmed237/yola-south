@extends('layouts.public')

@section('title', 'Security Verification - ' . $allocation->application_no)

@section('content')
<div class="max-w-lg mx-auto px-4 py-16" x-data="trackingVerification()">
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10 text-center relative overflow-hidden">
        <!-- Top decorative ribbon -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-700"></div>

        <!-- Shield Icon -->
        <div class="w-16 h-16 bg-emerald-50 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-emerald-100/60">
            <i data-lucide="shield-check" class="w-8 h-8"></i>
        </div>

        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Security Verification</h1>
        <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-mono font-bold">
            <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-400"></i>
            {{ $allocation->application_no }}
        </div>
        <p class="text-xs text-slate-500 mt-3 max-w-sm mx-auto leading-relaxed">
            For security and privacy, you must verify ownership before accessing this application. We will send a 6-digit verification code to the registered email:
        </p>

        <!-- Masked Email Card -->
        <div class="my-4 p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-center gap-2">
            <i data-lucide="mail" class="w-4 h-4 text-emerald-700 shrink-0"></i>
            <span class="font-mono font-bold text-slate-900 text-sm tracking-wide">{{ $maskedEmail }}</span>
        </div>

        <!-- Alert messages -->
        <div x-show="errorMessage" x-cloak class="mb-4 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-semibold text-left flex items-start gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
            <span x-text="errorMessage"></span>
        </div>

        <div x-show="successMessage" x-cloak class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold text-left flex items-start gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
            <span x-text="successMessage"></span>
        </div>

        @if(session('error'))
        <div class="mb-4 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-semibold text-left">
            {{ session('error') }}
        </div>
        @endif

        <!-- OTP Interaction Workflow -->
        <div class="space-y-4 text-left">
            <!-- Step 1: Send OTP -->
            <div>
                <button type="button" 
                        @click="sendOtp()" 
                        :disabled="sendingOtp || countdown > 0"
                        class="w-full py-3.5 px-4 rounded-xl border font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-sm"
                        :class="otpSent ? 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' : 'bg-emerald-700 hover:bg-emerald-800 text-white border-transparent shadow-emerald-700/20'">
                    
                    <template x-if="sendingOtp">
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Sending Code...
                        </span>
                    </template>

                    <template x-if="!sendingOtp && countdown > 0">
                        <span class="inline-flex items-center gap-1.5 text-slate-500 font-mono">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            Resend code in <span x-text="countdown"></span>s
                        </span>
                    </template>

                    <template x-if="!sendingOtp && countdown <= 0">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span x-text="otpSent ? 'Resend Verification Code' : 'Send Verification Code'"></span>
                        </span>
                    </template>
                </button>
            </div>

            <!-- Step 2: OTP Input & Verify (Visible once sent) -->
            <form @submit.prevent="verifyOtp()" x-show="otpSent" x-transition class="space-y-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">
                        Enter 6-Digit Verification Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           x-model="otpCode"
                           maxlength="6"
                           required
                           placeholder="&bull; &bull; &bull; &bull; &bull; &bull;"
                           autocomplete="one-time-code"
                           class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xl font-mono font-bold text-center tracking-[0.4em] text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-600 transition-all outline-none">
                    <p class="text-[11px] text-slate-400 mt-1.5 text-center">
                        Check your inbox or spam folder for an email from Yola South Local Government.
                    </p>
                </div>

                <button type="submit" 
                        :disabled="verifying || otpCode.length < 6"
                        class="w-full py-3.5 px-4 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-emerald-700/20 transition-all flex items-center justify-center gap-2">
                    <template x-if="verifying">
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Verifying...
                        </span>
                    </template>
                    <template x-if="!verifying">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="unlock" class="w-4 h-4"></i>
                            Verify & Access Application
                        </span>
                    </template>
                </button>
            </form>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <a href="{{ route('public.shop-application.track') }}" class="font-semibold hover:text-slate-900 flex items-center gap-1">
                &larr; Different application
            </a>
            <a href="{{ route('public.shop-application.index') }}" class="font-semibold text-emerald-700 hover:underline">
                New application
            </a>
        </div>
    </div>
</div>

<script>
function trackingVerification() {
    return {
        otpSent: false,
        sendingOtp: false,
        verifying: false,
        otpCode: '',
        countdown: 0,
        timer: null,
        errorMessage: '',
        successMessage: '',
        redirectUrl: @json($redirect),

        sendOtp() {
            if (this.sendingOtp || this.countdown > 0) return;
            this.sendingOtp = true;
            this.errorMessage = '';
            this.successMessage = '';

            fetch("{{ route('public.shop-application.track-send-otp', $allocation->application_no) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json",
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                this.sendingOtp = false;
                if (data.success) {
                    this.otpSent = true;
                    this.successMessage = data.message || "Verification code sent to your email!";
                    this.startCountdown(60);
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                } else {
                    this.errorMessage = data.message || "Failed to send verification code. Please try again.";
                }
            })
            .catch(err => {
                this.sendingOtp = false;
                this.errorMessage = "An error occurred while sending the code. Please check your connection and retry.";
            });
        },

        verifyOtp() {
            if (this.verifying || this.otpCode.length < 6) return;
            this.verifying = true;
            this.errorMessage = '';
            this.successMessage = '';

            fetch("{{ route('public.shop-application.track-verify-otp', $allocation->application_no) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json",
                },
                body: JSON.stringify({
                    otp: this.otpCode,
                    redirect: this.redirectUrl
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                this.verifying = false;
                if (status === 200 && body.success) {
                    this.successMessage = "Verification successful! Accessing application...";
                    setTimeout(() => {
                        window.location.href = body.redirect || this.redirectUrl;
                    }, 500);
                } else {
                    this.errorMessage = body.message || "The verification code entered is invalid or has expired.";
                }
            })
            .catch(err => {
                this.verifying = false;
                this.errorMessage = "Verification failed. Please try again.";
            });
        },

        startCountdown(seconds) {
            this.countdown = seconds;
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            }, 1000);
        }
    };
}
</script>
@endsection
