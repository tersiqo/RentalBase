@extends('layouts.admin')

@section('title', 'Dashboard Operasional')
@section('page-title', 'Dashboard Operasional')

@section('content')

{{-- Page Header --}}
<div class="mb-6 pt-1 lg:pt-2">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-orange-50 text-orange-600 border border-orange-200/60 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                    Pusat Kendali Rental
                </span>
                <span class="text-xs text-gray-400 font-medium hidden sm:inline">&bull;</span>
                <span class="text-xs text-gray-500 font-medium hidden sm:inline">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">
                Selamat Datang kembali, {{ Str::before($user->name ?? 'Admin', ' ') }}!
            </h1>
            <p class="mt-1 text-xs lg:text-sm text-gray-500 font-medium">
                Pantau pesanan masuk, jadwal pengiriman, utilisasi unit, dan pendapatan toko secara real-time.
            </p>
        </div>

        {{-- Header Quick Actions --}}
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-white hover:bg-stone-50 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold shadow-sm flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Semua Pesanan
            </a>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-bold shadow-sm shadow-orange-500/25 flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Kelola Produk
            </a>
        </div>
    </div>
</div>

{{-- Baris 1: 4 Kartu KPI Metrik Cerdas (Grid 4 Kolom) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- KPI 1: Order Masuk / Baru --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all group">
        <div class="flex items-start justify-between mb-2">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">ORDER MASUK / BARU</p>
                <p class="text-3xl font-extrabold text-gray-900 tracking-tight mt-1">{{ $totalBookingBaru ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        <div class="pt-2 border-t border-gray-50 flex items-center justify-between text-xs">
            @if(($bookingHariIni ?? 0) > 0)
                <span class="text-orange-600 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    +{{ $bookingHariIni }} order masuk hari ini
                </span>
            @else
                <span class="text-gray-400 font-medium">Tidak ada pesanan baru hari ini</span>
            @endif
            <a href="{{ route('admin.orders.index', ['status' => 'menunggu_konfirmasi']) }}" class="text-gray-400 hover:text-orange-600 font-bold transition-colors">
                Lihat &rarr;
            </a>
        </div>
    </div>

    {{-- KPI 2: Pengiriman Hari Ini --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all group">
        <div class="flex items-start justify-between mb-2">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-500">PENGIRIMAN HARI INI</p>
                <p class="text-3xl font-extrabold text-gray-900 tracking-tight mt-1">{{ $totalPengirimanHariIni ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4 4 4-4m-4-5v9m-7-7a4 4 0 014-4h10a4 4 0 014 4v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7z"/>
                </svg>
            </div>
        </div>
        <div class="pt-2 border-t border-gray-50 flex items-center justify-between text-xs">
            @if(($totalPengirimanHariIni ?? 0) > 0)
                <span class="text-indigo-600 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Perlu diantar / diambil
                </span>
            @else
                <span class="text-emerald-600 font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Semua jadwal kirim beres
                </span>
            @endif
            <a href="{{ route('admin.shipments.index') }}" class="text-gray-400 hover:text-indigo-600 font-bold transition-colors">
                Dispatch &rarr;
            </a>
        </div>
    </div>

    {{-- KPI 3: Pengembalian Hari Ini --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all group">
        <div class="flex items-start justify-between mb-2">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-500">PENGEMBALIAN HARI INI</p>
                <p class="text-3xl font-extrabold text-gray-900 tracking-tight mt-1">{{ $totalPengembalianHariIni ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
        </div>
        <div class="pt-2 border-t border-gray-50 flex items-center justify-between text-xs">
            @if(($totalPengembalianHariIni ?? 0) > 0)
                <span class="text-amber-600 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Masa sewa habis / dicek
                </span>
            @else
                <span class="text-gray-400 font-medium">Tidak ada jatuh tempo hari ini</span>
            @endif
            <a href="{{ route('admin.returns.index') }}" class="text-gray-400 hover:text-amber-600 font-bold transition-colors">
                Cek &rarr;
            </a>
        </div>
    </div>

    {{-- KPI 4: Omset Bulan Berjalan --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all group">
        <div class="flex items-start justify-between mb-2">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500">OMSET BULAN INI</p>
                <p class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight mt-1 whitespace-nowrap">
                    @php
                        $pendapatan = (float) ($pendapatanBulanIni ?? 0);
                        if ($pendapatan >= 1000000000) {
                            $formattedPendapatan = 'Rp ' . round($pendapatan / 1000000000, 1) . 'M';
                        } elseif ($pendapatan >= 1000000) {
                            $formattedPendapatan = 'Rp ' . round($pendapatan / 1000000, 1) . ' Juta';
                        } else {
                            $formattedPendapatan = 'Rp ' . number_format($pendapatan, 0, ',', '.');
                        }
                    @endphp
                    {{ $formattedPendapatan }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="pt-2 border-t border-gray-50 flex items-center justify-between text-xs">
            <span class="text-emerald-600 font-semibold flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
            </span>
            <span class="text-gray-400 font-medium">{{ $totalDisewa ?? 0 }} unit disewa</span>
        </div>
    </div>

</div>

{{-- Sisa Kuota Limit Paket & Status Langganan Toko (Sesuai Konfigurasi Single Source of Truth di config/packages.php) --}}
@php
    $currentPlanKey = strtolower(trim((string) ($subscription?->plan_name ?? 'starter')));
    $isBusiness = $currentPlanKey === 'business';
    $isProfessional = $currentPlanKey === 'professional';

    // Konfigurasi paket aktif dari config/packages.php sebagai Single Source of Truth
    $packageConfig = config("packages.{$currentPlanKey}") ?? ($pkgConfig ?? config('packages.starter'));
    $planName = $packageConfig['name'] ?? ucfirst($currentPlanKey);
    $planPrice = (int) ($packageConfig['price'] ?? 0);
    $planDuration = (int) ($packageConfig['duration_months'] ?? 3);
    $planDescription = $packageConfig['description'] ?? '';
    $canCustomTheme = (bool) ($packageConfig['features']['custom_theme'] ?? false);

    $planBadgeStyle = match($currentPlanKey) {
        'professional' => 'bg-purple-100 text-purple-700 border-purple-200/80',
        'business'     => 'bg-orange-100 text-orange-700 border-orange-200/80',
        default        => 'bg-slate-100 text-slate-700 border-slate-200/80',
    };

    // 1. Kuota Jenis Produk (config/packages.php: Starter=10, Business=50, Professional=null/Unlimited)
    $usedProd = $totalProduk ?? 0;
    $maxProd = $packageConfig['limits']['products'] ?? null;
    $isUnlimitedProd = $maxProd === null || $maxProd >= 9999;
    $pctProd = $isUnlimitedProd ? 0 : min(100, round(($usedProd / $maxProd) * 100));

    // 2. Kuota Unit Peralatan Fisik (config/packages.php: Starter=50, Business=100, Professional=null/Unlimited)
    $usedUnit = $totalUnit ?? 0;
    $maxUnitVal = $packageConfig['limits']['units'] ?? null;
    $isUnlimitedUnit = $maxUnitVal === null || $maxUnitVal >= 9999;
    $pctUnit = $isUnlimitedUnit ? 0 : min(100, round(($usedUnit / $maxUnitVal) * 100));

    // 3. Kuota Kategori (config/packages.php: Starter=5, Business=20, Professional=null/Unlimited)
    $usedCat = $totalKategori ?? 0;
    $maxCatVal = $packageConfig['limits']['categories'] ?? null;
    $isUnlimitedCat = $maxCatVal === null || $maxCatVal >= 9999;
    $pctCat = $isUnlimitedCat ? 0 : min(100, round(($usedCat / $maxCatVal) * 100));

    // 4. Kuota Admin Rental (config/packages.php: Starter=1, Business=3, Professional=10)
    $usedAdm = $totalAdmin ?? 1;
    $maxAdmVal = $packageConfig['limits']['admins'] ?? 1;
    $isUnlimitedAdm = $maxAdmVal === null || $maxAdmVal >= 9999;
    $pctAdm = $isUnlimitedAdm ? 0 : min(100, round(($usedAdm / $maxAdmVal) * 100));

    // Sisa hari & status aktif
    $isExp = $isExpired ?? false;
    $daysLeft = $daysRemaining ?? 0;
@endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 mb-6 space-y-4">
    {{-- Header Paket & Masa Aktif (Sesuai config/packages.php) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-100">
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider border {{ $planBadgeStyle }}">
                Paket {{ $planName }}
            </span>

            @if($isBusiness)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-orange-500 text-white shadow-xs">
                    Paling Populer
                </span>
            @elseif($isProfessional)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-600 text-white shadow-xs">
                    Kapasitas Maksimal
                </span>
            @endif

            <span class="text-xs text-gray-400 font-semibold hidden md:inline">&bull;</span>
            <span class="text-xs text-gray-600 font-medium hidden md:inline">
                Harga: <strong>{{ $planPrice > 0 ? 'Rp ' . number_format($planPrice, 0, ',', '.') : 'Gratis' }}</strong>
            </span>

            <span class="text-xs text-gray-400 font-semibold hidden md:inline">&bull;</span>
            <span class="text-xs text-gray-600 font-medium hidden md:inline">
                Durasi: <strong>{{ $planDuration }} Bulan</strong>
            </span>

            <span class="text-xs text-gray-400 font-semibold hidden sm:inline">&bull;</span>
            <div class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                <span>Masa Aktif:</span>
                <strong class="text-gray-700">
                    {{ $subscription?->start_date ? $subscription->start_date->translatedFormat('d M Y') : '-' }}
                </strong>
                <span>s/d</span>
                <strong class="text-gray-700">
                    {{ $subscription?->end_date ? $subscription->end_date->translatedFormat('d M Y') : '-' }}
                </strong>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            {{-- Status Badge --}}
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $isExp ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' }}">
                <span class="w-2 h-2 rounded-full {{ $isExp ? 'bg-red-500' : 'bg-emerald-500 ring-2 ring-emerald-200' }}"></span>
                {{ $isExp ? 'Kadaluarsa' : 'Aktif (' . $daysLeft . ' Hari Tersisa)' }}
            </span>
            
            <a href="{{ route('admin.subscriptions.index') }}" 
               class="px-3.5 py-1.5 bg-gray-900 text-white rounded-xl text-xs font-bold hover:bg-gray-800 transition-colors shadow-xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Upgrade Paket
            </a>
        </div>
    </div>

    {{-- Deskripsi Paket dari config/packages.php --}}
    @if(!empty($planDescription))
        <div class="flex items-center gap-2 text-xs text-gray-500 bg-stone-50/80 px-3 py-2 rounded-xl border border-gray-100">
            <svg class="w-3.5 h-3.5 text-orange-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium text-gray-600">{{ $planDescription }}</span>
        </div>
    @endif

    {{-- Alert Pengajuan Menunggu Konfirmasi (jika ada) --}}
    @if(!empty($pendingSubscription))
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/80 flex items-center justify-between gap-3 text-xs text-amber-800">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                    Pengajuan perpanjangan/upgrade ke paket <strong>{{ $pendingSubscription->plan_name }}</strong> sedang menunggu verifikasi pembayaran oleh Owner.
                </span>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}" class="font-bold underline text-amber-900 hover:text-orange-600 whitespace-nowrap">
                Cek Status &rarr;
            </a>
        </div>
    @endif

    {{-- 4-Grid Kuota Penggunaan Operasional (Sesuai limits di config/packages.php) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        {{-- 1. Kuota Jenis Produk (limits.products) --}}
        <div class="p-3.5 rounded-xl bg-stone-50/70 border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-xs font-bold text-gray-800">Katalog Produk</span>
                    </div>
                    <span class="text-xs font-extrabold {{ $pctProd >= 90 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedProd }} / {{ $isUnlimitedProd ? '∞' : $maxProd }}
                    </span>
                </div>
                <div class="w-full bg-gray-200/70 rounded-full h-2 overflow-hidden mb-1">
                    <div class="h-full rounded-full transition-all duration-700 ease-out {{ $pctProd >= 90 ? 'bg-red-500' : 'bg-orange-500' }}"
                         @style(['width: ' . ($isUnlimitedProd ? 100 : $pctProd) . '%'])></div>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-2 pt-2 border-t border-gray-100/60">
                <span class="truncate">{{ $isUnlimitedProd ? 'Unlimited jenis produk' : 'Maks. ' . $maxProd . ' jenis produk' }}</span>
                <a href="{{ route('admin.products.index') }}" class="text-orange-600 font-semibold hover:underline shrink-0">Kelola</a>
            </div>
        </div>

        {{-- 2. Kuota Unit Peralatan Fisik (limits.units) --}}
        <div class="p-3.5 rounded-xl bg-stone-50/70 border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-xs font-bold text-gray-800">Unit Peralatan Fisik</span>
                    </div>
                    <span class="text-xs font-extrabold {{ $pctUnit >= 90 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedUnit }} / {{ $isUnlimitedUnit ? '∞' : $maxUnitVal }}
                    </span>
                </div>
                <div class="w-full bg-gray-200/70 rounded-full h-2 overflow-hidden mb-1">
                    <div class="h-full rounded-full transition-all duration-700 ease-out {{ $pctUnit >= 90 ? 'bg-red-500' : 'bg-emerald-500' }}"
                         @style(['width: ' . ($isUnlimitedUnit ? 100 : $pctUnit) . '%'])></div>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-2 pt-2 border-t border-gray-100/60">
                <span class="truncate">{{ $isUnlimitedUnit ? 'Unlimited unit peralatan fisik total' : 'Maks. ' . $maxUnitVal . ' unit peralatan total' }}</span>
                <a href="{{ route('admin.units.index') }}" class="text-emerald-600 font-semibold hover:underline shrink-0">Kelola</a>
            </div>
        </div>

        {{-- 3. Kuota Kategori Produk (limits.categories) --}}
        <div class="p-3.5 rounded-xl bg-stone-50/70 border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="text-xs font-bold text-gray-800">Kategori Produk</span>
                    </div>
                    <span class="text-xs font-extrabold {{ $pctCat >= 90 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedCat }} / {{ $isUnlimitedCat ? '∞' : $maxCatVal }}
                    </span>
                </div>
                <div class="w-full bg-gray-200/70 rounded-full h-2 overflow-hidden mb-1">
                    <div class="h-full rounded-full transition-all duration-700 ease-out {{ $pctCat >= 90 ? 'bg-red-500' : 'bg-indigo-500' }}"
                         @style(['width: ' . ($isUnlimitedCat ? 100 : $pctCat) . '%'])></div>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-2 pt-2 border-t border-gray-100/60">
                <span class="truncate">{{ $isUnlimitedCat ? 'Unlimited kategori' : 'Maks. ' . $maxCatVal . ' kategori' }}</span>
                <a href="{{ route('admin.categories.index') }}" class="text-indigo-600 font-semibold hover:underline shrink-0">Kelola</a>
            </div>
        </div>

        {{-- 4. Kuota Akun Admin Rental (limits.admins) --}}
        <div class="p-3.5 rounded-xl bg-stone-50/70 border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-xs font-bold text-gray-800">Admin Rental</span>
                    </div>
                    <span class="text-xs font-extrabold {{ $pctAdm >= 100 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedAdm }} / {{ $maxAdmVal }}
                    </span>
                </div>
                <div class="w-full bg-gray-200/70 rounded-full h-2 overflow-hidden mb-1">
                    <div class="h-full rounded-full transition-all duration-700 ease-out {{ $pctAdm >= 100 ? 'bg-red-500' : 'bg-purple-500' }}"
                         @style(['width: ' . $pctAdm . '%'])></div>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-2 pt-2 border-t border-gray-100/60">
                <span class="truncate">Maks. {{ $maxAdmVal }} Admin Rental</span>
                <a href="{{ route('admin.admins.index') }}" class="text-purple-600 font-semibold hover:underline shrink-0">Kelola</a>
            </div>
        </div>
    </div>

    {{-- Footer Fitur Tambahan Paket (Sesuai features di config/packages.php) --}}
    <div class="pt-2 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-[11px] text-gray-500">
        <div class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 {{ $canCustomTheme ? 'text-emerald-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            @if($canCustomTheme)
                <span>Fitur Desain: <strong class="text-emerald-700">Dapat menyesuaikan warna tema toko</strong> (Kustom Tema Aktif)</span>
            @else
                <span>Fitur Desain: <strong class="text-gray-700">Tema Standar</strong> (Tidak dapat mengubah tema setelah registrasi awal)</span>
            @endif
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" class="font-bold text-orange-600 hover:text-orange-700 transition-colors">
            Lihat Rincian Paket Lengkap &rarr;
        </a>
    </div>
</div>

{{-- Baris 2: Dua Grafik Utama (Grid 2 Kolom: 60% vs 40%) --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">

    {{-- Kolom Kiri (60% / 7 cols): Grafik Tren Pendapatan 30 Hari --}}
    <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 lg:p-6 flex flex-col justify-between">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    <h2 class="text-base font-bold text-gray-900">Tren Pendapatan 30 Hari Terakhir</h2>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">Akumulasi omset harian dari pesanan aktif & selesai</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl bg-orange-50 text-orange-700 border border-orange-200/60 text-xs font-bold">
                    Total: Rp {{ number_format($revenueChartData['total_30_days'] ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Canvas Chart --}}
        <div class="relative w-full h-64 sm:h-72">
            <canvas id="revenueChart"></canvas>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-400">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                Nominal tercatat saat pesanan dibuat (non-batal)
            </span>
            <span class="text-gray-500 font-semibold">30 Hari Terakhir</span>
        </div>
    </div>

    {{-- Kolom Kanan (40% / 5 cols): Diagram Donat Utilisasi Unit Inventaris --}}
    <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 lg:p-6 flex flex-col justify-between">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-base font-bold text-gray-900">Utilisasi Unit Fisik</h2>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">Status ketersediaan armada peralatan toko</p>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-stone-100 text-gray-700 font-bold text-xs">
                {{ $unitStatusData['total'] ?? 0 }} Unit Total
            </span>
        </div>

        {{-- Container Donut dengan Center Percentage --}}
        <div class="relative flex items-center justify-center h-48 sm:h-52 my-auto">
            <canvas id="unitStatusChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
                    {{ $unitStatusData['utilization_rate'] ?? 0 }}%
                </span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                    Disewa
                </span>
            </div>
        </div>

        {{-- Legenda Donat --}}
        <div class="grid grid-cols-2 gap-2.5 mt-4 pt-3 border-t border-gray-50">
            <div class="flex items-center justify-between p-2 rounded-xl bg-emerald-50/50 border border-emerald-100/60 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="font-medium text-gray-700">Tersedia</span>
                </div>
                <span class="font-extrabold text-emerald-700">{{ $unitStatusData['tersedia'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-xl bg-blue-50/50 border border-blue-100/60 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
                    <span class="font-medium text-gray-700">Disewa</span>
                </div>
                <span class="font-extrabold text-blue-700">{{ $unitStatusData['disewa'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-xl bg-amber-50/50 border border-amber-100/60 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span class="font-medium text-gray-700">Steril/Cuci</span>
                </div>
                <span class="font-extrabold text-amber-700">{{ $unitStatusData['maintenance'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-xl bg-rose-50/50 border border-rose-100/60 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span class="font-medium text-gray-700">Rusak</span>
                </div>
                <span class="font-extrabold text-rose-700">{{ $unitStatusData['rusak'] ?? 0 }}</span>
            </div>
        </div>
    </div>

</div>

{{-- Baris 3: Top Produk & Jadwal Operasional Hari Ini (Grid 2 Kolom) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Kolom Kiri: 5 Produk Paling Diminati (Leaderboard / Horizontal Bar) --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 lg:p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-orange-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </span>
                        <h2 class="text-base font-bold text-gray-900">5 Produk Paling Diminati</h2>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">Paling sering disewa oleh pelanggan bulan ini</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700 transition-colors">
                    Lihat Katalog &rarr;
                </a>
            </div>

            <div class="space-y-3.5 mt-2">
                @forelse($topProducts as $index => $prod)
                    @php
                        $rankNumber = $index + 1;
                        $rankStyle = match($rankNumber) {
                            1 => 'bg-amber-100 text-amber-800 border-amber-200',
                            2 => 'bg-slate-100 text-slate-700 border-slate-200',
                            3 => 'bg-orange-100 text-orange-800 border-orange-200',
                            default => 'bg-gray-50 text-gray-500 border-gray-100',
                        };
                    @endphp
                    <div class="p-3 rounded-xl bg-stone-50/60 hover:bg-stone-50 border border-gray-100/80 transition-colors flex items-center gap-3">
                        {{-- Rank Badge --}}
                        <div class="w-7 h-7 rounded-lg border font-black text-xs flex items-center justify-center shrink-0 {{ $rankStyle }}">
                            #{{ $rankNumber }}
                        </div>

                        {{-- Thumbnail --}}
                        <img src="{{ $prod->thumbnail }}" 
                             alt="{{ $prod->name }}" 
                             class="w-11 h-11 rounded-xl object-cover border border-gray-200/80 shrink-0 bg-white"
                             loading="lazy">

                        {{-- Product Info & Progress Bar --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h3 class="text-xs font-bold text-gray-900 truncate" title="{{ $prod->name }}">
                                    {{ $prod->name }}
                                </h3>
                                <span class="text-xs font-extrabold text-orange-600 whitespace-nowrap">
                                    {{ $prod->total_rented }} unit
                                </span>
                            </div>
                            <div class="w-full bg-gray-200/80 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-orange-400 to-orange-500 h-full rounded-full transition-all duration-700" 
                                     @style(['width: ' . $prod->percentage . '%'])></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                                <span class="truncate">{{ $prod->category_name }}</span>
                                <span>{{ $prod->rental_count }}x transaksi</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Belum ada data persewaan produk bulan ini.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-50 text-[11px] text-gray-400 flex items-center justify-between">
            <span>Dihitung dari total kuantitas unit disewa</span>
            <span class="font-semibold text-gray-500">Top 5 Leaderboard</span>
        </div>
    </div>

    {{-- Kolom Kanan: Pusat Tindakan Hari Ini (Today's Dispatch & Return Hub) --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 lg:p-6 flex flex-col justify-between"
         x-data="{ activeTab: 'kirim' }">
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <h2 class="text-base font-bold text-gray-900">Perlu Diproses</h2>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">Operasional pengiriman dan pengembalian unit hari ini</p>
                </div>

                {{-- Interactive Tabs --}}
                <div class="flex items-center p-1 bg-stone-100 rounded-xl">
                    <button type="button"
                            @click="activeTab = 'kirim'"
                            :class="activeTab === 'kirim' ? 'bg-white text-gray-900 shadow-sm font-bold' : 'text-gray-500 font-medium hover:text-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4 4 4-4m-4-5v9m-7-7a4 4 0 014-4h10a4 4 0 014 4v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7z"/>
                        </svg>
                        Jadwal Kirim
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700">
                            {{ $totalPengirimanHariIni }}
                        </span>
                    </button>
                    <button type="button"
                            @click="activeTab = 'kembali'"
                            :class="activeTab === 'kembali' ? 'bg-white text-gray-900 shadow-sm font-bold' : 'text-gray-500 font-medium hover:text-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Jadwal Kembali
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700">
                            {{ $totalPengembalianHariIni }}
                        </span>
                    </button>
                </div>
            </div>

            {{-- Tab 1: Jadwal Kirim (Maksimal 4 Tindakan) --}}
            <div x-show="activeTab === 'kirim'" class="space-y-3 mt-2" x-transition:enter="transition ease-out duration-200">
                @forelse($todayShipments->take(4) as $shipmentOrder)
                    @php
                        $firstItem = $shipmentOrder->orderItems->first();
                        $itemTitle = $firstItem ? ($firstItem->product->name ?? 'Produk') : 'Barang rental';
                        if ($shipmentOrder->orderItems->count() > 1) {
                            $itemTitle .= ' (+' . ($shipmentOrder->orderItems->count() - 1) . ' item)';
                        }
                        $isPickup = $shipmentOrder->isPickup();
                    @endphp
                    <div class="p-3 rounded-xl bg-indigo-50/30 hover:bg-indigo-50/60 border border-indigo-100/60 transition-colors flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl {{ $isPickup ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700' }} flex items-center justify-center shrink-0">
                                @if($isPickup)
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <h4 class="text-xs font-bold text-gray-900 truncate">{{ $shipmentOrder->user->name ?? 'Pelanggan' }}</h4>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold {{ $isPickup ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ $isPickup ? 'Ambil di Toko' : 'Kurir Antar' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 truncate" title="{{ $itemTitle }}">{{ $itemTitle }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    Mulai: {{ \Carbon\Carbon::parse($shipmentOrder->rental_start_date)->format('H:i') !== '00:00' ? \Carbon\Carbon::parse($shipmentOrder->rental_start_date)->format('d M H:i') : \Carbon\Carbon::parse($shipmentOrder->rental_start_date)->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.show', $shipmentOrder->id) }}" 
                           class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:border-indigo-500 hover:text-indigo-600 font-bold text-xs text-gray-700 shadow-sm transition-colors whitespace-nowrap">
                            Proses &rarr;
                        </a>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tidak ada pengiriman atau serah terima unit yang dijadwalkan hari ini.
                    </div>
                @endforelse

                @if(($totalPengirimanHariIni ?? 0) > 4)
                    <div class="pt-2 text-center">
                        <a href="{{ route('admin.shipments.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                            <span>Lihat {{ $totalPengirimanHariIni - 4 }} Pengiriman Lainnya</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Tab 2: Jadwal Kembali (Maksimal 4 Tindakan) --}}
            <div x-show="activeTab === 'kembali'" class="space-y-3 mt-2" x-cloak x-transition:enter="transition ease-out duration-200">
                @forelse($todayReturns->take(4) as $returnOrder)
                    @php
                        $firstItem = $returnOrder->orderItems->first();
                        $itemTitle = $firstItem ? ($firstItem->product->name ?? 'Produk') : 'Barang rental';
                        if ($returnOrder->orderItems->count() > 1) {
                            $itemTitle .= ' (+' . ($returnOrder->orderItems->count() - 1) . ' item)';
                        }
                        $endDate = \Carbon\Carbon::parse($returnOrder->rental_end_date);
                        $isOverdue = $endDate->isPast() && !$endDate->isToday();
                    @endphp
                    <div class="p-3 rounded-xl {{ $isOverdue ? 'bg-rose-50/40 border-rose-200/80' : 'bg-amber-50/30 border-amber-100/60' }} border transition-colors flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl {{ $isOverdue ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }} flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <h4 class="text-xs font-bold text-gray-900 truncate">{{ $returnOrder->user->name ?? 'Pelanggan' }}</h4>
                                    @if($isOverdue)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold bg-rose-100 text-rose-700 animate-pulse">
                                            Terlambat Kembali
                                        </span>
                                    @else
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold bg-amber-100 text-amber-800">
                                            Jatuh Tempo Hari Ini
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-gray-500 truncate" title="{{ $itemTitle }}">{{ $itemTitle }}</p>
                                <p class="text-[10px] {{ $isOverdue ? 'text-rose-600 font-semibold' : 'text-gray-400' }} mt-0.5">
                                    Batas Kembali: {{ $endDate->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.show', $returnOrder->id) }}" 
                           class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:border-amber-500 hover:text-amber-600 font-bold text-xs text-gray-700 shadow-sm transition-colors whitespace-nowrap">
                            Cek Unit &rarr;
                        </a>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-xs">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tidak ada unit yang jatuh tempo pengembalian hari ini.
                    </div>
                @endforelse

                @if(($totalPengembalianHariIni ?? 0) > 4)
                    <div class="pt-2 text-center">
                        <a href="{{ route('admin.returns.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 hover:text-amber-800 transition-colors">
                            <span>Lihat {{ $totalPengembalianHariIni - 4 }} Pengembalian Lainnya</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-50 text-[11px] text-gray-400 flex items-center justify-between">
            <span>Maksimal 4 tindakan prioritas ditampilkan</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.shipments.index') }}" class="hover:text-indigo-600 font-semibold transition-colors">Semua Kiriman ({{ $totalPengirimanHariIni }})</a>
                <span>&bull;</span>
                <a href="{{ route('admin.returns.index') }}" class="hover:text-amber-600 font-semibold transition-colors">Semua Pengembalian ({{ $totalPengembalianHariIni }})</a>
            </div>
        </div>
    </div>

</div>

{{-- Baris 4: Pesanan Terbaru (Recent Orders Table) --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col mb-6">
    
    {{-- Table Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-5 py-4 border-b border-gray-100 gap-3 bg-stone-50/40">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
            <div>
                <h2 class="text-base font-bold text-gray-900">Pesanan Terbaru</h2>
                <p class="text-xs text-gray-400">Daftar transaksi sewa paling mutakhir dari pelanggan</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="relative">
                <input type="text"
                       name="search"
                       placeholder="Cari pesanan..."
                       class="w-44 lg:w-56 pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 transition-all shadow-sm">
                <button type="submit" aria-label="Cari Pesanan" class="text-gray-400 hover:text-orange-500 absolute left-2.5 top-2 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
            <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-1.5 bg-white hover:bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 shadow-sm flex items-center gap-1.5 transition-colors">
                Lihat Semua
            </a>
        </div>
    </div>

    {{-- Table Content --}}
    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="bg-stone-50/70 border-b border-gray-100">
                    <th class="px-5 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">ID PESANAN</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">CUSTOMER & PRODUK</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">PERIODE SEWA</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">METODE</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">TOTAL HARGA</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">STATUS</th>
                    <th class="px-5 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400 text-right">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentOrders as $order)
                    @php
                        $statusLabel = $order->status_label;
                        $statusClean = strtolower(trim(str_replace(['-', ' '], '_', $order->status ?? '')));
                        
                        // Badge Status dengan tema warna yang sesuai instruksi
                        $statusBadge = match($statusClean) {
                            'menunggu_konfirmasi'                  => 'bg-orange-50 text-orange-700 border border-orange-200/60',
                            'menunggu_pembayaran', 'belum_dibayar' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                            'menunggu_verifikasi', 'verifikasi'    => 'bg-yellow-50 text-yellow-800 border border-yellow-200/60',
                            'siap_kirim', 'diproses', 'dikonfirmasi'=> 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                            'dikirim'                              => 'bg-purple-50 text-purple-700 border border-purple-200/60',
                            'sedang_disewa'                        => 'bg-blue-50 text-blue-700 border border-blue-200/60',
                            'dikembalikan'                         => 'bg-cyan-50 text-cyan-700 border border-cyan-200/60',
                            'selesai'                              => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                            'dibatalkan', 'batal', 'ditolak'       => 'bg-rose-50 text-rose-700 border border-rose-200/60',
                            default                                => 'bg-slate-100 text-slate-700 border border-slate-200/60',
                        };

                        $dotColor = match($statusClean) {
                            'menunggu_konfirmasi', 'menunggu_pembayaran', 'belum_dibayar' => 'bg-orange-500',
                            'menunggu_verifikasi', 'verifikasi'                           => 'bg-yellow-500',
                            'siap_kirim', 'diproses', 'dikonfirmasi', 'selesai'           => 'bg-emerald-500',
                            'dikirim'                                                     => 'bg-purple-500',
                            'sedang_disewa'                                               => 'bg-blue-500',
                            'dikembalikan'                                                => 'bg-cyan-500',
                            'dibatalkan', 'batal', 'ditolak'                              => 'bg-rose-500',
                            default                                                       => 'bg-slate-400',
                        };
                        
                        $startDate = \Carbon\Carbon::parse($order->rental_start_date);
                        $endDate = \Carbon\Carbon::parse($order->rental_end_date);
                        $duration = max(1, (int) ($order->duration_days ?? $startDate->diffInDays($endDate)));
                        
                        $firstItem = $order->orderItems->first();
                        $productName = $firstItem ? ($firstItem->product->name ?? 'Produk tidak diketahui') : 'Tidak ada produk';
                        if($order->orderItems->count() > 1) {
                            $productName .= ' (+' . ($order->orderItems->count() - 1) . ' item)';
                        }

                        $isPickup = $order->isPickup();
                    @endphp
                    <tr class="hover:bg-stone-50/50 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-orange-500 whitespace-nowrap">
                            #{{ $order->order_number ?? 'ORD-'.$order->id }}
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-bold text-gray-900 leading-tight">{{ $order->user->name ?? 'Tamu' }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate max-w-[200px]" title="{{ $productName }}">
                                {{ $productName }}
                            </p>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $startDate->format('d M') }} - {{ $endDate->format('d M') }}</span>
                                <span class="text-gray-400 font-medium">({{ $duration }}h)</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold {{ $isPickup ? 'bg-stone-100 text-stone-700' : 'bg-indigo-50 text-indigo-700' }}">
                                {{ $isPickup ? 'Ambil Toko' : 'Kurir' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 font-bold text-gray-900 whitespace-nowrap">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusBadge }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('admin.orders.show', $order->id) }}" 
                               class="inline-block px-3 py-1.5 bg-white border border-gray-200 hover:border-orange-500 hover:text-orange-500 text-gray-600 font-semibold rounded-lg text-xs transition-colors shadow-sm">
                                Kelola
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-xs">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            Belum ada transaksi pesanan terbaru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Data JSON untuk Grafik --}}
<script type="application/json" id="revenue-chart-data">@json($revenueChartData)</script>
<script type="application/json" id="unit-status-data">@json($unitStatusData)</script>
<script>
(function() {
    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initDashboardCharts, 50);
            return;
        }

        const revenueEl = document.getElementById('revenue-chart-data');
        const unitEl = document.getElementById('unit-status-data');
        if (!revenueEl || !unitEl) return;

        const revenueChartData = JSON.parse(revenueEl.textContent || '{}');
        const unitStatusData = JSON.parse(unitEl.textContent || '{}');

        // ==========================================
        // 1. Grafik Tren Pendapatan 30 Hari (Line Area Chart)
        // ==========================================
        const ctxRevenue = document.getElementById('revenueChart');
        if (ctxRevenue) {
            // Hancurkan chart sebelumnya jika ada agar tidak bentrok
            const existingRevenue = Chart.getChart(ctxRevenue);
            if (existingRevenue) {
                existingRevenue.destroy();
            }

            const ctx2d = ctxRevenue.getContext('2d');
            const gradient = ctx2d.createLinearGradient(0, 0, 0, 280);
            gradient.addColorStop(0, 'rgba(249, 115, 22, 0.30)');
            gradient.addColorStop(1, 'rgba(249, 115, 22, 0.00)');

            new Chart(ctx2d, {
                type: 'line',
                data: {
                    labels: revenueChartData.labels || [],
                    datasets: [{
                        label: 'Pendapatan',
                        data: revenueChartData.data || [],
                        borderColor: '#f97316',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#ea580c',
                        pointBorderWidth: 2,
                        pointRadius: 2.5,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#ea580c',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#18181b',
                            titleColor: '#a1a1aa',
                            titleFont: { size: 11, weight: '600' },
                            bodyColor: '#ffffff',
                            bodyFont: { size: 12, weight: '700' },
                            padding: 10,
                            cornerRadius: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + Number(context.raw || 0).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: { size: 10 },
                                maxTicksLimit: 8
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: { size: 10 },
                                callback: function(val) {
                                    if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + 'M';
                                    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + 'jt';
                                    if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'rb';
                                    return 'Rp ' + val;
                                }
                            }
                        }
                    }
                }
            });
        }

        // ==========================================
        // 2. Diagram Donat Utilisasi Unit Inventaris
        // ==========================================
        const ctxUnit = document.getElementById('unitStatusChart');
        if (ctxUnit) {
            // Hancurkan chart sebelumnya jika ada agar tidak bentrok
            const existingUnit = Chart.getChart(ctxUnit);
            if (existingUnit) {
                existingUnit.destroy();
            }

            const totalUnits = (unitStatusData && unitStatusData.total) ? unitStatusData.total : 0;
            const chartLabels = (unitStatusData && unitStatusData.labels) ? unitStatusData.labels : ['Tersedia', 'Disewa', 'Steril/Cuci', 'Rusak'];
            
            const chartData = (totalUnits > 0)
                ? (unitStatusData.data || [0, 0, 0, 0])
                : [1];
            const chartColors = (totalUnits > 0)
                ? ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
                : ['#e5e7eb'];

            new Chart(ctxUnit.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: (totalUnits > 0) ? chartLabels : ['Belum Ada Unit'],
                    datasets: [{
                        data: chartData,
                        backgroundColor: chartColors,
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: (totalUnits > 0) ? 6 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: totalUnits > 0,
                            backgroundColor: '#18181b',
                            titleColor: '#a1a1aa',
                            titleFont: { size: 11, weight: '600' },
                            bodyColor: '#ffffff',
                            bodyFont: { size: 12, weight: '700' },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const val = Number(context.raw || 0);
                                    const pct = totalUnits > 0 ? Math.round((val / totalUnits) * 100) : 0;
                                    return ` ${context.label}: ${val} unit (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Jalankan segera jika halaman berpindah via AJAX / DOM sudah siap,
    // atau tunggu DOMContentLoaded jika refresh halaman awal
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
})();
</script>

@endsection
