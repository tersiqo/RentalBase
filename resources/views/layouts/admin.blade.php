@php
    $currentClient = $client ?? Auth::user()?->client ?? Auth::user()?->tenant;
    $currentProfile = $currentClient?->tenantProfile;
    $storeName = $currentProfile?->name ?? $currentClient?->business_name ?? $currentClient?->nama_usaha ?? 'RentalBase Admin';
    $subdomain = $currentProfile?->subdomain ?? $currentClient?->subdomain ?? 'tenant';

    $storeLogo = $currentProfile?->logo_path ?? $currentClient?->logo ?? null;
    $storeLogoUrl = null;
    if ($storeLogo) {
        $storeLogoUrl = str_starts_with($storeLogo, 'http')
            ? $storeLogo
            : \Illuminate\Support\Facades\Storage::url($storeLogo);
    }

    // Notifikasi operasional untuk toko aktif
    $adminNotifications = collect();
    $adminUnreadNotificationsCount = 0;
    if ($currentClient) {
        $adminNotifications = \App\Models\Notification::where('client_id', $currentClient->id)
            ->latest()
            ->take(5)
            ->get();
        $adminUnreadNotificationsCount = \App\Models\Notification::where('client_id', $currentClient->id)
            ->where('is_read', false)
            ->count();
    }
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $storeName }}</title>
    <meta name="description" content="Panel Admin RentalBase — Kelola pesanan, produk, dan operasional rental peralatan bayi Anda.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/rentalbase-icon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

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
        @if($storeLogoUrl)
            <img src="{{ $storeLogoUrl }}"
                 alt="{{ $storeName }}"
                 onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.style.display='flex';"
                 class="shrink-0 w-9 h-9 rounded-xl object-contain border border-gray-100 p-0.5 bg-white shadow-sm">
            <div class="hidden shrink-0 w-9 h-9 bg-orange-500 rounded-xl items-center justify-center shadow-md shadow-orange-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        @else
            <div class="shrink-0 w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shadow-md shadow-orange-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        @endif
        <div x-show="!collapsed || mobileOpen"
             x-transition:enter="transition-opacity duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="overflow-hidden whitespace-nowrap min-w-0">
            <p class="text-sm font-bold text-gray-900 leading-tight truncate" title="{{ $storeName }}">{{ $storeName }}</p>
            <p class="text-[11px] text-gray-400 font-medium truncate">Rental Ops — {{ $subdomain }}.rentalbase</p>
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

        {{-- Notifikasi --}}
        <a href="{{ route('admin.notifications.index') }}"
           title="Pusat pemberitahuan operasional toko terkait pesanan baru, bukti pembayaran, verifikasi KTP, dan pengembalian unit."
           class="group flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.notifications.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <div class="flex items-center gap-3 min-w-0">
                <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.notifications.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                    </svg>
                </span>
                <span x-show="!collapsed || mobileOpen" class="truncate">Notifikasi</span>
            </div>
            @if($adminUnreadNotificationsCount > 0)
                <span x-show="!collapsed || mobileOpen"
                      class="shrink-0 px-2 py-0.5 text-[10px] font-bold rounded-full bg-orange-100 text-orange-700">
                    {{ $adminUnreadNotificationsCount > 99 ? '99+' : $adminUnreadNotificationsCount }}
                </span>
            @endif
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
           title="Mencatat tiket kerusakan setelah barang kembali, mengomunikasikan penilaian kepada penyewa, serta mengelola verifikasi kompensasi dan penutupan kasus."
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
           title="Mengelola master data unit fisik individual (kode aset, nomor seri, dan status ketersediaan) perlengkapan rental."
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
           title="Pantau laporan transaksi, pendapatan sewa pokok, denda, dan kompensasi kerusakan toko."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.reports.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.reports.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Laporan</span>
        </a>

        {{-- Log Aktivitas Toko --}}
        <a href="{{ route('admin.activity-logs.index') }}"
           title="Menelusuri jejak rekam audit dan aktivitas operasional staf toko."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.activity-logs.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.activity-logs.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Log Aktivitas</span>
        </a>

        <hr class="my-3 border-gray-100" x-show="!collapsed || mobileOpen">

        <p x-show="!collapsed || mobileOpen"
           class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
            PENGATURAN TOKO
        </p>

        {{-- Profil & Tema Toko --}}
        <a href="{{ route('admin.settings.profile') }}"
           title="Mengatur identitas toko, logo, kontak, dan kustomisasi warna tema."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.settings.profile*') || request()->routeIs('admin.theme.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.settings.profile*') || request()->routeIs('admin.theme.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Profil & Tema Toko</span>
        </a>

        {{-- Metode Pembayaran --}}
        <a href="{{ route('admin.settings.payments') }}"
           title="Mengatur nomor rekening bank dan QRIS pembayaran pesanan customer."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.settings.payments*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.settings.payments*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Metode Pembayaran</span>
        </a>

        {{-- Langganan & Paket Toko --}}
        <a href="{{ route('admin.subscriptions.index') }}"
           title="Pantau kuota operasional toko, perpanjang masa aktif, atau upgrade paket langganan."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.subscriptions.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.subscriptions.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Langganan & Paket</span>
        </a>

        @if(Auth::user()?->isPrimaryAdmin())
        {{-- Manajemen Akun Admin --}}
        <a href="{{ route('admin.admins.index') }}"
           title="Mengelola akun staf Admin internal toko dan memantau penggunaan kuota paket subscription."
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.admins.*') ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm border border-orange-100/50' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('admin.admins.*') ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-600' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Manajemen Akun Admin</span>
        </a>
        @endif
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
                <p class="text-[10px] text-gray-400 capitalize truncate">{{ Auth::user()?->isPrimaryAdmin() ? 'Admin Utama' : 'Admin Staf' }}</p>
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
        <span class="font-bold text-gray-900">{{ $storeName }}</span>
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
    <a href="{{ route('admin.subscriptions.index') }}"
       title="Kelola Langganan & Upgrade Paket"
       class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 bg-orange-50/80 hover:bg-orange-100/90 border border-orange-200/60 rounded-full transition-colors cursor-pointer group">
        <span class="w-2 h-2 rounded-full bg-orange-500 group-hover:scale-125 transition-transform"></span>
        <span class="text-xs font-bold text-orange-700">Paket: {{ $subscription->plan_name ?? 'Business' }} (Aktif)</span>
    </a>

    {{-- Bell Notification Dropdown --}}
    <div x-data="{ notifOpen: false }" class="relative" @keydown.escape.stop="notifOpen = false">
        <button @click="notifOpen = !notifOpen"
                type="button"
                id="btn-admin-notifications"
                title="Pemberitahuan Operasional Toko"
                class="relative w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-stone-100 transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500"
                :aria-expanded="notifOpen.toString()">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
            </svg>
            @if($adminUnreadNotificationsCount > 0)
                <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-orange-600 text-[10px] font-bold text-white ring-2 ring-white">
                    {{ $adminUnreadNotificationsCount > 99 ? '99+' : $adminUnreadNotificationsCount }}
                </span>
            @endif
        </button>

        {{-- Dropdown Preview Menu --}}
        <div x-show="notifOpen"
             x-cloak
             id="admin-notification-dropdown-menu"
             @click.away="notifOpen = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-xl border border-gray-100 py-2 z-50 text-xs font-medium divide-y divide-gray-100">

            {{-- Header --}}
            <div class="px-4 py-2.5 flex items-center justify-between bg-stone-50/60">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900 text-sm">Pemberitahuan</span>
                    @if($adminUnreadNotificationsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">
                            {{ $adminUnreadNotificationsCount }} baru
                        </span>
                    @endif
                </div>
                @if($adminUnreadNotificationsCount > 0)
                    <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[11px] font-medium text-orange-600 hover:text-orange-700 transition">
                            Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
            </div>

            {{-- 5 Latest Notifications List --}}
            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                @forelse($adminNotifications as $notif)
                    <div class="p-3.5 hover:bg-stone-50 transition {{ ! $notif->is_read ? 'bg-orange-50/30' : '' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                @if(! $notif->is_read)
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 shrink-0"></span>
                                @endif
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $notif->type_badge['bg'] }}">
                                    {{ $notif->type_badge['label'] }}
                                </span>
                            </div>
                            <span class="text-[10px] text-gray-400 shrink-0">
                                {{ $notif->created_at ? $notif->created_at->diffForHumans() : '-' }}
                            </span>
                        </div>
                        <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST" class="mt-1">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $notif->admin_target_url }}">
                            <button type="submit" class="text-left font-semibold text-gray-800 hover:text-orange-600 transition truncate w-full">
                                {{ $notif->title }}
                            </button>
                        </form>
                        <p class="text-gray-500 text-[11px] mt-0.5 line-clamp-2 leading-relaxed">
                            {{ $notif->message }}
                        </p>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-gray-400">
                        <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                        </svg>
                        <p class="font-medium text-xs text-gray-500">Belum ada pemberitahuan</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Semua peringatan operasional akan muncul di sini</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="p-2 text-center bg-stone-50/40">
                <a href="{{ route('admin.notifications.index') }}"
                   class="inline-flex items-center justify-center w-full py-1.5 text-xs font-semibold text-orange-600 hover:text-orange-700 hover:bg-orange-50/60 rounded-xl transition">
                    Lihat Semua Pemberitahuan
                </a>
            </div>
        </div>
    </div>

    {{-- Profile Dropdown --}}
    <div x-data="{ profileOpen: false }" class="relative" @keydown.escape.stop="profileOpen = false">
        <button @click="profileOpen = !profileOpen"
                type="button"
                id="btn-profile-dropdown"
                title="Menu Profil Admin"
                class="w-9 h-9 rounded-full bg-slate-900 flex items-center justify-center text-white text-xs font-bold shadow-sm hover:ring-2 hover:ring-orange-500 hover:ring-offset-2 transition-all focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                :aria-expanded="profileOpen.toString()">
            @if(Auth::user()?->avatar)
                <img src="{{ storage_url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-full h-full rounded-full object-cover">
            @else
                <span class="sr-only">Buka menu profil</span>
                <span class="uppercase font-semibold text-xs">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
            @endif
        </button>

        {{-- Dropdown Menu --}}
        <div x-show="profileOpen"
             x-cloak
             @click.away="profileOpen = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl border border-gray-100 py-1.5 z-50 text-xs font-medium divide-y divide-gray-100">

            {{-- User Info Header --}}
            <div class="px-4 py-3 bg-stone-50/50">
                <p class="font-bold text-gray-900 text-sm truncate">{{ Auth::user()->name ?? 'Budi Admin' }}</p>
                <p class="text-gray-500 truncate text-[11px] mt-0.5">{{ Auth::user()->email ?? 'admin@rentalbase.id' }}</p>
                <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full {{ Auth::user()?->isPrimaryAdmin() ? 'bg-orange-50 text-orange-700 border-orange-200/60' : 'bg-slate-100 text-slate-700 border-slate-200' }} text-[10px] font-bold border">
                    <span class="w-1.5 h-1.5 rounded-full {{ Auth::user()?->isPrimaryAdmin() ? 'bg-orange-500' : 'bg-slate-400' }}"></span>
                    <span>{{ Auth::user()?->isPrimaryAdmin() ? 'Admin Utama' : 'Admin Staf' }}</span>
                </div>
            </div>

            {{-- Main Links --}}
            <div class="py-1.5">
                <a href="{{ route('admin.settings.profile') }}"
                   class="flex items-center gap-2.5 px-4 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Profil &amp; Tema Toko</span>
                </a>
                <a href="{{ route('admin.settings.payments') }}"
                   class="flex items-center gap-2.5 px-4 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Metode Pembayaran</span>
                </a>
            </div>

            {{-- Logout Action --}}
            <div class="py-1.5">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-left text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-semibold">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
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

{{-- Unobtrusive AJAX Filter, Search, Reset, and Pagination System --}}
<script>
    (function() {
        let currentPartialAbort = null;
        let progressBarEl = null;

        function getOrCreateProgressBar() {
            if (!progressBarEl) {
                progressBarEl = document.createElement('div');
                progressBarEl.id = 'admin-ajax-progress';
                progressBarEl.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:linear-gradient(90deg,#f97316,#ea580c);z-index:99999;transition:width 0.2s ease, opacity 0.3s ease;width:0%;opacity:0;pointer-events:none;';
                document.body.appendChild(progressBarEl);
            }
            return progressBarEl;
        }

        function startProgressBar() {
            const bar = getOrCreateProgressBar();
            bar.style.transition = 'width 0.2s ease, opacity 0.3s ease';
            bar.style.opacity = '1';
            bar.style.width = '30%';
            setTimeout(() => {
                if (bar.style.opacity === '1') bar.style.width = '70%';
            }, 150);
        }

        function finishProgressBar() {
            const bar = getOrCreateProgressBar();
            bar.style.width = '100%';
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => {
                    bar.style.width = '0%';
                }, 300);
            }, 150);
        }

        window.adminPartialSwap = async function(targetUrl, options = {}) {
            const {
                pushHistory = true,
                preserveScroll = false,
                scrollToTable = false,
                preserveFocus = true,
                showLoading = true
            } = options;

            if (currentPartialAbort) {
                currentPartialAbort.abort();
            }
            currentPartialAbort = new AbortController();
            const { signal } = currentPartialAbort;

            const currentMain = document.querySelector('main');
            if (!currentMain) {
                window.location.href = targetUrl;
                return;
            }

            const scrollX = window.scrollX;
            const scrollY = window.scrollY;

            // Preserve active input focus & cursor selection
            let activeName = null;
            let selectionStart = null;
            let selectionEnd = null;
            if (preserveFocus) {
                const activeEl = document.activeElement;
                if (activeEl && currentMain.contains(activeEl) && activeEl.name) {
                    activeName = activeEl.name;
                    if (typeof activeEl.selectionStart === 'number') {
                        selectionStart = activeEl.selectionStart;
                        selectionEnd = activeEl.selectionEnd;
                    }
                }
            }

            // Visual loading indication
            const tableContainer = currentMain.querySelector('table')?.closest('.overflow-x-auto')?.parentElement 
                || currentMain.querySelector('table')?.parentElement 
                || currentMain;
            if (showLoading) {
                startProgressBar();
                tableContainer.style.transition = 'opacity 0.15s ease';
                tableContainer.style.opacity = '0.55';
                tableContainer.style.pointerEvents = 'none';
            }

            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/xhtml+xml'
                    },
                    signal
                });

                if (!response.ok) {
                    console.warn(`[RentalBase Admin AJAX] Request returned status ${response.status}. Falling back to standard navigation.`);
                    window.location.href = targetUrl;
                    return;
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newMain = doc.querySelector('main');
                if (!newMain) {
                    window.location.href = targetUrl;
                    return;
                }

                if (doc.title) {
                    document.title = doc.title;
                }

                if (pushHistory && window.location.href !== targetUrl) {
                    window.history.pushState({ rbAdminAjax: true, url: targetUrl }, '', targetUrl);
                }

                if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                    window.Alpine.destroyTree(currentMain);
                }

                currentMain.innerHTML = newMain.innerHTML;

                // Execute scripts in replacement DOM
                currentMain.querySelectorAll('script').forEach(script => {
                    const newScript = document.createElement('script');
                    Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.textContent = script.textContent;
                    script.parentNode.replaceChild(newScript, script);
                });

                // Synchronize notification bell & dropdown in topbar
                const currentBell = document.getElementById('btn-admin-notifications');
                const newBell = doc.getElementById('btn-admin-notifications');
                if (currentBell && newBell) {
                    currentBell.innerHTML = newBell.innerHTML;
                }
                const currentMenu = document.getElementById('admin-notification-dropdown-menu');
                const newMenu = doc.getElementById('admin-notification-dropdown-menu');
                if (currentMenu && newMenu) {
                    currentMenu.innerHTML = newMenu.innerHTML;
                }

                // Synchronize CSRF token
                const newCsrf = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (newCsrf) {
                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', newCsrf);
                }

                // Re-initialize Alpine tree
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(currentMain);
                }

                // Re-scan Livewire if present
                if (window.Livewire && typeof window.Livewire.rescan === 'function') {
                    window.Livewire.rescan();
                }

                // Restore focused element & cursor
                if (activeName) {
                    const safeSelector = (typeof CSS !== 'undefined' && CSS.escape) ? CSS.escape(activeName) : activeName.replace(/"/g, '\\"');
                    const restoredEl = currentMain.querySelector(`[name="${safeSelector}"]`);
                    if (restoredEl && typeof restoredEl.focus === 'function') {
                        restoredEl.focus();
                        if (typeof restoredEl.setSelectionRange === 'function' && selectionStart !== null && selectionEnd !== null) {
                            restoredEl.setSelectionRange(selectionStart, selectionEnd);
                        }
                    }
                }

                // Scroll handling
                if (preserveScroll) {
                    window.scrollTo({ top: scrollY, left: scrollX, behavior: 'instant' });
                } else if (scrollToTable) {
                    const newTable = currentMain.querySelector('table');
                    if (newTable) {
                        const rect = newTable.getBoundingClientRect();
                        window.scrollTo({
                            top: Math.max(0, window.scrollY + rect.top - 100),
                            behavior: 'smooth'
                        });
                    }
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    return;
                }
                console.error('[RentalBase Admin AJAX] Error during partial swap:', err);
                window.location.href = targetUrl;
            } finally {
                tableContainer.style.opacity = '';
                tableContainer.style.pointerEvents = '';
                finishProgressBar();
            }
        };

        // 1. Intercept GET form submit inside main (Filter & Search)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;
            if ((form.getAttribute('method') || 'GET').toUpperCase() !== 'GET') return;
            if (!form.closest('main')) return;
            if (form.hasAttribute('data-no-ajax')) return;
            if (e.defaultPrevented) return;

            e.preventDefault();

            const formAction = form.getAttribute('action') || window.location.pathname;
            const actionUrl = new URL(formAction, window.location.origin);
            const params = new URLSearchParams(actionUrl.search);
            const formData = new FormData(form);

            for (const [key, val] of formData.entries()) {
                if (typeof val === 'string' && val.trim() !== '') {
                    params.set(key, val.trim());
                } else {
                    params.delete(key);
                }
            }

            const queryString = params.toString();
            const targetUrl = actionUrl.pathname + (queryString ? '?' + queryString : '');

            window.adminPartialSwap(targetUrl, {
                pushHistory: true,
                preserveFocus: true,
                scrollToTable: false
            });
        });

        // 2. Intercept Reset, Pagination, and Notification tab links inside main
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !link.href) return;
            if (link.target && link.target !== '_self') return;
            if (link.hasAttribute('download') || link.hasAttribute('data-no-ajax')) return;
            if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
            if (!link.closest('main')) return;

            const url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) return;

            const isResetLink = link.closest('form[method="GET"], form[method="get"]') ||
                                link.hasAttribute('data-ajax-reset') ||
                                (link.textContent && link.textContent.trim().toLowerCase().startsWith('reset') && url.pathname === window.location.pathname);

            const isPagination = link.closest('nav[role="navigation"]') ||
                                 link.closest('.pagination') ||
                                 (url.pathname === window.location.pathname && url.searchParams.has('page'));

            const isNotificationTab = url.pathname.includes('/admin/notifications') && url.searchParams.has('tab');

            if (isResetLink || isPagination || isNotificationTab) {
                e.preventDefault();
                window.adminPartialSwap(link.href, {
                    pushHistory: true,
                    preserveFocus: isResetLink,
                    scrollToTable: isPagination
                });
            }
        });

        // 3. Handle browser Back/Forward navigation smoothly
        window.addEventListener('popstate', function(e) {
            if (document.querySelector('main')) {
                window.adminPartialSwap(window.location.href, {
                    pushHistory: false,
                    preserveScroll: false,
                    preserveFocus: false
                });
            }
        });
    })();
