@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl shadow-primary-100/50 border border-primary-50/50 p-12 text-center relative overflow-hidden">
        
        <!-- Background Accents -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-primary-50 rounded-full blur-3xl opacity-50"></div>
        
        <div class="relative w-20 h-20 mx-auto mb-8 bg-primary-100 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-100/50 transform rotate-3">
            <i data-lucide="key-round" class="w-10 h-10 text-primary-600"></i>
        </div>

        <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">Forgot Password?</h2>
        <p class="text-slate-500 text-sm leading-relaxed mb-8">
            No worries! Enter your email address and we'll send you a link to reset your password.
        </p>

        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-50 text-emerald-600 rounded-2xl text-xs font-bold border border-emerald-100 flex items-center">
                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-4 bg-red-50 text-red-600 rounded-2xl text-xs font-bold border border-red-100 flex items-start">
                <i data-lucide="alert-circle" class="w-4 h-4 mr-2 mt-0.5"></i>
                <ul class="flex-1 text-left space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf
            <div class="text-left">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Email Address</label>
                <div class="relative group">
                    <i data-lucide="mail" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors"></i>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                           placeholder="admin@example.com">
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-500/20 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                Send Reset Link
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-slate-50">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-400 hover:text-primary-600 transition-colors uppercase tracking-widest flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Login
            </a>
        </div>
    </div>
</div>
@endsection
