@extends('layouts.auth')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
    <div class="min-h-screen flex items-center justify-center p-4">
        <div
            class="max-w-3xl w-full bg-white rounded-[2.5rem] shadow-2xl shadow-primary-500/10 border border-primary-50/50 p-12 relative overflow-hidden">

            <!-- Background Accents -->
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>

            <div class="relative text-center mb-12">
                <div
                    class="w-20 h-20 bg-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-primary-500/20 transform -rotate-3">
                    <i data-lucide="shield-check" class="w-10 h-10 text-white"></i>
                </div>
                <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">Setup Mandatory 2FA</h2>
                <p class="text-slate-500 text-sm leading-relaxed max-w-md mx-auto">
                    To comply with our ISO 27001 security standards, you must enable Two-Factor Authentication before
                    accessing the dashboard.
                </p>
            </div>

            @if(session('recovery_codes'))
                <div
                    class="mb-10 p-8 bg-emerald-50 border-2 border-dashed border-emerald-200 rounded-[2rem] text-center animate-in fade-in zoom-in duration-500">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="shield-check" class="w-8 h-8 text-emerald-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">2FA Activated Successfully!</h3>
                    <p class="text-slate-600 text-sm mb-6">These are your emergency recovery codes. **Save them now**, you will
                        not see them again.</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
                        @foreach(session('recovery_codes') as $code)
                            <div
                                class="bg-white px-3 py-2 rounded-xl font-mono text-sm border border-emerald-100 text-slate-700 shadow-sm">
                                {{ $code }}</div>
                        @endforeach
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button onclick="window.print()"
                            class="px-8 py-4 bg-slate-800 text-white rounded-2xl font-bold text-sm shadow-lg hover:bg-slate-900 transition-all flex items-center justify-center">
                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Codes
                        </button>
                        <a href="{{ route('admin.dashboard') }}"
                            class="px-8 py-4 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-500/20 hover:bg-primary-700 transition-all flex items-center justify-center">
                            Continue to Dashboard <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
                    <!-- Step 1: Scan -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-10 h-10 bg-primary-50 text-primary-600 rounded-xl flex items-center justify-center font-black">
                                1</div>
                            <h4 class="font-bold text-slate-800">Scan QR Code</h4>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100 flex justify-center">
                            {!! $qrCode !!}
                        </div>
                        <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                            <p class="text-[10px] uppercase font-bold text-indigo-400 mb-1 tracking-wider">Manual Setup Key</p>
                            <code class=" text-gray-900 font-mono font-bold text-sm select-all">{{ $secret }}</code>
                        </div>
                    </div>

                    <!-- Step 2: Verify -->
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-10 h-10 bg-primary-50 text-primary-600 rounded-xl flex items-center justify-center font-black">
                                2</div>
                            <h4 class="font-bold text-slate-800">Verify & Activate</h4>
                        </div>

                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 mb-4">
                            <div class="flex items-start">
                                <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600 mr-2 mt-0.5"></i>
                                <div>
                                    <p class="text-amber-800 font-bold text-[11px] uppercase tracking-wider mb-1">Important:
                                        Recovery Codes</p>
                                    <p class="text-amber-700 text-xs leading-relaxed">
                                        After activation, you will be given <strong>8 recovery codes</strong>. You must save
                                        these securely. They are the only way to access your account if you lose your phone.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p class="text-slate-500 text-sm leading-relaxed">
                            Enter the 6-digit code generated by your authenticator app to complete the setup.
                        </p>

                        <form action="{{ route('security.2fa.confirm') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <input type="text" name="code" maxlength="6" autofocus placeholder="000000"
                                    class="w-full px-5 py-5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-primary-500/10 focus:border-indigo-500 transition-all font-mono text-3xl tracking-[0.5em] text-center"
                                    required>
                            </div>
                            <button type="submit"
                                class="w-full py-5 bg-primary-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary-500/20 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                                Enable 2FA & Access Dashboard
                            </button>
                        </form>

                        <div class="pt-6 border-t border-slate-50">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest flex items-center mx-auto">
                                    <i data-lucide="log-out" class="w-3 h-3 mr-2"></i> Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection