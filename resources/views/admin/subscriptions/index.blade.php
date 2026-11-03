@extends('layouts.admin')

@section('title', 'Langganan & Paket Toko')

@section('content')
<div class="max-w-7xl mx-auto space-y-8" x-data="subscriptionAdminApp()">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1">
                <span>Pengaturan</span>
                <span>/</span>
                <span class="text-orange-600 font-semibold">Langganan & Paket</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Langganan & Paket Toko</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau kuota operasional, perpanjang masa aktif, atau upgrade paket untuk memperluas kapasitas bisnis rental Anda.</p>
        </div>

        {{-- Badge Status --}}
        <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white border border-gray-200/80 shadow-xs shrink-0">
            <div class="w-2.5 h-2.5 rounded-full {{ $isExpired ? 'bg-red-500' : 'bg-emerald-500 ring-4 ring-emerald-50' }}"></div>
            <div class="text-xs">
                <span class="text-gray-400 font-medium">Status: </span>
                <span class="font-extrabold uppercase {{ $isExpired ? 'text-red-700' : 'text-emerald-700' }}">
                    {{ $isExpired ? 'Kadaluarsa' : 'Aktif (' . $daysRemaining . ' Hari Tersisa)' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl flex items-center gap-3.5 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-emerald-900">Berhasil!</p>
                <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl flex items-center gap-3.5 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center shrink-0 text-red-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-red-900">Perhatian</p>
                <p class="text-xs text-red-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Alert Pengajuan Menunggu Konfirmasi --}}
    @if($pendingRequest)
        <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-950 flex items-center gap-2">
                        Pengajuan {{ strtolower($pendingRequest->plan_name) !== $currentPlanKey ? 'Upgrade' : 'Perpanjangan' }} Sedang Diproses
                        <span class="px-2 py-0.5 text-[10px] uppercase font-extrabold rounded-md bg-amber-200 text-amber-900">Menunggu Verifikasi Owner</span>
                    </h3>
                    <p class="text-xs text-amber-800 mt-1">
                        Paket Tujuan: <strong>{{ $pendingRequest->plan_name }}</strong> (Rp {{ number_format($pendingRequest->price, 0, ',', '.') }}). Bukti transfer telah terkirim pada {{ $pendingRequest->created_at->translatedFormat('d M Y, H:i') }}.
                    </p>
                </div>
            </div>
            @if($pendingRequest->payment_proof)
                <a href="{{ storage_url($pendingRequest->payment_proof) }}" target="_blank" class="px-4 py-2 text-xs font-bold bg-white text-amber-800 border border-amber-200 rounded-xl hover:bg-amber-100/50 transition inline-flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Bukti Transfer
                </a>
            @endif
        </div>
    @endif

    {{-- KARTU STATUS PAKET AKTIF & RINGKASAN KUOTA --}}
    <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100">
            <div>
                <span class="px-3 py-1 text-xs font-extrabold uppercase rounded-lg tracking-wider {{ $currentPlanKey === 'professional' ? 'bg-purple-100 text-purple-700' : ($currentPlanKey === 'business' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-700') }}">
                    Paket Toko Saat Ini
                </span>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight mt-2 flex items-center gap-3">
                    {{ $subscription->plan_name ?? 'Starter' }}
                    <span class="text-sm font-semibold text-gray-400">
                        ({{ $currentPackage['duration_months'] ?? 3 }} Bulan / Periode)
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Masa Aktif: <strong class="text-gray-700">{{ $subscription?->start_date ? $subscription->start_date->translatedFormat('d F Y') : '-' }}</strong> s/d <strong class="text-gray-700">{{ $subscription?->end_date ? $subscription->end_date->translatedFormat('d F Y') : '-' }}</strong>
                </p>
            </div>

            {{-- CTA Tombol Aksi --}}
            <div class="flex flex-wrap items-center gap-3">
                @if(Auth::user()?->isPrimaryAdmin())
                    @if($currentPlanKey !== 'starter')
                    <button type="button"
                            @click="openRenewModal()"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-white text-gray-700 hover:bg-stone-50 border border-gray-200 shadow-xs transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Perpanjang Paket Ini
                    </button>
                    @endif

                    @if($currentPlanKey !== 'professional')
                    <button type="button"
                            @click="openUpgradeModal()"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-orange-500 hover:bg-orange-600 text-white shadow-sm shadow-orange-500/30 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Upgrade Paket
                    </button>
                    @endif
                @else
                    <span class="text-xs text-gray-400 italic">Hanya Admin Utama yang dapat mengajukan perpanjangan/upgrade.</span>
                @endif
            </div>
        </div>

        {{-- 4 Progress Bar Kuota --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-6">
            {{-- Kuota Produk --}}
            @php
                $maxProd = $currentPackage['limits']['products'] ?? null;
                $pctProd = $maxProd ? min(100, round(($usedProducts / $maxProd) * 100)) : 0;
            @endphp
            <div class="p-4 rounded-2xl bg-stone-50/60 border border-stone-200/60">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-xs font-bold text-gray-700">Katalog Produk</span>
                    <span class="text-xs font-extrabold {{ $pctProd >= 90 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedProducts }} / {{ $maxProd ?: '∞' }}
                    </span>
                </div>
                <div class="w-full bg-stone-200/70 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $pctProd >= 90 ? 'bg-red-500' : 'bg-orange-500' }}" style="width: {{ $maxProd ? $pctProd : 100 }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2">Maks. varian perlengkapan sewa</p>
            </div>

            {{-- Kuota Unit Fisik --}}
            @php
                $maxUnit = $currentPackage['limits']['units'] ?? null;
                $pctUnit = $maxUnit ? min(100, round(($usedUnits / $maxUnit) * 100)) : 0;
            @endphp
            <div class="p-4 rounded-2xl bg-stone-50/60 border border-stone-200/60">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-xs font-bold text-gray-700">Unit Fisik Peralatan</span>
                    <span class="text-xs font-extrabold {{ $pctUnit >= 90 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedUnits }} / {{ $maxUnit ?: '∞' }}
                    </span>
                </div>
                <div class="w-full bg-stone-200/70 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $pctUnit >= 90 ? 'bg-red-500' : 'bg-orange-500' }}" style="width: {{ $maxUnit ? $pctUnit : 100 }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2">Total aset fisik yang dapat disewakan</p>
            </div>

            {{-- Kuota Kategori --}}
            @php
                $maxCat = $currentPackage['limits']['categories'] ?? null;
                $pctCat = $maxCat ? min(100, round(($usedCategories / $maxCat) * 100)) : 0;
            @endphp
            <div class="p-4 rounded-2xl bg-stone-50/60 border border-stone-200/60">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-xs font-bold text-gray-700">Kategori Produk</span>
                    <span class="text-xs font-extrabold {{ $pctCat >= 90 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedCategories }} / {{ $maxCat ?: '∞' }}
                    </span>
                </div>
                <div class="w-full bg-stone-200/70 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $pctCat >= 90 ? 'bg-red-500' : 'bg-orange-500' }}" style="width: {{ $maxCat ? $pctCat : 100 }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2">Klasifikasi perlengkapan di katalog</p>
            </div>

            {{-- Kuota Staf Admin --}}
            @php
                $maxAdm = $currentPackage['limits']['admins'] ?? 1;
                $pctAdm = min(100, round(($usedAdmins / $maxAdm) * 100));
            @endphp
            <div class="p-4 rounded-2xl bg-stone-50/60 border border-stone-200/60">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-xs font-bold text-gray-700">Akun Admin Staf</span>
                    <span class="text-xs font-extrabold {{ $pctAdm >= 100 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $usedAdmins }} / {{ $maxAdm }}
                    </span>
                </div>
                <div class="w-full bg-stone-200/70 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $pctAdm >= 100 ? 'bg-red-500' : 'bg-orange-500' }}" style="width: {{ $pctAdm }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2">Jumlah operator operasional toko</p>
            </div>
        </div>
    </div>

    {{-- PILIHAN SEMUA PAKET RENTALBASE --}}
    <div>
        <div class="mb-4">
            <h2 class="text-lg font-black text-gray-900 tracking-tight">Perbandingan Paket RentalBase</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pilih paket yang paling sesuai dengan volume dan skala bisnis rental perlengkapan bayi Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($packagesConfig as $key => $pkg)
                @continue($key === 'reserved_subdomains' || !is_array($pkg) || !isset($pkg['name']))
                @php
                    $isCurrent = $currentPlanKey === $key;
                @endphp
                <div class="bg-white rounded-3xl border {{ $isCurrent ? 'border-orange-500 ring-2 ring-orange-500/20 shadow-md' : 'border-gray-200/80 shadow-xs' }} p-6 flex flex-col justify-between relative overflow-hidden">
                    @if($isCurrent)
                        <div class="absolute top-0 right-0 bg-orange-500 text-white text-[10px] uppercase font-black px-4 py-1 rounded-bl-xl tracking-wider">
                            Paket Aktif
                        </div>
                    @endif

                    <div>
                        <div class="mb-4">
                            <h3 class="text-xl font-black text-gray-900">{{ $pkg['name'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1 min-h-[32px]">{{ $pkg['description'] }}</p>
                        </div>

                        <div class="mb-6 flex items-baseline gap-1">
                            <span class="text-3xl font-black text-gray-900">
                                {{ $pkg['price'] > 0 ? 'Rp ' . number_format($pkg['price'], 0, ',', '.') : 'Gratis' }}
                            </span>
                            <span class="text-xs text-gray-500 font-medium">/ {{ $pkg['duration_months'] }} Bulan</span>
                        </div>

                        {{-- Fitur / Manfaat --}}
                        <ul class="space-y-3 text-xs text-gray-600 mb-6 border-t border-gray-100 pt-5">
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Maks. <strong>{{ $pkg['limits']['products'] ? $pkg['limits']['products'] . ' Produk' : 'Unlimited Produk' }}</strong></span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Maks. <strong>{{ $pkg['limits']['units'] ? $pkg['limits']['units'] . ' Unit Fisik' : 'Unlimited Unit Fisik' }}</strong></span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Maks. <strong>{{ $pkg['limits']['categories'] ? $pkg['limits']['categories'] . ' Kategori' : 'Unlimited Kategori' }}</strong></span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Maks. <strong>{{ $pkg['limits']['admins'] }} Staf Admin</strong></span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                @if($pkg['features']['custom_theme'] ?? false)
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-gray-900 font-semibold">Kustomisasi Tema Toko Aktif</span>
                                @else
                                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span class="text-gray-400">Tema Standar Toko</span>
                                @endif
                            </li>
                        </ul>
                    </div>

                    {{-- Tombol Aksi per Card --}}
                    <div>
                        @if($isCurrent)
                            @if($key !== 'starter' && Auth::user()?->isPrimaryAdmin())
                                <button type="button" @click="openRenewModal()" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-stone-100 hover:bg-stone-200 text-gray-700 transition">
                                    Perpanjang Masa Aktif
                                </button>
                            @else
                                <div class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-center bg-gray-50 text-gray-400 border border-gray-100">
                                    Sedang Digunakan
                                </div>
                            @endif
                        @else
                            @if(Auth::user()?->isPrimaryAdmin())
                                <button type="button" @click="openUpgradeSpecificModal('{{ $key }}', '{{ $pkg['name'] }}', {{ $pkg['price'] }}, {{ $pkg['duration_months'] }})" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-orange-500 hover:bg-orange-600 text-white shadow-xs transition">
                                    Upgrade ke {{ $pkg['name'] }}
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- INFORMASI REKENING RESMI OWNER PLATFORM --}}
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 sm:p-8 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-orange-400 uppercase tracking-widest mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Pusat Pembayaran Resmi RentalBase Platform
            </div>
            <h3 class="text-xl font-extrabold tracking-tight">Metode Transfer Pembayaran Langganan</h3>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Transfer biaya perpanjangan atau upgrade ke nomor rekening resmi platform berikut. Simpan bukti transfer untuk diunggah pada formulir pengajuan.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 shrink-0">
            <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Bank Central Asia (BCA)</p>
                <p class="text-base font-black tracking-wider text-white mt-0.5">829-019-2811</p>
                <p class="text-[11px] text-slate-300">a.n. PT RentalBase Teknologi Indonesia</p>
            </div>
            <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Bank Mandiri</p>
                <p class="text-base font-black tracking-wider text-white mt-0.5">144-00-1928-112</p>
                <p class="text-[11px] text-slate-300">a.n. RentalBase Platform</p>
            </div>
        </div>
    </div>

    {{-- TABEL RIWAYAT SUBSCRIPTION --}}
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-gray-900 tracking-tight">Riwayat Langganan Toko</h3>
                <p class="text-xs text-gray-400 mt-0.5">Catatan historis aktivasi, perpanjangan, dan verifikasi paket.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50/70 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-6">Tanggal Pengajuan</th>
                        <th class="py-3 px-6">Paket</th>
                        <th class="py-3 px-6">Nominal Biaya</th>
                        <th class="py-3 px-6">Periode Berlaku</th>
                        <th class="py-3 px-6">Bukti Transfer</th>
                        <th class="py-3 px-6">Status Pembayaran</th>
                        <th class="py-3 px-6">Catatan / Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                    @forelse($history as $item)
                        <tr class="hover:bg-stone-50/40 transition">
                            <td class="py-4 px-6 text-gray-500">
                                {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-6 font-bold text-gray-900">
                                {{ $item->plan_name }}
                            </td>
                            <td class="py-4 px-6 font-extrabold text-gray-900">
                                {{ $item->price > 0 ? 'Rp ' . number_format($item->price, 0, ',', '.') : 'Gratis' }}
                            </td>
                            <td class="py-4 px-6 text-gray-600">
                                {{ $item->start_date ? $item->start_date->translatedFormat('d/m/Y') : '-' }} s/d {{ $item->end_date ? $item->end_date->translatedFormat('d/m/Y') : '-' }}
                            </td>
                            <td class="py-4 px-6">
                                @if($item->payment_proof)
                                    <a href="{{ storage_url($item->payment_proof) }}" target="_blank" class="text-orange-600 hover:underline font-bold inline-flex items-center gap-1">
                                        Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">Tanpa File</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($item->payment_status === 'diverifikasi')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Diverifikasi
                                    </span>
                                @elseif($item->payment_status === 'menunggu')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Cek
                                    </span>
                                @elseif($item->payment_status === 'ditolak')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-red-50 text-red-700 border border-red-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700">
                                        {{ $item->payment_status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 max-w-xs truncate text-gray-500">
                                @if($item->rejection_reason)
                                    <span class="text-red-600 font-semibold" title="{{ $item->rejection_reason }}">
                                        Ditolak: {{ $item->rejection_reason }}
                                    </span>
                                @else
                                    {{ $item->notes ?: '-' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">
                                Belum ada riwayat langganan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL PERPANJANG LANGGANAN --}}
    <div x-show="renewModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="renewModalOpen = false">
        
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100" @click.outside="renewModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight">Perpanjang Paket {{ $subscription->plan_name ?? 'Business' }}</h3>
                        <p class="text-xs text-gray-500">Masa aktif bertambah {{ $currentPackage['duration_months'] ?? 6 }} bulan ke depan</p>
                    </div>
                </div>
                <button type="button" @click="renewModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.subscriptions.renew') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="p-4 rounded-2xl bg-orange-50/60 border border-orange-200/60 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Paket:</span>
                        <span class="font-bold text-gray-900">{{ $subscription->plan_name ?? 'Business' }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Durasi Tambahan:</span>
                        <span class="font-bold text-gray-900">{{ $currentPackage['duration_months'] ?? 6 }} Bulan</span>
                    </div>
                    <div class="flex justify-between text-xs pt-2 border-t border-orange-200/60">
                        <span class="font-bold text-gray-700">Total Transfer:</span>
                        <span class="font-black text-orange-600 text-sm">Rp {{ number_format($currentPackage['price'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Unggah Bukti Transfer <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="payment_proof" required accept="image/jpeg,image/png,image/jpg,application/pdf" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 border border-gray-200 rounded-xl p-2 bg-stone-50">
                    <p class="text-[10px] text-gray-400 mt-1">Format: JPG, PNG, atau PDF (Maks. 5MB)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Catatan Pengajuan (Opsional)
                    </label>
                    <textarea name="notes" rows="2" placeholder="Nomor rekening pengirim, nama pemilik rekening, atau keterangan lainnya..." class="w-full px-3.5 py-2.5 text-xs bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button type="button" @click="renewModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-stone-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-orange-500 hover:bg-orange-600 text-white shadow-xs transition">
                        Kirim Pengajuan Perpanjangan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL UPGRADE PAKET --}}
    <div x-show="upgradeModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="upgradeModalOpen = false">
        
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100" @click.outside="upgradeModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight">Formulir Upgrade Paket Toko</h3>
                        <p class="text-xs text-gray-500">Masa aktif paket baru dihitung penuh mulai hari disetujui</p>
                    </div>
                </div>
                <button type="button" @click="upgradeModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.subscriptions.upgrade') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Pilih Paket Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select name="plan_name" x-model="selectedUpgradePlan" required class="w-full px-3.5 py-2.5 text-xs bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 font-bold">
                        @if($currentPlanKey === 'starter')
                            <option value="business">Business (Rp 99.000 / 6 Bulan — 50 Produk, 100 Unit, 3 Admin, Kustom Tema)</option>
                            <option value="professional">Professional (Rp 249.000 / 12 Bulan — Unlimited Produk, 10 Admin, Tema)</option>
                        @elseif($currentPlanKey === 'business')
                            <option value="professional">Professional (Rp 249.000 / 12 Bulan — Unlimited Produk, 10 Admin, Tema)</option>
                        @endif
                    </select>
                </div>

                <div class="p-4 rounded-2xl bg-orange-50/60 border border-orange-200/60 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Total Nominal Transfer:</span>
                        <span class="font-black text-orange-600 text-sm" x-text="selectedUpgradePrice"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Masa Aktif Baru:</span>
                        <span class="font-bold text-gray-900" x-text="selectedUpgradeDuration"></span>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Sesuai SOP, masa aktif dihitung penuh sejak tanggal pengajuan upgrade disetujui oleh Owner.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Unggah Bukti Transfer <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="payment_proof" required accept="image/jpeg,image/png,image/jpg,application/pdf" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 border border-gray-200 rounded-xl p-2 bg-stone-50">
                    <p class="text-[10px] text-gray-400 mt-1">Format: JPG, PNG, atau PDF (Maks. 5MB)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Catatan Pengajuan (Opsional)
                    </label>
                    <textarea name="notes" rows="2" placeholder="Nama pemilik rekening pengirim, alasan upgrade, dll..." class="w-full px-3.5 py-2.5 text-xs bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button type="button" @click="upgradeModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-stone-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-orange-500 hover:bg-orange-600 text-white shadow-xs transition">
                        Kirim Pengajuan Upgrade
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function subscriptionAdminApp() {
        return {
            renewModalOpen: false,
            upgradeModalOpen: false,
            selectedUpgradePlan: @json($currentPlanKey === 'starter' ? 'business' : 'professional'),

            get selectedUpgradePrice() {
                if (this.selectedUpgradePlan === 'business') {
                    return 'Rp 99.000';
                }
                return 'Rp 249.000';
            },

            get selectedUpgradeDuration() {
                if (this.selectedUpgradePlan === 'business') {
                    return '6 Bulan Penuh';
                }
                return '12 Bulan Penuh';
            },

            openRenewModal() {
                this.renewModalOpen = true;
            },

            openUpgradeModal() {
                this.upgradeModalOpen = true;
            },

            openUpgradeSpecificModal(planKey, planName, price, duration) {
                this.selectedUpgradePlan = planKey;
                this.upgradeModalOpen = true;
            }
        };
    }
</script>
@endsection
