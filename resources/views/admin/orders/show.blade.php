@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . ($order->order_number ?? $order->id))
@section('page-title', 'Detail Pesanan')

@section('content')

{{-- Notifikasi Sukses / Error --}}
@if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    </div>
@endif

{{-- Navigasi Kembali & Header Utama --}}
<div x-data="{ 
    modalImage: null, 
    openImageModal(url) { this.modalImage = url; }, 
    closeImageModal() { this.modalImage = null; },
    deallocateModal: false,
    deallocateAction: '',
    deallocateUnitCode: '',
    openDeallocateModal(action, code) {
        this.deallocateAction = action;
        this.deallocateUnitCode = code;
        this.deallocateModal = true;
    },
    openReuploadModal: false
}">
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('admin.orders.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-orange-600 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Master Pesanan</span>
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                    Pesanan #{{ $order->order_number ?? $order->id }}
                </h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
                    @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi_identitas'])) bg-amber-50 text-amber-700
                    @elseif(in_array($order->status, ['menunggu_pembayaran', 'belum_dibayar'])) bg-yellow-50 text-yellow-800
                    @elseif(in_array($order->status, ['menunggu_verifikasi', 'verifikasi'])) bg-blue-50 text-blue-700
                    @elseif(in_array($order->status, ['siap_kirim', 'diproses', 'dikonfirmasi'])) bg-indigo-50 text-indigo-700
                    @elseif($order->status === 'dikirim') bg-purple-50 text-purple-700
                    @elseif($order->status === 'sedang_disewa') bg-sky-50 text-sky-700
                    @elseif($order->status === 'selesai') bg-emerald-50 text-emerald-700
                    @elseif(in_array($order->status, ['batal', 'dibatalkan', 'ditolak'])) bg-rose-50 text-rose-700
                    @else bg-slate-100 text-slate-700 @endif">
                    
                    <span class="w-1.5 h-1.5 rounded-full
                        @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi_identitas'])) bg-amber-500
                        @elseif(in_array($order->status, ['menunggu_pembayaran', 'belum_dibayar'])) bg-yellow-500
                        @elseif(in_array($order->status, ['menunggu_verifikasi', 'verifikasi'])) bg-blue-500
                        @elseif(in_array($order->status, ['siap_kirim', 'diproses', 'dikonfirmasi'])) bg-indigo-500
                        @elseif($order->status === 'dikirim') bg-purple-500
                        @elseif($order->status === 'sedang_disewa') bg-sky-500
                        @elseif($order->status === 'selesai') bg-emerald-500
                        @elseif(in_array($order->status, ['batal', 'dibatalkan', 'ditolak'])) bg-red-500
                        @else bg-gray-400 @endif"></span>
                    
                    <span>{{ $order->status_label }}</span>
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                Dibuat pada {{ $order->created_at ? $order->created_at->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i') : '-' }} WIB
            </p>
        </div>

        {{-- Tombol Tindakan Cepat sesuai status alur pesanan --}}
        @if(in_array($order->status, ['siap_kirim', 'diproses']))
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.condition-checks.index', ['order_id' => $order->id, 'type' => 'sebelum_serah', 'return_to' => 'order']) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition shadow-2xs"
                   title="Catat pemeriksaan fisik unit sebelum diserahkan">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Cek Fisik Awal</span>
                </a>
                @if(! $order->canProcessHandover())
                    <span class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1.5" title="{{ $order->handover_blocked_reason }}">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Belum Jadwal Serah Terima</span>
                    </span>
                @endif
                <a href="{{ route('admin.shipments.index', ['search' => $order->order_number]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                    <span>Buka di Pengiriman</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        @elseif(in_array($order->status, ['sedang_disewa', 'dikembalikan', 'menunggu_pembayaran_denda']))
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.condition-checks.index', ['order_id' => $order->id, 'type' => 'setelah_kembali', 'return_to' => 'order']) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Cek Fisik Unit</span>
                </a>
                <a href="{{ route('admin.returns.index', ['search' => $order->order_number]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                    <span>Buka di Pengembalian</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        @elseif($order->status === 'dikirim')
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Tombol Konfirmasi Diterima Pelanggan (Memecah Deadlock Alur Kurir) --}}
                <form method="POST" action="{{ route('admin.shipments.delivered', $order->id) }}"
                      onsubmit="return confirm('Konfirmasi bahwa paket pesanan #{{ $order->order_number ?? $order->id }} telah sampai dan diterima oleh pelanggan?\n\nStatus pesanan akan beralih menjadi \'Sedang Disewa\' dan langsung dapat diproses di menu Pengembalian.');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Tandai Sudah Diterima Pelanggan</span>
                    </button>
                </form>

                <a href="{{ route('admin.shipments.index', ['search' => $order->order_number, 'status' => 'dikirim']) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 text-xs font-semibold rounded-lg transition shadow-2xs">
                    <span>Pantau di Pengiriman</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Stepper Status Alur Pesanan (Progress Tracker) --}}
