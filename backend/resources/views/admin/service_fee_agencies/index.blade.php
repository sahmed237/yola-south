@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Service Fee Setup</h1>
        <p class="text-slate-500 text-sm">Manage payment portal service fee organizations and their sub-accounts.</p>
    </div>
    <a href="{{ route('admin.service-fee-agencies.create') }}" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-500/20 flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Service Fee Org
    </a>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center shadow-sm">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl flex items-center shadow-sm">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3"></i>
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                    <th class="px-8 py-5">Name & Code</th>
                    <th class="px-8 py-5">Service Fee</th>
                    <th class="px-8 py-5">Bank Details</th>
                    <th class="px-8 py-5">Sub-Accounts</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($agencies as $agency)
                <tr class="group hover:bg-slate-50/80 transition-all">
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-800">{{ $agency->name }}</p>
                        <p class="text-[10px] text-slate-400 font-mono tracking-tight">{{ $agency->code }}</p>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-2.5 py-1 bg-primary-50 text-primary-600 text-xs font-bold rounded-lg border border-primary-100">
                            ₦{{ number_format($agency->service_fee_amount, 2) }}
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-xs font-bold text-slate-700">{{ $agency->bank_name }}</p>
                        <p class="text-[10px] text-slate-400">{{ $agency->account_number }}</p>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase w-12">Paystack</span>
                                @if($agency->paystackSubAccount)
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ $agency->paystackSubAccount->subaccount_code }}</span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-400 italic">Not Linked</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase w-12">Monnify</span>
                                @if($agency->monnifySubAccount)
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">{{ $agency->monnifySubAccount->subaccount_code }}</span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-400 italic">Not Linked</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        @if($agency->status)
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-tighter rounded-lg border border-emerald-100">Active</span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-tighter rounded-lg border border-slate-100">Inactive</span>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.service-fee-agencies.show', $agency->id) }}" class="p-2 bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-primary-600 hover:border-primary-100 hover:bg-primary-50 transition-all">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('admin.service-fee-agencies.edit', $agency->id) }}" class="p-2 bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-20 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="percent" class="w-8 h-8 text-slate-300"></i>
                        </div>
                        <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">No service fee organisations configured</p>
                        <p class="text-slate-400 text-xs mt-2">Configure at least one service fee organization to collect processing or administrative fees during checkout.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($agencies->hasPages())
    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
        {{ $agencies->links() }}
    </div>
    @endif
</div>
@endsection
