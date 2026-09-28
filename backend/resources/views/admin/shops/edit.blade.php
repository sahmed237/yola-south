@extends('layouts.admin')

@section('title', 'Edit Unit ' . $shop->shop_code)

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <a href="{{ route('admin.shops.index') }}" class="hover:text-emerald-700">Shop inventory</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <a href="{{ route('admin.shops.show', $shop) }}" class="hover:text-emerald-700">{{ $shop->shop_code }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold">Edit</span>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
            <div>
                <h1 class="text-lg font-bold text-slate-900">Edit Shop Unit</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ $shop->market->name }} &middot; {{ $shop->shop_code }}</p>
            </div>
            <span class="text-xs font-mono font-bold px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700">{{ $shop->shop_code }}</span>
        </div>

        <form action="{{ route('admin.shops.update', $shop) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Market *</label>
                <select name="market_id" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach($markets as $m)
                    <option value="{{ $m->id }}" {{ old('market_id', $shop->market_id) == $m->id ? 'selected' : '' }}>
                        {{ $m->name }} ({{ $m->ward_name }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Block Name *</label>
                    <input type="text" name="block_name" value="{{ old('block_name', $shop->block_name) }}" required class="w-full text-xs rounded-lg border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit Number *</label>
                    <input type="text" name="shop_number" value="{{ old('shop_number', $shop->shop_number) }}" required class="w-full text-xs rounded-lg border-slate-200">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Size *</label>
                    <input type="text" name="size" value="{{ old('size', $shop->size) }}" required class="w-full text-xs rounded-lg border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Type *</label>
                    <select name="type" class="w-full text-xs rounded-lg border-slate-200">
                        <option value="Lock-up shop" {{ old('type', $shop->type) === 'Lock-up shop' ? 'selected' : '' }}>Lock-up shop</option>
                        <option value="Open stall" {{ old('type', $shop->type) === 'Open stall' ? 'selected' : '' }}>Open stall</option>
                        <option value="Warehouse" {{ old('type', $shop->type) === 'Warehouse' ? 'selected' : '' }}>Warehouse</option>
                        <option value="Kiosk" {{ old('type', $shop->type) === 'Kiosk' ? 'selected' : '' }}>Kiosk</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Monthly Rent (₦) *</label>
                    <input type="number" step="100" name="monthly_rent" value="{{ old('monthly_rent', $shop->monthly_rent) }}" required class="w-full text-xs font-mono rounded-lg border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status *</label>
                    <select name="status" class="w-full text-xs rounded-lg border-slate-200">
                        <option value="vacant" {{ old('status', $shop->status) === 'vacant' ? 'selected' : '' }}>Vacant</option>
                        <option value="occupied" {{ old('status', $shop->status) === 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="arrears" {{ old('status', $shop->status) === 'arrears' ? 'selected' : '' }}>In arrears</option>
                        <option value="reserved" {{ old('status', $shop->status) === 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="maintenance" {{ old('status', $shop->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Occupant Name</label>
                    <input type="text" name="current_occupant_name" value="{{ old('current_occupant_name', $shop->current_occupant_name) }}" class="w-full text-xs rounded-lg border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Occupant Phone</label>
                    <input type="text" name="current_occupant_phone" value="{{ old('current_occupant_phone', $shop->current_occupant_phone) }}" class="w-full text-xs font-mono rounded-lg border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full text-xs rounded-lg border-slate-200">{{ old('notes', $shop->notes) }}</textarea>
            </div>

            <div class="pt-5 border-t border-slate-200 flex items-center justify-between">
                <div>
                    @can('delete shop')
                    @if($shop->status !== 'occupied')
                    <button type="button" onclick="if(confirm('Are you sure you want to delete this shop unit?')) document.getElementById('delete-shop-form').submit();" class="text-xs text-red-600 hover:text-red-800 font-semibold">
                        Delete Unit
                    </button>
                    @endif
                    @endcan
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.shops.show', $shop) }}" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm">Update Unit</button>
                </div>
            </div>
        </form>

        <form id="delete-shop-form" action="{{ route('admin.shops.destroy', $shop) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
