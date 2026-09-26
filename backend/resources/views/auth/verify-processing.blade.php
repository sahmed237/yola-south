@extends('layouts.auth')

@section('title', 'Verifying Your Email')

@section('content')
<div x-data="verifyHandler()" class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 border border-indigo-50/50 p-12 text-center relative overflow-hidden">
        
        <!-- Background Accents -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>

        <!-- Processing State -->
        <div x-show="state === 'processing'" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <div class="relative w-24 h-24 mx-auto mb-8">
                <div class="absolute inset-0 border-4 border-indigo-100 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center text-indigo-600">
                    <i data-lucide="shield-check" class="w-10 h-10"></i>
                </div>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-4 tracking-tight">Verifying Your Account</h2>
            <p class="text-slate-500 text-sm leading-relaxed mb-0">
                Please hold on while we cryptographically verify your email address. This only takes a moment...
            </p>
        </div>

        <!-- Success State -->
        <div x-show="state === 'success'" style="display: none;"
             x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-700"
             x-transition:enter-start="opacity-0 transform scale-50"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <div class="w-24 h-24 bg-emerald-500 rounded-full mx-auto mb-8 flex items-center justify-center shadow-lg shadow-emerald-200">
                <i data-lucide="check" class="w-12 h-12 text-white animate-bounce"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-4 tracking-tight">Verification Successful!</h2>
            <p class="text-slate-500 text-sm leading-relaxed mb-8">
                Your account is now fully active. We've sent a confirmation to your email. Redirecting you to login...
            </p>

            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="bg-emerald-500 h-full transition-all duration-[3000ms] ease-linear" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>

        <!-- Error State -->
        <div x-show="state === 'error'" style="display: none;"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <div class="w-24 h-24 bg-red-500 rounded-full mx-auto mb-8 flex items-center justify-center shadow-lg shadow-red-200">
                <i data-lucide="x" class="w-12 h-12 text-white"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-4 tracking-tight">Verification Failed</h2>
            <p x-text="errorMessage" class="text-slate-500 text-sm leading-relaxed mb-8"></p>

            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-slate-800 text-white rounded-2xl font-bold text-sm hover:bg-slate-900 transition-all">
                Return to Login
            </a>
        </div>

    </div>
</div>

<script>
function verifyHandler() {
    return {
        state: 'processing',
        progress: 0,
        errorMessage: '',
        
        init() {
            // Start verification after a slight delay for aesthetic effect
            setTimeout(() => this.performVerification(), 2000);
            
            // Re-initialize Lucide icons
            lucide.createIcons();
        },

        async performVerification() {
            try {
                const response = await fetch('{!! $verifyUrl !!}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.state = 'success';
                    setTimeout(() => {
                        this.progress = 100;
                        setTimeout(() => window.location.href = '{{ route('login') }}', 3000);
                    }, 100);
                } else {
                    this.state = 'error';
                    this.errorMessage = data.message || 'Something went wrong during verification.';
                }
            } catch (error) {
                this.state = 'error';
                this.errorMessage = 'Network error. Please try again later.';
            }
        }
    }
}
</script>
@endsection
