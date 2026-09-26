@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Welcome Back, {{ auth()->user()->name }}</h1>
    <p class="text-slate-500 text-sm">Here's what's happening with the revenue collection today.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Establishments -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center">
        <div class="p-3 bg-blue-100 rounded-2xl mr-4 text-blue-600">
            <i data-lucide="store" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Establishments</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_establishments']) }}</p>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center">
        <div class="p-3 bg-amber-100 rounded-2xl mr-4 text-amber-600">
            <i data-lucide="clock" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase">Pending Approvals</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['pending_approvals']) }}</p>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center">
        <div class="p-3 bg-emerald-100 rounded-2xl mr-4 text-emerald-600">
            <i data-lucide="banknote" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase">Total Revenue</p>
            <p class="text-2xl font-bold text-slate-800">₦{{ number_format($stats['total_revenue'], 2) }}</p>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center">
        <div class="p-3 bg-purple-100 rounded-2xl mr-4 text-purple-600">
            <i data-lucide="users" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase">System Users</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_users']) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Establishments Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Recent Establishment Registrations</h3>
            <a href="{{ route('admin.establishments.index') }}" class="text-sm font-medium text-primary-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase bg-slate-50">
                        <th class="px-6 py-3">Establishment Name</th>
                        <th class="px-6 py-3">LGA/Ward</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentEstablishments as $establishment)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $establishment->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $establishment->lga }} / {{ $establishment->ward }}</td>
                        <td class="px-6 py-4">
                            @if($establishment->status == 'pending')
                                <span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-lg">Pending</span>
                            @elseif($establishment->status == 'approved')
                                <span class="px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-lg">Approved</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-lg">Rejected</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400 text-sm">No recent registrations.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Recent Payments</h3>
            <a href="#" class="text-sm font-medium text-primary-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase bg-slate-50">
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Establishment</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentPayments as $payment)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-bold text-slate-700">₦{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $payment->establishment->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-lg">Success</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400 text-sm">No recent payments.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
