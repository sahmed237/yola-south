@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">User Profile</h1>
        <p class="text-slate-500 text-sm">Detailed information for {{ $user->name }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-medium flex items-center hover:bg-slate-50 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
            Back to List
        </a>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-medium flex items-center hover:bg-primary-700 transition-colors">
            <i data-lucide="edit-3" class="w-5 h-5 mr-2"></i>
            Edit Profile
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Basic Info Card -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">
            <div class="w-32 h-32 rounded-3xl bg-slate-100 border border-slate-200 mx-auto mb-6 flex items-center justify-center overflow-hidden">
                @if($user->passport)
                    <img src="{{ $user->passport }}" class="w-full h-full object-cover">
                @else
                    <i data-lucide="user" class="w-16 h-16 text-slate-300"></i>
                @endif
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-1">{{ $user->name }}</h2>
            <p class="text-slate-400 text-sm mb-4">{{ $user->email }}</p>
            
            <div class="flex flex-wrap justify-center gap-2 mb-6">
                @foreach($user->roles as $role)
                    <span class="px-3 py-1 bg-primary-50 text-primary-700 text-[10px] font-bold uppercase rounded-lg">
                        {{ str_replace('-', ' ', $role->name) }}
                    </span>
                @endforeach
            </div>

            <div class="pt-6 border-t border-slate-50 grid grid-cols-2 gap-4">
                <div class="text-center">
                    <p class="text-xs text-slate-400 uppercase font-bold mb-1">Status</p>
                    @if($user->is_active)
                        <span class="text-emerald-600 text-sm font-bold flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                            Active
                        </span>
                    @else
                        <span class="text-red-600 text-sm font-bold flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                            Suspended
                        </span>
                    @endif
                </div>
                <div class="text-center border-l border-slate-50">
                    <p class="text-xs text-slate-400 uppercase font-bold mb-1">Verified</p>
                    @if($user->email_verified_at)
                        <span class="text-indigo-600 text-sm font-bold flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-1.5"></i>
                            Yes
                        </span>
                    @else
                        <span class="text-amber-600 text-sm font-bold flex items-center justify-center">
                            <i data-lucide="alert-circle" class="w-4 h-4 mr-1.5"></i>
                            No
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Contact Details</h3>
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3 shrink-0">
                        <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Phone Number</p>
                        <p class="text-sm text-slate-700 font-medium">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3 shrink-0">
                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Residential Address</p>
                        <p class="text-sm text-slate-700 font-medium">{{ $user->address ?? 'Not provided' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Activity & Permissions -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
                <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-primary-500"></i>
                Designated Areas of Operation
            </h3>
            
            @if($user->hasRole('super-admin'))
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-600 text-sm">
                    As a Super Admin, this user has operational access to all LGAs and Wards.
                </div>
            @elseif($user->designatedAreas->isEmpty())
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 text-sm italic">
                    No designated areas assigned yet.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($user->designatedAreas->groupBy('lga_id') as $lgaId => $areas)
                        @php
                            $lga = $areas->first()->lga;
                            $hasFullLga = $areas->contains('ward_id', null);
                        @endphp
                        @if($lga)
                        <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50">
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                                <span class="font-bold text-slate-800 text-sm">{{ $lga->name }}</span>
                                @if($hasFullLga)
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-wider rounded-full">
                                        Full LGA
                                    </span>
                                @endif
                            </div>
                            
                            @if(!$hasFullLga)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($areas as $area)
                                        @if($area->ward)
                                            <span class="px-2.5 py-1 bg-white border border-slate-150 text-slate-600 text-[10px] font-medium rounded-lg">
                                                {{ $area->ward->name }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-500 font-medium">All wards in this LGA are accessible.</p>
                            @endif
                        </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Account Permissions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($user->getAllPermissions() as $permission)
                    <div class="flex items-center p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-2 h-2 rounded-full bg-primary-500 mr-3"></div>
                        <span class="text-sm font-medium text-slate-700">{{ ucwords(str_replace('_', ' ', $permission->name)) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Account Activity</h3>
            <div class="space-y-6 relative before:absolute before:left-4 before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
                <div class="relative pl-10">
                    <div class="absolute left-0 top-0 w-8 h-8 rounded-full bg-white border-2 border-primary-500 flex items-center justify-center z-10">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5 text-primary-500"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Account Created</p>
                        <p class="text-xs text-slate-400">{{ $user->created_at->format('M d, Y @ H:i') }}</p>
                    </div>
                </div>
                @if($user->email_verified_at)
                <div class="relative pl-10">
                    <div class="absolute left-0 top-0 w-8 h-8 rounded-full bg-white border-2 border-indigo-500 flex items-center justify-center z-10">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-indigo-500"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Email Verified</p>
                        <p class="text-xs text-slate-400">{{ $user->email_verified_at->format('M d, Y @ H:i') }}</p>
                    </div>
                </div>
                @endif
                <div class="relative pl-10">
                    <div class="absolute left-0 top-0 w-8 h-8 rounded-full bg-white border-2 border-slate-300 flex items-center justify-center z-10">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800 text-slate-400 italic">No recent login activity found.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
