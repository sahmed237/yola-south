@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end print:hidden">
    <div>
        <a href="{{ route('admin.establishments.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Registrations
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Establishment Profile</h1>
        <p class="text-slate-500 text-sm">Detailed information and identification assets for {{ $establishment->name }}.</p>
    </div>
    <div class="flex gap-3">
        @php
            $pendingRequest = $establishment->updateRequests->whereIn('status', ['pending', 'approved'])->first();
        @endphp

        @if($establishment->status === 'approved' && !$pendingRequest)
            @can('request establishment update')
            <button @click="$dispatch('open-modal', 'request-update')" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-2">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
                Request Update
            </button>
            @endcan
        @endif

        @if($establishment->status === 'rejected')
            @if($establishment->created_by === auth()->id() || auth()->user()->hasPermissionTo('view all invalid establishment'))
            <a href="{{ route('admin.establishments.edit', $establishment->id) }}" class="px-6 py-3 bg-red-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-red-600 transition-all shadow-xl shadow-red-200 flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                Correct Registration
            </a>
            @endif
        @endif

        @if($pendingRequest && $pendingRequest->status === 'approved')
            @can('execute establishment update')
            <a href="{{ route('admin.establishments.edit', $establishment->id) }}" class="px-6 py-3 bg-amber-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-amber-600 transition-all shadow-xl shadow-amber-200 flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                Execute Approved Update
            </a>
            @endcan
        @endif

        <a href="{{ route('admin.establishments.show', $establishment->id) }}" class="px-6 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-900 transition-all shadow-xl shadow-slate-200 flex items-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Print Profile
        </a>
    </div>
</div>

