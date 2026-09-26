@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end print:hidden">
    <div>
        <a href="{{ route('admin.establishment-update-requests.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Requests
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Review Update Request</h1>
        <p class="text-slate-500 text-sm">Reviewing modification request for {{ $establishment->name }}.</p>
    </div>
</div>

<!-- Request Info Header (Decision Panel) -->
<div class="mb-10 bg-slate-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-800">
    <div class="grid grid-cols-1 lg:grid-cols-3">
        <div class="p-10 lg:col-span-2 border-b lg:border-b-0 lg:border-r border-slate-800">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                    <i data-lucide="help-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Update Justification</h3>
                    <p class="text-xs text-white/40 font-medium uppercase tracking-wider">Submitted by {{ $updateRequest->requester->name }} • {{ $updateRequest->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <div class="p-6 bg-white/5 rounded-2xl border border-white/10 relative">
                <i data-lucide="quote" class="absolute top-4 right-6 w-8 h-8 text-white/5"></i>
                <p class="text-sm text-white/70 italic leading-relaxed">
                    "{{ $updateRequest->reason }}"
                </p>
            </div>
        </div>
        
        <div class="p-10 bg-slate-800/50 flex flex-col justify-center">
            <h4 class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-6">Administrative Decision</h4>
            
            @if($updateRequest->status === 'pending')
            <form action="{{ route('admin.establishment-update-requests.approve', $updateRequest->id) }}" method="POST" id="action-form">
                @csrf
                <div class="mb-6">
                    <textarea name="remarks" rows="2" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-white placeholder-white/20" placeholder="Optional remarks..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-3 bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20">
                        Approve
                    </button>
                    <button type="button" onclick="rejectRequest()" class="flex-1 px-4 py-3 bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-red-500 hover:text-white transition-all">
                        Reject
                    </button>
                </div>
            </form>
            @else
            <div class="text-center py-4">
                <span class="px-4 py-2 bg-white/5 rounded-full text-[10px] font-black text-white/60 uppercase tracking-widest border border-white/10">
                    Request {{ ucfirst($updateRequest->status) }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 print:block">
    <!-- Left Column: Primary Identity Card -->
    <div class="lg:col-span-1 space-y-8 print:mb-8">
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
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
        </div>
    </div>

    <!-- Right Column: Detailed Info Sections (Mirrored from details.blade.php) -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Physical Address Details -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500">
                    <i data-lucide="map" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Physical Address</h3>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <div class="col-span-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Street Address</p>
                    <p class="text-base font-bold text-slate-800">{{ $establishment->street_address }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">House/Establishment Number</p>
                    <p class="text-base font-bold text-slate-800">{{ $establishment->house_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">City</p>
                    <p class="text-base font-bold text-slate-800">{{ $establishment->city }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Postal Code</p>
                    <p class="text-base font-bold text-slate-800">{{ $establishment->postal_code }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Metropolis Status</p>
                    <p class="text-base font-bold text-slate-800">{{ $establishment->inside_metropolis ? 'Within City Limits' : 'Outside City Limits' }}</p>
                </div>
            </div>
        </div>

        <!-- Establishment Gallery -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500">
                        <i data-lucide="camera" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Establishment Gallery</h3>
                </div>
                <span class="px-4 py-1.5 bg-slate-100 rounded-full text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    {{ $establishment->images->count() }} Photos
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($establishment->images as $image)
                    <div class="group relative aspect-square rounded-[2rem] overflow-hidden border border-slate-100 bg-slate-50 shadow-sm transition-all hover:shadow-xl">
                        <img src="{{ Storage::url($image->image_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    </div>
                @empty
                    <div class="col-span-3 py-12 flex flex-col items-center justify-center text-slate-400 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                        <i data-lucide="image-off" class="w-12 h-12 mb-4 opacity-20"></i>
                        <p class="text-sm font-bold uppercase tracking-widest">No images uploaded</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Geospatial Intelligence -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500">
                    <i data-lucide="globe" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Geospatial Intelligence</h3>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Latitude</p>
                    <p class="text-base font-bold text-slate-800 font-mono">{{ $establishment->lat }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Longitude</p>
                    <p class="text-base font-bold text-slate-800 font-mono">{{ $establishment->lng }}</p>
                </div>
                <div class="col-span-2">
                    <div id="map" class="w-full h-48 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Occupant Profile -->
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Occupant Profile</h3>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Full Name</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->occupant->name ?? 'Not Available' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Phone Number</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->occupant->phone ?? 'Not Available' }}</p>
                    </div>
                </div>
            </div>

            <!-- Owner Profile -->
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-purple-500">
                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Owner Profile</h3>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Owner Name</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->owner->name ?? 'Not Available' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Identity (NIN)</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->owner->nin ?? 'Not Available' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Contact Details</p>
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->owner->phone ?? '' }}</p>
                        <p class="text-xs text-slate-500">{{ $establishment->owner->email ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($establishment->activityLogs->count() > 0)
        <!-- Activity History -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400">
                    <i data-lucide="history" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Audit Trail</h3>
            </div>

            <div class="relative space-y-6 before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
                @foreach($establishment->activityLogs as $log)
                <div class="relative flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-white border border-slate-100 shadow-sm flex items-center justify-center shrink-0 z-10">
                        @php
                            $color = match($log->action_type) {
                                'approved', 'update_authorized', 'update_completed' => 'bg-emerald-500',
                                'rejected', 'update_denied' => 'bg-red-500',
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
                        <div class="w-2 h-2 rounded-full {{ $color }}"></div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-bold text-slate-800 uppercase tracking-tight">{{ $label }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $log->remarks ?? 'No comments provided' }}</p>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mt-2">
                            {{ $log->user?->name ?? 'Public Portal' }} • {{ $log->created_at->format('M d, Y h:ia') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function rejectRequest() {
    if(confirm('Are you sure you want to REJECT this update request?')) {
        const form = document.getElementById('action-form');
        form.action = "{{ route('admin.establishment-update-requests.reject', $updateRequest->id) }}";
        form.submit();
    }
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    @media print {
        body { background: white !important; }
        .print\:hidden { display: none !important; }
        .shadow-xl, .shadow-2xl { shadow: none !important; box-shadow: none !important; }
        .rounded-\[2\.5rem\] { border-radius: 1rem !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $establishment->lat }};
        const lng = {{ $establishment->lng }};
        
        const map = L.map('map', {
            center: [lat, lng],
            zoom: 16,
            zoomControl: false,
            dragging: false,
            touchZoom: false,
            doubleClickZoom: false,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map);
    });
</script>
@endpush
