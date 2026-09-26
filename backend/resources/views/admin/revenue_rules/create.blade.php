@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.revenue-rules.index') }}" class="inline-flex items-center text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-primary-600 transition-colors mb-4 group">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
        Back to Rules
    </a>
    <h1 class="text-2xl font-bold text-slate-800">New Revenue Rule</h1>
    <p class="text-slate-500 text-sm">Define billing criteria and automated calculation logic.</p>
</div>

<form action="{{ route('admin.revenue-rules.store') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600">
                        <i data-lucide="settings" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Rule Configuration</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Assigned Agency <span class="text-rose-500">*</span></label>
                        <select name="agency_id" required class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700">
                            <option value="">Select Agency</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}>{{ $agency->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rule Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="e.g. Annual Establishment License">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Base Amount (₦) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700 placeholder-slate-300"
                            placeholder="0.00">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Billing Frequency <span class="text-rose-500">*</span></label>
                        <select name="frequency" required class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700">
                            <option value="annual" {{ old('frequency') == 'annual' ? 'selected' : '' }}>Annual</option>
                            <option value="quarterly" {{ old('frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rule Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-bold text-slate-700">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dynamic Calculation Logic (Optional)</label>
                        <textarea name="sql_rule" rows="4" 
                            class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all text-sm font-mono text-indigo-600 placeholder-slate-300"
                            placeholder="type=retail & size=large & inside_metro=true : 25000; default : 5000">{{ old('sql_rule') }}</textarea>
                        <p class="text-[10px] text-slate-400 font-medium ml-1">
                            Format: <code>condition:amount; ... ; default:amount</code>. You can use operators like <code>&amp;</code> or <code>and</code> to combine multiple field parameters!
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-slate-900/20">
                <h3 class="text-lg font-bold mb-4">Rule Summary</h3>
                <p class="text-white/60 text-sm leading-relaxed mb-8">
                    These rules are applied to establishments during invoice generation. Dynamic logic allows for flexible pricing based on establishment attributes.
                </p>
                
                <button type="submit" class="w-full px-8 py-5 bg-emerald-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-500/20 flex items-center justify-center gap-3">
                    Save Revenue Rule
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </div>
            
            <div class="bg-indigo-50 rounded-[2.5rem] p-8 border border-indigo-100">
                <div class="flex items-center gap-3 mb-4 text-indigo-700">
                    <i data-lucide="info" class="w-5 h-5"></i>
                    <h4 class="font-black uppercase tracking-widest text-[10px]">Logic Examples</h4>
                </div>
                <div class="space-y-3">
                    <div class="p-3 bg-white/50 rounded-xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Simple Condition</p>
                        <code class="text-[10px] text-indigo-600 block break-all">type=retail:10000;type=wholesale:50000;default:2000</code>
                    </div>
                    <div class="p-3 bg-white/50 rounded-xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Multi-Attribute Condition</p>
                        <code class="text-[10px] text-indigo-600 block break-all">type=retail &amp; size=large &amp; inside_metro=true:25000;default:5000</code>
                    </div>
                    <div class="p-3 bg-white/50 rounded-xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Available Fields</p>
                        <p class="text-[9px] text-slate-500 leading-normal">
                            <code>type</code>, <code>size</code>, <code>inside_metro</code> (true/false), <code>lga</code>, <code>ward</code>, <code>city</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
