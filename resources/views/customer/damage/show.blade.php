@extends('layouts.customer')

@section('title', 'Laporan Kerusakan - Pesanan #' . ($order->order_number ?? $order->id))

@section('content')
<div class="max-w-[1260px] mx-auto px-4 sm:px-6 py-6 sm:py-8 font-sans text-gray-700">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 mb-4">
        <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="hover:text-orange-600 transition">Beranda</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}" class="hover:text-orange-600 transition">Pesanan Saya</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('customer.orders.show', $order->id) }}" class="hover:text-orange-600 transition font-mono">{{ $order->order_number ?? ('ORD-' . $order->id) }}</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold">Klaim Kerusakan</span>
    </nav>

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-gray-200 mb-6">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-sm border border-rose-100">⚠️</span>
                    <span>Laporan Kerusakan & Klaim Kompensasi</span>
                </h1>
                @if($damageCase)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $damageCase->status_color }}">
                        {{ $damageCase->status_label }}
                    </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-gray-500 mt-1.5 font-mono">
                Pesanan #{{ $order->order_number ?? $order->id }} &bull; Tenant: <span class="text-gray-800 font-medium font-sans">{{ $client->nama_usaha ?? 'RentalBase' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('customer.orders.show', $order->id) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Lihat Detail Pesanan</span>
            </a>
        </div>
    </div>

    {{-- Notifikasi Flash --}}
    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <div>
                <p class="font-bold">Berhasil!</p>
                <p class="mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-bold">Terjadi Kesalahan</p>
                <p class="mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 border border-rose-200 shadow-sm">
            <p class="font-bold mb-1">Periksa kembali data yang Anda kirimkan:</p>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(! $damageReport)
        {{-- Empty State: Jika belum ada laporan kerusakan pada order ini --}}
        <div class="bg-white border border-gray-200 rounded-3xl p-10 sm:p-14 text-center max-w-xl mx-auto shadow-sm my-8">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-4 border border-emerald-100 shadow-sm">
                🎉
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Klaim Kerusakan</h2>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                Unit barang pada pesanan ini dalam kondisi aman atau toko belum mencatat adanya klaim kerusakan fisik pasca-pengembalian.
            </p>
            <a href="{{ route('customer.orders.show', $order->id) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                Kembali ke Detail Pesanan
            </a>
        </div>
    @else
        {{-- Banner Status Interaktif --}}
        <div class="mb-6 p-4 sm:p-5 rounded-2xl border shadow-sm
            @if(in_array(strtolower($damageCase?->case_status ?? ''), ['selesai', 'lunas']))
                bg-emerald-50/70 border-emerald-200 text-emerald-900
            @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['kompensasi_dibayar', 'menunggu_verifikasi']))
                bg-purple-50/70 border-purple-200 text-purple-900
            @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['dalam_tinjauan', 'negosiasi']))
                bg-blue-50/70 border-blue-200 text-blue-900
            @else
                bg-amber-50/70 border-amber-200 text-amber-900
            @endif
        ">
            <div class="flex items-start gap-3.5">
                <div class="text-2xl mt-0.5">
                    @if(in_array(strtolower($damageCase?->case_status ?? ''), ['selesai', 'lunas']))
                        ✅
                    @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['kompensasi_dibayar', 'menunggu_verifikasi']))
                        ⏳
                    @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['dalam_tinjauan', 'negosiasi']))
                        💬
                    @else
                        📢
                    @endif
                </div>
                <div>
                    <h2 class="text-base font-bold">
                        @if(in_array(strtolower($damageCase?->case_status ?? ''), ['selesai', 'lunas']))
                            Kasus Selesai & Lunas
                        @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['kompensasi_dibayar', 'menunggu_verifikasi']))
                            Pembayaran Kompensasi Sedang Diverifikasi
                        @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['dalam_tinjauan', 'negosiasi']))
                            Tanggapan Sedang Ditinjau oleh Toko
                        @else
                            Pemberitahuan Temuan Kerusakan Fisik Barang
                        @endif
                    </h2>
                    <p class="text-xs sm:text-sm mt-1 leading-relaxed opacity-90">
                        @if(in_array(strtolower($damageCase?->case_status ?? ''), ['selesai', 'lunas']))
                            Klaim kompensasi kerusakan telah lunas diverifikasi oleh pihak toko. Tiket kerusakan ini resmi ditutup.
                        @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['kompensasi_dibayar', 'menunggu_verifikasi']))
                            Bukti pembayaran kompensasi Anda telah kami terima. Admin toko akan memeriksa mutasi pembayaran dalam 1x24 jam kerja.
                        @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['dalam_tinjauan', 'negosiasi']))
                            Catatan atau tanggapan Anda telah terkirim. Admin toko sedang meninjau respon Anda untuk penyesuaian tindak lanjut.
                        @else
                            Berdasarkan inspeksi fisik pengembalian barang, toko menemukan adanya cacat/kerusakan fisik pada alat sewa. Silakan tinjau bukti foto, nominal biaya perbaikan, dan berikan tanggapan atau lakukan pembayaran kompensasi.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Main Grid: 2 Kolom di Desktop, 1 Kolom di Mobile --}}
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_400px] gap-6 items-start" x-data="{ activeImageModal: null }">

            {{-- Kolom Kiri: Detail Temuan, Galeri Foto, & Form Tanggapan --}}
            <div class="space-y-6">

                {{-- Card 1: Ringkasan Unit yang Rusak --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">📦</span>
                        <span>Unit Sewa Terkait</span>
                    </h2>

                    @php
                        $unit = $damageReport->equipmentUnit;
                        $product = $unit?->product ?? $order->orderItems->first()?->product;
                    @endphp

                    <div class="flex flex-col sm:flex-row gap-4 items-start bg-gray-50/80 p-4 rounded-xl border border-gray-100">
                        @if($product && $product->cover_image_url)
                            <img src="{{ $product->cover_image_url }}" alt="{{ $product->name }}" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border border-gray-200 bg-white shrink-0">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-200 rounded-xl flex items-center justify-center text-gray-400 font-bold text-xs shrink-0">
                                No Foto
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-2 py-0.5 bg-gray-200 text-gray-700 text-[10px] font-bold uppercase rounded tracking-wider mb-1">
                                {{ $product?->category?->name ?? 'Peralatan Sewa' }}
                            </span>
                            <h3 class="text-base font-bold text-gray-900 truncate">
                                {{ $product?->name ?? 'Produk Sewa' }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 text-xs text-gray-600">
                                <div>
                                    <span class="text-gray-400">Kode Unit / SN:</span>
                                    <span class="font-mono font-bold text-gray-800 ml-1">{{ $unit?->asset_code ?? ('UNIT-' . ($unit->id ?? '-')) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Dilaporkan Pada:</span>
                                    <span class="font-medium text-gray-800 ml-1">{{ $damageReport->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Petugas Inspeksi:</span>
                                    <span class="font-medium text-gray-800 ml-1">{{ $damageReport->reporter->name ?? 'Admin Toko' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Kondisi Fisik:</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-rose-100 text-rose-700">Perlu Perbaikan</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Deskripsi Kerusakan dari Toko --}}
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Deskripsi Kerusakan oleh Toko</label>
                        <div class="bg-amber-50/60 border border-amber-200/80 rounded-xl p-3.5 text-sm text-gray-800 leading-relaxed whitespace-pre-line">
                            {{ $damageReport->description ?? 'Tidak ada catatan deskripsi tambahan dari toko.' }}
                        </div>
                    </div>
                </div>

                {{-- Card 2: Galeri Foto Bukti Kerusakan Fisik --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">📸</span>
                            <span>Foto Bukti Fisik dari Toko</span>
                        </h2>
                        <span class="text-xs font-medium text-gray-500">
                            {{ count($damageReport->images_list) }} Foto Tersedia
                        </span>
                    </div>

                    @if(count($damageReport->images_list) > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($damageReport->images_list as $index => $imgUrl)
                                <div class="group relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50 aspect-square cursor-pointer shadow-sm hover:shadow-md transition"
                                     @click="activeImageModal = '{{ $imgUrl }}'">
                                    <img src="{{ $imgUrl }}" alt="Bukti Kerusakan {{ $index + 1 }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        <span>Perbesar</span>
                                    </div>
                                    <div class="absolute bottom-1.5 left-1.5 px-2 py-0.5 rounded bg-black/60 text-[10px] text-white font-mono">
                                        Foto {{ $index + 1 }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2.5 italic">
                            * Klik pada gambar di atas untuk melihat foto bukti kerusakan dalam ukuran penuh.
                        </p>
                    @else
                        <div class="p-8 text-center bg-gray-50 border border-dashed border-gray-200 rounded-xl text-gray-400 text-sm">
                            Belum ada foto bukti kerusakan yang dilampirkan oleh toko.
                        </div>
                    @endif
                </div>

                {{-- Card 3: Dialog & Catatan Tanggapan Customer --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">💬</span>
                        <span>Riwayat Komunikasi & Tanggapan Anda</span>
                    </h2>

                    {{-- Log Riwayat Resolusi / Catatan --}}
                    @if($damageCase && $damageCase->resolution_notes)
                        <div class="mb-5 bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs font-mono text-gray-700 whitespace-pre-wrap leading-relaxed max-h-56 overflow-y-auto">
                            {{ $damageCase->resolution_notes }}
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic mb-4">
                            Belum ada riwayat tanggapan atau negosiasi yang tercatat.
                        </p>
                    @endif

                    {{-- Form Tanggapan Customer --}}
                    @if($damageCase && !in_array(strtolower($damageCase->case_status), ['selesai', 'lunas']))
                        <form action="{{ route('customer.orders.damage.respond', $order->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="damage_case_id" value="{{ $damageCase->id }}">

                            <label for="notes" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Berikan Tanggapan / Klarifikasi
                            </label>
                            <textarea id="notes"
                                      name="notes"
                                      rows="3"
                                      required
                                      minlength="5"
                                      maxlength="1500"
                                      placeholder="Contoh: Saya setuju dengan biaya penggantian, atau: Mohon maaf noda tersebut bukan dari pemakaian saya melainkan..."
                                      class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 placeholder-gray-400"></textarea>

                            <div class="flex items-center justify-between mt-3">
                                <span class="text-[11px] text-gray-400">Minimal 5 karakter. Tanggapan Anda akan langsung dibaca pihak toko.</span>
                                <button type="submit"
                                        class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shadow-sm">
                                    Kirim Tanggapan
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="p-3 bg-gray-50 rounded-xl text-xs text-gray-500 border border-gray-200 text-center">
                            Tiket klaim kerusakan ini telah ditutup. Kolom tanggapan dinonaktifkan.
                        </div>
                    @endif
                </div>

            </div>

            {{-- Kolom Kanan: Rincian Nominal Klaim & Panel Pembayaran --}}
            <div class="space-y-6">

                {{-- Card Tagihan Biaya Ganti Rugi --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <span>🧾</span>
                        <span>Rincian Klaim Kompensasi</span>
                    </h2>

                    @if($damageCase)
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Biaya Perbaikan Fisik</span>
                                <span class="font-mono font-medium text-gray-900">{{ $damageCase->formatted_repair_cost }}</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Denda / Kerugian Alat</span>
                                <span class="font-mono font-medium text-gray-900">{{ $damageCase->formatted_fine_amount }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-100 flex justify-between items-baseline">
                                <span class="font-bold text-gray-900">Total Kompensasi</span>
                                <span class="text-xl font-extrabold text-rose-600 font-mono">{{ $damageCase->formatted_total }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <span class="text-gray-400">Status Pembayaran:</span>
                            <span class="font-bold uppercase tracking-wider
                                @if($damageCase->payment_status === 'lunas') text-emerald-600
                                @elseif($damageCase->payment_status === 'menunggu_verifikasi') text-purple-600
                                @else text-rose-600 @endif">
                                {{ str_replace('_', ' ', $damageCase->payment_status) }}
                            </span>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 italic">Belum ada rincian biaya yang diajukan toko.</p>
                    @endif
                </div>

                {{-- Card Panel Pembayaran --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <span>💳</span>
                        <span>Pembayaran Kompensasi</span>
                    </h2>

                    @if($damageCase && $damageCase->payment_status === 'lunas')
                        {{-- State Lunas --}}
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg mx-auto mb-2">
                                ✓
                            </div>
                            <h3 class="font-bold text-emerald-900 text-sm">Kompensasi Telah Lunas</h3>
                            <p class="text-xs text-emerald-700 mt-1">Pembayaran sebesar {{ $damageCase->formatted_total }} telah diverifikasi oleh toko.</p>
                        </div>

                    @elseif($damageCase && in_array($damageCase->payment_status, ['menunggu_verifikasi']) || in_array(strtolower($damageCase->case_status ?? ''), ['kompensasi_dibayar']))
                        {{-- State Menunggu Verifikasi --}}
                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-lg mx-auto mb-2">
                                ⏳
                            </div>
                            <h3 class="font-bold text-purple-900 text-sm">Menunggu Verifikasi Toko</h3>
                            <p class="text-xs text-purple-700 mt-1">
                                Bukti transfer kompensasi Anda telah tersimpan dan sedang diverifikasi oleh admin toko.
                            </p>
                            @if($compensationPayment && $compensationPayment->payment_proof)
                                <div class="mt-3 pt-3 border-t border-purple-200/60">
                                    <a href="{{ str_starts_with($compensationPayment->payment_proof, 'http') ? $compensationPayment->payment_proof : asset('storage/' . $compensationPayment->payment_proof) }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs text-purple-800 hover:text-purple-900 font-bold underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>Lihat Bukti Bayar Terkirim</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                    @elseif($damageCase && $damageCase->can_pay)
                        {{-- State Form Bayar Kompensasi --}}
                        <div class="space-y-4">
                            {{-- Info Rekening Toko --}}
                            <div class="bg-orange-50/70 border border-orange-200/70 rounded-xl p-3.5">
                                <p class="text-xs font-bold text-orange-900 uppercase tracking-wide mb-2">Metode Pembayaran Toko</p>

                                @if($paymentMethods->count() > 0)
                                    <div class="space-y-2.5">
                                        @foreach($paymentMethods as $pm)
                                            <div class="bg-white p-2.5 rounded-lg border border-orange-200/50 text-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-gray-900">{{ $pm->bank_name }}</span>
                                                    <span class="text-[10px] px-1.5 py-0.5 bg-orange-100 text-orange-800 font-bold rounded">Aktif</span>
                                                </div>
                                                <p class="font-mono text-sm font-extrabold text-gray-800 mt-1 tracking-wider">{{ $pm->account_number }}</p>
                                                <p class="text-gray-500 text-[11px]">a.n. {{ $pm->account_holder }}</p>

                                                @if($pm->qris_image)
                                                    <div class="mt-2 pt-2 border-t border-gray-100 text-center">
                                                        <img src="{{ str_starts_with($pm->qris_image, 'http') ? $pm->qris_image : asset('storage/' . $pm->qris_image) }}" alt="QRIS" class="w-32 h-32 mx-auto object-contain border rounded-md">
                                                        <span class="text-[10px] text-gray-400 block mt-1">Pindai QRIS di atas</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-orange-800">
                                        Silakan hubungi pihak toko untuk konfirmasi rekening pembayaran kompensasi.
                                    </p>
                                @endif
                            </div>

                            {{-- Form Upload Bukti Transfer --}}
                            <form action="{{ route('customer.orders.damage.pay', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <input type="hidden" name="damage_case_id" value="{{ $damageCase->id }}">

                                @if($paymentMethods->count() > 1)
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Pilih Rekening Tujuan</label>
                                        <select name="client_payment_method_id" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-xl focus:ring-orange-500 focus:border-orange-500">
                                            @foreach($paymentMethods as $pm)
                                                <option value="{{ $pm->id }}">{{ $pm->bank_name }} - {{ $pm->account_number }} ({{ $pm->account_holder }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Unggah Bukti Transfer Kompensasi
                                    </label>
                                    <input type="file"
                                           name="payment_proof"
                                           required
                                           accept="image/jpeg,image/png,image/jpg,application/pdf"
                                           class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer border border-gray-300 rounded-xl p-1 bg-white">
                                    <p class="text-[10px] text-gray-400 mt-1">Format JPG, PNG, atau PDF. Maksimal 2MB.</p>
                                </div>

                                <button type="submit"
                                        class="w-full py-2.5 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition shadow-[0_4px_12px_rgba(249,115,22,0.3)]">
                                    Kirim Bukti Pembayaran Kompensasi
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-4 text-center bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-400">
                            Tagihan kompensasi belum aktif atau tidak memerlukan pembayaran saat ini.
                        </div>
                    @endif
                </div>

            </div>

            {{-- Modal Lightbox untuk Perbesar Foto Bukti --}}
            <div x-show="activeImageModal"
                 x-cloak
                 style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
                 @keydown.escape.window="activeImageModal = null">
                <div class="relative max-w-3xl w-full bg-transparent p-2 text-center"
                     @click.away="activeImageModal = null">
                    <button type="button"
                            @click="activeImageModal = null"
                            class="absolute -top-10 right-0 text-white hover:text-gray-300 text-2xl font-bold p-2">
                        ✕ Tutup
                    </button>
                    <img :src="activeImageModal" alt="Preview Foto Bukti" class="max-h-[85vh] w-auto mx-auto rounded-2xl shadow-2xl border border-white/20">
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
