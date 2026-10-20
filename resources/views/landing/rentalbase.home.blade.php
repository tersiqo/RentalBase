<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalBase - Kelola Bisnis Rental Peralatan dalam Satu Sistem</title>
    
    <!-- CDN Links -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine Plugins (must load before Alpine core) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
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
<body x-data="{ showRoleModal: false }" class="bg-gray-50 text-gray-800 antialiased selection:bg-brand selection:text-white">

    <!-- NAVBAR START -->
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" 
                       x-data="{
                           clickCount: 0,
                           timer: null,
                           handleLogoClick(e) {
                               this.clickCount++;
                               if (this.clickCount === 1) {
                                   this.timer = setTimeout(() => {
                                       this.clickCount = 0;
                                       window.location.href = '/';
                                   }, 500);
                                   e.preventDefault();
                               } else if (this.clickCount === 2) {
                                   e.preventDefault();
                               } else if (this.clickCount >= 3) {
                                   clearTimeout(this.timer);
                                   this.clickCount = 0;
                                   e.preventDefault();
                                   window.location.href = '{{ route('owner.login') }}';
                               }
                           }
                       }"
                       @click="handleLogoClick($event)"
                       class="flex-shrink-0 flex items-center gap-2 group cursor-pointer select-none"
                       title="RentalBase">
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
                    <a href="/login" class="text-gray-700 hover:text-brand font-medium transition">Login</a>
                    <button type="button" @click="showRoleModal = true" class="bg-brand hover:bg-brand-hover text-white px-6 py-2.5 rounded-full font-semibold transition shadow-lg shadow-orange-200/50 transform hover:-translate-y-0.5 cursor-pointer">Daftar Sekarang</button>
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
                <a href="/login" class="block px-3 py-3 rounded-md text-base font-medium text-gray-700 hover:text-brand hover:bg-brand-light">Login</a>
                <button type="button" @click="open = false; showRoleModal = true" class="block w-full px-3 py-3 rounded-md text-base font-bold text-center text-white bg-brand hover:bg-brand-hover mt-4 cursor-pointer">Daftar Sekarang</button>
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

                <!-- ===== LEFT COLUMN: staggered fade-in-up via Alpine x-data/x-init ===== -->
                <div
                    x-data="{
                        s0: false, s1: false, s2: false, s3: false, s4: false
                    }"
                    x-init="
                        setTimeout(() => s0 = true, 100);
                        setTimeout(() => s1 = true, 280);
                        setTimeout(() => s2 = true, 460);
                        setTimeout(() => s3 = true, 620);
                        setTimeout(() => s4 = true, 780);
                    "
                    class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left"
                >
                    <!-- Badge -->
                    <div
                        x-show="s0"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 -translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display:none"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold text-brand bg-brand-light mb-6 border border-orange-200"
                    >
                        <span class="flex h-2 w-2 rounded-full bg-brand mr-2 animate-pulse"></span>
                        Platform Manajemen Rental #1
                    </div>

                    <!-- Headline -->
                    <h1
                        x-show="s1"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display:none"
                        class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl lg:leading-tight"
                    >
                        Kelola Bisnis Rental <br>
                        <span class="text-brand relative">
                            dalam Satu Sistem
                            <svg class="absolute w-full h-3 -bottom-1 left-0 text-brand-light -z-10" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 5 Q 50 15 100 5 L 100 10 L 0 10 Z" fill="currentColor"></path></svg>
                        </span>
                    </h1>

                    <!-- Description -->
                    <p
                        x-show="s2"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display:none"
                        class="mt-6 text-base text-gray-600 sm:text-lg lg:text-xl leading-relaxed"
                    >
                        Tinggalkan pencatatan manual. Pantau ketersediaan barang, jadwal booking, transaksi, hingga laporan kerusakan unit secara real-time.
                    </p>

                    <!-- CTA Button -->
                    <div
                        x-show="s3"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display:none"
                        class="mt-10 flex flex-col sm:flex-row gap-4 sm:justify-center lg:justify-start"
                    >
                        <button type="button" @click="showRoleModal = true" class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-bold rounded-full shadow-lg shadow-orange-200/50 text-white bg-brand hover:bg-brand-hover hover:-translate-y-1 transition duration-300 cursor-pointer">
                            Daftar Sekarang
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>

                    <!-- Social Proof -->
                    <div
                        x-show="s4"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display:none"
                        class="mt-6 flex items-center gap-4 text-sm text-gray-500 sm:justify-center lg:justify-start"
                    >
                        <div class="flex -space-x-2">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=1" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=2" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://i.pravatar.cc/100?img=3" alt="User">
                        </div>
                        <p>Dipercaya oleh 500+ pemilik rental</p>
                    </div>
                </div>

                <!-- ===== RIGHT COLUMN: Browser Mockup + SVG Dashboard ===== -->
                <div
                    x-data="{ showCard: false }"
                    x-init="setTimeout(() => showCard = true, 900)"
                    class="mt-16 relative sm:max-w-lg sm:mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-span-6"
                >
                    <div
                        x-show="showCard"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        style="display:none"
                        class="relative mx-auto w-full rounded-2xl shadow-2xl overflow-hidden bg-white border border-gray-100 flex flex-col"
                    >
                        <!-- Browser chrome bar -->
                        <div class="bg-gray-100 px-4 py-3 flex items-center gap-2 border-b border-gray-200 flex-shrink-0">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <div class="ml-3 flex-1 bg-white rounded-md px-3 py-1 text-xs text-gray-400 border border-gray-200 max-w-xs">rentalbase.app/dashboard</div>
                        </div>

                        <!-- SVG Dashboard Mockup — viewBox 800x500, fills the card -->
                        <svg
                            viewBox="0 0 800 500"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-full h-auto block"
                            style="aspect-ratio: 8/5;"
                        >
                            <!-- ── Background ── -->
                            <rect width="800" height="500" fill="#f8fafc"/>

                            <!-- ── Sidebar ── -->
                            <rect x="0" y="0" width="160" height="500" fill="#1e293b"/>
                            <!-- Sidebar: Logo -->
                            <rect x="14" y="16" width="30" height="30" rx="7" fill="#f97316"/>
                            <text x="29" y="36" font-family="Inter,sans-serif" font-size="15" font-weight="800" fill="white" text-anchor="middle">R</text>
                            <text x="54" y="36" font-family="Inter,sans-serif" font-size="12" font-weight="700" fill="white">Rental</text>
                            <text x="102" y="36" font-family="Inter,sans-serif" font-size="12" font-weight="700" fill="#f97316">Base</text>
                            <!-- Sidebar: Active item -->
                            <rect x="10" y="64" width="140" height="34" rx="8" fill="#f97316"/>
                            <text x="28" y="86" font-family="Inter,sans-serif" font-size="11" fill="white" font-weight="600">⊞  Dashboard</text>
                            <!-- Sidebar: Other menu items -->
                            <text x="28" y="124" font-family="Inter,sans-serif" font-size="11" fill="#94a3b8">📦  Unit Rental</text>
                            <text x="28" y="158" font-family="Inter,sans-serif" font-size="11" fill="#94a3b8">📅  Booking</text>
                            <text x="28" y="192" font-family="Inter,sans-serif" font-size="11" fill="#94a3b8">💳  Transaksi</text>
                            <text x="28" y="226" font-family="Inter,sans-serif" font-size="11" fill="#94a3b8">📊  Laporan</text>
                            <text x="28" y="260" font-family="Inter,sans-serif" font-size="11" fill="#94a3b8">⚙️  Pengaturan</text>
                            <!-- Sidebar: Avatar at bottom -->
                            <circle cx="80" cy="468" r="18" fill="#334155"/>
                            <text x="80" y="474" font-family="Inter,sans-serif" font-size="13" font-weight="700" fill="#f97316" text-anchor="middle">A</text>
                            <text x="104" y="466" font-family="Inter,sans-serif" font-size="9" fill="white">Admin</text>
                            <text x="104" y="477" font-family="Inter,sans-serif" font-size="8" fill="#64748b">Superadmin</text>

                            <!-- ── Top Header Bar ── -->
                            <rect x="160" y="0" width="640" height="52" fill="white"/>
                            <line x1="160" y1="52" x2="800" y2="52" stroke="#e2e8f0" stroke-width="1"/>
                            <text x="180" y="32" font-family="Inter,sans-serif" font-size="15" font-weight="700" fill="#0f172a">Dashboard</text>
                            <text x="450" y="25" font-family="Inter,sans-serif" font-size="10" fill="#94a3b8">Oktober 2026</text>
                            <!-- Notification bell -->
                            <circle cx="740" cy="26" r="14" fill="#f1f5f9"/>
                            <text x="740" y="31" font-family="Inter,sans-serif" font-size="12" fill="#64748b" text-anchor="middle">🔔</text>
                            <!-- Avatar -->
                            <circle cx="775" cy="26" r="14" fill="#ffedd5"/>
                            <text x="775" y="31" font-family="Inter,sans-serif" font-size="11" font-weight="700" fill="#f97316" text-anchor="middle">A</text>

                            <!-- ── Stat Cards Row ── -->
                            <!-- Card 1: Total Unit -->
                            <rect x="170" y="68" width="140" height="80" rx="10" fill="white" stroke="#e2e8f0" stroke-width="1" filter="url(#shadow)"/>
                            <rect x="180" y="78" width="28" height="28" rx="7" fill="#ffedd5"/>
                            <text x="194" y="97" font-family="Inter,sans-serif" font-size="14" fill="#f97316" text-anchor="middle">▦</text>
                            <text x="180" y="123" font-family="Inter,sans-serif" font-size="9" fill="#64748b">TOTAL UNIT</text>
                            <text x="180" y="140" font-family="Inter,sans-serif" font-size="22" font-weight="800" fill="#0f172a">128</text>
                            <text x="290" y="140" font-family="Inter,sans-serif" font-size="9" fill="#22c55e">▲ 4%</text>

                            <!-- Card 2: Sedang Disewa -->
                            <rect x="322" y="68" width="140" height="80" rx="10" fill="white" stroke="#e2e8f0" stroke-width="1"/>
                            <rect x="332" y="78" width="28" height="28" rx="7" fill="#dbeafe"/>
                            <text x="346" y="97" font-family="Inter,sans-serif" font-size="14" fill="#3b82f6" text-anchor="middle">✓</text>
                            <text x="332" y="123" font-family="Inter,sans-serif" font-size="9" fill="#64748b">SEDANG DISEWA</text>
                            <text x="332" y="140" font-family="Inter,sans-serif" font-size="22" font-weight="800" fill="#0f172a">54</text>
                            <text x="440" y="140" font-family="Inter,sans-serif" font-size="9" fill="#22c55e">▲ 12%</text>

                            <!-- Card 3: Tersedia -->
                            <rect x="474" y="68" width="140" height="80" rx="10" fill="white" stroke="#e2e8f0" stroke-width="1"/>
                            <rect x="484" y="78" width="28" height="28" rx="7" fill="#dcfce7"/>
                            <text x="498" y="97" font-family="Inter,sans-serif" font-size="14" fill="#16a34a" text-anchor="middle">○</text>
                            <text x="484" y="123" font-family="Inter,sans-serif" font-size="9" fill="#64748b">TERSEDIA</text>
                            <text x="484" y="140" font-family="Inter,sans-serif" font-size="22" font-weight="800" fill="#0f172a">74</text>
                            <text x="592" y="140" font-family="Inter,sans-serif" font-size="9" fill="#ef4444">▼ 2%</text>

                            <!-- Card 4: Omzet (orange accent) -->
                            <rect x="626" y="68" width="154" height="80" rx="10" fill="#f97316"/>
                            <text x="640" y="88" font-family="Inter,sans-serif" font-size="9" fill="#ffedd5" font-weight="600">OMZET BULAN INI</text>
                            <text x="640" y="116" font-family="Inter,sans-serif" font-size="18" font-weight="800" fill="white">Rp 48,5 Jt</text>
                            <text x="640" y="136" font-family="Inter,sans-serif" font-size="8" fill="#fed7aa">▲ 18% dari bulan lalu</text>

                            <!-- ── Bar Chart ── -->
                            <rect x="170" y="164" width="330" height="190" rx="10" fill="white" stroke="#e2e8f0" stroke-width="1"/>
                            <text x="186" y="183" font-family="Inter,sans-serif" font-size="11" font-weight="700" fill="#0f172a">Pendapatan Bulanan</text>
                            <!-- Grid lines -->
                            <line x1="200" y1="205" x2="490" y2="205" stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="200" y1="230" x2="490" y2="230" stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="200" y1="255" x2="490" y2="255" stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="200" y1="280" x2="490" y2="280" stroke="#f1f5f9" stroke-width="1"/>
                            <line x1="200" y1="305" x2="490" y2="305" stroke="#f1f5f9" stroke-width="1"/>
                            <!-- Y labels -->
                            <text x="183" y="208" font-family="Inter,sans-serif" font-size="7" fill="#94a3b8">60jt</text>
                            <text x="183" y="233" font-family="Inter,sans-serif" font-size="7" fill="#94a3b8">45jt</text>
                            <text x="183" y="258" font-family="Inter,sans-serif" font-size="7" fill="#94a3b8">30jt</text>
                            <text x="183" y="283" font-family="Inter,sans-serif" font-size="7" fill="#94a3b8">15jt</text>
                            <text x="187" y="308" font-family="Inter,sans-serif" font-size="7" fill="#94a3b8">0</text>
                            <!-- Bars -->
                            <rect x="212" y="248" width="26" height="57" rx="4" fill="#fed7aa"/>
                            <rect x="250" y="228" width="26" height="77" rx="4" fill="#fed7aa"/>
                            <rect x="288" y="238" width="26" height="67" rx="4" fill="#fed7aa"/>
                            <rect x="326" y="210" width="26" height="95" rx="4" fill="#f97316"/>
                            <rect x="364" y="222" width="26" height="83" rx="4" fill="#fed7aa"/>
                            <rect x="402" y="200" width="26" height="105" rx="4" fill="#f97316"/>
                            <rect x="440" y="218" width="26" height="87" rx="4" fill="#fed7aa"/>
                            <!-- X labels -->
                            <text x="225" y="320" font-family="Inter,sans-serif" font-size="7.5" fill="#94a3b8" text-anchor="middle">Apr</text>
                            <text x="263" y="320" font-family="Inter,sans-serif" font-size="7.5" fill="#94a3b8" text-anchor="middle">Mei</text>
                            <text x="301" y="320" font-family="Inter,sans-serif" font-size="7.5" fill="#94a3b8" text-anchor="middle">Jun</text>
                            <text x="339" y="320" font-family="Inter,sans-serif" font-size="7.5" fill="#94a3b8" text-anchor="middle">Jul</text>
                            <text x="377" y="320" font-family="Inter,sans-serif" font-size="7.5" fill="#94a3b8" text-anchor="middle">Agu</text>
                            <text x="415" y="320" font-family="Inter,sans-serif" font-size="7.5" fill="#94a3b8" text-anchor="middle">Sep</text>
                            <text x="453" y="320" font-family="Inter,sans-serif" font-size="7.5" fill="#94a3b8" text-anchor="middle">Okt</text>
                            <!-- Legend -->
                            <rect x="186" y="336" width="9" height="8" rx="2" fill="#f97316"/>
                            <text x="200" y="344" font-family="Inter,sans-serif" font-size="8" fill="#64748b">Tertinggi</text>
                            <rect x="248" y="336" width="9" height="8" rx="2" fill="#fed7aa"/>
                            <text x="262" y="344" font-family="Inter,sans-serif" font-size="8" fill="#64748b">Normal</text>

                            <!-- ── Donut Chart ── -->
                            <rect x="514" y="164" width="266" height="190" rx="10" fill="white" stroke="#e2e8f0" stroke-width="1"/>
                            <text x="530" y="183" font-family="Inter,sans-serif" font-size="11" font-weight="700" fill="#0f172a">Status Unit</text>
                            <!-- Donut rings -->
                            <circle cx="647" cy="272" r="62" fill="none" stroke="#f97316" stroke-width="24" stroke-dasharray="196 390" stroke-dashoffset="98" transform="rotate(-90 647 272)"/>
                            <circle cx="647" cy="272" r="62" fill="none" stroke="#3b82f6" stroke-width="24" stroke-dasharray="134 390" stroke-dashoffset="-98" transform="rotate(-90 647 272)"/>
                            <circle cx="647" cy="272" r="62" fill="none" stroke="#22c55e" stroke-width="24" stroke-dasharray="60 390" stroke-dashoffset="-232" transform="rotate(-90 647 272)"/>
                            <!-- Center hole -->
                            <circle cx="647" cy="272" r="40" fill="white"/>
                            <text x="647" y="268" font-family="Inter,sans-serif" font-size="14" font-weight="800" fill="#0f172a" text-anchor="middle">128</text>
                            <text x="647" y="282" font-family="Inter,sans-serif" font-size="8" fill="#64748b" text-anchor="middle">Total Unit</text>
                            <!-- Legend -->
                            <circle cx="526" cy="346" r="5" fill="#f97316"/>
                            <text x="535" y="350" font-family="Inter,sans-serif" font-size="8" fill="#475569">Disewa (54)</text>
                            <circle cx="598" cy="346" r="5" fill="#3b82f6"/>
                            <text x="607" y="350" font-family="Inter,sans-serif" font-size="8" fill="#475569">Tersedia (74)</text>
                            <circle cx="526" cy="360" r="5" fill="#22c55e"/>
                            <text x="535" y="364" font-family="Inter,sans-serif" font-size="8" fill="#475569">Maintenance (0)</text>

                            <!-- ── Transactions Table ── -->
                            <rect x="170" y="366" width="610" height="120" rx="10" fill="white" stroke="#e2e8f0" stroke-width="1"/>
                            <text x="186" y="384" font-family="Inter,sans-serif" font-size="11" font-weight="700" fill="#0f172a">Transaksi Terbaru</text>
                            <!-- Table header -->
                            <rect x="170" y="390" width="610" height="22" fill="#f8fafc" rx="0"/>
                            <text x="186" y="405" font-family="Inter,sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">ID</text>
                            <text x="218" y="405" font-family="Inter,sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">PELANGGAN</text>
                            <text x="360" y="405" font-family="Inter,sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">UNIT</text>
                            <text x="488" y="405" font-family="Inter,sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">TANGGAL</text>
                            <text x="578" y="405" font-family="Inter,sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">TOTAL</text>
                            <text x="662" y="405" font-family="Inter,sans-serif" font-size="8.5" fill="#94a3b8" font-weight="600">STATUS</text>
                            <!-- Row 1 -->
                            <text x="186" y="424" font-family="Inter,sans-serif" font-size="9" fill="#64748b">#1234</text>
                            <text x="218" y="424" font-family="Inter,sans-serif" font-size="9" fill="#0f172a">Andi Saputra</text>
                            <text x="360" y="424" font-family="Inter,sans-serif" font-size="9" fill="#64748b">Kamera Sony A7III</text>
                            <text x="488" y="424" font-family="Inter,sans-serif" font-size="9" fill="#64748b">01 Okt 2026</text>
                            <text x="578" y="424" font-family="Inter,sans-serif" font-size="9" font-weight="700" fill="#0f172a">Rp 850.000</text>
                            <rect x="660" y="414" width="46" height="14" rx="7" fill="#dcfce7"/>
                            <text x="683" y="424" font-family="Inter,sans-serif" font-size="8" fill="#16a34a" text-anchor="middle">Lunas</text>
                            <!-- Divider -->
                            <line x1="180" y1="432" x2="770" y2="432" stroke="#f1f5f9" stroke-width="1"/>
                            <!-- Row 2 -->
                            <text x="186" y="448" font-family="Inter,sans-serif" font-size="9" fill="#64748b">#1235</text>
                            <text x="218" y="448" font-family="Inter,sans-serif" font-size="9" fill="#0f172a">Rina Marlina</text>
                            <text x="360" y="448" font-family="Inter,sans-serif" font-size="9" fill="#64748b">Drone DJI Mini 3</text>
                            <text x="488" y="448" font-family="Inter,sans-serif" font-size="9" fill="#64748b">03 Okt 2026</text>
                            <text x="578" y="448" font-family="Inter,sans-serif" font-size="9" font-weight="700" fill="#0f172a">Rp 1.200.000</text>
                            <rect x="660" y="438" width="46" height="14" rx="7" fill="#fef9c3"/>
                            <text x="683" y="448" font-family="Inter,sans-serif" font-size="8" fill="#ca8a04" text-anchor="middle">DP</text>
                            <!-- Divider -->
                            <line x1="180" y1="456" x2="770" y2="456" stroke="#f1f5f9" stroke-width="1"/>
                            <!-- Row 3 -->
                            <text x="186" y="472" font-family="Inter,sans-serif" font-size="9" fill="#64748b">#1236</text>
                            <text x="218" y="472" font-family="Inter,sans-serif" font-size="9" fill="#0f172a">Bagas Firmansyah</text>
                            <text x="360" y="472" font-family="Inter,sans-serif" font-size="9" fill="#64748b">Tenda Camping 4P</text>
                            <text x="488" y="472" font-family="Inter,sans-serif" font-size="9" fill="#64748b">05 Okt 2026</text>
                            <text x="578" y="472" font-family="Inter,sans-serif" font-size="9" font-weight="700" fill="#0f172a">Rp 450.000</text>
                            <rect x="660" y="462" width="46" height="14" rx="7" fill="#fee2e2"/>
                            <text x="683" y="472" font-family="Inter,sans-serif" font-size="8" fill="#dc2626" text-anchor="middle">Proses</text>
                        </svg>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- HERO SECTION END -->

    <!-- FEATURES SECTION START -->
    <section id="fitur" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section Heading --}}
            <div 
                x-data="{ shown: false }" x-intersect.once="shown = true"
                class="text-center max-w-3xl mx-auto mb-16 transition-all duration-1000 ease-out"
                :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'"
            >
                <h2 class="text-brand font-bold tracking-widest uppercase text-sm mb-3">Fitur Unggulan</h2>
                <p class="text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Didesain Khusus untuk Rental Fisik
                </p>
                <p class="mt-4 max-w-2xl text-lg text-gray-600 mx-auto">
                    Bukan sekadar sistem kasir, RentalBase memahami alur kerja penyewaan barang dari awal pemesanan hingga pengembalian.
                </p>
            </div>

            {{-- Feature Cards with Staggered Scroll Animation --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Feature 1 -->
                <div 
                    x-data="{ shown: false }" x-intersect.once="shown = true"
                    class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 hover:border-orange-100 transition-all duration-1000 ease-out delay-100 cursor-default"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-24'"
                >
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 group-hover:bg-brand group-hover:text-white transition-all duration-300">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-brand transition-colors duration-300">Kalender Booking</h3>
                    <p class="text-gray-600 leading-relaxed">Atur jadwal ketersediaan unit secara visual. Bebas dari risiko bentrok penyewaan (double booking).</p>
                </div>

                <!-- Feature 2 -->
                <div 
                    x-data="{ shown: false }" x-intersect.once="shown = true"
                    class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 hover:border-orange-100 transition-all duration-1000 ease-out delay-300 cursor-default"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-24'"
                >
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 group-hover:bg-brand group-hover:text-white transition-all duration-300">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-brand transition-colors duration-300">Inventaris Cerdas</h3>
                    <p class="text-gray-600 leading-relaxed">Lacak status barang (tersedia, disewa, rusak). Catat riwayat perbaikan tiap unit dengan detail.</p>
                </div>

                <!-- Feature 3 -->
                <div 
                    x-data="{ shown: false }" x-intersect.once="shown = true"
                    class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 hover:border-orange-100 transition-all duration-1000 ease-out delay-500 cursor-default"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-24'"
                >
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 group-hover:bg-brand group-hover:text-white transition-all duration-300">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-brand transition-colors duration-300">Faktur &amp; Denda</h3>
                    <p class="text-gray-600 leading-relaxed">Buat invoice otomatis, catat deposit, dan kalkulasi denda keterlambatan secara instan.</p>
                </div>

                <!-- Feature 4 -->
                <div 
                    x-data="{ shown: false }" x-intersect.once="shown = true"
                    class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-2xl hover:-translate-y-2 hover:border-orange-100 transition-all duration-1000 ease-out delay-700 cursor-default"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-24'"
                >
                    <div class="w-14 h-14 bg-brand-light text-brand rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 group-hover:bg-brand group-hover:text-white transition-all duration-300">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-brand transition-colors duration-300">Laporan Analitik</h3>
                    <p class="text-gray-600 leading-relaxed">Analisis omzet, unit paling laris, dan performa bisnis Anda melalui dashboard interaktif.</p>
                </div>

            </div>
        </div>
    </section>
    <!-- FEATURES SECTION END -->

    <!-- PRICING SECTION START -->
    <section id="harga" class="py-24 bg-white relative" x-data="{ shown: false }" x-intersect.once="shown = true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section Heading --}}
            <div
                class="text-center max-w-3xl mx-auto mb-20 transition-all duration-700 ease-out"
                :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
            >
                <h2 class="text-brand font-bold tracking-widest uppercase text-sm mb-3">Harga Langganan</h2>
                <p class="text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Investasi Tepat untuk Usaha Anda
                </p>
            </div>

            {{-- Pricing Cards with stagger --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto items-stretch">
                @if(isset($packages))
                    @foreach($packages as $index => $pkg)
                        @php
                            $isPopular = $pkg['name'] === 'Business';
                            $delays = ['delay-[100ms]', 'delay-[250ms]', 'delay-[400ms]'];
                            $delay = $delays[$index] ?? 'delay-[100ms]';
                        @endphp
                        <div
                            class="bg-white border-2 {{ $isPopular ? 'border-brand rounded-3xl p-8 shadow-xl relative transform lg:-translate-y-4' : 'border-gray-200 rounded-3xl p-8 shadow-sm' }} flex flex-col transition-all duration-700 ease-out {{ $delay }}"
                            :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
                        >
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
                            <a href="{{ route('tenant.register', ['plan' => strtolower($pkg['name'])]) }}" class="block w-full py-4 px-4 {{ $isPopular ? 'bg-brand text-white hover:bg-brand-hover shadow-lg shadow-orange-200/50' : 'bg-brand-light text-brand hover:bg-orange-200' }} font-bold text-center rounded-xl transition">
                                Pilih Paket {{ $pkg['name'] }}
                            </a>
                        </div>
                    @endforeach
                @else
                    <!-- Fallback jika packages tidak tersedia -->
                    <p class="col-span-3 text-center text-gray-500">Paket sedang tidak tersedia.</p>
                @endif
            </div>
        </div>
    </section>
    <!-- PRICING SECTION END -->

    <!-- CTA SECTION START -->
    <section class="py-20 bg-white" x-data="{ shown: false }" x-intersect.once="shown = true">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-brand rounded-3xl shadow-xl overflow-hidden relative transition-all duration-700 ease-out"
                :class="shown ? 'opacity-100 scale-100 translate-y-0' : 'opacity-0 scale-95 translate-y-8'"
            >
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white opacity-20 rounded-full blur-3xl"></div>

                <div class="relative px-8 py-16 sm:px-16 sm:py-20 text-center">
                    <h2
                        class="text-3xl font-extrabold text-white sm:text-4xl mb-4 transition-all duration-700 ease-out delay-[150ms]"
                        :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    >
                        Siap Membawa Bisnis Rental Anda ke Level Berikutnya?
                    </h2>
                    <p
                        class="text-orange-100 text-lg mb-10 max-w-2xl mx-auto transition-all duration-700 ease-out delay-[300ms]"
                        :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    >
                        Ribuan pemilik rental telah menghemat waktu dan meningkatkan profit mereka. Mulai kelola dengan profesional sekarang.
                    </p>
                    <button
                        type="button"
                        @click="showRoleModal = true"
                        class="inline-flex items-center justify-center px-10 py-4 text-lg font-bold rounded-full text-brand bg-white hover:bg-gray-50 hover:scale-105 transition-all duration-700 ease-out delay-[450ms] shadow-xl cursor-pointer"
                        :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    >
                        Sign Up Gratis — Tanpa Kartu Kredit
                    </button>
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
                    <a href="/" 
                       x-data="{
                           clickCount: 0,
                           timer: null,
                           handleLogoClick(e) {
                               this.clickCount++;
                               if (this.clickCount === 1) {
                                   this.timer = setTimeout(() => {
                                       this.clickCount = 0;
                                       window.location.href = '/';
                                   }, 500);
                                   e.preventDefault();
                               } else if (this.clickCount === 2) {
                                   e.preventDefault();
                               } else if (this.clickCount >= 3) {
                                   clearTimeout(this.timer);
                                   this.clickCount = 0;
                                   e.preventDefault();
                                   window.location.href = '{{ route('owner.login') }}';
                               }
                           }
                       }"
                       @click="handleLogoClick($event)"
                       class="flex-shrink-0 flex items-center gap-2 mb-4 cursor-pointer select-none"
                       title="RentalBase">
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
                        <li><a href="#" class="text-gray-500 hover:text-brand transition">Syarat &amp; Ketentuan (Terms)</a></li>
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

    <!-- ======================================================== -->
    <!-- MODAL PILIHAN PENDAFTARAN (CUSTOMER ATAU TENANT)        -->
    <!-- ======================================================== -->
    <div 
        x-show="showRoleModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="showRoleModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showRoleModal = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        ></div>

        <!-- Modal Box -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <div 
                x-show="showRoleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.away="showRoleModal = false"
                @keydown.escape.window="showRoleModal = false"
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100 p-6 sm:p-8"
            >
                <!-- Close Button -->
                <button 
                    @click="showRoleModal = false" 
                    type="button" 
                    class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-700 flex items-center justify-center transition-colors focus:outline-none"
                    aria-label="Tutup Modal"
                >
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

                <!-- Modal Header -->
                <div class="text-center max-w-md mx-auto mb-6">
                    <div class="w-12 h-12 bg-orange-100 text-brand rounded-2xl flex items-center justify-center mx-auto mb-3 text-xl font-bold shadow-sm">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900" id="modal-title">
                        Pilih Jenis Pendaftaran
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Bagaimana Anda ingin menggunakan platform RentalBase?
                    </p>
                </div>

                <!-- Role Selection Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- 1. Customer Option Card -->
                    <div class="rounded-2xl border-2 border-slate-200 hover:border-brand hover:shadow-lg transition-all duration-200 p-5 flex flex-col justify-between bg-white group">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-brand flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 group-hover:bg-brand group-hover:text-white transition-all">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-slate-900 mb-1">
                                Sebagai Customer
                            </h4>
                            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                Untuk pelanggan yang ingin menyewa barang, alat, atau kendaraan di toko rental mitra kami.
                            </p>
                            <ul class="text-xs text-slate-600 space-y-2 mb-5">
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                    <span>Akun universal untuk semua toko</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                    <span>Riwayat order & tracking sewa</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                    <span>Gratis tanpa biaya pendaftaran</span>
                                </li>
                            </ul>
                        </div>
                        <a 
                            href="{{ route('login', ['tab' => 'register']) }}" 
                            class="w-full text-center py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm border border-slate-300 text-slate-700 bg-slate-50 hover:bg-brand hover:text-white hover:border-brand transition-colors duration-150 block"
                        >
                            Daftar Akun Customer &rarr;
                        </a>
                    </div>

                    <!-- 2. Tenant Option Card (Recommended) -->
                    <div class="rounded-2xl border-2 border-brand bg-orange-50/20 hover:bg-orange-50/50 hover:shadow-xl transition-all duration-200 p-5 flex flex-col justify-between relative group">
                        <div class="absolute -top-3 right-4 bg-brand text-white text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full shadow-sm tracking-wider">
                            Pemilik Usaha
                        </div>
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-brand text-white flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 transition-transform shadow-md shadow-orange-500/25">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-slate-900 mb-1">
                                Sebagai Pemilik Toko
                            </h4>
                            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                Untuk pengusaha rental yang ingin mengelola stok unit, booking, invoice, dan website toko online sendiri.
                            </p>
                            <ul class="text-xs text-slate-600 space-y-2 mb-5">
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-brand text-xs"></i>
                                    <span>Subdomain website toko mandiri</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-brand text-xs"></i>
                                    <span>Manajemen unit & jadwal sewa</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-brand text-xs"></i>
                                    <span>Laporan omzet & analitik bisnis</span>
                                </li>
                            </ul>
                        </div>
                        <a 
                            href="{{ route('tenant.register') }}" 
                            class="w-full text-center py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm bg-brand text-white hover:bg-brand-hover shadow-md shadow-orange-500/25 transition-all duration-150 block"
                        >
                            Buka Toko Rental (Tenant) &rarr;
                        </a>
                    </div>
                </div>

                <!-- Footer Switch -->
                <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">
                        Sudah punya akun sebelumnya? 
                        <a href="{{ route('login') }}" class="font-bold text-brand hover:underline ml-1">
                            Masuk di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
