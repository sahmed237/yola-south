@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex justify-between items-end print:hidden">
        <div>
            <a href="{{ route('admin.establishments.index') }}"
                class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Registrations
            </a>
            <h1 class="text-2xl font-bold text-slate-800">Print Profile</h1>
            <p class="text-slate-500 text-sm">Official identity card for {{ $establishment->name }}.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.establishments.details', $establishment->id) }}"
                class="px-6 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-2">
                <i data-lucide="eye" class="w-4 h-4"></i>
                Detailed Info
            </a>
            <button onclick="window.print()"
                class="px-6 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-900 transition-all shadow-xl shadow-slate-200 flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print Now
            </button>
        </div>
    </div>

    <div class="flex items-center justify-center p-4">
        <div
            class="max-w-md w-full bg-white shadow-2xl rounded-[2.5rem] overflow-hidden border border-slate-100 print:shadow-none print:border-none print:m-0">
            <!-- Header / Identity -->
            <div class="bg-indigo-600 p-10 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                <div class="relative">
                    <div
                        class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-md">
                        <i data-lucide="store" class="text-white w-8 h-8"></i>
                    </div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ $establishment->name }}</h1>
                    <!-- <p class="text-indigo-100 text-xs font-bold uppercase tracking-[0.2em] mt-2">Official Verified Establishment</p> -->
                </div>
            </div>

            <div class="p-8">
                <!-- Unique ID Badge -->
                <div class="bg-slate-100 rounded-2xl p-4 mb-8 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Registration ID</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->unique_id ?? 'PENDING' }}</p>
                    </div>
                    <i data-lucide="shield-check" class="text-emerald-500 w-6 h-6"></i>
                </div>

                <!-- Establishment Details -->
                <div class="space-y-6 mb-10">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="text-indigo-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Full Address</p>
                            <p class="text-sm font-bold text-slate-700 leading-tight">
                                {{ $establishment->house_number }}, {{ $establishment->street_address }},<br>
                                {{ $establishment->city }}, {{ $establishment->lga }} LGA, {{ $establishment->ward }}
                                Ward<br>
                                {{ $establishment->postal_code }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="text-indigo-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Registered
                                Occupant</p>
                            <p class="text-sm font-bold text-slate-700">
                                {{ $establishment->occupant->name ?? 'Not Registered' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="tag" class="text-indigo-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Business
                                Classification</p>
                            <p class="text-sm font-bold text-slate-700">
                                {{ $establishment->establishmentType->value ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="scaling" class="text-indigo-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Scale Size</p>
                            <p class="text-sm font-bold text-slate-700">
                                {{ $establishment->establishmentSize->value ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="mt-8 pt-8 border-t border-slate-50 flex flex-col items-center">
                        <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 mb-4">
                            {!! $qrCode !!}
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Scan to Verify</p>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Live Verification Secure
                        </p>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-relaxed max-w-[200px] mx-auto font-medium">
                        State Government Unified Revenue Collection Portal
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @media print {
            @page {
                size: auto;
                margin: 10mm;
            }

            aside,
            header,
            .print\:hidden {
                display: none !important;
            }

            main {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
                overflow: visible !important;
                display: block !important;
            }

            .flex-1 {
                overflow: visible !important;
                display: block !important;
            }

            .h-full {
                height: auto !important;
            }

            .overflow-hidden {
                overflow: visible !important;
            }

            /* Scale for Half A4 (A5 area) */
            .max-w-md {
                max-width: 148mm !important;
                width: 148mm !important;
                margin: 0 auto !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 1.5rem !important;
            }

            .shadow-2xl {
                box-shadow: none !important;
            }

            .bg-indigo-600 {
                background-color: #4f46e5 !important;
                -webkit-print-color-adjust: exact;
            }

            /* Adjust padding and spacing for smaller scale */
            .p-10 {
                padding: 1rem !important;
            }

            .p-8 {
                padding: 1rem !important;
            }

            .mb-8 {
                margin-bottom: 0.75rem !important;
            }

            .mb-10 {
                margin-bottom: 0.75rem !important;
            }

            .mt-8 {
                margin-top: 0.5rem !important;
            }

            .pt-8 {
                padding-top: 0.5rem !important;
            }

            .space-y-6> :not([hidden])~ :not([hidden]) {
                margin-top: 0.5rem !important;
            }

            .text-2xl {
                font-size: 1.1rem !important;
                line-height: 1.2 !important;
            }

            .text-sm {
                font-size: 0.8rem !important;
            }

            .text-[10px] {
                font-size: 0.7rem !important;
            }

            .w-16 {
                width: 2.5rem !important;
                height: 2.5rem !important;
            }

            .h-16 {
                height: 2.5rem !important;
            }

            .w-10 {
                width: 1.75rem !important;
                height: 1.75rem !important;
            }

            .h-10 {
                height: 1.75rem !important;
            }

            /* Compact QR Code */
            .bg-white.p-3 {
                padding: 0.5rem !important;
            }

            svg {
                width: 100px !important;
                height: 100px !important;
            }
        }
    </style>
@endpush