@extends('layouts.public')

@section('title', 'Explore Map - ' . ($system_settings['platform_name'] ?? 'Unified Revenue Collection System'))

@section('head_scripts')
    <!-- Leaflet CSS & JS for Interactive Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endsection

@section('styles')
<style>
    #map-canvas {
        height: 100%;
        width: 100%;
        z-index: 1;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 1.5rem;
        padding: 6px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        border: 1px solid #f1f5f9;
    }
    .leaflet-popup-tip {
        background: white;
    }
    .leaflet-control-layers {
        border-radius: 1rem !important;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1) !important;
        border: 1px solid #f1f5f9 !important;
        padding: 6px 10px !important;
        font-family: '{{ $system_settings['theme_font_family'] ?? 'Outfit' }}', sans-serif !important;
        font-weight: 600 !important;
        font-size: 11px !important;
    }
    .leaflet-bar {
        border-radius: 1rem !important;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1) !important;
        border: 1px solid #f1f5f9 !important;
    }
    .leaflet-bar a {
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .leaflet-bar a:first-child {
        border-top-left-radius: 1rem !important;
        border-top-right-radius: 1rem !important;
    }
    .leaflet-bar a:last-child {
        border-bottom-left-radius: 1rem !important;
        border-bottom-right-radius: 1rem !important;
        border-bottom: none !important;
    }
    .custom-pin-icon {
        background: none !important;
        border: none !important;
    }
</style>
@endsection

@section('content')
<main class="w-full max-w-7xl mx-auto px-6 py-6 flex-grow flex flex-col relative z-10">
    <!-- Title & Summary Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="/" class="inline-flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors group">
                <svg class="w-3.5 h-3.5 mr-1 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Home
            </a>
            <h1 class="text-3xl font-black text-slate-900 mt-2 tracking-tight flex items-center gap-3">
                Find Your Establishment
                <span class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-lg">
                    Map Lookup
                </span>
            </h1>
            <p class="text-slate-500 text-xs mt-1">Browse the interactive map or search the registry to locate your establishment and settle outstanding taxes.</p>
        </div>
    </div>

    <!-- Main Layout Container -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:h-[700px] w-full">
        <!-- Left Sidebar Panel -->
        <div class="lg:col-span-4 bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl p-6 flex flex-col h-[400px] lg:h-full overflow-hidden">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Establishment List</h3>
                <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mt-0.5" id="results-count">Showing {{ $establishments->count() }} locations</p>
                
                <!-- Sleek Search Input -->
                <div class="relative mt-4">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" id="establishment-search" placeholder="Search by name, address, owner..." class="w-full pl-10 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-semibold text-slate-800 placeholder-slate-400" autocomplete="off">
                </div>
            </div>

            <!-- List Scroll Container -->
            <div id="establishments-list" class="flex-grow overflow-y-auto fancy-scrollbar space-y-3 mt-4 pr-1">
                @foreach($establishments as $est)
                    <div id="est-card-{{ $est->id }}" class="group bg-slate-50 hover:bg-white border border-slate-100 hover:border-emerald-500/20 rounded-3xl p-4 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer flex flex-col justify-between" onclick="locateEstablishment({{ $est->id }})">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 bg-slate-200/50 group-hover:bg-emerald-50 text-[9px] font-black uppercase tracking-wider font-mono text-slate-600 group-hover:primary-text rounded-md transition-colors">
                                    {{ $est->unique_id }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-400 group-hover:text-emerald-500 flex items-center gap-1 transition-colors">
                                    Locate <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </span>
                            </div>
                            <h4 class="font-extrabold text-sm text-slate-900 group-hover:primary-text transition-colors leading-tight mb-2">
                                {{ $est->name }}
                            </h4>
                            
                            <div class="space-y-1.5 text-[11px] text-slate-500 font-semibold border-t border-slate-200/50 pt-2">
                                <div class="flex items-start gap-1">
                                    <span class="text-slate-400">📍</span>
                                    <span class="line-clamp-1">{{ $est->house_number }} {{ $est->street_address }}, {{ $est->ward }}, {{ $est->lga }}</span>
                                </div>
                                @if($est->occupant)
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-400">👤</span>
                                        <span class="line-clamp-1">{{ $est->occupant->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3.5 pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $est->establishmentType->value ?? 'Business' }}</span>
                            <a href="{{ route('public.establishment.show', $est->unique_id) }}" class="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider rounded-xl transition-all shadow-sm" onclick="event.stopPropagation();">
                                Pay Taxes
                            </a>
                        </div>
                    </div>
                @endforeach

                <!-- No Results State -->
                <div id="no-results" class="hidden text-center py-12 px-4">
                    <div class="w-12 h-12 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center text-slate-400 mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">No establishments found</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Try checking your spelling or search terms.</p>
                </div>
            </div>
        </div>

        <!-- Right Map Panel -->
        <div id="map-container" class="lg:col-span-8 bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl p-2 h-[450px] lg:h-full overflow-hidden relative">
            <div id="map-canvas" class="rounded-[2.2rem] overflow-hidden"></div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Parse Laravel establishments data
        const establishments = [
            @foreach($establishments as $est)
            {
                id: {{ $est->id }},
                unique_id: "{{ $est->unique_id }}",
                name: "{{ addslashes($est->name) }}",
                lat: {{ $est->lat }},
                lng: {{ $est->lng }},
                address: "{{ addslashes($est->house_number . ' ' . $est->street_address) }}",
                lga: "{{ addslashes($est->lga) }}",
                ward: "{{ addslashes($est->ward) }}",
                type: "{{ addslashes($est->establishmentType->value ?? 'Business') }}",
                size: "{{ addslashes($est->establishmentSize->value ?? '') }}",
                occupant: "{{ addslashes($est->occupant->name ?? '') }}",
                owner: "{{ addslashes($est->owner->name ?? '') }}",
                showUrl: "{{ route('public.establishment.show', $est->unique_id) }}"
            },
            @endforeach
        ];

        // Map Layers
        const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        const googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        // Default state center coordinates (Adamawa, Nigeria)
        const initialLat = {{ config('app.initial_lat') }};
        const initialLng = {{ config('app.initial_lng') }};

        // Initialize Map with Google Hybrid as default
        const map = L.map('map-canvas', {
            center: [initialLat, initialLng],
            zoom: 13,
            layers: [googleHybrid]
        });

        // Layer Control
        const baseMaps = {
            "Google Hybrid": googleHybrid,
            "Open Street Map": osm
        };
        L.control.layers(baseMaps, null, { collapsed: false }).addTo(map);

        // Marker references mapping
        const markers = {};

        // Cyan marker icon configuration
        const cyanIcon = L.divIcon({
            html: `
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.25));">
                    <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2Z" fill="#00E5FF" stroke="#0F172A" stroke-width="1.5" stroke-linejoin="round"/>
                    <circle cx="12" cy="9" r="3.5" fill="#0F172A"/>
                </svg>
            `,
            className: 'custom-pin-icon',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        // Add markers
        establishments.forEach(est => {
            const marker = L.marker([est.lat, est.lng], { icon: cyanIcon });

            // Custom popup markup using Tailwind compatible style variables & SVG icons
            const popupContent = `
                <div class="p-2 min-w-[220px] font-sans">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[9px] font-mono font-bold tracking-wider">
                            ${est.unique_id}
                        </span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 mb-1 leading-snug">${est.name}</h4>
                    <p class="text-[9px] text-slate-400 font-bold mb-2.5 uppercase tracking-widest">${est.type} • ${est.size}</p>
                    
                    <div class="space-y-1.5 text-[11px] text-slate-600 mb-4 border-t border-slate-100 pt-2.5 font-semibold">
                        <div class="flex items-start gap-1">
                            <span class="text-slate-400">📍</span>
                            <span>${est.address}, ${est.ward} Ward, ${est.lga} LGA</span>
                        </div>
                        ${est.occupant ? `
                        <div class="flex items-start gap-1">
                            <span class="text-slate-400">👤</span>
                            <span>${est.occupant}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <a href="${est.showUrl}" class="block text-center py-2.5 primary-btn text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all shadow-md">
                        Settle Taxes
                    </a>
                </div>
            `;

            marker.bindPopup(popupContent);
            marker.addTo(map);
            markers[est.id] = marker;
        });

        // If there are markers, fit the map bounds to contain them
        if (establishments.length > 0) {
            const group = new L.featureGroup(Object.values(markers));
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // Locate Establishment Function
        window.locateEstablishment = function(id) {
            const est = establishments.find(e => e.id === id);
            const marker = markers[id];
            if (est && marker) {
                map.setView([est.lat, est.lng], 17);
                marker.openPopup();

                // Smooth scroll to map on small screen viewports
                if (window.innerWidth < 1024) {
                    document.getElementById('map-container').scrollIntoView({ behavior: 'smooth' });
                }
            }
        };

        // Dynamic Sidebar + Map Search Filter
        const searchInput = document.getElementById('establishment-search');
        const resultsCountEl = document.getElementById('results-count');
        const noResultsState = document.getElementById('no-results');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            establishments.forEach(est => {
                const match = est.name.toLowerCase().includes(query) ||
                              est.unique_id.toLowerCase().includes(query) ||
                              est.address.toLowerCase().includes(query) ||
                              est.lga.toLowerCase().includes(query) ||
                              est.ward.toLowerCase().includes(query) ||
                              est.type.toLowerCase().includes(query) ||
                              est.occupant.toLowerCase().includes(query) ||
                              est.owner.toLowerCase().includes(query);

                const card = document.getElementById(`est-card-${est.id}`);
                const marker = markers[est.id];

                if (match) {
                    card.classList.remove('hidden');
                    if (!map.hasLayer(marker)) {
                        map.addLayer(marker);
                    }
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                    if (map.hasLayer(marker)) {
                        map.removeLayer(marker);
                    }
                }
            });

            // Update result counter text
            resultsCountEl.textContent = `Showing ${visibleCount} location${visibleCount !== 1 ? 's' : ''}`;

            // Handle empty search results state
            if (visibleCount === 0) {
                noResultsState.classList.remove('hidden');
            } else {
                noResultsState.classList.add('hidden');
            }
        });
    });
</script>
@endsection
