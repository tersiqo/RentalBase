@extends('layouts.admin')

@section('title', 'Dashboard Operasional')
@section('page-title', 'Dashboard Operasional')

@section('content')

{{-- Page Header --}}
<div class="mb-8 pt-3 lg:pt-4">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-orange-500 mb-1 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 inline-block"></span>
                PUSAT KENDALI RENTAL
            </p>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">
                Selamat Datang kembali, {{ Str::before($user->name ?? 'Budi', ' ') }}!
            </h1>
            <p class="mt-1 text-xs lg:text-sm text-gray-500 font-medium">
                Pantau pesanan masuk, jadwal pengiriman, sterilisasi unit, dan verifikasi jaminan hari ini secara real-time.
            </p>
        </div>

        {{-- Header Quick Actions --}}
        <div class="flex items-center gap-2.5">
            <div class="relative">
                <input type="text"
                       placeholder="Cek Ketersediaan Perlengkapan Bayi"
                       class="w-52 lg:w-64 pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all shadow-sm">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-bold shadow-sm shadow-orange-500/30 flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Catat Reservasi Manual
            </button>
        </div>
    </div>
</div>

{{-- Card Metrik --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- ORDER BARU --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-2">
            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">ORDER BARU</p>
            <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $totalBookingBaru ?? 12 }}</p>
        <p class="mt-2 text-xs text-orange-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4"/>
            </svg>
            +3 pengajuan sewa baru
        </p>
    </div>

    {{-- VERIFIKASI --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-2">
            <p class="text-[11px] font-bold uppercase tracking-wide text-amber-500">VERIFIKASI</p>
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $menungguVerifikasi ?? 5 }}</p>
        <p class="mt-2 text-xs text-amber-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Menunggu Cek Pembayaran
        </p>
    </div>

    {{-- DISEWA --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-2">
            <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-500">DISEWA</p>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $totalDisewa ?? 0 }}</p>
        <p class="mt-2 text-xs text-emerald-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Unit aktif disewa pelanggan
        </p>
    </div>

    {{-- PENDAPATAN --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-5 hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-2">
            <p class="text-[11px] font-bold uppercase tracking-wide text-indigo-500">PENDAPATAN</p>
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight whitespace-nowrap">
            @php
                $pendapatan = $pendapatanBulanIni ?? 0;
                if ($pendapatan >= 1000000000) {
                    $formattedPendapatan = 'Rp ' . round($pendapatan / 1000000000, 1) . 'M';
                } elseif ($pendapatan >= 1000000) {
                    $formattedPendapatan = 'Rp ' . round($pendapatan / 1000000, 1) . ' Juta';
                } else {
                    $formattedPendapatan = 'Rp ' . number_format($pendapatan, 0, ',', '.');
                }
            @endphp
            {{ $formattedPendapatan }}
        </p>
        <p class="mt-2 text-xs text-indigo-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
        </p>
    </div>

</div>

{{-- Progress Bar: Sisa Kuota Limit Paket --}}
@php
    $max = $maxProduk;
    $used = $totalUnit ?? 0;
    $isUnlimited = empty($max);
    $percentage = $isUnlimited ? 0 : min(100, round(($used / $max) * 100));
@endphp
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 lg:p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex-1 w-full">
        <div class="flex justify-between items-end mb-2.5">
            <div>
                <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Sisa Kuota Limit Paket ({{ $subscription->plan_name ?? 'Gratis' }})
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Anda telah menggunakan <span class="font-bold text-gray-700">{{ $used }}</span> dari total 
                    <span class="font-bold text-gray-700">{{ $isUnlimited ? 'Unlimited' : $max }}</span> limit kapasitas produk/unit.
                </p>
            </div>
            <span class="text-2xl font-extrabold {{ $percentage >= 90 ? 'text-red-500' : 'text-orange-500' }}">
                {{ $isUnlimited ? '∞' : $percentage . '%' }}
            </span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3.5 overflow-hidden shadow-inner">
            <div class="h-full rounded-full transition-all duration-1000 ease-out 
                {{ $percentage >= 90 ? 'bg-gradient-to-r from-red-500 to-red-600' : 'bg-gradient-to-r from-orange-400 to-orange-500' }}" 
                style="width: {{ $isUnlimited ? 100 : $percentage }}%"></div>
        </div>
    </div>
    <div class="shrink-0 flex gap-3 mt-2 md:mt-0">
        <a href="#" class="px-4 py-2.5 bg-stone-100 text-gray-700 rounded-xl text-xs font-bold hover:bg-stone-200 transition-colors border border-stone-200/60 inline-flex items-center">
            Kelola Unit
        </a>
        <a href="#" class="px-4 py-2.5 bg-gray-900 text-white rounded-xl text-xs font-bold shadow-sm shadow-gray-900/30 hover:bg-gray-800 transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Upgrade Paket
        </a>
    </div>
</div>

{{-- Tabel 5 Order Terbaru --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col mb-6">
    
    {{-- Table Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-5 py-4 border-b border-gray-100 gap-3 bg-stone-50/30">
        <div class="flex items-center gap-2.5">
            <h2 class="text-base font-bold text-gray-900">Pesanan Terbaru</h2>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative">
                <input type="text"
                       placeholder="Cari pesanan..."
                       class="w-44 lg:w-56 pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 transition-all shadow-sm">
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 bg-white hover:bg-stone-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 shadow-sm flex items-center gap-1.5 transition-colors">
                Lihat Semua
            </a>
        </div>
    </div>

    {{-- Table Content --}}
    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="bg-stone-50/70 border-b border-gray-100">
                    <th class="px-5 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">ID PESANAN</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">CUSTOMER & ITEM</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">TGL SEWA</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">TOTAL HARGA</th>
                    <th class="px-4 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400">STATUS</th>
                    <th class="px-5 py-3.5 font-bold uppercase tracking-wider text-[10px] text-gray-400 text-right">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">

                @forelse($recentOrders as $order)
                    @php
                        $statusBadge = match($order->status) {
                            'menunggu_pembayaran'      => 'bg-gray-100 text-gray-600 border-gray-200/60 ring-gray-400',
                            'menunggu_konfirmasi'      => 'bg-amber-50 text-amber-600 border-amber-200/60 ring-amber-500',
                            'pembayaran_terverifikasi' => 'bg-blue-50 text-blue-600 border-blue-200/60 ring-blue-500',
                            'dikonfirmasi'             => 'bg-blue-50 text-blue-600 border-blue-200/60 ring-blue-500',
                            'siap_kirim'               => 'bg-indigo-50 text-indigo-600 border-indigo-200/60 ring-indigo-500',
                            'sedang_disewa'            => 'bg-emerald-50 text-emerald-600 border-emerald-200/60 ring-emerald-500',
                            'selesai'                  => 'bg-teal-50 text-teal-600 border-teal-200/60 ring-teal-500',
                            'dibatalkan'               => 'bg-red-50 text-red-600 border-red-200/60 ring-red-500',
                            default                    => 'bg-gray-100 text-gray-600 border-gray-200/60 ring-gray-400',
                        };

                        $statusLabel = ucwords(str_replace('_', ' ', $order->status));
                        
                        $startDate = \Carbon\Carbon::parse($order->rental_start_date);
                        $endDate = \Carbon\Carbon::parse($order->rental_end_date);
                        $duration = $startDate->diffInDays($endDate);
                        if($duration == 0) $duration = 1;
                        
                        $firstItem = $order->orderItems->first();
                        $productName = $firstItem ? ($firstItem->product->name ?? 'Produk tidak diketahui') : 'Tidak ada produk';
                        if($order->orderItems->count() > 1) {
                            $productName .= ' (+' . ($order->orderItems->count() - 1) . ' item)';
                        }
                    @endphp
                    <tr class="hover:bg-stone-50/50 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-orange-500 whitespace-nowrap">#{{ $order->order_number ?? 'ORD-'.$order->id }}</td>
                        <td class="px-4 py-3.5">
                            <p class="font-bold text-gray-900 leading-tight">{{ $order->user->name ?? 'Tamu' }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate max-w-[200px]" title="{{ $productName }}">{{ $productName }}</p>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $startDate->format('d') }} - {{ $endDate->format('d M') }} <span class="text-gray-400">({{ $duration }} Hari)</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 font-bold text-gray-900 whitespace-nowrap">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $statusBadge }}">
                                <span class="w-1.5 h-1.5 rounded-full ring-current bg-current"></span>
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <a href="#" class="inline-block px-3 py-1.5 bg-white border border-gray-200 hover:border-orange-500 hover:text-orange-500 text-gray-600 font-semibold rounded-lg text-xs transition-colors shadow-sm">
                                Kelola
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-500 text-xs">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            Belum ada transaksi pesanan terbaru.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>
</div>

@endsection
