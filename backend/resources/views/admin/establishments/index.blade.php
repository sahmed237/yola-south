@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Establishment Registrations</h1>
        <p class="text-slate-500 text-sm">Review, approve, and manage establishment identities across the state.</p>
    </div>
    <a href="{{ route('admin.establishments.create') }}" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-500/20 flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Register New Establishment
    </a>
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
        this.selectedWard = ''; // Clear ward when LGA changes
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
    <form method="GET" action="{{ route('admin.establishments.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-end">
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
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Status</label>
            <select name="status" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
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

        @if($canViewAll)
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Registered By</label>
            <select name="registered_by" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                <option value="">All Users</option>
                @foreach($creators as $creator)
                    <option value="{{ $creator->id }}" {{ request('registered_by') == $creator->id ? 'selected' : '' }}>{{ $creator->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

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
            
            @if(request()->anyFilled(['unique_id', 'name', 'address', 'status', 'type', 'size', 'lga', 'ward', 'registered_by']))
                <a href="{{ route('admin.establishments.index') }}" class="px-5 py-3 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center justify-center">
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
                    <th class="px-8 py-5">Government ID</th>
                    <th class="px-8 py-5">Establishment</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="px-8 py-5">Identity Assets</th>
                    <th class="px-8 py-5">Registered By</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($establishments as $establishment)
                <tr class="group hover:bg-slate-50/80 transition-all">
                    <td class="px-8 py-6">
                        @if($establishment->unique_id)
                            <div class="bg-indigo-50 text-indigo-700 px-3 py-2 rounded-xl border border-indigo-100 w-fit">
                                <p class="text-xs font-black font-mono tracking-tighter">{{ $establishment->unique_id }}</p>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">Pending</span>
                        @endif
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->name }}</p>
                        <p class="text-xs text-slate-400 font-medium">{{ $establishment->lga }} LGA / {{ $establishment->ward }} Ward</p>
                    </td>
                    <td class="px-8 py-6">
                        @if($establishment->status === 'approved')
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold uppercase tracking-wider">Approved</span>
                        @elseif($establishment->status === 'rejected')
                            <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold uppercase tracking-wider">Rejected</span>
                        @else
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold uppercase tracking-wider">Pending</span>
                        @endif
                    </td>
                    <td class="px-8 py-6">
                        @if($establishment->qr_code_path)
                            <a href="{{ asset('storage/' . $establishment->qr_code_path) }}" target="_blank" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 transition-colors">
                                <i data-lucide="qr-code" class="w-5 h-5"></i>
                                <span class="text-xs font-bold uppercase tracking-wider">Download QR Code</span>
                            </a>
                        @else
                            <span class="text-xs text-slate-400 italic">Unavailable</span>
                        @endif
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-[10px] font-bold text-slate-500">
                                {{ substr($establishment->creator->name ?? '?', 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-slate-600">{{ $establishment->creator->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end items-center gap-3">
                            <a href="{{ route('admin.establishments.details', $establishment->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-800 hover:text-white transition-all shadow-sm">
                                View Info
                                <i data-lucide="eye" class="w-3 h-3"></i>
                            </a>
                            @if($establishment->status === 'approved')
                                <a href="{{ route('admin.establishments.show', $establishment->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                    Print Profile
                                    <i data-lucide="printer" class="w-3 h-3"></i>
                                </a>
                            @else
                                <span class="text-xs text-slate-400 italic">No Assets</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-20 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="shield" class="w-10 h-10 text-slate-200"></i>
                        </div>
                        <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">No establishments found</p>
                        <p class="text-slate-300 text-xs mt-1">Register a establishment to get started.</p>
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
