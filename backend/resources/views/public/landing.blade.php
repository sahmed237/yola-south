@extends('layouts.public')

@section('title', ($system_settings['platform_name'] ?? 'Unified Revenue Collection System') . ' - Public Taxpayer Portal')

@section('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin-slow {
        animation: spin-slow 25s linear infinite;
    }
</style>
@endsection

@section('content')
<div class="flex-grow flex flex-col justify-between">
    <!-- Hero Wrapper with City Background -->
    <div class="relative w-full overflow-hidden bg-cover bg-center border-b border-slate-100" style="background-image: url('{{ asset('img/hero-bg.jpg') }}');">
        <!-- Soft gradient overlay to ensure text readability -->
        <div class="absolute inset-0 bg-gradient-to-r from-slate-50 via-slate-50/95 to-slate-50/80 lg:from-white lg:via-white/95 lg:to-white/70"></div>
        
        <!-- Background decorative art / blur glows -->
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-emerald-500/10 rounded-full blur-3xl"></div>
        
        <!-- Hero Content Container -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 py-16 lg:py-24 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Column: Copy & Search -->
            <div class="lg:col-span-7 flex flex-col items-start text-left">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest mb-6">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Secure Online Payments & Verification
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 leading-tight">
                    Unified Revenue & <br />
                    <span class="bg-gradient-to-r from-emerald-600 via-[#58c6a5] to-teal-500 bg-clip-text text-transparent">Taxpayer Portal</span>
                </h1>
                <p class="text-slate-600 text-sm md:text-base font-semibold mt-6 max-w-xl leading-relaxed">
                    Instantly lookup registered business establishments, view outstanding tax assessments, and safely settle payments online.
                </p>
                
                <!-- Search Bar Lookup -->
                <div class="w-full mt-10 max-w-2xl">
                    <form action="{{ route('public.search') }}" method="GET" class="relative group shadow-2xl shadow-slate-200/50">
                        <input type="text" name="q" placeholder="Search by Business Name, Owner Name or Registration ID..."
                            required
                            class="w-full px-6 py-5 bg-white/90 backdrop-blur-sm border border-slate-200 focus:border-emerald-500/40 rounded-3xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all duration-300 pr-16 shadow-inner">
                        <button type="submit"
                            class="absolute right-3 top-3 w-12 h-12 primary-btn text-white rounded-2xl flex items-center justify-center transition-all shadow-lg hover:scale-105">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </button>
                    </form>
                    @if(session('error'))
                        <div class="mt-4 text-left text-xs font-bold text-rose-500">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Right Column: Portal Mockup Graphic -->
            <div class="lg:col-span-5 flex justify-center items-center relative">
                <!-- Abstract rotating / floating circle patterns behind mockup -->
                <div class="absolute w-[360px] h-[360px] lg:w-[450px] lg:h-[450px] bg-gradient-to-tr from-emerald-500/10 to-teal-500/5 rounded-full animate-spin-slow -z-10"></div>
                <div class="absolute w-[280px] h-[280px] lg:w-[350px] lg:h-[350px] border border-emerald-500/10 rounded-full -z-10"></div>
                
                <!-- Mockup Image -->
                <img src="{{ asset('img/portal-mockup.png') }}" alt="Portal Mockup" 
                     class="w-full max-w-[380px] lg:max-w-[420px] h-auto object-contain drop-shadow-3xl animate-float">
            </div>
            
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="w-full max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl w-full mx-auto">
            <!-- Widget 1 -->
            <div class="bg-white rounded-[2.5rem] p-8 relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300 border border-slate-100 shadow-xl shadow-slate-100">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-50 rounded-full blur-xl"></div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Verified System</span>
                </div>
                <p class="text-3xl font-black text-slate-800">₦{{ number_format($totalRevenue, 2) }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Total Online Collections</p>
            </div>

            <!-- Widget 2 -->
            <div class="bg-white rounded-[2.5rem] p-8 relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300 border border-slate-100 shadow-xl shadow-slate-100">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-50 rounded-full blur-xl"></div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Approved</span>
                </div>
                <p class="text-3xl font-black text-slate-800">{{ number_format($totalEstablishments) }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Active Registrations</p>
            </div>

            <!-- Widget 3 -->
            <div class="bg-white rounded-[2.5rem] p-8 relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300 border border-slate-100 shadow-xl shadow-slate-100">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-50 rounded-full blur-xl"></div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Revenue Code</span>
                </div>
                <p class="text-3xl font-black text-slate-800">{{ $totalRules }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Active Revenue Rules</p>
            </div>
        </div>
    </div>
</div>
@endsection