</script>

@if($currentClient)
@if(config('broadcasting.connections.pusher.key'))
<script>
    (function() {
        window.ensureAdminEcho = function() {
            if (window.Echo && typeof window.Echo.private === 'function') {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token && window.Echo.connector?.options?.auth?.headers) {
                    window.Echo.connector.options.auth.headers['X-CSRF-TOKEN'] = token;
                }
                return window.Echo;
            }
            const EchoCtor = (typeof window.EchoClass === 'function') ? window.EchoClass : ((typeof window.Echo === 'function') ? window.Echo : null);
            if (EchoCtor && typeof Pusher !== 'undefined') {
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
            return window.Echo || null;
        };
        window.ensureAdminEcho();
    })();
</script>
@endif

{{-- Real-Time WebSocket Toast Notification Container --}}
<div x-data="realtimeNotification({{ $currentClient->id }})" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full">
    <template x-for="toast in toasts" :key="toast.id">
        <div class="pointer-events-auto bg-white border border-gray-200 rounded-xl shadow-2xl p-4 flex items-start gap-3 transform transition-all duration-300 ease-out"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2">
            <div class="w-9 h-9 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-gray-900" x-text="toast.title"></h4>
                <p class="text-xs text-gray-600 mt-0.5" x-text="toast.message"></p>
                <div class="mt-2 flex items-center gap-2">
                    <a x-show="toast.url" :href="toast.url" class="text-xs font-semibold text-orange-600 hover:text-orange-700">Lihat Pesanan &rarr;</a>
                    <button @click="removeToast(toast.id)" class="text-xs text-gray-400 hover:text-gray-600 ml-auto">Tutup</button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function realtimeNotification(clientId) {
        return {
            toasts: [],
            _reloadTimer: null,
            init() {
                try {
                    const saved = sessionStorage.getItem('rb_pending_toast_admin');
                    if (saved) {
                        sessionStorage.removeItem('rb_pending_toast_admin');
                        const parsed = JSON.parse(saved);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            parsed.forEach(t => {
                                this.addToast(t.title, t.message, t.url);
                            });
                        }
                    }
                } catch (err) {}

                const startListening = () => {
                    const echo = (typeof window.ensureAdminEcho === 'function')
                        ? window.ensureAdminEcho()
                        : (window.Echo && typeof window.Echo.private === 'function' ? window.Echo : null);

                    if (!echo) {
                        return false;
                    }

                    const isOrderPage = (orderId = null) => {
                        const p = window.location.pathname;
                        return p === '/admin/dashboard' || p === '/admin' || p === '/admin/orders' || (orderId && p.includes('/admin/orders/' + orderId));
                    };

                    echo.private(`tenant.${clientId}.orders`)
                        .error((error) => {
                            console.error('[RentalBase Realtime] Gagal autentikasi channel tenant:', error);
                        })
                        .listen('.order.placed', (e) => {
                            this.playChime();
                            this.addToast('Pesanan Baru Masuk!', e.message || `Pesanan #${e.order_number} baru saja dibuat oleh customer.`, `/admin/orders/${e.order_id}`);
                            if (isOrderPage(e.order_id)) {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.payment.uploaded', (e) => {
                            this.playChime();
                            this.addToast('Bukti Pembayaran Diunggah!', e.message || `Bukti bayar untuk pesanan #${e.order_number} telah diupload.`, `/admin/orders/${e.order_id}`);
                            if (isOrderPage(e.order_id) || window.location.pathname === '/admin/payments') {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.identity.uploaded', (e) => {
                            this.playChime();
                            this.addToast('Revisi KTP Diunggah!', e.message || `Customer mengunggah revisi identitas pesanan #${e.order_number}.`, `/admin/orders/${e.order_id}`);
                            if (isOrderPage(e.order_id)) {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.return.requested', (e) => {
                            this.playChime();
                            this.addToast('Pengajuan Retur Barang!', e.message || `Customer mengajukan retur untuk pesanan #${e.order_number}.`, `/admin/returns`);
                            if (isOrderPage(e.order_id) || window.location.pathname === '/admin/returns') {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.order.received', (e) => {
                            this.playChime();
                            this.addToast('Pesanan Telah Diterima!', e.message || `Customer telah menerima pesanan #${e.order_number}.`, `/admin/orders/${e.order_id}`);
                            if (isOrderPage(e.order_id) || window.location.pathname === '/admin/shipments') {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.order.cancelled', (e) => {
                            this.playChime();
                            this.addToast('Pesanan Dibatalkan!', e.message || `Customer membatalkan pesanan #${e.order_number}.`, `/admin/orders/${e.order_id}`);
                            if (isOrderPage(e.order_id)) {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.late_fee.uploaded', (e) => {
                            this.playChime();
                            this.addToast('Bukti Denda Diunggah!', e.message || `Bukti pembayaran denda pesanan #${e.order_number} telah diupload.`, `/admin/returns`);
                            if (window.location.pathname.includes('/admin/returns') || window.location.pathname.includes('/admin/orders/' + e.order_id)) {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.damage.payment_uploaded', (e) => {
                            this.playChime();
                            this.addToast('Bukti Kompensasi Kerusakan!', e.message || `Customer mengunggah bukti bayar kompensasi kerusakan.`, `/admin/damage-cases/${e.case_id}`);
                            if (window.location.pathname.includes('/admin/damage-cases') || window.location.pathname.includes('/admin/orders')) {
                                this.triggerReload(800);
                            }
                        })
                        .listen('.tenant.quota_alert', (e) => {
                            this.playChime();
                            this.addToast('Peringatan Kuota Produk!', e.message, '/admin/products');
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
            triggerReload(delay = 800, forceFull = false) {
                if (this._reloadTimer) return;
                this._reloadTimer = setTimeout(async () => {
                    this._reloadTimer = null;

                    if (forceFull) {
                        try {
                            if (this.toasts && this.toasts.length > 0) {
                                sessionStorage.setItem('rb_pending_toast_admin', JSON.stringify(this.toasts));
                            }
                        } catch (err) {}
                        window.location.reload();
                        return;
                    }

                    // 1. Guard: Check if admin is currently typing or editing an input
                    const activeEl = document.activeElement;
                    const isEditing = activeEl && (
                        activeEl.tagName === 'INPUT' ||
                        activeEl.tagName === 'TEXTAREA' ||
                        activeEl.tagName === 'SELECT' ||
                        activeEl.isContentEditable
                    );
                    if (isEditing) {
                        return;
                    }

                    // 2. Guard: Check if an interactive modal/dialog is visible
                    const openDialog = document.querySelector('[role="dialog"]:not([style*="display: none"]):not([style*="display:none"])');
                    if (openDialog && openDialog.offsetParent !== null) {
                        return;
                    }

                    if (typeof window.adminPartialSwap === 'function') {
                        await window.adminPartialSwap(window.location.href, {
                            pushHistory: false,
                            preserveScroll: true,
                            preserveFocus: false,
                            showLoading: false
                        });
                        return;
                    }

                    window.location.reload();
                }, delay);
            },
            addToast(title, message, url = null) {
                const id = Date.now() + Math.floor(Math.random() * 1000);
                this.toasts.push({ id, title, message, url });
                setTimeout(() => this.removeToast(id), 6000);
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
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime);
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

</body>
</html>
