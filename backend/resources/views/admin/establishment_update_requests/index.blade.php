@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Establishment Update Requests</h1>
    <p class="text-slate-500 text-sm">Review and authorize requests to modify active establishment information.</p>
</div>

<div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                    <th class="px-8 py-5">Establishment Info</th>
                    <th class="px-8 py-5">Requested By</th>
                    <th class="px-8 py-5">Reason</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($requests as $request)
                <tr class="group hover:bg-slate-50/80 transition-all">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                                <i data-lucide="store" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $request->establishment->name }}</p>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ $request->establishment->unique_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-[10px] font-bold text-slate-500">
                                {{ substr($request->requester->name, 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-slate-600">{{ $request->requester->name }}</span>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-xs text-slate-600 italic leading-relaxed max-w-md">"{{ $request->reason }}"</p>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <a href="{{ route('admin.establishment-update-requests.show', $request->id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-800 hover:text-white transition-all shadow-sm">
                            Review Request
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-20 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="inbox" class="w-10 h-10 text-slate-200"></i>
                        </div>
                        <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">No pending update requests</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($requests->hasPages())
    <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-50">
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection
