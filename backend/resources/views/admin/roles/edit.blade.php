@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Edit Role</h1>
        <p class="text-slate-500 text-sm">Update permissions for the {{ ucwords(str_replace('-', ' ', $role->name)) }} role.</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-medium flex items-center hover:bg-slate-50 transition-colors">
        <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
        Back to List
    </a>
</div>

<div class="max-w-4xl">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-8">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Role Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                    class="w-full max-w-md px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all @error('name') border-red-500 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-6">Assign Permissions</label>
                
                <div class="space-y-8">
                    @foreach($permissions->groupBy('group') as $group => $groupPermissions)
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center">
                            <span class="bg-slate-100 px-2 py-1 rounded-md mr-3">{{ $group ?: 'General' }}</span>
                            <div class="h-px bg-slate-100 flex-grow"></div>
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($groupPermissions as $permission)
                            <label class="flex items-center p-4 rounded-2xl border border-slate-100 bg-slate-50/30 cursor-pointer hover:border-primary-200 hover:bg-white transition-all shadow-sm group">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                    class="w-5 h-5 text-primary-600 border-slate-300 rounded-lg focus:ring-primary-500 transition-all">
                                <span class="ml-3 text-sm font-bold text-slate-700 group-hover:text-primary-700 transition-colors">
                                    {{ ucwords(str_replace(['.', '-'], ' ', $permission->name)) }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('permissions') <p class="mt-2 text-xs text-red-500 font-bold italic">{{ $message }}</p> @enderror
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 shadow-lg shadow-primary-200 transition-all">
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
