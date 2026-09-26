<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ \App\Models\Setting::get('platform_name', 'YSLG-IMRS') }}</title>

    <!-- Fonts: Archivo, Newsreader, IBM Plex Mono, Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400&family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '{{ $system_settings['theme_primary_color'] ?? '#0b6b3a' }}10',
                            100: '{{ $system_settings['theme_primary_color'] ?? '#0b6b3a' }}20',
                            500: '{{ $system_settings['theme_primary_color'] ?? '#0b6b3a' }}',
                            600: '{{ $system_settings['theme_primary_color'] ?? '#0b6b3a' }}',
                            700: '{{ $system_settings['theme_primary_color'] ?? '#0b6b3a' }}dd',
                        },
                    },
                    fontFamily: {
                        sans: ['Archivo', '{{ $system_settings['theme_font_family'] ?? 'Inter' }}', 'sans-serif'],
                        serif: ['Newsreader', 'Georgia', 'serif'],
                        mono: ['IBM Plex Mono', 'monospace'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        :root {
            --primary-color: {{ $system_settings['theme_primary_color'] ?? '#0b6b3a' }};
        }
        [x-cloak] { display: none !important; }
        body { font-family: 'Archivo', '{{ $system_settings['theme_font_family'] ?? 'Inter' }}', sans-serif; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
    </style>
    @stack('styles')
    @yield('styles')
</head>
<body class="@yield('body-class', 'bg-slate-50 antialiased')">
    <main>
        @yield('content')
    </main>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
