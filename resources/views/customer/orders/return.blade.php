@extends('layouts.customer')

@section('content')
@php
    $isLate = $lateFeeData['is_late'] ?? false;
    $totalLateFee = (float) ($lateFeeData['total_late_fee'] ?? 0);
    $lateHours = (int) ($lateFeeData['late_hours'] ?? 0);
    $backUrl = route('customer.orders.show', $order->id);
@endphp

<div class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full font-sans text-gray-700"
     x-data="{
        returnMethod: '{{ old('return_method', $order->isPickup() ? 'ambil_toko' : 'kurir') }}',
        selectedCourier: '{{ old('courier_name', '') }}',
        submitting: false
     }">

    {{-- Breadcrumb & Header Halaman --}}
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 mb-2">
            <a href="{{ route('customer.orders') }}" class="hover:text-primary-600 transition">Pesanan Saya</a>
            <span class="text-gray-300">/</span>
            <a href="{{ $backUrl }}" class="hover:text-primary-600 transition font-mono">{{ $order->order_number ?? ('ORD-' . $order->id) }}</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-800 font-semibold">Ajukan Pengembalian</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Form Pengembalian Barang</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $order->status_color }}">
                        {{ $order->status_label }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Lengkapi metode pengembalian perlengkapan sewa Anda untuk pesanan <b class="font-mono text-gray-800">{{ $order->order_number }}</b>.
                </p>
            </div>

            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition shadow-sm self-start sm:self-auto">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Detail Pesanan</span>
            </a>
        </div>
    </div>

    {{-- Alert Pesan Error dari Session atau Validasi --}}
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 font-bold text-sm mb-1.5">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Mohon periksa kembali isian formulir Anda:</span>
            </div>
            <ul class="list-disc list-inside text-xs sm:text-sm space-y-1 text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UTAMA PENGEMBALIAN BARANG --}}
    <form action="{{ route('customer.orders.return', $order->id) }}"
          method="POST"
          enctype="multipart/form-data"
          @submit="submitting = true">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_380px] gap-6 items-start">

            {{-- ================= KOLOM KIRI ================= --}}
            <div class="space-y-6">

                {{-- A. RINGKASAN PESANAN DAN BARANG --}}
                <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                        <span>Ringkasan Pesanan & Barang yang Disewa</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm pb-4 border-b border-gray-100">
                        <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                            <span class="text-xs text-gray-500 block mb-0.5">Nomor Order</span>
                            <b class="text-gray-900 font-mono font-bold">{{ $order->order_number }}</b>
                        </div>
                        <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                            <span class="text-xs text-gray-500 block mb-0.5">Toko Rental</span>
                            <b class="text-gray-900 font-semibold">{{ $client->nama_usaha ?? ($order->client->nama_usaha ?? 'RentalBase Store') }}</b>
                        </div>
                        <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                            <span class="text-xs text-gray-500 block mb-0.5">Periode & Tenggat Sewa</span>
                            <b class="text-primary-600 font-bold block">{{ $order->date_range }}</b>
                            <small class="text-[11px] text-gray-500">
                                Berakhir: {{ $order->rental_end_date ? $order->rental_end_date->translatedFormat('d M Y, H:i') : '-' }} WIB
                            </small>
                        </div>
                    </div>

                    {{-- Daftar Barang yang Disewa --}}
                    <div class="pt-4 space-y-3">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Daftar Unit yang Harus Dikembalikan</h3>
                        @foreach($order->orderItems as $item)
                            @php
                                $product = $item->product;
                                $dbFee = (float) ($product?->late_fee_per_hour ?? 0);
                                $dailyPrice = (float) ($product?->rental_price_per_day ?? 0);
                                $effectiveLateFee = $dbFee > 0 ? $dbFee : ($dailyPrice > 0 ? round($dailyPrice * 0.1, -3) : 10000);
                                $img = $product?->main_image
                                    ? storage_url($product->main_image)
                                    : 'https://placehold.co/100x100/f3f4f6/9ca3af?text=Produk';
                            @endphp
                            <div class="flex items-center justify-between p-3.5 bg-gray-50/60 border border-gray-100 rounded-xl gap-4">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <img src="{{ $img }}"
                                         alt="{{ $product?->name ?? 'Produk' }}"
                                         loading="lazy"
                                         class="w-14 h-14 rounded-xl object-cover bg-white border border-gray-200 shrink-0">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-gray-900 text-sm truncate">{{ $product?->name ?? 'Peralatan Sewa' }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Jumlah: <b class="text-gray-800">{{ $item->quantity ?? 1 }} unit</b>
                                            &bull; Tarif Denda: <span class="font-semibold text-gray-800 tabular-nums">Rp {{ number_format($effectiveLateFee, 0, ',', '.') }}/jam</span>
                                        </p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-primary-50 text-primary-700 font-bold text-xs shrink-0">
                                    {{ $item->quantity ?? 1 }}x Unit
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- B. PANDUAN DAN SYARAT PENGEMBALIAN --}}
                <section class="bg-blue-50/60 border border-blue-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-blue-950 flex items-center gap-2.5 mb-3">
                        <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </span>
                        <span>Panduan & Syarat Pengembalian Barang</span>
                    </h2>
                    <ul class="space-y-2 text-xs sm:text-sm text-blue-900 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-blue-600 mt-0.5">1.</span>
                            <span><b>Kondisi Bersih & Rapi:</b> Pastikan seluruh barang dikembalikan dalam keadaan bersih seperti saat pertama kali diterima.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-blue-600 mt-0.5">2.</span>
                            <span><b>Kelengkapan Aksesori:</b> Semua aksesori, baterai, charger, kabel, tas, maupun kotak pelindung wajib disertakan secara lengkap.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-blue-600 mt-0.5">3.</span>
                            <span><b>Kejujuran Kondisi Fisik:</b> Apabila terdapat kendala fungsi atau kerusakan selama pemakaian, mohon tuliskan pada kolom <i>Catatan Tambahan</i> di bawah agar mempercepat pemeriksaan.</span>
                        </li>
                    </ul>
                </section>

                {{-- C. PILIHAN METODE PENGEMBALIAN --}}
                <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        </span>
                        <span>Pilih Metode Pengembalian <span class="text-red-500">*</span></span>
                    </h2>

                    {{-- Toggle Radio Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        {{-- Pilihan 1: Kirim via Ekspedisi / Kurir --}}
                        <label class="cursor-pointer block">
                            <input type="radio"
                                   name="return_method"
                                   value="kurir"
                                   x-model="returnMethod"
                                   class="sr-only">
                            <div class="p-4 rounded-2xl border-2 transition-all h-full flex flex-col justify-between"
                                 :class="returnMethod === 'kurir'
                                    ? 'border-primary-500 bg-primary-50/50 ring-2 ring-primary-100'
                                    : 'border-gray-200 hover:border-gray-300 bg-white'">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="w-7 h-7 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center mb-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        </div>
                                        <h3 class="font-bold text-sm text-gray-900">Kirim via Ekspedisi / Kurir</h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Kirim barang melalui JNE, J&T, SiCepat, Gojek, atau Grab dan masukkan nomor resi pengiriman.
                                        </p>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                                          :class="returnMethod === 'kurir' ? 'border-primary-500 bg-primary-500 text-white' : 'border-gray-300'">
                                        <svg x-show="returnMethod === 'kurir'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                </div>
                            </div>
                        </label>

                        {{-- Pilihan 2: Antar Langsung ke Toko --}}
                        <label class="cursor-pointer block">
                            <input type="radio"
                                   name="return_method"
                                   value="ambil_toko"
                                   x-model="returnMethod"
                                   class="sr-only">
                            <div class="p-4 rounded-2xl border-2 transition-all h-full flex flex-col justify-between"
                                 :class="returnMethod === 'ambil_toko'
                                    ? 'border-primary-500 bg-primary-50/50 ring-2 ring-primary-100'
                                    : 'border-gray-200 hover:border-gray-300 bg-white'">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="w-7 h-7 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center mb-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                        <h3 class="font-bold text-sm text-gray-900">Antar Langsung ke Toko</h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Serahkan barang secara langsung kepada petugas di alamat fisik toko rental.
                                        </p>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                                          :class="returnMethod === 'ambil_toko' ? 'border-primary-500 bg-primary-500 text-white' : 'border-gray-300'">
                                        <svg x-show="returnMethod === 'ambil_toko'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Konten Kondisional 1: Jika Pilih KURIR --}}
                    <div x-show="returnMethod === 'kurir'" x-transition class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-4">
                        <div>
                            <label for="courier_name" class="block text-xs font-bold text-gray-700 mb-1.5">
                                Pilih Layanan Ekspedisi / Kurir <span class="text-red-500">*</span>
                            </label>
                            <select id="courier_name"
                                    name="courier_name"
                                    x-model="selectedCourier"
                                    :required="returnMethod === 'kurir'"
                                    :disabled="returnMethod !== 'kurir'"
                                    class="w-full text-sm p-2.5 rounded-xl border border-gray-300 bg-white focus:ring-primary-500 focus:border-primary-500 font-medium">
                                <option value="">-- Pilih Kurir Pengembalian --</option>
                                @foreach($couriers as $courier)
                                    <option value="{{ $courier }}">{{ $courier }}</option>
                                @endforeach
                            </select>
                            @error('courier_name')
                                <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tracking_number" class="block text-xs font-bold text-gray-700 mb-1.5">
                                Nomor Resi Retur / Kode Pelacakan <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="tracking_number"
                                   name="tracking_number"
                                   value="{{ old('tracking_number') }}"
                                   :required="returnMethod === 'kurir'"
                                   :disabled="returnMethod !== 'kurir'"
                                   placeholder="Contoh: JNE1234567890 atau Link Live Tracking Gojek/Grab"
                                   class="w-full text-sm p-2.5 rounded-xl border border-gray-300 bg-white focus:ring-primary-500 focus:border-primary-500 font-mono">
                            <p class="text-[11px] text-gray-500 mt-1">
                                Pastikan nomor resi valid dan tidak kosong agar admin toko dapat melacak paket Anda.
                            </p>
                            @error('tracking_number')
                                <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Konten Kondisional 2: Jika Pilih ANTAR LANGSUNG KE TOKO --}}
                    <div x-show="returnMethod === 'ambil_toko'" style="display: none;" x-transition class="p-4 bg-emerald-50/70 rounded-xl border border-emerald-200">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="text-xs sm:text-sm">
                                <h4 class="font-bold text-emerald-950">Alamat Fisik Toko Rental</h4>
                                <p class="text-emerald-900 font-medium mt-1">
                                    {{ $client->address ?? ($order->client->address ?? 'Silakan hubungi admin toko untuk detail titik pengantaran.') }}
                                </p>
                                <p class="text-xs text-emerald-700 mt-1">
                                    Jam Operasional: <b>{{ $client->operational_hours ?? '08:00 - 20:00 WIB' }}</b>
                                    @if(!empty($client->phone))
                                        &bull; Telp/WA: <b>{{ $client->phone }}</b>
                                    @endif
                                </p>
                                <div class="text-[11px] text-emerald-800 mt-2 bg-white/80 px-2.5 py-1.5 rounded-lg border border-emerald-200 inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Anda tidak perlu mengisi nomor resi untuk metode pengantaran langsung ke toko.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- G. CATATAN TAMBAHAN (OPSIONAL) --}}
                <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                        <span>Catatan Tambahan <span class="text-xs font-normal text-gray-400">(Opsional)</span></span>
                    </h2>
                    <p class="text-xs text-gray-500 mb-3">
                        Tuliskan kondisi barang saat dikembalikan, kelengkapan aksesori yang disertakan, atau informasi tambahan untuk admin toko.
                    </p>
                    <textarea name="notes"
                              rows="3"
                              placeholder="Contoh: Seluruh unit lengkap bersama tas dan kabel charger, kondisi fisik aman dan sudah dibersihkan."
                              class="w-full text-sm p-3 rounded-xl border border-gray-300 focus:ring-primary-500 focus:border-primary-500">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </section>

            </div>

            {{-- ================= KOLOM KANAN (STICKY PANEL DENDA & SUBMIT) ================= --}}
            <aside class="space-y-6 lg:sticky lg:top-24">

                {{-- PANEL PERINGATAN KETERLAMBATAN & ESTIMASI DENDA BERJALAN --}}
                @if($isLate && $totalLateFee > 0)
                    <section class="bg-white border-2 border-amber-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-extrabold text-amber-950 leading-tight">Peringatan Keterlambatan Sewa</h2>
                                <span class="text-[11px] text-amber-700 font-semibold">Waktu sewa telah melewati batas akhir</span>
                            </div>
                        </div>

                        {{-- Rincian Keterlambatan & Estimasi Denda Berjalan --}}
                        <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200 text-xs space-y-2">
                            <div class="flex justify-between">
                                <span class="text-amber-800">Tenggat Akhir Sewa:</span>
                                <b class="text-amber-950 font-bold">{{ $order->rental_end_date?->translatedFormat('d M Y, H:i') }} WIB</b>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-amber-800">Durasi Keterlambatan:</span>
                                <b class="text-amber-900 font-bold">
                                    {{ $lateHours }} Jam
                                    @if(($lateFeeData['late_days'] ?? 0) > 0)
                                        ({{ $lateFeeData['late_days'] }} hari {{ $lateFeeData['remaining_hours'] }} jam)
                                    @endif
                                </b>
                            </div>

                            <div class="border-t border-amber-200/70 pt-2 space-y-1.5">
                                <span class="text-[11px] font-bold text-amber-900 block">Rincian Tarif per Barang:</span>
                                @foreach($lateFeeData['items'] as $feeItem)
                                    <div class="flex justify-between text-[11px] text-amber-900">
                                        <span>{{ $feeItem['product_name'] }} ({{ $feeItem['quantity'] }}x &bull; Rp {{ number_format($feeItem['hourly_rate'], 0, ',', '.') }}/jam &times; {{ $feeItem['late_hours'] }} jam)</span>
                                        <b class="font-bold tabular-nums">Rp {{ number_format($feeItem['subtotal_fee'], 0, ',', '.') }}</b>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-amber-200 pt-2.5 flex justify-between items-baseline">
                                <span class="font-bold text-amber-950 text-xs">Estimasi Denda Berjalan:</span>
                                <span class="text-lg font-extrabold text-amber-700 tracking-tight tabular-nums">
                                    Rp {{ number_format($totalLateFee, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Kotak Informasi Edukatif --}}
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 leading-relaxed">
                            <p class="font-bold mb-1">Informasi Keterlambatan Sewa:</p>
                            <p>Silakan segera kirimkan perlengkapan sewa ke toko. Tagihan denda resmi akan diterbitkan oleh Admin Toko setelah barang fisik tiba dan diperiksa. Anda dapat melunasi denda melalui notifikasi yang akan masuk ke akun Anda.</p>
                        </div>
                    </section>
                @else
                    {{-- Jika TIDAK Terlambat --}}
                    <section class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-xs sm:text-sm text-emerald-900">
                                <h2 class="font-bold text-emerald-950">Pengembalian Tepat Waktu</h2>
                                <p class="mt-1 leading-relaxed">
                                    Anda mengajukan pengembalian sebelum batas waktu sewa berakhir. <b>Tidak ada tagihan denda keterlambatan (Rp 0).</b>
                                </p>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- H. TOMBOL SUBMIT --}}
                <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-3">
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-primary-600'"
                            class="w-full py-3.5 px-4 bg-primary-500 text-white font-extrabold rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                        <span x-show="!submitting">Konfirmasi Pengembalian Barang</span>
                        <span x-show="submitting" style="display: none;">Memproses Pengajuan...</span>
                    </button>

                    <a href="{{ $backUrl }}"
                       class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center">
                        Batal & Kembali
                    </a>
                </section>

            </aside>

        </div>
    </form>
</div>
@endsection
