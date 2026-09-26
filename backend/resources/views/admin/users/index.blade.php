@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">User Management</h1>
        <p class="text-slate-500 text-sm">Manage platform access and permissions</p>
    </div>
    <div class="flex items-center gap-3">
        @can('users logs')
        <a href="{{ route('admin.users.all-auth-logs') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm flex items-center hover:bg-slate-50 transition-all shadow-sm">
            <i data-lucide="history" class="w-4 h-4 mr-2"></i>
            Auth Logs
        </a>
        @endcan
        <button onclick="window.location.reload()" class="p-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-all shadow-sm">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
        </button>
        @can('users create')
        <a href="{{ route('admin.users.create') }}" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-bold text-sm flex items-center hover:bg-primary-700 transition-all shadow-lg shadow-primary-200">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add User
        </a>
        @endcan
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Total Users</p>
            <h3 class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center">
            <i data-lucide="users" class="w-6 h-6 text-slate-400"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between border-l-4 border-l-emerald-500">
        <div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Active</p>
            <h3 class="text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center">
            <i data-lucide="user-check" class="w-6 h-6 text-emerald-500"></i>
        </div>
    </div>
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between border-l-4 border-l-amber-500">
        <div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Suspended</p>
            <h3 class="text-2xl font-bold text-amber-600">{{ $stats['suspended'] }}</h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center">
            <i data-lucide="user-x" class="w-6 h-6 text-amber-500"></i>
        </div>
    </div>
    <a href="{{ request('view') === 'trash' ? route('admin.users.index') : route('admin.users.index', ['view' => 'trash']) }}" 
       class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between transition-all hover:shadow-md group {{ request('view') === 'trash' ? 'border-primary-500 ring-4 ring-primary-50' : '' }}">
        <div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Trashed</p>
            <h3 class="text-2xl font-bold {{ $stats['trashed'] > 0 ? 'text-red-500' : 'text-slate-400' }}">{{ $stats['trashed'] }}</h3>
        </div>
        <div class="w-12 h-12 rounded-2xl {{ request('view') === 'trash' ? 'bg-primary-600 text-white' : 'bg-red-50 text-red-500' }} flex items-center justify-center group-hover:scale-110 transition-transform">
            <i data-lucide="{{ request('view') === 'trash' ? 'arrow-left' : 'trash-2' }}" class="w-6 h-6"></i>
        </div>
    </a>
