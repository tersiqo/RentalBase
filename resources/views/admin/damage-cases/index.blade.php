@extends('layouts.admin')

@section('page-title', 'Kasus Kerusakan')

@section('content')
<div x-data="damageCaseManagement()" class="space-y-6 pb-12 font-sans">

    {{-- HEADER PAGE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-rose-600 mb-1">
                <span class="w-2 h-2 rounded-full bg-rose-500 inline-block animate-pulse"></span>
                <span>Operasional Retur & Penggantian Aset</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kasus Kerusakan & Kompensasi</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pencatatan unit cacat pasca-retur, pantau proses klarifikasi penyewa, verifikasi bukti pembayaran ganti rugi, dan penutupan kasus.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.damage-cases.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-rose-600 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-md shadow-rose-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Tiket Kerusakan</span>
            </a>
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

    {{-- SUMMARY METRICS CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
        {{-- Total Kasus --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Total Kasus</span>
                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ number_format($metrics['total']) }}</p>
            <span class="text-[11px] font-medium text-slate-400">Seluruh tiket tercatat</span>
        </div>

        {{-- Menunggu Respons --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-amber-700">Menunggu Tanggapan</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-amber-900">{{ number_format($metrics['terbuka']) }}</p>
            <span class="text-[11px] font-medium text-amber-600">Menunggu respons penyewa</span>
        </div>

        {{-- Dalam Tinjauan --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-blue-700">Dalam Tinjauan</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-blue-900">{{ number_format($metrics['dalam_tinjauan']) }}</p>
            <span class="text-[11px] font-medium text-blue-600">Proses klarifikasi toko</span>
        </div>

        {{-- Menunggu Verifikasi --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-purple-700">Verifikasi Bayar</span>
                <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-purple-900">{{ number_format($metrics['menunggu_verifikasi']) }}</p>
            <span class="text-[11px] font-medium text-purple-600">Bukti bayar diunggah</span>
        </div>

        {{-- Selesai / Lunas --}}
        <div class="col-span-2 sm:col-span-1 bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-emerald-700">Selesai / Lunas</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-emerald-900">{{ number_format($metrics['selesai']) }}</p>
            <span class="text-[11px] font-medium text-emerald-600">Kasus tuntas terselesaikan</span>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR (INSTANT REAL-TIME FILTER — SAMA PERSIS DENGAN HALAMAN PRODUK & UNIT) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            {{-- Pencarian Realtime --}}
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Cari no. tiket, order, kode unit, atau customer..." 
                       class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-800">
            </div>

            {{-- Filter Status Kasus --}}
            <div class="sm:col-span-3">
                <select x-model="selectedCaseStatus" 
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                    <option value="">Semua Status Kasus</option>
                    <option value="terbuka">Menunggu Tanggapan</option>
                    <option value="dalam_tinjauan">Dalam Tinjauan</option>
                    <option value="kompensasi_dibayar">Kompensasi Dibayar</option>
                    <option value="selesai">Selesai / Lunas</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>

            {{-- Filter Status Pembayaran --}}
            <div class="sm:col-span-2">
                <select x-model="selectedPaymentStatus" 
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                    <option value="">Semua Status Bayar</option>
                    <option value="belum_dibayar">Belum Dibayar</option>
                    <option value="menunggu_verifikasi">Verifikasi Bayar</option>
                    <option value="lunas">Lunas</option>
                </select>
            </div>

            {{-- Aksi Reset & Indikator Hasil --}}
            <div class="sm:col-span-2 flex items-center gap-2">
                <template x-if="hasActiveFilters">
                    <button type="button" 
                            @click="resetFilters()" 
                            class="w-full py-2 px-3 text-xs font-bold rounded-xl border border-orange-200 bg-orange-50 text-orange-600 hover:bg-orange-100 transition flex items-center justify-center gap-1.5 shadow-xs"
                            title="Reset Filter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Reset Filter</span>
                    </button>
                </template>
                <template x-if="!hasActiveFilters">
                    <div class="w-full py-2 px-2 text-[11px] font-semibold text-slate-400 text-center bg-slate-50 border border-dashed border-slate-200 rounded-xl truncate">
                        <span x-text="casesData.length + ' Kasus'"></span>
                    </div>
                </template>
            </div>

        </div>
    </div>

    {{-- MAIN TABLE OF DAMAGE CASES --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 sm:px-6">Tiket & Waktu</th>
                        <th class="py-3.5 px-4">Pesanan & Penyewa</th>
                        <th class="py-3.5 px-4">Unit Fisik / Produk</th>
                        <th class="py-3.5 px-4">Tuntutan Kompensasi</th>
                        <th class="py-3.5 px-4">Bukti Foto</th>
                        <th class="py-3.5 px-4">Status Kasus</th>
                        <th class="py-3.5 px-4">Status Bayar</th>
                        <th class="py-3.5 px-4 text-right sm:pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($damageCases as $case)
                        @php
                            $report = $case->damageReport;
                            $order = $report?->order;
                            $unit = $report?->equipmentUnit;
                            $product = $unit?->product;
                            $imagesCount = count($report?->images_list ?? []);
                        @endphp
                        <tr x-show="matchesFilter({{ $case->id }})" class="hover:bg-slate-50/60 transition-colors">
                            {{-- Tiket & Waktu --}}
                            <td class="py-4 px-4 sm:px-6">
                                <div class="font-extrabold text-slate-900">#{{ $case->id }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $case->created_at ? $case->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                                </div>
                            </td>

                            {{-- Pesanan & Penyewa --}}
                            <td class="py-4 px-4">
                                @if($order)
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="font-semibold text-rose-600 hover:text-rose-700 hover:underline inline-flex items-center gap-1"
                                       title="Buka detail pesanan">
                                        <span>{{ $order->order_number ?? ('ORD-' . $order->id) }}</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                    <div class="text-xs text-slate-600 font-medium mt-0.5">
                                        {{ $order->user->name ?? 'Penyewa' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $order->user->email ?? '-' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Pesanan terhapus</span>
                                @endif
                            </td>

                            {{-- Unit Fisik / Produk --}}
                            <td class="py-4 px-4">
                                @if($unit)
                                    <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-xs">
                                            {{ $unit->unit_code }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-600 font-medium mt-1 truncate max-w-[180px]">
                                        {{ $product->name ?? 'Perlengkapan Rental' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        SN: {{ $unit->serial_number ?? '-' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Unit tidak terikat</span>
                                @endif
                            </td>

                            {{-- Tuntutan Kompensasi --}}
                            <td class="py-4 px-4">
                                <div class="font-extrabold text-slate-900">{{ $case->formatted_total }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Perbaikan: {{ $case->formatted_repair_cost }}
                                </div>
                                @if((float) $case->fine_amount > 0)
                                    <div class="text-[11px] text-rose-500 font-medium">
                                        Denda: +{{ $case->formatted_fine_amount }}
                                    </div>
                                @endif
                            </td>

                            {{-- Bukti Foto --}}
                            <td class="py-4 px-4">
                                @if($imagesCount > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $imagesCount }} Foto</span>
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tidak ada foto</span>
                                @endif
                            </td>

                            {{-- Status Kasus --}}
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $case->status_color }}">
                                    {{ $case->status_label }}
                                </span>
                            </td>

                            {{-- Status Bayar --}}
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $case->payment_status_color }}">
                                    {{ $case->payment_status_label }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="py-4 px-4 text-right sm:pr-6">
                                <a href="{{ route('admin.damage-cases.show', $case->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 transition-all border border-slate-200/80">
                                    <span>Detail & Tangani</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800">Belum Ada Kasus Kerusakan</h3>
                                    <p class="text-xs text-slate-500">
                                        Belum ada catatan kerusakan yang dibuat. Seluruh unit rental yang dikembalikan dalam kondisi normal.
                                    </p>
                                    <div class="pt-2">
                                        <a href="{{ route('admin.damage-cases.create') }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700 transition-all">
                                            <span>+ Buat Tiket Kerusakan Baru</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                    {{-- Dynamic Instant Filter Empty State --}}
                    <tr x-show="filteredCount === 0 && casesData.length > 0" class="border-b border-slate-100">
                        <td colspan="8" class="py-12 px-4 text-center">
                            <div class="max-w-sm mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <h3 class="text-base font-bold text-slate-800">Tidak Ada Kasus yang Cocok</h3>
                                <p class="text-xs text-slate-500">
                                    Tidak ada laporan kerusakan yang cocok dengan kata kunci atau filter status yang Anda pilih.
                                </p>
                                <div class="pt-1">
                                    <button type="button"
                                            @click="resetFilters()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-orange-50 text-orange-600 hover:bg-orange-100 border border-orange-200 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Reset Filter</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- PAGINATION (Hanya jika tipe Paginator dan tidak sedang difilter client-side) --}}
        @if($damageCases instanceof \Illuminate\Pagination\LengthAwarePaginator && $damageCases->hasPages())
            <div x-show="!hasActiveFilters" class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Menampilkan <span class="font-bold text-slate-700">{{ $damageCases->firstItem() }}</span> -
                    <span class="font-bold text-slate-700">{{ $damageCases->lastItem() }}</span> dari
                    <span class="font-bold text-slate-700">{{ $damageCases->total() }}</span> kasus
                </div>
                <div>
                    {{ $damageCases->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- SCRIPT ALPINE.JS MANAGEMENT FILTER --}}
<script>
    function damageCaseManagement() {
        return {
            searchQuery: {!! json_encode((string) request('search', '')) !!},
            selectedCaseStatus: {!! json_encode((string) (request('status') ?? request('case_status', ''))) !!},
            selectedPaymentStatus: {!! json_encode((string) request('payment_status', '')) !!},

            casesData: {!! json_encode($damageCases->map(function ($c) {
                $report = $c->damageReport;
                $order = $report?->order;
                $unit = $report?->equipmentUnit;
                $product = $unit?->product;
                return [
                    'id' => (int) $c->id,
                    'case_status' => (string) $c->case_status,
                    'payment_status' => (string) $c->payment_status,
                    'order_number' => (string) ($order?->order_number ?? ''),
                    'unit_code' => (string) ($unit?->unit_code ?? ''),
                    'serial_number' => (string) ($unit?->serial_number ?? ''),
                    'product_name' => (string) ($product?->name ?? ''),
                    'customer_name' => (string) ($order?->user?->name ?? ''),
                    'customer_email' => (string) ($order?->user?->email ?? ''),
                    'description' => (string) ($report?->description ?? ''),
                ];
            })->values()) !!},

            matchesFilter(caseId) {
                const c = this.casesData.find(item => item.id === caseId);
                if (!c) return true;

                // Filter status kasus
                if (this.selectedCaseStatus) {
                    if (this.selectedCaseStatus === 'terbuka' && !['terbuka', 'menunggu_respons', 'menunggu_tanggapan'].includes(c.case_status)) {
                        return false;
                    } else if (this.selectedCaseStatus === 'dalam_tinjauan' && !['dalam_tinjauan', 'negosiasi'].includes(c.case_status)) {
                        return false;
                    } else if (this.selectedCaseStatus === 'kompensasi_dibayar' && !['kompensasi_dibayar', 'menunggu_verifikasi'].includes(c.case_status)) {
                        return false;
                    } else if (this.selectedCaseStatus === 'selesai' && !['selesai', 'lunas'].includes(c.case_status)) {
                        return false;
                    } else if (!['terbuka', 'dalam_tinjauan', 'kompensasi_dibayar', 'selesai'].includes(this.selectedCaseStatus) && c.case_status !== this.selectedCaseStatus) {
                        return false;
                    }
                }

                // Filter status pembayaran
                if (this.selectedPaymentStatus && c.payment_status !== this.selectedPaymentStatus) {
                    return false;
                }

                // Pencarian teks
                if (this.searchQuery.trim()) {
                    const q = this.searchQuery.trim().toLowerCase();
                    const idStr = '#' + c.id;
                    const idPlain = String(c.id);
                    const orderNum = (c.order_number || '').toLowerCase();
                    const unitCode = (c.unit_code || '').toLowerCase();
                    const serialNum = (c.serial_number || '').toLowerCase();
                    const productName = (c.product_name || '').toLowerCase();
                    const customerName = (c.customer_name || '').toLowerCase();
                    const customerEmail = (c.customer_email || '').toLowerCase();
                    const desc = (c.description || '').toLowerCase();

                    if (
                        !idStr.includes(q) &&
                        !idPlain.includes(q) &&
                        !orderNum.includes(q) &&
                        !unitCode.includes(q) &&
                        !serialNum.includes(q) &&
                        !productName.includes(q) &&
                        !customerName.includes(q) &&
                        !customerEmail.includes(q) &&
                        !desc.includes(q)
                    ) {
                        return false;
                    }
                }

                return true;
            },

            get filteredCount() {
                return this.casesData.filter(c => this.matchesFilter(c.id)).length;
            },

            get hasActiveFilters() {
                return Boolean(this.searchQuery.trim() || this.selectedCaseStatus || this.selectedPaymentStatus);
            },

            resetFilters() {
                this.searchQuery = '';
                this.selectedCaseStatus = '';
                this.selectedPaymentStatus = '';
            }
        };
    }
</script>
@endsection
