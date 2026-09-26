@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">FAQ Management</h1>
        <p class="text-slate-500 text-sm">Create, edit, and organize the taxpayer-facing Frequently Asked Questions.</p>
    </div>
    
    <div>
        <a href="{{ route('admin.faqs.create') }}" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-200 flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New FAQ
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl text-xs font-black uppercase tracking-wider">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
    <!-- Search Bar & Filters -->
    <div class="mb-8 flex flex-col md:flex-row items-center gap-4">
        <form action="{{ route('admin.faqs.index') }}" method="GET" class="w-full md:w-96 relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search FAQs..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-semibold text-slate-700">
            <div class="absolute left-4 top-3.5 text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
        </form>
    </div>

    <!-- FAQs Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 text-left">
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest w-16">Sort</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Question</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Answer</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    <th class="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($faqs as $faq)
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 text-xs font-bold text-slate-500">
                            <span class="px-2 py-1 bg-slate-100 rounded-md font-mono">#{{ $faq->sort_order }}</span>
                        </td>
                        <td class="py-4 pr-4">
                            <span class="text-xs font-black text-slate-800 block line-clamp-2" title="{{ $faq->question }}">
                                {{ $faq->question }}
                            </span>
                        </td>
                        <td class="py-4 pr-4">
                            <span class="text-xs text-slate-500 font-medium block line-clamp-2" title="{{ $faq->answer }}">
                                {{ $faq->answer }}
                            </span>
                        </td>
                        <td class="py-4">
                            @if($faq->is_published)
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                    Published
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-slate-50 rounded-xl transition-all">
                                    <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                                </a>
                                
                                <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-slate-50 rounded-xl transition-all">
                                        <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i data-lucide="help-circle" class="w-12 h-12 mb-4 opacity-20"></i>
                                <p class="text-xs font-black uppercase tracking-widest">No FAQs found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $faqs->links() }}
    </div>
</div>
@endsection
