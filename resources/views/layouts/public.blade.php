<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalBase - Kelola Bisnis Rental</title>
    
    <!-- PWA & Mobile Web App Meta -->
    <meta name="apple-mobile-web-app-title" content="RentalBase">
    <meta name="application-name" content="RentalBase">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#F97316">
    
    <!-- Icons & Manifest -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=4">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/rentalbase-icon.svg') }}?v=4">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}?v=4">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js untuk interaktivitas -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased selection:bg-orange-500 selection:text-white">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="/" class="text-2xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2 group">
                        <img src="{{ asset('images/rentalbase-icon.svg') }}" alt="RentalBase" class="w-8 h-8 drop-shadow-xs group-hover:scale-105 transition-transform duration-200">
                        <span>Rental<span class="text-orange-500">Base</span></span>
                    </a>
                </div>
                
                <!-- Menu Tengah (Desktop) -->
                <div class="hidden md:flex space-x-8">
                    <a href="#" class="text-gray-600 hover:text-orange-500 font-medium transition">Fitur</a>
                    <a href="#" class="text-gray-600 hover:text-orange-500 font-medium transition">Harga</a>
                    <a href="#" class="text-gray-600 hover:text-orange-500 font-medium transition">Kontak</a>
                </div>
                
                <!-- Tombol Action -->
                <div class="flex items-center">
                    <a href="#" class="text-orange-600 border-2 border-orange-600 hover:bg-orange-50 font-bold text-sm md:text-base py-2 px-4 rounded-lg transition-colors">
                        Login Admin/Owner
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Slot -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-20 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 text-sm text-center md:text-left">© {{ date('Y') }} RentalBase. All rights reserved.</p>
            <div class="flex space-x-6">
                <a href="#" class="text-gray-400 hover:text-orange-500 text-sm transition">Syarat & Ketentuan</a>
                <a href="#" class="text-gray-400 hover:text-orange-500 text-sm transition">Kebijakan Privasi</a>
            </div>
        </div>
    </footer>

</body>
</html>
