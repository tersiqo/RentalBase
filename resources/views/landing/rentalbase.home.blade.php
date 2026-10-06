<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalBase - Kelola Bisnis Rental Peralatan dalam Satu Sistem</title>
    
    <!-- CDN Links -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#f97316', // Orange-500
                            hover: '#ea580c',   // Orange-600
                            light: '#ffedd5',   // Orange-100
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased selection:bg-brand selection:text-white">

    <!-- NAVBAR START -->
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" class="flex-shrink-0 flex items-center gap-2 group">
                        <div class="w-10 h-10 bg-brand text-white rounded-lg flex items-center justify-center text-xl font-bold group-hover:bg-brand-hover transition">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <span class="font-extrabold text-2xl text-gray-900 tracking-tight">Rental<span class="text-brand">Base</span></span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#fitur" class="text-gray-600 hover:text-brand font-medium transition">Fitur</a>
                    <a href="#harga" class="text-gray-600 hover:text-brand font-medium transition">Harga</a>
                    <a href="#kontak" class="text-gray-600 hover:text-brand font-medium transition">Kontak</a>
                    <div class="h-6 w-px bg-gray-200"></div>
                    <a href="#" class="text-gray-700 hover:text-brand font-medium transition">Login Admin/Owner</a>
                    <a href="/register-tenant" class="bg-brand hover:bg-brand-hover text-white px-6 py-2.5 rounded-full font-semibold transition shadow-lg shadow-orange-200/50 transform hover:-translate-y-0.5">Buka Toko Gratis</a>
                </div>
                
                <!-- Mobile Menu Button -->
                <div class="-mr-2 flex items-center md:hidden">
                    <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                        <i class="fa-solid fa-bars text-2xl" x-show="!open"></i>
                        <i class="fa-solid fa-xmark text-2xl" x-show="open" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu Dropdown -->
        <div x-show="open" class="md:hidden bg-white border-b border-gray-100 absolute w-full shadow-lg" x-transition x-cloak>
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="#fitur" class="block px-3 py-3 rounded-md text-base font-medium text-gray-700 hover:text-brand hover:bg-brand-light">Fitur</a>
                <a href="#harga" class="block px-3 py-3 rounded-md text-base font-medium text-gray-700 hover:text-brand hover:bg-brand-light">Harga</a>
                <a href="#kontak" class="block px-3 py-3 rounded-md text-base font-medium text-gray-700 hover:text-brand hover:bg-brand-light">Kontak</a>
                <div class="border-t border-gray-100 my-2"></div>
                <a href="#" class="block px-3 py-3 rounded-md text-base font-medium text-gray-700 hover:text-brand hover:bg-brand-light">Login</a>
                <a href="/register-tenant" class="block px-3 py-3 rounded-md text-base font-bold text-center text-white bg-brand hover:bg-brand-hover mt-4">Buka Toko Gratis</a>
            </div>
        </div>
    </nav>
    <!-- NAVBAR END -->

    <!-- HERO SECTION START -->
    <section class="relative bg-white overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-brand-light/30 rounded-bl-[100px] -z-10 hidden lg:block"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24">
            <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold text-brand bg-brand-light mb-6 border border-orange-200">
                        <span class="flex h-2 w-2 rounded-full bg-brand mr-2 animate-pulse"></span>
                        Platform Manajemen Rental #1
                    </div>
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl lg:leading-tight">
                        Kelola Bisnis Rental <br>
                        <span class="text-brand relative">
                            dalam Satu Sistem
                            <svg class="absolute w-full h-3 -bottom-1 left-0 text-brand-light -z-10" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 5 Q 50 15 100 5 L 100 10 L 0 10 Z" fill="currentColor"></path></svg>
                        </span>
                    </h1>
                    <p class="mt-6 text-base text-gray-600 sm:text-lg lg:text-xl leading-relaxed">
                        Tinggalkan pencatatan manual. Pantau ketersediaan barang, jadwal booking, transaksi, hingga laporan kerusakan unit secara real-time.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 sm:justify-center lg:justify-start">
                        <a href="/register-tenant" class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-bold rounded-full shadow-lg shadow-orange-200/50 text-white bg-brand hover:bg-brand-hover hover:-translate-y-1 transition duration-300">
                            Mulai Buka Toko Gratis
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                    <div class="mt-6 flex items-center gap-4 text-sm text-gray-500 sm:justify-center lg:justify-start">
                        <div class="flex -space-x-2">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=1" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=2" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=3" alt="User">
                        </div>
                        <p>Dipercaya oleh 500+ pemilik rental</p>
                    </div>
                </div>
                <div class="mt-16 relative sm:max-w-lg sm:mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-span-6">
                    <div class="relative mx-auto w-full rounded-2xl shadow-2xl overflow-hidden bg-white border border-gray-100 aspect-[4/3] flex flex-col">
                        <div class="bg-gray-100 px-4 py-3 flex items-center gap-2 border-b">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex-1 flex items-center justify-center bg-gray-50 relative overflow-hidden group">
                            <!-- Placeholder untuk Image Dashboard Asli -->
                            <i class="fa-solid fa-chart-pie text-8xl text-gray-200 group-hover:scale-110 transition duration-500"></i>
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-100/80 to-transparent"></div>
                            <p class="absolute bottom-6 font-bold text-gray-400 text-lg uppercase tracking-widest">Dashboard Preview</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- HERO SECTION END -->

    <!-- FEATURES SECTION START -->
    <section id="fitur" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-brand font-bold tracking-widest uppercase text-sm mb-3">Fitur Unggulan</h2>
                <p class="text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Didesain Khusus untuk Rental Fisik
                </p>
                <p class="mt-4 max-w-2xl text-lg text-gray-600 mx-auto">
                    Bukan sekadar sistem kasir, RentalBase memahami alur kerja penyewaan barang dari awal pemesanan hingga pengembalian.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Kalender Booking</h3>
                    <p class="text-gray-600 leading-relaxed">Atur jadwal ketersediaan unit secara visual. Bebas dari risiko bentrok penyewaan (double booking).</p>
                </div>
                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Inventaris Cerdas</h3>
                    <p class="text-gray-600 leading-relaxed">Lacak status barang (tersedia, disewa, rusak). Catat riwayat perbaikan tiap unit dengan detail.</p>
                </div>
                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Faktur & Denda</h3>
                    <p class="text-gray-600 leading-relaxed">Buat invoice otomatis, catat deposit, dan kalkulasi denda keterlambatan secara instan.</p>
                </div>
                <!-- Feature 4 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Laporan Analitik</h3>
                    <p class="text-gray-600 leading-relaxed">Analisis omzet, unit paling laris, dan performa bisnis Anda melalui dashboard interaktif.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- FEATURES SECTION END -->

    <!-- PRICING SECTION START -->
    <section id="harga" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <h2 class="text-brand font-bold tracking-widest uppercase text-sm mb-3">Harga Langganan</h2>
                <p class="text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Investasi Tepat untuk Usaha Anda
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto items-stretch">
                @if(isset($packages))
                    @foreach($packages as $index => $pkg)
                        @php
                            $isPopular = $pkg['name'] === 'Business';
                        @endphp
                        <div class="bg-white border-2 {{ $isPopular ? 'border-brand rounded-3xl p-8 shadow-xl relative transform lg:-translate-y-4' : 'border-gray-200 rounded-3xl p-8 shadow-sm' }} flex flex-col">
                            @if($isPopular)
                                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-brand text-white px-6 py-1.5 rounded-full text-sm font-bold uppercase tracking-widest shadow-md">
                                    Paling Populer
                                </div>
                            @endif
                            <h3 class="text-2xl font-bold text-gray-900 mb-2 {{ $isPopular ? 'mt-2' : '' }}">{{ $pkg['name'] }}</h3>
                            <p class="text-gray-500 mb-6 min-h-[48px]">{{ $pkg['description'] }}</p>
                            <div class="mb-4 flex items-baseline">
                                <span class="text-3xl font-extrabold text-gray-900">{{ $pkg['price'] }}</span>
                            </div>
                            <div class="text-sm font-bold text-brand mb-6">Durasi: {{ $pkg['duration'] }}</div>
                            <ul class="space-y-4 text-gray-{{ $isPopular ? '800' : '600' }} mb-8 flex-1">
                                @foreach($pkg['features'] as $feature)
                                    <li class="flex items-start">
                                        <i class="fa-solid fa-check{{ $isPopular ? '-circle' : '' }} text-brand mt-1 mr-3 {{ $isPopular ? 'text-lg' : '' }}"></i> 
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            <a href="/register-tenant" class="block w-full py-4 px-4 {{ $isPopular ? 'bg-brand text-white hover:bg-brand-hover shadow-lg shadow-orange-200/50' : 'bg-brand-light text-brand hover:bg-orange-200' }} font-bold text-center rounded-xl transition">
                                Pilih Paket {{ $pkg['name'] }}
                            </a>
                        </div>
                    @endforeach
                @else
                    <!-- Fallback hardcoded if packages are not set -->
                    <p>Paket sedang tidak tersedia.</p>
                @endif
            </div>
        </div>
    </section>
    <!-- PRICING SECTION END -->

    <!-- CTA SECTION START -->
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-brand rounded-3xl shadow-xl overflow-hidden relative">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white opacity-20 rounded-full blur-3xl"></div>
                
                <div class="relative px-8 py-16 sm:px-16 sm:py-20 text-center">
                    <h2 class="text-3xl font-extrabold text-white sm:text-4xl mb-4">
                        Siap Membawa Bisnis Rental Anda ke Level Berikutnya?
                    </h2>
                    <p class="text-orange-100 text-lg mb-10 max-w-2xl mx-auto">
                        Ribuan pemilik rental telah menghemat waktu dan meningkatkan profit mereka. Mulai kelola dengan profesional sekarang.
                    </p>
                    <a href="/register-tenant" class="inline-flex items-center justify-center px-10 py-4 text-lg font-bold rounded-full text-brand bg-white hover:bg-gray-50 hover:scale-105 transition duration-300 shadow-xl">
                        Buka Toko Sekarang — Gratis
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- CTA SECTION END -->

    <!-- FOOTER START -->
    <footer id="kontak" class="bg-white border-t border-gray-200 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8 mb-12">
                <div class="md:col-span-2">
                    <a href="/" class="flex-shrink-0 flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-brand text-white rounded-lg flex items-center justify-center font-bold">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <span class="font-extrabold text-xl text-gray-900 tracking-tight">Rental<span class="text-brand">Base</span></span>
                    </a>
                    <p class="text-gray-500 max-w-sm mb-6 leading-relaxed">Platform SaaS andalan pengusaha rental. Mendukung manajemen penyewaan alat fotografi, kendaraan, perlengkapan bayi, dan masih banyak lagi.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-brand hover:text-white transition"><i class="fa-brands fa-instagram text-lg"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-brand hover:text-white transition"><i class="fa-brands fa-facebook-f text-lg"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-brand hover:text-white transition"><i class="fa-brands fa-linkedin-in text-lg"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-4 uppercase text-sm tracking-wider">Perusahaan</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-500 hover:text-brand transition">Tentang Kami</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-brand transition">Kontak Developer</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-brand transition">Pusat Bantuan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-4 uppercase text-sm tracking-wider">Legal</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-500 hover:text-brand transition">Syarat & Ketentuan (Terms)</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-brand transition">Kebijakan Privasi (Privacy)</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} RentalBase. All rights reserved.</p>
                <p class="text-gray-400 text-sm mt-2 md:mt-0">Dibuat dengan <i class="fa-solid fa-heart text-brand mx-1 animate-pulse"></i> oleh Tim RentalBase</p>
            </div>
        </div>
    </footer>
    <!-- FOOTER END -->

</body>
</html>