<!-- Request Update Modal -->
<div x-data="{ open: false }" @open-modal.window="if($event.detail === 'request-update') open = true" class="relative z-[60]" x-show="open" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form action="{{ route('admin.establishments.request-update', $establishment->id) }}" method="POST">
                    @csrf
                    <div class="bg-white p-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500">
                                <i data-lucide="alert-circle" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">Request Information Update</h3>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Formal Authorization Required</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Reason for Update</label>
                                <textarea name="reason" rows="4" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all" placeholder="Explain why this establishment's information needs to be modified after approval..."></textarea>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <p class="text-[10px] text-slate-500 leading-relaxed italic">
                                    <i data-lucide="info" class="w-3 h-3 inline-block mr-1"></i>
                                    Note: This request will be reviewed by an authorized supervisor. Once approved, the establishment will become editable by an authorized officer.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-10 py-6 flex flex-row-reverse gap-3">
                        <button type="submit" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-200">Submit Request</button>
                        <button type="button" @click="open = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 print:block">
    <!-- Left Column: Primary Identity Card -->
    <div class="lg:col-span-1 space-y-8 print:mb-8">
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <!-- Header Pattern -->
            <div class="h-32 bg-primary-600 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-full p-8">
                    <div class="flex justify-between items-end">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg">
                            <i data-lucide="store" class="text-primary-600 w-8 h-8"></i>
                        </div>
                        <div class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-lg border border-white/30">
                            <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ $establishment->status }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 pt-6">
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $establishment->name }}</h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $establishment->establishmentType->value ?? 'N/A' }} • {{ $establishment->establishmentSize->value ?? 'N/A' }}</p>
                </div>

                <!-- QR Code Section -->
                @if($establishment->status === 'approved')
                <div class="bg-slate-50 rounded-[2rem] p-8 mb-8 flex flex-col items-center border border-slate-100 shadow-inner">
                    <div class="bg-white p-4 rounded-2xl shadow-sm mb-4">
                        {!! $qrCode !!}
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 text-center">Scan to Verify Profile</p>
                    <p class="text-sm font-bold text-slate-800 text-center font-mono">{{ $establishment->unique_id }}</p>
                </div>
                @else
                <div class="bg-slate-50 rounded-[2rem] p-8 mb-8 flex flex-col items-center border border-slate-100 shadow-inner opacity-60">
                    <div class="w-24 h-24 bg-white/50 rounded-2xl flex items-center justify-center border-2 border-dashed border-slate-200 mb-4">
                        <i data-lucide="qr-code" class="w-10 h-10 text-slate-200"></i>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 text-center">Identity Assets Pending</p>
                    <p class="text-[10px] font-bold text-amber-600 text-center uppercase tracking-tighter">Requires Verification</p>
                </div>
                @endif

                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="text-indigo-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Location</p>
                            <p class="text-sm font-bold text-slate-700">{{ $establishment->lga }} LGA, {{ $establishment->ward }} Ward</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="text-emerald-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Registered Date</p>
                            <p class="text-sm font-bold text-slate-700">{{ $establishment->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="calendar-days" class="text-indigo-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue Base Year</p>
                            <p class="text-sm font-bold text-slate-700">{{ $establishment->base_year ?? config('app.revenue_base_year', date('Y')) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="user-check" class="text-amber-600 w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Enumerator</p>
                            <p class="text-sm font-bold text-slate-700">{{ $establishment->creator->name ?? 'Unknown' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Decoration -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Unified Revenue System</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Tabbed Sections -->
    <div class="lg:col-span-2 space-y-6" 
         x-data="{
             activeTab: (function() {
                 const h = window.location.hash.replace('#', '');
                 if (h === 'tax-assessment' || h === 'billing') return 'billing';
                 if (h === 'audit-trail' || h === 'audit') return 'audit';
                 if (h === 'establishment-info' || h === 'info') return 'info';
                 return 'info';
             })(),
             setTab(tab) {
                 this.activeTab = tab;
                 window.location.hash = tab === 'billing' ? 'tax-assessment' : (tab === 'audit' ? 'audit-trail' : 'establishment-info');
                 if (tab === 'info') {
                     setTimeout(() => {
                         if (window.leafletMap) window.leafletMap.invalidateSize();
                     }, 150);
                 }
                 this.$nextTick(() => {
                     if (window.lucide) lucide.createIcons();
                 });
             }
         }"
         x-init="$watch('activeTab', tab => {
             if (tab === 'info') {
                 setTimeout(() => {
                     if (window.leafletMap) window.leafletMap.invalidateSize();
                 }, 150);
             }
         })">

        <!-- Navigation Tabs Bar -->
        <div class="bg-white p-2 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-wrap items-center justify-between gap-2 print:hidden">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                <!-- Tab 1: Establishment Info -->
                <button type="button" @click="setTab('info')"
                    :class="activeTab === 'info' 
                        ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25' 
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Info</span>
                </button>

                <!-- Tab 2: Tax Assessment & Billing -->
                <button type="button" @click="setTab('billing')"
                    :class="activeTab === 'billing' 
                        ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25' 
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Billing</span>
                    @if($taxStatus['totals']['outstanding'] > 0)
                        <span :class="activeTab === 'billing' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">
                            ₦{{ number_format($taxStatus['totals']['outstanding']) }}
                        </span>
                    @else
                        <span :class="activeTab === 'billing' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">
                            Paid
                        </span>
                    @endif
                </button>

                <!-- Tab 3: Audit Trail -->
                <button type="button" @click="setTab('audit')"
                    :class="activeTab === 'audit' 
                        ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25' 
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Audit Trail</span>
                    @if($establishment->activityLogs->count() > 0)
                        <span :class="activeTab === 'audit' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">
                            {{ $establishment->activityLogs->count() }}
                        </span>
                    @endif
                </button>
            </div>
        </div>

        <!-- ==================== TAB 1: ESTABLISHMENT INFO ==================== -->
        <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6 print:!block">
            <!-- Physical Address & Map Card -->
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                            <i data-lucide="map-pin" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Physical Address &amp; Location</h3>
                            <p class="text-xs text-slate-400">Street location, coordinates, and geospatial boundary.</p>
                        </div>
                    </div>
                    <span class="px-3.5 py-1.5 bg-slate-100 rounded-xl text-[10px] font-black uppercase tracking-wider text-slate-600 border border-slate-200">
                        {{ $establishment->inside_metropolis ? 'Metropolitan Area' : 'Outside Metropolis' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-8 bg-slate-50/80 p-6 rounded-2xl border border-slate-100">
                    <div class="col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Street Address</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->street_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">House / Unit No.</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->house_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">City / Town</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->city ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Postal Code</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->postal_code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Latitude</p>
                        <p class="text-sm font-bold text-slate-800 font-mono">{{ $establishment->lat ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Longitude</p>
                        <p class="text-sm font-bold text-slate-800 font-mono">{{ $establishment->lng ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Interactive Map -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                            <i data-lucide="globe" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Geospatial Satellite &amp; Street Map
                        </span>
                        <span class="text-[10px] font-medium text-slate-400">Interactive coordinates locator</span>
                    </div>
                    <div id="map" class="w-full h-64 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner z-0"></div>
                </div>
            </div>

            <!-- Occupant & Owner Profiles (Side-by-Side Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Occupant Profile -->
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500">
                            <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Occupant Profile</h3>
                            <p class="text-xs text-slate-400">Current business operator</p>
                        </div>
                    </div>

                    <div class="space-y-4 bg-slate-50/80 p-6 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Full Name</p>
                            <p class="text-sm font-bold text-slate-800">{{ $establishment->occupant->name ?? 'Not Available' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Phone Number</p>
                            <p class="text-sm font-bold text-slate-800 font-mono">{{ $establishment->occupant->phone ?? 'Not Available' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Owner Profile -->
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600">
                            <i data-lucide="briefcase" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Owner Profile</h3>
                            <p class="text-xs text-slate-400">Property / asset owner</p>
                        </div>
                    </div>

                    <div class="space-y-4 bg-slate-50/80 p-6 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Owner Name</p>
                            <p class="text-sm font-bold text-slate-800">{{ $establishment->owner->name ?? 'Not Available' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">NIN</p>
                                <p class="text-sm font-bold text-slate-800 font-mono">{{ $establishment->owner->nin ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Phone</p>
                                <p class="text-sm font-bold text-slate-800 font-mono">{{ $establishment->owner->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @if(!empty($establishment->owner->email))
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email</p>
                            <p class="text-xs font-semibold text-slate-600">{{ $establishment->owner->email }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Establishment Photo Gallery -->
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500">
                            <i data-lucide="camera" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Establishment Photo Gallery</h3>
                            <p class="text-xs text-slate-400">Enumeration site verification photographs</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 bg-slate-100 rounded-full text-[10px] font-black text-slate-600 uppercase tracking-widest">
                        {{ $establishment->images->count() }} Photos
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @forelse($establishment->images as $image)
                        <div class="group relative aspect-square rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 shadow-sm transition-all hover:shadow-xl">
                            <img src="{{ Storage::url($image->image_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                                <p class="text-white text-[10px] font-black uppercase tracking-widest mb-1 flex items-center gap-1.5">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-emerald-400"></i>
                                    Geotag Info
                                </p>
                                <div class="text-white/80 text-[9px] font-medium leading-tight">
                                    <p>LAT: {{ $image->lat }}</p>
                                    <p>LNG: {{ $image->lng }}</p>
                                    <p class="mt-1 text-white/60 truncate" title="{{ $image->device_info }}">
                                        {{ Str::limit($image->device_info, 30) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-10 flex flex-col items-center justify-center text-slate-400 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-100">
                            <i data-lucide="image-off" class="w-10 h-10 mb-2 opacity-20"></i>
                            <p class="text-xs font-bold uppercase tracking-widest">No verification images uploaded</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ==================== TAB 2: TAX ASSESSMENT & BILLING ==================== -->
        <div x-show="activeTab === 'billing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6 print:!block" id="tax-assessment" x-data="{ openPayModal: false }">
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10 relative overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Tax Assessment & Billing</h3>
                        <p class="text-xs text-slate-400">Live evaluation of applicable revenue heads and outstanding liabilities.</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($taxStatus['totals']['outstanding'] > 0)
                        <span class="px-4 py-2 bg-rose-50 border border-rose-100 rounded-full text-xs font-black text-rose-600 uppercase tracking-widest">
                            ₦{{ number_format($taxStatus['totals']['outstanding'], 2) }} Outstanding
                        </span>
                    @else
                        <span class="px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-full text-xs font-black text-emerald-600 uppercase tracking-widest">
                            Fully Settled
                        </span>
                    @endif
                </div>
            </div>

            @if(!config('services.manual_payment') && $taxStatus['totals']['outstanding'] > 0)
                <div class="mb-8 p-6 bg-rose-50 border border-rose-200 rounded-[2rem] flex items-center gap-4 text-rose-700 shadow-sm">
                    <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center shrink-0">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-rose-800">Direct Payments Disabled</p>
                        <p class="text-xs text-rose-600 font-medium mt-0.5">Manual direct collection is currently disabled in system configuration.</p>
                    </div>
                </div>
            @endif

            <!-- Tax Summary Cards -->
            <div class="grid grid-cols-3 gap-6 mb-8 bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Total Assessment</span>
                    <span class="text-sm md:text-base font-black text-slate-800 block">₦{{ number_format($taxStatus['totals']['due'], 2) }}</span>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Total Settled</span>
                    <span class="text-sm md:text-base font-black text-emerald-600 block">₦{{ number_format($taxStatus['totals']['paid'], 2) }}</span>
                </div>
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Outstanding Balance</span>
                    <span class="text-sm md:text-base font-black text-rose-600 block">₦{{ number_format($taxStatus['totals']['outstanding'], 2) }}</span>
                </div>
            </div>

            <!-- Rules Status Table -->
            {{-- Records info bar --}}
            @if($taxStatus['fees']->total() > 0)
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Showing {{ $taxStatus['fees']->firstItem() }}–{{ $taxStatus['fees']->lastItem() }} of {{ number_format($taxStatus['fees']->total()) }} records
                </span>
                <span class="text-[10px] font-bold text-slate-400">
                    Page {{ $taxStatus['fees']->currentPage() }} of {{ $taxStatus['fees']->lastPage() }}
                </span>
            </div>
            @endif

            {{-- Scrollable table container (max-height = ~10 rows) --}}
            <div class="overflow-x-auto">
                <div class="overflow-y-auto" style="max-height: 480px;">
                    <table class="w-full">
                        <thead class="sticky top-0 z-10 bg-white">
                            <tr class="border-b border-slate-100 text-left">
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue Head</th>
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Frequency</th>
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Period</th>
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Assessment</th>
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Paid</th>
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Outstanding</th>
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="pb-4 pt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($taxStatus['fees'] as $fee)
                                <tr class="group hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4">
                                        <span class="text-xs font-bold text-slate-800 block">{{ $fee['rule_name'] }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $fee['agency_name'] }}</span>
                                    </td>
                                    <td class="py-4 text-xs font-bold text-slate-600">{{ $fee['frequency'] }}</td>
                                    <td class="py-4 text-xs font-medium text-slate-500">{{ $fee['period'] }}</td>
                                    <td class="py-4 text-xs font-black text-slate-800 text-right">₦{{ number_format($fee['due_amount'], 2) }}</td>
                                    <td class="py-4 text-xs font-black text-emerald-600 text-right">₦{{ number_format($fee['paid_amount'], 2) }}</td>
                                    <td class="py-4 text-xs font-black text-rose-600 text-right font-bold">₦{{ number_format($fee['outstanding_amount'], 2) }}</td>
                                    <td class="py-4 text-center">
                                        @php
                                            $badgeColor = match($fee['status']) {
                                                'Paid' => 'bg-emerald-100 text-emerald-700',
                                                'Partially Paid' => 'bg-amber-100 text-amber-700',
                                                'Unpaid' => 'bg-rose-100 text-rose-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider {{ $badgeColor }}">
                                            {{ $fee['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-center">
                                        @if($fee['outstanding_amount'] > 0)
                                            @if(config('services.manual_payment'))
                                                <button @click="$dispatch('open-modal', { name: 'record-payment', ruleName: '{{ $fee['rule_name'] }}', ruleId: '{{ $fee['rule_id'] }}', dueAmount: {{ $fee['due_amount'] }}, outstandingAmount: {{ $fee['outstanding_amount'] }} })" class="px-3 py-1.5 bg-primary-600 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-primary-700 transition-all shadow-md shadow-primary-100">
                                                    Settle Tax
                                                </button>
                                            @else
                                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Unpaid</span>
                                            @endif
                                        @else
                                            <span class="text-slate-300 text-[10px] font-bold uppercase tracking-widest flex items-center justify-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i> Settled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-400">
                                        <i data-lucide="receipt" class="w-12 h-12 mb-4 opacity-20 mx-auto block"></i>
                                        <p class="text-sm font-bold uppercase tracking-widest">No applicable revenue heads</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination links --}}
            @if($taxStatus['fees']->hasPages())
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-6">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    {{ $taxStatus['fees']->total() }} total records
                </span>
                <div class="flex items-center gap-1">
                    {{-- Previous --}}
                    @if($taxStatus['fees']->onFirstPage())
                        <span class="px-3 py-2 text-[10px] font-black text-slate-300 uppercase tracking-widest bg-slate-50 rounded-xl cursor-not-allowed">&laquo;</span>
                    @else
                        <a href="{{ $taxStatus['fees']->previousPageUrl() }}#tax-assessment" class="px-3 py-2 text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors">&laquo;</a>
                    @endif

                    {{-- Page numbers --}}
                    @foreach($taxStatus['fees']->getUrlRange(max(1, $taxStatus['fees']->currentPage()-2), min($taxStatus['fees']->lastPage(), $taxStatus['fees']->currentPage()+2)) as $pg => $url)
                        @if($pg == $taxStatus['fees']->currentPage())
                            <span class="px-3 py-2 text-[10px] font-black text-white bg-primary-600 rounded-xl">{{ $pg }}</span>
                        @else
                            <a href="{{ $url }}#tax-assessment" class="px-3 py-2 text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors">{{ $pg }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($taxStatus['fees']->hasMorePages())
                        <a href="{{ $taxStatus['fees']->nextPageUrl() }}#tax-assessment" class="px-3 py-2 text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors">&raquo;</a>
                    @else
                        <span class="px-3 py-2 text-[10px] font-black text-slate-300 uppercase tracking-widest bg-slate-50 rounded-xl cursor-not-allowed">&raquo;</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- ==================== TAB 3: AUDIT TRAIL ==================== -->
        <div x-show="activeTab === 'audit'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6 print:!block" id="audit-trail">
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-600">
                            <i data-lucide="history" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Establishment Audit Trail</h3>
                            <p class="text-xs text-slate-400">Complete historical timeline of registrations, verifications, updates, and approvals.</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 bg-slate-100 rounded-full text-[10px] font-black text-slate-600 uppercase tracking-widest">
                        {{ $establishment->activityLogs->count() }} Entries Recorded
                    </span>
                </div>

                @if($establishment->activityLogs->count() > 0)
                    <div class="relative space-y-6 before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-px before:bg-slate-200 pl-1">
                        @foreach($establishment->activityLogs as $log)
                            <div class="relative flex items-start gap-4 group">
                                <div class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center shrink-0 z-10">
                                    @php
                                        $color = match($log->action_type) {
                                            'approved', 'update_authorized', 'update_completed' => 'bg-emerald-500',
                                            'rejected', 'update_denied' => 'bg-rose-500',
                                            'update_requested' => 'bg-amber-500',
                                            default => 'bg-primary-500',
                                        };
                                        $label = match($log->action_type) {
                                            'registration_submitted' => 'Registration Submitted',
                                            'approved' => 'Registration Approved',
                                            'rejected' => 'Registration Rejected',
                                            'update_requested' => 'Update Requested',
                                            'update_authorized' => 'Update Authorized',
                                            'update_denied' => 'Update Denied',
                                            'update_completed' => 'Update Executed',
                                            'updated' => 'Registration Corrected',
                                            default => ucfirst(str_replace('_', ' ', $log->action_type)),
                                        };
                                    @endphp
                                    <div class="w-2.5 h-2.5 rounded-full {{ $color }}"></div>
                                </div>
                                <div class="pt-1 flex-1">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <p class="text-sm font-bold text-slate-800 uppercase tracking-tight">{{ $label }}</p>
                                        <span class="text-[10px] font-mono text-slate-400 font-medium">
                                            {{ $log->created_at->format('M d, Y h:ia') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                                        {{ $log->remarks ?? 'No remarks recorded for this activity.' }}
                                    </p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2 flex items-center gap-1.5">
                                        <i data-lucide="user-check" class="w-3 h-3 text-slate-400"></i>
                                        Action By: <span class="text-slate-600">{{ $log->user?->name ?? 'Public Portal / System' }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 flex flex-col items-center justify-center text-slate-400 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200/60">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 mb-3 shadow-sm">
                            <i data-lucide="history" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-1">No Audit Logs Recorded</p>
                        <p class="text-xs text-slate-400">Activity and status changes performed on this establishment will be tracked here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Settle Tax Payment Modal -->
<div x-data="{ open: false, ruleName: '', ruleId: '', dueAmount: 0, outstandingAmount: 0, reference: '' }" 
     @open-modal.window="if($event.detail.name === 'record-payment') { open = true; ruleName = $event.detail.ruleName; ruleId = $event.detail.ruleId; dueAmount = $event.detail.dueAmount; outstandingAmount = $event.detail.outstandingAmount; reference = ''; }" 
     class="relative z-[60]" 
     x-show="open" 
     style="display: none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form action="{{ route('admin.establishments.pay-rule', $establishment->id) }}" method="POST">
                    @csrf
                    <div class="bg-white p-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                                <i data-lucide="credit-card" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">Record Tax Payment</h3>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider font-mono">Establishment Collection Assistant</p>
                            </div>
                        </div>

                        <input type="hidden" name="revenue_head_id" :value="ruleId">
                        <input type="hidden" name="revenue_rule_id" :value="ruleId">

                        <div class="space-y-6">
                            <!-- Payment Reference -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Payment Reference <span class="text-rose-500">*</span></label>
                                <input type="text" name="reference" x-model="reference" required placeholder="e.g. TRF/9820491024/UBA" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-mono font-bold text-slate-800">
                            </div>

                            <div>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 px-1">Selected Rule</span>
                                <span class="text-base font-bold text-slate-800" x-text="ruleName"></span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Original Assessment</span>
                                    <span class="text-sm font-bold text-slate-700" x-text="'₦' + dueAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Remaining Outstanding</span>
                                    <span class="text-sm font-black text-rose-600" x-text="'₦' + outstandingAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Amount to Pay (₦)</label>
                                <input type="number" step="0.01" name="amount" :max="outstandingAmount" :value="outstandingAmount" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-black text-slate-800">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Payment Mode</label>
                                <select name="gateway" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-700">
                                    <option value="Bank Transfer" selected>Bank Transfer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-10 py-6 flex flex-row-reverse gap-3">
                        <button type="submit" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-200">Settle Tax Balance</button>
                        <button type="button" @click="open = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    @media print {
        body { background: white !important; }
        .print\:hidden { display: none !important; }
        .print\:block { display: block !important; }
        .shadow-xl, .shadow-2xl { shadow: none !important; box-shadow: none !important; }
        .rounded-\[2\.5rem\] { border-radius: 1rem !important; }
        .bg-primary-600 { background-color: #4f46e5 !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $establishment->lat }};
        const lng = {{ $establishment->lng }};
        
        // Map Layers
        const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href=\'http://www.openstreetmap.org/copyright\'>OpenStreetMap</a>'
        });

        const googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        // Initialize Map
        const map = L.map('map', {
            center: [lat, lng],
            zoom: 16,
            layers: [osm]
        });

        // Layer Control
        const baseMaps = {
            "Open Street Map": osm,
            "Google Hybrid": googleHybrid
        };
        L.control.layers(baseMaps).addTo(map);

        L.marker([lat, lng]).addTo(map);
    });
</script>
@endpush
