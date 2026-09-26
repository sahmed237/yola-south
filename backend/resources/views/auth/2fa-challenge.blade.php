@extends('layouts.auth')

@section('title', 'Two-Factor Challenge')

@section('content')
    <div x-data="{ mode: 'code' }" class="min-h-screen flex items-center justify-center p-4">
        <div
            class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl shadow-primary-100/50 border border-primary-50/50 p-12 text-center relative overflow-hidden">

            <!-- Background Accents -->
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-primary-50 rounded-full blur-3xl opacity-50">
            </div>

            <div
                class="relative w-20 h-20 mx-auto mb-8 bg-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-200 transform -rotate-3">
                <i data-lucide="shield-check" class="w-10 h-10 text-white"></i>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-2 tracking-tight">Security Check</h2>
            <p class="text-slate-500 text-sm leading-relaxed mb-8" x-show="mode === 'code'">
                Enter the 6-digit verification code from your authenticator app to continue.
            </p>
            <p class="text-slate-500 text-sm leading-relaxed mb-8" x-show="mode === 'recovery'" style="display: none;">
                Enter one of your emergency recovery codes to access your account.
            </p>

            @if(session('error'))
                <div
                    class="mb-8 p-4 bg-red-50 text-red-600 rounded-2xl text-xs font-bold border border-red-100 flex items-center">
                    <i data-lucide="alert-circle" class="w-4 h-4 mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- 2FA Code Form -->
            <div x-show="mode === 'code'">
                <form action="{{ route('2fa.verify') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <input type="text" name="code" maxlength="6" autofocus placeholder="000000"
                            class="w-full px-5 py-5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-mono text-3xl tracking-[0.5em] text-center"
                            required>
                    </div>
                    <button type="submit"
                        class="w-full py-5 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-200 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                        Verify & Access
                    </button>
                </form>
                <button @click="mode = 'recovery'"
                    class="mt-6 text-xs font-bold text-slate-400 hover:text-primary-600 transition-colors uppercase tracking-widest">
                    Lost your device? Use recovery code
                </button>
            </div>

            <!-- Recovery Code Form -->
            <div x-show="mode === 'recovery'" style="display: none;">
                <form action="{{ route('2fa.recovery') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <input type="text" name="recovery_code" placeholder="00000000"
                            class="w-full px-5 py-5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-mono text-xl text-center"
                            required>
                    </div>
                    <button type="submit"
                        class="w-full py-5 bg-slate-800 text-white rounded-2xl font-bold text-sm shadow-xl shadow-slate-200 hover:bg-slate-900 hover:-translate-y-1 transition-all">
                        Verify Recovery Code
                    </button>
                </form>
                <button @click="mode = 'code'"
                    class="mt-6 text-xs font-bold text-slate-400 hover:text-primary-600 transition-colors uppercase tracking-widest">
                    Back to Authenticator App
                </button>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-50">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mb-4">ISO 27001 Protected Session
                </p>
                <a href="{{ route('login') }}"
                    class="text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    Cancel & Sign Out
                </a>
            </div>
        </div>
    </div>
@endsection