<!-- ========================================================================= -->
<!-- MOCKUP DASHBOARD STATIS KOMPAK (HERO DESKTOP VIEW)                        -->
<!-- Sesuai persis dengan UI dashboard asli (Baby Rental Studio)               -->
<!-- Proporsional, rapi, tidak memanjang ke bawah & teks tidak terpotong       -->
<!-- ========================================================================= -->
<div class="w-full max-w-4xl mx-auto rounded-xl shadow-2xl overflow-hidden border border-gray-200 bg-white select-none text-left font-sans">

    <!-- ── 1. Header Browser Tiruan (macOS 3 Titik + Search Bar URL) ── -->
    <div class="h-8 bg-gray-50 border-b border-gray-200 px-3 flex items-center justify-between shrink-0">
        <!-- 3 Titik Sirkel macOS -->
        <div class="flex items-center gap-1.5 shrink-0">
            <span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span>
        </div>

        <!-- Pill Putih Kecil Berisi Search Bar URL -->
        <div class="bg-white border border-gray-200 rounded-md px-3 py-0.5 text-[10px] font-mono text-gray-500 shadow-xs flex items-center gap-1.5 max-w-xs w-full justify-center">
            <svg class="size-2.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <span>rentalbase.app/dashboard</span>
        </div>

        <!-- Tombol Tambah (+) Kanan Sesuai Mockup Asli -->
        <div class="flex items-center text-gray-400 shrink-0">
            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </div>
    </div>

    <!-- ── 2. Layout Dashboard Desktop (Kiri: Sidebar, Kanan: Main Content) ── -->
    <div class="flex flex-row h-[395px] overflow-hidden">

        <!-- ===================================================================== -->
        <!-- 3. SIDEBAR (Kiri - Lebar Proporsional w-36, Rapi & Lengkap)            -->
        <!-- ===================================================================== -->
        <aside class="w-36 shrink-0 bg-white border-r border-gray-100 flex flex-col p-2 overflow-hidden justify-between">
            <div class="space-y-1.5">
                <!-- Header Sidebar: Logo & Nama Usaha -->
                <div class="flex items-center gap-1.5 pb-1.5 border-b border-gray-100">
                    <div class="w-6 h-6 rounded-md bg-orange-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.25A2.25 2.25 0 0 1 0 18.75V10.5m18 10.5h3.75A2.25 2.25 0 0 0 24 18.75V10.5M3 10.5 5.25 3h13.5L21 10.5M3 10.5h18" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-bold text-[10px] text-gray-900 leading-none truncate">Baby Rental Studio</h2>
                        <span class="text-[7.5px] font-semibold text-orange-500 truncate block mt-0.5">Rental Ops</span>
                    </div>
                </div>

                <!-- Navigasi Menu List Vertikal -->
                <nav class="space-y-0.5">
                    <span class="text-[7px] font-bold text-gray-400 uppercase tracking-wider px-1.5 block">NAVIGASI UTAMA</span>

                    <!-- Menu 1: Dashboard (Aktif) -->
                    <div class="flex items-center gap-1.5 px-1.5 py-0.5 rounded-md bg-orange-50 text-orange-600 font-semibold text-[9.5px]">
                        <svg class="size-3 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </div>

                    <!-- Menu 2: Notifikasi (Badge '1') -->
                    <div class="flex items-center justify-between px-1.5 py-0.5 rounded-md text-gray-600 text-[9.5px] font-medium hover:bg-gray-50">
                        <div class="flex items-center gap-1.5">
                            <svg class="size-3 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            <span>Notifikasi</span>
                        </div>
                        <span class="w-3.5 h-3.5 rounded-full bg-orange-500 text-white text-[7.5px] font-bold flex items-center justify-center">1</span>
                    </div>

                    <!-- Menu 3: Pesanan -->
                    <div class="flex items-center gap-1.5 px-1.5 py-0.5 rounded-md text-gray-600 text-[9.5px] font-medium hover:bg-gray-50">
                        <svg class="size-3 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        <span>Pesanan</span>
                    </div>

                    <!-- Menu 4: Pengiriman -->
                    <div class="flex items-center gap-1.5 px-1.5 py-0.5 rounded-md text-gray-600 text-[9.5px] font-medium hover:bg-gray-50">
                        <svg class="size-3 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V18.75m0-11.25a2.25 2.25 0 0 0-2.25-2.25h-6A2.25 2.25 0 0 0 3.75 7.5v9.75m8.25-9.75H3.75" />
                        </svg>
                        <span>Pengiriman</span>
                    </div>

                    <!-- Menu 5: Pengembalian -->
                    <div class="flex items-center gap-1.5 px-1.5 py-0.5 rounded-md text-gray-600 text-[9.5px] font-medium hover:bg-gray-50">
                        <svg class="size-3 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Pengembalian</span>
                    </div>

                    <!-- Menu 6: Inspeksi Fisik -->
                    <div class="flex items-center gap-1.5 px-1.5 py-0.5 rounded-md text-gray-600 text-[9.5px] font-medium hover:bg-gray-50">
                        <svg class="size-3 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                        <span>Inspeksi Fisik</span>
                    </div>

                    <!-- Label Kecil: KATALOG PERALATAN -->
                    <div class="pt-1">
                        <span class="text-[7px] font-bold text-gray-400 uppercase tracking-wider px-1.5 block mb-0.5">KATALOG PERALATAN</span>
                        <div class="flex items-center gap-1.5 px-1.5 py-0.2 rounded-md text-gray-600 text-[9px] font-medium hover:bg-gray-50">
                            <svg class="size-2.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /></svg>
                            <span>Kategori</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-1.5 py-0.2 rounded-md text-gray-600 text-[9px] font-medium hover:bg-gray-50">
                            <svg class="size-2.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                            <span>Produk</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-1.5 py-0.2 rounded-md text-gray-600 text-[9px] font-medium hover:bg-gray-50">
                            <svg class="size-2.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                            <span>Unit</span>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Footer User Profile di Bawah Sidebar -->
            <div class="pt-1.5 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-1.5 min-w-0">
                    <div class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[8.5px] font-bold shrink-0">
                        B
                    </div>
                    <div class="truncate">
                        <p class="font-bold text-[9px] text-gray-900 leading-tight truncate">Budi Admin</p>
                        <p class="text-[7.5px] text-gray-400 truncate">Admin Utama</p>
                    </div>
                </div>
                <svg class="size-2.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                </svg>
            </div>
        </aside>

        <!-- ===================================================================== -->
        <!-- 4. MAIN CONTENT (Kanan - Tampilan Rapi, Proporsional & Lengkap)       -->
        <!-- ===================================================================== -->
        <main class="flex-1 bg-slate-50 p-2.5 flex flex-col justify-between overflow-hidden min-w-0">

            <!-- ── Topbar: Breadcrumb, Tanggal, Badge Paket, Lonceng, Avatar ── -->
            <header class="flex items-center justify-between bg-white px-2.5 py-1 rounded-lg border border-gray-100 shadow-xs shrink-0">
                <!-- Kiri: Hamburger + Breadcrumb -->
                <div class="flex items-center gap-1.5 text-[9.5px] font-semibold text-gray-800 shrink-0">
                    <svg class="size-3 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <span>Baby Rental Studio</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-500 font-medium">Dashboard Operasional</span>
                </div>

                <!-- Kanan: Tanggal, Badge Paket, Lonceng, Avatar -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[8.5px] text-gray-400 font-medium hidden sm:inline-block">Tuesday, 3 Nov 2026</span>

                    <!-- Badge: Paket: Pro Enterprise (Aktif) -->
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[8.5px] font-semibold text-orange-600 bg-orange-50 border border-orange-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                        Paket: Pro Enterprise
                    </span>

                    <!-- Ikon Lonceng -->
                    <div class="relative w-5 h-5 rounded border border-gray-200 bg-gray-50 text-gray-600 flex items-center justify-center">
                        <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-orange-500 text-white text-[7px] font-bold flex items-center justify-center">1</span>
                    </div>

                    <!-- Avatar User Lingkaran Hitam 'B' -->
                    <div class="w-5 h-5 rounded-full bg-slate-900 text-white font-bold text-[9px] flex items-center justify-center shadow-xs">
                        B
                    </div>
                </div>
            </header>

            <!-- ── Hero Title & Action Header ── -->
            <div class="flex items-center justify-between gap-2 shrink-0">
                <div class="min-w-0">
                    <span class="text-[7.5px] font-bold text-orange-500 tracking-wider uppercase flex items-center gap-1">
                        <span class="w-1 h-1 rounded-full bg-orange-500"></span>
                        PUSAT KENDALI RENTAL
                    </span>
                    <h1 class="text-xs sm:text-[13px] font-bold text-gray-900 tracking-tight leading-tight whitespace-nowrap">
                        Selamat Datang kembali, Budi!
                    </h1>
                    <p class="text-[8px] text-gray-500 leading-none truncate mt-0.5">
                        Pantau pesanan masuk, jadwal pengiriman, dan verifikasi jaminan secara real-time.
                    </p>
                </div>

                <!-- Sisi Kanan: Search Input & Tombol Oranye Solid -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <div class="relative hidden sm:block">
                        <svg class="size-2.5 text-gray-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" 
                               placeholder="Cek Perlengkapan..." 
                               class="bg-white border border-gray-200 rounded pl-5 pr-1.5 py-0.5 text-[8.5px] text-gray-600 placeholder:text-gray-400 w-28 focus:outline-none shadow-xs" 
                               readonly>
                    </div>

                    <button type="button" class="inline-flex items-center gap-1 px-2 py-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-[8.5px] rounded shadow-xs shrink-0 whitespace-nowrap">
                        <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Catat Reservasi Manual</span>
                    </button>
                </div>
            </div>

            <!-- ── 4 Metric Cards (grid grid-cols-4 gap-2) ── -->
            <div class="grid grid-cols-4 gap-2 shrink-0">
                <!-- Card 1: ORDER BARU 0 -->
                <div class="bg-white rounded-lg p-2 shadow-xs border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[7.5px] font-bold text-gray-400 tracking-wider uppercase truncate">ORDER BARU</span>
                        <div class="w-4.5 h-4.5 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                            <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-1">
                        <div class="text-sm font-extrabold text-gray-900 leading-none">0</div>
                        <p class="text-[7.5px] font-semibold text-emerald-600 mt-1 whitespace-nowrap leading-none">+3 pengajuan sewa</p>
                    </div>
                </div>

                <!-- Card 2: VERIFIKASI 0 -->
                <div class="bg-white rounded-lg p-2 shadow-xs border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[7.5px] font-bold text-gray-400 tracking-wider uppercase truncate">VERIFIKASI</span>
                        <div class="w-4.5 h-4.5 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                            <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-1">
                        <div class="text-sm font-extrabold text-gray-900 leading-none">0</div>
                        <p class="text-[7.5px] font-semibold text-amber-600 mt-1 whitespace-nowrap leading-none">Cek Pembayaran</p>
                    </div>
                </div>

                <!-- Card 3: DISEWA 2 -->
                <div class="bg-white rounded-lg p-2 shadow-xs border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[7.5px] font-bold text-gray-400 tracking-wider uppercase truncate">DISEWA</span>
                        <div class="w-4.5 h-4.5 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-1">
                        <div class="text-sm font-extrabold text-gray-900 leading-none">2</div>
                        <p class="text-[7.5px] font-semibold text-emerald-600 mt-1 whitespace-nowrap leading-none">Unit aktif disewa</p>
                    </div>
                </div>

                <!-- Card 4: PENDAPATAN Rp 0 -->
                <div class="bg-white rounded-lg p-2 shadow-xs border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[7.5px] font-bold text-gray-400 tracking-wider uppercase truncate">PENDAPATAN</span>
                        <div class="w-4.5 h-4.5 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-1">
                        <div class="text-sm font-extrabold text-gray-900 leading-none">Rp 0</div>
                        <p class="text-[7.5px] font-semibold text-purple-600 mt-1 whitespace-nowrap leading-none">Bulan Nov 2026</p>
                    </div>
                </div>
            </div>

            <!-- ── Quota Banner (Sisa Kuota Limit Paket) ── -->
            <div class="bg-white rounded-lg p-2 shadow-xs border border-gray-100 flex items-center justify-between gap-2 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-bold text-[9.5px] text-gray-800 leading-none">Sisa Kuota Limit Paket</h3>
                            <span class="text-[7.5px] font-semibold text-orange-600 bg-orange-50 px-1 py-0.2 rounded border border-orange-200">Pro Enterprise</span>
                        </div>
                        <p class="text-[7.5px] text-gray-400 mt-0.5 leading-none">12 dari 200 unit terpakai</p>
                        <!-- Progress Bar Oranye 6% -->
                        <div class="w-28 sm:w-36 h-1 bg-gray-100 rounded-full overflow-hidden mt-1">
                            <div class="h-full bg-orange-500 rounded-full w-[6%]"></div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Kelola Unit & Upgrade Paket -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-xs font-black text-orange-500">6%</span>
                    <button type="button" class="px-2 py-0.5 rounded border border-gray-200 text-gray-700 text-[8px] font-medium hover:bg-gray-50 shadow-xs">
                        Kelola Unit
                    </button>
                    <button type="button" class="px-2 py-0.5 rounded bg-slate-900 hover:bg-black text-white text-[8px] font-semibold shadow-xs flex items-center gap-0.5">
                        <span class="text-[7px]">⚡</span>
                        <span>Upgrade</span>
                    </button>
                </div>
            </div>

            <!-- ── Tabel Pesanan (Card Putih: 2 Baris Lengkap, Rapi & Tidak Terpotong) ── -->
            <div class="bg-white rounded-lg p-2 shadow-xs border border-gray-100 flex flex-col gap-1 shrink-0">
                <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                    <h3 class="text-[9.5px] font-bold text-gray-800">Pesanan Terbaru</h3>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[8px] text-gray-400 hidden sm:inline-block">Cari pesanan...</span>
                        <span class="text-[7.5px] font-semibold text-gray-600 bg-gray-50 border border-gray-200 px-1.5 py-0.2 rounded">Lihat Semua</span>
                    </div>
                </div>

                <div class="w-full">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/70 border-b border-gray-100 text-[7.5px] font-bold text-gray-400 uppercase tracking-wider">
                                <th class="py-0.5 px-1.5">ID Pesanan</th>
                                <th class="py-0.5 px-1.5">Customer & Item</th>
                                <th class="py-0.5 px-1.5">Tgl Sewa</th>
                                <th class="py-0.5 px-1.5">Total</th>
                                <th class="py-0.5 px-1.5 text-center">Status</th>
                                <th class="py-0.5 px-1.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-[8.5px]">
                            <!-- Baris 1: #ORD-001 / iqo gibran / Rp 180.000 / Siap Kirim (Biru) -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-1 px-1.5 font-mono font-bold text-orange-600 text-[8px]">
                                    #ORD-001
                                </td>
                                <td class="py-1 px-1.5">
                                    <div class="font-semibold text-gray-800 leading-tight">iqo gibran</div>
                                    <div class="text-[7px] text-gray-400">Cocolatte Pockit Gen 7</div>
                                </td>
                                <td class="py-1 px-1.5 text-gray-600 text-[7.5px]">
                                    21 - 23 Oct
                                </td>
                                <td class="py-1 px-1.5 font-bold text-gray-800 text-[8px]">
                                    Rp 180.000
                                </td>
                                <td class="py-1 px-1.5 text-center">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[7.5px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        ● Siap Kirim
                                    </span>
                                </td>
                                <td class="py-1 px-1.5 text-right">
                                    <button type="button" class="px-1.5 py-0.2 rounded border border-gray-200 text-gray-600 text-[7.5px] font-medium hover:bg-gray-50">
                                        Kelola
                                    </button>
                                </td>
                            </tr>

                            <!-- Baris 2: #ORD-002 / Sarah Amalia / Rp 300.000 / Batal (Merah) -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-1 px-1.5 font-mono font-bold text-orange-600 text-[8px]">
                                    #ORD-002
                                </td>
                                <td class="py-1 px-1.5">
                                    <div class="font-semibold text-gray-800 leading-tight">Sarah Amalia</div>
                                    <div class="text-[7px] text-gray-400">Babyzen YOYO2 Frame</div>
                                </td>
                                <td class="py-1 px-1.5 text-gray-600 text-[7.5px]">
                                    22 - 24 Oct
                                </td>
                                <td class="py-1 px-1.5 font-bold text-gray-800 text-[8px]">
                                    Rp 300.000
                                </td>
                                <td class="py-1 px-1.5 text-center">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[7.5px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        ● Batal
                                    </span>
                                </td>
                                <td class="py-1 px-1.5 text-right">
                                    <button type="button" class="px-1.5 py-0.2 rounded border border-gray-200 text-gray-600 text-[7.5px] font-medium hover:bg-gray-50">
                                        Kelola
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>
