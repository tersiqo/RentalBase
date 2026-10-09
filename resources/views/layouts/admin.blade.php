<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $client->business_name ?? 'RentalBase Admin' }}</title>
    <meta name="description" content="Panel Admin RentalBase — Kelola pesanan, produk, dan operasional rental peralatan bayi Anda.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 72px;
            --topbar-height: 64px;
            --color-primary: #F97316;
            --color-primary-hover: #EA580C;
            --color-primary-light: #FFF7ED;
        }

        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #F9FAFB; }
        ::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #D1D5DB; }

        /* Sidebar transition */
        #sidebar {
            transition: width 0.25s cubic-bezier(0.4,0,0.2,1), transform 0.25s cubic-bezier(0.4,0,0.2,1);
            will-change: width, transform;
        }

        /* Main content transition */
        #main-content {
            transition: margin-left 0.25s cubic-bezier(0.4,0,0.2,1);
        }

        /* Nav item active dot */
        .nav-active {
            background-color: #FFF7ED;
            color: #F97316;
            font-weight: 600;
        }

        .nav-active .nav-icon {
            color: #F97316;
        }

        /* Status badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="h-full bg-stone-50 text-gray-800 antialiased"
      x-data="adminLayout()"
      x-init="init()">

{{-- Mobile overlay --}}
<div x-show="mobileOpen"
     x-cloak
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileOpen = false"
     class="fixed inset-0 z-30 bg-black/40 backdrop-blur-sm lg:hidden"
     aria-hidden="true">
</div>

<aside id="sidebar"
       :class="sidebarClasses()"
       class="fixed top-0 left-0 h-full z-40 bg-white border-r border-gray-100 flex flex-col overflow-hidden shadow-sm">

    {{-- Logo / Brand --}}
    <div class="flex items-center gap-3 px-5 border-b border-gray-100"
         style="height: 64px; min-height: 64px;">
        <div class="shrink-0 w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shadow-md shadow-orange-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div x-show="!collapsed || mobileOpen"
             x-transition:enter="transition-opacity duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="overflow-hidden whitespace-nowrap">
            <p class="text-sm font-bold text-gray-900 leading-tight">{{ $client->business_name ?? 'RentalBase Baby Care' }}</p>
            <p class="text-[11px] text-gray-400 font-medium">Rental Ops — Peralatan Bayi</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <p x-show="!collapsed || mobileOpen"
           class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
            NAVIGASI UTAMA
        </p>

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
           title="Menampilkan metrik harian, progress limit produk/unit berdasarkan langganan klien yang tercatat di tabel subscriptions, dan ringkasan aktivitas penyewaan terbaru untuk memantau transaksi."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150
                  {{ request()->routeIs('admin.dashboard') ? 'bg-orange-50 text-orange-600 shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Dashboard</span>
        </a>

        {{-- Pesanan --}}
        <a href="{{ route('admin.orders.index') }}"
           title="Mengelola tabel orders dan payments. Fitur ini berfungsi untuk mengecek jadwal sewa, mengonfirmasi booking pengajuan dari Customer, memverifikasi keabsahan bukti pembayaran bank, serta membatalkan order dan mengelola proses input alasan serta unggah bukti refund."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.orders.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.orders.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Pesanan</span>
        </a>

        {{-- Pengiriman --}}
        <a href="{{ route('admin.shipments.index') }}"
           title="Mengelola tabel shipments. Berfungsi untuk memperbarui status pengiriman (dikirim, diterima), menginput data nama kurir beserta nomor resi pelacakan, serta memproses pencatatan kondisi awal barang sebelum diserahkan kepada kurir atau penyewa langsung."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.shipments.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.shipments.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Pengiriman</span>
        </a>

        {{-- Pengembalian --}}
        <a href="{{ route('admin.returns.index') }}"
           title="Mengelola tabel returns. Berfungsi untuk mengonfirmasi penerimaan barang kembali dari pelanggan ketika masa sewa selesai, mengecek keterlambatan (late days), serta menetapkan status pesanan menjadi selesai."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.returns.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.returns.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Pengembalian</span>
        </a>

        {{-- Inspeksi Fisik --}}
        <a href="{{ route('admin.condition-checks.index') }}"
           title="Terhubung dengan tabel condition_checks. Menu khusus untuk menginput pencatatan kondisi fisik unit barang melalui lampiran foto serta lembar centang (checklist) sebelum barang keluar dan sesudah barang dikembalikan."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.condition-checks.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.condition-checks.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Inspeksi Fisik</span>
        </a>

        {{-- Kasus Kerusakan --}}
        <a href="{{ route('admin.damage-cases.index') }}"
           title="Mengelola entri pada tabel damage_reports dan damage_cases. Digunakan untuk mencatat rincian kerusakan fisik jika ditemukan masalah saat inspeksi pengembalian, melampirkan bukti foto kerusakan, serta menentukan dan memantau status penyelesaian nominal ganti rugi (fine amount) yang dibebankan kepada Customer."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.damage-cases.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.damage-cases.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Kasus Kerusakan</span>
        </a>

        <hr class="my-3 border-gray-100" x-show="!collapsed || mobileOpen">

        <p x-show="!collapsed || mobileOpen"
           class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
            KATALOG PERALATAN
        </p>

        {{-- Kategori --}}
        <a href="{{ route('admin.categories.index') }}"
           title="Mengelola master data pada tabel categories, yang memungkinkan Admin untuk menambah, mengubah, atau menghapus klasifikasi jenis produk rental perlengkapan bayi."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.categories.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.categories.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Kategori</span>
        </a>

        {{-- Produk --}}
        <a href="{{ route('admin.products.index') }}"
           title="Mengelola master data pada tabel products (termasuk nama, harga sewa per hari, deskripsi, dan gambar utama perlengkapan bayi). Sistem memastikan pembatasan kuota penambahan produk didasarkan pada paket lisensi platform."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.products.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.products.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Produk</span>
        </a>

        {{-- Unit --}}
        <a href="{{ route('admin.units.index') }}"
           title="Mengelola tabel equipment_units. Menu untuk mendata kuantitas fisik spesifik dari sebuah produk melalui pengisian unit_code, serial_number, dan ketersediaan individual setiap unit perlengkapan bayi."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.units.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.units.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Unit</span>
        </a>

        <hr class="my-3 border-gray-100" x-show="!collapsed || mobileOpen">

        {{-- Laporan --}}
        <a href="{{ route('admin.reports.index') }}"
           title="Mengakses ringkasan informasi yang diekstraksi dari aktivitas operasional rental perlengkapan bayi. Berfungsi menyajikan laporan transaksi, riwayat pelaporan kondisi unit, serta kompilasi kasus kerusakan yang telah diinput Admin."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.reports.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.reports.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Laporan</span>
        </a>

    </nav>

    {{-- User Profile (bottom of sidebar) --}}
    <div class="border-t border-gray-100 p-3 bg-stone-50/50">
        <div class="flex items-center gap-3 px-2 py-1.5 rounded-xl bg-white border border-gray-100 shadow-sm">
            <div class="shrink-0 w-8 h-8 rounded-full bg-slate-900 flex items-center justify-center text-white text-xs font-bold">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div x-show="!collapsed || mobileOpen" class="flex-1 min-w-0">
                <p class="text-xs font-bold text-gray-900 truncate">{{ Auth::user()->name ?? 'Budi Admin' }}</p>
                <p class="text-[10px] text-gray-400 capitalize truncate">Admin Rental</p>
            </div>
            <form x-show="!collapsed || mobileOpen"
                  method="POST"
                  action="{{ route('admin.logout') }}"
                  class="shrink-0">
                @csrf
                <button type="submit"
                        title="Keluar"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<header id="topbar"
        :style="topbarStyle()"
        class="fixed top-0 right-0 z-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center gap-4 px-6 lg:px-8 transition-all duration-250 shadow-sm shadow-stone-100/40">

    {{-- Hamburger --}}
    <button @click="toggleSidebar()"
            id="btn-hamburger"
            class="shrink-0 w-9.5 h-9.5 flex items-center justify-center rounded-xl text-gray-500 hover:bg-stone-100 hover:text-gray-800 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Breadcrumb/Page title --}}
    <div class="flex items-center gap-2 text-xs font-medium">
        <span class="font-bold text-gray-900">{{ $client->business_name ?? 'Baby Rental Studio' }}</span>
        <span class="text-gray-300">/</span>
        <span class="text-gray-500">@yield('page-title', 'Dashboard Operasional')</span>
    </div>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Date --}}
    <div class="hidden lg:flex items-center gap-1.5 text-xs text-gray-500 font-medium">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, j F Y') }}</span>
    </div>

    {{-- Subscription badge --}}
    <div class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 bg-orange-50/80 border border-orange-200/60 rounded-full">
        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
        <span class="text-xs font-bold text-orange-700">Paket: {{ $subscription->plan_name ?? 'Business' }} (Aktif)</span>
    </div>

    {{-- Bell Notification Icon --}}
    <button class="relative w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-stone-100 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
        </svg>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-orange-500 ring-2 ring-white"></span>
    </button>

    {{-- Dark Circle Avatar --}}
    <div class="w-9 h-9 rounded-full bg-slate-900 flex items-center justify-center text-white text-xs font-bold shadow-sm">
        <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
    </div>
</header>

<div id="main-content"
     :style="mainContentStyle()"
     class="transition-all duration-250">
    <main class="min-h-screen px-6 py-6 lg:px-9 lg:py-8">
        @yield('content')
    </main>
</div>

{{-- Alpine.js Component --}}
<script>
    function adminLayout() {
        return {
            collapsed: false,
            mobileOpen: false,
            isMobile: false,

            init() {
                this.checkMobile();
                window.addEventListener('resize', () => this.checkMobile());
            },

            checkMobile() {
                this.isMobile = window.innerWidth < 1024;
                if (this.isMobile) {
                    this.collapsed = false;
                    this.mobileOpen = false;
                }
            },

            toggleSidebar() {
                if (this.isMobile) {
                    this.mobileOpen = !this.mobileOpen;
                } else {
                    this.collapsed = !this.collapsed;
                }
            },

            sidebarClasses() {
                const base = '';
                if (this.isMobile) {
                    return this.mobileOpen
                        ? 'translate-x-0'
                        : '-translate-x-full';
                }
                return this.collapsed
                    ? 'w-[72px]'
                    : 'w-[260px]';
            },

            topbarStyle() {
                const left = this.isMobile ? '0' : (this.collapsed ? '72px' : '260px');
                return `left: ${left}; height: var(--topbar-height);`;
            },

            mainContentStyle() {
                const ml = this.isMobile ? '0' : (this.collapsed ? '72px' : '260px');
                return `margin-left: ${ml}; padding-top: calc(var(--topbar-height) - 32px);`;
            },
        };
    }
</script>

</body>
</html>
