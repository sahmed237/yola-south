@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex justify-between items-end">
        <div>
            <a href="{{ route('admin.approvals.index') }}"
                class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Approvals
            </a>
            <h1 class="text-2xl font-bold text-slate-800">Verification Details</h1>
            <p class="text-slate-500 text-sm">Review, modify and finalize establishment registration.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-8 p-6 bg-red-50 border border-red-100 text-red-700 rounded-[2rem] shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <h3 class="font-bold">Validation Errors Detected</h3>
            </div>
            <ul class="space-y-1 ml-11 list-disc text-sm font-medium opacity-80">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.approvals.process', $establishment->id) }}" method="POST" id="approvalForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Establishment Details Editable -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-500">
                            <i data-lucide="store" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Establishment Profile</h2>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Registration Metadata</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ 
                        selectedLga: '{{ old('lga', $establishment->lga) }}',
                        lgas: {{ Js::from($lgas) }},
                        isLoadingWards: false,
                        displayWards: [],
                        updateWards() {
                            this.isLoadingWards = true;
                            this.displayWards = [];
                            setTimeout(() => {
                                const lga = this.lgas.find(l => l.name === this.selectedLga);
                                this.displayWards = lga ? lga.wards : [];
                                this.isLoadingWards = false;
                            }, 400); // 400ms simulated loading
                        },
                        init() {
                            if (this.selectedLga) {
                                const lga = this.lgas.find(l => l.name === this.selectedLga);
                                this.displayWards = lga ? lga.wards : [];
                            }
                        }
                    }">
                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Legal
                                Business Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name', $establishment->name) }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Category
                                <span class="text-red-500">*</span></label>
                            <select name="establishment_type_id" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                <option value="">Select Category</option>
                                @foreach($establishmentTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('establishment_type_id', $establishment->establishment_type_id) == $type->id ? 'selected' : '' }}>{{ $type->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Classification
                                Size <span class="text-red-500">*</span></label>
                            <select name="establishment_size_id" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                <option value="">Select Size</option>
                                @foreach($establishmentSizes as $size)
                                    <option value="{{ $size->id }}" {{ old('establishment_size_id', $establishment->establishment_size_id) == $size->id ? 'selected' : '' }}>{{ $size->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Local
                                Government Area <span class="text-red-500">*</span></label>
                            <select name="lga" required x-model="selectedLga" @change="updateWards"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                <option value="">Select LGA</option>
                                <template x-for="lga in lgas" :key="lga.id">
                                    <option :value="lga.name" x-text="lga.name" :selected="lga.name === selectedLga">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label
                                class="flex items-center justify-between text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                <span>Political Ward <span class="text-red-500">*</span></span>
                                <div x-show="isLoadingWards" style="display: none;"
                                    class="flex items-center gap-1 text-primary-500">
                                    <i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i>
                                    <span class="text-[9px]">Loading...</span>
                                </div>
                            </label>
                            <select name="ward" required :disabled="isLoadingWards"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                :class="{'opacity-50 cursor-wait': isLoadingWards}">
                                <option value="" x-text="isLoadingWards ? 'Loading wards...' : 'Select Ward'"></option>
                                <template x-for="ward in displayWards" :key="ward.id">
                                    <option :value="ward.name" x-text="ward.name"
                                        :selected="ward.name === {{ Js::from(old('ward', $establishment->ward)) }}"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label
                                class="flex items-center justify-between text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                                <span>Inside Metropolis? <span class="text-red-500">*</span></span>
                            </label>
                            <div class="flex items-center gap-3 px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl">
                                <input type="hidden" name="inside_metropolis" value="0">
                                <input type="checkbox" name="inside_metropolis" value="1" {{ old('inside_metropolis', $establishment->inside_metropolis) ? 'checked' : '' }}
                                    class="w-5 h-5 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                                <span class="text-sm text-slate-600 font-medium">Establishment is within city limits</span>
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Revenue
                                Base Year <span class="text-red-500">*</span></label>
                            <select name="base_year" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                @for ($y = (int)date('Y'); $y >= 2015; $y--)
                                    <option value="{{ $y }}" {{ old('base_year', $establishment->base_year ?? config('app.revenue_base_year', date('Y'))) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Address Details -->
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500">
                            <i data-lucide="map" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Address Verification</h2>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Physical Location Details
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Street
                                Address <span class="text-red-500">*</span></label>
                            <input type="text" name="street_address" required
                                value="{{ old('street_address', $establishment->street_address) }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">House/Establishment
                                Number <span class="text-red-500">*</span></label>
                            <input type="text" name="house_number" required
                                value="{{ old('house_number', $establishment->house_number) }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">City
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="city" required value="{{ old('city', $establishment->city) }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Postal
                                Code <span class="text-red-500">*</span></label>
                            <input type="text" name="postal_code" required
                                value="{{ old('postal_code', $establishment->postal_code) }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Evidence & Verification Photos -->
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500">
                                <i data-lucide="camera" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-800">Evidence Photos</h2>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Field Capture Verification</p>
                            </div>
                        </div>
                        <span class="px-4 py-1.5 bg-slate-100 rounded-full text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            {{ $establishment->images->count() }} Assets
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @forelse($establishment->images as $image)
                            <div class="group relative aspect-square rounded-[2rem] overflow-hidden border border-slate-100 bg-slate-50 shadow-sm transition-all hover:shadow-xl">
                                <img src="{{ Storage::url($image->image_path) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-6 text-white">
                                    <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-widest mb-2 text-rose-300">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        Geotagged Asset
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-bold">LAT: {{ $image->lat }}</p>
                                        <p class="text-[10px] font-bold">LNG: {{ $image->lng }}</p>
                                        <p class="text-[8px] text-white/50 truncate mt-2">{{ $image->device_info }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 py-12 flex flex-col items-center justify-center text-slate-400 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                                <i data-lucide="image-off" class="w-12 h-12 mb-4 opacity-20"></i>
                                <p class="text-sm font-bold uppercase tracking-widest">No verification images found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Geospatial Data -->
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500">
                                <i data-lucide="map-pin" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-800">Geospatial Context</h2>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Precise Location
                                    Verification</p>
                            </div>
                        </div>
                        <button type="button" id="locate-me"
                            class="px-4 py-2 bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-200 transition-all flex items-center gap-2">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                            Locate My Device
                        </button>
                    </div>

                    <div class="mb-6 rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                        <div id="map" class="w-full h-[400px] bg-slate-50"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Latitude
                                <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="lat" id="lat" value="{{ old('lat', $establishment->lat) }}"
                                required readonly
                                class="w-full px-5 py-4 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-0 cursor-not-allowed">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Longitude
                                <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="lng" id="lng" value="{{ old('lng', $establishment->lng) }}"
                                required readonly
                                class="w-full px-5 py-4 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-0 cursor-not-allowed">
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

            <div class="space-y-8">
                <!-- Occupant Profile -->
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500">
                            <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Occupant Profile</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Full
                                Name</label>
                            <input type="text" name="occupant_name"
                                value="{{ old('occupant_name', $establishment->occupant->name ?? '') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Phone
                                Number</label>
                            <input type="text" name="occupant_phone"
                                value="{{ old('occupant_phone', $establishment->occupant->phone ?? '') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Owner Profile -->
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10" x-data="{ 
                        showOwnerModal: false, 
                        owners: [], 
                        isTouched: {{ ($establishment->owner ? 'true' : 'false') }},
                        checkTouched() {
                            this.isTouched = $refs.ninInput.value || $refs.ownerName.value || $refs.ownerPhone.value || $refs.ownerEmail.value || $refs.ownerGender.value;
                        },
                        async searchOwner() {
                            const nin = $refs.ninInput.value;
                            if (nin.length < 4) return;

                            try {
                                const response = await fetch(`{{ route('admin.owners.search') }}?nin=${nin}`);
                                this.owners = await response.json();
                                if (this.owners.length > 0) {
                                    this.showOwnerModal = true;
                                }
                            } catch (error) {
                                console.error('Owner search failed:', error);
                            }
                        },
                        selectOwner(owner) {
                            $refs.ownerName.value = owner.name;
                            $refs.ownerPhone.value = owner.phone || '';
                            $refs.ownerEmail.value = owner.email || '';
                            $refs.ownerGender.value = owner.gender || '';
                            $refs.ninInput.value = owner.nin;
                            this.showOwnerModal = false;
                        }
                    }">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-500">
                            <i data-lucide="briefcase" class="w-6 h-6"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Owner Profile</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">National
                                ID (NIN) <span class="text-slate-300 font-medium lowercase italic">(Type to
                                    lookup)</span></label>
                            <div class="relative group">
                                <input type="text" name="owner_nin" x-ref="ninInput"
                                    value="{{ old('owner_nin', $establishment->owner->nin ?? '') }}" @focusout="searchOwner"
                                    class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                    placeholder="Enter NIN to check for existing record...">
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-500 transition-colors">
                                    <i data-lucide="search" class="w-5 h-5"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Owner
                                Full Name <span x-show="isTouched" class="text-red-500">*</span></label>
                            <input type="text" name="owner_name" x-ref="ownerName"
                                value="{{ old('owner_name', $establishment->owner->name ?? '') }}" @input="checkTouched"
                                :required="isTouched"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>


                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Owner
                                Phone <span x-show="isTouched && !$refs.ownerEmail.value"
                                    class="text-red-500">*</span></label>
                            <input type="text" name="owner_phone" x-ref="ownerPhone"
                                value="{{ old('owner_phone', $establishment->owner->phone ?? '') }}" @input="checkTouched"
                                :required="isTouched && !$refs.ownerEmail.value"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Owner
                                Email <span x-show="isTouched && !$refs.ownerPhone.value"
                                    class="text-red-500">*</span></label>
                            <input type="email" name="owner_email" x-ref="ownerEmail"
                                value="{{ old('owner_email', $establishment->owner->email ?? '') }}" @input="checkTouched"
                                :required="isTouched && !$refs.ownerPhone.value"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>


                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Owner
                                Type / Gender <span x-show="isTouched" class="text-red-500">*</span></label>
                            <select name="owner_gender" x-ref="ownerGender" @change="checkTouched" :required="isTouched"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                <option value="">Select Option</option>
                                <option value="male" {{ old('owner_gender', $establishment->owner->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('owner_gender', $establishment->owner->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="corporate" {{ old('owner_gender', $establishment->owner->gender ?? '') == 'corporate' ? 'selected' : '' }}>Corporate / Entity</option>
                            </select>
                        </div>
                    </div>

                    <!-- Owner Suggestion Modal -->
                    <template x-if="showOwnerModal">
                        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                            @click.self="showOwnerModal = false">
                            <div
                                class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
                                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-800">Existing Owners Found</h3>
                                        <p class="text-xs text-slate-500 mt-1">We found owners matching this NIN. Would you
                                            like to use one?</p>
                                    </div>
                                    <button type="button" @click="showOwnerModal = false"
                                        class="p-2 hover:bg-white rounded-xl transition-colors">
                                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                                    </button>
                                </div>
                                <div class="p-4 max-h-[400px] overflow-y-auto">
                                    <template x-for="owner in owners" :key="owner.id">
                                        <div @click="selectOwner(owner)"
                                            class="p-6 mb-3 rounded-2xl border border-slate-100 hover:border-primary-500 hover:bg-primary-50/30 cursor-pointer transition-all group">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-bold text-slate-800 group-hover:text-primary-600 transition-colors"
                                                        x-text="owner.name"></p>
                                                    <p class="text-xs text-slate-500 mt-1" x-text="owner.nin"></p>
                                                    <div class="flex gap-4 mt-3">
                                                        <div
                                                            class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                            <i data-lucide="phone" class="w-3 h-3"></i>
                                                            <span x-text="owner.phone || 'No Phone'"></span>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                            <i data-lucide="mail" class="w-3 h-3"></i>
                                                            <span x-text="owner.email || 'No Email'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-primary-600 group-hover:text-white transition-all">
                                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                                    <button type="button" @click="showOwnerModal = false"
                                        class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500 hover:text-slate-800 transition-colors">
                                        Keep Current Entry
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Approval Action -->
                <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-slate-300">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white/60">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-bold">Final Verification</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-white/50 uppercase tracking-widest mb-2">Action</label>
                            <select name="action" id="approvalAction" required
                                class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-2xl text-sm text-white placeholder-white/50 focus:ring-4 focus:ring-white/10 focus:border-white/30 transition-all">
                                <option value="" class="text-slate-800">Select Action...</option>
                                <option value="approve" class="text-slate-800" {{ old('action') == 'approve' ? 'selected' : '' }}>Approve</option>
                                <option value="reject" class="text-slate-800" {{ old('action') == 'reject' ? 'selected' : '' }}>Reject</option>
                            </select>
                        </div>

                        <div id="remarksContainer" style="display: {{ old('action') == 'reject' ? 'block' : 'none' }};">
                            <label class="block text-[10px] font-black text-white/50 uppercase tracking-widest mb-2">Remarks
                                / Comment <span class="text-red-400">*</span></label>
                            <textarea name="remarks" id="remarksInput" rows="3"
                                class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-2xl text-sm text-white placeholder-white/50 focus:ring-4 focus:ring-white/10 focus:border-white/30 transition-all"
                                placeholder="Provide reason for rejection...">{{ old('remarks') }}</textarea>
                        </div>

                        <button type="submit"
                            class="w-full py-4 bg-primary-600 hover:bg-primary-500 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-primary-500/20 flex items-center justify-center gap-2">
                            Submit Verification
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map {
            z-index: 1;
        }

        .leaflet-control-layers-toggle {
            width: 44px !important;
            height: 44px !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const actionSelect = document.getElementById('approvalAction');
            const remarksContainer = document.getElementById('remarksContainer');
            const remarksInput = document.getElementById('remarksInput');

            actionSelect.addEventListener('change', function () {
                if (this.value === 'reject') {
                    remarksContainer.style.display = 'block';
                    remarksInput.setAttribute('required', 'required');
                } else {
                    remarksContainer.style.display = 'none';
                    remarksInput.removeAttribute('required');
                }
            });

            // Map Initialization
            const initialLat = {{ $establishment->lat ?? config('app.initial_lat') }};
            const initialLng = {{ $establishment->lng ?? config('app.initial_lng') }};

            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');

            const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href=\'http://www.openstreetmap.org/copyright\'>OpenStreetMap</a>'
            });

            const googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            });

            const map = L.map('map', {
                center: [initialLat, initialLng],
                zoom: 16,
                layers: [osm]
            });

            const baseMaps = {
                "Open Street Map": osm,
                "Google Hybrid": googleHybrid
            };
            L.control.layers(baseMaps).addTo(map);

            let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                updateInputs(position.lat, position.lng);
            });

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
            }

            function placeMarker(lat, lng) {
                marker.setLatLng([lat, lng]);
                updateInputs(lat, lng);
            }

            map.on('click', function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
            });

            [latInput, lngInput].forEach(input => {
                input.addEventListener('change', function () {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.setView([lat, lng], 16);
                        placeMarker(lat, lng);
                    }
                });
            });

            const locateBtn = document.getElementById('locate-me');
            locateBtn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert("Geolocation is not supported by your browser.");
                    return;
                }
                map.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true });
            });

            map.on('locationfound', function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
            });

            map.on('locationerror', function (e) {
                if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                    alert("Location access is blocked on non-secure (HTTP) connections. Please use HTTPS.");
                } else {
                    alert("Location access denied. Please check your browser permissions.");
                }
            });
        });
    </script>
@endpush