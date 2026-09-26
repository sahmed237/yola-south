@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center">
        <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center mr-4 shadow-lg shadow-indigo-100">
            <i data-lucide="history" class="w-7 h-7 text-white"></i>
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Security History</h1>
            <p class="text-slate-500 text-sm">Reviewing access logs for <span class="font-bold text-slate-700">{{ $user->name }}</span></p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm flex items-center hover:bg-slate-50 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Users
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- User Profile Summary -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden sticky top-8">
            <div class="h-24 bg-gradient-to-br from-indigo-500 to-primary-600"></div>
            <div class="px-6 pb-6 -mt-12 text-center">
                <div class="inline-block p-1 bg-white rounded-3xl mb-4 shadow-sm">
                    @if($user->passport)
                        <img src="{{ $user->passport }}" class="w-24 h-24 rounded-2xl object-cover">
                    @else
                        <div class="w-24 h-24 rounded-2xl bg-slate-100 flex items-center justify-center">
                            <i data-lucide="user" class="w-10 h-10 text-slate-300"></i>
                        </div>
                    @endif
                </div>
                <h3 class="text-lg font-bold text-slate-800">{{ $user->name }}</h3>
                <p class="text-sm text-slate-400 mb-4">{{ $user->email }}</p>
                
                <div class="flex flex-wrap justify-center gap-1 mb-6">
                    @foreach($user->roles as $role)
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-black rounded-md uppercase tracking-wider">{{ $role->name }}</span>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 gap-3 pt-6 border-t border-slate-50">
                    <div class="text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1">Total Logs</p>
                        <p class="text-xl font-bold text-slate-700">{{ $logs->total() }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1">Status</p>
                        <span class="px-2 py-0.5 {{ $user->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} text-[10px] font-black rounded-md uppercase tracking-wider">
                            {{ $user->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs List -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50">
                <h2 class="text-lg font-bold text-slate-800">Account Access Log</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Context</th>
                            <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Environment</th>
                            <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-all">
                            <td class="px-6 py-4">
                                @php
                                    $actionMeta = match($log->action) {
                                        'login' => ['color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'icon' => 'log-in'],
                                        'logout' => ['color' => 'text-slate-600', 'bg' => 'bg-slate-100', 'icon' => 'log-out'],
                                        'failed_login' => ['color' => 'text-red-600', 'bg' => 'bg-red-50', 'icon' => 'alert-circle'],
                                        'password_change' => ['color' => 'text-indigo-600', 'bg' => 'bg-indigo-50', 'icon' => 'key'],
                                        default => ['color' => 'text-slate-400', 'bg' => 'bg-slate-50', 'icon' => 'info']
                                    };
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg {{ $actionMeta['bg'] }} flex items-center justify-center mr-3">
                                        <i data-lucide="{{ $actionMeta['icon'] }}" class="w-4 h-4 {{ $actionMeta['color'] }}"></i>
                                    </div>
                                    <span class="text-sm font-bold {{ $actionMeta['color'] }} uppercase tracking-tight">{{ str_replace('_', ' ', $log->action) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-mono text-slate-600">{{ $log->ip_address }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $log->isp ?? 'ISP details hidden' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-slate-600">{{ $log->platform }} / {{ $log->browser }}</span>
                                    <span class="text-[10px] text-slate-400 truncate max-w-[200px]" title="{{ $log->user_agent }}">{{ $log->user_agent }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-700">{{ $log->created_at->format('M d, Y') }}</span>
                                    <span class="text-[11px] text-slate-400 font-medium">{{ $log->created_at->format('h:i A') }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="shield-off" class="w-12 h-12 mx-auto mb-4 text-slate-200"></i>
                                <p>No security logs found for this user.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
            <div class="p-6 border-t border-slate-50">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
