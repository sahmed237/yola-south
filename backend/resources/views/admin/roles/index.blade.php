@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Roles & Permissions</h1>
        <p class="text-slate-500 text-sm">Define access levels for different system users.</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-medium flex items-center hover:bg-primary-700 transition-colors">
        <i data-lucide="shield-plus" class="w-5 h-5 mr-2"></i>
        Add New Role
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($roles as $role)
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col h-full">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-primary-50 rounded-2xl text-primary-600">
                <i data-lucide="shield" class="w-6 h-6"></i>
            </div>
            <div class="flex space-x-1">
                <a href="{{ route('admin.roles.edit', $role->id) }}" class="p-2 text-slate-400 hover:text-primary-600 transition-colors">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
        
        <h3 class="text-lg font-bold text-slate-800 capitalize mb-1">{{ str_replace('-', ' ', $role->name) }}</h3>
        <p class="text-xs text-slate-400 mb-6">Assigned to {{ $role->users_count ?? 0 }} users</p>

        <div class="flex-1">
            <p class="text-xs font-semibold text-slate-400 uppercase mb-3">Permissions</p>
            <div class="flex flex-wrap gap-2">
                @foreach($role->permissions as $permission)
                    <span class="px-2 py-1 text-[10px] font-medium bg-slate-100 text-slate-600 rounded-lg">
                        {{ $permission->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
