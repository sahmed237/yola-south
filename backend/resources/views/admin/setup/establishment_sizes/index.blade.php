@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Establishment Sizes</h1>
        <p class="text-slate-500 text-sm">Configure standard dimensions with keys, values, and visibility status.</p>
    </div>
    <button onclick="openModal('create-modal')" class="px-6 py-3 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-xl shadow-primary-500/20 flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Establishment Size
    </button>
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

@if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl">
        <ul class="list-disc list-inside text-sm font-medium">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                    <th class="px-8 py-5">Key / Value</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="px-8 py-5">Audit Context</th>
                    <th class="px-8 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($sizes as $size)
                <tr class="group hover:bg-slate-50/80 transition-all">
                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-800">{{ $size->value }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $size->key }}</p>
                    </td>
                    <td class="px-8 py-6">
                        @if($size->status)
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-tighter rounded-lg border border-emerald-100">Active</span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-tighter rounded-lg border border-slate-100">Inactive</span>
                        @endif
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Created:</span>
                                <span class="text-xs font-medium text-slate-600">{{ $size->creator->name ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Updated:</span>
                                <span class="text-xs font-medium text-slate-600">{{ $size->updater->name ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="editSize({{ $size->id }}, '{{ $size->key }}', '{{ $size->value }}', {{ $size->status ? 'true' : 'false' }})" class="p-2.5 bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('admin.setup.establishment-sizes.destroy', $size->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this establishment size? It will only be deleted if it is not assigned to any establishments.');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2.5 bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-red-600 hover:border-red-100 hover:bg-red-50 transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-20 text-center">
                        <i data-lucide="maximize" class="w-10 h-10 text-slate-200 mx-auto mb-4"></i>
                        <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">No establishment sizes configured</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="create-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden">
        <form action="{{ route('admin.setup.establishment-sizes.store') }}" method="POST">
            @csrf
            <div class="px-10 pt-10 pb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">New Establishment Size</h3>
                <p class="text-sm text-slate-500 mb-8">Define establishment dimension details.</p>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Key (Identifier)</label>
                        <input type="text" name="key" required class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-slate-700" placeholder="e.g. small-kiosk">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Value (Display Name)</label>
                        <input type="text" name="value" required class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-slate-700" placeholder="e.g. Small (Kiosk)">
                    </div>
                    <div class="flex items-center gap-3 ml-1">
                        <input type="checkbox" name="status" id="create-status" checked class="w-5 h-5 rounded-lg border-slate-200 text-primary-600 focus:ring-primary-500/20">
                        <label for="create-status" class="text-sm font-bold text-slate-600">Active / Visible</label>
                    </div>
                </div>
            </div>
            <div class="px-10 py-8 bg-slate-50/50 flex gap-4">
                <button type="button" onclick="closeModal('create-modal')" class="flex-1 px-6 py-4 bg-white border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-50 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-6 py-4 bg-primary-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/20">Create Size</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden">
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            <div class="px-10 pt-10 pb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Edit Establishment Size</h3>
                <p class="text-sm text-slate-500 mb-8">Update dimension details.</p>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Key (Identifier)</label>
                        <input type="text" name="key" id="edit-key" required class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Value (Display Name)</label>
                        <input type="text" name="value" id="edit-value" required class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm font-bold text-slate-700">
                    </div>
                    <div class="flex items-center gap-3 ml-1">
                        <input type="checkbox" name="status" id="edit-status" class="w-5 h-5 rounded-lg border-slate-200 text-primary-600 focus:ring-primary-500/20">
                        <label for="edit-status" class="text-sm font-bold text-slate-600">Active / Visible</label>
                    </div>
                </div>
            </div>
            <div class="px-10 py-8 bg-slate-50/50 flex gap-4">
                <button type="button" onclick="closeModal('edit-modal')" class="flex-1 px-6 py-4 bg-white border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-50 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20">Update Size</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function editSize(id, key, value, status) {
        const form = document.getElementById('edit-form');
        form.action = `/admin/setup/establishment-sizes/${id}`;
        document.getElementById('edit-key').value = key;
        document.getElementById('edit-value').value = value;
        document.getElementById('edit-status').checked = status;
        openModal('edit-modal');
    }
</script>
@endsection
