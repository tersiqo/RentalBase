@extends('layouts.admin')

@section('page-title', 'Unit Fisik')

@section('content')
@php
    $planLabel = $subscription->plan_name ?? 'Starter';
    $isQuotaFull = ($limit !== null && $used >= $limit);
    $percentage = $limit ? min(100, round(($used / $limit) * 100)) : 0;
@endphp

<div x-data="unitManagement()" class="space-y-6 pb-12 font-sans">

    {{-- HEADER PAGE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-orange-600 mb-1">
                <span class="w-2 h-2 rounded-full bg-orange-500 inline-block animate-pulse"></span>
                <span>Manajemen Inventaris & Aset Fisik</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Unit Fisik Perlengkapan</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola stok fisik individual per produk rental, catat nomor seri pabrik, kode aset unik, dan pantau status ketersediaan unit.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($isQuotaFull)
                <button type="button"
                        @click="showQuotaAlert = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-slate-200 text-slate-500 cursor-not-allowed shadow-xs"
                        title="Batas kuota unit fisik paket telah penuh">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>+ Tambah Unit (Penuh)</span>
                </button>
            @else
                <button type="button"
                        @click="openAddModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-orange-500 text-white hover:bg-orange-600 active:scale-95 transition-all shadow-md shadow-orange-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Unit Fisik</span>
                </button>
            @endif
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-900 flex items-start justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Berhasil!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div x-data="{ show: true }" x-show="show" class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-900 flex items-start justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Terjadi Kesalahan</h4>
                    <p class="text-xs text-red-700 mt-0.5">{{ session('error') ?? $errors->first() }}</p>
                </div>
            </div>
            <button @click="show = false" class="text-red-400 hover:text-red-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    {{-- PANEL KUOTA & METRIK STATUS --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Kuota Paket Tenant --}}
        <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kapasitas Unit Paket</span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $isQuotaFull ? 'bg-red-100 text-red-700' : ($percentage >= 80 ? 'bg-amber-100 text-amber-700' : 'bg-orange-100 text-orange-700') }}">
                        Paket {{ ucfirst($planLabel) }}
                    </span>
                </div>
                <div class="flex items-baseline justify-between mb-2">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-extrabold text-slate-900">{{ $used }}</span>
                        <span class="text-sm font-semibold text-slate-400">/ {{ $limit ? $limit . ' unit' : 'Tanpa Batas' }}</span>
                    </div>
                    @if($limit)
                        <span class="text-xs font-bold text-slate-500">{{ $percentage }}% terpakai</span>
                    @endif
                </div>

                @if($limit)
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $isQuotaFull ? 'bg-red-500' : ($percentage >= 80 ? 'bg-amber-500' : 'bg-orange-500') }}"
                             style="width: {{ $percentage }}%"></div>
                    </div>
                @else
                    <div class="w-full bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-1.5 text-xs font-semibold text-emerald-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kuota unit tak terbatas aktif (Professional)</span>
                    </div>
                @endif
            </div>

            <p class="text-xs text-slate-400 mt-4">
                @if($isQuotaFull)
                    <span class="text-red-600 font-semibold">Batas kuota unit telah tercapai.</span> Hubungi Super Admin/Owner untuk melakukan upgrade paket usaha.
                @elseif($limit && $percentage >= 80)
                    <span class="text-amber-600 font-semibold">Kapasitas hampir penuh.</span> Anda dapat menambah {{ $limit - $used }} unit lagi.
                @else
                    Setiap unit mewakili 1 aset fisik nyata yang dapat disewa oleh pelanggan secara bersamaan.
                @endif
            </p>
        </div>

        {{-- 4 Kartu Metrik Operasional Unit --}}
        <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Tersedia --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Tersedia</span>
                    <span class="text-2xl font-extrabold text-emerald-600">{{ $metrics['tersedia'] }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Siap disewakan</span>
                </div>
            </div>

            {{-- Sedang Disewa --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Disewa</span>
                    <span class="text-2xl font-extrabold text-blue-600">{{ $metrics['disewa'] }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Sedang di customer</span>
                </div>
            </div>

            {{-- Perawatan / Maintenance --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Perawatan</span>
                    <span class="text-2xl font-extrabold text-amber-600">{{ $metrics['maintenance'] }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Servis / Cuci</span>
                </div>
            </div>

            {{-- Tidak Tersedia / Rusak --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Nonaktif</span>
                    <span class="text-2xl font-extrabold text-rose-600">{{ $metrics['tidak_tersedia'] }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Rusak / Tidak aktif</span>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR (INSTANT REAL-TIME FILTER — SAMA DENGAN HALAMAN PRODUK) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            {{-- Pencarian Realtime --}}
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Cari kode unit, no seri, atau nama produk..."
                       class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
            </div>

            {{-- Filter Produk Induk --}}
            <div class="sm:col-span-3">
                <select x-model="selectedProduct"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                    <option value="">Semua Produk Induk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status Operasional --}}
            <div class="sm:col-span-2">
                <select x-model="selectedStatus"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                    <option value="">Semua Status</option>
                    <option value="tersedia">Tersedia</option>
                    <option value="disewa">Sedang Disewa</option>
                    <option value="maintenance">Perawatan</option>
                    <option value="tidak_tersedia">Tidak Tersedia</option>
                </select>
            </div>

            {{-- Filter Kondisi Fisik --}}
            <div class="sm:col-span-2">
                <select x-model="selectedCondition"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                    <option value="">Semua Kondisi</option>
                    <option value="baik">Kondisi Baik</option>
                    <option value="rusak_ringan">Rusak Ringan</option>
                    <option value="rusak_berat">Rusak Berat</option>
                </select>
            </div>

            {{-- Aksi Reset & Indikator Hasil --}}
            <div class="sm:col-span-1 flex items-center gap-2">
                <template x-if="hasActiveFilters">
                    <button type="button"
                            @click="resetFilters()"
                            class="w-full py-2 px-2 text-xs font-bold rounded-xl border border-orange-200 bg-orange-50 text-orange-600 hover:bg-orange-100 transition flex items-center justify-center gap-1 shadow-xs"
                            title="Reset Filter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="hidden sm:inline">Reset</span>
                    </button>
                </template>
                <template x-if="!hasActiveFilters">
                    <div class="w-full py-2 px-1 text-[11px] font-semibold text-slate-400 text-center bg-slate-50 border border-dashed border-slate-200 rounded-xl truncate">
                        <span x-text="unitsData.length + ' Unit'"></span>
                    </div>
                </template>
            </div>

        </div>
    </div>

    {{-- TABEL MASTER UNIT FISIK --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($units->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Kode Unit / Aset</th>
                            <th class="py-3.5 px-4">Produk Induk</th>
                            <th class="py-3.5 px-4">Nomor Seri Pabrik</th>
                            <th class="py-3.5 px-4">Kondisi Fisik</th>
                            <th class="py-3.5 px-4">Status Operasional</th>
                            <th class="py-3.5 px-4">Pesanan / Status Sewa</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @foreach($units as $unit)
                            @php
                                $st = strtolower((string)$unit->status);
                                $cond = strtolower((string)$unit->condition);
                                $isRented = in_array($st, ['disewa', 'rented']) || $unit->isActivelyRented();
                                $latestOrderItem = $unit->orderItemUnits->first();
                                $activeOrder = $latestOrderItem?->orderItem?->order;
                            @endphp
                            <tr x-show="matchesFilter({{ $unit->id }})" class="hover:bg-slate-50/50 transition">
                                {{-- Kode Unit / Aset --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        </div>
                                        <div>
                                            <span class="font-mono font-bold text-slate-900 block tracking-tight">{{ $unit->unit_code }}</span>
                                            <span class="text-[10px] text-slate-400">ID: #{{ $unit->id }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Produk Induk --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200/60">
                                            @if($unit->product?->main_image)
                                                <img src="{{ Storage::url($unit->product->main_image) }}"
                                                     alt="{{ $unit->product->name }}"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=100&auto=format&fit=crop&q=60';">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 line-clamp-1">{{ $unit->product?->name ?? 'Produk Dihapus' }}</p>
                                            <p class="text-xs text-orange-600 font-semibold mt-0.5">
                                                Rp {{ number_format($unit->product?->rental_price_per_day ?? 0, 0, ',', '.') }}/hari
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Nomor Seri Pabrik --}}
                                <td class="py-3.5 px-4">
                                    @if($unit->serial_number)
                                        <span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/50">
                                            {{ $unit->serial_number }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Tidak ada</span>
                                    @endif
                                </td>

                                {{-- Kondisi Fisik --}}
                                <td class="py-3.5 px-4">
                                    @if($cond === 'baik')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Baik
                                        </span>
                                    @elseif($cond === 'rusak_ringan')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Rusak Ringan
                                        </span>
                                    @elseif($cond === 'rusak_berat')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Rusak Berat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ ucfirst($unit->condition) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Status Operasional --}}
                                <td class="py-3.5 px-4">
                                    @if(in_array($st, ['tersedia', 'available']))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100/70 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Tersedia
                                        </span>
                                    @elseif(in_array($st, ['disewa', 'rented']))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100/70 text-blue-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                            Sedang Disewa
                                        </span>
                                    @elseif($st === 'maintenance')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100/70 text-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            Perawatan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                            Tidak Tersedia
                                        </span>
                                    @endif
                                </td>

                                {{-- Pesanan / Status Sewa --}}
                                <td class="py-3.5 px-4">
                                    @if($activeOrder)
                                        <div>
                                            <a href="{{ route('admin.orders.show', $activeOrder->id) }}"
                                               class="font-semibold text-orange-600 hover:text-orange-700 hover:underline flex items-center gap-1">
                                                <span>#{{ $activeOrder->order_number }}</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                            <span class="text-[11px] text-slate-400 block mt-0.5">
                                                Status: <span class="font-medium text-slate-600">{{ str_replace('_', ' ', ucfirst($activeOrder->status)) }}</span>
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum pernah disewa</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        {{-- Edit Button --}}
                                        <button type="button"
                                                @click="openEditModal({{ json_encode([
                                                    'id'            => $unit->id,
                                                    'product_id'    => $unit->product_id,
                                                    'product_name'  => $unit->product?->name ?? 'Produk',
                                                    'unit_code'     => $unit->unit_code,
                                                    'serial_number' => $unit->serial_number ?? '',
                                                    'condition'     => $unit->condition,
                                                    'status'        => $unit->status,
                                                    'is_rented'     => $isRented,
                                                ]) }})"
                                                class="w-8 h-8 rounded-xl border border-slate-200 text-slate-600 hover:text-orange-600 hover:border-orange-200 hover:bg-orange-50 flex items-center justify-center transition-all shadow-2xs"
                                                title="Ubah Kondisi & Status Unit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        @if($isRented)
                                            <button type="button"
                                                    class="w-8 h-8 rounded-xl border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed flex items-center justify-center shadow-2xs"
                                                    title="Unit fisik sedang disewa, tidak dapat dihapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @else
                                            <button type="button"
                                                    @click="openDeleteModal('{{ $unit->id }}', '{{ $unit->unit_code }}', '{{ addslashes($unit->product?->name ?? 'Produk') }}')"
                                                    class="w-8 h-8 rounded-xl border border-slate-200 text-slate-600 hover:text-red-600 hover:border-red-200 hover:bg-red-50 flex items-center justify-center transition-all shadow-2xs"
                                                    title="Hapus Unit Fisik">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Pesan Kosong Saat Filter Tidak Menemukan Hasil --}}
                        <tr x-show="filteredCount === 0" x-cloak>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 mx-auto flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Tidak ada unit yang cocok</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Tidak ditemukan unit fisik yang sesuai dengan filter pencarian aktif.
                                    </p>
                                    <div class="mt-3">
                                        <button type="button"
                                                @click="resetFilters()"
                                                class="px-3.5 py-1.5 rounded-xl bg-orange-50 text-orange-600 border border-orange-200 font-bold text-xs hover:bg-orange-100 transition">
                                            Reset Filter
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 px-4 text-center">
                <div class="max-w-sm mx-auto text-center">
                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum ada unit fisik terdaftar</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Belum ada unit fisik perlengkapan yang didaftarkan pada toko Anda. Mulai daftarkan aset unit pertama Anda.
                    </p>
                    @if(! $isQuotaFull)
                        <div class="mt-4">
                            <button type="button"
                                    @click="openAddModal()"
                                    class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs transition-colors shadow-xs">
                                + Tambah Unit Sekarang
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL TAMBAH UNIT FISIK (CRISP WHITE, DI ATAS BACKDROP)                   --}}
    {{-- ========================================================================= --}}
    <div x-show="addModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="addModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                 @click="addModalOpen = false"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="addModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop
                 class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">

                <form method="POST" action="{{ route('admin.units.store') }}" class="space-y-0">
                    @csrf
                    <div class="p-6 sm:p-8 space-y-4 bg-white">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900" id="modal-title">Tambah Unit Fisik Baru</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Daftarkan stok aset nyata ke inventaris toko</p>
                                </div>
                            </div>
                            <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Pilih Produk Induk --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Produk Induk <span class="text-red-500">*</span>
                            </label>
                            <select name="product_id"
                                    x-model="form.product_id"
                                    required
                                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                                <option value="" disabled selected>-- Pilih Produk Perlengkapan --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Rp {{ number_format($p->rental_price_per_day, 0, ',', '.') }}/hari)</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Satu produk induk dapat memiliki beberapa unit fisik individual.</p>
                        </div>

                        {{-- Kode Unit / Aset --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kode Unit / Aset <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="unit_code"
                                   x-model="form.unit_code"
                                   @input="form.unit_code = $event.target.value.toUpperCase()"
                                   required
                                   placeholder="Contoh: BBR-YOYO-03 atau UNIT-STROLLER-01"
                                   class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 font-mono uppercase rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800 placeholder:text-slate-400 placeholder:normal-case">
                            <p class="text-[11px] text-slate-400 mt-1">Kode barcode/aset internal toko. Wajib unik per toko.</p>
                        </div>

                        {{-- Nomor Seri Pabrik --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Seri Pabrik (Opsional)
                            </label>
                            <input type="text"
                                   name="serial_number"
                                   x-model="form.serial_number"
                                   placeholder="Contoh: SN-YOYO-2024-9988"
                                   class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 font-mono rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800 placeholder:text-slate-400">
                            <p class="text-[11px] text-slate-400 mt-1">Nomor seri yang tertera pada badan fisik alat dari pabrikan.</p>
                        </div>

                        {{-- Grid Kondisi & Status Awal --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kondisi Fisik Awal <span class="text-red-500">*</span>
                                </label>
                                <select name="condition"
                                        x-model="form.condition"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                                    <option value="baik">Kondisi Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Status Awal <span class="text-red-500">*</span>
                                </label>
                                <select name="status"
                                        x-model="form.status"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                                    <option value="tersedia">Tersedia (Siap Sewa)</option>
                                    <option value="maintenance">Perawatan (Maintenance)</option>
                                    <option value="tidak_tersedia">Tidak Tersedia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="addModalOpen = false"
                                class="px-4 py-2.5 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs bg-orange-500 text-white hover:bg-orange-600 transition shadow-md shadow-orange-500/20">
                            Simpan Unit Fisik
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL EDIT UNIT FISIK                                                     --}}
    {{-- ========================================================================= --}}
    <div x-show="editModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                 @click="editModalOpen = false"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop
                 class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">

                <form method="POST" :action="editActionUrl" class="space-y-0">
                    @csrf
                    @method('PUT')
                    <div class="p-6 sm:p-8 space-y-4 bg-white">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900">Ubah Data Unit Fisik</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Perbarui kondisi fisik dan status operasional unit</p>
                                </div>
                            </div>
                            <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Alert jika unit sedang disewa --}}
                        <template x-if="editData.is_rented">
                            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-3.5 text-blue-900 text-xs flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <span class="font-bold block">Proteksi Transaksi Sewa:</span>
                                    <span>Unit ini sedang disewa dalam pesanan aktif. Status tidak dapat diubah menjadi "Tersedia" sebelum proses retur/pembatalan order selesai dilakukan.</span>
                                </div>
                            </div>
                        </template>

                        {{-- Pilih Produk Induk --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Produk Induk <span class="text-red-500">*</span>
                            </label>
                            <select name="product_id"
                                    x-model="editData.product_id"
                                    required
                                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kode Unit / Aset --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kode Unit / Aset <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="unit_code"
                                   x-model="editData.unit_code"
                                   @input="editData.unit_code = $event.target.value.toUpperCase()"
                                   required
                                   class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 font-mono uppercase rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                        </div>

                        {{-- Nomor Seri Pabrik --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Seri Pabrik (Opsional)
                            </label>
                            <input type="text"
                                   name="serial_number"
                                   x-model="editData.serial_number"
                                   class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 font-mono rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                        </div>

                        {{-- Grid Kondisi & Status --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kondisi Fisik <span class="text-red-500">*</span>
                                </label>
                                <select name="condition"
                                        x-model="editData.condition"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                                    <option value="baik">Kondisi Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Status Operasional <span class="text-red-500">*</span>
                                </label>
                                <select name="status"
                                        x-model="editData.status"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
                                    <option value="tersedia" :disabled="editData.is_rented">Tersedia (Siap Sewa)</option>
                                    <option value="disewa">Sedang Disewa</option>
                                    <option value="maintenance">Perawatan (Maintenance)</option>
                                    <option value="tidak_tersedia">Tidak Tersedia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="editModalOpen = false"
                                class="px-4 py-2.5 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs bg-orange-500 text-white hover:bg-orange-600 transition shadow-md shadow-orange-500/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL KONFIRMASI HAPUS UNIT                                               --}}
    {{-- ========================================================================= --}}
    <div x-show="deleteModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                 @click="deleteModalOpen = false"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop
                 class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">

                <form method="POST" :action="deleteActionUrl">
                    @csrf
                    @method('DELETE')
                    <div class="p-6 sm:p-8 bg-white">
                        <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>

                        <div class="text-center">
                            <h3 class="text-base font-extrabold text-slate-900">Hapus Unit Fisik?</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Anda akan menghapus unit fisik <span class="font-mono font-bold text-slate-800" x-text="deleteUnitCode"></span> untuk produk <span class="font-bold text-slate-800" x-text="deleteProductName"></span>.
                            </p>
                            <p class="text-[11px] text-red-600 bg-red-50 border border-red-100 rounded-xl p-2.5 mt-3 text-left">
                                <strong>Perhatian:</strong> Aksi ini akan menghapus data aset unit dari inventaris secara permanen. Pastikan unit tidak sedang digunakan dalam transaksi sewa.
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="deleteModalOpen = false"
                                class="px-4 py-2.5 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs bg-red-600 text-white hover:bg-red-700 transition shadow-md shadow-red-600/20">
                            Ya, Hapus Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL ALERT KUOTA PENUH                                                   --}}
    {{-- ========================================================================= --}}
    <div x-show="showQuotaAlert"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showQuotaAlert"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                 @click="showQuotaAlert = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showQuotaAlert"
                 @click.stop
                 class="relative z-10 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Batas Kuota Paket Tercapai</h3>
                <p class="text-xs text-slate-500 mt-2">
                    Toko Anda saat ini menggunakan paket <strong>{{ ucfirst($planLabel) }}</strong> dengan batas maksimal <strong>{{ $limit }} unit fisik</strong> (saat ini terpakai: {{ $used }} unit).
                </p>
                <p class="text-xs text-slate-500 mt-2">
                    Untuk menambah lebih banyak unit peralatan, silakan hubungi tim Owner RentalBase untuk melakukan upgrade ke paket yang lebih tinggi.
                </p>
                <div class="mt-5">
                    <button type="button"
                            @click="showQuotaAlert = false"
                            class="w-full py-2.5 rounded-xl font-bold text-xs bg-slate-900 text-white hover:bg-slate-800 transition">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function unitManagement() {
        return {
            searchQuery: {!! json_encode((string) request('search', '')) !!},
            selectedProduct: {!! json_encode((string) request('product_id', '')) !!},
            selectedStatus: {!! json_encode((string) request('status', '')) !!},
            selectedCondition: {!! json_encode((string) request('condition', '')) !!},

            unitsData: {!! json_encode($units->map(fn($u) => [
                'id' => (int) $u->id,
                'product_id' => (string) ($u->product_id ?? ''),
                'product_name' => (string) ($u->product?->name ?? ''),
                'unit_code' => (string) $u->unit_code,
                'serial_number' => (string) ($u->serial_number ?? ''),
                'status' => (string) $u->status,
                'condition' => (string) $u->condition,
                'is_rented' => in_array(strtolower((string)$u->status), ['disewa', 'rented']) || $u->isActivelyRented(),
            ])->values()) !!},

            matchesFilter(unitId) {
                const u = this.unitsData.find(item => item.id === unitId);
                if (!u) return true;

                if (this.selectedProduct && u.product_id !== String(this.selectedProduct)) {
                    return false;
                }

                if (this.selectedStatus) {
                    const s = (u.status || '').toLowerCase();
                    const sel = this.selectedStatus.toLowerCase();
                    if (sel === 'tersedia' && !['tersedia', 'available'].includes(s)) return false;
                    if (sel === 'disewa' && !['disewa', 'rented'].includes(s)) return false;
                    if (sel === 'maintenance' && s !== 'maintenance') return false;
                    if (sel === 'tidak_tersedia' && !['tidak_tersedia', 'rusak', 'lost', 'inactive'].includes(s)) return false;
                }

                if (this.selectedCondition && u.condition !== this.selectedCondition) {
                    return false;
                }

                if (this.searchQuery.trim()) {
                    const q = this.searchQuery.trim().toLowerCase();
                    const code = (u.unit_code || '').toLowerCase();
                    const sn = (u.serial_number || '').toLowerCase();
                    const pName = (u.product_name || '').toLowerCase();
                    if (!code.includes(q) && !sn.includes(q) && !pName.includes(q)) {
                        return false;
                    }
                }

                return true;
            },

            get filteredCount() {
                return this.unitsData.filter(u => this.matchesFilter(u.id)).length;
            },

            get hasActiveFilters() {
                return Boolean(this.searchQuery.trim() || this.selectedProduct || this.selectedStatus || this.selectedCondition);
            },

            resetFilters() {
                this.searchQuery = '';
                this.selectedProduct = '';
                this.selectedStatus = '';
                this.selectedCondition = '';
            },

            addModalOpen: false,
            editModalOpen: false,
            deleteModalOpen: false,
            showQuotaAlert: false,

            form: {
                product_id: '{{ $products->first()?->id ?? "" }}',
                unit_code: '',
                serial_number: '',
                condition: 'baik',
                status: 'tersedia',
            },

            editData: {
                id: '',
                product_id: '',
                product_name: '',
                unit_code: '',
                serial_number: '',
                condition: 'baik',
                status: 'tersedia',
                is_rented: false,
            },

            editActionUrl: '',
            deleteActionUrl: '',
            deleteUnitCode: '',
            deleteProductName: '',

            openAddModal() {
                this.form = {
                    product_id: '{{ $products->first()?->id ?? "" }}',
                    unit_code: '',
                    serial_number: '',
                    condition: 'baik',
                    status: 'tersedia',
                };
                this.addModalOpen = true;
            },

            openEditModal(data) {
                this.editData = { ...data };
                this.editActionUrl = '/admin/units/' + data.id;
                this.editModalOpen = true;
            },

            openDeleteModal(id, unitCode, productName) {
                this.deleteUnitCode = unitCode;
                this.deleteProductName = productName;
                this.deleteActionUrl = '/admin/units/' + id;
                this.deleteModalOpen = true;
            }
        };
    }
</script>
@endsection
