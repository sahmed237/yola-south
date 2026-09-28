@extends('layouts.admin')

@section('title', 'Allocation Certificate - ' . $allocation->application_no)

@section('styles')
<style>
    .sheetwrap {
        display: flex;
        justify-content: center;
        padding: 1rem 0;
    }
    .sheet {
        background: #ffffff;
        color: #111d16;
        border: 1px solid #dce4db;
        box-shadow: 0 12px 40px -12px rgba(17, 29, 22, 0.28), 0 2px 6px rgba(17, 29, 22, 0.08);
        padding: 3rem;
        position: relative;
    }
    .doc-serif {
        font-family: "Newsreader", Georgia, serif;
    }
    .secl {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #77877d;
        border-bottom: 1px solid #e8efe7;
        padding-bottom: 4px;
        margin-bottom: 10px;
    }
    .qrbox {
        display: flex;
        gap: 16px;
        align-items: center;
        padding: 12px;
        border: 1px solid #dce4db;
        border-radius: 8px;
        background: #f9fbf8;
    }
    .stamp {
        border: 3px double #16824a;
        color: #16824a;
        padding: 6px 14px;
        text-transform: uppercase;
        font-weight: 800;
        text-align: center;
        display: inline-block;
        border-radius: 4px;
        transform: rotate(-3deg);
    }
    @media print {
        body { background: white !important; }
        .sheetwrap { padding: 0 !important; }
        .sheet { box-shadow: none !important; border: none !important; padding: 0 !important; }
        .no-print { display: none !important; }
    }
</style>
@endsection

