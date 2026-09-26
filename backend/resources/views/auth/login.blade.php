@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <div class="min-h-screen flex items-center justify-center p-4">
        <div
            class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 border border-indigo-50/50 p-12 relative overflow-hidden">

            <!-- Background Accents -->
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>

            <div class="relative text-center mb-10">
                @if(!empty($system_settings['platform_logo']))
                    <img src="{{ $system_settings['platform_logo'] }}" alt="Logo" class="h-12 w-auto mx-auto mb-6">
                @else
                    <div
                        class="w-16 h-16 bg-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary-500/20 transform -rotate-3">
                        <span
                            class="text-white font-black text-2xl">{{ substr($system_settings['platform_name'] ?? 'APP', 0, 2) }}</span>
                    </div>
                @endif
                <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">Welcome Back</h2>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Sign in to your {{ $system_settings['platform_name'] ?? 'APP' }} admin account.
                </p>
            </div>

            @if($errors->any())
                <div
                    class="mb-8 p-4 bg-red-50 text-red-600 rounded-2xl text-xs font-bold border border-red-100 flex items-start">
                    <i data-lucide="alert-circle" class="w-4 h-4 mr-2 mt-0.5"></i>
                    <ul class="flex-1 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div
                    class="mb-8 p-4 bg-emerald-50 text-emerald-600 rounded-2xl text-xs font-bold border border-emerald-100 flex items-center">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div
                    class="mb-8 p-4 bg-blue-50 text-blue-600 rounded-2xl text-xs font-bold border border-blue-100 flex items-center">
                    <i data-lucide="info" class="w-4 h-4 mr-2"></i>
                    {{ session('info') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Email
                        Address</label>
                    <div class="relative group">
                        <i data-lucide="mail"
                            class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                            placeholder="admin@example.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2 px-1">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Password</label>
                        @if(($system_settings['user_can_forget_password'] ?? '1') == '1')
                            <a href="{{ route('password.request') }}"
                                class="text-[10px] font-bold text-primary-600 hover:underline transition-all">Forgot
                                password?</a>
                        @else
                            <span class="text-[10px] font-bold text-slate-400 cursor-help"
                                title="Password reset via self-service is disabled. Please contact your system administrator.">Forgot
                                password? Contact Administrator</span>
                        @endif
                    </div>
                    <x-password-input name="password" required placeholder="••••••••" />
                </div>

                <div class="flex items-center px-1">
                    <input type="checkbox" id="remember" name="remember"
                        class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                    <label for="remember"
                        class="ml-2 block text-xs font-bold text-slate-500 uppercase tracking-wider cursor-pointer">Remember
                        me</label>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-500/20 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                    Sign In to Platform
                </button>
            </form>

            <div class="mt-10 pt-8 border-t border-slate-50 text-center">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mb-2">Secure Portal</p>
                <p class="text-[10px] text-slate-400 leading-relaxed max-w-[200px] mx-auto font-medium">
                    ISO 27001 Certified Environment
                </p>
            </div>
        </div>
    </div>
@endsection