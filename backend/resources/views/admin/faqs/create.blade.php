@extends('layouts.admin')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.faqs.index') }}" class="p-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-2xl transition-all shadow-sm">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Add New FAQ</h1>
        <p class="text-slate-500 text-sm">Create a new Frequently Asked Question entry for the taxpayer portal.</p>
    </div>
</div>

<div class="max-w-3xl bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
    <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Question -->
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Question</label>
            <textarea name="question" rows="2" placeholder="e.g. How do I download my payment receipt?" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-700">{{ old('question') }}</textarea>
            @error('question')
                <p class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Answer -->
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Answer</label>
            <textarea name="answer" rows="5" placeholder="Provide a detailed, clear answer to the taxpayer's question..." required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-700">{{ old('answer') }}</textarea>
            @error('answer')
                <p class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Sort Order & Publish Switch -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 font-bold">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" required min="0" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-slate-700">
                @error('sort_order')
                    <p class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center pt-6">
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    <span class="ml-3 text-xs font-black uppercase tracking-wider text-slate-600">Publish Immediately</span>
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.faqs.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-200 font-bold">
                Create FAQ
            </button>
        </div>
    </form>
</div>
@endsection
