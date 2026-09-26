@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Revenue Heads</h1>
        <p class="text-slate-500 text-sm">Define billing amounts, frequencies, and dynamic calculation logic.</p>
    </div>
    <a href="{{ route('admin.revenue-heads.create') }}" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-500/20 flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Revenue Head
    </a>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center shadow-sm">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                    <th class="px-8 py-5">Head Name</th>
                    <th class="px-8 py-5">Agency</th>
                    <th class="px-8 py-5">Base Amount</th>
                    <th class="px-8 py-5">Frequency</th>
                    <th class="px-8 py-5">Dynamic Logic</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($heads as $head)
                <tr class="group hover:bg-slate-50/80 transition-all">
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-800">{{ $head->name }}</p>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-tighter rounded-lg border border-slate-200">
                            {{ $head->agency->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-700">₦{{ number_format($head->amount, 2) }}</p>
                    </td>
                    <td class="px-8 py-6">
                        <span class="text-xs font-medium text-slate-500 capitalize">{{ $head->frequency }}</span>
                    </td>
                    <td class="px-8 py-6">
                        @if($head->sql_rule)
                            <div class="max-w-[200px] truncate">
                                <code class="text-[10px] bg-slate-50 text-indigo-600 px-2 py-1 rounded border border-slate-100 font-mono">{{ $head->sql_rule }}</code>
                            </div>
                        @else
                            <span class="text-[10px] text-slate-300 italic font-medium">No dynamic logic</span>
                        @endif
                    </td>
                    <td class="px-8 py-6">
                        @if($head->status === 'active')
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-emerald-100">Active</span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-slate-200">Inactive</span>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.revenue-heads.edit', $head->id) }}" class="p-2 bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.revenue-heads.destroy', $head->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this revenue head?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-red-600 hover:border-red-100 hover:bg-red-50 transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-8 py-20 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="calculator" class="w-8 h-8 text-slate-300"></i>
                        </div>
                        <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">No revenue heads defined</p>
                        <p class="text-slate-400 text-xs mt-2">Revenue heads determine how much establishments are billed.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($heads->hasPages())
    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
        {{ $heads->links() }}
    </div>
    @endif
</div>
@endsection
