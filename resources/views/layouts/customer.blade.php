<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $client->nama_usaha ?? 'RentalBase Tenant' }} - Katalog Sewa</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/rentalbase-icon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts/Styles (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @php
        $tenantProfile = $client?->tenantProfile ?? $client?->profile;
        $themeColor = $tenantProfile?->theme_color ?? $client?->theme_color ?? '#F97316';
        $textColor = $tenantProfile?->text_color ?? (new \App\Models\TenantProfile(['theme_color' => $themeColor]))->text_color;

        $logoPath = $tenantProfile?->logo_path ?? $client?->logo ?? null;
        $logoUrl = null;
        if ($logoPath) {
            $logoUrl = str_starts_with($logoPath, 'http')
                ? $logoPath
                : \Illuminate\Support\Facades\Storage::url($logoPath);
        }

        $shopName = $tenantProfile?->name ?? $client->nama_usaha ?? ($client->business_name ?? 'RentalBase');
        $rawPhone = $tenantProfile?->whatsapp ?? $client->phone ?? null;
        $waNumber = null;
        if ($rawPhone) {
            $digits = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($digits, '0')) {
                $waNumber = '62' . substr($digits, 1);
            } elseif (str_starts_with($digits, '62')) {
                $waNumber = $digits;
            } else {
                $waNumber = '62' . $digits;
            }
        }
    @endphp

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }

        :root {
            --tenant-theme: {{ $themeColor }};
            --tenant-text: {{ $textColor }};
            --color-primary-500: {{ $themeColor }};
            --color-primary-600: {{ $themeColor }};
            --tenant-theme-50: color-mix(in srgb, var(--tenant-theme) 10%, white);
            --tenant-theme-100: color-mix(in srgb, var(--tenant-theme) 18%, white);
            --tenant-theme-200: color-mix(in srgb, var(--tenant-theme) 30%, white);
            --tenant-theme-300: color-mix(in srgb, var(--tenant-theme) 48%, white);
            --tenant-theme-400: color-mix(in srgb, var(--tenant-theme) 70%, white);
            --tenant-theme-600: color-mix(in srgb, var(--tenant-theme) 85%, black);
            --tenant-theme-700: color-mix(in srgb, var(--tenant-theme) 72%, black);
            --tenant-theme-800: color-mix(in srgb, var(--tenant-theme) 60%, black);
            --tenant-theme-900: color-mix(in srgb, var(--tenant-theme) 45%, black);
        }

        /* Override tombol checkout & elemen utama katalog customer */
        .bg-primary-500,
        .btn-tenant {
            background-color: var(--tenant-theme) !important;
            color: var(--tenant-text) !important;
        }

        .text-primary-500,
        .text-primary-600 {
            color: var(--tenant-theme) !important;
        }

        .border-primary-500 {
            border-color: var(--tenant-theme) !important;
        }

        .border-primary-100 {
            border-color: color-mix(in srgb, var(--tenant-theme) 25%, white) !important;
        }

        .border-primary-200 {
            border-color: color-mix(in srgb, var(--tenant-theme) 35%, white) !important;
        }

        .border-primary-300 {
            border-color: color-mix(in srgb, var(--tenant-theme) 50%, white) !important;
        }

        .bg-primary-50 {
            background-color: color-mix(in srgb, var(--tenant-theme) 10%, white) !important;
        }

        .bg-primary-100 {
            background-color: color-mix(in srgb, var(--tenant-theme) 18%, white) !important;
        }

        .text-primary-700 {
            color: color-mix(in srgb, var(--tenant-theme) 75%, black) !important;
        }

        .text-primary-800 {
            color: color-mix(in srgb, var(--tenant-theme) 60%, black) !important;
        }

        .text-primary-900 {
            color: color-mix(in srgb, var(--tenant-theme) 45%, black) !important;
        }

        .from-primary-500 {
            --tw-gradient-from: var(--tenant-theme) var(--tw-gradient-from-position) !important;
            --tw-gradient-to: color-mix(in srgb, var(--tenant-theme) 80%, black) var(--tw-gradient-to-position) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }

        .to-primary-600 {
            --tw-gradient-to: color-mix(in srgb, var(--tenant-theme) 80%, black) var(--tw-gradient-to-position) !important;
        }

        .hover\:bg-primary-600:hover {
            filter: brightness(0.92);
        }

        /* Dynamic Tenant Hero Banner & Card Theme */
        .tenant-hero-bg {
            background: radial-gradient(circle at 8% 0%, color-mix(in srgb, var(--tenant-theme) 18%, white), transparent 45%),
                        radial-gradient(circle at 95% 10%, color-mix(in srgb, var(--tenant-theme) 12%, white), transparent 45%),
                        #fff !important;
        }

        .tenant-hero-card {
            background: linear-gradient(145deg, 
                color-mix(in srgb, var(--tenant-theme) 45%, white) 0%, 
                color-mix(in srgb, var(--tenant-theme) 75%, white) 45%, 
                var(--tenant-theme) 100%
            ) !important;
            box-shadow: 0 30px 60px -20px color-mix(in srgb, var(--tenant-theme) 45%, transparent) !important;
        }

        .tenant-hero-card:hover {
            box-shadow: 0 36px 70px -18px color-mix(in srgb, var(--tenant-theme) 55%, transparent) !important;
        }

        .tenant-badge {
            background-color: color-mix(in srgb, var(--tenant-theme) 10%, white) !important;
            color: color-mix(in srgb, var(--tenant-theme) 80%, black) !important;
            border: 1px solid color-mix(in srgb, var(--tenant-theme) 25%, white) !important;
        }

        .tenant-badge-dot {
            background-color: var(--tenant-theme) !important;
        }

        .tenant-shadow-sm {
            box-shadow: 0 8px 18px -8px color-mix(in srgb, var(--tenant-theme) 60%, transparent) !important;
        }

        .tenant-shadow-lg {
            box-shadow: 0 20px 45px -15px color-mix(in srgb, var(--tenant-theme) 35%, transparent) !important;
        }
    </style>
