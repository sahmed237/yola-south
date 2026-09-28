@extends('layouts.public')

@section('title', 'Track Application Status - Yola South')

@section('content')
<div class="max-w-xl mx-auto px-4 py-16">
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10 text-center">
        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
            <i data-lucide="search" class="w-7 h-7"></i>
        </div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Track Application Status</h1>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto leading-relaxed">
            Enter your unique Application Reference Number (e.g. ALL-YSLG-2026-000186) to check approval progress and view your allocation card.
        </p>

        @if(session('error'))
        <div class="mt-4 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl text-xs font-semibold text-left">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('public.shop-application.track') }}" method="GET" class="mt-6 space-y-4">
            <div class="text-left">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                    Application Reference Number <span class="text-red-500">*</span>
                </label>
                <input type="text" name="ref" required placeholder="e.g. ALL-YSLG-2026-000186" value="{{ request('ref') }}" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono font-bold text-center tracking-widest uppercase focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
            </div>
            <button type="submit" class="primary-btn w-full py-4 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all flex items-center justify-center gap-2">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                Check Live Status
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-center gap-2 text-xs text-slate-500">
            <span>New applicant?</span>
            <a href="{{ route('public.shop-application.index') }}" class="font-bold text-emerald-700 hover:underline">Apply for a shop unit &rarr;</a>
        </div>
    </div>
</div>
@endsection
