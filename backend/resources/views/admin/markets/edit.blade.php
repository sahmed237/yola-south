@extends('layouts.admin')

@section('title', 'Edit ' . $market->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center text-xs font-semibold text-slate-500 mb-3 space-x-1.5 uppercase tracking-wider">
        <a href="{{ route('admin.markets.index') }}" class="hover:text-emerald-700">Markets</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <a href="{{ route('admin.markets.show', $market) }}" class="hover:text-emerald-700">{{ $market->name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-900 font-bold">Edit</span>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-900">Edit Market Details</h1>
                <p class="text-xs text-slate-500 mt-0.5">Modify information for {{ $market->name }}</p>
            </div>
            <span class="text-xs font-mono font-bold px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700">{{ $market->code }}</span>
        </div>

        <form action="{{ route('admin.markets.update', $market) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Market Name *</label>
                <input type="text" name="name" value="{{ old('name', $market->name) }}" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Market Code *</label>
                    <input type="text" name="code" value="{{ old('code', $market->code) }}" required class="w-full text-xs font-mono rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Total Blocks *</label>
                    <input type="number" name="blocks_count" min="1" value="{{ old('blocks_count', $market->blocks_count) }}" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ward</label>
                    <select name="ward_id" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Select Ward --</option>
                        @foreach($wards as $ward)
                        <option value="{{ $ward->id }}" {{ old('ward_id', $market->ward_id) == $ward->id ? 'selected' : '' }}>{{ $ward->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status *</label>
                    <select name="status" required class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="active" {{ old('status', $market->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $market->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="under_renovation" {{ old('status', $market->status) === 'under_renovation' ? 'selected' : '' }}>Under Renovation</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Address / Street Location</label>
                <input type="text" name="address" value="{{ old('address', $market->address) }}" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full text-xs rounded-lg border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $market->description) }}</textarea>
            </div>

            <div class="pt-5 border-t border-slate-200 flex items-center justify-between">
                <div>
                    @can('delete market')
                    <button type="button" onclick="if(confirm('Are you sure you want to archive this market?')) document.getElementById('delete-market-form').submit();" class="text-xs text-red-600 hover:text-red-800 font-semibold">
                        Archive Market
                    </button>
                    @endcan
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.markets.show', $market) }}" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold rounded-lg">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm">Save Changes</button>
                </div>
            </div>
        </form>

        <form id="delete-market-form" action="{{ route('admin.markets.destroy', $market) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
