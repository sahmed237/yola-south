@extends('layouts.admin')

@section('title', $market->name . ' - Market Details')

@section('content')
<div x-data="{ addShopModal: false, editMarketModal: false }">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <a href="{{ route('admin.markets.index') }}" class="hover:text-emerald-700">Markets</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold">{{ $market->name }}</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $market->name }}</h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $market->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ ucfirst(str_replace('_', ' ', $market->status)) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ $market->code }} &middot; {{ $market->ward_name ?? 'Yola South Ward' }} &middot; {{ $market->address ?? 'Main Market Street' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('create shop')
            <button @click="addShopModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add shop unit
            </button>
            @endcan
            @can('edit market')
            <a href="{{ route('admin.markets.edit', $market) }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="pencil" class="w-4 h-4 text-slate-500"></i>
                Edit market
            </a>
            @endcan
            <a href="{{ route('admin.markets.index') }}" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 text-xs font-semibold rounded-lg">
                Back
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Blocks</span>
            <div class="text-xl font-bold text-slate-900 mt-1 font-mono">{{ $market->blocks_count }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Units</span>
            <div class="text-xl font-bold text-slate-900 mt-1 font-mono">{{ $shopsCount }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Occupied</span>
            <div class="text-xl font-bold text-emerald-700 mt-1 font-mono">{{ $occupiedCount }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Vacant</span>
            <div class="text-xl font-bold text-slate-700 mt-1 font-mono">{{ $vacantCount }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Occupancy Rate</span>
            <div class="text-xl font-bold text-slate-900 mt-1 font-mono">{{ $occupancyRate }}%</div>
        </div>
    </div>

    <!-- Shop Units in Market -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Shop Units in {{ $market->name }}</h2>
            <span class="text-xs text-slate-500 font-mono">{{ $shopsCount }} units configured</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Shop ID</th>
                        <th class="py-3 px-3">Location / Unit</th>
                        <th class="py-3 px-3">Size</th>
                        <th class="py-3 px-3">Occupant</th>
                        <th class="py-3 px-3 text-right">Monthly Rent</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($market->shops as $shop)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">
                            {{ $shop->shop_code }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-semibold text-slate-800">{{ $shop->block_name }}</span> &middot; Unit {{ $shop->shop_number }}
                        </td>
                        <td class="py-3 px-3 text-slate-600 font-mono">
                            {{ $shop->size }}
                        </td>
                        <td class="py-3 px-3">
                            @if($shop->current_occupant_name)
                                <div class="font-semibold text-slate-900">{{ $shop->current_occupant_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $shop->current_occupant_phone }}</div>
                            @else
                                <span class="text-slate-400 italic">&mdash; Vacant &mdash;</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                            {{ $shop->formatted_monthly_rent }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($shop->status === 'occupied')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Occupied</span>
                            @elseif($shop->status === 'vacant')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Vacant</span>
                            @elseif($shop->status === 'arrears')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">In Arrears</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">{{ ucfirst($shop->status) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.shops.show', $shop) }}" class="text-slate-400 hover:text-emerald-600" title="Details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @can('edit shop')
                                <a href="{{ route('admin.shops.edit', $shop) }}" class="text-slate-400 hover:text-blue-600" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            No shops registered under this market yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Add Shop Unit -->
    <div x-show="addShopModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="addShopModal = false" class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Add Unit to {{ $market->name }}</h3>
                <button @click="addShopModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.shops.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="market_id" value="{{ $market->id }}">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Block *</label>
                        <input type="text" name="block_name" required placeholder="e.g. Block A" class="w-full text-xs rounded-lg border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit Number *</label>
                        <input type="text" name="shop_number" required placeholder="e.g. A01" class="w-full text-xs rounded-lg border-slate-200">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Dimensions / Size *</label>
                        <input type="text" name="size" value="3.0 × 4.0 m" required class="w-full text-xs rounded-lg border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Type *</label>
                        <select name="type" class="w-full text-xs rounded-lg border-slate-200">
                            <option value="Lock-up shop">Lock-up shop</option>
                            <option value="Open stall">Open stall</option>
                            <option value="Warehouse">Warehouse</option>
                            <option value="Kiosk">Kiosk</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Monthly Rent (₦) *</label>
                    <input type="number" step="100" name="monthly_rent" value="15000" required class="w-full text-xs font-mono rounded-lg border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Initial Status *</label>
                    <select name="status" class="w-full text-xs rounded-lg border-slate-200">
                        <option value="vacant">Vacant</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="addShopModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
