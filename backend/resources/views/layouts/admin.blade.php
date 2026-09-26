<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }} - Revenue Collection System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Montserrat:wght@300;400;600;700&family=Poppins:wght@300;400;600;700&family=Outfit:wght@300;400;600;700&family=Lato:wght@300;400;700&family=Nunito:wght@300;400;600;700&family=Raleway:wght@300;400;600;700&family=Ubuntu:wght@300;400;500;700&family=Quicksand:wght@300;400;600;700&family=Fira+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary-color: {{ $system_settings['theme_primary_color'] ?? '#2563eb' }};
            --sidebar-bg: {{ $system_settings['theme_sidebar_bg'] ?? '#0f172a' }};
            --sidebar-accent: {{ $system_settings['theme_sidebar_accent'] ?? '#1e293b' }};
            --sidebar-scrollbar: {{ $system_settings['theme_sidebar_scrollbar_color'] ?? 'rgba(255, 255, 255, 0.1)' }};
        }
        body { font-family: '{{ $system_settings['theme_font_family'] ?? 'Inter' }}', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        .fancy-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .fancy-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .fancy-scroll::-webkit-scrollbar-thumb {
            background: var(--sidebar-scrollbar);
            border-radius: 10px;
        }
        .fancy-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .sidebar-active {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        @media print {
            aside, header, footer, .print\:hidden {
                display: none !important;
            }
            body, html, main, .flex-1, .overflow-y-auto, .h-full, .overflow-hidden {
                overflow: visible !important;
                height: auto !important;
                min-height: auto !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .grid {
                display: block !important;
            }
            .lg\:col-span-2, .lg\:col-span-1 {
                width: 100% !important;
                margin-bottom: 2rem !important;
            }
            .rounded-\[2\.5rem\] {
                border-radius: 1rem !important;
            }
            .shadow-xl {
                box-shadow: none !important;
            }
        }
    </style>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}10',
                            100: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}20',
                            500: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}',
                            600: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}',
                        },
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="h-full overflow-hidden" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 w-64 text-white transition-transform duration-300 transform lg:relative lg:translate-x-0 print:hidden"
            style="background-color: {{ $system_settings['theme_sidebar_bg'] ?? '#0f172a' }}"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
        >
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-6 border-b" style="border-color: rgba(255,255,255,0.1)">
                    @if(!empty($system_settings['platform_logo']))
                        <img src="{{ $system_settings['platform_logo'] }}" alt="Logo" class="h-8 w-auto">
                    @else
                        <span class="text-xl font-bold tracking-wider text-white">{{ $system_settings['platform_name'] ?? 'URCS' }}</span>
                    @endif
                    <button @click="sidebarOpen = false" class="lg:hidden text-white/50 hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Nav Links -->
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto fancy-scroll">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.dashboard') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                        Dashboard
                    </a>
                    
                    <div class="pt-4 pb-2 text-xs font-semibold tracking-wider uppercase text-white/30">Revenue Management</div>
                    @can('view all establishment')
                    <a href="{{ route('admin.establishments.approved') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.establishments.approved') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="building-2" class="w-5 h-5 mr-3"></i>
                        Approved Establishments
                    </a>
                    @endcan
                    <a href="{{ route('admin.establishments.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.establishments.index') || request()->routeIs('admin.establishments.create') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="store" class="w-5 h-5 mr-3"></i>
                        Establishment Management
                    </a>
                    @php
                        $invalidCountQuery = \App\Models\Establishment::where('status', 'rejected');
                        if (auth()->check() && !auth()->user()->hasPermissionTo('view all invalid establishment')) {
                            $invalidCountQuery->where('created_by', auth()->id());
                        }
                        $invalidCount = auth()->check() ? $invalidCountQuery->count() : 0;
                    @endphp
                    <a href="{{ route('admin.establishments.invalid') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.establishments.invalid') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="x-square" class="w-5 h-5 mr-3"></i>
                        Invalid Establishments
                        @if($invalidCount > 0)
                        <span class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-red-600 text-white">
                            {{ $invalidCount }}
                        </span>
                        @endif
                    </a>
                    @can('establishment approval')
                    @php
                        $pendingApprovalsCount = \App\Models\Establishment::where('status', 'pending')->count();
                    @endphp
                    <a href="{{ route('admin.approvals.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.approvals.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
                        Establishment Approvals
                        @if($pendingApprovalsCount > 0)
                        <span class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-600 text-white">
                            {{ $pendingApprovalsCount }}
                        </span>
                        @endif
                    </a>
                    @endcan
                    @can('approve establishment update')
                    <a href="{{ route('admin.establishment-update-requests.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.establishment-update-requests.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="refresh-cw" class="w-5 h-5 mr-3"></i>
                        Update Requests
                    </a>
                    @endcan
                     @can('view unpaid taxes')
                    <a href="{{ route('admin.establishments.unpaid') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.establishments.unpaid') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="wallet" class="w-5 h-5 mr-3"></i>
                        Unpaid Taxes
                    </a>
                    @endcan
                    @can('view invoice')
                    <a href="{{ route('admin.invoices.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.invoices.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="receipt" class="w-5 h-5 mr-3"></i>
                        Invoices
                    </a>
                    @endcan
                    @canany(['view payments', 'view payment'])
                    <a href="{{ route('admin.payments.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.payments.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="credit-card" class="w-5 h-5 mr-3"></i>
                        Payments
                    </a>
                    @endcanany
                    @can('view report')
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.reports.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>
                        Reports
                    </a>
                    @endcan

                    @if(auth()->user()->can('manage faq') || auth()->user()->hasRole('super-admin'))
                    <div class="pt-4 pb-2 text-xs font-semibold tracking-wider uppercase text-white/30">System Setup</div>
                    @endif
                    @can('manage faq')
                    <a href="{{ route('admin.faqs.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.faqs.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="help-circle" class="w-5 h-5 mr-3"></i>
                        Manage FAQs
                    </a>
                    @endcan
                    @role('super-admin')
                    <a href="{{ route('admin.setup.establishment-types.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.setup.establishment-types.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="tag" class="w-5 h-5 mr-3"></i>
                        Establishment Types
                    </a>
                    <a href="{{ route('admin.setup.establishment-sizes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.setup.establishment-sizes.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="maximize" class="w-5 h-5 mr-3"></i>
                        Establishment Sizes
                    </a>
                    @endrole
                    @role('super-admin')
                    <a href="{{ route('admin.agencies.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.agencies.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="building" class="w-5 h-5 mr-3"></i>
                        Agencies
                    </a>
                    <a href="{{ route('admin.service-fee-agencies.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.service-fee-agencies.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="percent" class="w-5 h-5 mr-3"></i>
                        Service Fee Setup
                    </a>
                    <a href="{{ route('admin.revenue-rules.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.revenue-rules.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="calculator" class="w-5 h-5 mr-3"></i>
                        Revenue Rules
                    </a>
                    @endrole

                    @if(auth()->user()->can('manage users') || auth()->user()->can('manage roles') || auth()->user()->hasRole('super-admin'))
                    <div class="pt-4 pb-2 text-xs font-semibold tracking-wider uppercase text-white/30">Administration</div>
                    @endif
                    @can('manage users')
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.users.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                        User Management
                    </a>
                    @endcan
                    @can('manage roles')
                    <a href="{{ route('admin.roles.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.roles.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="shield-check" class="w-5 h-5 mr-3"></i>
                        Roles & Permissions
                    </a>
                    @endcan
                    @role('super-admin')
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-3 text-sm font-medium transition-colors rounded-xl {{ request()->routeIs('admin.settings.*') ? 'sidebar-active' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="settings" class="w-5 h-5 mr-3"></i>
                        System Settings
                    </a>
                    @endrole
                </nav>

                <!-- User Profile -->
                <a href="{{ route('admin.profile') }}" class="block p-4 m-4 rounded-2xl transition-all hover:bg-white/10 group" style="background-color: rgba(255,255,255,0.05)">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow-inner transition-transform group-hover:scale-110" style="background-color: var(--primary-color)">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="ml-3 overflow-hidden">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Admin User' }}</p>
                            <p class="text-xs text-slate-400 truncate group-hover:text-primary-400 transition-colors">Settings & Profile</p>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 ml-auto text-white/20 group-hover:text-white transition-all transform group-hover:translate-x-1"></i>
                    </div>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header class="h-16 glass z-40 px-6 flex items-center justify-between border-b border-gray-200 print:hidden">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 lg:hidden">
                    <i data-lucide="menu" class="w-6 h-6 text-slate-600"></i>
                </button>
                
                <div class="hidden lg:flex items-center bg-gray-100 rounded-full px-4 py-2 w-96">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 mr-2"></i>
                    <input type="text" placeholder="Search for establishments, payments..." class="bg-transparent border-none focus:ring-0 text-sm w-full">
                </div>

                <div class="flex items-center space-x-4">
                    <button class="relative p-2 text-gray-400 hover:text-primary-600 transition-colors">
                        <i data-lucide="bell" class="w-6 h-6"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    <div class="h-8 w-px bg-gray-200"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center text-sm font-medium text-gray-600 hover:text-red-600 transition-colors">
                            <i data-lucide="log-out" class="w-5 h-5 mr-2"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 flex flex-col justify-between">
                <div class="max-w-7xl mx-auto w-full">
                    @yield('content')
                </div>
                
                <!-- Footer -->
                <footer class="w-full max-w-7xl mx-auto mt-12 pt-6 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between text-xs text-slate-400 print:hidden">
                    <p class="font-medium">&copy; {{ date('Y') }} {{ $system_settings['platform_name'] ?? 'Unified Revenue Collection System' }}. All rights reserved.</p>
                    <p class="flex items-center gap-1.5 font-semibold text-slate-500 mt-2 md:mt-0 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                        Powered by <a href="{{ config('app.powered_url') }}" target="_blank" class="hover:text-primary-600 transition-colors font-extrabold">{{ config('app.powered_by') }}</a>
                    </p>
                </footer>
            </div>
        </main>
    </div>

    <!-- Notifications Toast -->
    <div x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            init() {
                @if(session('success'))
                    this.notify('{{ session('success') }}', 'success');
                @endif
                @if(session('error'))
                    this.notify('{{ session('error') }}', 'error');
                @endif
                @if(session('info'))
                    this.notify('{{ session('info') }}', 'info');
                @endif
                @if(session('warning'))
                    this.notify('{{ session('warning') }}', 'warning');
                @endif
            },
            notify(msg, type) {
                this.message = msg;
                this.type = type;
                this.show = true;
                setTimeout(() => { this.show = false }, 5000);
            }
         }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-10 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-10 opacity-0"
         class="fixed bottom-8 right-8 z-[100] max-w-sm w-full"
         style="display: none;">
        
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                 :class="{
                    'bg-emerald-50 text-emerald-600': type === 'success',
                    'bg-red-50 text-red-600': type === 'error',
                    'bg-indigo-50 text-indigo-600': type === 'info',
                    'bg-amber-50 text-amber-600': type === 'warning'
                 }">
                <i data-lucide="check-circle" class="w-6 h-6" x-show="type === 'success'"></i>
                <i data-lucide="alert-circle" class="w-6 h-6" x-show="type === 'error'"></i>
                <i data-lucide="info" class="w-6 h-6" x-show="type === 'info'"></i>
                <i data-lucide="alert-triangle" class="w-6 h-6" x-show="type === 'warning'"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-slate-800" x-text="message"></p>
            </div>
            <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    @stack('modals')
    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
