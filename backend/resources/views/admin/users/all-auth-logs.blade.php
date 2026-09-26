@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">System Authentication Logs</h1>
        <p class="text-slate-500 text-sm">Detailed history of all security events and access attempts</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm flex items-center hover:bg-slate-50 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Users
        </a>
    </div>
</div>

<div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b border-slate-50 flex items-center justify-between">
        <h2 class="text-lg font-bold text-slate-800">Global Access History</h2>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-slate-50 text-slate-500 text-[11px] font-black rounded-full uppercase tracking-wider">
                {{ $logs->total() }} Records
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">IP Address</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Device / OS</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Browser</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-wider">Date & Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50/50 transition-all">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center mr-3">
                                <span class="text-[10px] font-bold text-indigo-600">{{ substr($log->user->name ?? '?', 0, 2) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700">{{ $log->user->name ?? 'Deleted User' }}</p>
                                <p class="text-[11px] text-slate-400">{{ $log->user->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $badgeClass = match($log->action) {
                                'login' => 'bg-emerald-50 text-emerald-600',
                                'logout' => 'bg-slate-100 text-slate-600',
                                'failed_login' => 'bg-red-50 text-red-600',
                                'password_change' => 'bg-indigo-50 text-indigo-600',
                                default => 'bg-slate-50 text-slate-500'
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                            {{ str_replace('_', ' ', $log->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-slate-600 font-mono">{{ $log->ip_address }}</span>
                            <span class="text-[10px] text-slate-400">{{ $log->isp ?? 'ISP details unavailable' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center text-slate-600">
                            @php
                                $platformIcon = match(strtolower($log->platform)) {
                                    'windows' => 'monitor',
                                    'mac os' => 'laptop',
                                    'linux' => 'terminal',
                                    'ios', 'android' => 'smartphone',
                                    default => 'help-circle'
                                };
                            @endphp
                            <i data-lucide="{{ $platformIcon }}" class="w-4 h-4 mr-2 text-slate-400"></i>
                            <span class="text-sm">{{ $log->platform }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-slate-600">{{ $log->browser }}</span>
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
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                <i data-lucide="shield-alert" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-400">No logs found</h3>
                            <p class="text-slate-400 text-sm">Authentication events will appear here as they occur.</p>
                        </div>
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
@endsection
