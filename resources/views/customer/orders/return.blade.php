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
        returnMethod: '{{ old('return_method', 'kurir') }}',
        selectedCourier: '{{ old('courier_name', '') }}',
        ptab: 'bank',
        previewUrl: null,
        fileName: '',
        fileError: '',
        submitting: false,
        toastMessage: '',
        showToast: false,

        handleFileChange(event) {
            this.fileError = '';
            const file = event.target.files[0];
            if (!file) {
                this.previewUrl = null;
                this.fileName = '';
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                this.fileError = 'Format file harus berupa JPG atau PNG.';
                event.target.value = '';
                this.previewUrl = null;
                this.fileName = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                this.fileError = 'Ukuran file terlalu besar (maksimal 2 MB).';
                event.target.value = '';
                this.previewUrl = null;
                this.fileName = '';
                return;
            }

            this.fileName = file.name;
            this.previewUrl = URL.createObjectURL(file);
        },

        clearFile(inputRef) {
            this.previewUrl = null;
            this.fileName = '';
            this.fileError = '';
            if (inputRef) inputRef.value = '';
        },

        copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
                this.toastMessage = 'Nomor rekening disalin: ' + text;
                this.showToast = true;
                setTimeout(() => { this.showToast = false; }, 3000);
            }
        }
     }">

    {{-- Breadcrumb & Header Halaman --}}
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 mb-2">
            <a href="{{ route('customer.orders') }}" class="hover:text-orange-600 transition">Pesanan Saya</a>
            <span class="text-gray-300">/</span>
            <a href="{{ $backUrl }}" class="hover:text-orange-600 transition font-mono">{{ $order->order_number ?? ('ORD-' . $order->id) }}</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-800 font-semibold">Ajukan Pengembalian</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Form Pengembalian Barang</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $order->status_color }}">
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
            <span class="text-lg shrink-0">⚠️</span>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 font-bold text-sm mb-1.5">
                <span>❌</span>
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
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">📦</span>
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
                            <b class="text-orange-600 font-bold block">{{ $order->date_range }}</b>
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
                                    ? (str_starts_with($product->main_image, 'http') ? $product->main_image : Storage::url($product->main_image))
                                    : 'https://placehold.co/100x100/f3f4f6/9ca3af?text=Produk';
                            @endphp
                            <div class="flex items-center justify-between p-3.5 bg-gray-50/60 border border-gray-100 rounded-xl gap-4">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <img src="{{ $img }}"
                                         alt="{{ $product?->name ?? 'Produk' }}"
                                         class="w-14 h-14 rounded-xl object-cover bg-white border border-gray-200 shrink-0">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-gray-900 text-sm truncate">{{ $product?->name ?? 'Peralatan Sewa' }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Jumlah: <b class="text-gray-800">{{ $item->quantity ?? 1 }} unit</b>
                                            &bull; Tarif Denda: <span class="font-mono text-gray-700">Rp {{ number_format($effectiveLateFee, 0, ',', '.') }}/jam</span>
                                        </p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 font-bold text-xs shrink-0">
                                    {{ $item->quantity ?? 1 }}x Unit
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- B. PANDUAN DAN SYARAT PENGEMBALIAN --}}
                <section class="bg-blue-50/60 border border-blue-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-blue-950 flex items-center gap-2.5 mb-3">
                        <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm">📋</span>
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
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">🚚</span>
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
                                    ? 'border-orange-500 bg-orange-50/50 ring-2 ring-orange-100'
                                    : 'border-gray-200 hover:border-gray-300 bg-white'">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <span class="text-lg block mb-1">🛵</span>
                                        <h3 class="font-bold text-sm text-gray-900">Kirim via Ekspedisi / Kurir</h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Kirim barang melalui JNE, J&T, SiCepat, Gojek, atau Grab dan masukkan nomor resi pengiriman.
                                        </p>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                                          :class="returnMethod === 'kurir' ? 'border-orange-500 bg-orange-500 text-white' : 'border-gray-300'">
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
                                    ? 'border-orange-500 bg-orange-50/50 ring-2 ring-orange-100'
                                    : 'border-gray-200 hover:border-gray-300 bg-white'">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <span class="text-lg block mb-1">🏪</span>
                                        <h3 class="font-bold text-sm text-gray-900">Antar Langsung ke Toko</h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Serahkan barang secara langsung kepada petugas di alamat fisik toko rental.
                                        </p>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                                          :class="returnMethod === 'ambil_toko' ? 'border-orange-500 bg-orange-500 text-white' : 'border-gray-300'">
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
                                    class="w-full text-sm p-2.5 rounded-xl border border-gray-300 bg-white focus:ring-orange-500 focus:border-orange-500 font-medium">
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
                                   class="w-full text-sm p-2.5 rounded-xl border border-gray-300 bg-white focus:ring-orange-500 focus:border-orange-500 font-mono">
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
                            <span class="text-xl shrink-0">📍</span>
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
                                <p class="text-[11px] text-emerald-800 mt-2 bg-white/80 px-2.5 py-1.5 rounded-lg border border-emerald-200 inline-block">
                                    💡 Anda tidak perlu mengisi nomor resi untuk metode pengantaran langsung ke toko.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- G. CATATAN TAMBAHAN (OPSIONAL) --}}
                <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">📝</span>
                        <span>Catatan Tambahan <span class="text-xs font-normal text-gray-400">(Opsional)</span></span>
                    </h2>
                    <p class="text-xs text-gray-500 mb-3">
                        Tuliskan kondisi barang saat dikembalikan, kelengkapan aksesori yang disertakan, atau informasi tambahan untuk admin toko.
                    </p>
                    <textarea name="notes"
                              rows="3"
                              placeholder="Contoh: Seluruh unit lengkap bersama tas dan kabel charger, kondisi fisik aman dan sudah dibersihkan."
                              class="w-full text-sm p-3 rounded-xl border border-gray-300 focus:ring-orange-500 focus:border-orange-500">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </section>

            </div>

            {{-- ================= KOLOM KANAN (STICKY PANEL DENDA & SUBMIT) ================= --}}
            <aside class="space-y-6 lg:sticky lg:top-24">

                {{-- D, E, F. PANEL DENDA KETERLAMBATAN & UPLOAD BUKTI --}}
                @if($isLate && $totalLateFee > 0)
                    <section class="bg-white border-2 border-red-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex items-center gap-2.5 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-sm">⏰</span>
                            <div>
                                <h2 class="text-base font-extrabold text-red-900 leading-tight">Tagihan Denda Keterlambatan</h2>
                                <span class="text-[11px] text-red-600 font-semibold">Waktu sewa telah melewati batas akhir</span>
                            </div>
                        </div>

                        {{-- Rincian Keterlambatan --}}
                        <div class="p-3.5 bg-red-50 rounded-xl border border-red-100 text-xs space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-red-800">Tenggat Akhir Sewa:</span>
                                <b class="text-red-950 font-mono">{{ $order->rental_end_date?->translatedFormat('d M Y, H:i') }} WIB</b>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-800">Durasi Keterlambatan:</span>
                                <b class="text-red-700 font-bold">
                                    {{ $lateHours }} Jam
                                    @if(($lateFeeData['late_days'] ?? 0) > 0)
                                        ({{ $lateFeeData['late_days'] }} hari {{ $lateFeeData['remaining_hours'] }} jam)
                                    @endif
                                </b>
                            </div>

                            <div class="border-t border-red-200/70 pt-2 space-y-1.5">
                                <span class="text-[11px] font-bold text-red-900 block">Rincian Tarif per Barang:</span>
                                @foreach($lateFeeData['items'] as $feeItem)
                                    <div class="flex justify-between text-[11px] text-red-900">
                                        <span>{{ $feeItem['product_name'] }} ({{ $feeItem['quantity'] }}x &bull; Rp {{ number_format($feeItem['hourly_rate'], 0, ',', '.') }}/jam &times; {{ $feeItem['late_hours'] }} jam)</span>
                                        <b class="font-mono">Rp {{ number_format($feeItem['subtotal_fee'], 0, ',', '.') }}</b>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-red-200 pt-2.5 flex justify-between items-baseline">
                                <span class="font-bold text-red-950 text-xs">Total Nominal Denda:</span>
                                <span class="text-lg font-extrabold font-mono text-red-700">
                                    Rp {{ number_format($totalLateFee, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- E. Informasi Rekening Bank / QRIS Toko untuk Pembayaran Denda --}}
                        <div class="mb-4">
                            <h3 class="text-xs font-bold text-gray-800 mb-2">Metode Pembayaran Denda Toko:</h3>

                            <div class="flex bg-gray-100 p-1 rounded-xl mb-3 text-xs font-bold">
                                <button type="button" @click="ptab = 'bank'" :class="ptab === 'bank' ? 'bg-white text-orange-600 shadow-xs' : 'text-gray-500 hover:text-gray-900'" class="flex-1 py-1.5 rounded-lg transition-all text-center">
                                    Transfer Bank
                                </button>
                                <button type="button" @click="ptab = 'qris'" :class="ptab === 'qris' ? 'bg-white text-orange-600 shadow-xs' : 'text-gray-500 hover:text-gray-900'" class="flex-1 py-1.5 rounded-lg transition-all text-center">
                                    QRIS
                                </button>
                            </div>

                            {{-- Tab Transfer Bank --}}
                            <div x-show="ptab === 'bank'" class="space-y-2">
                                @php
                                    $allMethods = ($client && method_exists($client, 'clientPaymentMethods') && $client->clientPaymentMethods()->where('is_active', true)->exists())
                                        ? $client->clientPaymentMethods()->where('is_active', true)->get()
                                        : collect($client?->paymentMethods ?? []);
                                    $bankMethods = $allMethods->filter(fn($m) => !empty($m->account_number) && (!isset($m->type) || $m->type !== 'qris') && !str_starts_with((string)$m->account_number, 'QRIS-'));
                                @endphp
                                @forelse($bankMethods as $method)
                                    <div class="p-3 border border-gray-200 rounded-xl flex items-center justify-between gap-2 bg-gray-50/70">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">{{ $method->bank_name }}</span>
                                                <b class="font-mono text-gray-900 text-xs tracking-wider">{{ $method->account_number }}</b>
                                            </div>
                                            <small class="text-[11px] text-gray-500 block mt-0.5">a.n. {{ $method->account_holder ?? $method->account_name }}</small>
                                        </div>
                                        <button type="button"
                                                @click="copyToClipboard('{{ str_replace(' ', '', $method->account_number) }}')"
                                                class="px-2.5 py-1 text-[11px] font-bold text-orange-600 bg-white hover:bg-orange-50 rounded-lg border border-orange-200 transition shrink-0">
                                            Salin
                                        </button>
                                    </div>
                                @empty
                                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                        Rekening bank toko belum diatur di database. Silakan gunakan QRIS atau hubungi admin toko untuk instruksi pembayaran denda.
                                    </div>
                                @endforelse
                            </div>

                            {{-- Tab QRIS --}}
                            <div x-show="ptab === 'qris'" style="display: none;" class="text-center p-3 border border-gray-200 rounded-xl bg-gray-50/70">
                                @php
                                    $qrisMethod = $allMethods->first(fn($m) => !empty($m->qris_image));
                                    $qrisImg = $qrisMethod?->qris_image
                                        ? (str_starts_with($qrisMethod->qris_image, 'http') ? $qrisMethod->qris_image : Storage::url($qrisMethod->qris_image))
                                        : null;
                                @endphp
                                @if($qrisImg)
                                    <img src="{{ $qrisImg }}" alt="QRIS Toko" class="w-40 h-40 mx-auto object-contain mb-2 rounded-lg bg-white p-1 border border-gray-200">
                                    <span class="text-xs font-bold text-gray-900 block">{{ $client->nama_usaha ?? 'QRIS Toko' }}</span>
                                @else
                                    <p class="text-xs text-gray-500 py-4">Gambar QRIS toko belum tersedia. Silakan gunakan metode Transfer Bank.</p>
                                @endif
                            </div>
                        </div>

                        {{-- F. Upload Bukti Pembayaran Denda --}}
                        <div class="pt-3 border-t border-gray-100">
                            <label for="late_fee_proof" class="block text-xs font-bold text-gray-800 mb-1">
                                Unggah Bukti Transfer Denda (JPG/PNG, Maks. 2 MB) <span class="text-red-500">*</span>
                            </label>
                            <input type="file"
                                   id="late_fee_proof"
                                   name="late_fee_proof"
                                   x-ref="feeInput"
                                   accept="image/jpeg,image/jpg,image/png"
                                   required
                                   @change="handleFileChange($event)"
                                   class="w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-500 file:text-white hover:file:bg-orange-600 cursor-pointer">

                            {{-- Pesan error validasi frontend / backend --}}
                            <p x-show="fileError" x-text="fileError" class="text-xs text-red-600 mt-1.5 font-medium" style="display: none;"></p>
                            @error('late_fee_proof')
                                <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror

                            {{-- Preview Foto Sebelum Dikirim --}}
                            <div x-show="previewUrl" style="display: none;" class="mt-3 p-2.5 bg-gray-50 rounded-xl border border-gray-200">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[11px] font-bold text-gray-700 truncate" x-text="fileName"></span>
                                    <button type="button" @click="clearFile($refs.feeInput)" class="text-[11px] text-red-600 hover:underline font-bold">
                                        Hapus
                                    </button>
                                </div>
                                <img :src="previewUrl" alt="Preview Bukti Denda" class="max-h-44 w-auto mx-auto rounded-lg object-contain bg-white border border-gray-100">
                            </div>

                            <p class="text-[11px] text-gray-500 mt-2 leading-relaxed">
                                ℹ️ Bukti transfer yang diunggah akan berstatus <b>Menunggu Verifikasi</b> sampai diperiksa oleh Admin Toko.
                            </p>
                        </div>
                    </section>
                @else
                    {{-- Jika TIDAK Terlambat --}}
                    <section class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="text-xl shrink-0">✅</span>
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
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-orange-600'"
                            class="w-full py-3.5 px-4 bg-orange-500 text-white font-extrabold rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2">
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

    {{-- Toast Notification untuk Copy Rekening --}}
    <div x-show="showToast"
         x-transition
         style="display: none;"
         class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white text-xs sm:text-sm font-semibold px-4 py-3 rounded-xl shadow-xl flex items-center gap-2 border-l-4 border-emerald-400">
        <span>✅</span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
