@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Trash Bin - Users</h1>
        <p class="text-slate-500 text-sm">Manage and restore recently deleted users.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-medium flex items-center hover:bg-slate-50 transition-colors">
        <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
        Back to Active Users
    </a>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-xs font-semibold text-slate-400 uppercase bg-slate-50">
                    <th class="px-6 py-4">Name & Email</th>
                    <th class="px-6 py-4">Deleted At</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold mr-3">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{ $user->deleted_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end space-x-2">
                            <form action="{{ route('admin.users.restore', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-lg transition-colors" title="Restore User">
                                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" onsubmit="return confirm('PERMANENT DELETE: Are you absolutely sure? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition-colors" title="Delete Permanently">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-10 text-center text-slate-400 text-sm">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 opacity-20"></i>
                        The trash is empty.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
        {{ $users->links() }}
    </div>
</div>
@endsection