</div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50"
         x-data="{ 
            selectedUsers: [],
            isAllSelected: false,
            toggleAll() {
                if (this.selectedUsers.length === {{ $users->count() }}) {
                    this.selectedUsers = [];
                    this.isAllSelected = false;
                } else {
                    this.selectedUsers = [ @foreach($users as $user) '{{ $user->id }}', @endforeach ];
                    this.isAllSelected = true;
                }
            },
            performBulkAction(action) {
                if (this.selectedUsers.length === 0) return;
                if (confirm('Are you sure you want to perform this action on ' + this.selectedUsers.length + ' selected users?')) {
                    this.$refs.bulkActionInput.value = action;
                    this.$refs.bulkIdsInput.value = JSON.stringify(this.selectedUsers);
                    this.$refs.bulkForm.submit();
                }
            }
         }"
         x-init="$watch('selectedUsers', value => isAllSelected = value.length === {{ $users->count() }})">
        
    <!-- Filters Header -->
    <div class="p-6 border-b border-slate-50 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <label class="flex items-center cursor-pointer group">
                <input type="checkbox" class="hidden" @change="toggleAll()" :checked="isAllSelected">
                <div class="w-6 h-6 rounded-full border-2 transition-all flex items-center justify-center mr-3"
                     :class="isAllSelected ? 'bg-primary-600 border-primary-600 shadow-lg shadow-primary-200' : 'border-slate-200 group-hover:border-primary-600'">
                    <i data-lucide="check" class="w-3.5 h-3.5 text-white" x-show="isAllSelected"></i>
                </div>
                <span class="text-sm font-bold text-slate-600">Select All</span>
            </label>

            <!-- Selection Info & Bulk Actions -->
            <div class="flex items-center gap-3 pl-4 border-l border-slate-100" x-show="selectedUsers.length > 0" x-cloak x-transition>
                <div class="px-4 py-2 bg-primary-50 text-primary-600 text-[11px] font-black rounded-xl uppercase tracking-wider border border-primary-100">
                    <span x-text="selectedUsers.length"></span> Selected
                </div>
                
                <div class="relative" x-data="{ bulkOpen: false }">
                    <button @click="bulkOpen = !bulkOpen" @click.away="bulkOpen = false" 
                            class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-2xl font-bold text-sm flex items-center hover:bg-slate-50 transition-all shadow-sm">
                        Bulk Actions
                        <i data-lucide="more-horizontal" class="w-4 h-4 ml-2 text-slate-400"></i>
                    </button>
                    
                    <div x-show="bulkOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden py-2" style="display: none;">
                        
                        @can('users security')
                        <button @click="performBulkAction('require_password')" class="w-full flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors text-left">
                            <i data-lucide="key" class="w-4 h-4 mr-3 text-slate-400"></i>
                            Require Password Change
                        </button>
                        <button @click="performBulkAction('remove_password_req')" class="w-full flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors text-left">
                            <i data-lucide="check-square" class="w-4 h-4 mr-3 text-slate-400"></i>
                            Remove Password Change Req.
                        </button>
                        @endcan

                        @can('users status')
                        <button @click="performBulkAction('activate')" class="w-full flex items-center px-4 py-2.5 text-sm text-emerald-600 hover:bg-slate-50 transition-colors text-left">
                            <i data-lucide="user-check" class="w-4 h-4 mr-3"></i>
                            Activate Users
                        </button>
                        <button @click="performBulkAction('suspend')" class="w-full flex items-center px-4 py-2.5 text-sm text-amber-600 hover:bg-slate-50 transition-colors text-left">
                            <i data-lucide="user-x" class="w-4 h-4 mr-3"></i>
                            Suspend Users
                        </button>
                        @endcan

                        @can('users delete')
                        <div class="h-px bg-slate-50 my-1 mx-2"></div>
                        <button @click="performBulkAction('delete')" class="w-full flex items-center px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors text-left">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
                            Delete Users
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." 
                       class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 transition-all shadow-sm">
            </div>
            <select name="role" onchange="this.form.submit()" class="px-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 transition-all shadow-sm">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucwords($role->name) }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="px-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 transition-all shadow-sm">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
            </select>
        </form>
    </div>

    <!-- Hidden Bulk Action Form -->
    <form x-ref="bulkForm" action="{{ route('admin.users.bulk-action') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="action" x-ref="bulkActionInput">
        <input type="hidden" name="ids" x-ref="bulkIdsInput">
    </form>

    <div class="min-h-[450px]">
        <table class="w-full text-left border-separate border-spacing-0">
            <thead>
                <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50/30">
                    <th class="px-8 py-5 border-b border-slate-50">User</th>
                    <th class="px-8 py-5 border-b border-slate-50 text-center">Role</th>
                    <th class="px-8 py-5 border-b border-slate-50 text-center">Status</th>
                    <th class="px-8 py-5 border-b border-slate-50">Joined Date</th>
                    <th class="px-8 py-5 border-b border-slate-50 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($users as $user)
                <tr class="group hover:bg-slate-50/50 transition-all" :class="selectedUsers.map(id => Number(id)).includes({{ $user->id }}) ? 'bg-slate-50' : ''">
                    <td class="px-8 py-5">
                        <div class="flex items-center">
                            <div class="mr-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="hidden" value="{{ $user->id }}" x-model="selectedUsers">
                                    <div class="w-5 h-5 rounded-full border-2 transition-all flex items-center justify-center"
                                         :class="selectedUsers.map(id => Number(id)).includes({{ $user->id }}) ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200 group-hover:border-emerald-500'">
                                        <i data-lucide="check" class="w-3 h-3 text-white" x-show="selectedUsers.map(id => Number(id)).includes({{ $user->id }})"></i>
                                    </div>
                                </label>
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden mr-4 group-hover:scale-105 transition-transform">
                                @if($user->passport)
                                    <img src="{{ $user->passport }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs font-black text-slate-400 uppercase">{{ substr($user->name, 0, 2) }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800 leading-none mb-1.5">{{ $user->name }}</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                    @if(!$user->email_verified_at)
                                        <span class="px-1.5 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-bold rounded uppercase">Unverified</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex justify-center">
                            @foreach($user->roles as $role)
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-bold rounded-lg uppercase tracking-wide">
                                    {{ str_replace('-', ' ', $role->name) }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        @if(request('view') === 'trash')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-2 animate-pulse"></span>
                                Trashed
                            </span>
                        @elseif($user->locked_until && $user->locked_until->isFuture())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-600" title="Locked until {{ $user->locked_until->format('M d, H:i') }}">
                                <i data-lucide="lock" class="w-3 h-3 mr-1.5"></i>
                                Locked
                            </span>
                        @elseif($user->is_active)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2"></span>
                                Suspended
                            </span>
                        @endif
                    </td>
                    <td class="px-8 py-5 text-sm font-medium text-slate-500">
                        {{ $user->created_at->format('j/n/Y') }}
                    </td>
                    <td class="px-8 py-5 text-right" x-data="{ open: false }">
                        <div class="relative inline-block text-left">
                            <button @click="open = !open" @click.away="open = false" class="p-2 hover:bg-white hover:shadow-sm rounded-xl transition-all text-slate-400 hover:text-slate-600">
                                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 py-2" style="display: none;">
                                
                                @if(request('view') === 'trash')
                                @can('users delete')
                                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="contents">
                                    @csrf
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4 mr-3 text-emerald-400"></i>
                                        Restore Account
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" class="contents" onsubmit="return confirm('PERMANENTLY DELETE this account? This cannot be undone!')">
                                    @csrf @method('DELETE')
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i data-lucide="skull" class="w-4 h-4 mr-3 text-red-400"></i>
                                        Delete Permanently
                                    </button>
                                </form>
                                @endcan
                                @else
                                <a href="{{ route('admin.users.show', $user->id) }}" class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4 mr-3 text-slate-400"></i>
                                    View Profile
                                </a>
                                @can('users logs')
                                <a href="{{ route('admin.users.auth-logs', $user->id) }}" class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                    <i data-lucide="shield" class="w-4 h-4 mr-3 text-slate-400"></i>
                                    Security Logs
                                </a>
                                @endcan

                                @can('users verify')
                                @if(! $user->hasVerifiedEmail())
                                <form action="{{ route('admin.users.resend-verification', $user->id) }}" method="POST" class="contents">
                                    @csrf
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-indigo-600 hover:bg-indigo-50 transition-colors">
                                        <i data-lucide="mail-plus" class="w-4 h-4 mr-3 text-indigo-400"></i>
                                        Resend Verification
                                    </button>
                                </form>
                                @endif
                                @endcan

                                @can('users reset 2fa')
                                @if($user->hasTwoFactorEnabled())
                                <form action="{{ route('admin.users.reset-2fa', $user->id) }}" method="POST" class="contents" onsubmit="return confirm('Are you sure you want to reset 2FA for this user? They will be forced to set it up again on their next login.')">
                                    @csrf
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-amber-600 hover:bg-amber-50 transition-colors">
                                        <i data-lucide="shield-off" class="w-4 h-4 mr-3 text-amber-400"></i>
                                        Reset 2FA
                                    </button>
                                </form>
                                @endif
                                @endcan

                                @can('users unlock')
                                @if($user->locked_until && $user->locked_until->isFuture())
                                <form action="{{ route('admin.users.unlock', $user->id) }}" method="POST" class="contents">
                                    @csrf
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-amber-600 hover:bg-amber-50 transition-colors">
                                        <i data-lucide="unlock" class="w-4 h-4 mr-3 text-amber-400"></i>
                                        Unlock Account
                                    </button>
                                </form>
                                @endif
                                @endcan
                                
                                @can('users edit')
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                    <i data-lucide="shield-check" class="w-4 h-4 mr-3 text-slate-400"></i>
                                    Edit Profile
                                </a>
                                @endcan

                                @can('users logs')
                                <a href="{{ route('admin.users.auth-logs', $user->id) }}" class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                    <i data-lucide="history" class="w-4 h-4 mr-3 text-slate-400"></i>
                                    Auth Logs
                                </a>
                                @endcan

                                @can('users email')
                                <button @click="$dispatch('open-email-modal', { id: {{ $user->id }}, name: '{{ $user->name }}' })" 
                                        class="w-full flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                    <i data-lucide="mail" class="w-4 h-4 mr-3 text-slate-400"></i>
                                    Send Email
                                </button>
                                @endcan

                                <div class="h-px bg-slate-50 my-1 mx-2"></div>

                                @can('users status')
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="contents">
                                    @csrf
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm {{ $user->is_active ? 'text-amber-600' : 'text-emerald-600' }} hover:bg-slate-50 transition-colors">
                                        <i data-lucide="{{ $user->is_active ? 'ban' : 'check-circle' }}" class="w-4 h-4 mr-3"></i>
                                        {{ $user->is_active ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                                @endcan

                                @can('users verify')
                                @if(!$user->email_verified_at)
                                <form action="{{ route('admin.users.verify-email', $user->id) }}" method="POST" class="contents">
                                    @csrf
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-indigo-600 hover:bg-slate-50 transition-colors">
                                        <i data-lucide="check-square" class="w-4 h-4 mr-3"></i>
                                        Confirm Email
                                    </button>
                                </form>
                                @endif
                                @endcan

                                @can('users reset password')
                                <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" class="contents" onsubmit="return confirm('Are you sure you want to reset password?')">
                                    @csrf
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-emerald-500 hover:bg-slate-50 transition-colors">
                                        <i data-lucide="key-round" class="w-4 h-4 mr-3"></i>
                                        Reset Password
                                    </button>
                                </form>
                                @endcan

                                @can('users delete')
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="contents" onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button class="w-full flex items-center px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
                                        Delete
                                    </button>
                                </form>
                                @endcan
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="px-8 py-5 border-t border-slate-50">
        {{ $users->links() }}
    </div>
</div>

<!-- Send Email Modal -->
<div x-data="{ 
        isOpen: false, 
        userId: null, 
        userName: '',
        subject: '',
        body: ''
    }"
    @open-email-modal.window="
        isOpen = true; 
        userId = $event.detail.id; 
        userName = $event.detail.name;
        subject = '';
        body = '';
    "
    x-show="isOpen"
    class="fixed inset-0 z-[100] overflow-y-auto"
    style="display: none;">
    
    <!-- Backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="isOpen = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- Modal Content -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full max-w-2xl bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden">
            
            <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Send Custom Email</h3>
                    <p class="text-sm text-slate-500 mt-1">Recipient: <span class="font-bold text-primary-600" x-text="userName"></span></p>
                </div>
                <button @click="isOpen = false" class="p-2 hover:bg-white rounded-xl transition-colors text-slate-400">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form :action="`/admin/users/${userId}/send-email`" method="POST" class="p-8 space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Subject</label>
                    <input type="text" name="subject" x-model="subject" required
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all"
                           placeholder="Enter email subject...">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Message Body</label>
                    <textarea name="body" x-model="body" rows="8" required
                              class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all resize-none"
                              placeholder="Type your message here..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" @click="isOpen = false" 
                            class="px-6 py-3 border border-slate-200 text-slate-600 rounded-2xl font-bold text-sm hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-8 py-3 bg-primary-600 text-white rounded-2xl font-bold text-sm hover:bg-primary-700 transition-all shadow-lg shadow-primary-200 flex items-center">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
