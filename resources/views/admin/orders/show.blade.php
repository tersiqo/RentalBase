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
<div x-data="{ modalImage: null, openImageModal(url) { this.modalImage = url; }, closeImageModal() { this.modalImage = null; } }">
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
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                    @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi_identitas'])) bg-amber-50 text-amber-700 border border-amber-200
                    @elseif(in_array($order->status, ['menunggu_pembayaran', 'belum_dibayar'])) bg-yellow-50 text-yellow-700 border border-yellow-200
                    @elseif(in_array($order->status, ['menunggu_verifikasi', 'verifikasi'])) bg-blue-50 text-blue-700 border border-blue-200
                    @elseif(in_array($order->status, ['siap_kirim', 'diproses', 'dikonfirmasi'])) bg-indigo-50 text-indigo-700 border border-indigo-200
                    @elseif($order->status === 'dikirim') bg-purple-50 text-purple-700 border border-purple-200
                    @elseif($order->status === 'sedang_disewa') bg-sky-50 text-sky-700 border border-sky-200
                    @elseif($order->status === 'selesai') bg-emerald-50 text-emerald-700 border border-emerald-200
                    @elseif(in_array($order->status, ['batal', 'dibatalkan', 'ditolak'])) bg-red-50 text-red-700 border border-red-200
                    @else bg-gray-50 text-gray-700 border border-gray-200 @endif">
                    
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
                Dibuat pada {{ $order->created_at ? $order->created_at->translatedFormat('l, d F Y - H:i') : '-' }} WIB
            </p>
        </div>

        {{-- Tombol Tindakan Cepat ke Pengiriman jika sudah siap kirim --}}
        @if(in_array($order->status, ['siap_kirim', 'diproses']))
            <a href="{{ route('admin.shipments.index', ['search' => $order->order_number]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                <span>Buka di Daftar Pengiriman</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
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
                    <p class="text-xs text-gray-500 mt-0.5">{{ $order->duration_days }} hari sewa</p>
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
                </div>
            </div>

            {{-- Daftar Barang yang Dipesan --}}
            <p class="text-xs text-gray-400 uppercase font-bold mb-3">Daftar Barang Disewa</p>
            <div class="divide-y divide-gray-100">
                @forelse($order->orderItems as $item)
                    @php
                        $mainImg = $item->product?->main_image;
                        $imgUrl = ($mainImg && str_starts_with($mainImg, 'http'))
                            ? $mainImg
                            : ($mainImg ? \Illuminate\Support\Facades\Storage::url($mainImg) : null);
                    @endphp
                    <div class="py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $item->product->name ?? 'Produk' }}"
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
                        <div class="flex items-center justify-between mb-2.5">
                            <p class="text-sm font-bold text-gray-900">
                                {{ $item->product->name ?? 'Produk' }}
                            </p>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $isFulfilled ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
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
                                        @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi', 'siap_kirim']))
                                            <form action="{{ route('admin.orders.deallocate', [$order->id, $allocation->id]) }}" method="POST" onsubmit="return confirm('Batalkan alokasi unit ini untuk memilih unit lain?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-semibold hover:underline flex items-center gap-1 transition cursor-pointer" title="Batalkan alokasi unit ini">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    <span>Ganti Unit</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic mb-3">Belum ada unit fisik yang dipilih untuk item ini.</p>
                        @endif

                        {{-- Form dropdown tambah alokasi unit --}}
                        @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi', 'siap_kirim']) && ! $isFulfilled)
                            @php
                                $matchingUnits = $availableUnits->filter(function($unit) use ($item) {
                                    return $unit->product_id === $item->product_id;
                                });
                            @endphp
                            @if($matchingUnits->isNotEmpty())
                                <form action="{{ route('admin.orders.allocate', $order->id) }}" method="POST" class="flex gap-2 mt-2">
                                    @csrf
                                    <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                    <select name="equipment_unit_id"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-xs focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white"
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
                                            class="px-3.5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-md transition shrink-0 shadow-xs">
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
                        @if($order->status === 'menunggu_konfirmasi')
                            <button type="submit"
                                    class="w-full px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-md transition shadow-xs flex items-center justify-center gap-1.5">
                                <span>Setujui Identitas &amp; Minta Bayar</span>
                            </button>
                            <button type="submit" name="direct_ready" value="1"
                                    class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md transition flex items-center justify-center gap-1.5"
                                    title="Gunakan ini jika customer sudah lunas tunai di tempat">
                                <span>Konfirmasi Langsung Lunas (Siap Kirim)</span>
                            </button>
                        @else
                            <button type="submit"
                                    class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md transition shadow-xs flex items-center justify-center gap-1.5">
                                <span>Verifikasi Pembayaran &amp; Siap Kirim</span>
                            </button>
                        @endif
                    </form>

                    <button @click="showRejectForm = true"
                            type="button"
                            class="w-full px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-semibold rounded-md transition">
                        Tolak / Batalkan Pesanan
                    </button>
                </div>

                {{-- Form Penolakan Pesanan --}}
                <div x-show="showRejectForm" x-cloak class="pt-1">
                    <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST">
                        @csrf
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="cancellation_reason"
                                  rows="3"
                                  required
                                  placeholder="Contoh: Foto KTP buram atau stok unit sedang dalam perbaikan..."
                                  class="w-full border border-gray-300 rounded-md text-xs px-3 py-2 focus:ring-red-500 focus:border-red-500 mb-3"></textarea>
                        <div class="flex gap-2">
                            <button type="button" @click="showRejectForm = false"
                                    class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-md">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-xs">
                                Ya, Tolak
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
                        <div class="flex justify-between items-center text-gray-500">
                            <span>Rekening Tujuan:</span>
                            <span class="font-medium text-gray-700">
                                {{ $paymentMethod->account_number }} (a/n {{ $paymentMethod->account_holder }})
                            </span>
                        </div>
                    @endif
                    @if($order->latestPayment->paid_at)
                        <div class="flex justify-between items-center text-gray-500 pt-1 border-t border-gray-200/50">
                            <span>Waktu Unggah:</span>
                            <span class="font-medium text-gray-700">{{ $order->latestPayment->paid_at->translatedFormat('d M Y, H:i') }}</span>
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
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold
                        @if($order->identityGuarantee->status === 'diverifikasi') bg-emerald-100 text-emerald-800 border border-emerald-200
                        @elseif($order->identityGuarantee->status === 'ditolak') bg-red-100 text-red-800 border border-red-200
                        @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                        {{ ucfirst(str_replace('_', ' ', $order->identityGuarantee->status ?? '-')) }}
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

</div>

@endsection
