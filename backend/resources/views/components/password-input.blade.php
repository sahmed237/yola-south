@props(['name', 'placeholder' => '••••••••', 'required' => false, 'icon' => 'lock'])

<div x-data="{ show: false }" class="relative group">
    <i data-lucide="{{ $icon }}" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors"></i>
    
    <input :type="show ? 'text' : 'password'" 
           name="{{ $name }}" 
           @if($required) required @endif
           {{ $attributes->merge(['class' => 'w-full pl-12 pr-12 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:bg-white focus:border-primary-500 transition-all']) }}
           placeholder="{{ $placeholder }}">
    
    <button type="button" 
            @click="show = !show" 
            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-primary-500 transition-colors">
        <i data-lucide="eye" x-show="!show" class="w-5 h-5"></i>
        <i data-lucide="eye-off" x-show="show" class="w-5 h-5" style="display: none;"></i>
    </button>
</div>
