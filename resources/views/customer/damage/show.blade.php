@extends('layouts.customer')

@section('title', 'Laporan Kerusakan - Pesanan #' . ($order->order_number ?? $order->id))

@section('content')
<div class="max-w-[1260px] mx-auto px-4 sm:px-6 py-6 sm:py-8 font-sans text-gray-700">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 mb-4">
        <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="hover:text-primary-600 transition">Beranda</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}" class="hover:text-primary-600 transition">Pesanan Saya</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('customer.orders.show', $order->id) }}" class="hover:text-primary-600 transition font-mono">{{ $order->order_number ?? ('ORD-' . $order->id) }}</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold">Klaim Kerusakan</span>
    </nav>

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-gray-200 mb-6">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-sm border border-rose-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
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
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 border border-emerald-100 shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Klaim Kerusakan</h2>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                Unit barang pada pesanan ini dalam kondisi aman atau toko belum mencatat adanya klaim kerusakan fisik pasca-pengembalian.
            </p>
            <a href="{{ route('customer.orders.show', $order->id) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-xl transition shadow-sm">
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
                <div class="mt-0.5 shrink-0">
                    @if(in_array(strtolower($damageCase?->case_status ?? ''), ['selesai', 'lunas']))
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['kompensasi_dibayar', 'menunggu_verifikasi']))
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif(in_array(strtolower($damageCase?->case_status ?? ''), ['dalam_tinjauan', 'negosiasi']))
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    @else
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
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
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_400px] gap-6 items-start" 
             x-data="{ 
                 activeImageModal: null,
                 showToast: false,
                 toastMessage: '',
                 copyToClipboard(text) {
                     if (navigator.clipboard) {
                         navigator.clipboard.writeText(text);
                         this.toastMessage = 'Berhasil disalin: ' + text;
                         this.showToast = true;
                         setTimeout(() => { this.showToast = false; }, 3000);
                     }
                 }
             }">

            {{-- Kolom Kiri: Detail Temuan, Galeri Foto, & Form Tanggapan --}}
            <div class="space-y-6">

                {{-- Card 1: Ringkasan Unit yang Rusak --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                        <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
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
                            <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
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
                        <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </span>
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
                                      class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400"></textarea>

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
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                        <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
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
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100">
                        <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </span>
                        <span>Pembayaran Kompensasi</span>
                    </h2>

                    @if($damageCase && $damageCase->payment_status === 'lunas')
                        {{-- State Lunas --}}
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h3 class="font-bold text-emerald-900 text-sm">Kompensasi Telah Lunas</h3>
                            <p class="text-xs text-emerald-700 mt-1">Pembayaran sebesar {{ $damageCase->formatted_total }} telah diverifikasi oleh toko.</p>
                        </div>

                    @elseif($damageCase && in_array($damageCase->payment_status, ['menunggu_verifikasi']) || in_array(strtolower($damageCase->case_status ?? ''), ['kompensasi_dibayar']))
                        {{-- State Menunggu Verifikasi --}}
                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="font-bold text-purple-900 text-sm">Menunggu Verifikasi Toko</h3>
                            <p class="text-xs text-purple-700 mt-1">
                                Bukti transfer kompensasi Anda telah tersimpan dan sedang diverifikasi oleh admin toko.
                            </p>
                            @if($compensationPayment && $compensationPayment->payment_proof)
                                <div class="mt-3 pt-3 border-t border-purple-200/60">
                                    <a href="{{ storage_url($compensationPayment->payment_proof) }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs text-purple-800 hover:text-purple-900 font-bold underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>Lihat Bukti Bayar Terkirim</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                    @elseif($damageCase && $damageCase->can_pay)
                        @php
                            $activePaymentMethods = $paymentMethods;
                            $bankMethods = $activePaymentMethods->filter(function($m) {
                                $bankName = strtoupper($m->bank_name ?? '');
                                $type = strtolower($m->type ?? '');
                                $accNum = (string)($m->account_number ?? '');

                                if (empty($accNum)) return false;
                                if ($type === 'qris' || $bankName === 'QRIS') return false;
                                if (str_starts_with($accNum, 'QRIS-')) return false;
                                if (!empty($m->qris_image) && empty($m->bank_name)) return false;

                                return true;
                            });

                            $qrisMethod = $activePaymentMethods->first(function($m) {
                                return ($m->type ?? '') === 'qris'
                                    || strtoupper($m->bank_name ?? '') === 'QRIS'
                                    || !empty($m->qris_image)
                                    || !empty($m->qris_image_path);
                            });

                            $firstBankId = $bankMethods->first()?->id ?? '';
                            $qrisId = $qrisMethod?->id ?? '';
                            $qrisImg = storage_url($qrisMethod?->qris_image ?? $qrisMethod?->qris_image_path);
                            $qrisMerchantName = $qrisMethod?->qris_merchant_name ?? $qrisMethod?->account_holder ?? $client->nama_usaha ?? 'RentalBase';
                            $qrisNmid = $qrisMethod?->account_number;
                        @endphp

                        {{-- State Form Bayar Kompensasi --}}
                        <div class="space-y-4"
                             x-data="{
                                 ptab: 'bank',
                                 bankMethodId: '{{ $firstBankId }}',
                                 qrisMethodId: '{{ $qrisId }}',
                                 get selectedPaymentMethodId() {
                                     return this.ptab === 'qris' ? this.qrisMethodId : this.bankMethodId;
                                 }
                             }">
                            {{-- Info Rekening & QRIS Toko --}}
                            <div>
                                <p class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">Metode Pembayaran Toko</p>

                                <!-- Payment Tabs -->
                                <div class="flex bg-gray-100 p-1 rounded-xl mb-3 text-xs font-bold">
                                    <button type="button" @click="ptab = 'bank'" :class="ptab === 'bank' ? 'bg-white text-primary-600 shadow-xs' : 'text-gray-500 hover:text-gray-900'" class="flex-1 py-2 rounded-lg transition-all text-center">
                                        Transfer Bank
                                    </button>
                                    <button type="button" @click="ptab = 'qris'" :class="ptab === 'qris' ? 'bg-white text-primary-600 shadow-xs' : 'text-gray-500 hover:text-gray-900'" class="flex-1 py-2 rounded-lg transition-all text-center">
                                        QRIS
                                    </button>
                                </div>

                                <!-- Tab Transfer Bank -->
                                <div x-show="ptab === 'bank'" class="space-y-2">
                                    @forelse($bankMethods as $method)
                                        @if(!empty($method->account_number))
                                            <div @click="bankMethodId = '{{ $method->id }}'"
                                                 :class="bankMethodId == '{{ $method->id }}' ? 'ring-2 ring-primary-500 border-primary-500 bg-primary-50/20' : 'border-gray-200 bg-white'"
                                                 class="p-3 border rounded-xl flex items-center justify-between gap-3 cursor-pointer transition">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[11px]">{{ $method->bank_name }}</span>
                                                        <b class="font-mono text-gray-900 text-sm tracking-wider">{{ $method->account_number }}</b>
                                                    </div>
                                                    <small class="text-xs text-gray-500 block mt-0.5">a.n. {{ $method->account_holder ?? $method->account_name }}</small>
                                                </div>
                                                <button type="button" @click.stop="copyToClipboard('{{ str_replace(' ', '', $method->account_number) }}')" class="px-2.5 py-1 text-xs font-bold text-primary-600 hover:bg-primary-50 rounded-lg border border-primary-200 transition">
                                                    Salin
                                                </button>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                            Rekening bank toko belum diatur. Silakan gunakan tab QRIS atau hubungi pihak toko.
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Tab QRIS -->
                                <div x-show="ptab === 'qris'" style="display: none;" class="text-center p-4 border border-gray-200 rounded-xl bg-white">
                                    @if($qrisImg)
                                        <img src="{{ $qrisImg }}" alt="Kode QRIS" class="w-44 h-44 mx-auto object-contain mb-2 rounded-lg border border-gray-100 p-1 shadow-xs">
                                    @else
                                        <div class="w-40 h-40 mx-auto mb-2 bg-gray-50 p-2 rounded-xl border border-gray-200 flex items-center justify-center">
                                            <svg viewBox="0 0 25 25" class="w-full h-full text-gray-900 fill-current" shape-rendering="crispEdges">
                                                <rect width="25" height="25" fill="#fff"/>
                                                <rect x="1" y="1" width="7" height="7"/>
                                                <rect x="2" y="2" width="5" height="5" fill="#fff"/>
                                                <rect x="3" y="3" width="3" height="3"/>
                                                <rect x="17" y="1" width="7" height="7"/>
                                                <rect x="18" y="2" width="5" height="5" fill="#fff"/>
                                                <rect x="19" y="3" width="3" height="3"/>
                                                <rect x="1" y="17" width="7" height="7"/>
                                                <rect x="2" y="18" width="5" height="5" fill="#fff"/>
                                                <rect x="3" y="19" width="3" height="3"/>
                                                <rect x="10" y="2" width="2" height="4"/>
                                                <rect x="13" y="6" width="3" height="3"/>
                                                <rect x="10" y="10" width="5" height="5"/>
                                                <rect x="17" y="12" width="4" height="2"/>
                                                <rect x="11" y="17" width="2" height="4"/>
                                                <rect x="15" y="18" width="5" height="4"/>
                                            </svg>
                                        </div>
                                    @endif

                                    @if($qrisNmid && !str_starts_with($qrisNmid, 'QRIS-'))
                                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-stone-50 border border-gray-200 rounded-xl text-xs mb-2">
                                            <span class="text-gray-500 font-semibold">NMID:</span>
                                            <span class="font-mono font-bold text-gray-900 tracking-wider">{{ $qrisNmid }}</span>
                                            <button type="button" @click="copyToClipboard('{{ $qrisNmid }}')" class="px-2 py-0.5 rounded text-[11px] font-bold text-primary-600 hover:bg-primary-50 transition border border-primary-200">
                                                Salin
                                            </button>
                                        </div>
                                    @endif

                                    <span class="text-xs font-bold text-gray-900 block">{{ $qrisMerchantName }}</span>
                                    <small class="text-[11px] text-gray-500 block mt-0.5">Scan dengan BCA Mobile, GoPay, OVO, Dana, atau ShopeePay</small>
                                </div>
                            </div>

                            {{-- Form Upload Bukti Transfer --}}
                            <form action="{{ route('customer.orders.damage.pay', $order->id) }}" 
                                  method="POST" 
                                  enctype="multipart/form-data" 
                                  x-data="{ submitting: false }"
                                  @submit="submitting = true"
                                  class="pt-3 border-t border-gray-100 space-y-3">
                                @csrf
                                <input type="hidden" name="damage_case_id" value="{{ $damageCase->id }}">
                                <input type="hidden" name="client_payment_method_id" :value="selectedPaymentMethodId">

                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-gray-800">Unggah Bukti Transfer Kompensasi</label>
                                    <span class="text-[11px] text-gray-500">
                                        Tujuan: <strong class="text-primary-600" x-text="ptab === 'qris' ? 'QRIS Toko' : 'Transfer Bank'"></strong>
                                    </span>
                                </div>

                                <input type="file"
                                       name="payment_proof"
                                       required
                                       accept="image/jpeg,image/png,image/jpg,application/pdf"
                                       class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 cursor-pointer">
                                <p class="text-[10px] text-gray-400">Format JPG, PNG, atau PDF. Maksimal 2MB.</p>

                                <button type="submit"
                                        :disabled="submitting"
                                        :class="submitting ? 'opacity-70 cursor-not-allowed' : ''"
                                        class="w-full py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span x-text="submitting ? 'Mengirim Bukti...' : 'Kirim Bukti Pembayaran Kompensasi'"></span>
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
                            class="absolute -top-10 right-0 text-white hover:text-gray-300 text-sm font-bold p-2 inline-flex items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Tutup</span>
                    </button>
                    <img :src="activeImageModal" alt="Preview Foto Bukti" class="max-h-[85vh] w-auto mx-auto rounded-2xl shadow-2xl border border-white/20">
                </div>
            </div>

            {{-- Toast Notification Salin --}}
            <div x-show="showToast" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-4"
                 class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white text-xs sm:text-sm font-semibold px-4 py-3 rounded-xl shadow-xl flex items-center gap-2 border-l-4 border-emerald-400" 
                 style="display: none;">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span x-text="toastMessage"></span>
            </div>

        </div>
    @endif

</div>
@endsection
