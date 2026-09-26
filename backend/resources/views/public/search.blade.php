@extends('layouts.public')

@section('title', 'Search Registrations - ' . ($system_settings['platform_name'] ?? 'Unified Revenue Collection System'))

@section('head_scripts')
    <!-- Leaflet CSS & JS for Interactive Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endsection

@section('content')
    <!-- Content -->
    <main class="w-full max-w-7xl mx-auto px-6 py-12 flex-grow relative z-10">
        <!-- Back Navigation -->
        <div class="mb-8">
            <a href="/" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors group">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Home
            </a>
            <h2 class="text-2xl font-black text-slate-900 mt-4">Search Results for "{{ $query }}"</h2>
            <p class="text-slate-500 text-xs mt-1">Found {{ $establishments->count() }} active verified business registrations.</p>
        </div>

        @if($establishments->count() > 0)
            <!-- Grid of Results -->
            <div class="flex flex-wrap justify-center gap-8">
                @foreach($establishments as $est)
                    <div class="bg-white rounded-[2.5rem] p-8 relative overflow-hidden border border-slate-100 hover:border-emerald-500/20 shadow-2xl shadow-slate-100 w-full md:w-[480px] flex flex-col justify-between group hover:scale-[1.01] transition-all duration-300">
                        <div>
                            <!-- Header ID Badge -->
                            <div class="flex items-center justify-between mb-6">
                                <span class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[9px] font-black uppercase tracking-widest font-mono primary-text bg-emerald-50/50">
                                    {{ $est->unique_id }}
                                </span>
                                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600 shrink-0">
                                    <i data-lucide="check-check" class="w-4 h-4"></i>
                                </div>
                            </div>

                            <!-- Storefront Images banner -->
                            <div class="mb-6 rounded-3xl overflow-hidden shadow-sm relative">
                                @if($est->images && $est->images->count() > 0)
                                    <div class="w-full h-48 relative">
                                        <img src="{{ Storage::url($est->images->first()->image_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                        @if($est->images->count() > 1)
                                            <span class="absolute right-3 top-3 px-2 py-1 bg-slate-950/70 text-white text-[9px] font-black rounded-lg backdrop-blur-sm shadow-md">
                                                +{{ $est->images->count() - 1 }} More photo(s)
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="w-full h-36 bg-gradient-to-tr from-emerald-500/10 to-teal-500/5 flex items-center justify-center border border-emerald-500/10 relative">
                                        <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-[#58c6a5]/10 rounded-full blur-xl"></div>
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-white rounded-2xl shadow-md border border-slate-100 flex items-center justify-center mx-auto text-emerald-600 mb-2">
                                                <i data-lucide="store" class="w-5 h-5"></i>
                                            </div>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">No Storefront Image Uploaded</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Name -->
                            <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight group-hover:primary-text transition-colors duration-300">
                                {{ $est->name }}
                            </h3>

                            <!-- Complete Detailed Info grid -->
                            <div class="bg-slate-50 border border-slate-100 p-5 rounded-3xl space-y-4 mb-6 shadow-inner">
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Location LGA & Ward</span>
                                        <span class="font-bold text-slate-800">{{ $est->lga }} LGA, {{ $est->ward }} Ward</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Physical Address</span>
                                        <span class="font-bold text-slate-800 block truncate">{{ $est->house_number }} {{ $est->street_address }}</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-xs pt-3 border-t border-slate-200/60">
                                    <div>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Business Classification</span>
                                        <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px]">{{ $est->establishmentType->value }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Scale Size</span>
                                        <span class="font-bold text-slate-800">{{ $est->establishmentSize->value }} Scale</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-xs pt-3 border-t border-slate-200/60">
                                    <div>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Registered Occupant</span>
                                        <span class="font-bold text-slate-800 block truncate">{{ $est->occupant->name ?? 'No Registered Occupant' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Business Owner</span>
                                        <span class="font-bold text-slate-800 block truncate">{{ $est->owner->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                @if($est->inside_metropolis)
                                    <div class="pt-3 border-t border-slate-200/60 text-left">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 border border-emerald-200 text-emerald-800 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                            <span class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-pulse"></span>
                                            Inside Metropolis Zone
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Map location Section -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-2.5">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                        <i data-lucide="map" class="w-3.5 h-3.5 primary-text"></i>
                                        Geographic Location
                                    </span>

                                    @if($est->lat && $est->lng)
                                        <span class="text-[9px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">
                                            GPS: {{ number_format($est->lat, 4) }}, {{ number_format($est->lng, 4) }}
                                        </span>
                                    @else
                                        <span class="text-[9px] font-bold text-rose-500 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded">
                                            GPS Coordinates Missing
                                        </span>
                                    @endif
                                </div>
                                
                                <div id="map-{{ $est->unique_id }}" class="h-44 w-full rounded-3xl border border-slate-100 shadow-inner relative z-10 overflow-hidden"></div>
                            </div>
                        </div>

                        <!-- Proceed to Tax Details Button -->
                        <a href="{{ route('public.establishment.show', $est->unique_id) }}" class="w-full py-4 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg flex items-center justify-center gap-2 mt-4">
                            Settle Taxes
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                    <!-- Map initialization Script block -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var lat = {{ $est->lat ?? config('app.initial_lat') }};
                            var lng = {{ $est->lng ?? config('app.initial_lng') }};
                            
                            var map = L.map('map-{{ $est->unique_id }}', {
                                zoomControl: true,
                                scrollWheelZoom: false
                            }).setView([lat, lng], 15);
                            
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(map);
                            
                            var marker = L.marker([lat, lng]).addTo(map);
                            marker.bindPopup("<b class='text-xs font-bold text-slate-800'>{{ addslashes($est->name) }}</b><br><span class='text-[10px] text-slate-500 font-semibold'>{{ addslashes($est->house_number) }} {{ addslashes($est->street_address) }}</span>").openPopup();
                        });
                    </script>
                @endforeach
            </div>
        @else
            <!-- Zero State -->
            <div class="bg-white rounded-[2.5rem] p-16 text-center max-w-xl mx-auto my-12 border border-slate-100 shadow-xl shadow-slate-100">
                <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-3xl flex items-center justify-center text-slate-400 mx-auto mb-6">
                    <i data-lucide="search-code" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">No Matching Businesses Found</h3>
                <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                    We couldn't find any approved business registrations matching "{{ $query }}". Please make sure you have spelled the name correctly, or entered the complete Unique ID code.
                </p>
                <div class="mt-8 flex justify-center gap-4">
                    <a href="/" class="px-6 py-3 primary-btn text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                        New Search
                    </a>
                </div>
            </div>
        @endif
    </main>
@endsection
