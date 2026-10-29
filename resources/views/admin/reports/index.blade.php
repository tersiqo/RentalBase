@extends('layouts.admin')

@section('title', 'Laporan Transaksi')
@section('page-title', 'Laporan Transaksi')

@section('content')
<div x-data="reportPage({
        preset: '{{ old('preset', $preset) }}',
        startDate: '{{ old('start_date', $startDate) }}',
        endDate: '{{ old('end_date', $endDate) }}',
        appliedStartDate: '{{ $startDate }}',
        appliedEndDate: '{{ $endDate }}',
        todayStr: '{{ $todayStr }}',
        sevenDaysAgoStr: '{{ $sevenDaysAgoStr }}',
        startOfMonthStr: '{{ $startOfMonthStr }}',
        endOfMonthStr: '{{ $endOfMonthStr }}'
    })"
    class="space-y-6 pb-12 font-sans">

    {{-- HEADER HALAMAN & AKSI EKSPOR --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-orange-600 mb-1">
                <span class="w-2 h-2 rounded-full bg-orange-500 inline-block animate-pulse"></span>
                <span>Audit Finansial & Operasional</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Laporan Transaksi Toko</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pantau realisasi pendapatan sewa pokok, penerimaan denda keterlambatan, dan pelunasan kompensasi kerusakan unit rental.
            </p>
        </div>

        {{-- Tombol Dropdown Export (CSV, Excel, PDF) --}}
        <div class="relative" x-data="{ openExport: false }">
            <button type="button"
                    @click="openExport = !openExport"
                    @click.outside="openExport = false"
                    id="btn-export"
                    title="Export laporan transaksi ke format CSV, Excel, atau PDF"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 active:scale-95 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Export</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': openExport }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Menu Pilihan Format Export --}}
            <div x-show="openExport"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-900/10 py-1.5 z-50 divide-y divide-slate-100"
                 style="display: none;">

                <div class="px-3.5 py-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pilih Format Unduhan</span>
                </div>

                <div class="py-1">
                    {{-- 1. Export CSV --}}
                    <a href="{{ route('admin.reports.export', ['format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate, 'preset' => $preset]) }}"
                       id="btn-export-csv"
                       @click="openExport = false"
                       class="flex items-center gap-3 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        <div>
                            <div class="font-bold text-slate-900">Export CSV</div>
                            <span class="text-[10px] text-slate-400">Data mentah (.csv)</span>
                        </div>
                    </a>

                    {{-- 2. Export Excel (.xlsx) --}}
                    <a href="{{ route('admin.reports.export', ['format' => 'excel', 'start_date' => $startDate, 'end_date' => $endDate, 'preset' => $preset]) }}"
                       id="btn-export-excel"
                       @click="openExport = false"
                       class="flex items-center gap-3 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <div>
                            <div class="font-bold text-slate-900">Export Excel</div>
                            <span class="text-[10px] text-slate-400">Spreadsheet (.xlsx)</span>
                        </div>
                    </a>

                    {{-- 3. Export PDF (.pdf) --}}
                    <a href="{{ route('admin.reports.export', ['format' => 'pdf', 'start_date' => $startDate, 'end_date' => $endDate, 'preset' => $preset]) }}"
                       id="btn-export-pdf"
                       @click="openExport = false"
                       class="flex items-center gap-3 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-rose-700 transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <div>
                            <div class="font-bold text-slate-900">Export PDF</div>
                            <span class="text-[10px] text-slate-400">Dokumen cetak (.pdf)</span>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- FLASH MESSAGES & VALIDATION ERRORS --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-900 flex items-start justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Berhasil!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-900 flex items-start justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Kesalahan Validasi Tanggal</h4>
                    <ul class="text-xs text-red-700 mt-1 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- KARTU FILTER PERIODE LAPORAN --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs">
        <form method="GET" action="{{ route('admin.reports.index') }}" @submit="handleSubmit($event)" class="space-y-4">

            {{-- 1. Bagian Preset Periode Laporan --}}
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500">
                        PERIODE LAPORAN
                    </label>
                    <template x-if="isDirty">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Perubahan belum diterapkan - Klik "Terapkan Filter"
                        </span>
                    </template>
                </div>

                {{-- Segmented Control Preset --}}
                <div class="inline-flex flex-wrap p-1 bg-slate-100/90 rounded-xl gap-1 border border-slate-200/60 max-w-full">
                    <button type="button"
                            @click="setPreset('hari_ini')"
                            :class="preset === 'hari_ini' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium'"
                            class="px-3.5 py-2 rounded-lg text-xs transition-all cursor-pointer">
                        Hari Ini
                    </button>

                    <button type="button"
                            @click="setPreset('7_hari_terakhir')"
                            :class="preset === '7_hari_terakhir' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium'"
                            class="px-3.5 py-2 rounded-lg text-xs transition-all cursor-pointer">
                        7 Hari Terakhir
                    </button>

                    <button type="button"
                            @click="setPreset('bulan_ini')"
                            :class="preset === 'bulan_ini' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium'"
                            class="px-3.5 py-2 rounded-lg text-xs transition-all cursor-pointer">
                        Bulan Ini
                    </button>

                    <button type="button"
                            @click="preset = 'kustom'"
                            :class="preset === 'kustom' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium'"
                            class="px-3.5 py-2 rounded-lg text-xs transition-all cursor-pointer">
                        Rentang Kustom
                    </button>
                </div>

                <input type="hidden" name="preset" :value="preset">
            </div>

            {{-- 2. Input Tanggal Mulai & Tanggal Akhir serta Tombol Aksi --}}
            <div class="pt-3 border-t border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-start">

                    {{-- Tanggal Mulai --}}
                    <div class="md:col-span-4">
                        <label for="start_date" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Tanggal Mulai
                        </label>
                        <input type="date"
                               id="start_date"
                               name="start_date"
                               x-model="startDate"
                               @change="onDateChange()"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border @error('start_date') border-red-300 ring-1 ring-red-300 @else border-slate-200 @enderror rounded-xl text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                        @error('start_date')
                            <p class="mt-1.5 text-xs text-red-600 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="md:col-span-4">
                        <label for="end_date" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Tanggal Akhir
                        </label>
                        <input type="date"
                               id="end_date"
                               name="end_date"
                               x-model="endDate"
                               :min="startDate"
                               @change="onDateChange()"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border @error('end_date') border-red-300 ring-1 ring-red-300 @else border-slate-200 @enderror rounded-xl text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                        @error('end_date')
                            <p class="mt-1.5 text-xs text-red-600 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                        <template x-if="endDate && startDate && endDate < startDate">
                            <p class="mt-1.5 text-xs text-red-600 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>Tanggal akhir tidak boleh lebih awal dari tanggal mulai.</span>
                            </p>
                        </template>
                    </div>

                    {{-- Tombol Terapkan Filter & Reset --}}
                    <div class="md:col-span-4 flex items-center gap-2.5 pt-1 md:pt-6">
                        <button type="submit"
                                :disabled="isSubmitting || (endDate && startDate && endDate < startDate)"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-orange-500 hover:bg-orange-600 text-white shadow-md shadow-orange-500/20 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" :class="{ 'animate-spin': isSubmitting }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span x-text="isSubmitting ? 'Memproses...' : 'Terapkan Filter'">Terapkan Filter</span>
                        </button>

                        <a href="{{ route('admin.reports.index') }}"
                           title="Kembalikan filter ke periode default Bulan Ini"
                           class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl font-bold text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all cursor-pointer">
                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Reset</span>
                        </a>
                    </div>

                </div>

                {{-- Petunjuk Singkat di Bawah Input --}}
                <p class="text-xs text-slate-500 mt-2.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Pilih rentang tanggal untuk melihat transaksi pada periode tersebut.</span>
                </p>
            </div>

        </form>
    </div>

    {{-- RINGKASAN PERIODE LAPORAN AKTIF --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:px-5 sm:py-3.5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Periode</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        Preset: {{ $presetLabel }}
                    </span>
                </div>
                <p class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">
                    Periode laporan: <span class="text-orange-600">{{ $activePeriodText }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span>Data ringkasan finansial &amp; tabel aktif sesuai periode ini</span>
        </div>
    </div>

    {{-- KARTU RINGKASAN FINANSIAL & METRIK TRANSAKSI SELESAI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">

        {{-- 1. Total Pendapatan --}}
        <div class="bg-white border-2 border-orange-500/30 rounded-2xl p-4 shadow-sm relative overflow-hidden bg-gradient-to-br from-white via-orange-50/20 to-orange-100/30">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-orange-950 uppercase tracking-wider">Total Pendapatan</span>
                <span class="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-sm shadow-orange-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-orange-600">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            <span class="text-[11px] font-semibold text-slate-500 mt-1 block">Dana masuk lunas &amp; sah</span>
        </div>

        {{-- 2. Pendapatan Sewa Pokok --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Sewa Pokok</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900">Rp {{ number_format($pendapatanSewaPokok, 0, ',', '.') }}</p>
            <span class="text-[11px] font-medium text-slate-400 mt-1 block">Pesanan selesai terverifikasi</span>
        </div>

        {{-- 3. Pendapatan Denda --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pendapatan Denda</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900">Rp {{ number_format($pendapatanDenda, 0, ',', '.') }}</p>
            <span class="text-[11px] font-medium text-slate-400 mt-1 block">Denda telat lunas</span>
        </div>

        {{-- 4. Kompensasi Kerusakan --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Kompensasi Rusak</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900">Rp {{ number_format($kompensasiKerusakan, 0, ',', '.') }}</p>
            <span class="text-[11px] font-medium text-slate-400 mt-1 block">Klaim kerusakan lunas</span>
        </div>

        {{-- 5. Total Transaksi Selesai --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Transaksi Selesai</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900">{{ number_format($totalTransaksiSelesai) }}</p>
            <span class="text-[11px] font-medium text-slate-400 mt-1 block">Pesanan selesai tuntas</span>
        </div>

    </div>

    {{-- TABEL RINCIAN TRANSAKSI --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Rincian Transaksi Pesanan</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Periode laporan: <strong class="text-slate-700">{{ $activePeriodText }}</strong>
                </p>
            </div>
            <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-600 rounded-full w-fit">
                Total: {{ $orders->total() }} Transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs text-slate-500 uppercase font-bold tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center w-12">No.</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Tanggal Transaksi</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Kode Order</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Nama Customer</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right">Biaya Sewa</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right">Denda</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right">Kompensasi Kerusakan</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Metode Pembayaran</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-right">Total Bersih</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Status Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($orders as $index => $order)
                        @php
                            $isVerified = ($order->status === 'selesai' && ($order->payments->whereIn('status', ['terverifikasi', 'diterima'])->isNotEmpty() || $order->payments->isEmpty()))
                                || $order->payments->whereIn('status', ['terverifikasi', 'diterima'])->isNotEmpty();

                            $sewa = ($order->status === 'selesai' && $isVerified)
                                ? (float) $order->total_amount
                                : 0;

                            $denda = ($order->returnModel && $order->returnModel->late_fee_status === 'lunas')
                                ? (float) $order->returnModel->late_fee_amount
                                : 0;

                            $kompensasi = (float) $order->damageCases
                                ->where('payment_status', 'lunas')
                                ->sum(fn ($dc) => (float) $dc->repair_cost + (float) $dc->fine_amount);

                            $totalBersih = $sewa + $denda + $kompensasi;
                            $metode = $formatPaymentMethod($order->latestPayment ?? $order->payments->first());
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- No. urut paginasi --}}
                            <td class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400">
                                {{ $orders->firstItem() + $index }}
                            </td>

                            {{-- Tanggal Transaksi --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-medium text-slate-800 block text-xs">
                                    {{ $order->created_at ? $order->created_at->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    {{ $order->created_at ? $order->created_at->format('H:i') . ' WIB' : '' }}
                                </span>
                            </td>

                            {{-- Kode Order --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="font-bold text-slate-900 hover:text-orange-600 transition-colors">
                                    #{{ $order->order_number }}
                                </a>
                            </td>

                            {{-- Nama Customer --}}
                            <td class="px-5 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                {{ $order->user->name ?? 'Customer' }}
                            </td>

                            {{-- Biaya Sewa --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right font-medium text-slate-800">
                                Rp {{ number_format($sewa, 0, ',', '.') }}
                            </td>

                            {{-- Denda --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right font-medium {{ $denda > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">
                                Rp {{ number_format($denda, 0, ',', '.') }}
                            </td>

                            {{-- Kompensasi Kerusakan --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right font-medium {{ $kompensasi > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                Rp {{ number_format($kompensasi, 0, ',', '.') }}
                            </td>

                            {{-- Metode Pembayaran --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                @if(stripos($metode, 'qris') !== false)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        {{ $metode }}
                                    </span>
                                @elseif($metode !== '-')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        {{ $metode }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Total Bersih --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right font-extrabold text-slate-900">
                                Rp {{ number_format($totalBersih, 0, ',', '.') }}
                            </td>

                            {{-- Status Transaksi --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                                    @if($order->status === 'selesai') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($order->status === 'sedang_disewa') bg-sky-50 text-sky-700 border border-sky-200
                                    @elseif(in_array($order->status, ['siap_kirim', 'diproses', 'dikonfirmasi'])) bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @elseif($order->status === 'dikirim') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($order->status === 'dikembalikan') bg-cyan-50 text-cyan-700 border border-cyan-200
                                    @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        @if($order->status === 'selesai') bg-emerald-500
                                        @elseif($order->status === 'sedang_disewa') bg-sky-500
                                        @elseif(in_array($order->status, ['siap_kirim', 'diproses', 'dikonfirmasi'])) bg-indigo-500
                                        @elseif($order->status === 'dikirim') bg-purple-500
                                        @elseif($order->status === 'dikembalikan') bg-cyan-500
                                        @else bg-slate-400 @endif"></span>
                                    {{ $order->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 shadow-inner">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-base font-bold text-slate-700">Tidak ada transaksi ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    Tidak ada data transaksi yang memenuhi kriteria untuk rentang periode tanggal yang dipilih.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $orders->links() }}
            </div>
        @endif

    </div>

</div>

{{-- ALPINE.JS COMPONENT UNTUK FILTER PRESET & INTERAKSI --}}
<script>
    function reportPage(config) {
        return {
            preset: config.preset || 'bulan_ini',
            startDate: config.startDate || '',
            endDate: config.endDate || '',
            appliedStartDate: config.appliedStartDate || config.startDate || '',
            appliedEndDate: config.appliedEndDate || config.endDate || '',
            todayStr: config.todayStr || '',
            sevenDaysAgoStr: config.sevenDaysAgoStr || '',
            startOfMonthStr: config.startOfMonthStr || '',
            endOfMonthStr: config.endOfMonthStr || '',
            isSubmitting: false,

            get isDirty() {
                return (this.startDate !== this.appliedStartDate) || (this.endDate !== this.appliedEndDate);
            },

            setPreset(type) {
                this.preset = type;

                if (type === 'hari_ini') {
                    this.startDate = this.todayStr;
                    this.endDate = this.todayStr;
                } else if (type === '7_hari_terakhir') {
                    this.startDate = this.sevenDaysAgoStr;
                    this.endDate = this.todayStr;
                } else if (type === 'bulan_ini') {
                    this.startDate = this.startOfMonthStr;
                    this.endDate = this.endOfMonthStr;
                }
            },

            onDateChange() {
                // Deteksi apakah tanggal yang dipilih cocok dengan salah satu preset
                if (this.startDate === this.todayStr && this.endDate === this.todayStr) {
                    this.preset = 'hari_ini';
                } else if (this.startDate === this.sevenDaysAgoStr && this.endDate === this.todayStr) {
                    this.preset = '7_hari_terakhir';
                } else if (this.startDate === this.startOfMonthStr && this.endDate === this.endOfMonthStr) {
                    this.preset = 'bulan_ini';
                } else {
                    this.preset = 'kustom';
                }
            },

            handleSubmit(event) {
                if (this.endDate && this.startDate && this.endDate < this.startDate) {
                    event.preventDefault();
                    return false;
                }
                this.isSubmitting = true;
                return true;
            }
        };
    }
</script>
@endsection
