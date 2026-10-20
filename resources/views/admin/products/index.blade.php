@extends('layouts.admin')

@section('page-title', 'Katalog Produk')

@section('content')
@php
    $planLabel = $subscription->plan_name ?? 'Starter';
    $isQuotaFull = ($limit !== null && $used >= $limit);
    $percentage = $limit ? min(100, round(($used / $limit) * 100)) : 0;
@endphp

<div x-data="productManagement()" class="space-y-6 pb-12 font-sans">

    {{-- HEADER PAGE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-orange-600 mb-1">
                <span class="w-2 h-2 rounded-full bg-orange-500 inline-block animate-pulse"></span>
                <span>Manajemen Inventaris & Katalog</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Katalog Produk Rental</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola jenis produk rental, gambar cover, tarif sewa harian, denda keterlambatan per jam, dan syarat jaminan identitas.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($isQuotaFull)
                <button type="button" 
                        @click="showQuotaAlert = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-slate-200 text-slate-500 cursor-not-allowed shadow-xs"
                        title="Batas kuota produk paket telah penuh">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>+ Tambah Produk (Penuh)</span>
                </button>
            @else
                <button type="button" 
                        @click="openAddModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-orange-500 text-white hover:bg-orange-600 active:scale-95 transition-all shadow-md shadow-orange-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Produk</span>
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

    {{-- KUOTA PRODUK PAKET SUBSCRIPTION --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs transition hover:shadow-md">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl {{ $isQuotaFull ? 'bg-red-50 text-red-500' : ($percentage >= 80 ? 'bg-amber-50 text-amber-500' : 'bg-orange-50 text-orange-500') }} flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kuota Produk Toko</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold uppercase {{ $isQuotaFull ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                            Paket {{ ucfirst($planLabel) }}
                        </span>
                    </div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">
                        @if($limit === null)
                            <span>{{ $used }} Produk</span>
                            <span class="text-xs font-semibold text-emerald-600 ml-1.5">(Unlimited Kapasitas)</span>
                        @else
                            <span>{{ $used }} / {{ $limit }} Produk Digunakan</span>
                            <span class="text-xs font-semibold {{ $isQuotaFull ? 'text-red-600' : 'text-slate-500' }} ml-1.5">({{ $percentage }}%)</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($limit !== null)
                <div class="w-full md:w-64">
                    <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $isQuotaFull ? 'bg-red-500' : ($percentage >= 80 ? 'bg-amber-500' : 'bg-orange-500') }}"
                             style="width: {{ $percentage }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5 text-right font-medium">
                        @if($isQuotaFull)
                            <span class="text-red-600 font-bold">Kuota penuh! Upgrade paket untuk tambah produk.</span>
                        @else
                            <span>Tersisa {{ max(0, $limit - $used) }} slot produk aktif</span>
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            {{-- Pencarian --}}
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama produk atau deskripsi..." 
                       class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
            </div>

            {{-- Filter Kategori --}}
            <div class="sm:col-span-3">
                <select name="category_id" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status --}}
            <div class="sm:col-span-2">
                <select name="status" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            {{-- Tombol Submit & Reset --}}
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-3 text-xs font-bold rounded-xl bg-slate-800 text-white hover:bg-slate-900 transition flex items-center justify-center gap-1.5">
                    <span>Filter</span>
                </button>
                @if(request()->filled('search') || request()->filled('category_id') || request()->filled('status'))
                    <a href="{{ route('admin.products.index') }}" class="py-2 px-3 text-xs font-bold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- TABEL DAFTAR PRODUK --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Info Produk</th>
                            <th class="py-3.5 px-4">Tarif Sewa & Denda</th>
                            <th class="py-3.5 px-4 text-center">Unit Fisik</th>
                            <th class="py-3.5 px-4">Syarat Jaminan</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($products as $product)
                            <tr class="hover:bg-slate-50/50 transition">
                                
                                {{-- Thumbnail & Nama --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-14 h-14 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 relative group">
                                            <img src="{{ $product->cover_image_url }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                                 onerror="this.src='https://placehold.co/100x100/f8fafc/64748b?text=IMG'">
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 truncate max-w-xs hover:text-orange-600 transition" title="{{ $product->name }}">
                                                {{ $product->name }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600">
                                                    {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                                </span>
                                                <span class="text-[11px] text-slate-400 font-mono">/{{ $product->slug }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tarif Sewa & Denda --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-black text-slate-900">
                                        Rp {{ number_format($product->rental_price_per_day, 0, ',', '.') }}
                                        <span class="text-xs font-normal text-slate-500">/hari</span>
                                    </div>
                                    <div class="text-xs text-orange-600 font-semibold mt-0.5">
                                        Denda: Rp {{ number_format($product->late_fee_per_hour, 0, ',', '.') }}/jam
                                    </div>
                                </td>

                                {{-- Unit Fisik --}}
                                <td class="py-3.5 px-4 text-center">
                                    @if($product->units_count > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>{{ $product->available_units_count }} / {{ $product->units_count }} Tersedia</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60" title="Belum memiliki unit fisik. Kelola di menu Unit Fisik.">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>0 Unit Fisik</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Syarat Jaminan --}}
                                <td class="py-3.5 px-4 max-w-xs">
                                    @if($product->identity_guarantee_requirements)
                                        <p class="text-xs text-slate-600 line-clamp-2" title="{{ $product->identity_guarantee_requirements }}">
                                            {{ $product->identity_guarantee_requirements }}
                                        </p>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Standar toko (KTP)</span>
                                    @endif
                                </td>

                                {{-- Status Aktif / Nonaktif --}}
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk {{ $product->status === 'aktif' ? 'nonaktifkan' : 'aktifkan' }} produk"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all cursor-pointer {{ $product->status === 'aktif' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $product->status === 'aktif' ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                                            <span>{{ ucfirst($product->status) }}</span>
                                        </button>
                                    </form>
                                </td>

                                {{-- Aksi --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        {{-- Tombol Edit --}}
                                        <button type="button" 
                                                @click='openEditModal(@json($product))'
                                                class="p-2 rounded-xl text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition" 
                                                title="Edit Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button type="button" 
                                                @click='openDeleteModal(@json($product))'
                                                class="p-2 rounded-xl text-slate-600 hover:text-red-600 hover:bg-red-50 transition" 
                                                title="Hapus Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50/40">
                {{ $products->links() }}
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="py-16 px-4 text-center">
                <div class="w-16 h-16 rounded-3xl bg-orange-50 text-orange-500 mx-auto flex items-center justify-center mb-4 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Belum Ada Produk Ditampilkan</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    @if(request()->filled('search') || request()->filled('category_id') || request()->filled('status'))
                        Tidak ada produk yang cocok dengan kriteria pencarian Anda. Coba atur ulang filter pencarian.
                    @else
                        Mulai tambahkan katalog produk rental peralatan bayi Anda untuk ditampilkan kepada pelanggan toko.
                    @endif
                </p>

                <div class="mt-5 flex items-center justify-center gap-3">
                    @if(request()->filled('search') || request()->filled('category_id') || request()->filled('status'))
                        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-700 hover:bg-slate-50">
                            Reset Filter
                        </a>
                    @elseif(!$isQuotaFull)
                        <button type="button" @click="openAddModal()" class="px-4 py-2 rounded-xl text-xs font-bold bg-orange-500 text-white hover:bg-orange-600 transition shadow-sm">
                            + Tambah Produk Sekarang
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL TAMBAH PRODUK                                      --}}
    {{-- ======================================================== --}}
    <div x-show="addModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="addModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="addModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="addModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">
                
                <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-5">
                    @csrf

                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900" id="modal-title">Tambah Produk Rental Baru</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi katalog dan konfigurasi tarif sewa produk.</p>
                        </div>
                        <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Nama Produk --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Produk <span class="text-orange-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   required 
                                   placeholder="Contoh: Stroller Babyzen YOYO2 Black Frame"
                                   class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-orange-500">*</span>
                            </label>
                            <select name="category_id" required class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Status Tayang <span class="text-orange-500">*</span>
                            </label>
                            <select name="status" required class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                                <option value="aktif" selected>Aktif (Tampil di Katalog)</option>
                                <option value="nonaktif">Nonaktif (Disembunyikan)</option>
                            </select>
                        </div>

                        {{-- Harga Sewa per Hari --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Harga Sewa / Hari (Rp) <span class="text-orange-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-bold">Rp</span>
                                <input type="number" 
                                       name="rental_price_per_day" 
                                       required 
                                       min="0" 
                                       step="1000"
                                       placeholder="150000"
                                       class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                            </div>
                        </div>

                        {{-- Tarif Denda per Jam --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Denda Keterlambatan / Jam (Rp) <span class="text-orange-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-bold">Rp</span>
                                <input type="number" 
                                       name="late_fee_per_hour" 
                                       required 
                                       min="0" 
                                       step="1000"
                                       placeholder="10000"
                                       value="10000"
                                       class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                            </div>
                        </div>

                        {{-- Upload Foto Produk --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Foto Produk (Cover Utama)
                            </label>
                            <div class="border-2 border-dashed border-slate-200 hover:border-orange-400 rounded-2xl p-4 text-center transition bg-slate-50/50">
                                <input type="file" 
                                       name="image" 
                                       id="add-product-image"
                                       accept="image/jpeg,image/png,image/jpg,image/webp" 
                                       @change="previewAddImage($event)"
                                       class="hidden">
                                <label for="add-product-image" class="cursor-pointer block">
                                    <template x-if="addImagePreview">
                                        <div class="flex flex-col items-center">
                                            <img :src="addImagePreview" class="w-32 h-32 object-cover rounded-xl shadow-md border border-slate-200 mb-2">
                                            <span class="text-xs text-orange-600 font-bold hover:underline">Ganti Gambar</span>
                                        </div>
                                    </template>
                                    <template x-if="!addImagePreview">
                                        <div>
                                            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 mx-auto flex items-center justify-center mb-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <p class="text-xs font-bold text-slate-700">Klik untuk upload foto produk</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Format JPG, PNG, WEBP (Maks 2MB). Foto pertama otomatis jadi cover.</p>
                                        </div>
                                    </template>
                                </label>
                            </div>
                        </div>

                        {{-- Syarat Jaminan Identitas --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Syarat Jaminan Khusus Produk (Opsional)
                            </label>
                            <textarea name="identity_guarantee_requirements" 
                                      rows="2"
                                      placeholder="Contoh: Wajib KTP asli dan selfie memegang KTP. Domisili Malang diprioritaskan."
                                      class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Catatan ini akan tampil di halaman detail produk dan checkout pelanggan.</p>
                        </div>

                        {{-- Deskripsi Produk --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Deskripsi & Spesifikasi Produk
                            </label>
                            <textarea name="description" 
                                      rows="3"
                                      placeholder="Jelaskan fitur, kondisi, kelengkapan, dan spesifikasi produk ini..."
                                      class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition"></textarea>
                        </div>

                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="addModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-orange-500 text-white hover:bg-orange-600 rounded-xl transition shadow-md shadow-orange-500/20">
                            Simpan Produk
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL EDIT PRODUK                                        --}}
    {{-- ======================================================== --}}
    <div x-show="editModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="editModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">
                
                <form :action="editActionUrl" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Edit Produk Rental</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Perbarui rincian informasi dan tarif produk.</p>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Nama Produk --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Produk <span class="text-orange-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   x-model="editForm.name"
                                   required 
                                   class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-orange-500">*</span>
                            </label>
                            <select name="category_id" x-model="editForm.category_id" required class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Status Tayang <span class="text-orange-500">*</span>
                            </label>
                            <select name="status" x-model="editForm.status" required class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition text-slate-700">
                                <option value="aktif">Aktif (Tampil di Katalog)</option>
                                <option value="nonaktif">Nonaktif (Disembunyikan)</option>
                            </select>
                        </div>

                        {{-- Harga Sewa per Hari --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Harga Sewa / Hari (Rp) <span class="text-orange-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-bold">Rp</span>
                                <input type="number" 
                                       name="rental_price_per_day" 
                                       x-model="editForm.rental_price_per_day"
                                       required 
                                       min="0" 
                                       step="1000"
                                       class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                            </div>
                        </div>

                        {{-- Tarif Denda per Jam --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Denda Keterlambatan / Jam (Rp) <span class="text-orange-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-bold">Rp</span>
                                <input type="number" 
                                       name="late_fee_per_hour" 
                                       x-model="editForm.late_fee_per_hour"
                                       required 
                                       min="0" 
                                       step="1000"
                                       class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                            </div>
                        </div>

                        {{-- Ganti Foto Produk --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Foto Produk
                            </label>
                            <div class="border-2 border-dashed border-slate-200 hover:border-orange-400 rounded-2xl p-4 text-center transition bg-slate-50/50">
                                <input type="file" 
                                       name="image" 
                                       id="edit-product-image"
                                       accept="image/jpeg,image/png,image/jpg,image/webp" 
                                       @change="previewEditImage($event)"
                                       class="hidden">
                                <label for="edit-product-image" class="cursor-pointer block">
                                    <div class="flex flex-col items-center">
                                        <img :src="editImagePreview || editForm.cover_url" class="w-28 h-28 object-cover rounded-xl shadow-md border border-slate-200 mb-2">
                                        <span class="text-xs text-orange-600 font-bold hover:underline">Klik untuk Ganti Foto</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Biarkan kosong jika tidak ingin mengubah foto</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Syarat Jaminan Identitas --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Syarat Jaminan Khusus Produk (Opsional)
                            </label>
                            <textarea name="identity_guarantee_requirements" 
                                      x-model="editForm.identity_guarantee_requirements"
                                      rows="2"
                                      class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition"></textarea>
                        </div>

                        {{-- Deskripsi Produk --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Deskripsi & Spesifikasi Produk
                            </label>
                            <textarea name="description" 
                                      x-model="editForm.description"
                                      rows="3"
                                      class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition"></textarea>
                        </div>

                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold bg-orange-500 text-white hover:bg-orange-600 rounded-xl transition shadow-md shadow-orange-500/20">
                            Perbarui Produk
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL KONFIRMASI HAPUS                                   --}}
    {{-- ======================================================== --}}
    <div x-show="deleteModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="deleteModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="deleteModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 p-6 sm:p-7">
                
                <div class="text-center">
                    <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>

                    <h3 class="text-lg font-black text-slate-900">Hapus Produk Ini?</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Anda akan menghapus produk <span class="font-bold text-slate-800" x-text="deleteProductName"></span>.
                    </p>

                    <div class="mt-3 bg-amber-50 border border-amber-200/80 rounded-xl p-3 text-left">
                        <p class="text-[11px] text-amber-800 leading-normal">
                            <strong>Perhatian Keamanan:</strong> Sesuai aturan database, produk yang sudah pernah disewa tidak dapat dihapus permanen. Jika pernah disewa, sistem akan menyarankan untuk menonaktifkan status produk.
                        </p>
                    </div>

                    <form :action="deleteActionUrl" method="POST" class="mt-6 flex items-center justify-center gap-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="deleteModalOpen = false" class="w-full py-2.5 px-4 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="w-full py-2.5 px-4 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition shadow-md shadow-red-600/20">
                            Ya, Hapus
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    function productManagement() {
        return {
            addModalOpen: false,
            editModalOpen: false,
            deleteModalOpen: false,
            showQuotaAlert: false,

            addImagePreview: null,
            editImagePreview: null,

            editActionUrl: '',
            editForm: {
                name: '',
                category_id: '',
                rental_price_per_day: '',
                late_fee_per_hour: '',
                deposit_fee: '',
                description: '',
                identity_guarantee_requirements: '',
                status: 'aktif',
                cover_url: ''
            },

            deleteActionUrl: '',
            deleteProductName: '',

            openAddModal() {
                this.addImagePreview = null;
                this.addModalOpen = true;
            },

            previewAddImage(e) {
                const file = e.target.files[0];
                if (file) {
                    this.addImagePreview = URL.createObjectURL(file);
                }
            },

            openEditModal(product) {
                this.editActionUrl = `/admin/products/${product.id}`;
                this.editForm = {
                    name: product.name || '',
                    category_id: product.category_id || '',
                    rental_price_per_day: product.rental_price_per_day || '',
                    late_fee_per_hour: product.late_fee_per_hour || '',
                    deposit_fee: product.deposit_fee || '',
                    description: product.description || '',
                    identity_guarantee_requirements: product.identity_guarantee_requirements || '',
                    status: product.status || 'aktif',
                    cover_url: product.cover_image_url || ''
                };
                this.editImagePreview = null;
                this.editModalOpen = true;
            },

            previewEditImage(e) {
                const file = e.target.files[0];
                if (file) {
                    this.editImagePreview = URL.createObjectURL(file);
                }
            },

            openDeleteModal(product) {
                this.deleteActionUrl = `/admin/products/${product.id}`;
                this.deleteProductName = product.name;
                this.deleteModalOpen = true;
            }
        };
    }
</script>
@endsection
