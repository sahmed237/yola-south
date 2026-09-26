@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Edit User</h1>
        <p class="text-slate-500 text-sm">Update profile details and roles for {{ $user->name }}.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-medium flex items-center hover:bg-slate-50 transition-colors">
        <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
        Back to List
    </a>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Profile Photo -->
                <div class="flex flex-col items-center sm:flex-row sm:space-x-6 mb-2">
                    <div class="w-24 h-24 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden mb-4 sm:mb-0" id="passport-preview">
                        @if($user->passport)
                            <img src="{{ $user->passport }}" class="w-full h-full object-cover">
                        @else
                            <i data-lucide="user" class="w-10 h-10 text-slate-300"></i>
                        @endif
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <label for="passport" class="block text-sm font-medium text-slate-700 mb-2">Update Passport Photo</label>
                        <input type="file" name="passport" id="passport" accept="image/*" onchange="previewImage(this)"
                            class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-all">
                        <p class="mt-2 text-xs text-slate-400">PNG, JPG or JPEG. Max 2MB.</p>
                        @error('passport') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all @error('name') border-red-500 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all @error('email') border-red-500 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all @error('phone') border-red-500 @enderror"
                            placeholder="08012345678">
                        @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Residential Address</label>
                        <textarea name="address" id="address" rows="1"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all @error('address') border-red-500 @enderror"
                            placeholder="Enter physical address">{{ old('address', $user->address) }}</textarea>
                        @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center">
                        <i data-lucide="lock" class="w-4 h-4 mr-2"></i>
                        Change Password
                    </h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">New Password</label>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all @error('password') border-red-500 @enderror"
                                placeholder="Leave blank to keep current">
                            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-3">Assign Roles <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                        <label class="flex items-center p-4 rounded-xl border border-slate-100 bg-white cursor-pointer hover:border-primary-200 transition-all shadow-sm">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                {{ $user->hasRole($role->name) ? 'checked' : '' }}
                                class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                            <span class="ml-3 text-sm font-medium text-slate-700">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100" x-data="{
                    init() {
                        @foreach($lgas as $lga)
                            @php
                                $isLgaFull = isset($fullySelectedLgaIds) && in_array($lga->id, $fullySelectedLgaIds);
                            @endphp
                            if ({{ $isLgaFull ? 'true' : 'false' }}) {
                                this.disableWards({{ $lga->id }}, true);
                            }
                        @endforeach
                    },
                    toggleLga(lgaId, event) {
                        let isChecked = event.target.checked;
                        this.disableWards(lgaId, isChecked);
                        let wardCheckboxes = document.querySelectorAll('.ward-checkbox-' + lgaId);
                        wardCheckboxes.forEach(cb => {
                            cb.checked = isChecked;
                        });
                    },
                    disableWards(lgaId, disabled) {
                        let wardCheckboxes = document.querySelectorAll('.ward-checkbox-' + lgaId);
                        wardCheckboxes.forEach(cb => {
                            cb.disabled = disabled;
                        });
                    }
                }">
                    <h3 class="text-sm font-bold text-slate-800 mb-2 flex items-center">
                        <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-primary-500"></i>
                        Designated Areas of Operation
                    </h3>
                    <p class="text-xs text-slate-500 mb-6">Assign the LGAs and Wards this user is designated to work on. Assigning an entire LGA will automatically cover all its Wards.</p>
                    
                    <div class="grid grid-cols-1 gap-6">
                        @foreach($lgas as $lga)
                        @php
                            $isLgaDesignated = isset($designatedLgaIds) && in_array($lga->id, $designatedLgaIds);
                            $isLgaFull = isset($fullySelectedLgaIds) && in_array($lga->id, $fullySelectedLgaIds);
                        @endphp
                        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                                <span class="font-bold text-slate-800 text-sm">{{ $lga->name }}</span>
                                <label class="flex items-center text-xs text-primary-600 font-semibold cursor-pointer">
                                    <input type="checkbox" 
                                           name="designated_areas[{{ $lga->id }}][full]" 
                                           value="1" 
                                           {{ $isLgaFull ? 'checked' : '' }}
                                           @change="toggleLga({{ $lga->id }}, $event)"
                                           class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500 mr-2 lga-checkbox-{{ $lga->id }}">
                                    Full LGA
                                </label>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($lga->wards as $ward)
                                @php
                                    $isWardDesignated = isset($designatedWardIds) && in_array($ward->id, $designatedWardIds);
                                @endphp
                                <label class="flex items-center p-2 rounded-lg border border-slate-100 bg-slate-50 cursor-pointer hover:border-slate-200 transition-colors text-xs">
                                    <input type="checkbox" 
                                           name="designated_areas[{{ $lga->id }}][wards][]" 
                                           value="{{ $ward->id }}"
                                           {{ ($isWardDesignated || $isLgaFull) ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 text-primary-500 border-slate-300 rounded focus:ring-primary-400 mr-2 ward-checkbox-{{ $lga->id }}">
                                    <span class="text-slate-600 font-medium truncate">{{ $ward->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 shadow-lg shadow-primary-200 transition-all">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('passport-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