@php
    $step = $order->step_index;
    $isCancelled = in_array($order->status, ['batal', 'dibatalkan', 'ditolak']);
@endphp
<div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-6">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Alur Progres Pesanan</p>
    
    @if($isCancelled)
        <div class="p-3 bg-red-50 border border-red-200 rounded-md text-xs text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Pesanan ini berstatus <strong>Dibatalkan / Ditolak</strong>. Alur sewa tidak dilanjutkan.</span>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center text-xs">
            {{-- 1. Verifikasi KTP --}}
            <div class="p-2.5 rounded-lg border {{ $step >= 1 ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : ($step === 0 ? 'bg-orange-50 border-orange-300 text-orange-700 font-bold ring-2 ring-orange-500/20' : 'bg-gray-50 border-gray-200 text-gray-400') }}">
                <div class="text-sm font-bold mb-0.5">{{ $step >= 1 ? 'Selesai' : 'Tahap 1' }}</div>
                <div>Verifikasi KTP</div>
            </div>

            {{-- 2. Pembayaran --}}
            <div class="p-2.5 rounded-lg border {{ $step >= 3 ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : ($step === 1 || $step === 2 ? 'bg-orange-50 border-orange-300 text-orange-700 font-bold ring-2 ring-orange-500/20' : 'bg-gray-50 border-gray-200 text-gray-400') }}">
                <div class="text-sm font-bold mb-0.5">{{ $step >= 3 ? 'Selesai' : 'Tahap 2' }}</div>
                <div>Pembayaran</div>
            </div>

            {{-- 3. Alokasi & Siap Kirim --}}
            <div class="p-2.5 rounded-lg border {{ $step >= 4 ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : ($step === 3 ? 'bg-orange-50 border-orange-300 text-orange-700 font-bold ring-2 ring-orange-500/20' : 'bg-gray-50 border-gray-200 text-gray-400') }}">
                <div class="text-sm font-bold mb-0.5">{{ $step >= 4 ? 'Selesai' : 'Tahap 3' }}</div>
                <div>Siap Kirim / Diambil</div>
            </div>

            {{-- 4. Sedang Disewa --}}
            <div class="p-2.5 rounded-lg border {{ $step >= 5 ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : ($step === 4 ? 'bg-orange-50 border-orange-300 text-orange-700 font-bold ring-2 ring-orange-500/20' : 'bg-gray-50 border-gray-200 text-gray-400') }}">
                <div class="text-sm font-bold mb-0.5">{{ $step >= 5 ? 'Selesai' : 'Tahap 4' }}</div>
                <div>Masa Sewa Berjalan</div>
            </div>

            {{-- 5. Selesai --}}
            <div class="col-span-2 sm:col-span-1 p-2.5 rounded-lg border {{ $step >= 6 ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-bold' : 'bg-gray-50 border-gray-200 text-gray-400' }}">
                <div class="text-sm font-bold mb-0.5">{{ $step >= 6 ? 'Selesai' : 'Tahap 5' }}</div>
                <div>Pesanan Selesai</div>
            </div>
        </div>
    @endif
</div>

