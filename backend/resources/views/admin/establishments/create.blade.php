@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.establishments.index') }}"
            class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Registrations
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Register New Establishment</h1>
        <p class="text-slate-500 text-sm">Manually add a establishment to the central revenue repository.</p>
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

    <form action="{{ route('admin.establishments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Establishment Details -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <i data-lucide="store" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Establishment Details</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ 
                        selectedLga: '{{ old('lga') }}',
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
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Establishment
                                Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="Enter legal business name">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Establishment
                                Type <span class="text-red-500">*</span></label>
                            <select name="establishment_type_id" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                <option value="">Select Category</option>
                                @foreach($establishmentTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('establishment_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Establishment
                                Size <span class="text-red-500">*</span></label>
                            <select name="establishment_size_id" required
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                <option value="">Select Size</option>
                                @foreach($establishmentSizes as $size)
                                    <option value="{{ $size->id }}" {{ old('establishment_size_id') == $size->id ? 'selected' : '' }}>
                                        {{ $size->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">LGA
                                <span class="text-red-500">*</span></label>
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
                                <span>Ward <span class="text-red-500">*</span></span>
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
                                        :selected="ward.name === {{ Js::from(old('ward')) }}"></option>
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
                                <input type="checkbox" name="inside_metropolis" value="1" {{ old('inside_metropolis') ? 'checked' : '' }}
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
                                    <option value="{{ $y }}" {{ old('base_year', config('app.revenue_base_year', date('Y'))) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Address Details -->
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                            <i data-lucide="map" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Address Details</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Street
                                Address <span class="text-red-500">*</span></label>
                            <input type="text" name="street_address" required value="{{ old('street_address') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="e.g. 123 Business Way">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">House/Establishment
                                Number <span class="text-red-500">*</span></label>
                            <input type="text" name="house_number" required value="{{ old('house_number') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="e.g. Suite 4B">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">City
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="city" required value="{{ old('city') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="e.g. Yola">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Postal
                                Code <span class="text-red-500">*</span></label>
                            <input type="text" name="postal_code" required value="{{ old('postal_code') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="e.g. 640001">
                        </div>
                    </div>
                </div>

                <!-- Establishment Images -->
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10" x-data="{ 
                    images: [],
                    maxImages: 3,
                    handleFileSelect(event) {
                        const files = Array.from(event.target.files);
                        if (this.images.length + files.length > this.maxImages) {
                            alert(`You can only upload a maximum of ${this.maxImages} images.`);
                            return;
                        }
                        
                        files.forEach(file => {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.images.push({
                                    id: Date.now() + Math.random(),
                                    file: file,
                                    preview: e.target.result,
                                    name: file.name
                                });
                            };
                            reader.readAsDataURL(file);
                        });
                    },
                    removeImage(id) {
                        this.images = this.images.filter(img => img.id !== id);
                    }
                }">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                                <i data-lucide="camera" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">Establishment Images</h2>
                                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Required: 1 - 3 Photos</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Image Previews -->
                        <div class="grid grid-cols-3 gap-4" x-show="images.length > 0">
                            <template x-for="image in images" :key="image.id">
                                <div class="relative aspect-square rounded-2xl overflow-hidden border border-slate-100 group">
                                    <img :src="image.preview" class="w-full h-full object-cover">
                                    <button type="button" @click="removeImage(image.id)" 
                                        class="absolute top-2 right-2 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg transform scale-0 group-hover:scale-100 transition-transform">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                    <div class="absolute bottom-0 left-0 right-0 p-2 bg-black/50 backdrop-blur-sm text-[8px] text-white truncate" x-text="image.name"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Upload Options -->
                        <div class="flex gap-4" x-show="images.length < maxImages">
                            <!-- File Upload -->
                            <label class="flex-1 flex flex-col items-center justify-center py-10 bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2rem] cursor-pointer hover:bg-slate-100 hover:border-primary-500 transition-all group">
                                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                                    <i data-lucide="upload-cloud" class="text-primary-600 w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-600">Upload Gallery</span>
                                <span class="text-[9px] text-slate-400 mt-1 uppercase tracking-widest">JPG, PNG up to 5MB</span>
                                <input type="file" name="establishment_images[]" class="hidden" accept="image/*" @change="handleFileSelect" multiple>
                            </label>

                            <!-- Camera Access (Mobile) -->
                            <label class="w-32 flex flex-col items-center justify-center py-10 bg-indigo-50 border-2 border-dashed border-indigo-200 rounded-[2rem] cursor-pointer hover:bg-indigo-100 hover:border-indigo-500 transition-all group">
                                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                                    <i data-lucide="aperture" class="text-indigo-600 w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-bold text-indigo-600 text-center">Take<br>Photo</span>
                                <input type="file" name="establishment_images[]" class="hidden" accept="image/*" capture="environment" @change="handleFileSelect">
                            </label>
                        </div>

                        <div class="bg-amber-50 rounded-xl p-4 flex gap-3 items-start border border-amber-100">
                            <i data-lucide="info" class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"></i>
                            <p class="text-[10px] text-amber-700 leading-relaxed font-medium">
                                <strong>Geotagging Tip:</strong> Ensure your phone's location service is active while taking photos. For high-precision verification, we recommend using a camera app that embeds GPS metadata.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Geospatial Data -->
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </div>
                            <h2 class="text-lg font-bold text-slate-800">Geospatial Intelligence</h2>
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
                            <input type="number" step="any" name="lat" id="lat" value="{{ old('lat') }}" required readonly
                                class="w-full px-5 py-4 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-0 cursor-not-allowed"
                                placeholder="Click on map to set">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Longitude
                                <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="lng" id="lng" value="{{ old('lng') }}" required readonly
                                class="w-full px-5 py-4 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-0 cursor-not-allowed"
                                placeholder="Click on map to set">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Occupant Details -->
            <div class="space-y-8">
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Occupant</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Full
                                Name</label>
                            <input type="text" name="occupant_name" value="{{ old('occupant_name') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="Person in charge">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Phone
                                Number</label>
                            <input type="text" name="occupant_phone" value="{{ old('occupant_phone') }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all"
                                placeholder="080 0000 0000">
                        </div>
                    </div>
                </div>

                <!-- Owner Details -->
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10" x-data="{ 
                        showOwnerModal: false, 
                        owners: [], 
                        isTouched: false,
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
                        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                            <i data-lucide="briefcase" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Owner Details</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">National
                                ID (NIN) <span class="text-slate-300 font-medium lowercase italic">(Type to
                                    lookup)</span></label>
                            <div class="relative group">
                                <input type="text" name="owner_nin" x-ref="ninInput" value="{{ old('owner_nin') }}"
                                    @focusout="searchOwner"
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
                            <input type="text" name="owner_name" x-ref="ownerName" value="{{ old('owner_name') }}"
                                @input="checkTouched" :required="isTouched"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>


                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Owner
                                Phone <span x-show="isTouched && !$refs.ownerEmail.value"
                                    class="text-red-500">*</span></label>
                            <input type="text" name="owner_phone" x-ref="ownerPhone" value="{{ old('owner_phone') }}"
                                @input="checkTouched" :required="isTouched && !$refs.ownerEmail.value"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Owner
                                Email <span x-show="isTouched && !$refs.ownerPhone.value"
                                    class="text-red-500">*</span></label>
                            <input type="email" name="owner_email" x-ref="ownerEmail" value="{{ old('owner_email') }}"
                                @input="checkTouched" :required="isTouched && !$refs.ownerPhone.value"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                        </div>


                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Owner
                                Type / Gender <span x-show="isTouched" class="text-red-500">*</span></label>
                            <select name="owner_gender" x-ref="ownerGender" @change="checkTouched" :required="isTouched"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                <option value="">Select Option</option>
                                <option value="male" {{ old('owner_gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('owner_gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="corporate" {{ old('owner_gender') == 'corporate' ? 'selected' : '' }}>Corporate
                                    / Entity</option>
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
                                        Create New Owner Instead
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Submit -->
                <div class="bg-slate-900 rounded-[2rem] p-10 text-white shadow-xl shadow-slate-300">
                    <h3 class="font-bold mb-2">Ready to submit?</h3>
                    <p class="text-slate-400 text-xs leading-relaxed mb-8">This establishment will be added to the pending
                        queue for final verification and ID generation.</p>

                    <button type="submit"
                        class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-primary-500/20">
                        Create Establishment
                    </button>
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
            // Initial coordinates (Adamawa, Nigeria)
            const initialLat = {{ config('app.initial_lat') }};
            const initialLng = {{ config('app.initial_lng') }};

            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');

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
                center: [initialLat, initialLng],
                zoom: 13,
                layers: [osm]
            });

            // Layer Control
            const baseMaps = {
                "Open Street Map": osm,
                "Google Hybrid": googleHybrid
            };
            L.control.layers(baseMaps).addTo(map);

            // Marker
            let marker = null;

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
            }

            function placeMarker(lat, lng) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    marker.on('dragend', function (e) {
                        const position = marker.getLatLng();
                        updateInputs(position.lat, position.lng);
                    });
                }
                updateInputs(lat, lng);
            }

            // Map Click
            map.on('click', function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
            });

            // Manual Input Change
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

            // Locate Me
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

            // Initialize from old values if present
            if (latInput.value && lngInput.value) {
                const l = parseFloat(latInput.value);
                const n = parseFloat(lngInput.value);
                if (!isNaN(l) && !isNaN(n)) {
                    map.setView([l, n], 16);
                    placeMarker(l, n);
                }
            }
        });
    </script>
@endpush