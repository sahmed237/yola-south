@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Invalid / Rejected Registrations</h1>
    <p class="text-slate-500 text-sm">Review establishments that were rejected during verification.</p>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center shadow-sm">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
        {{ session('success') }}
    </div>
@endif

<div class="mb-6 p-6 bg-white rounded-2xl shadow-sm border border-slate-100" x-data="{ 
    selectedLga: '{{ request('lga') }}',
    selectedWard: '{{ request('ward') }}',
    lgas: {{ Js::from($lgas) }},
    isLoadingWards: false,
    displayWards: [],
    updateWards() {
        this.isLoadingWards = true;
        this.displayWards = [];
        this.selectedWard = '';
        setTimeout(() => {
            const lga = this.lgas.find(l => l.name === this.selectedLga);
            this.displayWards = lga ? lga.wards : [];
            this.isLoadingWards = false;
        }, 400);
    },
    init() {
        if (this.selectedLga) {
            const lga = this.lgas.find(l => l.name === this.selectedLga);
            this.displayWards = lga ? lga.wards : [];
        }
    }
}">
    <form method="GET" action="{{ route('admin.establishments.invalid') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Government ID</label>
            <input type="text" name="unique_id" value="{{ request('unique_id') }}" placeholder="Search ID..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
        </div>

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Establishment Name</label>
            <input type="text" name="name" value="{{ request('name') }}" placeholder="Search name..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
        </div>

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Address / Location</label>
            <div class="relative group">
                <input type="text" name="address" value="{{ request('address') }}" placeholder="Search address..." 
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-500 transition-colors">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Category</label>
            <select name="establishment_type_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                <option value="">All Categories</option>
                @foreach($establishmentTypes as $type)
                    <option value="{{ $type->id }}" {{ request('establishment_type_id') == $type->id ? 'selected' : '' }}>{{ $type->value }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Classification</label>
            <select name="establishment_size_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                <option value="">All Sizes</option>
                @foreach($establishmentSizes as $size)
                    <option value="{{ $size->id }}" {{ request('establishment_size_id') == $size->id ? 'selected' : '' }}>{{ $size->value }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">LGA</label>
            <select name="lga" x-model="selectedLga" @change="updateWards" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                <option value="">All LGAs</option>
                @foreach($lgas as $lga)
                    <option value="{{ $lga->name }}">{{ $lga->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="flex items-center justify-between text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
                <span>Ward</span>
                <i x-show="isLoadingWards" data-lucide="loader-2" class="w-3 h-3 animate-spin text-primary-500" style="display: none;"></i>
            </label>
            <select name="ward" x-model="selectedWard" :disabled="isLoadingWards" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all" :class="{'opacity-50 cursor-wait': isLoadingWards}">
                <option value="" x-text="isLoadingWards ? 'Loading...' : 'All Wards'"></option>
                <template x-for="ward in displayWards" :key="ward.id">
                    <option :value="ward.name" x-text="ward.name" :selected="ward.name === selectedWard"></option>
                </template>
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Per Page</label>
            <select name="per_page" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 Records</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Records</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Records</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-5 py-3 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-900 transition-all flex items-center justify-center gap-2">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Apply
            </button>
            
            @if(request()->anyFilled(['unique_id', 'name', 'address', 'type', 'size', 'lga', 'ward', 'per_page']))
                <a href="{{ route('admin.establishments.invalid') }}" class="px-5 py-3 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                    <th class="px-8 py-5">Establishment</th>
                    <th class="px-8 py-5">Location Context</th>
                    <th class="px-8 py-5">Occupant Information</th>
                    <th class="px-8 py-5">Registered By</th>
                    <th class="px-8 py-5">Rejection Remarks</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($establishments as $establishment)
                <tr class="group hover:bg-slate-50/80 transition-all">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform">
                                <i data-lucide="x-circle" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $establishment->name }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter bg-slate-100 px-1.5 py-0.5 rounded">{{ $establishment->type }}</span>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter bg-slate-100 px-1.5 py-0.5 rounded">{{ $establishment->size }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-700">{{ $establishment->lga }} LGA</p>
                        <p class="text-xs text-slate-400 font-medium">{{ $establishment->ward }} Ward</p>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-700">{{ $establishment->occupant->name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-400 font-medium">{{ $establishment->occupant->phone ?? 'No phone provided' }}</p>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-[10px] font-bold text-slate-500">
                                {{ substr($establishment->creator->name ?? '?', 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-slate-600">{{ $establishment->creator->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        @php
                            $rejectionLog = $establishment->activityLogs->where('action_type', 'rejected')->last();
                        @endphp
                        @if($rejectionLog && $rejectionLog->remarks)
                            <div class="p-3 bg-red-50 border border-red-100 rounded-xl max-w-xs">
                                <p class="text-xs text-red-700 italic">"{{ $rejectionLog->remarks }}"</p>
                                <p class="text-[9px] font-black uppercase tracking-widest text-red-400 mt-2">{{ $rejectionLog->created_at->format('M d, Y') }}</p>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">No remarks provided.</span>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end items-center gap-3">

                            @if($establishment->created_by === auth()->id() || auth()->user()->hasPermissionTo('view all invalid establishment'))
                            <a href="{{ route('admin.establishments.edit', $establishment->id) }}" class="p-3 bg-primary-50 text-primary-600 rounded-2xl hover:bg-primary-600 hover:text-white transition-all shadow-sm group/btn" title="Edit & Re-submit">
                                <i data-lucide="edit-3" class="w-5 h-5 group-hover/btn:scale-110 transition-transform"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-20 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="check-square" class="w-10 h-10 text-slate-200"></i>
                        </div>
                        <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">No Invalid Registrations</p>
                        <p class="text-slate-300 text-xs mt-1">There are currently no rejected establishments to display.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($establishments->hasPages())
    <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-50">
        {{ $establishments->links() }}
    </div>
    @endif
</div>

<div class="mt-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
    <p>Showing {{ $establishments->firstItem() ?? 0 }} to {{ $establishments->lastItem() ?? 0 }} of {{ $establishments->total() }} establishments</p>
    <p>Page {{ $establishments->currentPage() }} of {{ $establishments->lastPage() }}</p>
</div>
@endsection