{{-- Banner Info Pengiriman Kurir Berjalan (Jika status dikirim) --}}
@if($order->status === 'dikirim')
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-900 flex items-start gap-3 mb-6 shadow-2xs">
        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
            <span class="font-bold block text-sm text-blue-950 mb-1">Pesanan Sedang Dalam Pengiriman Kurir</span>
            <div class="text-blue-800 space-y-0.5">
                <p>
                    Ekspedisi: <strong class="font-semibold text-blue-900">{{ $order->shipment->courier_name ?? 'Kurir' }}</strong> &bull; 
                    Nomor Resi: <strong class="font-mono font-bold text-blue-950">{{ $order->shipment->tracking_number ?? '-' }}</strong>
                    @if($order->shipment?->shipped_at)
                        &bull; Waktu Kirim: {{ $order->shipment->shipped_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                    @endif
                </p>
                <p class="text-blue-700">
                    Bila paket sudah sampai atau dikonfirmasi oleh kurir/pelanggan, klik tombol <strong>"Tandai Sudah Diterima Pelanggan"</strong> di kanan atas agar status beralih menjadi <em>Sedang Disewa</em> dan langsung masuk ke menu Pengembalian.
                </p>
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Kolom Kiri: Info Utama (2/3) --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Kartu Info Customer & Alamat Pengiriman --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Informasi Customer &amp; Pengiriman</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Nama Customer</p>
                    <p class="font-semibold text-gray-900">{{ $order->user->name ?? 'Customer' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Email</p>
                    <p class="font-semibold text-gray-900">{{ $order->user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Nomor Telepon / WhatsApp</p>
                    <p class="font-semibold text-gray-900">{{ $order->user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Alamat Tujuan Pengiriman</p>
                    <p class="font-semibold text-gray-900">
                        {{ $order->shipping_address ?: ($order->isPickup() ? 'Pelanggan mengambil langsung di toko rental' : '-') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Kartu Ringkasan Pesanan & Daftar Barang --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Ringkasan Pesanan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-5 pb-4 border-b border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Periode Sewa</p>
                    <p class="font-semibold text-gray-900">{{ $order->date_range }}</p>
                    @php
                        $isPreHandover = in_array($order->status, ['menunggu_konfirmasi', 'menunggu_pembayaran', 'menunggu_verifikasi', 'siap_kirim']);
                        $timeLabel = $isPreHandover ? 'Rencana' : 'Jam Aktif';
                    @endphp
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $order->duration_days }} hari sewa &bull; {{ $timeLabel }}: Pukul {{ $order->rental_start_date?->format('H:i') }} - {{ $order->rental_end_date?->format('H:i') }} WIB
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Total Tagihan</p>
                    <p class="font-bold text-gray-900 text-lg">{{ $order->formatted_total }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $order->items_count }} unit barang</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Metode Pengiriman</p>
                    <p class="font-semibold text-gray-900 flex items-center gap-1.5 mt-0.5">
                        @if($order->isPickup())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                Ambil di Toko
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                Kirim via Kurir
                            </span>
                        @endif
                    </p>
                    @if($order->shipment && ! $order->isPickup())
                        <div class="text-xs text-gray-500 mt-1">
                            Kurir: <span class="font-medium text-gray-800">{{ $order->shipment->courier_name ?? 'Kurir' }}</span>
                            @if($order->shipment->tracking_number)
                                &bull; Resi: <span class="font-mono font-bold text-gray-800">{{ $order->shipment->tracking_number }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Daftar Barang yang Dipesan --}}
            <p class="text-xs text-gray-400 uppercase font-bold mb-3">Daftar Barang Disewa</p>
            <div class="divide-y divide-gray-100">
                @forelse($order->orderItems as $item)
                    @php
                        $mainImg = $item->product?->main_image;
                        $imgUrl = $mainImg ? storage_url($mainImg) : null;
                    @endphp
                    <div class="py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $item->product->name ?? 'Produk' }}"
                                     loading="lazy"
                                     class="w-12 h-12 rounded-lg object-cover border border-gray-200 shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 text-gray-400">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-bold text-gray-900 text-sm leading-snug">
                                    {{ $item->product->name ?? 'Produk tidak ditemukan' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Jumlah: <span class="font-bold text-gray-800">{{ $item->quantity }}x unit</span>
                                    • Tarif: Rp {{ number_format((float)($item->unit_price ?? 0), 0, ',', '.') }}/hari
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-bold text-gray-900 text-sm">
                                Rp {{ number_format((float)($item->subtotal ?? $item->unit_price * $item->quantity * $order->duration_days), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-3 italic">Tidak ada item pesanan.</p>
                @endforelse
            </div>
        </div>

        {{-- Kartu Alokasi Unit Fisik --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Alokasi Unit Fisik</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Pilih nomor seri atau unit fisik barang sebelum pesanan diserahkan ke kurir atau customer.</p>
                </div>
            </div>

            <div class="space-y-5">
                @foreach($order->orderItems as $item)
                    @php
                        $allocatedCount = $item->orderItemUnits->count();
                        $neededCount = $item->quantity;
                        $isFulfilled = ($allocatedCount >= $neededCount);
                    @endphp
                    <div class="p-4 rounded-lg border {{ $isFulfilled ? 'bg-emerald-50/30 border-emerald-200' : 'bg-gray-50/50 border-gray-200' }}">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-2 mb-2.5">
                            <p class="text-sm font-bold text-gray-900 min-w-0">
                                {{ $item->product->name ?? 'Produk' }}
                            </p>
                            <span class="self-start sm:self-auto shrink-0 whitespace-nowrap text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $isFulfilled ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $allocatedCount }}/{{ $neededCount }} Unit Dialokasikan
                            </span>
                        </div>

                        {{-- Daftar unit yang sudah dialokasikan --}}
                        @if($item->orderItemUnits->isNotEmpty())
                            <div class="space-y-2 mb-3">
                                @foreach($item->orderItemUnits as $allocation)
                                    <div class="flex items-center justify-between bg-white border border-emerald-200 rounded-md px-3.5 py-2 text-sm shadow-2xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span class="font-bold text-gray-900 font-mono text-xs">
                                                {{ $allocation->equipmentUnit->unit_code ?? $allocation->equipmentUnit->asset_code ?? 'Unit #' . $allocation->equipment_unit_id }}
                                            </span>
                                            <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium">Siap Sewa</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            {{-- Pintasan Cek Fisik Unit --}}
                                            <a href="{{ route('admin.condition-checks.index', ['order_id' => $order->id, 'unit_id' => $allocation->equipment_unit_id, 'return_to' => 'order']) }}"
                                               class="text-xs text-orange-600 hover:text-orange-800 font-semibold hover:underline flex items-center gap-1 transition"
                                               title="Catat atau lihat kondisi fisik unit ini">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                <span>Cek Fisik</span>
                                            </a>

                                            @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_pembayaran', 'belum_dibayar', 'menunggu_verifikasi', 'verifikasi', 'siap_kirim', 'diproses', 'dikonfirmasi']))
                                                @php
                                                    $uCode = $allocation->equipmentUnit->unit_code ?? $allocation->equipmentUnit->asset_code ?? ('Unit #' . $allocation->equipment_unit_id);
                                                @endphp
                                                <button type="button" 
                                                        @click="openDeallocateModal('{{ route('admin.orders.deallocate', [$order->id, $allocation->id]) }}', '{{ $uCode }}')"
                                                        class="text-xs text-red-600 hover:text-red-800 font-semibold hover:underline flex items-center gap-1 transition cursor-pointer" 
                                                        title="Batalkan alokasi unit ini">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    <span>Ganti Unit</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic mb-3">Belum ada unit fisik yang dipilih untuk item ini.</p>
                        @endif

                        {{-- Form dropdown tambah alokasi unit --}}
                        @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_pembayaran', 'belum_dibayar', 'menunggu_verifikasi', 'verifikasi', 'siap_kirim', 'diproses', 'dikonfirmasi']) && ! $isFulfilled)
                            @php
                                $matchingUnits = $availableUnits->filter(function($unit) use ($item) {
                                    return $unit->product_id === $item->product_id;
                                });
                            @endphp
                            @if($matchingUnits->isNotEmpty())
                                <form action="{{ route('admin.orders.allocate', $order->id) }}" method="POST" class="flex flex-col sm:flex-row gap-2 mt-2">
                                    @csrf
                                    <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                    <select name="equipment_unit_id"
                                            class="w-full sm:flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-md text-xs focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white"
                                            required>
                                        <option value="">-- Pilih Nomor Seri Unit Tersedia --</option>
                                        @foreach($matchingUnits as $unit)
                                            <option value="{{ $unit->id }}">
                                                {{ $unit->unit_code ?? $unit->asset_code ?? 'Unit #' . $unit->id }}
                                                ({{ $unit->product->name ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                            class="w-full sm:w-auto px-3.5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-md transition shrink-0 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                        + Pasangkan Unit
                                    </button>
                                </form>
                            @else
                                <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-md text-xs text-amber-800 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>Stok unit fisik yang berstatus <strong>Tersedia</strong> untuk produk ini sedang kosong. Pastikan unit telah dikembalikan atau tambah unit baru di menu Katalog Unit.</span>
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Kolom Kanan: Dokumen, Status, & Aksi (1/3) --}}
    <div class="xl:col-span-1 space-y-6">

        {{-- Kartu Tindakan Admin (Verifikasi / Konfirmasi) --}}
        @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi', 'menunggu_pembayaran']))
            <div id="tindakan-admin" class="bg-white border-2 border-orange-400 rounded-lg shadow-sm p-5" x-data="{ showRejectForm: false }">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    <h2 class="text-xs font-bold text-orange-600 uppercase tracking-wider">Tindakan Admin</h2>
                </div>
                <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                    Periksa foto KTP dan bukti pembayaran di bawah sebelum menyetujui pesanan ini.
                </p>

                {{-- Tombol Aksi Terima --}}
                <div x-show="!showRejectForm" class="space-y-2.5">
                    <form action="{{ route('admin.orders.confirm', $order->id) }}" method="POST" class="space-y-2">
                        @csrf
                        @if($order->latestPayment)
                            <button type="submit"
                                    class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Konfirmasi Pesanan</span>
                            </button>
                        @elseif($order->status === 'menunggu_konfirmasi')
                            <button type="submit"
                                    class="w-full px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-md transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>Setujui Identitas &amp; Minta Bayar</span>
                            </button>
                            <button type="submit" name="direct_ready" value="1"
                                    class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md transition flex items-center justify-center gap-1.5 cursor-pointer"
                                    title="Gunakan ini jika customer sudah lunas tunai di tempat">
                                <span>Konfirmasi Langsung Lunas (Siap Kirim)</span>
                            </button>
                        @else
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 leading-relaxed">
                                <span class="font-bold block mb-0.5">Menunggu Bukti Pembayaran</span>
                                Customer belum mengunggah struk transfer. Tombol verifikasi akan aktif setelah bukti diunggah.
                            </div>
                            <button type="submit" name="direct_ready" value="1"
                                    class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer"
                                    title="Gunakan hanya jika customer membayar tunai langsung di tempat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Konfirmasi Lunas Tunai (Siap Kirim)</span>
                            </button>
                        @endif
                    </form>

                    {{-- Tombol Khusus Minta Unggah Ulang KTP (Ketika identitas belum diverifikasi) --}}
                    @if($order->identityGuarantee && $order->identityGuarantee->status !== 'diverifikasi')
                        <button type="button" @click="openReuploadModal = true"
                                class="w-full px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Minta Unggah Ulang KTP (Foto Buram)</span>
                        </button>
                    @endif

                    <button @click="showRejectForm = true"
                            type="button"
                            class="w-full px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-semibold rounded-md transition cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Tolak &amp; Batalkan Pesanan Permanen</span>
                    </button>
                </div>

                {{-- Form Penolakan & Pembatalan Permanen Pesanan --}}
                <div x-show="showRejectForm" x-cloak class="pt-1">
                    <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST">
                        @csrf
                        <div class="p-2.5 bg-red-50 border border-red-200 rounded-lg mb-2.5 text-[11px] text-red-800 leading-relaxed">
                            <span class="font-bold block text-xs text-red-900 mb-0.5">Peringatan Pembatalan Permanen</span>
                            Pesanan ini akan dibatalkan secara permanen dan unit yang dialokasikan akan dikembalikan ke status tersedia.
                        </div>

                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">
                            Alasan Pembatalan Pesanan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="cancellation_reason"
                                  rows="3"
                                  required
                                  placeholder="Contoh: Customer membatalkan sewa atau stok unit sedang tidak tersedia..."
                                  class="w-full border border-gray-300 rounded-md text-xs px-3 py-2 focus:ring-red-500 focus:border-red-500 mb-3"></textarea>
                        <div class="flex gap-2">
                            <button type="button" @click="showRejectForm = false"
                                    class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-md cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-xs cursor-pointer flex items-center justify-center gap-1">
                                <span>Ya, Batalkan Pesanan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Kartu Bukti Pembayaran --}}
        <div id="review-pembayaran" class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Review Bukti Pembayaran</h2>
                </div>
                @if($order->latestPayment)
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold
                        @if($order->latestPayment->status === 'terverifikasi') bg-emerald-100 text-emerald-800 border border-emerald-200
                        @elseif($order->latestPayment->status === 'ditolak') bg-red-100 text-red-800 border border-red-200
                        @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                        {{ ucfirst(str_replace('_', ' ', $order->latestPayment->status ?? 'menunggu')) }}
                    </span>
                @endif
            </div>

            @if($order->latestPayment)
                <div class="text-xs space-y-2 mb-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Nominal Transfer:</span>
                        <span class="font-bold text-gray-900 text-sm">
                            Rp {{ number_format((float)($order->latestPayment->amount ?? 0), 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Metode Bayar:</span>
                        <span class="font-semibold text-gray-800">
                            {{ $paymentMethod->bank_name ?? $order->latestPayment->payment_method ?? 'Transfer Bank' }}
                        </span>
                    </div>
                    @if($paymentMethod)
                        @php
                            $isQrisMethod = strtoupper($paymentMethod->bank_name ?? '') === 'QRIS' || !empty($paymentMethod->qris_image);
                        @endphp
                        <div class="flex justify-between items-center text-gray-500">
                            <span>{{ $isQrisMethod ? 'NMID / Merchant:' : 'Rekening Tujuan:' }}</span>
                            <span class="font-medium text-gray-700">
                                {{ $paymentMethod->account_number }} (a/n {{ $paymentMethod->account_holder }})
                            </span>
                        </div>
                    @endif
                    @if($order->latestPayment->paid_at)
                        <div class="flex justify-between items-center text-gray-500 pt-1 border-t border-gray-200/50">
                            <span>Waktu Unggah:</span>
                            <span class="font-medium text-gray-700">{{ $order->latestPayment->paid_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                        </div>
                    @endif
                    @if($order->latestPayment->notes)
                        <div class="pt-1 border-t border-gray-200/50">
                            <span class="text-gray-500 block mb-0.5">Catatan Customer:</span>
                            <p class="font-medium text-gray-800">{{ $order->latestPayment->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Preview Foto Struk Pembayaran --}}
                @if($paymentProofUrl)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-[11px] text-gray-500 font-semibold uppercase">
                            <span>Foto Struk / Bukti Transfer</span>
                            <button type="button"
                                    @click="openImageModal('{{ $paymentProofUrl }}')"
                                    class="text-orange-600 hover:text-orange-700 hover:underline text-xs font-semibold focus:outline-none cursor-pointer">
                                Lihat Foto
                            </button>
                        </div>
                        <div class="relative border rounded-lg overflow-hidden shadow-2xs bg-gray-50 group cursor-pointer"
                             @click="openImageModal('{{ $paymentProofUrl }}')">
                            <img src="{{ $paymentProofUrl }}"
                                 alt="Bukti Pembayaran"
                                 loading="lazy"
                                 class="w-full h-44 object-contain bg-white group-hover:scale-102 transition duration-200">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                <span class="px-3 py-1.5 bg-white text-gray-900 rounded-md text-xs font-semibold shadow flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Lihat Foto</span>
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                        Customer belum mengunggah struk bukti transfer.
                    </div>
                @endif
            @else
                <p class="text-xs text-gray-400 italic">Belum ada catatan pembayaran untuk pesanan ini.</p>
            @endif
        </div>

        {{-- Kartu Jaminan Identitas (KTP & Selfie) --}}
        <div id="review-identitas" class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Review Jaminan Identitas</h2>
                </div>
                @if($order->identityGuarantee)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $order->identityGuarantee->status_color }}">
                        {{ $order->identityGuarantee->status_label }}
                    </span>
                @endif
            </div>

            @if($order->identityGuarantee)
                {{-- Data Identitas Customer --}}
                <div class="text-xs space-y-2 mb-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex justify-between items-start">
                        <span class="text-gray-500 shrink-0">Nama KTP:</span>
                        <span class="font-bold text-gray-900 text-right">{{ $order->identityGuarantee->full_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-gray-500 shrink-0">Nomor NIK:</span>
                        <span class="font-mono font-bold text-gray-900 text-right tracking-wider">{{ $order->identityGuarantee->identity_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-start pt-1 border-t border-gray-200/50">
                        <span class="text-gray-500 shrink-0">Alamat KTP:</span>
                        <span class="font-medium text-gray-800 text-right max-w-[65%]">{{ $order->identityGuarantee->identity_address ?? '-' }}</span>
                    </div>
                    @if($order->identityGuarantee->notes)
                        <div class="pt-1 border-t border-gray-200/50">
                            <span class="text-gray-500 block mb-0.5">Catatan Verifikasi:</span>
                            <p class="font-medium text-gray-800">{{ $order->identityGuarantee->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Preview Foto KTP --}}
                <div class="mb-4 space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] text-gray-500 font-semibold uppercase">
                        <span>Foto KTP / Kartu Identitas</span>
                        @if($ktpUrl)
                            <button type="button"
                                    @click="openImageModal('{{ $ktpUrl }}')"
                                    class="text-orange-600 hover:text-orange-700 hover:underline text-xs font-semibold focus:outline-none cursor-pointer">
                                Lihat Foto
                            </button>
                        @endif
                    </div>
                    @if($ktpUrl)
                        <div class="relative border rounded-lg overflow-hidden shadow-2xs bg-gray-50 group cursor-pointer"
                             @click="openImageModal('{{ $ktpUrl }}')">
                            <img src="{{ $ktpUrl }}"
                                 alt="Foto KTP"
                                 loading="lazy"
                                 class="w-full h-40 object-contain bg-white group-hover:scale-102 transition duration-200">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                <span class="px-3 py-1.5 bg-white text-gray-900 rounded-md text-xs font-semibold shadow flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Lihat Foto</span>
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-400 italic">
                            Foto KTP belum diunggah oleh customer.
                        </div>
                    @endif
                </div>

                {{-- Preview Foto Wajah / Selfie --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] text-gray-500 font-semibold uppercase">
                        <span>Foto Selfie Customer</span>
                        @if($selfieUrl)
                            <button type="button"
                                    @click="openImageModal('{{ $selfieUrl }}')"
                                    class="text-orange-600 hover:text-orange-700 hover:underline text-xs font-semibold focus:outline-none cursor-pointer">
                                Lihat Foto
                            </button>
                        @endif
                    </div>
                    @if($selfieUrl)
                        <div class="relative border rounded-lg overflow-hidden shadow-2xs bg-gray-50 group cursor-pointer"
                             @click="openImageModal('{{ $selfieUrl }}')">
                            <img src="{{ $selfieUrl }}"
                                 alt="Foto Selfie"
                                 loading="lazy"
                                 class="w-full h-40 object-contain bg-white group-hover:scale-102 transition duration-200">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                <span class="px-3 py-1.5 bg-white text-gray-900 rounded-md text-xs font-semibold shadow flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Lihat Foto</span>
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-400 italic">
                            Foto selfie belum diunggah oleh customer.
                        </div>
                    @endif
                </div>

                {{-- Tombol Cepat Minta Unggah Ulang dari Review Identitas --}}
                @if($order->identityGuarantee->status !== 'diverifikasi' && in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi', 'menunggu_pembayaran']))
                    <div class="pt-3 border-t border-gray-100">
                        <button type="button" @click="openReuploadModal = true"
                                class="w-full py-2 px-3 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Minta Unggah Ulang KTP (Foto Buram)</span>
                        </button>
                    </div>
                @endif

            @else
                <p class="text-xs text-gray-400 italic">Tidak ada data jaminan identitas untuk pesanan ini.</p>
            @endif
        </div>

    </div>
</div>

{{-- ===== MODAL POP-UP FOTO (LIGHTBOX) SESUAI PEMERIKSAAN KONDISI FISIK ===== --}}
<div 
    x-show="modalImage" 
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    @keydown.escape.window="closeImageModal()"
>
    {{-- Area Klik di Luar Gambar untuk Menutup --}}
    <div class="fixed inset-0" @click="closeImageModal()"></div>

    {{-- Konten Pop-up --}}
    <div 
        x-show="modalImage"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative z-10 max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl p-2 overflow-hidden flex flex-col items-center"
    >
        {{-- Tombol Silang (Close) --}}
        <button 
            @click="closeImageModal()" 
            type="button" 
            class="absolute top-3 right-3 p-1.5 rounded-full bg-black/50 text-white hover:bg-black/75 transition-colors focus:outline-none cursor-pointer"
            title="Tutup (Esc)"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        {{-- Gambar --}}
        <img 
            :src="modalImage" 
            alt="Preview Dokumen" 
            class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-sm"
        >
    </div>
</div>

{{-- MODAL KONFIRMASI GANTI / BATAL ALOKASI UNIT --}}
<div x-show="deallocateModal"
     x-cloak
     @keydown.escape.window="deallocateModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
     x-transition.opacity>
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-sm w-full p-6 text-center space-y-4"
         @click.away="deallocateModal = false"
         x-transition.scale.95>
        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto shadow-xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <h3 class="text-base font-extrabold text-slate-900">Batalkan Alokasi Unit?</h3>
            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                Unit <strong class="font-mono font-bold text-slate-900" x-text="deallocateUnitCode"></strong> akan dilepaskan dari pesanan ini dan statusnya dikembalikan ke <strong>Siap Sewa</strong>.
            </p>
        </div>
        <form :action="deallocateAction" method="POST">
            @csrf
            @method('DELETE')
            <div class="grid grid-cols-2 gap-2.5 pt-2">
                <button type="button" @click="deallocateModal = false" class="py-2.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-3 rounded-xl bg-red-600 text-xs font-bold text-white hover:bg-red-700 shadow-sm transition cursor-pointer">
                    Ya, Batalkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL MINTA UNGGAH ULANG KTP (REUPLOAD) ===== --}}
<div x-show="openReuploadModal"
     x-cloak
     @keydown.escape.window="openReuploadModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-lg w-full overflow-hidden text-left"
         @click.away="openReuploadModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-amber-50/50">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-sm text-gray-900">Minta Unggah Ulang KTP</h3>
                    <p class="text-[11px] text-gray-500">Kirim instruksi perbaikan foto dokumen ke customer</p>
                </div>
            </div>
            <button type="button" @click="openReuploadModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.orders.reject', $order->id) }}" 
              method="POST" 
              class="p-6 space-y-4"
              x-data="{
                  presetReason: 'Foto KTP buram atau tidak terbaca jelas',
                  customNotes: '',
                  submitting: false,
                  get finalReason() {
                      return this.customNotes.trim() 
                          ? this.presetReason + ' (Catatan: ' + this.customNotes.trim() + ')' 
                          : this.presetReason;
                  }
              }"
              @submit="submitting = true">
            @csrf
            <input type="hidden" name="action_type" value="request_reupload">
            <input type="hidden" name="cancellation_reason" :value="finalReason">

            {{-- Kotak Edukatif Info --}}
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 leading-relaxed flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="font-semibold mb-0.5">Pesanan TIDAK akan dibatalkan.</p>
                    <p class="text-[11px] text-amber-800">
                        Customer akan menerima notifikasi untuk mengunggah ulang foto KTP yang lebih jelas. Status pesanan akan tetap berjalan.
                    </p>
                </div>
            </div>

            {{-- Pilihan Preset Alasan --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">
                    Pilih Alasan Utama <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                           :class="presetReason === 'Foto KTP buram atau tidak terbaca jelas' ? 'border-amber-400 bg-amber-50/40 text-amber-950 font-medium' : 'text-gray-700'">
                        <input type="radio" 
                               name="preset_option" 
                               value="Foto KTP buram atau tidak terbaca jelas" 
                               x-model="presetReason"
                               class="text-amber-600 focus:ring-amber-500 border-gray-300">
                        <span class="text-xs">Foto KTP buram atau tidak terbaca jelas</span>
                    </label>

                    <label class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                           :class="presetReason === 'Foto selfie memegang KTP terpotong / wajah tertutup' ? 'border-amber-400 bg-amber-50/40 text-amber-950 font-medium' : 'text-gray-700'">
                        <input type="radio" 
                               name="preset_option" 
                               value="Foto selfie memegang KTP terpotong / wajah tertutup" 
                               x-model="presetReason"
                               class="text-amber-600 focus:ring-amber-500 border-gray-300">
                        <span class="text-xs">Foto selfie memegang KTP terpotong / wajah tertutup</span>
                    </label>

                    <label class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                           :class="presetReason === 'NIK / Nama tidak sesuai dengan data formulir sewa' ? 'border-amber-400 bg-amber-50/40 text-amber-950 font-medium' : 'text-gray-700'">
                        <input type="radio" 
                               name="preset_option" 
                               value="NIK / Nama tidak sesuai dengan data formulir sewa" 
                               x-model="presetReason"
                               class="text-amber-600 focus:ring-amber-500 border-gray-300">
                        <span class="text-xs">NIK / Nama tidak sesuai dengan data formulir sewa</span>
                    </label>
                </div>
            </div>

            {{-- Textarea Catatan Tambahan (Opsional) --}}
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Catatan Tambahan untuk Customer
                    </label>
                    <span class="text-[10px] text-gray-400 font-normal">Opsional</span>
                </div>
                <textarea x-model="customNotes"
                          rows="2"
                          placeholder="Contoh: Pastikan pencahayaan cukup dan nomor NIK 16 digit terbaca tajam tanpa pantulan cahaya..."
                          class="w-full border border-gray-300 rounded-xl text-xs px-3 py-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
            </div>

            {{-- Tombol Aksi Modal --}}
            <div class="flex gap-2.5 pt-2 border-t border-gray-100">
                <button type="button" 
                        @click="openReuploadModal = false" 
                        :disabled="submitting"
                        class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        :disabled="submitting"
                        :class="submitting ? 'opacity-70 cursor-not-allowed' : ''"
                        class="flex-1 py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span x-text="submitting ? 'Mengirim...' : 'Kirim Permintaan'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

</div>

@endsection
