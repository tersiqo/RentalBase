@php
    $client = $order->client ?? $client ?? null;
    $identity = $order->identityGuarantee;
    $shipment = $order->shipment;
    $payment = $order->latestPayment ?? $order->payments->first();
    $return = $order->returnModel;
    $isCancelled = in_array(strtolower($order->status), ['batal', 'dibatalkan', 'ditolak']);
    $isPickup = $order->isPickup();
    
    // Tracking URL whitelist
    $courierSlug = strtolower(trim($shipment?->courier_name ?? ''));
    $trackingUrl = null;
    if ($shipment && $shipment->tracking_number) {
        $awb = urlencode($shipment->tracking_number);
        if (str_contains($courierSlug, 'jne')) {
            $trackingUrl = 'https://www.jne.co.id/tracking-package?awb=' . $awb;
        } elseif (str_contains($courierSlug, 'sicepat')) {
            $trackingUrl = 'https://www.sicepat.com/checkAwb?awb=' . $awb;
        } elseif (str_contains($courierSlug, 'j&t') || str_contains($courierSlug, 'jnt')) {
            $trackingUrl = 'https://jet.co.id/track?awb=' . $awb;
        }
    }

    // Stepper configuration
    $steps = [
        ['label' => 'Menunggu Konfirmasi', 'desc' => 'Toko memeriksa data pesanan'],
        ['label' => 'Dikonfirmasi / Menunggu Pembayaran', 'desc' => 'Menunggu pembayaran dari penyewa'],
        ['label' => 'Pembayaran Terverifikasi', 'desc' => 'Toko memverifikasi pembayaran'],
        ['label' => 'Siap Kirim / Dikirim', 'desc' => 'Barang dipersiapkan atau dalam pengiriman'],
        ['label' => 'Diterima / Sedang Disewa', 'desc' => 'Masa sewa sedang berjalan'],
        ['label' => 'Dikembalikan', 'desc' => 'Barang dikembalikan ke toko'],
        ['label' => 'Selesai', 'desc' => 'Pesanan sewa selesai sepenuhnya'],
    ];

    $currentStep = $order->step_index;
@endphp

