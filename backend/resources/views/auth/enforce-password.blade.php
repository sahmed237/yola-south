@extends('layouts.auth')

@section('title', 'Change Password Required')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-white rounded-[2.5rem] shadow-2xl shadow-primary-500/10 border border-primary-50/50 p-12 relative overflow-hidden">
        
        <!-- Background Accents -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-amber-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative text-center mb-10">
            <div class="w-20 h-20 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-100 transform rotate-3">
                <i data-lucide="key-round" class="w-10 h-10 text-amber-600"></i>
            </div>
            <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">Password Change Required</h2>
            <p class="text-slate-500 text-sm leading-relaxed">
                For security reasons, you must update your temporary password before accessing your account dashboard.
            </p>
        </div>

        @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 text-red-600 rounded-2xl text-xs font-bold border border-red-100 flex items-center">
                <i data-lucide="alert-circle" class="w-4 h-4 mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('security.password.update') }}" method="POST" 
              x-data="{ 
                password: '',
                minLen: {{ \App\Models\Setting::get('password_min_length', 6) }},
                get hasMinLen() { return this.password.length >= this.minLen },
                get hasUpper() { return /[A-Z]/.test(this.password) },
                get hasLower() { return /[a-z]/.test(this.password) },
                get hasNumber() { return /[0-9]/.test(this.password) },
                get hasSpecial() { return /[!@#$%^&*(),.?\':{}|<>]/.test(this.password) },
                get isValid() {
                  return this.hasMinLen
                    @if(\App\Models\Setting::get('password_require_uppercase') == '1')
                    && this.hasUpper
                    @endif
                    @if(\App\Models\Setting::get('password_require_number') == '1')
                    && this.hasNumber
                    @endif
                    @if(\App\Models\Setting::get('password_require_special') == '1')
                    && this.hasSpecial
                    @endif
                  ;
                }
              }">
            @csrf
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Current Password</label>
                    <x-password-input name="current_password" required placeholder="Enter current password" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">New Password</label>
                        <x-password-input name="password" required x-model="password" placeholder="New Password" />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Confirm New Password</label>
                        <x-password-input name="password_confirmation" required placeholder="Confirm Password" />
                    </div>
                </div>

                <!-- Requirement Checker -->
                <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Security Policy Requirements</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-center space-x-2 text-xs font-bold transition-colors duration-200" :class="hasMinLen ? 'text-emerald-600' : 'text-slate-400'">
                            <span class="w-4 h-4 flex items-center justify-center relative">
                                <i data-lucide="check-circle-2" class="w-4 h-4 absolute" x-show="hasMinLen"></i>
                                <i data-lucide="circle" class="w-4 h-4 absolute" x-show="!hasMinLen"></i>
                            </span>
                            <span>Min <span x-text="minLen"></span> characters</span>
                        </div>
                        @if(\App\Models\Setting::get('password_require_uppercase') == '1')
                        <div class="flex items-center space-x-2 text-xs font-bold transition-colors duration-200" :class="hasUpper ? 'text-emerald-600' : 'text-slate-400'">
                            <span class="w-4 h-4 flex items-center justify-center relative">
                                <i data-lucide="check-circle-2" class="w-4 h-4 absolute" x-show="hasUpper"></i>
                                <i data-lucide="circle" class="w-4 h-4 absolute" x-show="!hasUpper"></i>
                            </span>
                            <span>Uppercase Letter</span>
                        </div>
                        @endif
                        @if(\App\Models\Setting::get('password_require_number') == '1')
                        <div class="flex items-center space-x-2 text-xs font-bold transition-colors duration-200" :class="hasNumber ? 'text-emerald-600' : 'text-slate-400'">
                            <span class="w-4 h-4 flex items-center justify-center relative">
                                <i data-lucide="check-circle-2" class="w-4 h-4 absolute" x-show="hasNumber"></i>
                                <i data-lucide="circle" class="w-4 h-4 absolute" x-show="!hasNumber"></i>
                            </span>
                            <span>Numeric Character</span>
                        </div>
                        @endif
                        @if(\App\Models\Setting::get('password_require_special') == '1')
                        <div class="flex items-center space-x-2 text-xs font-bold transition-colors duration-200" :class="hasSpecial ? 'text-emerald-600' : 'text-slate-400'">
                            <span class="w-4 h-4 flex items-center justify-center relative">
                                <i data-lucide="check-circle-2" class="w-4 h-4 absolute" x-show="hasSpecial"></i>
                                <i data-lucide="circle" class="w-4 h-4 absolute" x-show="!hasSpecial"></i>
                            </span>
                            <span>Special Character</span>
                        </div>
                        @endif
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-5 text-white rounded-2xl font-bold text-sm shadow-xl transition-all"
                        :class="isValid ? 'bg-primary-600 hover:bg-primary-700 shadow-primary-500/20 hover:-translate-y-1' : 'bg-slate-300 cursor-not-allowed shadow-none'"
                        :disabled="!isValid">
                    Update Password & Continue
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-50 text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">
                    Sign out and do this later
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
