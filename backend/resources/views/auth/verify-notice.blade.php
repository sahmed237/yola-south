@extends('layouts.auth')

@section('title', 'Verification Required')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 border border-indigo-50/50 p-12 text-center relative overflow-hidden">
        
        <!-- Background Accents -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative w-24 h-24 mx-auto mb-8 bg-amber-50 rounded-full flex items-center justify-center">
            <i data-lucide="mail-warning" class="w-12 h-12 text-amber-500 animate-pulse"></i>
        </div>

        <h2 class="text-2xl font-black text-slate-800 mb-4 tracking-tight">Verification Required</h2>
        <p class="text-slate-500 text-sm leading-relaxed mb-8">
            Your email address <strong>{{ $email }}</strong> has not been verified yet. Please check your inbox for the activation link.
        </p>

        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-50 text-emerald-600 rounded-2xl text-xs font-bold border border-emerald-100 flex items-center">
                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 text-red-600 rounded-2xl text-xs font-bold border border-red-100 flex items-center">
                <i data-lucide="alert-circle" class="w-4 h-4 mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-4">
            @if(($system_settings['user_can_request_new_email_verification'] ?? '1') == '1')
                <form action="{{ route('verification.resend') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="w-full px-8 py-4 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-200 hover:bg-primary-700 hover:-translate-y-1 transition-all flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                        Resend Verification Link
                    </button>
                </form>
            @else
                <div class="p-6 bg-slate-50 border border-slate-200 rounded-[2rem] text-center">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-slate-400"></i>
                    </div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Need a New Link?</p>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        If you did not receive the verification email, please contact your system administrator to resend the activation link or manually verify your account.
                    </p>
                </div>
            @endif

            <a href="{{ route('login') }}" class="w-full px-8 py-4 bg-slate-50 text-slate-600 rounded-2xl font-bold text-sm border border-slate-100 hover:bg-slate-100 transition-all flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Login
            </a>
        </div>

        <p class="mt-8 text-[11px] text-slate-400 font-medium">
            Having trouble? Please contact our <a href="#" class="text-indigo-600 underline">support team</a>.
        </p>
    </div>
</div>
@endsection
