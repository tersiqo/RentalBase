@extends('layouts.owner')

@section('title', 'Subscription Tenant — RentalBase Platform')
@section('page-title', 'Subscription Tenant')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="subscriptionPage()">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs space-y-1">
            <div class="font-bold flex items-center gap-2 text-rose-700">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Periksa kembali isian formulir:
            </div>
            <ul class="list-disc list-inside text-xs text-rose-600 pl-1 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200/60">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.dashboard') }}" class="text-xs text-orange-600 hover:underline flex items-center gap-1 font-semibold">
                    &larr; Dashboard
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-medium">Manajemen Subscriptions</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight sm:text-3xl mt-1">
                Subscription & Masa Aktif Tenant
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Monitor masa aktif langganan toko rental, deteksi periode jatuh tempo, dan perpanjang masa aktif berdasarkan konfirmasi pembayaran manual.
            </p>
        </div>
    </div>

    {{-- 4 KARTU RINGKASAN METRIK SUBSCRIPTION --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Total Tenant Aktif Berlangganan --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Tenant Aktif</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['total_active']) }}</h3>
                <p class="text-[11px] text-emerald-600 mt-0.5">Masa aktif berlaku</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- 2. Tenant Mendekati Jatuh Tempo (H-7) --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Jatuh Tempo (H-7)</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['expiring_soon']) }}</h3>
                <p class="text-[11px] text-amber-600 mt-0.5">Berakhir dalam 7 hari</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- 3. Tenant Kedaluwarsa --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Kedaluwarsa</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['expired']) }}</h3>
                <p class="text-[11px] text-rose-600 mt-0.5">Perlu perpanjangan masa aktif</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        {{-- 4. Estimasi Pendapatan Langganan (MRR) --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between bg-gradient-to-br from-white via-orange-50/20 to-orange-100/30">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-orange-600">Estimasi MRR</p>
                <h3 class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($metrics['estimated_mrr'], 0, ',', '.') }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Recurring revenue bulanan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-bold shadow-sm shadow-orange-500/30 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- FILTER PAKET, STATUS, DAN PENCARIAN --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('owner.subscriptions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">

            {{-- 1. Pencarian Toko / Subdomain --}}
            <div class="lg:col-span-5">
                <label for="search" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Pencarian Tenant
                </label>
                <div class="relative">
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama toko rental atau subdomain..."
                           class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- 2. Filter Paket --}}
            <div class="lg:col-span-3">
                <label for="plan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Paket Langganan
                </label>
                <select id="plan"
                        name="plan"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                    <option value="all" {{ $planFilter === 'all' ? 'selected' : '' }}>Semua Paket</option>
                    <option value="starter" {{ $planFilter === 'starter' ? 'selected' : '' }}>Starter (3 Bulan)</option>
                    <option value="business" {{ $planFilter === 'business' ? 'selected' : '' }}>Business (6 Bulan)</option>
                    <option value="professional" {{ $planFilter === 'professional' ? 'selected' : '' }}>Professional (12 Bulan)</option>
                </select>
            </div>

            {{-- 3. Filter Status --}}
            <div class="lg:col-span-2">
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Status
                </label>
                <select id="status"
                        name="status"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi ({{ $pendingSubscriptionsCount ?? 0 }})</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expiring_soon" {{ $statusFilter === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                    <option value="expired" {{ $statusFilter === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            {{-- 4. Tombol Filter & Reset --}}
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl font-bold text-sm bg-orange-500 hover:bg-orange-600 text-white shadow-md shadow-orange-500/20 active:scale-95 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>

                <a href="{{ route('owner.subscriptions.index') }}"
                   title="Reset filter ke kondisi awal"
                   class="inline-flex items-center justify-center px-3 py-2.5 rounded-xl font-bold text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </a>
            </div>

        </form>
    </div>

    {{-- TABEL MASTER SUBSCRIPTION TENANT --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Daftar Langganan Tenant</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Menampilkan total {{ $subscriptions->total() }} record langganan aktif, mendekati jatuh tempo, maupun kadaluarsa.
                </p>
            </div>
            <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-600 rounded-full w-fit">
                Halaman {{ $subscriptions->currentPage() }} dari {{ $subscriptions->lastPage() ?: 1 }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs text-slate-500 uppercase font-bold tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center w-12">No.</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Nama Toko / Tenant</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Subdomain</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Paket</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Mulai</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Jatuh Tempo</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Sisa Hari</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Status</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @php
                        $nowTime = \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'));
                    @endphp

                    @forelse($subscriptions as $index => $sub)
                        @php
                            $client = $sub->client;
                            $endDate = $sub->end_date ? \Carbon\Carbon::parse($sub->end_date) : null;
                            $startDate = $sub->start_date ? \Carbon\Carbon::parse($sub->start_date) : null;

                            // Perhitungan sisa hari aktif
                            $isExpired = false;
                            $daysLeftText = '-';
                            $daysLeftClass = 'bg-slate-100 text-slate-600';

                            if ($endDate) {
                                $diff = (int) $nowTime->diffInDays($endDate, false);

                                if ($endDate->isPast()) {
                                    $isExpired = true;
                                    $passedDays = abs($diff);
                                    $daysLeftText = $passedDays === 0 ? 'Expired hari ini' : "Expired {$passedDays} hari lalu";
                                    $daysLeftClass = 'bg-rose-50 text-rose-700 font-bold border border-rose-200';
                                } else {
                                    if ($diff === 0) {
                                        $daysLeftText = 'Jatuh tempo hari ini';
                                        $daysLeftClass = 'bg-amber-50 text-amber-700 font-bold border border-amber-200 animate-pulse';
                                    } elseif ($diff <= 7) {
                                        $daysLeftText = "{$diff} hari lagi";
                                        $daysLeftClass = 'bg-amber-50 text-amber-700 font-bold border border-amber-200';
                                    } else {
                                        $daysLeftText = "{$diff} hari lagi";
                                        $daysLeftClass = 'bg-slate-100 text-slate-700 font-medium';
                                    }
                                }
                            }

                            // Penentuan Status Badge
                            // Active, Expiring Soon, Expired, Suspended, Menunggu Verifikasi, Ditolak
                            $badgeLabel = 'Active';
                            $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                            $dotClass = 'bg-emerald-500';

                            if ($sub->status === 'menunggu_konfirmasi' || $sub->payment_status === 'menunggu') {
                                $badgeLabel = 'Menunggu Verifikasi';
                                $badgeClass = 'bg-amber-50 text-amber-800 border border-amber-300 animate-pulse';
                                $dotClass = 'bg-amber-500';
                            } elseif ($sub->status === 'ditolak' || $sub->payment_status === 'ditolak') {
                                $badgeLabel = 'Ditolak';
                                $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                $dotClass = 'bg-rose-500';
                            } elseif ($client && $client->status === 'suspended') {
                                $badgeLabel = 'Suspended';
                                $badgeClass = 'bg-slate-100 text-slate-700 border border-slate-300';
                                $dotClass = 'bg-slate-500';
                            } elseif ($sub->status === 'nonaktif') {
                                $badgeLabel = 'Suspended';
                                $badgeClass = 'bg-slate-100 text-slate-700 border border-slate-300';
                                $dotClass = 'bg-slate-500';
                            } elseif ($isExpired || $sub->status === 'kadaluarsa') {
                                $badgeLabel = 'Expired';
                                $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                $dotClass = 'bg-rose-500';
                            } elseif ($endDate && $endDate->diffInDays($nowTime) <= 7) {
                                $badgeLabel = 'Expiring Soon';
                                $badgeClass = 'bg-amber-50 text-amber-800 border border-amber-300';
                                $dotClass = 'bg-amber-500';
                            }

                            // Plan badge styling
                            $planKey = strtolower($sub->plan_name);
                            $planLabel = ucfirst($sub->plan_name);
                            $planClass = match($planKey) {
                                'professional' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                'business'     => 'bg-blue-50 text-blue-700 border border-blue-200',
                                default        => 'bg-slate-100 text-slate-700 border border-slate-200',
                            };

                            // Durasi paket
                            $planDuration = match($planKey) {
                                'business'     => 6,
                                'professional' => 12,
                                default        => 3,
                            };
                        @endphp

                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- No --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center text-xs text-slate-400 font-medium">
                                {{ $subscriptions->firstItem() + $index }}
                            </td>

                            {{-- Nama Toko / Tenant --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($client?->business_name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('owner.tenants.index', ['search' => $client?->subdomain]) }}"
                                           title="Buka profil usaha tenant di Manajemen Tenant"
                                           class="font-bold text-slate-900 hover:text-orange-600 transition-colors inline-flex items-center gap-1 group">
                                            <span>{{ $client?->business_name ?? 'Tenant Tidak Ditemukan' }}</span>
                                            <svg class="w-3 h-3 text-slate-400 group-hover:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                        <div class="text-xs text-slate-400">
                                            Admin: {{ $client?->adminUser?->name ?? 'Belum ada admin' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Subdomain --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="http://{{ $client?->subdomain }}.localhost:8000"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-orange-600 hover:underline">
                                    <span>{{ $client?->subdomain }}</span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </td>

                            {{-- Paket --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $planClass }}">
                                    {{ $planLabel }}
                                </span>
                            </td>

                            {{-- Tanggal Mulai --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center text-xs font-medium text-slate-600">
                                {{ $startDate ? $startDate->translatedFormat('d M Y') : '-' }}
                            </td>

                            {{-- Tanggal Jatuh Tempo --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center text-xs font-bold text-slate-900">
                                {{ $endDate ? $endDate->translatedFormat('d M Y') : '-' }}
                            </td>

                            {{-- Sisa Hari --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs {{ $daysLeftClass }}">
                                    {{ $daysLeftText }}
                                </span>
                            </td>

                            {{-- Status Langganan --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $badgeLabel }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($sub->status === 'menunggu_konfirmasi' || $sub->payment_status === 'menunggu')
                                        {{-- Tombol Verifikasi --}}
                                        <button type="button"
                                                @click="openVerify({
                                                    id: {{ $sub->id }},
                                                    business_name: '{{ addslashes($client?->business_name ?? 'Tenant') }}',
                                                    subdomain: '{{ $client?->subdomain }}',
                                                    plan_name: '{{ $planLabel }}',
                                                    plan_key: '{{ $planKey }}',
                                                    price_formatted: 'Rp {{ number_format($sub->price ?? 0, 0, ',', '.') }}',
                                                    notes: '{{ addslashes($sub->notes ?? '-') }}',
                                                    proof_url: '{{ $sub->payment_proof_url }}',
                                                    created_at: '{{ $sub->created_at ? $sub->created_at->translatedFormat('d M Y H:i') : '-' }}'
                                                })"
                                                title="Verifikasi Bukti Transfer & Aktifkan Paket"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all active:scale-95 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Verifikasi</span>
                                        </button>

                                        {{-- Tombol Tolak --}}
                                        <button type="button"
                                                @click="openReject({
                                                    id: {{ $sub->id }},
                                                    business_name: '{{ addslashes($client?->business_name ?? 'Tenant') }}',
                                                    plan_name: '{{ $planLabel }}'
                                                })"
                                                title="Tolak Pengajuan Bukti Transfer"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all active:scale-95 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Tolak</span>
                                        </button>
                                    @endif

                                    {{-- Tombol Perpanjang Manual --}}
                                    <button type="button"
                                            @click="openRenew({
                                                id: {{ $sub->id }},
                                                business_name: '{{ addslashes($client?->business_name ?? 'Tenant') }}',
                                                subdomain: '{{ $client?->subdomain }}',
                                                plan_name: '{{ $planLabel }}',
                                                plan_key: '{{ $planKey }}',
                                                duration: {{ $planDuration }},
                                                start_date: '{{ $startDate ? $startDate->translatedFormat('d M Y') : '-' }}',
                                                end_date: '{{ $endDate ? $endDate->translatedFormat('d M Y') : '-' }}',
                                                is_expired: {{ $isExpired ? 'true' : 'false' }},
                                                days_left: '{{ $daysLeftText }}'
                                            })"
                                            title="Perpanjang manual masa aktif subscription"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-orange-500 hover:bg-orange-600 text-white shadow-xs transition-all active:scale-95 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        <span>Perpanjang</span>
                                    </button>

                                    {{-- Tombol Riwayat --}}
                                    <button type="button"
                                            @click="openHistory({{ $sub->id }}, '{{ addslashes($client?->business_name ?? 'Tenant') }}')"
                                            title="Lihat riwayat perpanjangan dan log aktivitas"
                                            class="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Riwayat</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center bg-white">
                                <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 shadow-inner">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <p class="text-base font-bold text-slate-700">Tidak ada subscription ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    Tidak ada data subscription tenant yang sesuai dengan filter atau kata kunci pencarian yang dipilih.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($subscriptions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $subscriptions->links() }}
            </div>
        @endif

    </div>

    {{-- MODAL PERPANJANGAN SUBSCRIPTION --}}
    <div x-show="showRenewModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeRenew()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                 @click.outside="closeRenew()">

                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-title">Perpanjang Subscription</h3>
                            <p class="text-xs text-slate-500">Konfirmasi pembayaran dan perpanjang masa aktif tenant</p>
                        </div>
                    </div>
                    <button type="button" @click="closeRenew()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body Form --}}
                <form :action="'{{ url('/owner/subscriptions') }}/' + currentSub.id + '/renew'"
                      method="POST"
                      enctype="multipart/form-data"
                      @submit="isSubmitting = true">
                    @csrf

                    <div class="p-6 space-y-4">

                        {{-- Ringkasan Tenant Info Box --}}
                        <div class="p-4 bg-orange-50/50 border border-orange-200/60 rounded-xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Tenant:</span>
                                <span class="text-xs font-bold text-slate-900" x-text="currentSub.business_name"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Subdomain:</span>
                                <span class="text-xs font-semibold text-slate-700 font-mono" x-text="currentSub.subdomain"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Paket Langganan:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-white text-orange-700 border border-orange-200" x-text="currentSub.plan_name"></span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-orange-200/40">
                                <span class="text-xs text-slate-500 font-medium">Jatuh Tempo Saat Ini:</span>
                                <span class="text-xs font-bold text-slate-900" x-text="currentSub.end_date"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Kondisi Status:</span>
                                <span class="text-xs font-bold" :class="currentSub.is_expired ? 'text-rose-600' : 'text-emerald-600'" x-text="currentSub.days_left"></span>
                            </div>
                        </div>

                        {{-- Pilihan Durasi Perpanjangan (Terkunci Sesuai Paket) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Durasi Perpanjangan (Sesuai Paket)
                            </label>
                            <div class="px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 flex items-center justify-between">
                                <span x-text="currentSub.duration + ' Bulan (' + currentSub.plan_name + ')'"></span>
                                <span class="text-xs text-slate-500 font-normal">Otomatis terhitung</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Sesuai ketentuan platform: Starter (3 bln), Business (6 bln), Professional (12 bln).
                            </p>
                        </div>

                        {{-- Nomor Referensi Pembayaran / Invoice --}}
                        <div>
                            <label for="reference_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Referensi Transfer / Invoice <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="reference_number"
                                   name="reference_number"
                                   required
                                   maxlength="100"
                                   placeholder="Contoh: INV-20261029-001 atau REF-BCA-8921"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                        </div>

                        {{-- Upload Bukti Pembayaran --}}
                        <div>
                            <label for="payment_proof" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Unggah Bukti Pembayaran <span class="text-rose-500">*</span>
                            </label>
                            <input type="file"
                                   id="payment_proof"
                                   name="payment_proof"
                                   required
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 file:cursor-pointer border border-slate-200 rounded-xl bg-slate-50 p-1.5">
                            <p class="text-[11px] text-slate-400 mt-1">
                                Format: JPG, JPEG, PNG, atau PDF. Maksimal 5MB.
                            </p>
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button"
                                @click="closeRenew()"
                                :disabled="isSubmitting"
                                class="px-4 py-2.5 rounded-xl font-bold text-sm bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-orange-500 hover:bg-orange-600 text-white shadow-md shadow-orange-500/20 active:scale-95 transition-all disabled:opacity-50 cursor-pointer">
                            <svg class="w-4 h-4" :class="{ 'animate-spin': isSubmitting }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="isSubmitting ? 'Memproses...' : 'Konfirmasi & Perpanjang Masa Aktif'"></span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- MODAL RIWAYAT PERPANJANGAN --}}
    <div x-show="showHistoryModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-history-title" role="dialog" aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeHistory()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200"
                 @click.outside="closeHistory()">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-history-title">Riwayat Aktivitas &amp; Perpanjangan</h3>
                            <p class="text-xs text-slate-500" x-text="historyTenantName"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeHistory()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Content Body --}}
                <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
                    <template x-if="isLoadingHistory">
                        <div class="py-12 text-center text-slate-400">
                            <svg class="w-8 h-8 animate-spin mx-auto text-orange-500 mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <p class="text-xs font-medium">Memuat riwayat subscription...</p>
                        </div>
                    </template>

                    <template x-if="!isLoadingHistory && historyLogs.length === 0">
                        <div class="py-8 text-center text-slate-400">
                            <p class="text-sm font-semibold text-slate-600">Belum ada riwayat perpanjangan manual tercatat.</p>
                            <p class="text-xs text-slate-400 mt-1">Aktivitas perpanjangan berikutnya akan terekam secara otomatis di sini.</p>
                        </div>
                    </template>

                    <template x-if="!isLoadingHistory && historyLogs.length > 0">
                        <div class="space-y-3">
                            <template x-for="(log, idx) in historyLogs" :key="log.id">
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs space-y-1.5">
                                    <div class="flex items-center justify-between text-slate-400">
                                        <span class="font-bold text-slate-700" x-text="log.action.replace('_', ' ').toUpperCase()"></span>
                                        <span x-text="new Date(log.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })"></span>
                                    </div>
                                    <p class="text-slate-800 font-medium" x-text="log.description"></p>
                                    <div class="text-[11px] text-slate-400 pt-1 border-t border-slate-200/50 flex items-center justify-between">
                                        <span>Diproses oleh: <strong class="text-slate-600" x-text="log.user ? log.user.name : 'Owner Platform'"></strong></span>
                                        <span x-text="log.ip_address ? 'IP: ' + log.ip_address : ''"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-right">
                    <button type="button"
                            @click="closeHistory()"
                            class="px-4 py-2 rounded-xl font-bold text-xs bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL VERIFIKASI PEMBAYARAN SUBSCRIPTION --}}
    <div x-show="showVerifyModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-verify-title" role="dialog" aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeVerify()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                 @click.outside="closeVerify()">

                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-emerald-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-verify-title">Verifikasi &amp; Aktifkan Paket</h3>
                            <p class="text-xs text-slate-500">Periksa bukti transfer dan setujui aktivasi subscription tenant</p>
                        </div>
                    </div>
                    <button type="button" @click="closeVerify()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('/owner/subscriptions') }}/' + verifyData.id + '/verify'"
                      method="POST"
                      @submit="isSubmitting = true">
                    @csrf

                    <div class="p-6 space-y-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Toko / Tenant:</span>
                                <span class="font-bold text-slate-900" x-text="verifyData.business_name"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Subdomain:</span>
                                <span class="font-mono font-semibold text-slate-700" x-text="verifyData.subdomain"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Paket Diajukan:</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-orange-50 text-orange-700 border border-orange-200" x-text="verifyData.plan_name"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Nominal Transfer:</span>
                                <span class="font-bold text-emerald-700" x-text="verifyData.price_formatted"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-200">
                                <span class="text-slate-500 block mb-0.5">Catatan / Referensi Tenant:</span>
                                <span class="font-medium text-slate-800" x-text="verifyData.notes"></span>
                            </div>
                        </div>

                        {{-- Bukti Transfer Preview --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Bukti Transfer Pembayaran
                            </label>
                            <template x-if="verifyData.proof_url">
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 p-2">
                                    <template x-if="verifyData.proof_url.endsWith('.pdf')">
                                        <a :href="verifyData.proof_url" target="_blank" class="flex items-center justify-center gap-2 p-4 text-xs font-bold text-orange-600 hover:underline">
                                            <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            Buka Dokumen Bukti Transfer (PDF)
                                        </a>
                                    </template>
                                    <template x-if="!verifyData.proof_url.endsWith('.pdf')">
                                        <div class="text-center">
                                            <a :href="verifyData.proof_url" target="_blank" title="Klik untuk memperbesar">
                                                <img :src="verifyData.proof_url" alt="Bukti Transfer" class="max-h-56 mx-auto rounded-lg object-contain shadow-xs hover:opacity-95 transition-opacity" />
                                            </a>
                                            <p class="text-[11px] text-slate-400 mt-1">Klik gambar untuk melihat resolusi penuh di tab baru.</p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!verifyData.proof_url">
                                <div class="p-4 rounded-xl bg-slate-100 text-center text-xs text-slate-500 italic">
                                    Tidak ada file bukti pembayaran terlampir.
                                </div>
                            </template>
                        </div>

                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Efek Verifikasi (Model Pilihan A):
                            </p>
                            <ul class="list-disc list-inside text-[11px] text-emerald-700 space-y-0.5">
                                <li><strong>Upgrade:</strong> Periode paket baru terhitung penuh hari ini & kuota/fitur langsung aktif.</li>
                                <li><strong>Perpanjangan:</strong> Sisa masa aktif yang ada diakumulasikan ke masa aktif baru.</li>
                                <li>Status tenant otomatis diaktifkan kembali jika sebelumnya dibekukan/kadaluarsa.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button"
                                @click="closeVerify()"
                                :disabled="isSubmitting"
                                class="px-4 py-2.5 rounded-xl font-bold text-sm bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 active:scale-95 transition-all disabled:opacity-50 cursor-pointer">
                            <svg class="w-4 h-4" :class="{ 'animate-spin': isSubmitting }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="isSubmitting ? 'Memproses...' : 'Verifikasi & Aktifkan Paket'"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TOLAK PENGAJUAN SUBSCRIPTION --}}
    <div x-show="showRejectModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-reject-title" role="dialog" aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeReject()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200"
                 @click.outside="closeReject()">

                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-rose-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-reject-title">Tolak Pengajuan Langganan</h3>
                            <p class="text-xs text-slate-500">Berikan alasan jelas agar tenant dapat memperbaiki bukti pembayaran</p>
                        </div>
                    </div>
                    <button type="button" @click="closeReject()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('/owner/subscriptions') }}/' + rejectData.id + '/reject'"
                      method="POST"
                      @submit="isSubmitting = true">
                    @csrf

                    <div class="p-6 space-y-4">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                            <p class="text-slate-500">Toko: <strong class="text-slate-800" x-text="rejectData.business_name"></strong></p>
                            <p class="text-slate-500 mt-0.5">Paket: <strong class="text-orange-700" x-text="rejectData.plan_name"></strong></p>
                        </div>

                        <div>
                            <label for="rejection_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alasan Penolakan <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="rejection_reason"
                                      name="rejection_reason"
                                      rows="3"
                                      required
                                      maxlength="500"
                                      placeholder="Contoh: Bukti transfer buram/tidak terbaca, nominal tidak sesuai paket, atau dana belum masuk rekening."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Alasan ini akan langsung tampil di dashboard Admin Tenant bersangkutan.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button"
                                @click="closeReject()"
                                :disabled="isSubmitting"
                                class="px-4 py-2.5 rounded-xl font-bold text-sm bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20 active:scale-95 transition-all disabled:opacity-50 cursor-pointer">
                            <span x-text="isSubmitting ? 'Memproses...' : 'Tolak Pengajuan'"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>

{{-- SCRIPT ALPINE.JS INTERAKSI SUBSCRIPTION --}}
<script>
    function subscriptionPage() {
        return {
            showRenewModal: false,
            showHistoryModal: false,
            showVerifyModal: false,
            showRejectModal: false,
            isSubmitting: false,
            isLoadingHistory: false,
            historyTenantName: '',
            historyLogs: [],
            currentSub: {
                id: null,
                business_name: '',
                subdomain: '',
                plan_name: '',
                plan_key: '',
                duration: 3,
                start_date: '',
                end_date: '',
                is_expired: false,
                days_left: ''
            },
            verifyData: {
                id: null,
                business_name: '',
                subdomain: '',
                plan_name: '',
                plan_key: '',
                price_formatted: '',
                notes: '',
                proof_url: '',
                created_at: ''
            },
            rejectData: {
                id: null,
                business_name: '',
                plan_name: ''
            },

            openRenew(sub) {
                this.currentSub = { ...sub };
                this.isSubmitting = false;
                this.showRenewModal = true;
            },

            closeRenew() {
                this.showRenewModal = false;
            },

            openVerify(data) {
                this.verifyData = { ...data };
                this.isSubmitting = false;
                this.showVerifyModal = true;
            },

            closeVerify() {
                this.showVerifyModal = false;
            },

            openReject(data) {
                this.rejectData = { ...data };
                this.isSubmitting = false;
                this.showRejectModal = true;
            },

            closeReject() {
                this.showRejectModal = false;
            },

            async openHistory(subId, tenantName) {
                this.historyTenantName = tenantName;
                this.historyLogs = [];
                this.isLoadingHistory = true;
                this.showHistoryModal = true;

                try {
                    const response = await fetch(`{{ url('/owner/subscriptions') }}/${subId}/history`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        this.historyLogs = data.logs || [];
                    }
                } catch (e) {
                    console.error('Gagal memuat riwayat:', e);
                } finally {
                    this.isLoadingHistory = false;
                }
            },

            closeHistory() {
                this.showHistoryModal = false;
            }
        };
    }
</script>
@endsection
