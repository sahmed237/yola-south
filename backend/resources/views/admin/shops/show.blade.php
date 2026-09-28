@extends('layouts.admin')

@section('title', 'Unit ' . $shop->shop_code . ' Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <a href="{{ route('admin.shops.index') }}" class="hover:text-emerald-700">Shop inventory</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold">{{ $shop->shop_code }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight font-mono">{{ $shop->shop_code }}</h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $shop->status === 'occupied' ? 'bg-emerald-100 text-emerald-800' : ($shop->status === 'vacant' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-800') }}">
                    {{ ucfirst($shop->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ $shop->market->name }} &middot; {{ $shop->block_name }} &middot; Unit {{ $shop->shop_number }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('edit shop')
            <a href="{{ route('admin.shops.edit', $shop) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                Edit unit
            </a>
            @endcan
            <a href="{{ route('admin.shops.index') }}" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 text-xs font-semibold rounded-lg">
                Back to inventory
            </a>
        </div>
    </div>

    <!-- Shop Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Unit Specs -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Unit Specifications</h2>
            <dl class="space-y-3 text-xs">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Market:</dt>
                    <dd class="font-bold text-slate-900"><a href="{{ route('admin.markets.show', $shop->market) }}" class="text-emerald-700 hover:underline">{{ $shop->market->name }}</a></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Ward:</dt>
                    <dd class="font-medium text-slate-900">{{ $shop->market->ward_name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Block & Unit No:</dt>
                    <dd class="font-bold text-slate-900">{{ $shop->block_name }}, Unit {{ $shop->shop_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Unit Type:</dt>
                    <dd class="font-medium text-slate-900">{{ $shop->type }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Dimensions / Size:</dt>
                    <dd class="font-mono font-bold text-slate-900">{{ $shop->size }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Monthly Rent:</dt>
                    <dd class="font-mono font-bold text-slate-900">{{ $shop->formatted_monthly_rent }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Annual Rent:</dt>
                    <dd class="font-mono font-bold text-slate-900">{{ $shop->formatted_annual_rent }}</dd>
                </div>
            </dl>
        </div>

        <!-- Occupant Standing -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Current Occupant & Standing</h2>
            @if($shop->current_occupant_name)
            <dl class="space-y-3 text-xs">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Occupant Name:</dt>
                    <dd class="font-bold text-slate-900">{{ $shop->current_occupant_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Phone Number:</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $shop->current_occupant_phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">NIN / BVN:</dt>
                    <dd class="font-mono text-slate-900">{{ $shop->current_occupant_nin ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Rental Standing:</dt>
                    <dd>
                        @if($shop->status === 'occupied')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Current / Paid</span>
                        @elseif($shop->status === 'arrears')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">In Arrears</span>
                        @endif
                    </dd>
                </div>
                @if($shop->currentAllocation)
                <div class="flex justify-between pt-2 border-t border-slate-100">
                    <dt class="text-slate-500">Allocation File:</dt>
                    <dd>
                        <a href="{{ route('admin.allocations.show', $shop->currentAllocation) }}" class="font-mono font-bold text-emerald-700 hover:underline">
                            {{ $shop->currentAllocation->application_no }}
                        </a>
                    </dd>
                </div>
                @endif
            </dl>
            @else
            <div class="py-8 text-center">
                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mx-auto mb-2">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-semibold text-slate-600">Unit is currently vacant</p>
                <p class="text-[11px] text-slate-400 mt-1">Available for allocation through council online shop application portal.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Allocation History -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Allocation & Application History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Reference</th>
                        <th class="py-3 px-3">Applicant</th>
                        <th class="py-3 px-3">Trade</th>
                        <th class="py-3 px-3">Stage</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($shop->allocations as $alloc)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $alloc->application_no }}</td>
                        <td class="py-3 px-3 font-semibold text-slate-900">{{ $alloc->applicant_name }}</td>
                        <td class="py-3 px-3 text-slate-600">{{ $alloc->trade_type }}</td>
                        <td class="py-3 px-3 font-mono">{{ $alloc->stage }} of 7</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $alloc->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($alloc->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <a href="{{ route('admin.allocations.show', $alloc) }}" class="text-slate-400 hover:text-emerald-700 font-bold">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400">No previous allocation records for this unit.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
