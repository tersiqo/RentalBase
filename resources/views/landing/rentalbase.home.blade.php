<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalBase - Equipment Rental System</title>
    <meta name="description" content="Sistem informasi manajemen penyewaan peralatan berbasis web berbasis multi-client untuk mengelola produk, stok, booking, dan transaksi rental.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
        }
    </style>
</head>
<body class="text-slate-800 antialiased selection:bg-emerald-500 selection:text-white min-h-screen flex flex-col justify-between">

    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="#beranda" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-lg bg-emerald-700 flex items-center justify-center text-white font-extrabold text-sm shadow-xs">
                    R
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-slate-900 text-base leading-tight tracking-tight">RentalBase</span>
                    <span class="text-[10px] text-slate-500 font-medium tracking-wide">Equipment Rental System</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-7 text-sm font-semibold text-slate-600">
                <a href="#beranda" class="hover:text-emerald-700 transition">Beranda</a>
                <a href="#paket" class="hover:text-emerald-700 transition">Paket</a>
                <a href="#fitur" class="hover:text-emerald-700 transition">Fitur</a>
                <a href="#cara-kerja" class="hover:text-emerald-700 transition">Cara Kerja</a>
            </nav>

            <!-- Actions -->
            <div class="hidden md:flex items-center gap-4">
                <a href="#login" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition px-2 py-1">Login</a>
                <a href="#paket" class="bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-xs">
                    Mulai Sekarang
                </a>
            </div>

            <!-- Mobile Hamburger Button -->
            <button id="mobile-menu-btn" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg border border-slate-200" aria-label="Toggle menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2">
            <a href="#beranda" class="block py-2 text-sm font-semibold text-slate-700 hover:text-emerald-700">Beranda</a>
            <a href="#paket" class="block py-2 text-sm font-semibold text-slate-700 hover:text-emerald-700">Paket</a>
            <a href="#fitur" class="block py-2 text-sm font-semibold text-slate-700 hover:text-emerald-700">Fitur</a>
            <a href="#cara-kerja" class="block py-2 text-sm font-semibold text-slate-700 hover:text-emerald-700">Cara Kerja</a>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                <a href="#login" class="text-sm font-semibold text-slate-600">Login</a>
                <a href="#paket" class="bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg">Mulai Sekarang</a>
            </div>
        </div>
    </header>

    <main id="beranda" class="flex-grow">
        <!-- Hero Section -->
        <section class="pt-10 sm:pt-14 pb-12 px-4 sm:px-6 max-w-6xl mx-auto">
            <div class="max-w-3xl mx-auto text-center space-y-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    Multi-Client Rental Management Platform
                </span>
                
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Kelola Bisnis Rental dalam Satu Sistem
                </h1>
                
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed font-normal max-w-2xl mx-auto">
                    RentalBase membantu usaha rental mengelola peralatan, stok, booking, pembayaran, pengiriman, pengembalian, dan kondisi barang secara lebih terstruktur.
                </p>

                <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                    <a href="#paket" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm px-5 py-3 rounded-xl transition shadow-xs flex items-center gap-2">
                        <span>Lihat Paket</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                    <a href="#fitur" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-sm px-5 py-3 rounded-xl transition">
                        Pelajari Fitur
                    </a>
                </div>
            </div>

            <!-- RentalBase UI Mockup -->
            <div class="mt-10 max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-slate-100/90 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between text-xs text-slate-500 font-medium">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-400 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>
                        <span class="ml-2 font-mono text-[11px] text-slate-600">rentalbase.com/app/dashboard</span>
                    </div>
                    <span class="hidden sm:inline bg-white px-2 py-0.5 rounded border border-slate-200 text-[10px] text-slate-600">RentalBase Core System</span>
                </div>
                <div class="p-4 sm:p-6 bg-slate-50/50 space-y-4">
                    <!-- Top Bar Mockup -->
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/80">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center font-bold text-indigo-700 text-xs">
                                JB
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Jaya Baby Rental</div>
                                <div class="text-[11px] text-slate-500">Subdomain: <code class="text-emerald-700 font-semibold">jaya</code></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-md border border-emerald-200/60">Lisensi Aktif</span>
                            <span class="bg-slate-100 text-slate-600 text-[11px] font-medium px-2.5 py-1 rounded-md">Client ID #104</span>
                        </div>
                    </div>

                    <!-- Mini Stat Cards Mockup -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80">
                            <div class="text-[11px] text-slate-500 font-medium">Total Peralatan</div>
                            <div class="text-lg font-extrabold text-slate-900 mt-0.5">32 Unit</div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80">
                            <div class="text-[11px] text-slate-500 font-medium">Booking Aktif</div>
                            <div class="text-lg font-extrabold text-emerald-700 mt-0.5">14 Pesanan</div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80">
                            <div class="text-[11px] text-slate-500 font-medium">Pengiriman Hari Ini</div>
                            <div class="text-lg font-extrabold text-indigo-600 mt-0.5">3 Unit</div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80">
                            <div class="text-[11px] text-slate-500 font-medium">Pengembalian</div>
                            <div class="text-lg font-extrabold text-amber-600 mt-0.5">2 Unit</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Highlight Paket License Section (Prioritas Utama Setelah Hero) -->
        <section id="paket" class="py-12 bg-white border-y border-slate-200/80">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        Pilihan Paket RentalBase
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm mt-2">
                        Pilih paket license yang sesuai dengan kebutuhan usaha rental Anda.
                    </p>
                </div>

                <!-- Package Cards Layout (Equal Hierarchy, No Badges/Rankings) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                    @foreach($packages as $pkg)
                        <div class="bg-slate-50/70 rounded-2xl border border-slate-200 p-6 flex flex-col justify-between hover:border-slate-300 transition">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-lg font-extrabold text-slate-900 tracking-tight uppercase">{{ $pkg['name'] }}</h3>
                                    <p class="text-xs text-slate-600 mt-1 min-h-[36px] leading-relaxed">
                                        {{ $pkg['description'] }}
                                    </p>
                                </div>

                                <div class="py-3 border-y border-slate-200/80">
                                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Durasi License</div>
                                    <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $pkg['duration'] }}</div>
                                </div>

                                <div>
                                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Cakupan Fitur Utama</div>
                                    <ul class="space-y-2 text-xs text-slate-700">
                                        @foreach($pkg['features'] as $feature)
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <div class="pt-6 mt-6 border-t border-slate-200/80 space-y-3">
                                <div>
                                    <div class="text-[10px] text-slate-500 font-medium">Biaya License</div>
                                    <div class="text-lg font-extrabold text-slate-900">{{ $pkg['price'] }}</div>
                                </div>
                                <a href="#paket" class="w-full inline-flex justify-center items-center bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold text-xs py-3 px-4 rounded-xl transition shadow-xs">
                                    Pilih Paket
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Fitur Utama Section -->
        <section id="fitur" class="py-12 sm:py-16 max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                    Fitur Utama RentalBase
                </h2>
                <p class="text-slate-600 text-xs sm:text-sm mt-2">
                    Fungsi operasional terstruktur untuk mendukung kelancaran penyewaan peralatan Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                <!-- Fitur 1 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">
                        01
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Peralatan & Stok</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Kelola inventaris peralatan rental, ketersediaan stok unit, dan varian secara terpusat.</p>
                    </div>
                </div>

                <!-- Fitur 2 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">
                        02
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Ketersediaan & Booking</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Jadwal penyewaan otomatis untuk memastikan ketersediaan barang tanpa bentrok tanggal.</p>
                    </div>
                </div>

                <!-- Fitur 3 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">
                        03
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Pembayaran</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pencatatan dan konfirmasi pembayaran uang sewa serta bukti transaksi secara akurat.</p>
                    </div>
                </div>

                <!-- Fitur 4 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">
                        04
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Pengiriman & Pengembalian</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pemantauan status alur pengiriman unit sewa hingga pengembalian tepat waktu.</p>
                    </div>
                </div>

                <!-- Fitur 5 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">
                        05
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Kondisi & Kerusakan</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pencatatan hasil pengecekan kondisi barang sebelum dan sesudah masa penyewaan.</p>
                    </div>
                </div>

                <!-- Fitur 6 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">
                        06
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Dashboard & Laporan</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pantau performa penyewaan, status operasional, dan rekapitulasi data harian.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cara Kerja Section -->
        <section id="cara-kerja" class="py-12 sm:py-16 bg-white border-t border-slate-200/80">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        Cara Kerja Platform
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm mt-2">
                        Empat langkah sederhana memulai penggunaan sistem RentalBase.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 sm:gap-6">
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 relative">
                        <div class="text-xs font-bold text-emerald-700 tracking-wider">01</div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Pilih Paket</h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Tentukan paket lisensi RentalBase yang sesuai skala bisnis Anda.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 relative">
                        <div class="text-xs font-bold text-emerald-700 tracking-wider">02</div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Aktifkan License</h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Dapatkan lisensi dan akses konfigurasi awal akun rental Anda.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 relative">
                        <div class="text-xs font-bold text-emerald-700 tracking-wider">03</div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Kelola Usaha Rental</h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Atur katalog peralatan, stok, harga, dan branding penyewaan.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 relative">
                        <div class="text-xs font-bold text-emerald-700 tracking-wider">04</div>
                        <h3 class="font-bold text-slate-900 text-sm mt-1">Layani Customer</h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Terima booking sewa dan proses transaksi rental secara terstruktur.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Keunggulan Section -->
        <section class="py-12 sm:py-16 max-w-6xl mx-auto px-4 sm:px-6">
            <div class="bg-slate-900 text-white rounded-2xl p-6 sm:p-10 shadow-sm">
                <div class="max-w-xl mb-8">
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Dirancang untuk Bisnis Rental</h2>
                    <p class="text-slate-400 text-xs sm:text-sm mt-2">Solusi perangkat lunak terpusat yang fleksibel untuk beragam kategori rental peralatan.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-300">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span>Satu sistem untuk proses rental</span>
                    </div>
                    <div class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-300">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span>Data setiap client tetap terpisah</span>
                    </div>
                    <div class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-300">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span>Dapat digunakan berbagai usaha rental</span>
                    </div>
                    <div class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-300">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span>Client memiliki halaman rental sendiri</span>
                    </div>
                    <div class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-300">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span>Admin Rental mengelola operasional</span>
                    </div>
                    <div class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-300">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span>Customer penyewaan via web</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="pb-16 pt-4 max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-8 space-y-4">
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                    Siap Mengelola Bisnis Rental dengan Lebih Terstruktur?
                </h2>
                <div>
                    <a href="#paket" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition shadow-xs">
                        <span>Mulai Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 text-slate-600 text-xs py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-emerald-700 flex items-center justify-center text-white font-bold text-xs">R</div>
                <div>
                    <span class="font-bold text-slate-900">RentalBase</span>
                    <span class="text-slate-400 text-[11px] ml-1.5">— Equipment Rental System</span>
                </div>
            </div>

            <nav class="flex items-center gap-5 font-semibold text-slate-600">
                <a href="#beranda" class="hover:text-emerald-700 transition">Beranda</a>
                <a href="#paket" class="hover:text-emerald-700 transition">Paket</a>
                <a href="#fitur" class="hover:text-emerald-700 transition">Fitur</a>
                <a href="#cara-kerja" class="hover:text-emerald-700 transition">Cara Kerja</a>
            </nav>

            <div class="text-slate-400 text-[11px]">
                &copy; {{ date('Y') }} RentalBase. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Toggle Script -->
    <script>
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
