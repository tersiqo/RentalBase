@extends('layouts.customer')

@section('content')
@php
    $itemsList = $items->values();
    $shopLabel = $client->business_name ?? 'Outlet Rental';
@endphp
<div class="max-w-[1260px] mx-auto px-6 py-8" x-data="{
    currentStep: 1,
    periodStart: {{ json_encode(old('start_date', $period['start_date'])) }},
    periodEnd: {{ json_encode(old('end_date', $period['end_date'])) }},
    items: {{ json_encode($itemsList->all()) }},
    ship: 'antar',
    shopName: {{ json_encode($shopLabel) }},
    addressText: {{ json_encode(old('shipping_address', '')) }},
    agree: false,

    ktpPreview: null,
    ktpError: '',
    selfiePreview: null,
    selfieError: '',

    get durationDays() {
        if (!this.periodStart || !this.periodEnd) return 1;
        const start = new Date(this.periodStart);
        const end = new Date(this.periodEnd);
        if (end < start) return 1;
        const diffTime = Math.abs(end - start);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    },

    get shippingAddress() {
        return this.ship === 'ambil'
            ? 'Ambil di toko — ' + this.shopName
            : this.addressText;
    },

    rp(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    },

    itemSubtotal(item) {
        return item.price * this.durationDays * item.quantity;
    },

    get grandTotal() {
        return this.items.reduce((sum, item) => sum + this.itemSubtotal(item), 0);
    },

    changeQty(item, delta) {
        const max = item.available_units || 99;
        item.quantity = Math.min(max, Math.max(1, item.quantity + delta));
    },

    handleFileUpload(event, type) {
        const file = event.target.files[0];
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
            if (type === 'ktp') this.ktpError = 'Format file harus JPG atau PNG.';
            if (type === 'selfie') this.selfieError = 'Format file harus JPG atau PNG.';
            event.target.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            if (type === 'ktp') this.ktpError = 'Ukuran file maksimal 2MB.';
            if (type === 'selfie') this.selfieError = 'Ukuran file maksimal 2MB.';
            event.target.value = '';
            return;
        }

        if (type === 'ktp') this.ktpError = '';
        if (type === 'selfie') this.selfieError = '';

        const reader = new FileReader();
        reader.onload = (e) => {
            if (type === 'ktp') this.ktpPreview = e.target.result;
            if (type === 'selfie') this.selfiePreview = e.target.result;
        };
        reader.readAsDataURL(file);
    },

    removeFile(type) {
        if (type === 'ktp') {
            this.ktpPreview = null;
            document.getElementById('identity_document_image').value = '';
        } else if (type === 'selfie') {
            this.selfiePreview = null;
            document.getElementById('face_image').value = '';
        }
    }
}">

    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm font-medium text-gray-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain]) }}" class="hover:text-primary-600 transition">Katalog</a>
            </li>
            <li><span class="text-gray-300">/</span></li>
            <li>
                <a href="{{ route('customer.cart', ['subdomain' => $client->subdomain]) }}" class="hover:text-primary-600 transition">Keranjang</a>
            </li>
            <li><span class="text-gray-300">/</span></li>
            <li class="text-gray-900 font-bold">Checkout</li>
        </ol>
    </nav>

    <!-- Header Title -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Checkout Pesanan</h1>
        <p class="text-sm text-gray-500 mt-1">Lengkapi rincian sewa, alamat, dan verifikasi jaminan identitas Anda.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm">
            <div class="font-bold mb-1">Terjadi kesalahan input:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Stepper Navigation -->
    <div class="mb-8 bg-white border border-slate-100 rounded-3xl p-5 sm:p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.07)]">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <!-- Step 1 -->
            <button type="button" @click="currentStep = 1" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 1 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">1</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 1 ? 'text-primary-500' : 'text-slate-900'">Ringkasan Pesanan</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">Produk &amp; periode sewa</span>
                </div>
            </button>

            <!-- Step 2 -->
            <button type="button" @click="currentStep = 2" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 2 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">2</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 2 ? 'text-primary-500' : 'text-slate-900'">Pengiriman &amp; Kontak</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">Antar atau ambil</span>
                </div>
            </button>

            <!-- Step 3 -->
            <button type="button" @click="currentStep = 3" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 3 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">3</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 3 ? 'text-primary-500' : 'text-slate-900'">Identitas &amp; Jaminan</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">KTP &amp; swafoto</span>
                </div>
            </button>

            <!-- Step 4 -->
            <button type="button" @click="currentStep = 4" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 4 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">4</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 4 ? 'text-primary-500' : 'text-slate-900'">Konfirmasi Final</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">Total &amp; setuju</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('customer.checkout.store', ['subdomain' => $client->subdomain]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-8 items-start">

            <!-- Left Form Content -->
            <div class="space-y-6">

                <!-- Hidden item selections -->
                <template x-for="(item, idx) in items" :key="item.hash">
                    <div class="hidden">
                        <input type="hidden" name="selected_hashes[]" :value="item.hash">
                        <input type="hidden" :name="'items[' + item.hash + '][quantity]'" x-model.number="item.quantity">
                    </div>
                </template>

                <!-- STEP 1: Ringkasan Pesanan & Periode -->
                <div x-show="currentStep === 1" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <!-- Card Header -->
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 3h3l2.5 12h11L21 7H6"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Ringkasan Pesanan &amp; Periode</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Periksa produk dan atur tanggal mulai &amp; selesai sewa.</p>
                        </div>
                    </div>

                    <!-- Item list -->
                    <div class="space-y-4">
                        <template x-for="(item, idx) in items" :key="item.hash">
                            <div class="grid grid-cols-[78px_1fr_auto] gap-4 items-center border border-gray-100 rounded-2xl p-3.5 bg-gray-50/50">
                                <div class="w-[78px] h-[78px] rounded-xl bg-white border border-gray-200/80 overflow-hidden flex-none flex items-center justify-center relative">
                                    <template x-if="item.main_image">
                                        <img :src="'/storage/' + item.main_image" :alt="item.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.main_image">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </template>
                                </div>
                                <!-- Middle Details -->
                                <div class="min-w-0">
                                    <h4 class="text-[15px] font-bold text-gray-900 truncate leading-snug" x-text="item.name"></h4>
                                    <div class="text-xs font-semibold text-gray-400 mt-0.5" x-text="rp(item.price) + ' / hari · unit tersedia'"></div>
                                    <!-- Qty buttons -->
                                    <div class="inline-flex items-center gap-1 border border-gray-200/80 rounded-xl p-1 mt-2 bg-white">
                                        <button type="button" @click="changeQty(item, -1)" aria-label="Kurangi" class="w-7 h-7 rounded-lg bg-gray-50 hover:bg-primary-50 hover:text-primary-600 text-gray-800 font-extrabold flex items-center justify-center transition-colors text-sm">-</button>
                                        <span class="min-w-[32px] text-center font-bold text-gray-900 text-sm" x-text="item.quantity"></span>
                                        <button type="button" @click="changeQty(item, 1)" aria-label="Tambah" class="w-7 h-7 rounded-lg bg-gray-50 hover:bg-primary-50 hover:text-primary-600 text-gray-800 font-extrabold flex items-center justify-center transition-colors text-sm">+</button>
                                    </div>
                                </div>
                                <!-- Right Price -->
                                <div class="text-right">
                                    <b class="text-[15px] font-extrabold text-gray-900 block" x-text="rp(itemSubtotal(item))"></b>
                                    <span class="text-xs font-semibold text-gray-400 block mt-0.5" x-text="item.quantity + ' unit × ' + durationDays + ' hari'"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Date & Duration Section -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-gray-800 mb-2 uppercase tracking-wide">Periode Sewa <span class="text-primary-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr_auto] gap-3 items-end">
                            <div>
                                <label for="start_date" class="block text-[12.5px] font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                                <input type="date" id="start_date" name="start_date" x-model="periodStart" min="{{ date('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500 bg-white" required>
                            </div>
                            <div class="hidden sm:flex items-center justify-center text-gray-400 pb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </div>
                            <div>
                                <label for="end_date" class="block text-[12.5px] font-semibold text-gray-600 mb-1">Tanggal Selesai</label>
                                <input type="date" id="end_date" name="end_date" x-model="periodEnd" :min="periodStart" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500 bg-white" required>
                            </div>
                            <!-- Durbox -->
                            <div class="bg-primary-50/80 border border-primary-200/80 rounded-xl px-4 py-2 text-center min-w-[96px]">
                                <small class="text-[10px] font-bold text-primary-700 tracking-wider block uppercase">DURASI</small>
                                <b class="text-2xl font-extrabold text-primary-600 block leading-tight" x-text="durationDays"></b>
                                <small class="text-[10px] font-bold text-[#9A3412] tracking-wider block uppercase">HARI</small>
                            </div>
                        </div>
                        <span class="block text-[12.5px] text-gray-400 font-medium mt-2">Perhitungan otomatis: (Tanggal Selesai − Tanggal Mulai) × harga/hari. Minimal 1 hari.</span>
                    </div>

                    <!-- Notice Info Box -->
                    <div class="bg-primary-50/70 border border-primary-100 rounded-2xl p-4 flex gap-3.5 items-start">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-primary-500 shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8h.01M11 12h1v4h1"/></svg>
                        </div>
                        <div class="text-xs leading-relaxed">
                            <b class="block text-[#7C2D12] text-[13.5px] font-bold mb-0.5">Subtotal dihitung otomatis</b>
                            <p class="text-[#9A3412]">Subtotal = Σ (harga per hari × jumlah unit) × durasi hari. Lihat rincian pada panel kanan.</p>
                        </div>
                    </div>

                    <!-- Footer Navigation -->
                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="currentStep = 2" class="inline-flex items-center gap-2 rounded-xl font-bold px-6 py-3 bg-primary-500 text-white shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] hover:bg-primary-600 active:scale-95 transition text-sm cursor-pointer">
                            Lanjut ke Pengiriman
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Pengiriman & Kontak -->
                <div x-show="currentStep === 2" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Pengiriman &amp; Kontak</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Pilih cara menerima barang dan masukkan kontak penerima.</p>
                        </div>
                    </div>

                    <!-- Delivery method toggle -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Metode Penerimaan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" @click="ship = 'antar'" class="flex items-start gap-3 p-4 rounded-xl border-2 text-left transition" :class="ship === 'antar' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                                <span class="w-5 h-5 rounded-full border-2 flex-none mt-0.5 flex items-center justify-center" :class="ship === 'antar' ? 'border-primary-500' : 'border-gray-300'">
                                    <span x-show="ship === 'antar'" class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                                </span>
                                <span>
                                    <b class="block text-sm text-gray-900">Dikirim ke Alamat</b>
                                    <span class="block text-xs text-gray-500 mt-0.5 leading-relaxed">Diantar kurir pihak ketiga ke lokasi Anda (ongkir terpisah).</span>
                                </span>
                            </button>
                            <button type="button" @click="ship = 'ambil'" class="flex items-start gap-3 p-4 rounded-xl border-2 text-left transition" :class="ship === 'ambil' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                                <span class="w-5 h-5 rounded-full border-2 flex-none mt-0.5 flex items-center justify-center" :class="ship === 'ambil' ? 'border-primary-500' : 'border-gray-300'">
                                    <span x-show="ship === 'ambil'" class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                                </span>
                                <span>
                                    <b class="block text-sm text-gray-900">Ambil di Outlet</b>
                                    <span class="block text-xs text-gray-500 mt-0.5 leading-relaxed">Datang langsung ke outlet penyewa, gratis.</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden shipping address (server always expects it) -->
                    <input type="hidden" name="shipping_address" :value="shippingAddress">

                    <!-- Antar mode -->
                    <div x-show="ship === 'antar'" class="space-y-4">
                        <div>
                            <label for="addressText" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Alamat Pengiriman Lengkap <span class="text-red-500">*</span></label>
                            <textarea id="addressText" x-model="addressText" rows="3" class="w-full border border-gray-200 rounded-xl p-3.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos"></textarea>
                            @error('shipping_address')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="patokan" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Patokan Lokasi <span class="text-gray-400 font-medium normal-case">(opsional, memudahkan kurir)</span></label>
                            <input type="text" id="patokan" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="Contoh: sebelah minimarket, pagar hitam, gang setelah masjid">
                        </div>
                        <div>
                            <label for="wa" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nomor WhatsApp / Telepon Aktif Penerima <span class="text-red-500">*</span></label>
                            <input type="tel" id="wa" inputmode="tel" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="bg-primary-50/70 border border-primary-100 rounded-xl p-4 flex gap-3">
                            <svg class="w-5 h-5 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"></path></svg>
                            <div class="text-xs text-[#7C2D12] leading-relaxed">
                                <b>Biaya kirim dibayar terpisah.</b> Ongkos kirim tidak dihitung di sini; kurir akan menghubungi Anda via WhatsApp untuk konfirmasi biaya dan jadwal.
                            </div>
                        </div>
                    </div>

                    <!-- Ambil mode -->
                    <div x-show="ship === 'ambil'" class="space-y-4">
                        <div class="bg-primary-50/70 border border-primary-100 rounded-xl p-4 flex gap-3 items-start">
                            <svg class="w-5 h-5 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9l1-5h16l1 5M4 9v11h16V9M9 20v-6h6v6"></path></svg>
                            <div class="text-xs text-[#7C2D12] leading-relaxed">
                                <b>{{ $shopLabel }}</b>
                                <p class="mt-0.5 text-gray-500">Rincian alamat outlet akan dikonfirmasi admin via WhatsApp setelah pesanan diterima.</p>
                            </div>
                        </div>
                        <div>
                            <label for="wa2" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nomor WhatsApp / Telepon Aktif Penerima <span class="text-red-500">*</span></label>
                            <input type="tel" id="wa2" inputmode="tel" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="08xxxxxxxxxx">
                        </div>
                        <p class="text-xs text-gray-400">Tunjukkan nomor pesanan saat mengambil barang di outlet.</p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between">
                        <button type="button" @click="currentStep = 1" class="inline-flex items-center gap-2 rounded-xl font-bold px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            &larr; Kembali
                        </button>
                        <button type="button" @click="currentStep = 3" class="inline-flex items-center gap-2 rounded-xl font-bold px-6 py-2.5 bg-primary-500 text-white shadow-sm hover:bg-primary-600 transition">
                            Lanjut ke Jaminan Identitas &rarr;
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Identitas dan Jaminan Penyewa -->
                <div x-show="currentStep === 3" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="9" cy="11" r="2.2"/><path d="M14 10h4M14 14h4M5.5 16c.8-1.6 2-2.3 3.5-2.3s2.7.7 3.5 2.3"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Identitas &amp; Jaminan Penyewa</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Data sesuai KTP digunakan sebagai jaminan sewa tanpa deposit uang.</p>
                        </div>
                    </div>

                    <!-- Notice Box -->
                    <div class="bg-primary-50 border border-primary-100 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="text-xs text-[#7C2D12]">
                            <b>Ketentuan Verifikasi:</b> Data identitas Anda dijamin aman dan hanya digunakan oleh admin rental untuk memverifikasi kelayakan penyewaan sebelum instruksi pembayaran dibuka.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="full_name" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nama Lengkap (Sesuai KTP) <span class="text-red-500">*</span></label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name', auth()->user()->name ?? '') }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500" placeholder="Nama sesuai identitas" required>
                            @error('full_name')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="identity_number" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nomor KTP / NIK <span class="text-red-500">*</span></label>
                            <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number') }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500" placeholder="16 digit nomor NIK KTP" required>
                            @error('identity_number')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="identity_address" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Alamat Sesuai KTP <span class="text-red-500">*</span></label>
                        <textarea id="identity_address" name="identity_address" rows="2" class="w-full border border-gray-200 rounded-xl p-3 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500" placeholder="Alamat domisili sesuai KTP..." required>{{ old('identity_address') }}</textarea>
                        @error('identity_address')
                            <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Document Uploads -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <!-- Upload KTP -->
                        <div class="border border-dashed border-gray-300 rounded-2xl p-4 text-center bg-gray-50/50">
                            <label class="block text-xs font-bold text-gray-800 mb-2 uppercase">Foto KTP / Dokumen Identitas <span class="text-red-500">*</span></label>

                            <template x-if="!ktpPreview">
                                <div class="space-y-2 py-3">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <label for="identity_document_image" class="cursor-pointer bg-white border border-gray-200 text-xs font-bold text-gray-700 px-3 py-1.5 rounded-lg inline-block hover:border-primary-500 shadow-2xs">Pilih File KTP</label>
                                    <input type="file" id="identity_document_image" name="identity_document_image" accept="image/jpeg,image/png,image/jpg" @change="handleFileUpload($event, 'ktp')" class="hidden">
                                    <p class="text-[11px] text-gray-400">JPG/PNG, Maks. 2MB</p>
                                </div>
                            </template>

                            <template x-if="ktpPreview">
                                <div class="relative">
                                    <img :src="ktpPreview" class="h-32 w-full object-cover rounded-xl border border-gray-200 mb-2">
                                    <button type="button" @click="removeFile('ktp')" class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-lg hover:bg-red-700 transition">Hapus / Ganti KTP</button>
                                </div>
                            </template>

                            <p x-text="ktpError" class="text-xs text-red-600 mt-1 font-semibold"></p>
                            @error('identity_document_image')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Upload Selfie -->
                        <div class="border border-dashed border-gray-300 rounded-2xl p-4 text-center bg-gray-50/50">
                            <label class="block text-xs font-bold text-gray-800 mb-2 uppercase">Foto Swafoto (Selfie + KTP) <span class="text-red-500">*</span></label>

                            <template x-if="!selfiePreview">
                                <div class="space-y-2 py-3">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                    <label for="face_image" class="cursor-pointer bg-white border border-gray-200 text-xs font-bold text-gray-700 px-3 py-1.5 rounded-lg inline-block hover:border-primary-500 shadow-2xs">Pilih File Swafoto</label>
                                    <input type="file" id="face_image" name="face_image" accept="image/jpeg,image/png,image/jpg" @change="handleFileUpload($event, 'selfie')" class="hidden">
                                    <p class="text-[11px] text-gray-400">JPG/PNG, Maks. 2MB</p>
                                </div>
                            </template>

                            <template x-if="selfiePreview">
                                <div class="relative">
                                    <img :src="selfiePreview" class="h-32 w-full object-cover rounded-xl border border-gray-200 mb-2">
                                    <button type="button" @click="removeFile('selfie')" class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-lg hover:bg-red-700 transition">Hapus / Ganti Swafoto</button>
                                </div>
                            </template>

                            <p x-text="selfieError" class="text-xs text-red-600 mt-1 font-semibold"></p>
                            @error('face_image')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between">
                        <button type="button" @click="currentStep = 2" class="inline-flex items-center gap-2 rounded-xl font-bold px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            &larr; Kembali
                        </button>
                        <button type="button" @click="currentStep = 4" class="inline-flex items-center gap-2 rounded-xl font-bold px-6 py-2.5 bg-primary-500 text-white shadow-sm hover:bg-primary-600 transition">
                            Lanjut ke Konfirmasi &rarr;
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Konfirmasi & Persetujuan -->
                <div x-show="currentStep === 4" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Konfirmasi Final</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Tinjau kembali detail pesanan sebelum dikirim ke admin.</p>
                        </div>
                    </div>

                    <!-- Summary Verification -->
                    <div class="space-y-3 bg-gray-50 rounded-xl p-4 text-xs font-semibold text-gray-700">
                        <template x-for="(item, idx) in items" :key="item.hash">
                            <div class="flex justify-between border-b border-gray-200/60 pb-2 last:border-b-0 last:pb-0">
                                <span class="text-gray-500" x-text="item.name + ' × ' + item.quantity"></span>
                                <span class="text-gray-900 font-bold" x-text="rp(itemSubtotal(item))"></span>
                            </div>
                        </template>
                        <div class="flex justify-between border-b border-gray-200/60 pb-2">
                            <span class="text-gray-500">Periode Sewa:</span>
                            <span class="text-gray-900" x-text="durationDays + ' hari (' + periodStart + ' s/d ' + periodEnd + ')'"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-200/60 pb-2">
                            <span class="text-gray-500">Metode Penerimaan:</span>
                            <span class="text-gray-900" x-text="ship === 'ambil' ? 'Ambil di outlet' : 'Dikirim ke alamat'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Biaya Sewa:</span>
                            <span class="text-primary-600 font-extrabold text-sm" x-text="rp(grandTotal)"></span>
                        </div>
                    </div>

                    <!-- Verification notice -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="text-xs text-amber-900 font-medium leading-relaxed">
                            Pesanan akan berstatus <b>Menunggu Konfirmasi</b>. Pembayaran dibuka setelah verifikasi KTP disetujui Admin (est. 1×24 jam kerja).
                        </div>
                    </div>

                    <!-- Terms Agreement Checkbox -->
                    <div class="bg-primary-50/60 border border-primary-100 rounded-xl p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" x-model="agree" class="mt-1 rounded text-primary-500 focus:ring-primary-500" required>
                            <span class="text-xs text-[#7C2D12] font-medium leading-relaxed">
                                Saya menyatakan bahwa seluruh data identitas yang diunggah adalah sah dan benar. Saya menyetujui seluruh <b>Syarat & Ketentuan Sewa RentalBase</b> serta bersedia mematuhi aturan penanganan peralatan yang berlaku.
                            </span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
                        <button type="button" @click="currentStep = 3" class="inline-flex items-center gap-2 rounded-xl font-bold px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            &larr; Kembali
                        </button>
                        <button type="submit" :disabled="!agree" class="inline-flex items-center justify-center gap-2 rounded-xl font-extrabold px-7 py-3 bg-primary-500 text-white shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] hover:bg-primary-600 active:scale-95 transition text-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            Buat Pesanan & Kirim Verifikasi &rarr;
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sticky Order Summary Card -->
            <div class="lg:sticky lg:top-[90px]">
                <div class="bg-gradient-to-b from-primary-500 to-primary-600 rounded-t-2xl px-6 py-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-white flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"></path></svg>
                    <h3 class="text-base font-extrabold text-white">Ringkasan Biaya</h3>
                </div>
                <div class="bg-white border border-t-0 border-slate-100/80 rounded-b-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-4">
                    <div class="space-y-2.5 text-xs font-medium text-gray-600">
                        <template x-for="(item, idx) in items" :key="item.hash">
                            <div class="flex justify-between gap-2">
                                <span class="truncate" x-text="item.name + ' ×' + item.quantity"></span>
                                <span class="font-bold text-gray-900 shrink-0" x-text="rp(itemSubtotal(item))"></span>
                            </div>
                        </template>
                        <div class="flex justify-between">
                            <span>Durasi Sewa</span>
                            <span class="font-bold text-gray-900" x-text="durationDays + ' hari'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Ongkos Kirim</span>
                            <span class="font-bold" :class="ship === 'ambil' ? 'text-emerald-600' : 'text-gray-400'" x-text="ship === 'ambil' ? 'Gratis' : 'Dihitung kurir'"></span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold text-gray-800">Total Akhir</span>
                            <span class="text-xl font-extrabold text-primary-600" x-text="rp(grandTotal)"></span>
                        </div>
                    </div>

                    <div class="bg-primary-50 rounded-xl p-3 text-[11px] text-[#7C2D12] leading-relaxed border border-primary-100">
                        * Pembayaran dapat dilakukan via Transfer Bank / QRIS setelah status jaminan identitas diverifikasi oleh admin toko.
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection