@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Account Settings</h1>
    <p class="text-slate-500 text-sm">Manage your profile and security preferences</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profile Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="h-32 bg-gradient-to-br from-primary-500 to-indigo-600"></div>
            <div class="px-6 pb-6 -mt-12 text-center">
                <div class="inline-block p-1 bg-white rounded-3xl mb-4 shadow-sm border border-slate-100">
                    <div class="w-24 h-24 rounded-2xl bg-slate-100 flex items-center justify-center font-bold text-3xl text-primary-600 shadow-inner">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                </div>
                <h3 class="text-xl font-bold text-slate-800">{{ $user->name }}</h3>
                <p class="text-sm text-slate-400 mb-6">{{ $user->email }}</p>
                
                <div class="space-y-3 text-left">
                    <div class="p-4 bg-slate-50 rounded-2xl flex items-center">
                        <i data-lucide="shield" class="w-5 h-5 text-indigo-500 mr-3"></i>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Role</p>
                            <p class="text-sm font-bold text-slate-700 uppercase">{{ $user->getRoleNames()->first() ?? 'User' }}</p>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl flex items-center">
                        <i data-lucide="clock" class="w-5 h-5 text-emerald-500 mr-3"></i>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Account Created</p>
                            <p class="text-sm font-bold text-slate-700">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security & Info Forms -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information (Read-only) -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mr-4">
                        <i data-lucide="user" class="w-6 h-6 text-indigo-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Account Information</h2>
                        <p class="text-xs text-slate-400">These details are managed by your administrator.</p>
                    </div>
                </div>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-1">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Full Name</label>
                        <div class="relative group">
                            <i data-lucide="user" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text" value="{{ $user->name }}" readonly
                                   class="w-full pl-12 pr-4 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl text-sm text-slate-500 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Email Address</label>
                        <div class="relative group">
                            <i data-lucide="mail" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="email" value="{{ $user->email }}" readonly
                                   class="w-full pl-12 pr-4 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl text-sm text-slate-500 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Phone Number</label>
                        <div class="relative group">
                            <i data-lucide="phone" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text" value="{{ $user->phone ?? 'Not provided' }}" readonly
                                   class="w-full pl-12 pr-4 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl text-sm text-slate-500 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Physical Address</label>
                        <div class="relative group">
                            <i data-lucide="map-pin" class="w-5 h-5 absolute left-4 top-4 text-slate-300"></i>
                            <div class="w-full pl-12 pr-4 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl text-sm text-slate-500 min-h-[100px]">
                                {{ $user->address ?? 'No address provided.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mr-4">
                        <i data-lucide="shield-check" class="w-6 h-6 text-indigo-600"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-800">Two-Factor Authentication</h2>
                            @if($user->hasTwoFactorEnabled())
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase rounded-lg tracking-wider">Active</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400">Add an additional layer of security to your account using an authenticator app.</p>
                    </div>
                </div>
                <a href="{{ route('admin.profile.2fa') }}" class="px-6 py-3 bg-slate-800 text-white rounded-2xl font-bold text-sm shadow-xl shadow-slate-200 hover:bg-slate-900 hover:-translate-y-1 transition-all flex items-center">
                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                    Manage 2FA
                </a>
            </div>
            <div class="p-8 bg-slate-50/50">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Two-factor authentication (2FA) is a core security requirement (ISO 27001). When enabled, you will be prompted for a secure, random token from your mobile device during login.
                        </p>
                    </div>
                    @if(!$user->hasTwoFactorEnabled())
                        <div class="flex items-center text-amber-600 bg-amber-50 px-4 py-2 rounded-xl text-xs font-bold border border-amber-100">
                            <i data-lucide="alert-triangle" class="w-4 h-4 mr-2"></i>
                            Not yet enabled
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Form -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center mr-4">
                        <i data-lucide="key-round" class="w-6 h-6 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Change Password</h2>
                        <p class="text-xs text-slate-400">Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('admin.profile.password') }}" method="POST" class="p-8" 
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
                @method('PUT')
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Current Password</label>
                        <x-password-input name="current_password" required placeholder="Current Password" />
                        @error('current_password') <p class="mt-2 text-xs text-red-500 font-bold px-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">New Password</label>
                            <x-password-input name="password" required x-model="password" icon="shield-check" placeholder="New Password" />
                            @error('password') <p class="mt-2 text-xs text-red-500 font-bold px-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Confirm New Password</label>
                            <x-password-input name="password_confirmation" required icon="check-circle" placeholder="Confirm New Password" />
                        </div>
                    </div>

                    <!-- Password Requirements Real-time Checker -->
                    <div class="bg-slate-50 rounded-[2rem] p-6 border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Password Requirements</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-8">
                            <div class="flex items-center space-x-3 transition-all duration-300" :class="hasMinLen ? 'text-emerald-600' : 'text-slate-400'">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center border-2 transition-colors" :class="hasMinLen ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200'">
                                    <i data-lucide="check" class="w-3 h-3 text-white" x-show="hasMinLen"></i>
                                    <i data-lucide="x" class="w-3 h-3 text-slate-300" x-show="!hasMinLen"></i>
                                </div>
                                <span class="text-xs font-bold">At least <span x-text="minLen"></span> characters</span>
                            </div>

                            @if(\App\Models\Setting::get('password_require_uppercase') == '1')
                                <div class="flex items-center space-x-3 transition-all duration-300" :class="hasUpper ? 'text-emerald-600' : 'text-slate-400'">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center border-2 transition-colors" :class="hasUpper ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200'">
                                        <i data-lucide="check" class="w-3 h-3 text-white" x-show="hasUpper"></i>
                                        <i data-lucide="x" class="w-3 h-3 text-slate-300" x-show="!hasUpper"></i>
                                    </div>
                                    <span class="text-xs font-bold">Contains uppercase letter</span>
                                </div>
                            @endif

                            @if(\App\Models\Setting::get('password_require_lowercase') == '1')
                                <div class="flex items-center space-x-3 transition-all duration-300" :class="hasLower ? 'text-emerald-600' : 'text-slate-400'">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center border-2 transition-colors" :class="hasLower ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200'">
                                        <i data-lucide="check" class="w-3 h-3 text-white" x-show="hasLower"></i>
                                        <i data-lucide="x" class="w-3 h-3 text-slate-300" x-show="!hasLower"></i>
                                    </div>
                                    <span class="text-xs font-bold">Contains lowercase letter</span>
                                </div>
                            @endif

                            @if(\App\Models\Setting::get('password_require_number') == '1')
                                <div class="flex items-center space-x-3 transition-all duration-300" :class="hasNumber ? 'text-emerald-600' : 'text-slate-400'">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center border-2 transition-colors" :class="hasNumber ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200'">
                                        <i data-lucide="check" class="w-3 h-3 text-white" x-show="hasNumber"></i>
                                        <i data-lucide="x" class="w-3 h-3 text-slate-300" x-show="!hasNumber"></i>
                                    </div>
                                    <span class="text-xs font-bold">Contains numeric character</span>
                                </div>
                            @endif

                            @if(\App\Models\Setting::get('password_require_special') == '1')
                                <div class="flex items-center space-x-3 transition-all duration-300" :class="hasSpecial ? 'text-emerald-600' : 'text-slate-400'">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center border-2 transition-colors" :class="hasSpecial ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200'">
                                        <i data-lucide="check" class="w-3 h-3 text-white" x-show="hasSpecial"></i>
                                        <i data-lucide="x" class="w-3 h-3 text-slate-300" x-show="!hasSpecial"></i>
                                    </div>
                                    <span class="text-xs font-bold">Contains special character</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full md:w-auto px-8 py-4 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-200 hover:bg-primary-700 hover:-translate-y-1 transition-all flex items-center justify-center">
                            <i data-lucide="key" class="w-4 h-4 mr-2"></i>
                            Update Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
