@extends('layouts.admin')

@section('title', 'Shop Inventory - Commercial Registry')

@section('content')
<div x-data="{ addUnitModal: false }">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <span>YSLG-IMRS</span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span>Commercial registry</span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold">Shop inventory</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200/80 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Shop inventory</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Every lock-up shop, stall and commercial space, with its allocation, occupant, rent and standing.
            </p>
        </div>
        <div class="flex items-center flex-wrap gap-2.5">
            @can('create shop')
            <button @click="addUnitModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add unit
            </button>
            @endcan
            <a href="{{ route('admin.allocations.index') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="file-check" class="w-4 h-4 text-slate-500"></i>
                Allocations
            </a>
            <a href="{{ route('admin.markets.index') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="store" class="w-4 h-4 text-slate-500"></i>
                Markets & shops
            </a>
        </div>
    </div>

    <!-- Units Panel with Filterbar -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <!-- Filterbar (matching UI/index.html filterbar) -->
        <div class="p-4 border-b border-slate-200 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.shops.index') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Search input & market filter -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Shop ID, number or occupant…" class="w-full text-xs rounded-lg border-slate-200 pl-8 pr-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                    </div>

                    <select name="market_id" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-200 py-1.5 pr-8 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Markets</option>
                        @foreach($markets as $m)
                        <option value="{{ $m->id }}" {{ request('market_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter Chips (matching UI/index.html chips) -->
                <div class="flex items-center flex-wrap gap-1.5">
                    @php $currStatus = request('status', 'all'); @endphp
                    <a href="{{ route('admin.shops.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $currStatus === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        All ({{ $totalCount }})
                    </a>
                    <a href="{{ route('admin.shops.index', array_merge(request()->except('status', 'page'), ['status' => 'occupied'])) }}" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $currStatus === 'occupied' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                        Occupied ({{ $occupiedCount }})
                    </a>
                    <a href="{{ route('admin.shops.index', array_merge(request()->except('status', 'page'), ['status' => 'vacant'])) }}" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $currStatus === 'vacant' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                        Vacant ({{ $vacantCount }})
                    </a>
                    <a href="{{ route('admin.shops.index', array_merge(request()->except('status', 'page'), ['status' => 'arrears'])) }}" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $currStatus === 'arrears' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                        In arrears ({{ $arrearsCount }})
                    </a>

                    <span class="text-xs font-mono text-slate-400 ml-2 hidden sm:inline">
                        {{ $shops->count() }} of {{ number_format($totalCount) }}
                    </span>
                </div>
            </form>
        </div>

        <!-- Inventory Table (matching UI/index.html table) -->
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Shop ID</th>
                        <th class="py-3 px-4">Market</th>
                        <th class="py-3 px-3">Size</th>
                        <th class="py-3 px-4">Occupant</th>
                        <th class="py-3 px-4 text-right">Monthly rent</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($shops as $shop)
                    <tr class="hover:bg-slate-50/80 transition-colors group cursor-pointer" onclick="window.location='{{ route('admin.shops.show', $shop) }}'">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">
                            {{ $shop->shop_code }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-900">{{ $shop->market->name }}</span>
                            <div class="text-[11px] text-slate-400 font-medium">{{ $shop->block_name }} &middot; unit {{ $shop->shop_number }}</div>
                        </td>
                        <td class="py-3 px-3 font-mono text-slate-600">
                            {{ $shop->size }}
                        </td>
                        <td class="py-3 px-4">
                            @if($shop->current_occupant_name)
                                <div class="font-semibold text-slate-900">{{ $shop->current_occupant_name }}</div>
                                @if($shop->current_occupant_phone)
                                <div class="text-[11px] text-slate-400 font-mono">{{ $shop->current_occupant_phone }}</div>
                                @endif
                            @else
                                <span class="text-slate-400 font-mono">&mdash;</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                            {{ $shop->formatted_monthly_rent }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($shop->status === 'occupied')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">occupied</span>
                            @elseif($shop->status === 'vacant')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">vacant</span>
                            @elseif($shop->status === 'arrears')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">arrears</span>
                            @elseif($shop->status === 'reserved')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">reserved</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $shop->status }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.shops.show', $shop) }}" class="text-slate-400 hover:text-emerald-600 p-1" title="View details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @can('edit shop')
                                <a href="{{ route('admin.shops.edit', $shop) }}" class="text-slate-400 hover:text-blue-600 p-1" title="Edit shop">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No shop units found matching the selected filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shops->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $shops->links() }}
        </div>
        @endif
    </div>

    <!-- Modal: Add Shop Unit -->
    <div x-show="addUnitModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="addUnitModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Add Unit to Inventory</h3>
                <button @click="addUnitModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.shops.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Market *</label>
                    <select name="market_id" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Select Market --</option>
                        @foreach($markets as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->ward_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Block Name *</label>
                        <input type="text" name="block_name" required placeholder="e.g. Block B" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit Number *</label>
                        <input type="text" name="shop_number" required placeholder="e.g. B12" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Dimensions / Size *</label>
                        <input type="text" name="size" value="3.0 × 4.0 m" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit Type *</label>
                        <select name="type" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="Lock-up shop">Lock-up shop</option>
                            <option value="Open stall">Open stall</option>
                            <option value="Warehouse">Warehouse</option>
                            <option value="Kiosk">Kiosk</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Monthly Rent (₦) *</label>
                        <input type="number" step="100" name="monthly_rent" value="18000" required class="w-full text-xs font-mono rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Standing / Status *</label>
                        <select name="status" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="vacant">Vacant</option>
                            <option value="occupied">Occupied</option>
                            <option value="arrears">In arrears</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="addUnitModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
