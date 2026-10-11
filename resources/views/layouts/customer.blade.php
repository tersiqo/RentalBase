<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $client->nama_usaha ?? 'RentalBase Tenant' }} - Katalog Sewa</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts/Styles (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-[#FAFAFA] text-gray-600 font-sans antialiased min-h-screen flex flex-col">

    <!-- Header -->
    <header x-data="{ mobileMenuOpen: false }" class="bg-white/95 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-[1260px] mx-auto px-6">
            <div class="flex justify-between items-center h-[68px]">
                <!-- Logo & Name -->
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? '']) }}" class="flex items-center gap-3 font-extrabold text-gray-900 text-[17px]">
                    <div class="w-[38px] h-[38px] bg-primary-500 rounded-xl text-white flex items-center justify-center font-bold shadow-[0_8px_16px_-6px_rgba(249,115,22,0.5)]">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="19" r="2"/><circle cx="17" cy="19" r="2"/><path d="M3 4h3l2 11h10M6 8h13l-2 6H8"/></svg>
                    </div>
                    <span>{{ $client->nama_usaha ?? 'RentalBase Tenant' }}</span>
                </a>

                <!-- Center Navigation -->
                <nav class="hidden md:flex items-center space-x-7 font-semibold text-[14px]">
                    <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? '']) }}" class="{{ !request()->routeIs('customer.checkout*') ? 'text-primary-500 border-b-2 border-primary-500 py-[22px]' : 'text-gray-600 border-b-2 border-transparent hover:text-primary-500 py-[22px]' }} transition-colors">Katalog</a>
                    @if(request()->routeIs('customer.checkout*'))
                        <a href="#" class="text-primary-500 border-b-2 border-primary-500 py-[22px] transition-colors">Checkout</a>
                    @else
                        <a href="#katalog" class="text-gray-600 border-b-2 border-transparent hover:text-primary-500 py-[22px] transition-colors">Cara sewa</a>
                    @endif
                    <a href="#bantuan" class="text-gray-600 border-b-2 border-transparent hover:text-primary-500 py-[22px] transition-colors">Bantuan</a>
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center space-x-5">
                    <!-- Search Bar (Header) -->
                    <!-- Search Bar Navbar Dihapus agar tidak double dengan hero search -->

                    <!-- Cart -->
                    <livewire:customer.cart-icon :client="$client" />

                    @auth
                        <!-- Login / Avatar (Alpine Dropdown) -->
                        <div x-data="{ open: false }" class="relative flex items-center ml-1">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 focus:outline-none">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=f97316&color=fff&bold=true" alt="Profile" class="w-[42px] h-[42px] rounded-xl border border-gray-200 hover:border-primary-500 transition-colors">
                            </button>
                            
                            <div x-show="open" 
                                 x-transition.opacity.duration.200ms
                                 class="absolute right-0 top-12 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-100 z-50" 
                                 style="display: none;">
                                <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-sm font-bold text-gray-900">Halo, {{ auth()->user()->name }}!</p>
                                </div>
                                <a href="{{ route('customer.orders') }}" class="block px-4 py-2 text-sm font-semibold text-orange-600 bg-orange-50 transition">Pesanan Saya</a>
                                <div class="border-t border-gray-100 mt-1"></div>
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">Log out</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest Actions -->
                        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="hidden sm:inline-flex items-center justify-center gap-2 rounded-xl font-bold px-4 py-[9px] border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 text-[14px] transition-colors">Masuk</a>
                        <a href="{{ route('tenant.register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl font-bold px-4 py-[9px] border border-transparent bg-primary-500 text-white shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] hover:bg-primary-600 active:scale-95 text-[14px] transition-all">Daftar</a>
                    @endauth
                    
                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-500 hover:text-gray-900 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition
             class="md:hidden bg-white border-t border-gray-100"
             style="display: none;">
            <div class="px-4 pt-3 pb-3 space-y-2">
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? '']) }}" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Katalog</a>
                @if(request()->routeIs('customer.checkout*'))
                    <a href="#" class="block px-3 py-2 rounded-lg text-base font-medium text-primary-600 bg-primary-50">Checkout</a>
                @else
                    <a href="#katalog" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Cara sewa</a>
                @endif
                <a href="#bantuan" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Bantuan</a>
                
                @auth
                    <div class="border-t border-gray-100 pt-2 mt-2">
                        <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">Akun Saya</div>
                        <a href="{{ route('customer.orders') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-primary-600 hover:bg-primary-50">Pesanan Saya</a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-3 py-2 rounded-lg text-base font-medium text-red-600 hover:bg-red-50">Log out</a>
                    </div>
                @else
                    <div class="border-t border-gray-100 pt-3 mt-2 flex flex-col gap-2">
                        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="w-full text-center py-2.5 font-bold rounded-xl border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">Masuk</a>
                        <a href="{{ route('tenant.register') }}" class="w-full text-center py-2.5 font-bold rounded-xl bg-primary-500 text-white hover:bg-primary-600">Daftar</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer id="kontak" class="bg-slate-900 border-t border-slate-800 text-slate-400 py-[30px] text-[13px] mt-16">
        <div class="max-w-[1260px] mx-auto px-6">
            <div class="flex justify-between flex-wrap gap-5">
                <div>
                    <b class="text-white text-sm block mb-1">{{ $client->nama_usaha ?? 'BabyRent Malang' }}</b>
                    {{ $client->address ?? 'Jl. Contoh No. 12, Malang.' }}<br>
                    Buka setiap hari {{ $client->operational_hours ?? '08.00 - 20.00' }}<br>
                    WhatsApp: {{ $client->phone ?? '-' }}
                </div>
                <div class="text-right">
                    Ditenagai <b class="text-primary-500">RentalBase</b><br>
                    &copy; {{ date('Y') }} {{ $client->nama_usaha ?? 'BabyRent Malang' }}.
                </div>
            </div>
        </div>
    </footer>

    @livewireScriptConfig
</body>
</html>
