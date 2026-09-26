@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">System Settings</h1>
        <p class="text-slate-500 text-sm">Configure global platform parameters and integrations.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Tabs -->
        <div class="w-full md:w-64 space-y-2">
            @php
                $tabs = [
                    'general' => ['label' => 'General Settings', 'icon' => 'settings'],
                    'email' => ['label' => 'Email Config', 'icon' => 'mail'],
                    'payments' => ['label' => 'Payment Gateways', 'icon' => 'credit-card'],
                    'notifications' => ['label' => 'Notifications', 'icon' => 'bell'],
                    'security' => ['label' => 'Security', 'icon' => 'shield-check'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
                <a href="{{ route('admin.settings.index', ['group' => $key]) }}"
                    class="flex items-center px-4 py-3 rounded-xl transition-all {{ $group === $key ? 'bg-primary-600 text-white shadow-lg shadow-primary-200 font-semibold' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-100' }}">
                    <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5 mr-3"></i>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <!-- Settings Form -->
        <div class="flex-1 space-y-8">
            @if($group === 'payments')
                @php
                    $gateways = ['paystack', 'monnify', 'remita', 'zainpay'];
                @endphp

                @foreach($gateways as $gateway)
                    @php
                        $gatewaySettings = $settings->filter(fn($s) => str_starts_with($s->key, $gateway));
                    @endphp

                    @if($gatewaySettings->count() > 0)
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center mr-4">
                                        <i data-lucide="credit-card" class="w-5 h-5 text-primary-600"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-800 capitalize">{{ $gateway }}</h2>
                                        <p class="text-xs text-slate-400">Configure {{ $gateway }} integration keys and status.</p>
                                    </div>
                                </div>
                                @php $statusKey = $gateway . '_active';
                                $isActive = $gatewaySettings->where('key', $statusKey)->first()?->value == '1'; @endphp
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-bold {{ $isActive ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="p-8">
                                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                                    @csrf
                                    <input type="hidden" name="group" value="{{ $group }}">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($gatewaySettings as $setting)
                                            <div
                                                class="flex flex-col space-y-2 {{ $setting->type === 'boolean' ? 'col-span-1' : 'col-span-2' }}">
                                                <label for="{{ $setting->key }}" class="text-sm font-semibold text-slate-700">
                                                    {{ ucwords(str_replace(['_', $gateway], [' ', ''], $setting->key)) }}
                                                </label>

                                                @if($setting->type === 'boolean')
                                                    <div class="flex items-center h-12">
                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" name="{{ $setting->key }}" class="sr-only peer" {{ $setting->value == '1' ? 'checked' : '' }}>
                                                            <div
                                                                class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600">
                                                            </div>
                                                            <span
                                                                class="ml-3 text-sm text-slate-500">{{ str_contains($setting->key, 'active') ? 'Enable Gateway' : 'Live Mode' }}</span>
                                                        </label>
                                                    </div>
                                                @else
                                                    <input type="text" id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                        value="{{ $setting->value }}"
                                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500 outline-none transition-all font-mono text-sm"
                                                        placeholder="Enter {{ str_replace('_', ' ', $setting->key) }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-6 pt-6 border-t border-slate-50 flex justify-end">
                                        <button type="submit"
                                            class="px-6 py-2.5 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-900 transition-all text-sm">
                                            Update {{ ucfirst($gateway) }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <h2 class="text-lg font-bold text-slate-800 mb-6 capitalize">{{ $group }} Settings</h2>

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="group" value="{{ $group }}">

                        <div class="space-y-6">
                            @foreach($settings as $setting)
                                <div class="flex flex-col space-y-2">
                                    <label for="{{ $setting->key }}" class="text-sm font-semibold text-slate-700">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                    </label>

                                    @if($setting->key === 'platform_logo')
                                         <div class="flex items-center space-x-6">
                                             <div class="relative group">
                                                 <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center overflow-hidden" id="logo-preview-container">
                                                     @if($setting->value)
                                                         <img src="{{ $setting->value }}" class="w-full h-full object-contain" id="logo-preview-img">
                                                         <button type="button" 
                                                                 onclick="deleteLogo()"
                                                                 class="absolute inset-0 bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-2xl">
                                                             <i data-lucide="trash-2" class="w-6 h-6"></i>
                                                         </button>
                                                     @else
                                                         <i data-lucide="image" class="w-8 h-8 text-slate-300"></i>
                                                     @endif
                                                 </div>
                                             </div>
                                             <div class="flex-1">
                                                 <input type="file" name="{{ $setting->key }}" accept=".png,.jpg,.jpeg,.svg"
                                                     class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-all">
                                                 <p class="mt-2 text-xs text-slate-400">PNG, JPG or SVG. Max 2MB.</p>
                                             </div>
                                         </div>
                                     @elseif($setting->type === 'boolean')
                                        <div class="flex items-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="{{ $setting->key }}" class="sr-only peer" {{ $setting->value == '1' ? 'checked' : '' }}>
                                                <div
                                                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600">
                                                </div>
                                                <span class="ml-3 text-sm text-slate-500">Enabled</span>
                                            </label>
                                        </div>
                                    @elseif($setting->key === 'theme_font_family')
                                        <div x-data="{ font: '{{ $setting->value }}' }">
                                            <select name="{{ $setting->key }}" x-model="font"
                                                class="w-full max-w-md px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500 outline-none transition-all">
                                                @foreach(['Inter', 'Roboto', 'Open Sans', 'Montserrat', 'Poppins', 'Outfit', 'Lato', 'Nunito', 'Raleway', 'Ubuntu', 'Quicksand', 'Fira Sans'] as $font)
                                                    <option value="{{ $font }}">{{ $font }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-4 p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                                                <p class="text-xs text-slate-400 mb-2 font-sans">Font Preview:</p>
                                                <p :style="{ fontFamily: font }" class="text-lg text-slate-800">
                                                    The quick brown fox jumps over the lazy dog.
                                                </p>
                                            </div>
                                        </div>
                                    @elseif(str_contains($setting->key, 'color') || str_contains($setting->key, 'bg'))
                                        <div class="flex items-center space-x-3">
                                            <input type="color" id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                value="{{ $setting->value }}"
                                                class="h-12 w-20 p-1 bg-white border border-slate-200 rounded-xl cursor-pointer">
                                            <input type="text" value="{{ $setting->value }}"
                                                class="w-32 px-4 py-3 rounded-xl border border-slate-200 text-sm font-mono text-slate-500 bg-slate-50"
                                                readonly>
                                        </div>
                                    @elseif($setting->type === 'integer')
                                        <input type="number" id="{{ $setting->key }}" name="{{ $setting->key }}"
                                            value="{{ $setting->value }}"
                                            class="w-full max-w-md px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500 outline-none transition-all">
                                    @else
                                        <input type="text" id="{{ $setting->key }}" name="{{ $setting->key }}"
                                            value="{{ $setting->value }}"
                                            class="w-full max-w-md px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500 outline-none transition-all">
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-10 pt-6 border-t border-slate-100">
                            <button type="submit"
                                class="px-8 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 shadow-lg shadow-primary-200 transition-all">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                @if($group === 'email')
                    <div class="mt-8 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/50 flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center mr-4">
                                <i data-lucide="send" class="w-5 h-5 text-primary-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">Test Configuration</h2>
                                <p class="text-xs text-slate-400">Verify your SMTP settings by sending a test email.</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="flex flex-col md:flex-row gap-4 items-end">
                                <div class="flex-1 space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Recipient Email</label>
                                    <input type="email" id="test_recipient" placeholder="Enter email to receive test" 
                                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500 outline-none transition-all">
                                </div>
                                <button type="button" onclick="sendTestMail()" id="test_mail_btn"
                                        class="px-8 py-3 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-900 transition-all flex items-center">
                                    <i data-lucide="mail" class="w-4 h-4 mr-2"></i>
                                    Send Test
                                </button>
                            </div>
                            <div id="test_mail_result" class="mt-4 hidden p-4 rounded-xl text-sm"></div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
function sendTestMail() {
    const email = document.getElementById('test_recipient').value;
    const btn = document.getElementById('test_mail_btn');
    const result = document.getElementById('test_mail_result');
    
    if (!email) {
        alert('Please enter a recipient email address');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Sending...';
    lucide.createIcons();
    
    result.classList.add('hidden');
    
    fetch('{{ route("admin.settings.test-mail") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        result.classList.remove('hidden');
        if (data.success) {
            result.className = 'mt-4 p-4 rounded-xl text-sm bg-emerald-50 text-emerald-700 border border-emerald-100';
            result.innerText = data.message;
            document.getElementById('test_recipient').value = '';
        } else {
            result.className = 'mt-4 p-4 rounded-xl text-sm bg-red-50 text-red-700 border border-red-100';
            result.innerText = data.message;
        }
    })
    .catch(error => {
        result.classList.remove('hidden');
        result.className = 'mt-4 p-4 rounded-xl text-sm bg-red-50 text-red-700 border border-red-100';
        result.innerText = 'An error occurred while sending the test email.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="mail" class="w-4 h-4 mr-2"></i> Send Test';
        lucide.createIcons();
    });
}
function deleteLogo() {
    if (!confirm('Are you sure you want to remove the platform logo?')) return;
    
    fetch('{{ route("admin.settings.delete-logo") }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete logo');
    });
}
</script>
@endpush