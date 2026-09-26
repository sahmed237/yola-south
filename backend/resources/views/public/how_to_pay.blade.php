@extends('layouts.public')

@section('title', 'How to Pay - ' . ($system_settings['platform_name'] ?? 'Unified Revenue Collection System'))

@section('content')
    <!-- Main Content: Infographics Page -->
    <main class="w-full max-w-7xl mx-auto px-6 py-16 flex-grow relative z-10">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary-50 border border-primary-100 text-primary-700 text-[10px] font-black uppercase tracking-widest mb-6">
                <i data-lucide="info" class="w-3.5 h-3.5"></i>
                Step-By-Step Visual Guide
            </div>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                How to Pay Your <span class="bg-gradient-to-r from-primary-600 via-primary-500 to-indigo-500 bg-clip-text text-transparent">Assessments</span>
            </h1>
            <p class="text-slate-500 text-sm md:text-base font-medium max-w-xl mx-auto mt-6 leading-relaxed">
                Follow our interactive guidelines to verify your establishment and securely pay tax items in under three minutes.
            </p>
        </div>

        <!-- Infographics / Steps Flow -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-16 relative">
            
            <!-- Step 1 Card -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-100/50 hover:shadow-2xl hover:shadow-primary-100/20 hover:scale-[1.03] transition-all duration-300 relative group">
                <div class="absolute -right-3 -top-3 w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 font-black text-lg border border-primary-100 group-hover:scale-110 transition-transform">
                    01
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6">
                    <i data-lucide="search" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-3">Lookup Business</h3>
                <p class="text-xs text-slate-400 font-semibold leading-relaxed">
                    Search using your Business Name, Owner Name, or ID on the homepage to find your registered profile.
                </p>
            </div>

            <!-- Step 2 Card -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-100/50 hover:shadow-2xl hover:shadow-primary-100/20 hover:scale-[1.03] transition-all duration-300 relative group">
                <div class="absolute -right-3 -top-3 w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 font-black text-lg border border-primary-100 group-hover:scale-110 transition-transform">
                    02
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-6">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-3">Select Rule</h3>
                <p class="text-xs text-slate-400 font-semibold leading-relaxed">
                    Browse the list of active revenue rules/tax categories and choose the item you wish to pay.
                </p>
            </div>

            <!-- Step 3 Card -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-100/50 hover:shadow-2xl hover:shadow-primary-100/20 hover:scale-[1.03] transition-all duration-300 relative group">
                <div class="absolute -right-3 -top-3 w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 font-black text-lg border border-primary-100 group-hover:scale-110 transition-transform">
                    03
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center text-primary-600 mb-6">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-3">Secure Payment</h3>
                <p class="text-xs text-slate-400 font-semibold leading-relaxed">
                    Review split summary details and settle via credit card, bank transfer, or standard gateways.
                </p>
            </div>

            <!-- Step 4 Card -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-100/50 hover:shadow-2xl hover:shadow-primary-100/20 hover:scale-[1.03] transition-all duration-300 relative group">
                <div class="absolute -right-3 -top-3 w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 font-black text-lg border border-primary-100 group-hover:scale-110 transition-transform">
                    04
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 mb-6">
                    <i data-lucide="verified" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-3">Receipt Verified</h3>
                <p class="text-xs text-slate-400 font-semibold leading-relaxed">
                    Download your tamper-proof PDF digital receipt containing active QR verification code indicators.
                </p>
            </div>

        </div>

        <!-- Banner Action Card -->
        <div class="bg-slate-900 rounded-[2.5rem] p-10 md:p-12 text-white relative overflow-hidden shadow-2xl flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="absolute right-0 top-0 w-80 h-80 bg-primary-600/20 rounded-full blur-3xl -z-10"></div>
            <div>
                <span class="text-xs font-black uppercase tracking-widest text-primary-400">Ready to start?</span>
                <h2 class="text-2xl md:text-3xl font-black mt-2 leading-tight">Verify & settle your pending <br/>outstanding assessments now.</h2>
            </div>
            <div>
                <a href="/" class="px-8 py-4 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:scale-105 transition-transform duration-300 inline-block text-center shadow-lg">
                    Lookup Business Now
                </a>
            </div>
        </div>
    </main>
@endsection
