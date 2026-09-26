@extends('layouts.public')

@section('title', 'Frequently Asked Questions - ' . ($system_settings['platform_name'] ?? 'Unified Revenue Collection System'))

@section('content')
    <!-- Main Content: FAQs Page -->
    <main class="w-full max-w-4xl mx-auto px-6 py-16 flex-grow relative z-10">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary-50 border border-primary-100 text-primary-700 text-[10px] font-black uppercase tracking-widest mb-6">
                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                Help Center
            </div>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                Frequently Asked <span class="bg-gradient-to-r from-primary-600 via-primary-500 to-indigo-500 bg-clip-text text-transparent">Questions</span>
            </h1>
            <p class="text-slate-500 text-sm md:text-base font-medium max-w-xl mx-auto mt-6 leading-relaxed">
                Find quick answers to common questions about billing, splits, verification and taxpayer services.
            </p>
        </div>

        <!-- Accordions List -->
        <div class="space-y-6 max-w-3xl mx-auto mb-16">
            @forelse($faqs as $index => $faq)
                <!-- Accordion Item {{ $index + 1 }} -->
                <div class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-100/50 overflow-hidden transition-all duration-300">
                    <button class="w-full px-8 py-6 text-left flex justify-between items-center group focus:outline-none" onclick="toggleAccordion(this)">
                        <span class="text-sm font-bold text-slate-800 group-hover:text-primary-600 transition-colors">{{ $faq->question }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-primary-600 transition-all duration-300 pointer-events-none"></i>
                    </button>
                    <div class="hidden border-t border-slate-50 px-8 py-6 bg-slate-50/50">
                        <p class="text-xs text-slate-500 leading-relaxed font-semibold">
                            {!! nl2br(e($faq->answer)) !!}
                        </p>
                    </div>
                </div>
            @empty
                <!-- Accordion Item 1 -->
                <div class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-100/50 overflow-hidden transition-all duration-300">
                    <button class="w-full px-8 py-6 text-left flex justify-between items-center group focus:outline-none" onclick="toggleAccordion(this)">
                        <span class="text-sm font-bold text-slate-800 group-hover:text-primary-600 transition-colors">How do I verify if my payment is recorded?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-primary-600 transition-all duration-300 pointer-events-none"></i>
                    </button>
                    <div class="hidden border-t border-slate-50 px-8 py-6 bg-slate-50/50">
                        <p class="text-xs text-slate-500 leading-relaxed font-semibold">
                            Once a payment is successfully completed via our gateway, the database is instantly updated. You can enter your business name on the homepage lookup to confirm your active receipt status. The download receipt contains a secure QR verification indicator.
                        </p>
                    </div>
                </div>

                <!-- Accordion Item 2 -->
                <div class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-100/50 overflow-hidden transition-all duration-300">
                    <button class="w-full px-8 py-6 text-left flex justify-between items-center group focus:outline-none" onclick="toggleAccordion(this)">
                        <span class="text-sm font-bold text-slate-800 group-hover:text-primary-600 transition-colors">What happens if a transaction callback fails?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-primary-600 transition-all duration-300 pointer-events-none"></i>
                    </button>
                    <div class="hidden border-t border-slate-50 px-8 py-6 bg-slate-50/50">
                        <p class="text-xs text-slate-500 leading-relaxed font-semibold">
                            If a callback fails due to gateway issues or network latency, system officers can perform a manual "Verify Payment" recheck on the invoice detail page in their dashboard. This contacts the gateway API to safely update status codes instantly.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        function toggleAccordion(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('[data-lucide="chevron-down"]');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
@endsection
