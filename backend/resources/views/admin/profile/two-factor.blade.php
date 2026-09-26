@extends('layouts.admin')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Two-Factor Authentication (2FA)</h1>
                <p class="text-slate-500 text-sm mt-1">Add an extra layer of security to your account using an authenticator app.</p>
            </div>
            <a href="{{ route('admin.profile') }}" class="flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Profile
            </a>
        </div>

        @if(session('recovery_codes'))
            <div class="mb-6 p-8 bg-amber-50 border-2 border-dashed border-amber-200 rounded-[2rem] text-center">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="shield-alert" class="w-8 h-8 text-amber-600"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Save Your Recovery Codes</h2>
                <p class="text-slate-600 text-sm mb-6 max-w-md mx-auto">Store these codes in a secure password manager. They can be used to recover access to your account if you lose your 2FA device.</p>
                
                <div class="grid grid-cols-2 gap-3 max-w-sm mx-auto mb-6">
                    @foreach(session('recovery_codes') as $code)
                        <div class="bg-white px-4 py-2 rounded-xl font-mono text-sm border border-amber-100 text-slate-700 shadow-sm">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>

                <button onclick="window.print()" class="px-6 py-2.5 bg-slate-800 text-white rounded-xl font-bold text-sm hover:bg-slate-900 transition-all shadow-lg shadow-slate-200 flex items-center mx-auto">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                    Print or Save as PDF
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Status Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center {{ $user->hasTwoFactorEnabled() ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }}">
                        <i data-lucide="{{ $user->hasTwoFactorEnabled() ? 'shield-check' : 'shield-off' }}" class="w-10 h-10"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Status</h3>
                    <p class="text-sm font-medium mb-6">
                        @if($user->hasTwoFactorEnabled())
                            <span class="text-emerald-600">Enabled & Secured</span>
                        @else
                            <span class="text-slate-500">Not Configured</span>
                        @endif
                    </p>

                    @if($user->hasTwoFactorEnabled())
                        <button x-data="" @click="$dispatch('open-modal', 'confirm-2fa-disable')" class="w-full py-3 px-4 bg-red-50 text-red-600 rounded-2xl font-bold text-sm hover:bg-red-100 transition-all flex items-center justify-center">
                            <i data-lucide="power" class="w-4 h-4 mr-2"></i>
                            Disable 2FA
                        </button>
                    @endif
                </div>
            </div>

            <!-- Setup/Instruction Card -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                    @if(!$user->hasTwoFactorEnabled())
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                                <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center mr-3 text-sm">1</span>
                                Scan QR Code
                            </h3>
                            
                            <div class="flex flex-col md:flex-row items-center gap-8 mb-8">
                                <div class="p-4 bg-slate-50 rounded-3xl border border-slate-100">
                                    {!! $qrCode !!}
                                </div>
                                <div class="flex-1 space-y-4">
                                    <p class="text-slate-600 text-sm leading-relaxed">
                                        Scan this QR code with your authenticator app (like Google Authenticator, Authy, or Microsoft Authenticator).
                                    </p>
                                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                                        <p class="text-[10px] uppercase font-bold text-indigo-400 mb-1 tracking-wider">Manual Entry Key</p>
                                        <code class="text-indigo-600 font-mono font-bold text-lg select-all">{{ $secret }}</code>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-100 mb-8">

                            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                                <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center mr-3 text-sm">2</span>
                                Verify Code
                            </h3>

                            <form action="{{ route('admin.profile.2fa.confirm') }}" method="POST" class="max-w-sm">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Authentication Code</label>
                                    <input type="text" name="code" maxlength="6" placeholder="000000" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-mono text-2xl tracking-[0.5em] text-center" required>
                                </div>
                                <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all">
                                    Activate 2FA
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-slate-800 mb-6">Security Best Practices</h3>
                            <div class="space-y-6">
                                <div class="flex items-start p-4 bg-blue-50 rounded-2xl">
                                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 mt-0.5"></i>
                                    <div>
                                        <p class="text-blue-800 font-bold text-sm mb-1">ISO 27001:2022 Compliance</p>
                                        <p class="text-blue-700 text-xs leading-relaxed">Multi-factor authentication is a core requirement for access control (A.9). By enabling 2FA, you are helping ensure the integrity and confidentiality of the revenue collection data.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-4 border border-slate-100 rounded-2xl">
                                        <p class="font-bold text-slate-700 text-sm mb-2 flex items-center">
                                            <i data-lucide="key" class="w-4 h-4 mr-2 text-slate-400"></i>
                                            Backup Keys
                                        </p>
                                        <p class="text-slate-500 text-xs leading-relaxed">Always keep a copy of your recovery codes in a physical or digital vault.</p>
                                    </div>
                                    <div class="p-4 border border-slate-100 rounded-2xl">
                                        <p class="font-bold text-slate-700 text-sm mb-2 flex items-center">
                                            <i data-lucide="smartphone" class="w-4 h-4 mr-2 text-slate-400"></i>
                                            Device Security
                                        </p>
                                        <p class="text-slate-500 text-xs leading-relaxed">Ensure your mobile device has a screen lock (PIN, biometric) enabled.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable Confirmation Modal -->
<div x-data="{ open: false }" @open-modal.window="if($event.detail == 'confirm-2fa-disable') open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white p-8">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-6">
                    <i data-lucide="alert-triangle" class="w-8 h-8 text-red-600"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Disable 2FA?</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-8">This will significantly reduce your account security. You will be required to enter your password to confirm this action.</p>
                
                <form action="{{ route('admin.profile.2fa.disable') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Current Password</label>
                        <input type="password" name="current_password" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" required>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold text-sm hover:bg-slate-200 transition-all">Cancel</button>
                        <button type="submit" class="flex-1 py-4 bg-red-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-red-200 hover:bg-red-700 transition-all">Disable Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
