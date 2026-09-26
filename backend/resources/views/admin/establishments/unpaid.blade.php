@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Unpaid Taxes Directory</h1>
    <p class="text-slate-500 text-sm">List of approved establishments with outstanding tax balances and active liabilities.</p>
</div>

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
    <form method="GET" action="{{ route('admin.establishments.unpaid') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-end">
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

        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-5 py-3 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-900 transition-all flex items-center justify-center gap-2">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Apply
            </button>
            
            @if(request()->anyFilled(['unique_id', 'name', 'address', 'lga', 'ward', 'registered_by']))
                <a href="{{ route('admin.establishments.unpaid') }}" class="px-5 py-3 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center justify-center">
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
                    <th class="px-8 py-5 text-right">Assessed Assessment</th>
                    <th class="px-8 py-5 text-right">Total Settled</th>
                    <th class="px-8 py-5 text-right">Outstanding Balance</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($paginated as $establishment)
                <tr class="group hover:bg-slate-50/80 transition-all">
                    <td class="px-8 py-6">
                        <div class="bg-indigo-50 text-indigo-700 px-3 py-2 rounded-xl border border-indigo-100 w-fit shadow-sm">
                            <p class="text-xs font-black font-mono tracking-tighter">{{ $establishment->unique_id }}</p>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-800">{{ $establishment->name }}</p>
                        <p class="text-xs text-slate-400 font-medium lowercase tracking-wide">{{ $establishment->street_address }}, {{ $establishment->lga }}</p>
                    </td>
                    <td class="px-8 py-6 text-right font-semibold text-slate-700">
                        ₦{{ number_format($establishment->total_due, 2) }}
                    </td>
                    <td class="px-8 py-6 text-right font-semibold text-emerald-600">
                        ₦{{ number_format($establishment->total_paid, 2) }}
                    </td>
                    <td class="px-8 py-6 text-right">
                        <span class="px-3 py-1.5 bg-rose-50 border border-rose-100 rounded-xl text-xs font-black text-rose-600 font-bold shadow-sm">
                            ₦{{ number_format($establishment->outstanding_amount, 2) }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end items-center gap-3">
                            <a href="{{ route('admin.establishments.details', $establishment->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-primary-700 transition-all shadow-sm">
                                View Info & Settle
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                            <a href="{{ route('admin.establishments.show', $establishment->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                Print Profile
                                <i data-lucide="printer" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-20 text-center">
                        <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-100 text-emerald-500">
                            <i data-lucide="check-circle-2" class="w-10 h-10"></i>
                        </div>
                        <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">No Unpaid Taxes Found</p>
                        <p class="text-slate-300 text-xs mt-1 italic">All approved establishments in your designated area have settled their balances!</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($paginated->hasPages())
    <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-50">
        {{ $paginated->links() }}
    </div>
    @endif
</div>

<div class="mt-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
    <p>Showing {{ $paginated->firstItem() ?? 0 }} to {{ $paginated->lastItem() ?? 0 }} of {{ $paginated->total() }} establishments with outstanding balance</p>
    <p>Page {{ $paginated->currentPage() }} of {{ $paginated->lastPage() }}</p>
</div>
@endsection