<div class="rb-detail-container font-sans text-gray-700">
    <!-- Breadcrumb & Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 mb-2">
            @if(isset($backRoute))
                <a href="{{ $backRoute }}" class="hover:text-orange-600 transition">Pesanan Saya</a>
            @else
                <button type="button" @click="closeDetail()" class="hover:text-orange-600 transition">Pesanan Saya</button>
            @endif
            <span class="text-gray-300">/</span>
            <span class="font-mono text-gray-700 font-semibold">{{ $order->order_number ?? ('ORD-' . $order->id) }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-3">
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Detail Pesanan</h3>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $order->status_color }}">
                        {{ $order->status_label }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-1 font-mono">
                    {{ $order->order_number ?? ('ORD-' . $order->id) }} &bull; Dipesan pada {{ $order->created_at ? $order->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <span class="sm:hidden inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $order->status_color }}">
                    {{ $order->status_label }}
                </span>

                @if(isset($backRoute))
                    <a href="{{ $backRoute }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </a>
                @else
                    <button type="button" 
                            @click="closeDetail()" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali ke Pesanan Saya</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Alert Kasus Kerusakan jika ada --}}
    @if($order->damageReports()->exists())
        @php
            $latestDamage = $order->damageReports()->with('damageCase')->latest()->first();
            $dCase = $latestDamage?->damageCase;
        @endphp
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h4 class="text-sm font-bold text-rose-900">Ada Laporan Kerusakan untuk Pesanan Ini</h4>
                    <p class="text-xs text-rose-700 mt-0.5">
                        Toko telah mengajukan klaim kerusakan fisik alat sewa. Status: <strong class="font-bold">{{ $dCase?->status_label ?? 'Menunggu Tanggapan' }}</strong>
                        @if($dCase && $dCase->total_amount > 0)
                            &bull; Total Kompensasi: <strong class="font-bold">{{ $dCase->formatted_total }}</strong>
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('customer.orders.damage', $order->id) }}"
               class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm whitespace-nowrap">
                Lihat & Tanggapi Klaim &rarr;
            </a>
        </div>
    @endif

    <!-- Main Grid: 2 Columns on desktop, 1 on mobile -->
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-6 items-start">
        
        <!-- Left Column: Main Progress & Information Stack -->
        <div class="space-y-6">

            <!-- Panel 1: Stepper Progres Pesanan -->
            <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-5">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">🧭</span>
                    <span>Progres Pesanan</span>
                </h2>

                <div class="overflow-x-auto pb-4 pt-2 -mx-2 px-2 scrollbar-thin">
                    <div class="flex items-start min-w-[720px] relative">
                        @foreach($steps as $idx => $step)
                            @php
                                $isDone = ! $isCancelled && ($idx < $currentStep || ($idx === $currentStep && $order->status_label === 'Selesai'));
                                $isCur  = ! $isCancelled && ($idx === $currentStep && $order->status_label !== 'Selesai');
                                $isBad  = $isCancelled && ($idx === $currentStep);
                            @endphp
                            <div class="flex-1 relative text-center px-2 group">
                                @if($idx > 0)
                                    <div class="absolute top-[17px] -left-1/2 w-full h-[3px] -z-0 transition-colors {{ ($idx <= $currentStep && !$isCancelled) ? 'bg-orange-500' : ($isCancelled && $idx <= $currentStep ? 'bg-red-400' : 'bg-gray-200') }}"></div>
                                @endif

                                <div class="relative z-10 w-9 h-9 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-extrabold transition-all duration-300
                                    @if($isDone)
                                        bg-orange-500 border-2 border-orange-500 text-white shadow-[0_2px_8px_rgba(249,115,22,0.4)]
                                    @elseif($isCur)
                                        bg-white border-2 border-orange-500 text-orange-600 ring-4 ring-orange-100 shadow-sm
                                    @elseif($isBad)
                                        bg-red-600 border-2 border-red-600 text-white ring-4 ring-red-100
                                    @else
                                        bg-white border-2 border-gray-200 text-gray-400
                                    @endif">
                                    @if($isDone)
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @elseif($isBad)
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                <div class="text-[12px] font-semibold leading-tight {{ ($isDone || $isCur) ? 'text-gray-900 font-bold' : ($isBad ? 'text-red-600 font-bold' : 'text-gray-400') }}">
                                    {{ $step['label'] }}
                                </div>
                            </div>
                        @endforeach

                        @if($isCancelled)
                            <div class="flex-1 relative text-center px-2">
                                <div class="absolute top-[17px] -left-1/2 w-full h-[3px] bg-red-400 -z-0"></div>
                                <div class="relative z-10 w-9 h-9 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-extrabold bg-red-600 border-2 border-red-600 text-white ring-4 ring-red-100 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                                <div class="text-[12px] font-bold text-red-600 leading-tight">
                                    Dibatalkan / Ditolak
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Status note alert banner -->
                <div class="mt-4 p-3.5 rounded-xl bg-orange-50 border border-orange-200 text-orange-900 text-xs sm:text-sm font-medium flex items-start gap-2.5 shadow-sm">
                    <span class="text-base shrink-0 mt-0.5">💡</span>
                    <div>
                        <strong class="font-bold">Info Status:</strong> {{ $order->status_note }}
                    </div>
                </div>
            </section>

            <!-- Panel 2: Detail Pesanan (Facts, Items, Summary) -->
            <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-5">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">🧾</span>
                    <span>Rincian Sewa & Peralatan</span>
                </h2>

                <!-- Grid Facts -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm pb-5 border-b border-gray-100">
                    <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                        <span class="text-xs text-gray-500 block mb-0.5">Nomor Pesanan</span>
                        <b class="text-gray-900 font-mono font-bold">{{ $order->order_number ?? ('ORD-' . $order->id) }}</b>
                    </div>
                    <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                        <span class="text-xs text-gray-500 block mb-0.5">Tanggal Checkout</span>
                        <b class="text-gray-900 font-semibold">{{ $order->created_at ? $order->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB</b>
                    </div>
                    <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                        <span class="text-xs text-gray-500 block mb-0.5">Periode Sewa</span>
                        <b class="text-gray-900 font-bold text-orange-600">{{ $order->date_range }}</b>
                        <small class="text-xs text-gray-500 block mt-0.5">Durasi sewa: {{ $order->duration_days ?? 1 }} hari</small>
                    </div>
                    <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                        <span class="text-xs text-gray-500 block mb-0.5">Toko Penyedia</span>
                        <b class="text-gray-900 font-semibold">{{ $client->nama_usaha ?? ($order->client->nama_usaha ?? 'BabyRent Malang') }}</b>
                    </div>
                    <div class="sm:col-span-2 p-3 bg-gray-50/80 rounded-xl border border-gray-100">
                        <span class="text-xs text-gray-500 block mb-0.5">{{ $isPickup ? 'Lokasi Pengambilan Sendiri di Toko' : 'Alamat Pengiriman (Kurir)' }}</span>
                        <b class="text-gray-900 font-medium leading-relaxed">
                            {{ $isPickup ? ($client->address ?? 'Ambil langsung di toko rental') : ($order->shipping_address ?? '-') }}
                        </b>
                    </div>
                </div>

                <!-- Items List -->
                <div class="py-4 space-y-3">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Item Yang Disewa</h4>
                    @foreach($order->orderItems as $item)
                        @php
                            $product = $item->product;
                            $img = $product?->main_image 
                                ? (str_starts_with($product->main_image, 'http') ? $product->main_image : Storage::url($product->main_image)) 
                                : 'https://placehold.co/100x100/f3f4f6/9ca3af?text=Produk';
                        @endphp
                        <div class="flex items-center justify-between p-3.5 bg-gray-50/50 hover:bg-gray-50 border border-gray-100 rounded-xl gap-4 transition-colors">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <img src="{{ $img }}" alt="{{ $product?->name ?? 'Produk' }}" class="w-14 h-14 rounded-xl object-cover bg-white border border-gray-200 shrink-0 shadow-2xs">
                                <div class="min-w-0">
                                    <h5 class="font-bold text-gray-900 text-sm truncate">{{ $product?->name ?? 'Produk Sewa' }}</h5>
                                    <p class="text-xs text-gray-500 mt-0.5 font-medium">
                                        {{ $item->quantity ?? 1 }} unit &bull; Rp {{ number_format((float) ($item->price_per_day ?? 0), 0, ',', '.') }}/hari &bull; {{ $order->duration_days ?? 1 }} hari
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-bold text-sm text-gray-900 font-mono">
                                    Rp {{ number_format((float) ($item->subtotal ?? (($item->price_per_day ?? 0) * ($item->quantity ?? 1) * ($order->duration_days ?? 1))), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Cost Summary -->
                <div class="pt-4 border-t border-gray-100 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal Sewa</span>
                        <span class="font-mono font-medium">Rp {{ number_format((float) ($order->orderItems->sum('subtotal') ?: $order->total_amount), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Ongkos Kirim</span>
                        <span class="font-mono font-medium">{{ $isPickup ? 'Gratis (Ambil Sendiri)' : 'Gratis' }}</span>
                    </div>
                    <div class="pt-3 border-t border-dashed border-gray-200 flex justify-between items-baseline text-base sm:text-lg font-extrabold text-gray-900">
                        <span>Total Tagihan:</span>
                        <span class="text-xl sm:text-2xl text-orange-600 font-mono font-extrabold">{{ $order->formatted_total }}</span>
                    </div>
                </div>
            </section>

            <!-- Panel 3: Verifikasi Identitas (Identity Guarantee from DB) -->
            <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">🪪</span>
                    <span>Verifikasi Identitas Penyewa</span>
                </h2>

                @if($identity)
                    @php
                        $rawIdStatus = strtolower($identity->status ?? 'menunggu');
                        $orderStatusNormalized = strtolower(trim(str_replace(['-', ' '], '_', $order->status ?? '')));
                        $isOrderApproved = in_array($orderStatusNormalized, ['siap_kirim', 'diproses', 'dikonfirmasi', 'dikirim', 'sedang_disewa', 'dikembalikan', 'selesai']);
                        
                        // Jika pesanan sudah disetujui toko dan jaminan tidak ditolak, jaminan otomatis dianggap diverifikasi
                        $idStatus = ($isOrderApproved && $rawIdStatus !== 'ditolak') ? 'diverifikasi' : $rawIdStatus;
                        
                        $docImg = $identity->identity_document_image ? (str_starts_with($identity->identity_document_image, 'http') ? $identity->identity_document_image : Storage::url($identity->identity_document_image)) : null;
                        $faceImg = $identity->face_image ? (str_starts_with($identity->face_image, 'http') ? $identity->face_image : Storage::url($identity->face_image)) : null;
                    @endphp

                    @if($idStatus === 'diverifikasi')
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 mb-4 shadow-sm">
                            <span class="text-lg shrink-0 mt-0.5">✅</span>
                            <div>
                                <strong class="font-bold text-emerald-950 block">Identitas Terverifikasi</strong>
                                <span class="text-xs sm:text-sm text-emerald-800">
                                    Dokumen KTP & selfie Anda telah diverifikasi oleh admin toko.
                                    @if(in_array($orderStatusNormalized, ['menunggu_pembayaran', 'belum_dibayar']))
                                        Pembayaran dapat dilanjutkan.
                                    @endif
                                </span>
                            </div>
                        </div>
                    @elseif($idStatus === 'ditolak')
                        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-900 flex items-start gap-3 mb-4 shadow-sm">
                            <span class="text-lg shrink-0 mt-0.5">❌</span>
                            <div>
                                <strong class="font-bold text-red-950 block">Verifikasi Identitas Ditolak</strong>
                                <span class="text-xs sm:text-sm text-red-800">Alasan dari toko: {{ $identity->rejection_reason ?? 'Foto KTP atau swafoto buram atau tidak sesuai. Silakan unggah ulang dokumen yang jelas.' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 flex items-start gap-3 mb-4 shadow-sm">
                            <span class="text-lg shrink-0 mt-0.5">🔎</span>
                            <div>
                                <strong class="font-bold text-blue-950 block">Sedang Diperiksa</strong>
                                <span class="text-xs sm:text-sm text-blue-800">Dokumen KTP & selfie Anda sedang dalam proses peninjauan oleh toko rental (maksimal 1×24 jam).</span>
                            </div>
                        </div>
                    @endif

                    <!-- Informasi Identitas & Preview Foto -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div>
                            <span class="text-gray-500 block text-xs">Nama Lengkap (KTP)</span>
                            <span class="font-bold text-gray-900 mt-0.5 block">{{ $identity->full_name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Nomor Induk Kependudukan (NIK)</span>
                            <span class="font-bold text-gray-900 font-mono mt-0.5 block">{{ $identity->identity_number }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-gray-500 block text-xs">Alamat Sesuai KTP</span>
                            <span class="font-medium text-gray-800 mt-0.5 block">{{ $identity->identity_address }}</span>
                        </div>

                        <!-- Foto Dokumen -->
                        <div class="sm:col-span-2 pt-2 border-t border-gray-200">
                            <span class="text-xs font-bold text-gray-500 block mb-2">Dokumen Terunggah:</span>
                            <div class="flex gap-4 flex-wrap">
                                @if($docImg)
                                    <div class="flex flex-col items-start gap-1">
                                        <button type="button" @click="openLightbox('{{ $docImg }}', 'Foto KTP')" class="group relative rounded-lg overflow-hidden border border-gray-200 bg-white hover:opacity-95 transition shadow-2xs">
                                            <img src="{{ $docImg }}" alt="KTP" class="w-28 h-20 object-cover" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-28 h-20 bg-slate-100 flex flex-col items-center justify-center text-slate-400 gap-1 text-[11px]\'><span class=\'text-base\'>📄</span><span>Foto KTP</span></div>';">
                                            <span class="absolute inset-0 bg-black/30 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-xs font-bold">🔍 Lihat</span>
                                        </button>
                                        <span class="text-[11px] text-gray-500 font-medium">Foto KTP</span>
                                    </div>
                                @endif

                                @if($faceImg)
                                    <div class="flex flex-col items-start gap-1">
                                        <button type="button" @click="openLightbox('{{ $faceImg }}', 'Foto Selfie memegang KTP')" class="group relative rounded-lg overflow-hidden border border-gray-200 bg-white hover:opacity-95 transition shadow-2xs">
                                            <img src="{{ $faceImg }}" alt="Selfie" class="w-28 h-20 object-cover" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-28 h-20 bg-slate-100 flex flex-col items-center justify-center text-slate-400 gap-1 text-[11px]\'><span class=\'text-base\'>🤳</span><span>Selfie KTP</span></div>';">
                                            <span class="absolute inset-0 bg-black/30 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-xs font-bold">🔍 Lihat</span>
                                        </button>
                                        <span class="text-[11px] text-gray-500 font-medium">Selfie KTP</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Reupload form if identity rejected -->
                    @if($order->can_reupload_identity)
                        <form action="{{ route('customer.orders.identity', $order->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 p-4 rounded-xl border border-dashed border-orange-300 bg-orange-50/50">
                            @csrf
                            <h5 class="font-bold text-sm text-gray-900 mb-2">Unggah Ulang Dokumen Identitas</h5>
                            <p class="text-xs text-gray-500 mb-3">Pilih foto KTP dan foto selfie memegang KTP terbaru yang jelas (maks. 2 MB per file).</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Foto KTP Baru</label>
                                    <input type="file" name="identity_document_image" accept="image/jpeg,image/png,image/jpg" required class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-500 file:text-white hover:file:bg-orange-600 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Foto Selfie Baru</label>
                                    <input type="file" name="face_image" accept="image/jpeg,image/png,image/jpg" required class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-500 file:text-white hover:file:bg-orange-600 cursor-pointer">
                                </div>
                            </div>
                            <button type="submit" class="w-full py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm">
                                Kirim Ulang Dokumen Identitas
                            </button>
                        </form>
                    @endif

                @else
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-gray-600 text-xs sm:text-sm flex items-center gap-3">
                        <span class="text-base">ℹ️</span>
                        <span>Pesanan ini tidak menyertakan jaminan identitas khusus.</span>
                    </div>
                @endif
            </section>

            <!-- Panel 4: Pengiriman & Logistik -->
            <section class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">🚚</span>
                    <span>Informasi Pengiriman</span>
                </h2>

                @if(in_array(strtolower($order->status), ['menunggu_pembayaran', 'belum_dibayar', 'menunggu_konfirmasi', 'menunggu_verifikasi']) && !$shipment)
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-gray-600 text-xs sm:text-sm flex items-start gap-3">
                        <span class="text-base shrink-0 mt-0.5">ℹ️</span>
                        <div>
                            <strong class="font-bold text-gray-900 block mb-0.5">Belum Ada Informasi Pengiriman</strong>
                            <span>Rincian serah-terima/kurir dan nomor resi pengiriman akan tersedia setelah pembayaran Anda terverifikasi oleh toko rental.</span>
                        </div>
                    </div>
                @else
                    <div class="space-y-3 text-xs sm:text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Metode Serah-Terima</span>
                            <strong class="text-gray-900">{{ $isPickup ? 'Ambil Sendiri di Toko' : 'Kurir (Diantar ke Alamat)' }}</strong>
                        </div>

                        @if(!$isPickup)
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Kurir Pengiriman</span>
                                <strong class="text-gray-900">{{ $shipment?->courier_name ?? 'Kurir Toko / Ekspedisi' }}</strong>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-500">Nomor Resi</span>
                                <div class="flex items-center gap-2">
                                    @if($shipment?->tracking_number)
                                        <span class="font-mono font-bold bg-gray-100 px-2.5 py-1 rounded-lg text-gray-900 text-xs tracking-wider">
                                            {{ $shipment->tracking_number }}
                                        </span>
                                        <button type="button" @click="copyToClipboard('{{ $shipment->tracking_number }}')" class="text-orange-600 font-bold hover:text-orange-700 text-xs">
                                            Salin
                                        </button>
                                    @else
                                        <span class="text-gray-400 italic">Belum tersedia</span>
                                    @endif
                                </div>
                            </div>
                            <div class="py-2 border-b border-gray-100">
                                <span class="text-gray-500 block mb-1">Alamat Penerima</span>
                                <p class="text-gray-900 font-medium">{{ $shipment?->delivery_address ?? $order->shipping_address }}</p>
                            </div>

                            @if($trackingUrl)
                                <a href="{{ $trackingUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 w-full py-2.5 mt-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 font-bold text-xs sm:text-sm rounded-xl transition shadow-2xs">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    Lacak Paket di Ekspedisi
                                </a>
                            @endif
                        @else
                            <div class="py-2 border-b border-gray-100">
                                <span class="text-gray-500 block mb-1">Alamat Pengambilan Toko</span>
                                <p class="text-gray-900 font-semibold">{{ $client->address ?? 'Toko RentalBase Malang' }}</p>
                                <span class="text-xs text-gray-500 block mt-1">Jam Operasional: {{ $client->operational_hours ?? '08:00 - 20:00 WIB' }}</span>
                            </div>
                        @endif

                        <!-- Tombol Konfirmasi Terima Barang jika status dikirim -->
                        @if($order->can_receive)
                            <div class="pt-3">
                                <button type="button" 
                                        @click="openReceiveModal('{{ $order->order_number ?? ('ORD-' . $order->id) }}', '{{ route('customer.orders.receive', $order->id) }}')" 
                                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Konfirmasi Terima Barang</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            <!-- Panel 5: Status Pembatalan & Refund (Tampil hanya jika pesanan Batal) -->
            @if($isCancelled)
                <section class="bg-white border border-red-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4">
                        <span class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-sm shadow-sm">💸</span>
                        <span>Status Pembatalan & Refund</span>
                    </h2>

                    @php
                        $refundStatus = strtolower($order->refund_status ?? 'tidak_ada');
                        $refundProof = $order->refund_proof ? (str_starts_with($order->refund_proof, 'http') ? $order->refund_proof : Storage::url($order->refund_proof)) : null;
                    @endphp

                    <div class="space-y-3 text-xs sm:text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Status Refund</span>
                            <div>
                                @if($refundStatus === 'refund_selesai')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Refund Selesai</span>
                                @elseif($refundStatus === 'menunggu_refund')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Menunggu Refund Admin</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">Tidak Ada Refund</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500">Nominal Pengembalian</span>
                            <span class="font-mono font-bold text-gray-900 text-base">
                                {{ $refundStatus !== 'tidak_ada' ? $order->formatted_total : 'Rp 0' }}
                            </span>
                        </div>

                        @if($order->cancellation_reason)
                            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 text-gray-700">
                                <span class="text-xs font-bold text-gray-500 block mb-1">Catatan / Alasan Pembatalan:</span>
                                <p class="text-xs sm:text-sm">{{ $order->cancellation_reason }}</p>
                            </div>
                        @endif

                        @if($refundStatus === 'refund_selesai' && $refundProof)
                            <div class="pt-2">
                                <span class="text-xs font-bold text-gray-600 block mb-1.5">Bukti Transfer Refund dari Toko:</span>
                                <figure @click="openLightbox('{{ $refundProof }}', 'Bukti Transfer Pengembalian Dana')" class="border border-gray-200 rounded-xl overflow-hidden cursor-zoom-in bg-gray-50 group hover:border-orange-500 transition max-w-sm">
                                    <img src="{{ $refundProof }}" alt="Bukti Refund" class="w-full max-h-48 object-contain bg-white">
                                    <figcaption class="p-2 text-center text-xs text-gray-500 bg-gray-50 border-t border-gray-100 group-hover:text-orange-600 font-medium">
                                        🔍 Klik untuk melihat bukti transfer refund
                                    </figcaption>
                                </figure>
                            </div>
                        @elseif($refundStatus === 'menunggu_refund')
                            <p class="text-xs text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200 mt-2">
                                Admin toko sedang memproses pengembalian dana ke rekening Anda. Bukti transfer refund akan muncul di sini setelah dana berhasil dikirim.
                            </p>
                        @endif
                    </div>
                </section>
            @endif

        </div>

        <!-- Right Column: Sticky Payment & Actions Panel -->
        <aside class="space-y-6 lg:sticky lg:top-24">
            
            <!-- Panel Pembayaran -->
            <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm" x-data="{ ptab: 'bank' }">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">💳</span>
                    <span>Pembayaran</span>
                </h2>

                @php
                    $payStatus = strtolower($payment?->status ?? 'belum');
                    $isOrderAdvanced = in_array(strtolower($order->status), [
                        'diproses', 'dikonfirmasi', 'siap_kirim', 'dikirim', 'sedang_disewa', 'selesai', 'dikembalikan'
                    ]);
                    $isPaidVerified = $isOrderAdvanced || in_array($payStatus, [
                        'terverifikasi', 'dikonfirmasi', 'sukses', 'lunas', 'berhasil'
                    ]);
                    $isPaidWaiting = !$isPaidVerified && in_array($payStatus, [
                        'menunggu_konfirmasi', 'menunggu_verifikasi', 'pending'
                    ]);
                @endphp

                @if($isPaidVerified)
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 mb-4 shadow-sm">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-emerald-600 font-bold">✅</span>
                            <strong class="font-bold text-emerald-950">Pembayaran Terverifikasi</strong>
                        </div>
                        <p class="text-xs text-emerald-800">
                            Pembayaran sebesar <b>{{ $order->formatted_total }}</b> telah diverifikasi penuh oleh admin toko.
                        </p>
                        @if($payment?->payment_proof)
                            @php
                                $proofImg = str_starts_with($payment->payment_proof, 'http') ? $payment->payment_proof : Storage::url($payment->payment_proof);
                            @endphp
                            <button type="button" @click="openLightbox('{{ $proofImg }}', 'Bukti Pembayaran')" class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 underline">
                                🔍 Lihat Bukti Pembayaran
                            </button>
                        @endif
                    </div>
                @elseif($isPaidWaiting)
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 mb-4 shadow-sm">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-blue-600 font-bold">⏳</span>
                            <strong class="font-bold text-blue-950">Bukti Pembayaran Diterima</strong>
                        </div>
                        <p class="text-xs text-blue-800">
                            Bukti pembayaran Anda sedang dalam antrean verifikasi oleh tim toko rental.
                        </p>
                        @if($payment?->payment_proof)
                            @php
                                $proofImg = str_starts_with($payment->payment_proof, 'http') ? $payment->payment_proof : Storage::url($payment->payment_proof);
                            @endphp
                            <button type="button" @click="openLightbox('{{ $proofImg }}', 'Bukti Pembayaran Terunggah')" class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 hover:text-blue-800 underline">
                                🔍 Lihat Bukti Pembayaran
                            </button>
                        @endif
                    </div>
                @elseif(!$isCancelled)
                    <!-- Belum dibayar: Tampilkan Metode Pembayaran & Upload Form -->
                    <div class="p-3.5 bg-orange-50 border border-dashed border-orange-300 rounded-xl mb-4 flex justify-between items-center">
                        <span class="text-xs text-orange-800 font-medium">Total Yang Harus Dibayar:</span>
                        <b class="text-lg font-bold font-mono text-orange-700">{{ $order->formatted_total }}</b>
                    </div>

                    <!-- Payment Tabs -->
                    <div class="flex bg-gray-100 p-1 rounded-xl mb-3 text-xs font-bold">
                        <button type="button" @click="ptab = 'bank'" :class="ptab === 'bank' ? 'bg-white text-orange-600 shadow-xs' : 'text-gray-500 hover:text-gray-900'" class="flex-1 py-2 rounded-lg transition-all text-center">
                            Transfer Bank
                        </button>
                        <button type="button" @click="ptab = 'qris'" :class="ptab === 'qris' ? 'bg-white text-orange-600 shadow-xs' : 'text-gray-500 hover:text-gray-900'" class="flex-1 py-2 rounded-lg transition-all text-center">
                            QRIS
                        </button>
                    </div>

                    <!-- Tab Bank Transfer -->
                    <div x-show="ptab === 'bank'" class="space-y-2 mb-4">
                        @forelse($client?->paymentMethods ?? [] as $method)
                            <div class="p-3 border border-gray-200 rounded-xl flex items-center justify-between gap-3 bg-white">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[11px]">{{ $method->bank_name }}</span>
                                        <b class="font-mono text-gray-900 text-sm tracking-wider">{{ $method->account_number }}</b>
                                    </div>
                                    <small class="text-xs text-gray-500 block mt-0.5">a.n. {{ $method->account_holder }}</small>
                                </div>
                                <button type="button" @click="copyToClipboard('{{ str_replace(' ', '', $method->account_number) }}')" class="px-2.5 py-1 text-xs font-bold text-orange-600 hover:bg-orange-50 rounded-lg border border-orange-200 transition">
                                    Salin
                                </button>
                            </div>
                        @empty
                            <!-- Fallback Mock Bank accounts dari dokumentasi BabyRent Malang -->
                            <div class="p-3 border border-gray-200 rounded-xl flex items-center justify-between gap-3 bg-white">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[11px]">BCA</span>
                                        <b class="font-mono text-gray-900 text-sm tracking-wider">8830 4521 77</b>
                                    </div>
                                    <small class="text-xs text-gray-500 block mt-0.5">a.n. {{ $client->nama_usaha ?? 'BabyRent Malang' }}</small>
                                </div>
                                <button type="button" @click="copyToClipboard('8830452177')" class="px-2.5 py-1 text-xs font-bold text-orange-600 hover:bg-orange-50 rounded-lg border border-orange-200 transition">
                                    Salin
                                </button>
                            </div>
                            <div class="p-3 border border-gray-200 rounded-xl flex items-center justify-between gap-3 bg-white">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[11px]">BRI</span>
                                        <b class="font-mono text-gray-900 text-sm tracking-wider">0123 01 045678 50 3</b>
                                    </div>
                                    <small class="text-xs text-gray-500 block mt-0.5">a.n. {{ $client->nama_usaha ?? 'BabyRent Malang' }}</small>
                                </div>
                                <button type="button" @click="copyToClipboard('012301045678503')" class="px-2.5 py-1 text-xs font-bold text-orange-600 hover:bg-orange-50 rounded-lg border border-orange-200 transition">
                                    Salin
                                </button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Tab QRIS -->
                    <div x-show="ptab === 'qris'" style="display: none;" class="text-center p-3 border border-gray-200 rounded-xl mb-4 bg-white">
                        @php
                            $qrisMethod = collect($client?->paymentMethods ?? [])->first(fn($m) => !empty($m->qris_image));
                            $qrisImg = $qrisMethod?->qris_image ? (str_starts_with($qrisMethod->qris_image, 'http') ? $qrisMethod->qris_image : Storage::url($qrisMethod->qris_image)) : null;
                        @endphp

                        @if($qrisImg)
                            <img src="{{ $qrisImg }}" alt="Kode QRIS" class="w-48 h-48 mx-auto object-contain mb-2">
                        @else
                            <!-- Dynamic QRIS Placeholder SVG from reference -->
                            <div class="w-44 h-44 mx-auto mb-2 bg-gray-50 p-2 rounded-xl border border-gray-200 flex items-center justify-center">
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
                        <span class="text-xs font-bold text-gray-900 block">{{ $client->nama_usaha ?? 'BABYRENT MALANG' }}</span>
                        <small class="text-[11px] text-gray-500 block">Scan dengan BCA Mobile, GoPay, OVO, Dana, atau ShopeePay</small>
                    </div>

                    <!-- Upload Proof Form / Alert -->
                    @if($order->can_upload_payment)
                        <form action="{{ route('customer.orders.payment', $order->id) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-gray-100">
                            @csrf
                            <label class="block text-xs font-bold text-gray-800 mb-1.5">Unggah Bukti Transfer</label>
                            <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/jpg,application/pdf" required class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-500 file:text-white hover:file:bg-orange-600 cursor-pointer mb-2.5">
                            <button type="submit" class="w-full py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Kirim Bukti Pembayaran</span>
                            </button>
                        </form>
                    @else
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                            <strong class="font-bold block mb-0.5">🔒 Pembayaran Belum Dibuka</strong>
                            <span>Unggah bukti bayar akan tersedia setelah dokumen verifikasi identitas Anda disetujui oleh admin toko.</span>
                        </div>
                    @endif
                @endif
            </section>

            <!-- Panel Aksi -->
            <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2.5 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm shadow-sm">⚡</span>
                    <span>Tindakan Tersedia</span>
                </h2>

                <div class="space-y-3">
                    @if($return && in_array($return->return_status, ['diajukan', 'diproses', 'dalam_pengembalian', 'diterima', 'selesai']))
                        <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl text-blue-900 text-xs space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-blue-950">
                                <span>↩️</span>
                                <span>Pengembalian Barang ({{ ucfirst(str_replace('_', ' ', $return->return_status)) }})</span>
                            </div>
                            <p class="text-blue-800">
                                Metode: <b>{{ $return->return_method === 'kurir' ? 'Ekspedisi / Kurir (' . ($return->courier_name ?? '-') . ')' : 'Antar Langsung ke Toko' }}</b>
                                @if($return->return_method === 'kurir' && $return->tracking_number)
                                    &bull; Resi: <span class="font-mono font-bold">{{ $return->tracking_number }}</span>
                                @endif
                            </p>
                            @if((float) ($return->late_fee_amount ?? 0) > 0)
                                <p class="text-blue-800">
                                    Denda Keterlambatan ({{ $return->late_hours ?? 0 }} jam):
                                    <b class="font-mono">Rp {{ number_format((float) $return->late_fee_amount, 0, ',', '.') }}</b>
                                    ({{ ucfirst(str_replace('_', ' ', $return->late_fee_status ?? 'menunggu_pembayaran')) }})
                                </p>
                            @endif
                            @if($return->late_fee_proof)
                                @php
                                    $feeProofImg = str_starts_with($return->late_fee_proof, 'http') ? $return->late_fee_proof : Storage::url($return->late_fee_proof);
                                @endphp
                                <button type="button" @click="openLightbox('{{ $feeProofImg }}', 'Bukti Pembayaran Denda')" class="inline-flex items-center gap-1 font-bold text-blue-700 hover:underline pt-1">
                                    🔍 Lihat Bukti Pembayaran Denda
                                </button>
                            @endif
                        </div>
                    @endif

                    @if($order->can_cancel)
                        <button type="button" 
                                @click="openCancelModal('{{ $order->order_number ?? ('ORD-' . $order->id) }}', '{{ route('customer.orders.cancel', $order->id) }}')" 
                                class="w-full py-2.5 border border-red-300 text-red-600 hover:bg-red-50 font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Batalkan Pesanan</span>
                        </button>
                    @endif

                    @if($order->can_return)
                        <a href="{{ route('customer.orders.return.form', $order->id) }}" 
                           class="w-full py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            <span>Ajukan Pengembalian</span>
                        </a>
                    @endif

                    @if($order->can_receive)
                        <button type="button" 
                                @click="openReceiveModal('{{ $order->order_number ?? ('ORD-' . $order->id) }}', '{{ route('customer.orders.receive', $order->id) }}')" 
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Konfirmasi Terima Barang</span>
                        </button>
                    @endif

                    @if(!$order->can_cancel && !$order->can_return && !$order->can_receive)
                        <p class="text-xs text-gray-400 italic text-center py-1">
                            Tidak ada aksi lanjutan yang perlu dilakukan untuk status pesanan ini.
                        </p>
                    @endif

                    <div class="border-t border-gray-100 pt-3">
                        @php
                            $waPhone = preg_replace('/[^0-9]/', '', $client->phone ?? '081234567890');
                            if (str_starts_with($waPhone, '0')) {
                                $waPhone = '62' . substr($waPhone, 1);
                            }
                            $waText = urlencode('Halo ' . ($client->nama_usaha ?? 'Admin') . ', saya ingin bertanya mengenai pesanan saya ' . ($order->order_number ?? ('ORD-' . $order->id)));
                        @endphp
                        <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-800 font-bold rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-2xs">
                            <span class="text-base text-emerald-600">💬</span>
                            <span>Hubungi Toko Rental</span>
                        </a>
                    </div>
                </div>
            </section>

        </aside>

    </div>
</div>