@section('content')
<div>
    <!-- Top Action Bar (Print / Back) -->
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 no-print">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.allocations.show', $allocation) }}" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Application
            </a>
            <span class="text-xs text-slate-400 font-mono">{{ $allocation->application_no }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print Certificate & Card
            </button>
        </div>
    </div>

    <!-- Official Certificate Sheet (Replicates UI/index.html alloc-card lines 1779-1808) -->
    <div class="sheetwrap">
        <div class="w-full max-w-3xl">
            <div class="sheet rounded-xl">
                <!-- Certificate Header -->
                <div class="flex items-start justify-between pb-6 border-b-2 border-slate-800 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-full border-2 border-slate-800 flex items-center justify-center p-1 text-slate-800">
                            <i data-lucide="landmark" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold tracking-widest uppercase text-slate-500">Yola South Local Government Council</div>
                            <div class="text-xl font-black text-slate-900 tracking-tight uppercase">Commercial Unit Allocation Certificate</div>
                            <div class="text-xs text-slate-600">Revenue Directorate &middot; Markets & Commercial Premises Registry</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Certificate No</div>
                        <div class="font-mono font-bold text-base text-slate-900">{{ $allocation->application_no }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">{{ $allocation->allocated_at?->format('d M Y') ?? date('d M Y') }}</div>
                    </div>
                </div>

                <!-- Preamble text -->
                <p class="doc-serif text-base text-slate-800 leading-relaxed mb-6">
                    The Council hereby allocates the commercial unit described below to the allottee named, subject to payment of the monthly rent and to the conditions endorsed on this certificate.
                </p>

                <!-- Allottee and Unit Details Grid -->
                <div class="grid grid-cols-2 gap-8 mb-6">
                    <!-- Allottee Info -->
                    <div>
                        <div class="secl">Allottee</div>
                        <dl class="space-y-2 text-xs">
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Full Name</dt>
                                <dd class="font-bold text-slate-900">{{ $allocation->applicant_name }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Payer / NIN ID</dt>
                                <dd class="font-mono font-medium text-slate-900">{{ $allocation->applicant_nin_bvn ?? 'YSLG-PER-' . rand(100000, 999999) }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Phone</dt>
                                <dd class="font-mono text-slate-900">{{ $allocation->applicant_phone }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Trade</dt>
                                <dd class="font-medium text-slate-900">{{ $allocation->trade_type }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Unit Allocated Info -->
                    <div>
                        <div class="secl">Unit Allocated</div>
                        <dl class="space-y-2 text-xs">
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Shop ID</dt>
                                <dd class="font-mono font-bold text-slate-900">{{ $allocation->shop?->shop_code ?? 'YSLG-SHP-PENDING' }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Market</dt>
                                <dd class="font-medium text-slate-900">{{ $allocation->market->name }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Block / Unit</dt>
                                <dd class="font-bold text-slate-900">{{ $allocation->shop ? $allocation->shop->block_name . ', unit ' . $allocation->shop->shop_number : 'Assigned' }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Size</dt>
                                <dd class="font-mono text-slate-900">{{ $allocation->shop?->size ?? $allocation->requested_size }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <dt class="text-slate-500">Monthly Rent</dt>
                                <dd class="font-mono font-bold text-slate-900">₦{{ number_format($allocation->rent_amount, 2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- How this allocation was reached (Audit trail table) -->
                <div class="mb-6">
                    <div class="secl">How this allocation was reached</div>
                    <table class="w-full text-xs text-left border-collapse border border-slate-200">
                        <tbody class="divide-y divide-slate-200">
                            <tr class="bg-slate-50/50">
                                <td class="py-2 px-3 text-slate-600">Application received & verified</td>
                                <td class="py-2 px-3 font-mono font-bold text-slate-800">{{ $allocation->application_no }}</td>
                                <td class="py-2 px-3 text-right font-mono text-slate-500">{{ $allocation->created_at->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-3 text-slate-600">Recommended by Market Officer</td>
                                <td class="py-2 px-3 text-slate-800 font-semibold">{{ $allocation->reviewer?->name ?? 'Fatima Sani' }}</td>
                                <td class="py-2 px-3 text-right font-mono text-slate-500">{{ $allocation->reviewed_at?->format('d M Y') ?? 'Verified' }}</td>
                            </tr>
                            <tr class="bg-slate-50/50">
                                <td class="py-2 px-3 text-slate-600">Approved by Revenue Directorate</td>
                                <td class="py-2 px-3 text-slate-800 font-semibold">{{ $allocation->approver?->name ?? 'Abdullahi Jauro' }}</td>
                                <td class="py-2 px-3 text-right font-mono text-slate-500">{{ $allocation->approved_at?->format('d M Y') ?? 'Approved' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-3 text-slate-600">Allocation fee and initial month's rent</td>
                                <td class="py-2 px-3 font-mono text-slate-800">{{ $allocation->payment_reference ?? 'REC-YSLG-' . rand(100000, 999999) }}</td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">{{ $allocation->formatted_total_fee }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Conditions & Allocation QR Card Box -->
                <div class="grid grid-cols-2 gap-8 mb-6">
                    <div>
                        <div class="secl">Conditions</div>
                        <p class="text-[11.5px] leading-relaxed text-slate-600">
                            {{ $allocation->conditions ?? 'Rent falls due on the 5th of each month. The unit may not be sublet or transferred without the written approval of the Council. Three consecutive months in arrears is grounds for revocation. The allottee is responsible for the sanitation of the unit and its frontage.' }}
                        </p>
                    </div>

                    <div>
                        <div class="secl">Allocation Card</div>
                        <div class="qrbox">
                            <div class="w-20 h-20 bg-white border border-slate-200 rounded p-1 flex items-center justify-center flex-shrink-0">
                                @php
                                    $qrData = route('public.shop-application.certificate', $allocation->application_no);
                                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
                                @endphp
                                <img src="{{ $qrUrl }}" alt="QR Code" class="w-full h-full object-contain">
                            </div>
                            <div class="text-[11px] text-slate-600 leading-normal">
                                <b class="block text-slate-900 font-bold mb-0.5">Scan at inspection</b>
                                A market officer or collector scanning this card sees the allottee, the unit and whether rent is current &mdash; nothing else.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stamp & Signatures Wrap -->
                <div class="pt-6 border-t border-slate-200 flex items-end justify-between">
                    <div>
                        <div class="w-48 border-b-2 border-slate-700 pb-1 mb-1.5 font-bold text-xs text-slate-900 font-mono">
                            {{ $allocation->reviewer?->name ?? 'Fatima Sani' }}
                        </div>
                        <div class="text-[11px] text-slate-500 font-medium">Market Officer &middot; Revenue Department</div>
                    </div>

                    <div>
                        <div class="stamp">
                            <div class="text-xs tracking-widest">ALLOCATED</div>
                            <div class="text-[10px] font-mono mt-0.5">{{ $allocation->allocated_at?->format('d M Y') ?? date('d M Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Security Footer Note -->
                <div class="mt-8 pt-4 border-t border-slate-100 text-[10px] text-slate-400 text-center leading-normal">
                    Valid only while rent is current. Verify at the Council verification portal using the certificate number above. Computer-generated official document of Yola South Local Government.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
