<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $client->nama_usaha ?? 'RentalBase Tenant' }} - Katalog Sewa</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Scripts/Styles (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Tailwind CSS (CDN for rapid prototyping if Vite isn't ready) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Name -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg text-white flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <span class="font-bold text-gray-800 hidden sm:block">{{ $client->nama_usaha ?? 'KameraKu Studio Malang' }}</span>
                    <span class="text-[10px] font-medium bg-gray-100 text-gray-500 px-2 py-0.5 rounded border border-gray-200 hidden md:inline-block">Subdomain Tenant</span>
                </div>

                <!-- Center Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#" class="text-gray-500 hover:text-gray-900 text-sm font-medium transition-colors">Beranda</a>
                    <a href="#" class="text-primary-600 border-b-2 border-primary-500 pb-[1.125rem] pt-[1.125rem] text-sm font-semibold transition-colors">Katalog</a>
                    <a href="#" class="text-gray-500 hover:text-gray-900 text-sm font-medium transition-colors">Ketentuan Sewa</a>
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center space-x-5">
                    <!-- Search Bar (Header) -->
                    <div class="hidden lg:flex relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" class="block w-64 pl-9 pr-3 py-1.5 border border-gray-200 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-sm transition duration-150 ease-in-out" placeholder="Cari kamera, lensa...">
                    </div>

                    <!-- Cart -->
                    <a href="#" class="relative text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span class="absolute -top-1 -right-2 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-primary-600 border-2 border-white rounded-full">2</span>
                    </a>

                    <!-- Login / Avatar -->
                    <div class="flex items-center gap-3 pl-2">
                        <a href="#" class="hidden sm:inline-flex items-center justify-center px-4 py-1.5 border border-primary-500 text-sm font-medium rounded-md text-primary-600 bg-white hover:bg-primary-50 transition-colors">
                            Masuk / Daftar
                        </a>
                        <button class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </button>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <button class="md:hidden text-gray-500 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="md:flex md:items-center md:justify-between grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                <!-- Left -->
                <div>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">{{ $client->nama_usaha ?? 'KameraKu Studio Malang' }}</h3>
                    <p class="text-gray-500 mb-1">Jl. Soekarno Hatta No. 45, Lowokwaru, Kota Malang</p>
                    <p class="text-gray-500 flex items-center gap-1">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        WhatsApp: +62 812-3456-7890
                    </p>
                </div>
                <!-- Center -->
                <div class="md:text-center">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Jam Operasional</h3>
                    <p class="text-gray-500">Setiap Hari 08:00 - 21:00 WIB</p>
                    <p class="text-gray-500">Layanan pickup & return unit tersedia</p>
                </div>
                <!-- Right -->
                <div class="md:text-right flex flex-col justify-end h-full">
                    <p class="text-gray-500 mb-1">Powered by <span class="font-bold text-primary-600">RentalBase</span></p>
                    <p class="text-xs text-gray-400 mb-1">Multi-Tenant Rental Engine v2.6</p>
                    <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ $client->nama_usaha ?? 'KameraKu Studio Malang' }}. Hak cipta dilindungi.</p>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
