@extends('layouts.admin')

@section('title', 'Markets and Shops - Commercial Registry')

@section('content')
<div x-data="{ addMarketModal: false }">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <span>YSLG-IMRS</span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span>Commercial registry</span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold">Markets and shops</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200/80 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Markets and shops</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                A digital inventory of every Council market, block, shop and stall &mdash; the basis for both shop rent and the daily stall tax.
            </p>
        </div>
        <div class="flex items-center flex-wrap gap-2.5">
            @can('create market')
            <button @click="addMarketModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add market
            </button>
            @endcan
            <a href="{{ route('admin.shops.index') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                Shop inventory
            </a>
            <a href="{{ route('admin.allocations.index') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="file-check" class="w-4 h-4 text-slate-500"></i>
                Allocations
            </a>
        </div>
    </div>

    <!-- KPIs Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Markets -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Markets</span>
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $totalMarkets }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Across Yola South Wards</div>
            </div>
        </div>

        <!-- Shops & Stalls -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Shops & stalls</span>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalShops) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Numbered municipal units</div>
            </div>
        </div>

        <!-- Occupancy -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Occupancy</span>
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $occupancyRate }}%</span>
                </div>
                <!-- Progress Meter -->
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $occupancyRate) }}%"></div>
                </div>
                <div class="text-[11px] text-slate-400 mt-1.5">{{ number_format($occupiedShops) }} occupied &middot; {{ number_format($vacantShops) }} vacant</div>
            </div>
        </div>

        <!-- Market Revenue YTD -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Market revenue &mdash; YTD</span>
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">₦{{ number_format($totalRevenueYtd / 1000000, 1) }}<small class="text-sm font-bold text-slate-500">m</small></div>
                <div class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    <span>Collections active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Register + Schematic Map -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Market Register Table (Span 2) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Market register</h2>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">{{ $markets->total() }} records</span>
                </div>

                <!-- Search & Filters -->
                <form method="GET" action="{{ route('admin.markets.index') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search market or ward..." class="text-xs rounded-lg border-slate-200 pl-8 pr-3 py-1.5 w-48 sm:w-56 focus:ring-emerald-500 focus:border-emerald-500">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                    </div>
                    @if(request()->anyFilled(['search', 'ward_id', 'status']))
                        <a href="{{ route('admin.markets.index') }}" class="text-xs text-slate-500 hover:text-red-600 px-2 py-1">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Market</th>
                            <th class="py-3 px-3">Ward</th>
                            <th class="py-3 px-3 text-right">Blocks</th>
                            <th class="py-3 px-3 text-right">Units</th>
                            <th class="py-3 px-3 text-right">Occupied</th>
                            <th class="py-3 px-3 text-right">Occupancy</th>
                            <th class="py-3 px-4 text-right">Revenue YTD</th>
                            <th class="py-3 px-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($markets as $market)
                        @php
                            $mTotal = $market->shops_count ?? $market->shops->count();
                            $mOcc = $market->occupied_units;
                            $mPct = $mTotal > 0 ? round(($mOcc / $mTotal) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group cursor-pointer" onclick="window.location='{{ route('admin.markets.show', $market) }}'">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">{{ $market->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $market->code }}</div>
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-600">
                                {{ $market->ward_name ?? ($market->ward?->name ?? '—') }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-600">
                                {{ $market->blocks_count }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($mTotal) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-600">
                                {{ number_format($mOcc) }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="font-mono text-[11px] font-semibold text-slate-700">{{ $mPct }}%</span>
                                    <div class="w-12 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $mPct < 85 ? 'bg-amber-500' : 'bg-emerald-600' }}" style="width: {{ $mPct }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                ₦{{ number_format($market->revenue_ytd / 1000000, 1) }}m
                            </td>
                            <td class="py-3 px-3 text-center" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.markets.show', $market) }}" class="p-1 text-slate-400 hover:text-emerald-600 transition-colors" title="View Market">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    @can('edit market')
                                    <a href="{{ route('admin.markets.edit', $market) }}" class="p-1 text-slate-400 hover:text-blue-600 transition-colors" title="Edit Market">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                No markets registered yet. Click "Add market" to create one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200 text-slate-800">
                        <tr>
                            <td class="py-3 px-4">{{ $markets->count() }} markets on page</td>
                            <td></td>
                            <td class="py-3 px-3 text-right font-mono">{{ $markets->sum('blocks_count') }}</td>
                            <td class="py-3 px-3 text-right font-mono">{{ number_format($totalShops) }}</td>
                            <td class="py-3 px-3 text-right font-mono">{{ number_format($occupiedShops) }}</td>
                            <td class="py-3 px-3 text-right font-mono">{{ $occupancyRate }}%</td>
                            <td class="py-3 px-4 text-right font-mono">₦{{ number_format($totalRevenueYtd / 1000000, 1) }}m</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($markets->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $markets->links() }}
            </div>
            @endif
        </div>

        <!-- Revenue Points — Schematic Map Panel (matching UI/index.html line 1405) -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Revenue points &mdash; schematic</h2>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $markets->count() }} key hubs</span>
                </div>

                <!-- PostGIS Map Mockup SVG from UI/index.html -->
                <div class="w-full bg-[#e8efe7] rounded-xl border border-slate-200 overflow-hidden relative p-2 shadow-inner">
                    <svg viewBox="0 0 420 280" class="w-full h-auto drop-shadow-sm" role="img" aria-label="Schematic map of revenue points across Yola South wards">
                        <rect width="420" height="280" fill="#e8efe7"/>
                        <path d="M40 30 L300 18 L390 92 L360 230 L150 262 L28 190 Z" fill="#ffffff" stroke="#c3cfc1" stroke-width="1.5"/>
                        <path d="M150 22 L168 258" stroke="#dce4db" stroke-width="1" fill="none"/>
                        <path d="M32 120 L388 104" stroke="#dce4db" stroke-width="1" fill="none"/>
                        <path d="M60 200 L250 150 L370 160" stroke="#c9d4c7" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        
                        <!-- Wards Labels -->
                        <text class="text-[10px] font-bold fill-slate-400" x="86" y="52">Ngurore</text>
                        <text class="text-[10px] font-bold fill-slate-400" x="228" y="46">Adarawo</text>
                        <text class="text-[10px] font-bold fill-slate-400" x="70" y="160">Namtari</text>
                        <text class="text-[10px] font-bold fill-slate-400" x="252" y="132">Makama</text>
                        <text class="text-[10px] font-bold fill-slate-400" x="215" y="240">Toungo</text>
                        <text class="text-[10px] font-bold fill-slate-400" x="316" y="206">Bako</text>
                        
                        <!-- Market Pins -->
                        <g class="cursor-pointer transition-transform hover:scale-125" title="Ngurore Central Market">
                            <circle cx="112" cy="74" r="7" fill="#16824a" stroke="#ffffff" stroke-width="2"/>
                            <title>Ngurore Central Market</title>
                        </g>
                        <g class="cursor-pointer transition-transform hover:scale-125" title="Adarawo Market">
                            <circle cx="262" cy="70" r="7" fill="#16824a" stroke="#ffffff" stroke-width="2"/>
                            <title>Adarawo Market</title>
                        </g>
                        <g class="cursor-pointer transition-transform hover:scale-125" title="Makama Lock-up Shops">
                            <circle cx="286" cy="144" r="7" fill="#16824a" stroke="#ffffff" stroke-width="2"/>
                            <title>Makama Lock-up Shops</title>
                        </g>
                        <g class="cursor-pointer transition-transform hover:scale-125" title="Namtari Market">
                            <circle cx="96" cy="182" r="7" fill="#16824a" stroke="#ffffff" stroke-width="2"/>
                            <title>Namtari Market</title>
                        </g>
                        <g class="cursor-pointer transition-transform hover:scale-125" title="Bole Yolde Pate Market">
                            <circle cx="196" cy="222" r="7" fill="#16824a" stroke="#ffffff" stroke-width="2"/>
                            <title>Bole Yolde Pate Market</title>
                        </g>
                        <g class="cursor-pointer transition-transform hover:scale-125" title="Yolde Kohi Cattle Market">
                            <circle cx="336" cy="178" r="7" fill="#16824a" stroke="#ffffff" stroke-width="2"/>
                            <title>Yolde Kohi Cattle Market</title>
                        </g>
                        <g class="cursor-pointer" title="Adarawo Motor Park">
                            <circle cx="240" cy="104" r="5" fill="#2a78d6" stroke="#ffffff" stroke-width="2"/>
                            <title>Adarawo Motor Park</title>
                        </g>
                        <g class="cursor-pointer" title="Abattoir / Slaughter slab">
                            <circle cx="148" cy="132" r="5" fill="#8f5a00" stroke="#ffffff" stroke-width="2"/>
                            <title>Abattoir / Slaughter slab</title>
                        </g>
                    </svg>
                </div>

                <p class="text-[11.5px] text-slate-500 mt-3 leading-relaxed">
                    Schematic view of municipal market registries. In production this layer binds directly to PostGIS geometries: shops, revenue points, collection boundaries and ward allocations are geo-referenced.
                </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-600">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#16824a]"></span> Central Markets</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#2a78d6]"></span> Motor Parks</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#8f5a00]"></span> Abattoirs</span>
            </div>
        </div>
    </div>

    <!-- Modal: Add Market -->
    <div x-show="addMarketModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="addMarketModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Register New Market</h3>
                <button @click="addMarketModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.markets.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Market Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Adarawo Central Market" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Market Code *</label>
                        <input type="text" name="code" required placeholder="e.g. MKT-ADR-007" class="w-full text-xs font-mono rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Total Blocks *</label>
                        <input type="number" name="blocks_count" min="1" value="4" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ward</label>
                        <select name="ward_id" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Select Ward --</option>
                            @foreach($wards as $ward)
                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status *</label>
                        <select name="status" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="under_renovation">Under Renovation</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Address / Location</label>
                    <input type="text" name="address" placeholder="e.g. Adarawo Ward Main Road, Yola South" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description / Notes</label>
                    <textarea name="description" rows="2" placeholder="Brief details about the market, types of trade, etc." class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="addMarketModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm">Save Market</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
