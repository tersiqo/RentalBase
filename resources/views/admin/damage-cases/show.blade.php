@extends('layouts.admin')

@section('page-title', 'Detail Tiket Kerusakan #' . $damageCase->id)

@section('content')
@php
    $report = $damageCase->damageReport;
    $order = $report?->order;
    $unit = $report?->equipmentUnit;
    $product = $unit?->product;
    $images = $report?->images_list ?? [];
    $timeline = $damageCase->timeline_notes;
    $isClosed = in_array(strtolower($damageCase->case_status), ['selesai', 'dibatalkan']);
    $isPaid = strtolower($damageCase->payment_status) === 'lunas';
@endphp

<div x-data="damageCaseShow()" class="space-y-6 pb-16 font-sans">

    {{-- BREADCRUMBS --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.damage-cases.index') }}" class="hover:text-slate-800">Kasus Kerusakan</a>
        <span>/</span>
        <span class="text-rose-600 font-bold">Tiket #{{ $damageCase->id }}</span>
    </nav>

    {{-- HEADER PAGE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Tiket Kasus Kerusakan</span>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-extrabold text-slate-800 font-mono">#{{ $damageCase->id }}</span>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500">Dilaporkan {{ $damageCase->created_at ? $damageCase->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                {{ $product->name ?? 'Perlengkapan Rental' }}
                <span class="text-slate-400 font-mono text-xl font-bold">[{{ $unit->unit_code ?? '-' }}]</span>
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            {{-- Status Kasus Badge --}}
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-extrabold border {{ $damageCase->status_color }}">
                Status Kasus: {{ $damageCase->status_label }}
            </span>

            {{-- Status Bayar Badge --}}
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-extrabold border {{ $damageCase->payment_status_color }}">
                Bayar: {{ $damageCase->payment_status_label }}
            </span>

            <a href="{{ route('admin.damage-cases.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Daftar Kasus</span>
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

    {{-- 2-COLUMN MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: DETAIL & TIMELINE (8 COLS) --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. INFORMASI PESANAN & UNIT FISIK --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Data Pesanan & Unit Terkait</span>
                    </h3>
                    @if($order)
                        <a href="{{ route('admin.orders.show', $order->id) }}"
                           target="_blank"
                           class="text-xs font-bold text-rose-600 hover:text-rose-700 inline-flex items-center gap-1">
                            <span>Buka Order #{{ $order->order_number ?? $order->id }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    {{-- Data Penyewa --}}
                    <div class="space-y-1.5 p-3.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penyewa (Customer)</span>
                        <p class="font-bold text-slate-800 text-sm">{{ $order?->user?->name ?? 'Penyewa' }}</p>
                        <p class="text-slate-500">{{ $order?->user?->email ?? '-' }}</p>
                        <p class="text-slate-500">Alamat: {{ $order?->shipping_address ?? '-' }}</p>
                    </div>

                    {{-- Data Unit Fisik --}}
                    <div class="space-y-1.5 p-3.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Unit Fisik Perlengkapan</span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">
                                {{ $unit?->unit_code ?? '-' }}
                            </span>
                            <span class="text-slate-400 text-[11px]">(SN: {{ $unit?->serial_number ?? '-' }})</span>
                        </div>
                        <p class="font-medium text-slate-700">{{ $product?->name ?? '-' }}</p>
                        <p class="text-slate-500">
                            Status Saat Ini:
                            <span class="font-bold text-rose-600 uppercase text-[11px]">{{ $unit?->status ?? 'rusak' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- 2. DESKRIPSI & FOTO BUKTI CACAT --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Deskripsi Kerusakan & Bukti Foto</span>
                    </h3>
                    <span class="text-xs text-slate-400">Oleh: {{ $report?->reporter?->name ?? 'Admin Toko' }}</span>
                </div>

                {{-- Teks Deskripsi --}}
                <div class="p-4 rounded-xl bg-rose-50/40 border border-rose-100 text-slate-800 text-sm whitespace-pre-line leading-relaxed">
                    {{ $report?->description ?? 'Tidak ada deskripsi tambahan.' }}
                </div>

                {{-- Galeri Foto --}}
                @if(count($images) > 0)
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Dokumentasi Foto Bukti ({{ count($images) }} Foto) — Klik untuk memperbesar
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($images as $idx => $imgUrl)
                                <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer shadow-xs"
                                     @click="openLightbox('{{ $imgUrl }}', 'Foto Kerusakan #{{ $idx + 1 }}')">
                                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    </div>
                                    <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded text-[10px] bg-black/60 text-white font-mono">
                                        Foto {{ $idx + 1 }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada foto bukti yang diunggah saat pembuatan tiket ini.</p>
                @endif
            </div>

            {{-- 3. BUKTI PEMBAYARAN KOMPENSASI DARI PENYEWA (JIKA ADA) --}}
            @if($compensationPayment || strtolower($damageCase->payment_status) === 'menunggu_verifikasi' || $isPaid)
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-purple-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Pembayaran Kompensasi Penyewa</span>
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $damageCase->payment_status_color }}">
                            {{ $damageCase->payment_status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div class="space-y-2 text-xs">
                            <p class="text-slate-500">Nominal Tagihan: <span class="font-extrabold text-slate-900 text-sm">{{ $damageCase->formatted_total }}</span></p>
                            @if($compensationPayment)
                                <p class="text-slate-500">Jumlah Dibayar: <span class="font-bold text-slate-800">Rp {{ number_format($compensationPayment->amount, 0, ',', '.') }}</span></p>
                                <p class="text-slate-500">Tanggal Bayar: <span class="font-medium text-slate-700">{{ $compensationPayment->paid_at ? \Carbon\Carbon::parse($compensationPayment->paid_at)->translatedFormat('d M Y, H:i') : '-' }} WIB</span></p>
                                <p class="text-slate-500">Catatan: <span class="font-medium text-slate-700">{{ $compensationPayment->notes ?? '-' }}</span></p>
                            @endif

                            @if(!$isPaid)
                                <div class="pt-2">
                                    <form action="{{ route('admin.damage-cases.verify-payment', $damageCase->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin telah menerima pembayaran kompensasi sebesar {{ $damageCase->formatted_total }} secara sah?');">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 active:scale-95 transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Verifikasi & Tandai Lunas</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- Pratinjau Bukti Struk/Transfer --}}
                        @if($compensationPayment && $compensationPayment->payment_proof)
                            <div class="border border-slate-200 rounded-xl p-2 bg-slate-50/50 text-center">
                                <span class="block text-[11px] font-bold text-slate-500 mb-1">Bukti Transfer Penyewa</span>
                                <div class="relative group aspect-video max-h-36 rounded-lg overflow-hidden border border-slate-200 bg-white cursor-pointer mx-auto"
                                     @click="openLightbox('{{ asset('storage/' . $compensationPayment->payment_proof) }}', 'Bukti Transfer Kompensasi')">
                                    <img src="{{ asset('storage/' . $compensationPayment->payment_proof) }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span>Perbesar</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 4. RIWAYAT PERCAKAPAN & RESOLUSI TIMELINE --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Jejak Riwayat & Catatan Resolusi</span>
                    </h3>
                    <span class="text-xs text-slate-400">{{ count($timeline) }} entri tercatat</span>
                </div>

                {{-- Feed Timeline Percakapan --}}
                <div class="space-y-3">
                    @forelse($timeline as $item)
                        <div class="p-4 rounded-xl border text-xs space-y-1.5 transition-all
                            {{ $item['is_customer'] ? 'bg-blue-50/60 border-blue-200/70 text-blue-950' : ($item['is_payment'] ? 'bg-purple-50/60 border-purple-200/70 text-purple-950' : 'bg-slate-50/80 border-slate-200/80 text-slate-900') }}">
                            <div class="flex items-center justify-between font-bold">
                                <span class="flex items-center gap-1.5">
                                    @if($item['is_customer'])
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span class="text-blue-700">Penyewa:</span>
                                    @elseif($item['is_payment'])
                                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                        <span class="text-purple-700">Notifikasi Pembayaran:</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        <span class="text-rose-700">Pihak Toko:</span>
                                    @endif
                                    <span>{{ $item['header'] }}</span>
                                </span>
                            </div>
                            <div class="whitespace-pre-line text-slate-700 leading-relaxed font-sans text-xs pt-1">
                                {{ $item['message'] }}
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada catatan tanggapan atau negosiasi.</p>
                    @endforelse
                </div>

                {{-- FORM KIRIM TANGGAPAN BARU --}}
                @if(!$isClosed)
                    <div class="pt-4 border-t border-slate-100">
                        <form action="{{ route('admin.damage-cases.respond', $damageCase->id) }}"
                              method="POST"
                              class="space-y-3">
                            @csrf
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kirim Tanggapan / Jawaban Negosiasi ke Penyewa
                            </label>
                            <textarea name="notes"
                                      rows="3"
                                      required
                                      placeholder="Tuliskan penjelasan, rincian ganti rugi baru, atau klarifikasi toko untuk customer..."
                                      class="w-full p-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all"></textarea>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-500 font-medium">Ubah Status Kasus:</span>
                                    <select name="case_status" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs bg-white focus:outline-none">
                                        <option value="menunggu_respons">Menunggu Tanggapan Penyewa</option>
                                        <option value="dalam_tinjauan">Dalam Tinjauan Toko</option>
                                        <option value="menunggu_pembayaran">Menunggu Pembayaran Kompensasi</option>
                                    </select>
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <span>Kirim Tanggapan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: SUMMARY & ACTIONS (4 COLS) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- 1. KARTU RINCIAN FINANSIAL --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2.5">
                    Rincian Finansial Kompensasi
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Estimasi Biaya Servis / Sparepart</span>
                        <span class="font-bold text-slate-800">{{ $damageCase->formatted_repair_cost }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Denda Kelalaian Toko</span>
                        <span class="font-bold text-slate-800">{{ $damageCase->formatted_fine_amount }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="font-bold text-slate-700">Total Tagihan Kompensasi</span>
                        <span class="text-lg font-extrabold text-rose-600">{{ $damageCase->formatted_total }}</span>
                    </div>
                </div>

                {{-- Status Finansial Pill --}}
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                    <span class="text-[11px] text-slate-400 block mb-1">Status Pembayaran Ganti Rugi</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold border {{ $damageCase->payment_status_color }}">
                        {{ $damageCase->payment_status_label }}
                    </span>
                </div>
            </div>

            {{-- 2. KARTU AKSI PENGELOLAAN TIKET --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2.5">
                    Aksi Pengelolaan Kasus
                </h3>

                <div class="space-y-3">
                    {{-- Tombol Verifikasi Pembayaran jika belum lunas --}}
                    @if(!$isPaid)
                        <form action="{{ route('admin.damage-cases.verify-payment', $damageCase->id) }}"
                              method="POST"
                              onsubmit="return confirm('Verifikasi pembayaran kompensasi ini menjadi LUNAS?');">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 px-4 rounded-xl font-bold text-xs bg-emerald-600 text-white hover:bg-emerald-700 active:scale-95 transition-all shadow-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Verifikasi Pembayaran Lunas</span>
                            </button>
                        </form>
                    @endif

                    {{-- Tombol Tutup Kasus --}}
                    @if(!$isClosed)
                        <button type="button"
                                @click="showCloseModal = true"
                                class="w-full py-2.5 px-4 rounded-xl font-bold text-xs bg-rose-600 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Selesaikan & Tutup Kasus</span>
                        </button>
                    @else
                        <div class="p-3 rounded-xl bg-slate-100 text-slate-600 text-center text-xs font-bold">
                            Tiket Kasus Ini Telah Ditutup / Selesai
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. KARTU REKENING TOKO YANG DITAMPILKAN KE CUSTOMER --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2">
                    Metode Pembayaran Toko
                </h3>
                <p class="text-xs text-slate-500">
                    Penyewa melihat rekening bank / QRIS toko aktif di menu <span class="font-bold text-slate-700">Laporan Kerusakan Customer</span> untuk melakukan transfer ganti rugi.
                </p>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs font-mono text-slate-700 space-y-1">
                    <p class="font-bold text-slate-900">{{ $client->business_name ?? 'RentalBase' }}</p>
                    <p class="text-[11px] text-slate-400">Pastikan rekening toko aktif di menu Pengaturan Pembayaran Admin.</p>
                </div>
            </div>

        </div>

    </div>

    {{-- MODAL LIGHTBOX FOTO PREVIEW --}}
    <div x-show="lightboxOpen"
         x-cloak
         @keydown.escape.window="lightboxOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-xs p-4">
        <div class="relative max-w-4xl max-h-[90vh] bg-transparent flex flex-col items-center">
            <button @click="lightboxOpen = false"
                    class="absolute -top-10 right-0 text-white hover:text-slate-300 p-1 font-bold text-sm flex items-center gap-1">
                <span>Tutup</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="lightboxImg" class="max-w-full max-h-[80vh] rounded-2xl object-contain shadow-2xl border border-white/20">
            <p class="text-white text-xs mt-3 font-medium text-center" x-text="lightboxCaption"></p>
        </div>
    </div>

    {{-- MODAL KONFIRMASI TUTUP KASUS --}}
    <div x-show="showCloseModal"
         x-cloak
         @keydown.escape.window="showCloseModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Tutup Kasus Kerusakan</h3>
                <button @click="showCloseModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.damage-cases.close', $damageCase->id) }}" method="POST" class="space-y-4">
                @csrf

                <p class="text-xs text-slate-600">
                    Pastikan seluruh kompensasi telah disepakati atau diselesaikan. Saat menutup kasus, Anda wajib menentukan status unit fisik perlengkapan selanjutnya:
                </p>

                {{-- Status Fisik Unit Setelah Tutup --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Status Unit Fisik [{{ $unit->unit_code ?? '-' }}] Selanjutnya:
                    </label>
                    <select name="unit_status_after" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="tersedia">Tersedia (Sudah Diperbaiki & Siap Disewa Kembali)</option>
                        <option value="maintenance">Perawatan (Maintenance / Dalam Proses Servis)</option>
                        <option value="rusak">Tetap Rusak (Damaged / Belum Bisa Dipakai)</option>
                    </select>
                </div>

                {{-- Catatan Penutupan --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan Penutupan (Opsional)
                    </label>
                    <textarea name="closing_notes" rows="2" placeholder="Catatan resolusi akhir toko..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showCloseModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700 shadow-sm">
                        Ya, Selesaikan & Tutup Kasus
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function damageCaseShow() {
    return {
        lightboxOpen: false,
        lightboxImg: '',
        lightboxCaption: '',
        showCloseModal: false,

        openLightbox(url, caption) {
            this.lightboxImg = url;
            this.lightboxCaption = caption;
            this.lightboxOpen = true;
        }
    };
}
</script>
@endsection