</head>
<body class="bg-[#FAFAFA] text-gray-600 font-sans antialiased min-h-screen flex flex-col"
      x-data="{ howToRentModalOpen: false, helpModalOpen: false, mobileMenuOpen: false }"
      @keydown.escape.window="howToRentModalOpen = false; helpModalOpen = false; mobileMenuOpen = false">

    <!-- Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-[1260px] mx-auto px-6">
            <div class="flex justify-between items-center h-[68px]">
                <!-- Logo & Name -->
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="flex items-center gap-3 font-extrabold text-gray-900 text-[17px] group">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}"
                             alt="{{ $tenantProfile?->name ?? $client->nama_usaha ?? 'Logo Toko' }}"
                             onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.style.display='flex';"
                             class="w-[40px] h-[40px] rounded-xl object-contain border border-gray-100 p-0.5 shadow-sm group-hover:scale-105 transition-transform bg-white">
                        <div class="hidden w-[38px] h-[38px] rounded-xl items-center justify-center font-bold shadow-sm transition-all"
                             style="background-color: var(--tenant-theme); color: var(--tenant-text);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="19" r="2"/><circle cx="17" cy="19" r="2"/><path d="M3 4h3l2 11h10M6 8h13l-2 6H8"/></svg>
                        </div>
                    @else
                        <div class="w-[38px] h-[38px] rounded-xl flex items-center justify-center font-bold shadow-sm transition-all"
                             style="background-color: var(--tenant-theme); color: var(--tenant-text);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="19" r="2"/><circle cx="17" cy="19" r="2"/><path d="M3 4h3l2 11h10M6 8h13l-2 6H8"/></svg>
                        </div>
                    @endif
                    <span>{{ $tenantProfile?->name ?? $client->nama_usaha ?? ($client->name ?? 'RentalBase Tenant') }}</span>
                </a>

                <!-- Center Navigation -->
                <nav class="hidden md:flex items-center space-x-7 font-semibold text-[14px]">
                    <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="{{ (request()->routeIs('customer.home*') || request()->routeIs('customer.product.*')) ? 'text-primary-500 border-b-2 border-primary-500 py-[22px]' : 'text-gray-600 border-b-2 border-transparent hover:text-primary-500 py-[22px]' }} transition-colors">Katalog</a>
                    <button type="button" @click.prevent="howToRentModalOpen = true; mobileMenuOpen = false" class="text-gray-600 border-b-2 border-transparent hover:text-primary-500 py-[22px] transition-colors cursor-pointer font-semibold text-[14px]">Cara sewa</button>
                    @if(request()->routeIs('customer.checkout*'))
                        <span class="text-primary-500 border-b-2 border-primary-500 py-[22px] transition-colors font-semibold text-[14px]">Checkout</span>
                    @endif
                    @auth
                        <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}" class="{{ request()->routeIs('customer.orders*') ? 'text-primary-500 border-b-2 border-primary-500 py-[22px]' : 'text-gray-600 border-b-2 border-transparent hover:text-primary-500 py-[22px]' }} transition-colors">Pesanan Saya</a>
                    @endauth
                    <button type="button" @click.prevent="helpModalOpen = true; mobileMenuOpen = false" class="text-gray-600 border-b-2 border-transparent hover:text-primary-500 py-[22px] transition-colors cursor-pointer font-semibold text-[14px]">Bantuan</button>
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center space-x-5">
                    <!-- Search Bar (Header) -->
                    <!-- Search Bar Navbar Dihapus agar tidak double dengan hero search -->

                    <!-- Cart -->
                    <livewire:customer.cart-icon :client="$client" />

                    @auth
                        <!-- Bell Notification Icon Customer -->
                        @php
                            $unreadCustomerNotifs = \App\Models\Notification::where('user_id', auth()->id())
                                ->where('is_read', false)
                                ->count();
                        @endphp
                        <a href="{{ route('customer.notifications.index') }}"
                           class="relative p-2 rounded-xl text-gray-600 hover:text-primary-500 hover:bg-gray-100 transition-colors"
                           title="Pemberitahuan">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadCustomerNotifs > 0)
                                <span class="absolute top-1 right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-black text-white bg-primary-500 rounded-full ring-2 ring-white">
                                    {{ $unreadCustomerNotifs > 99 ? '99+' : $unreadCustomerNotifs }}
                                </span>
                            @endif
                        </a>
                    @endauth

                    @auth
                        <!-- Login / Avatar (Alpine Dropdown) -->
                        <div x-data="{ open: false }" class="relative flex items-center ml-1">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 focus:outline-none cursor-pointer">
                                @php
                                    $userAvatar = auth()->user()->avatar 
                                        ? (str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar))
                                        : "https://ui-avatars.com/api/?name=" . urlencode(auth()->user()->name) . "&background=f97316&color=fff&bold=true";
                                @endphp
                                <img src="{{ $userAvatar }}" 
                                     alt="Profile" 
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=f97316&color=fff&bold=true'"
                                     class="w-[42px] h-[42px] rounded-xl object-cover border border-gray-200 hover:border-primary-500 transition-colors">
                            </button>
                            
                            <div x-show="open" 
                                 x-transition.opacity.duration.200ms
                                 class="absolute right-0 top-12 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-100 z-50" 
                                 style="display: none;">
                                <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-sm font-bold text-gray-900">Halo, {{ auth()->user()->name }}!</p>
                                </div>
                                <a href="{{ route('customer.profile', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="block px-4 py-2 text-sm font-semibold {{ request()->routeIs('customer.profile*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-primary-600 hover:bg-primary-50' }} transition">Profil Saya</a>
                                <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:text-primary-600 hover:bg-primary-50 transition">Pesanan Saya</a>
                                <a href="{{ route('customer.notifications.index') }}" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:text-primary-600 hover:bg-primary-50 transition flex items-center justify-between">
                                    <span>Pemberitahuan</span>
                                    @if(isset($unreadCustomerNotifs) && $unreadCustomerNotifs > 0)
                                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-primary-50 text-primary-600">{{ $unreadCustomerNotifs }}</span>
                                    @endif
                                </a>
                                <div class="border-t border-gray-100 mt-1"></div>
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">Log out</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                    @if(request()->routeIs('customer.orders'))
                                        <input type="hidden" name="redirect" value="{{ route('customer.home', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}">
                                    @endif
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest Actions -->
                        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="hidden sm:inline-flex items-center justify-center gap-2 rounded-xl font-bold px-4 py-[9px] border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 text-[14px] transition-colors">Masuk</a>
                        <a href="{{ route('login', ['tab' => 'register', 'redirect' => request()->fullUrl()]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl font-bold px-4 py-[9px] border border-transparent bg-primary-500 text-white tenant-shadow-sm hover:bg-primary-600 active:scale-95 text-[14px] transition-all">Daftar</a>
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
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ (request()->routeIs('customer.home*') || request()->routeIs('customer.product.*')) ? 'text-primary-600 bg-primary-50 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Katalog</a>
                <button type="button" @click.prevent="howToRentModalOpen = true; mobileMenuOpen = false" class="w-full text-left block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 cursor-pointer">Cara sewa</button>
                @if(request()->routeIs('customer.checkout*'))
                    <span class="block px-3 py-2 rounded-lg text-base font-medium text-primary-600 bg-primary-50 font-semibold">Checkout</span>
                @endif
                @auth
                    <a href="{{ route('customer.profile', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('customer.profile*') ? 'text-primary-600 bg-primary-50 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Profil Saya</a>
                    <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('customer.orders*') ? 'text-primary-600 bg-primary-50 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Pesanan Saya</a>
                    <a href="{{ route('customer.notifications.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('customer.notifications*') ? 'text-primary-600 bg-primary-50 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Pemberitahuan</a>
                @endauth
                <button type="button" @click.prevent="helpModalOpen = true; mobileMenuOpen = false" class="w-full text-left block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 cursor-pointer">Bantuan</button>
                
                @auth
                    <div class="border-t border-gray-100 pt-2 mt-2">
                        <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">Akun Saya</div>
                        <a href="{{ route('customer.profile', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="block px-3 py-2 rounded-lg text-base font-semibold {{ request()->routeIs('customer.profile*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-primary-600' }}">Profil Saya</a>
                        <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-primary-600 hover:bg-primary-50">Pesanan Saya</a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-3 py-2 rounded-lg text-base font-medium text-red-600 hover:bg-red-50">Log out</a>
                    </div>
                @else
                    <div class="border-t border-gray-100 pt-3 mt-2 flex flex-col gap-2">
                        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="w-full text-center py-2.5 font-bold rounded-xl border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">Masuk</a>
                        <a href="{{ route('login', ['tab' => 'register', 'redirect' => request()->fullUrl()]) }}" class="w-full text-center py-2.5 font-bold rounded-xl bg-primary-500 text-white hover:bg-primary-600">Daftar</a>
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
                    <b class="text-white text-sm block mb-1">{{ $tenantProfile?->name ?? $client->nama_usaha ?? 'BabyRent' }}</b>
                    {{ $tenantProfile?->address ?? $client->address ?? 'Alamat operasional toko' }}
                    @if($tenantProfile?->city ?? $client->city)
                        , {{ $tenantProfile?->city ?? $client->city }}
                    @endif
                    <br>
                    WhatsApp: {{ $tenantProfile?->whatsapp ?? $client->phone ?? '-' }}
                </div>
                <div class="text-right">
                    Ditenagai <b class="text-primary-500">RentalBase</b><br>
                    &copy; {{ date('Y') }} {{ $tenantProfile?->name ?? $client->nama_usaha ?? 'RentalBase' }}.
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal 1: Panduan Cara Sewa & Jaminan KTP -->
    <div x-show="howToRentModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="how-to-rent-title" 
         role="dialog" 
         aria-modal="true"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="howToRentModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="howToRentModalOpen = false" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div x-show="howToRentModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                
                {{-- Header Modal --}}
                <div class="p-6 pb-4 border-b border-gray-100 flex items-start justify-between gap-4 bg-slate-50/60">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center flex-none shadow-xs border border-primary-100">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="how-to-rent-title" class="text-lg font-black text-gray-900 tracking-tight">
                                Panduan Cara Sewa &amp; Jaminan Identitas
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5 font-medium">
                                Alur penyewaan mudah di {{ $shopName }} — <strong>Tanpa deposit uang tunai!</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="howToRentModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body Modal: 4 Langkah --}}
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    {{-- Step 1 --}}
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white border border-gray-100 shadow-2xs hover:border-gray-200 transition">
                        <span class="w-8 h-8 rounded-xl bg-primary-500 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                            1
                        </span>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-gray-900">Pilih Alat &amp; Tentukan Tanggal Sewa</h4>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                Jelajahi katalog perlengkapan, pilih tanggal mulai dan selesai sewa untuk memeriksa ketersediaan stok unit secara real-time, lalu masukkan ke keranjang.
                            </p>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white border border-gray-100 shadow-2xs hover:border-gray-200 transition">
                        <span class="w-8 h-8 rounded-xl bg-primary-500 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                            2
                        </span>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-gray-900">Checkout &amp; Verifikasi Foto KTP</h4>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                Tentukan metode penerimaan (diantar kurir atau ambil di outlet), gunakan template alamat tersimpan, serta unggah foto KTP &amp; swafoto untuk screening awal.
                            </p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-primary-50/50 border border-primary-100 shadow-2xs">
                        <span class="w-8 h-8 rounded-xl bg-primary-500 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                            3
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900">Pembayaran &amp; Serah Terima (Titip KTP Asli)</h4>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-primary-100 text-primary-700">Wajib Titip KTP</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                Setelah identitas diverifikasi admin, lakukan pembayaran sewa via transfer/QRIS. Saat serah terima alat, <strong>serahkan fisik KTP asli sebagai jaminan sewa (tanpa deposit uang tunai)</strong>.
                            </p>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white border border-gray-100 shadow-2xs hover:border-gray-200 transition">
                        <span class="w-8 h-8 rounded-xl bg-primary-500 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                            4
                        </span>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-gray-900">Pengembalian Alat &amp; KTP Fisik Kembali</h4>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                Kembalikan alat sesuai batas tanggal sewa. Setelah pengecekan fisik selesai dan bebas denda, <strong>dokumen KTP asli Anda langsung diserahkan kembali utuh</strong>.
                            </p>
                        </div>
                    </div>

                    {{-- Highlight Info Box --}}
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex gap-3 text-xs text-amber-900 leading-relaxed">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong class="font-bold block mb-0.5">Keuntungan Jaminan KTP Asli:</strong>
                            Anda tidak perlu mengendapkan deposit uang tunai jutaan rupiah. Cukup serahkan KTP fisik asli saat serah terima barang, dan rawat alat sewa dengan baik.
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 sm:p-5 bg-gray-50 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                    <span class="text-[11px] text-gray-400 text-center sm:text-left">
                        Ada pertanyaan? Cek menu Bantuan atau hubungi admin toko.
                    </span>
                    <button type="button" 
                            @click="howToRentModalOpen = false" 
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs transition shadow-sm active:scale-95 cursor-pointer">
                        Mengerti, Tutup Panduan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Pusat Bantuan & Kontak Toko -->
    <div x-show="helpModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="help-modal-title" 
         role="dialog" 
         aria-modal="true"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="helpModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="helpModalOpen = false" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div x-show="helpModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                
                {{-- Header Modal --}}
                <div class="p-6 pb-4 border-b border-gray-100 flex items-start justify-between gap-4 bg-slate-50/60">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-none shadow-xs border border-emerald-100">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="help-modal-title" class="text-lg font-black text-gray-900 tracking-tight">
                                Pusat Bantuan &amp; Tanya Jawab
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5 font-medium">
                                Informasi seputar sewa, jaminan, dan kontak resmi {{ $shopName }}
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="helpModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    {{-- FAQ List --}}
                    <div class="space-y-3">
                        <div class="p-3.5 rounded-2xl bg-white border border-gray-100 shadow-2xs">
                            <h4 class="text-xs font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                Apakah perlu membayar uang deposit jaminan?
                            </h4>
                            <p class="text-xs text-gray-600 mt-1 pl-4 leading-relaxed">
                                Tidak perlu deposit uang tunai. Jaminan sewa berupa <strong>fisik KTP asli pelanggan</strong> yang dititipkan saat serah terima barang dan dikembalikan utuh setelah masa sewa selesai.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white border border-gray-100 shadow-2xs">
                            <h4 class="text-xs font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                Kapan pembayaran sewa dilakukan?
                            </h4>
                            <p class="text-xs text-gray-600 mt-1 pl-4 leading-relaxed">
                                Pembayaran dibuka setelah data KTP Anda diverifikasi Admin toko. Anda akan mendapatkan notifikasi dan instruksi transfer/QRIS di halaman pesanan.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white border border-gray-100 shadow-2xs">
                            <h4 class="text-xs font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                Bagaimana jika terlambat mengembalikan alat?
                            </h4>
                            <p class="text-xs text-gray-600 mt-1 pl-4 leading-relaxed">
                                Keterlambatan dikenakan tarif denda harian sesuai harga sewa. Fisik KTP asli akan dikembalikan setelah denda diselesaikan.
                            </p>
                        </div>
                    </div>

                    {{-- Kontak Outlet Card --}}
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-900">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Kontak Operasional {{ $shopName }}</span>
                        </div>
                        <div class="text-xs text-gray-600 space-y-1 pl-6">
                            <p><strong>Alamat:</strong> {{ $tenantProfile?->address ?? $client->address ?? 'Outlet Resmi RentalBase' }}</p>
                            <p><strong>WhatsApp:</strong> {{ $rawPhone ?: '-' }}</p>
                        </div>

                        @if($waNumber)
                        <div class="pt-1">
                            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Halo Admin ' . $shopName . ', saya ingin bertanya seputar sewa alat di toko Anda.') }}" 
                               target="_blank" 
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/>
                                </svg>
                                <span>Chat WhatsApp Admin Toko</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 sm:p-5 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="button" 
                            @click="helpModalOpen = false" 
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-xs transition active:scale-95 cursor-pointer">
                        Tutup Bantuan
                    </button>
                </div>
            </div>
        </div>
    @auth
    @if(config('broadcasting.connections.pusher.key'))
    {{-- Pusher & Echo Client for Customer Real-Time Order Tracking --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        (function() {
            window.ensureCustomerEcho = function() {
                if (window.Echo && typeof window.Echo.private === 'function') {
                    return window.Echo;
                }
                const EchoCtor = (typeof window.Echo === 'function') ? window.Echo : (typeof Echo !== 'undefined' ? Echo : null);
                if (EchoCtor && typeof Pusher !== 'undefined') {
                    window.Pusher = Pusher;
                    window.Echo = new EchoCtor({
                        broadcaster: 'pusher',
                        key: '{{ config('broadcasting.connections.pusher.key') }}',
                        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                        forceTLS: true,
                        csrfToken: '{{ csrf_token() }}',
                        authEndpoint: '/broadcasting/auth',
                        auth: {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }
                    });
                    return window.Echo;
                }
                return null;
            };
            window.ensureCustomerEcho();
            window.addEventListener('load', window.ensureCustomerEcho);
        })();
    </script>

    {{-- Real-Time Customer Toast Notification --}}
    <div x-data="customerRealtimeNotification({{ auth()->id() }})" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="pointer-events-auto bg-white border border-emerald-200 rounded-2xl shadow-2xl p-4 flex items-start gap-3 transform transition-all duration-300 ease-out"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2">
                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-900" x-text="toast.title"></h4>
                    <p class="text-xs text-gray-600 mt-0.5" x-text="toast.message"></p>
                    <div class="mt-2 flex items-center gap-2">
                        <a x-show="toast.url" :href="toast.url" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Buka Pesanan &rarr;</a>
                        <button @click="removeToast(toast.id)" class="text-xs text-gray-400 hover:text-gray-600 ml-auto">Tutup</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function customerRealtimeNotification(userId) {
            return {
                toasts: [],
                init() {
                    const startListening = () => {
                        const echo = (typeof window.ensureCustomerEcho === 'function')
                            ? window.ensureCustomerEcho()
                            : (window.Echo && typeof window.Echo.private === 'function' ? window.Echo : null);

                        if (!echo) {
                            return false;
                        }

                        echo.private(`customer.${userId}.orders`)
                            .error((error) => {
                                console.error('[RentalBase Realtime] Gagal autentikasi channel customer:', error);
                            })
                        .listen('.identity.approved', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Identitas KTP Disetujui!', e.message || `KTP pesanan #${e.order_number} disetujui. Silakan selesaikan pembayaran.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.identity.rejected', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Identitas KTP Ditolak!', e.message || `KTP pesanan #${e.order_number} perlu direvisi.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.payment.verified', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Pembayaran Diterima!', e.message || `Pembayaran pesanan #${e.order_number} telah diverifikasi admin.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.payment.rejected', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Pembayaran Ditolak!', e.message || `Bukti bayar pesanan #${e.order_number} ditolak. Silakan unggah bukti yang benar.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.order.confirmed', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Pesanan Dikonfirmasi!', e.message || `Pesanan #${e.order_number} telah dikonfirmasi oleh toko.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.order.shipped', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            const title = (e.status === 'sedang_disewa') ? 'Barang Telah Diserahkan!' : 'Barang Sedang Dikirim!';
                            this.addToast(title, e.message || `Pesanan #${e.order_number} sedang dalam perjalanan.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.return.accepted', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Pengembalian Diterima!', e.message || `Pengembalian pesanan #${e.order_number} telah diverifikasi toko.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.fine.issued', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Denda Keterlambatan!', e.message || `Tagihan denda keterlambatan terbit untuk pesanan #${e.order_number}.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.damage.created', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id + '/damage') : '/my-orders';
                            this.addToast('Klaim Kerusakan!', e.message || `Laporan klaim kerusakan diterbitkan untuk pesanan #${e.order_number}.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .listen('.damage.resolved', (e) => {
                            this.playChime();
                            const targetUrl = e.order_id ? ('/my-orders/' + e.order_id) : '/my-orders';
                            this.addToast('Klaim Kerusakan Selesai!', e.message || `Klaim kerusakan pesanan #${e.order_number} telah diselesaikan.`, targetUrl);
                            if (window.location.pathname.includes('/my-orders')) {
                                setTimeout(() => window.location.reload(), 800);
                            }
                        });
                    return true;
                };

                if (!startListening()) {
                    let attempts = 0;
                    const poll = setInterval(() => {
                        attempts++;
                        if (startListening() || attempts >= 30) {
                            clearInterval(poll);
                        }
                    }, 150);
                }
            },
                addToast(title, message, url = null) {
                    const id = Date.now();
                    this.toasts.push({ id, title, message, url });
                    setTimeout(() => this.removeToast(id), 7000);
                },
                removeToast(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                },
                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(659.25, ctx.currentTime);
                        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
                        gain.gain.setValueAtTime(0.2, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.5);
                    } catch (err) {}
                }
            };
        }
    </script>
    @endif
    @endauth

    @livewireScriptConfig
</body>
</html>
