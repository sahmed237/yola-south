@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-white rounded-[2.5rem] shadow-2xl shadow-primary-100/50 border border-primary-50/50 p-12 relative overflow-hidden">
        
        <!-- Background Accents -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-primary-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative text-center mb-10">
            <div class="w-20 h-20 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary-100/50 transform -rotate-3">
                <i data-lucide="shield-check" class="w-10 h-10 text-primary-600"></i>
            </div>
            <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">Set New Password</h2>
            <p class="text-slate-500 text-sm leading-relaxed">
                Choose a strong password to secure your account.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-8 p-4 bg-red-50 text-red-600 rounded-2xl text-xs font-bold border border-red-100 flex items-start">
                <i data-lucide="alert-circle" class="w-4 h-4 mr-2 mt-0.5"></i>
                <ul class="flex-1 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" 
              x-data="{ 
                password: '',
                minLen: {{ \App\Models\Setting::get('password_min_length', 6) }},
                get hasMinLen() { return this.password.length >= this.minLen },
                get hasUpper() { return /[A-Z]/.test(this.password) },
                get hasLower() { return /[a-z]/.test(this.password) },
                get hasNumber() { return /[0-9]/.test(this.password) },
                get hasSpecial() { return /[!@#$%^&*(),.?\':{}|<>]/.test(this.password) }
              }">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Email Address</label>
                    <div class="relative group">
                        <i data-lucide="mail" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors"></i>
                        <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-500 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">New Password</label>
                        <x-password-input name="password" required x-model="password" placeholder="New Password" />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Confirm Password</label>
                        <x-password-input name="password_confirmation" required placeholder="Confirm Password" />
                    </div>
                </div>

                <!-- Requirement Checker -->
                <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Security Policy Requirements</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-center space-x-2 text-xs font-bold" :class="hasMinLen ? 'text-emerald-600' : 'text-slate-400'">
                            <i :data-lucide="hasMinLen ? 'check-circle-2' : 'circle'" class="w-4 h-4"></i>
                            <span>Min <span x-text="minLen"></span> characters</span>
                        </div>
                        @if(\App\Models\Setting::get('password_require_uppercase') == '1')
                        <div class="flex items-center space-x-2 text-xs font-bold" :class="hasUpper ? 'text-emerald-600' : 'text-slate-400'">
                            <i :data-lucide="hasUpper ? 'check-circle-2' : 'circle'" class="w-4 h-4"></i>
                            <span>Uppercase Letter</span>
                        </div>
                        @endif
                        @if(\App\Models\Setting::get('password_require_number') == '1')
                        <div class="flex items-center space-x-2 text-xs font-bold" :class="hasNumber ? 'text-emerald-600' : 'text-slate-400'">
                            <i :data-lucide="hasNumber ? 'check-circle-2' : 'circle'" class="w-4 h-4"></i>
                            <span>Numeric Character</span>
                        </div>
                        @endif
                        @if(\App\Models\Setting::get('password_require_special') == '1')
                        <div class="flex items-center space-x-2 text-xs font-bold" :class="hasSpecial ? 'text-emerald-600' : 'text-slate-400'">
                            <i :data-lucide="hasSpecial ? 'check-circle-2' : 'circle'" class="w-4 h-4"></i>
                            <span>Special Character</span>
                        </div>
                        @endif
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-500/20 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                    Reset Password & Sign In
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
