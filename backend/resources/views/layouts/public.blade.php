<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $system_settings['platform_name'] ?? 'Unified Revenue Collection System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    @yield('head_scripts')
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Montserrat:wght@300;400;600;700&family=Poppins:wght@300;400;600;700&family=Outfit:wght@300;400;500;600;700;800;900&family=Lato:wght@300;400;700&family=Nunito:wght@300;400;600;700&family=Raleway:wght@300;400;600;700&family=Ubuntu:wght@300;400;500;700&family=Quicksand:wght@300;400;600;700&family=Fira+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: '{{ $system_settings['theme_font_family'] ?? 'Outfit' }}', sans-serif; 
            background-color: #f8fafc;
        }
        :root {
            --primary-color: {{ $system_settings['theme_primary_color'] ?? '#58c6a5' }};
        }
        .primary-btn {
            background-color: var(--primary-color) !important;
            box-shadow: 0 10px 15px -3px rgba(88, 198, 165, 0.2);
        }
        .primary-btn:hover {
            filter: brightness(0.95);
        }
        .primary-text {
            color: var(--primary-color) !important;
        }
        .primary-border {
            border-color: var(--primary-color) !important;
        }
        .primary-bg-light {
            background-color: var(--primary-color)10 !important;
        }
        
        /* Fancy Scrollbar */
        .fancy-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .fancy-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 9999px;
        }
        .fancy-scrollbar::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 9999px;
            border: 1px solid #f8fafc;
        }
        .fancy-scrollbar::-webkit-scrollbar-thumb:hover {
            opacity: 0.9;
        }
        .fancy-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f8fafc;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '{{ $system_settings['theme_primary_color'] ?? '#58c6a5' }}10',
                            100: '{{ $system_settings['theme_primary_color'] ?? '#58c6a5' }}20',
                            500: '{{ $system_settings['theme_primary_color'] ?? '#58c6a5' }}',
                            600: '{{ $system_settings['theme_primary_color'] ?? '#58c6a5' }}',
                            700: '{{ $system_settings['theme_primary_color'] ?? '#58c6a5' }}e8',
                        },
                    },
                    fontFamily: {
                        sans: ['{{ $system_settings['theme_font_family'] ?? 'Outfit' }}', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    @yield('styles')
</head>
<body class="@yield('body_class', 'min-h-screen text-slate-800 flex flex-col justify-between selection:bg-emerald-500 selection:text-white relative overflow-x-hidden')" @yield('body_attributes')>
    
    <!-- Background glows -->
    @section('background_glows')
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-500/5 rounded-full blur-3xl -z-10 animate-pulse print-hidden"></div>
    <div class="absolute bottom-20 right-1/4 w-96 h-96 bg-[#58c6a5]/10 rounded-full blur-3xl -z-10 animate-pulse print-hidden"></div>
    @show

    <!-- Navigation Header -->
    @section('header')
    <nav class="sticky top-0 z-50 w-full bg-white/80 backdrop-blur-lg border-b border-slate-200/50 transition-all duration-300 print-hidden" id="main-navbar">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <!-- Brand Logo & Name -->
            <a href="/" class="flex items-center gap-3 group">
                @if(!empty($system_settings['platform_logo']))
                    <img src="{{ asset($system_settings['platform_logo']) }}" alt="Logo" class="h-9 w-auto">
                @else
                    <div class="w-10 h-10 primary-btn rounded-2xl flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform duration-300">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                    </div>
                @endif
                <div>
                    <span class="text-base font-extrabold text-slate-900 tracking-wider block uppercase">{{ $system_settings['platform_name'] ?? 'State Revenue Portal' }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-widest block -mt-0.5 primary-text">Unified Taxpayer Portal</span>
                </div>
            </a>

            <!-- Desktop Menu (Hidden on Mobile) -->
            <div class="hidden lg:flex items-center gap-8">
                <div class="flex items-center gap-6 text-xs font-bold uppercase tracking-widest text-slate-500">
                    <a href="{{ route('public.landing') }}" class="hover:text-slate-900 transition-colors {{ request()->routeIs('public.landing') ? 'primary-text border-b-2 border-primary-500 pb-1' : 'hover:border-b-2 hover:border-slate-300 pb-1' }}">Home</a>
                    <a href="{{ route('public.shop-application.index') }}" class="hover:text-slate-900 transition-colors {{ request()->routeIs('public.shop-application.*') ? 'primary-text border-b-2 border-primary-500 pb-1' : 'hover:border-b-2 hover:border-slate-300 pb-1' }}">Shop Application</a>
                    <a href="{{ route('public.map') }}" class="hover:text-slate-900 transition-colors {{ request()->routeIs('public.map') ? 'primary-text border-b-2 border-primary-500 pb-1' : 'hover:border-b-2 hover:border-slate-300 pb-1' }}">Explore Map</a>
                    <a href="{{ route('public.how-to-pay') }}" class="hover:text-slate-900 transition-colors {{ request()->routeIs('public.how-to-pay') ? 'primary-text border-b-2 border-primary-500 pb-1' : 'hover:border-b-2 hover:border-slate-300 pb-1' }}">How to Pay</a>
                    <a href="{{ route('public.faq') }}" class="hover:text-slate-900 transition-colors {{ request()->routeIs('public.faq') ? 'primary-text border-b-2 border-primary-500 pb-1' : 'hover:border-b-2 hover:border-slate-300 pb-1' }}">FAQs</a>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 primary-btn text-white text-xs font-bold uppercase tracking-widest rounded-xl transition-all duration-300 flex items-center gap-2 shadow-sm">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-widest rounded-xl transition-all duration-300 flex items-center gap-2 border border-slate-200/60 shadow-sm">
                            <i data-lucide="log-in" class="w-4 h-4 primary-text"></i>
                            Officer Portal
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Hamburger Button (Mobile Only) -->
            <button id="mobile-menu-toggle" type="button" class="lg:hidden p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 hover:bg-slate-100 focus:outline-none transition-colors" aria-label="Toggle Navigation">
                <i data-lucide="menu" class="w-5 h-5 block" id="menu-icon-hamburger"></i>
                <i data-lucide="x" class="w-5 h-5 hidden" id="menu-icon-close"></i>
            </button>
        </div>

        <!-- Mobile Drawer Menu (Slide Down, Hidden by Default) -->
        <div id="mobile-menu-drawer" class="hidden lg:hidden border-t border-slate-100 bg-white w-full overflow-hidden transition-all duration-300 shadow-xl max-h-0">
            <div class="px-6 py-4 flex flex-col gap-4">
                <div class="flex flex-col gap-3 text-xs font-bold uppercase tracking-widest text-slate-500">
                    <a href="{{ route('public.landing') }}" class="py-2 hover:text-slate-900 border-b border-slate-50 {{ request()->routeIs('public.landing') ? 'primary-text' : '' }}">Home</a>
                    <a href="{{ route('public.shop-application.index') }}" class="py-2 hover:text-slate-900 border-b border-slate-50 {{ request()->routeIs('public.shop-application.*') ? 'primary-text' : '' }}">Shop Application</a>
                    <a href="{{ route('public.map') }}" class="py-2 hover:text-slate-900 border-b border-slate-50 {{ request()->routeIs('public.map') ? 'primary-text' : '' }}">Explore Map</a>
                    <a href="{{ route('public.how-to-pay') }}" class="py-2 hover:text-slate-900 border-b border-slate-50 {{ request()->routeIs('public.how-to-pay') ? 'primary-text' : '' }}">How to Pay</a>
                    <a href="{{ route('public.faq') }}" class="py-2 hover:text-slate-900 border-b border-slate-50 {{ request()->routeIs('public.faq') ? 'primary-text' : '' }}">FAQs</a>
                </div>
                <div class="pt-2 border-t border-slate-100 flex flex-col">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="w-full text-center py-3 primary-btn text-white text-xs font-bold uppercase tracking-widest rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full text-center py-3 bg-slate-100 border border-slate-200 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-widest rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                            <i data-lucide="log-in" class="w-4 h-4 primary-text"></i>
                            Officer Portal
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    @show

    <!-- Content -->
    @yield('content')

    <!-- Footer -->
    @section('footer')
    <footer class="w-full max-w-7xl mx-auto px-6 py-8 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between text-xs text-slate-400 relative z-10 print-hidden">
        <p class="font-medium">&copy; {{ date('Y') }} {{ $system_settings['platform_name'] ?? 'Unified Revenue Collection System' }}. All rights reserved.</p>
        <p class="flex items-center gap-1.5 font-semibold text-slate-500 mt-2 md:mt-0 uppercase tracking-wider">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
            Powered by <a href="{{ config('app.powered_url') }}" target="_blank" class="hover:text-primary-500 transition-colors font-extrabold">{{ config('app.powered_by') }}</a>
        </p>
    </footer>
    @show

    <script>
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            const menuDrawer = document.getElementById('mobile-menu-drawer');
            const hamburgerIcon = document.getElementById('menu-icon-hamburger');
            const closeIcon = document.getElementById('menu-icon-close');

            if (toggleBtn && menuDrawer) {
                toggleBtn.addEventListener('click', function() {
                    const isHidden = menuDrawer.classList.contains('hidden');
                    if (isHidden) {
                        // Open drawer
                        menuDrawer.classList.remove('hidden');
                        setTimeout(() => {
                            menuDrawer.style.maxHeight = '400px';
                        }, 10);
                        hamburgerIcon.classList.remove('block');
                        hamburgerIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
                        closeIcon.classList.add('block');
                    } else {
                        // Close drawer
                        menuDrawer.style.maxHeight = '0';
                        hamburgerIcon.classList.remove('hidden');
                        hamburgerIcon.classList.add('block');
                        closeIcon.classList.remove('block');
                        closeIcon.classList.add('hidden');
                        setTimeout(() => {
                            menuDrawer.classList.add('hidden');
                        }, 300);
                    }
                });
            }

            // Sticky Scroll Effect
            const navbar = document.getElementById('main-navbar');
            if (navbar) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 20) {
                        navbar.classList.add('shadow-lg', 'bg-white/95', 'py-1');
                        navbar.classList.remove('bg-white/80');
                    } else {
                        navbar.classList.remove('shadow-lg', 'bg-white/95', 'py-1');
                        navbar.classList.add('bg-white/80');
                    }
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
