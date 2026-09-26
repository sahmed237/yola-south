<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ \App\Models\Setting::get('platform_name', 'URCS') }}</title>

    <!-- Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
                            50: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}10',
                            100: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}20',
                            500: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}',
                            600: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}',
                            700: '{{ $system_settings['theme_primary_color'] ?? '#2563eb' }}dd',
                        },
                    },
                    fontFamily: {
                        sans: ['{{ $system_settings['theme_font_family'] ?? 'Inter' }}', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        :root {
            --primary-color: {{ $system_settings['theme_primary_color'] ?? '#2563eb' }};
        }
        [x-cloak] { display: none !important; }
        body { font-family: '{{ $system_settings['theme_font_family'] ?? 'Inter' }}', sans-serif; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 antialiased">
    <main>
        @yield('content')
    </main>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
