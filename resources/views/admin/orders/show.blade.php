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
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Bukti Pembayaran</h2>

            @if($order->latestPayment)
                <div class="text-xs space-y-2 mb-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Metode Bayar:</span>
                        <span class="font-bold text-gray-800">{{ $order->latestPayment->payment_method ?? 'Transfer Bank' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nominal:</span>
                        <span class="font-bold text-gray-900 text-sm">
                            Rp {{ number_format((float)($order->latestPayment->amount ?? 0), 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status Verifikasi:</span>
                        <span class="font-semibold
                            @if($order->latestPayment->status === 'terverifikasi') text-emerald-600
                            @elseif($order->latestPayment->status === 'ditolak') text-red-600
                            @else text-amber-600 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->latestPayment->status ?? 'menunggu')) }}
                        </span>
                    </div>
                    @if($order->latestPayment->paid_at)
                        <div class="flex justify-between pt-1 border-t border-gray-200/50">
                            <span class="text-gray-500">Waktu Diunggah:</span>
                            <span class="font-medium text-gray-700">{{ $order->latestPayment->paid_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Preview Bukti Pembayaran --}}
                @if($paymentProofUrl)
                    <p class="text-[11px] text-gray-400 uppercase font-bold mb-1.5">Foto Struk / Bukti Transfer</p>
                    <a href="{{ $paymentProofUrl }}" target="_blank"
                       x-data
                       @click.prevent="$dispatch('open-lightbox', { src: '{{ $paymentProofUrl }}' })"
                       class="block group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100 hover:border-orange-400 transition">
                        <img src="{{ $paymentProofUrl }}"
                             alt="Bukti Pembayaran"
                             class="w-full h-44 object-contain group-hover:scale-102 transition duration-200">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-medium transition">
                            Klik untuk memperbesar
                        </div>
                    </a>
                @else
                    <p class="text-xs text-gray-400 italic">Customer belum mengunggah struk bukti transfer.</p>
                @endif
            @else
                <p class="text-xs text-gray-400 italic">Belum ada catatan pembayaran untuk pesanan ini.</p>
            @endif
        </div>

        {{-- Kartu Jaminan Identitas (KTP & Selfie) --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Jaminan Identitas Customer</h2>

            @if($order->identityGuarantee)
                <div class="text-xs space-y-2 mb-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Status Identitas:</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold
                            @if($order->identityGuarantee->status === 'diverifikasi') bg-emerald-100 text-emerald-800
                            @elseif($order->identityGuarantee->status === 'ditolak') bg-red-100 text-red-800
                            @else bg-amber-100 text-amber-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->identityGuarantee->status ?? '-')) }}
                        </span>
                    </div>
                    @if($order->identityGuarantee->notes)
                        <div class="pt-1 border-t border-gray-200/50">
                            <span class="text-gray-500">Catatan:</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $order->identityGuarantee->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Foto KTP --}}
                <div class="mb-4">
                    <p class="text-[11px] text-gray-400 uppercase font-bold mb-1.5">Foto KTP / Kartu Identitas</p>
                    @if($ktpUrl)
                        <a href="{{ $ktpUrl }}" target="_blank"
                           x-data @click.prevent="$dispatch('open-lightbox', { src: '{{ $ktpUrl }}' })"
                           class="block group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100 hover:border-orange-400 transition">
                            <img src="{{ $ktpUrl }}"
                                 alt="Foto KTP"
                                 class="w-full h-36 object-contain group-hover:scale-102 transition duration-200">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-medium transition">
                                Perbesar foto KTP
                            </div>
                        </a>
                    @else
                        <p class="text-xs text-gray-400 italic">Foto KTP belum diunggah.</p>
                    @endif
                </div>

                {{-- Foto Selfie --}}
                <div>
                    <p class="text-[11px] text-gray-400 uppercase font-bold mb-1.5">Foto Selfie Customer</p>
                    @if($selfieUrl)
                        <a href="{{ $selfieUrl }}" target="_blank"
                           x-data @click.prevent="$dispatch('open-lightbox', { src: '{{ $selfieUrl }}' })"
                           class="block group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100 hover:border-orange-400 transition">
                            <img src="{{ $selfieUrl }}"
                                 alt="Foto Selfie"
                                 class="w-full h-36 object-contain group-hover:scale-102 transition duration-200">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-medium transition">
                                Perbesar foto selfie
                            </div>
                        </a>
                    @else
                        <p class="text-xs text-gray-400 italic">Foto selfie belum diunggah.</p>
                    @endif
                </div>

            @else
                <p class="text-xs text-gray-400 italic">Tidak ada data jaminan identitas untuk pesanan ini.</p>
            @endif
        </div>

    </div>
</div>

{{-- Lightbox Pop Up Foto Ukuran Penuh --}}
<div x-data="{
        open: false,
        src: '',
        init() {
            window.addEventListener('open-lightbox', (e) => {
                this.src = e.detail.src;
                this.open = true;
            });
        }
    }"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center"
     style="background-color: rgba(0,0,0,0.85);"
     @click.self="open = false"
     @keydown.escape.window="open = false">

    <div class="relative max-w-4xl max-h-[90vh] mx-4">
        <button @click="open = false"
                class="absolute -top-10 right-0 text-white text-2xl font-bold hover:text-gray-300 transition p-1"
                title="Tutup">
            &times;
        </button>
        <img :src="src"
             alt="Preview Dokumen"
             class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl bg-white/5">
    </div>
</div>

@endsection
