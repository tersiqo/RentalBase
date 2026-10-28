@extends('layouts.admin')

@section('title', 'Master Pesanan')
@section('page-title', 'Master Pesanan')

@section('content')

<div x-data="{ modalImage: null, openImageModal(url) { this.modalImage = url; }, closeImageModal() { this.modalImage = null; } }">

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Pesanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pantau seluruh pesanan dari semua status yang masuk ke toko kamu.</p>
</div>

{{-- Notifikasi sukses --}}
@if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

{{-- Form Filter --}}
<div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('admin.orders.index') }}"
          class="flex flex-col lg:flex-row gap-3 items-end">

        {{-- Input pencarian --}}
        <div class="flex-1 w-full">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Cari Pesanan</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Nama customer atau kode order..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>

        {{-- Filter status --}}
        <div class="w-full lg:w-52">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Status</label>
            <select name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <option value="">-- Semua Status --</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter tanggal dari --}}
        <div class="w-full lg:w-44">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Dari Tanggal</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>

        {{-- Filter tanggal sampai --}}
        <div class="w-full lg:w-44">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>

        {{-- Tombol aksi --}}
        <div class="flex gap-2 shrink-0 w-full lg:w-auto">
            <button type="submit"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-md transition shadow-xs">
                Filter
            </button>
            <a href="{{ route('admin.orders.index') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-md transition">
                Reset
            </a>
        </div>

    </form>
</div>

{{-- Tabel Pesanan --}}
<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wide border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 whitespace-nowrap">ID & Tanggal Masuk</th>
                    <th class="px-6 py-4 whitespace-nowrap">Customer</th>
                    <th class="px-6 py-4 whitespace-nowrap">Periode Sewa</th>
                    <th class="px-6 py-4 whitespace-nowrap">Total Tagihan</th>
                    <th class="px-6 py-4 text-center whitespace-nowrap">Status</th>
                    <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">

                @forelse($orders as $order)
                    @php
                        $isPickup = $order->isPickup();
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- ID & Tanggal --}}
                        <td class="px-6 py-4 align-top whitespace-nowrap">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="font-bold text-gray-900 hover:text-orange-600 transition-colors block">
                                #{{ $order->order_number ?? $order->id }}
                            </a>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $order->created_at ? $order->created_at->translatedFormat('d M Y, H:i') : '-' }}
                            </div>
                        </td>

                        {{-- Nama Customer & Metode Pengiriman --}}
                        <td class="px-6 py-4 align-top whitespace-nowrap">
                            <div class="font-semibold text-gray-900">
                                {{ $order->user->name ?? 'Customer' }}
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                @if($isPickup)
                                    <span class="inline-flex items-center text-[11px] font-semibold text-purple-700 bg-purple-50 border border-purple-200/80 px-2 py-0.5 rounded-md">
                                        Ambil di Toko
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded-md">
                                        Kirim via Kurir
                                    </span>
                                @endif

                                {{-- Preview Foto Dokumen seperti di Pemeriksaan Kondisi Fisik --}}
                                @php
                                    $ktpImage = $order->identityGuarantee?->identity_document_image ?? $order->identityGuarantee?->ktp_photo;
                                    $ktpUrl = $ktpImage ? (str_starts_with($ktpImage, 'http') ? $ktpImage : Storage::disk('public')->url($ktpImage)) : null;

                                    $proofImage = $order->latestPayment?->payment_proof ?? $order->latestPayment?->proof_of_payment;
                                    $proofUrl = $proofImage ? (str_starts_with($proofImage, 'http') ? $proofImage : Storage::disk('public')->url($proofImage)) : null;
                                @endphp

                                @if($ktpUrl)
                                    <button type="button"
                                            @click="openImageModal('{{ $ktpUrl }}')"
                                            class="text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-1.5 py-0.5 rounded text-[10px] font-semibold transition focus:outline-none cursor-pointer"
                                            title="Klik untuk melihat foto KTP">
                                        Lihat KTP
                                    </button>
                                @endif
                                @if($proofUrl)
                                    <button type="button"
                                            @click="openImageModal('{{ $proofUrl }}')"
                                            class="text-sky-700 hover:text-sky-900 bg-sky-50 hover:bg-sky-100 border border-sky-200 px-1.5 py-0.5 rounded text-[10px] font-semibold transition focus:outline-none cursor-pointer"
                                            title="Klik untuk melihat bukti pembayaran transfer">
                                        Lihat Bukti
                                    </button>
                                @endif
                            </div>
                        </td>

                        {{-- Periode Sewa --}}
                        <td class="px-6 py-4 align-top whitespace-nowrap">
                            <div class="text-gray-900 font-medium">{{ $order->date_range }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $order->duration_days }} hari sewa</div>
                        </td>

                        {{-- Total Harga --}}
                        <td class="px-6 py-4 align-top whitespace-nowrap">
                            <div class="font-bold text-gray-900">{{ $order->formatted_total }}</div>
                            <div class="text-[11px] text-gray-400 mt-0.5">{{ $order->items_count }} barang</div>
                        </td>

                        {{-- Badge Status Modern --}}
                        <td class="px-6 py-4 align-top text-center whitespace-nowrap">
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
                        </td>

                        {{-- Kolom Aksi --}}
                        <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Tombol Tindakan Cepat Verifikasi jika butuh perhatian admin --}}
                                @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi']))
                                    <a href="{{ route('admin.orders.show', $order->id) }}#{{ $order->status === 'menunggu_konfirmasi' ? 'review-identitas' : 'review-pembayaran' }}"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white {{ $order->status === 'menunggu_konfirmasi' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-blue-600 hover:bg-blue-700' }} rounded-lg transition shadow-xs">
                                        <span>{{ $order->status === 'menunggu_konfirmasi' ? 'Review KTP' : 'Review Bayar' }}</span>
                                    </a>
                                @endif

                                {{-- Tombol Detail Standar --}}
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-gray-900 rounded-lg transition">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </td>

                    </tr>
                @empty
                    {{-- Tampilan saat tidak ada data --}}
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-gray-700">Tidak ada pesanan ditemukan</p>
                            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                                @if(request()->hasAny(['search', 'status', 'from', 'to']))
                                    Tidak ada data pesanan yang cocok dengan kriteria filter saat ini.
                                @else
                                    Belum ada pesanan yang masuk ke toko rental ini.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif

</div>

{{-- ===== MODAL POP-UP FOTO (LIGHTBOX) SESUAI LAPORAN KONDISI FISIK ===== --}}
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
