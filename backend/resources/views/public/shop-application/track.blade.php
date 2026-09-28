@extends('layouts.public')

@section('title', 'Track Application Status - Yola South')

@section('content')
<div class="max-w-xl mx-auto px-4 py-16">
    <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-xl text-center">
        <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
            <i data-lucide="search" class="w-7 h-7"></i>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Track Application Status</h1>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto leading-relaxed">
            Enter your unique Application Reference Number (e.g. ALL-YSLG-2026-000186) to check approval progress and view your allocation card.
        </p>

        @if(session('error'))
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-semibold text-left">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('public.shop-application.track') }}" method="GET" class="mt-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 text-left">Application Reference Number *</label>
                <input type="text" name="ref" required placeholder="e.g. ALL-YSLG-2026-000186" value="{{ request('ref') }}" class="w-full text-center text-sm font-mono font-bold rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-3 uppercase">
            </div>
            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
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
