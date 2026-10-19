@extends('layouts.customer')

@section('content')
<div class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full"
     x-data="{ 
        activeTab: 'Semua', 
        viewMode: 'list',
        selectedOrderId: null,
        lightboxOpen: false,
        lightboxImg: '',
        lightboxCaption: '',
        cancelModalOpen: false,
        cancelActionUrl: '',
        cancelOrderNumber: '',
        returnModalOpen: false,
        returnActionUrl: '',
        returnOrderNumber: '',
        receiveModalOpen: false,
        receiveActionUrl: '',
        receiveOrderNumber: '',
        toastMessage: '',
        showToast: false,

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            const orderParam = urlParams.get('order');
            if (orderParam) {
                const target = document.querySelector(`[data-order-number='${orderParam}']`);
                if (target && target.dataset.orderId) {
                    this.openDetail(parseInt(target.dataset.orderId));
                }
            }
        },
        openDetail(orderId) {
            this.selectedOrderId = orderId;
            this.viewMode = 'detail';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        closeDetail() {
            this.viewMode = 'list';
            this.selectedOrderId = null;
        },
        openLightbox(img, caption) {
            this.lightboxImg = img;
            this.lightboxCaption = caption;
            this.lightboxOpen = true;
        },
        openCancelModal(orderNum, actionUrl) {
            this.cancelOrderNumber = orderNum;
            this.cancelActionUrl = actionUrl;
            this.cancelModalOpen = true;
        },
        openReturnModal(orderNum, actionUrl) {
            this.returnOrderNumber = orderNum;
            this.returnActionUrl = actionUrl;
            this.returnModalOpen = true;
        },
        openReceiveModal(orderNum, actionUrl) {
            this.receiveOrderNumber = orderNum;
            this.receiveActionUrl = actionUrl;
            this.receiveModalOpen = true;
        },
        copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
                this.toast('Disalin: ' + text);
            }
        },
        toast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3500);
        }
    }">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm font-medium">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm font-medium">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- ==================== VIEW 1: DAFTAR PESANAN SAYA ==================== -->
    <div x-show="viewMode === 'list'">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Pesanan Saya</h1>
                <p class="text-sm text-gray-500 mt-1">Pantau status penyewaan alat dan riwayat transaksi Anda</p>
            </div>
            <div>
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? 'babyrental']) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 rounded-xl transition shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Sewa Alat Baru
                </a>
            </div>
        </div>

        @php
            $tabs = ['Semua', 'Belum Dibayar', 'Menunggu Verifikasi', 'Diproses', 'Dikirim', 'Sedang Disewa', 'Selesai', 'Batal'];
            $counts = [
                'Semua' => $orders->count(),
                'Belum Dibayar' => $orders->filter(fn($o) => $o->status_label === 'Belum Dibayar')->count(),
                'Menunggu Verifikasi' => $orders->filter(fn($o) => $o->status_label === 'Menunggu Verifikasi')->count(),
                'Diproses' => $orders->filter(fn($o) => $o->status_label === 'Diproses')->count(),
                'Dikirim' => $orders->filter(fn($o) => $o->status_label === 'Dikirim')->count(),
                'Sedang Disewa' => $orders->filter(fn($o) => $o->status_label === 'Sedang Disewa')->count(),
                'Selesai' => $orders->filter(fn($o) => $o->status_label === 'Selesai')->count(),
                'Batal' => $orders->filter(fn($o) => $o->status_label === 'Batal')->count(),
            ];
        @endphp

        <!-- Tabs Filter -->
        <div class="border-b border-gray-100 mb-6 pb-3">
            <div class="flex gap-2 overflow-x-auto md:overflow-visible md:flex-wrap hide-scroll-bar whitespace-nowrap snap-x">
                @foreach($tabs as $tab)
                    <button 
                        type="button"
                        @click.prevent="activeTab = '{{ $tab }}'"
                        :class="activeTab === '{{ $tab }}' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 bg-white border border-gray-200/80'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all focus:outline-none shrink-0 snap-start flex items-center gap-2">
                        <span>{{ $tab }}</span>
                        @if(($counts[$tab] ?? 0) > 0)
                            <span :class="activeTab === '{{ $tab }}' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700'" 
                                  class="px-2 py-0.5 rounded-full text-xs font-bold transition-colors">
                                {{ $counts[$tab] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        @if($orders->isEmpty())
            <!-- Empty State jika belum ada pesanan -->
            <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
                <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    🧸
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Pesanan</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                    Anda belum memiliki riwayat pesanan sewa. Temukan dan sewa berbagai peralatan berkualitas di toko kami!
                </p>
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? 'babyrental']) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white font-bold rounded-xl hover:bg-orange-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Mulai Sewa Sekarang
                </a>
            </div>
        @else
            <!-- Daftar Card Pesanan -->
            <div class="space-y-4">
                @foreach ($orders as $order)
                    @php
                        $firstItem = $order->orderItems->first();
                        $product = $firstItem?->product;
                    @endphp
                    <div x-show="activeTab === 'Semua' || activeTab === '{{ $order->status_label }}' || activeTab === '{{ $order->status }}'"
                         x-transition.opacity.duration.300ms
                         data-order-id="{{ $order->id }}"
                         data-order-number="{{ $order->order_number }}"
                         class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow transition-shadow">
                        
                        <!-- Header Card -->
                        <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100 gap-3">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold font-mono text-gray-900 text-base">{{ $order->order_number ?? ('ORD-' . $order->id) }}</span>
                                    @if($order->client)
                                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-lg font-medium">{{ $order->client->nama_usaha }}</span>
                                    @endif
                                </div>
                                <span class="text-xs sm:text-sm text-gray-500 mt-1">
                                    Jadwal Sewa: <strong class="text-gray-700 font-semibold">{{ $order->date_range }}</strong> &bull; Dipesan {{ $order->created_at ? $order->created_at->translatedFormat('d M Y') : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wide {{ $order->status_color }}">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                        </div>

                        <!-- Body Card -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                            <div class="flex gap-4 items-center min-w-0">
                                <div class="shrink-0 flex flex-col items-center">
                                    <img src="{{ $order->primary_image }}" alt="{{ $product?->name ?? 'Produk' }}" class="w-20 h-20 object-cover rounded-xl bg-gray-50 border border-gray-100 shadow-2xs">
                                    @if($order->items_count > 1)
                                        <span class="text-[11px] text-gray-500 mt-1.5 font-medium">
                                            + {{ $order->items_count - 1 }} barang lainnya
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <h3 class="font-bold text-gray-900 text-base truncate">{{ $product?->name ?? 'Produk Sewa' }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5 font-medium">
                                        {{ $order->duration_days ?? 1 }} hari sewa &bull; {{ $order->isPickup() ? 'Ambil di Toko' : 'Pengiriman Kurir' }}
                                    </p>
                                    @if($order->shipping_address)
                                        <p class="text-xs text-gray-400 mt-1 truncate max-w-md">
                                            Lokasi: {{ $order->shipping_address }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col sm:items-end justify-center shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-50">
                                <span class="text-xs text-gray-500 mb-0.5">Total Biaya:</span>
                                <span class="text-lg sm:text-xl font-mono font-extrabold text-gray-900">{{ $order->formatted_total }}</span>
                            </div>
                        </div>

                        <!-- Footer Card Action Buttons -->
                        <div class="flex flex-wrap sm:flex-nowrap gap-3 mt-4 pt-3 border-t border-gray-50">
                            <!-- Tombol Lihat Detail: Buka View Detail Lengkap -->
                            <button type="button"
                                    @click="openDetail({{ $order->id }})" 
                                    class="flex-1 bg-orange-500 text-white font-bold py-2.5 rounded-xl hover:bg-orange-600 transition text-sm flex items-center justify-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat Detail</span>
                            </button>
                            
                            @if($order->can_cancel)
                                <button type="button" 
                                        @click="openCancelModal('{{ $order->order_number ?? ('ORD-' . $order->id) }}', '{{ route('customer.orders.cancel', $order->id) }}')" 
                                        class="flex-1 bg-white text-red-600 border border-red-300 font-bold py-2.5 rounded-xl hover:bg-red-50 transition text-sm flex items-center justify-center gap-1">
                                    <span>Batalkan Pesanan</span>
                                </button>
                            @endif

                            @if($order->can_return)
                                <button type="button" 
                                        @click="openReturnModal('{{ $order->order_number ?? ('ORD-' . $order->id) }}', '{{ route('customer.orders.return', $order->id) }}')" 
                                        class="flex-1 bg-white text-orange-600 border border-orange-300 font-bold py-2.5 rounded-xl hover:bg-orange-50 transition text-sm flex items-center justify-center gap-1">
                                    <span>Ajukan Pengembalian</span>
                                </button>
                            @endif

                            @if($order->can_receive)
                                <button type="button" 
                                        @click="openReceiveModal('{{ $order->order_number ?? ('ORD-' . $order->id) }}', '{{ route('customer.orders.receive', $order->id) }}')" 
                                        class="flex-1 bg-emerald-600 text-white font-bold py-2.5 rounded-xl hover:bg-emerald-700 transition text-sm flex items-center justify-center gap-1 shadow-sm">
                                    <span>Terima Barang</span>
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ==================== VIEW 2: DETAIL PESANAN LENGKAP ==================== -->
    <div x-show="viewMode === 'detail'" style="display: none;" x-transition>
        @foreach($orders as $order)
            <div x-show="selectedOrderId === {{ $order->id }}" style="display: none;">
                @include('customer.orders.detail-content', ['order' => $order, 'client' => $client])
            </div>
        @endforeach
    </div>

    <!-- ==================== ACTION MODALS ==================== -->

    <!-- Modal 1: Batalkan Pesanan -->
    <div x-show="cancelModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center" @click.away="cancelModalOpen = false">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                ⚠️
            </div>
            <h3 class="font-bold text-lg text-gray-900 mb-2">Batalkan Pesanan?</h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-5 leading-relaxed">
                Pesanan <b class="font-mono text-gray-800" x-text="cancelOrderNumber"></b> akan dibatalkan dan proses sewa dihentikan.
            </p>
            <form :action="cancelActionUrl" method="POST">
                @csrf
                <div class="text-left mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Pembatalan (Opsional)</label>
                    <textarea name="reason" rows="2" placeholder="Tulis alasan pembatalan..." class="w-full text-xs p-2.5 rounded-xl border border-gray-300 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="cancelModalOpen = false" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Kembali
                    </button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-red-600 hover:bg-red-700 text-white transition shadow-sm">
                        Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Ajukan Pengembalian -->
    <div x-show="returnModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="returnModalOpen = false">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">↩️</span>
                    <h3 class="font-bold text-base text-gray-900">Ajukan Pengembalian Barang</h3>
                </div>
                <button type="button" @click="returnModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="returnActionUrl" method="POST" class="p-6">
                @csrf
                <p class="text-xs sm:text-sm text-gray-500 mb-4">
                    Pesanan <b class="font-mono text-gray-900" x-text="returnOrderNumber"></b>. Silakan pilih rencana pengembalian dan berikan keterangan kondisi barang saat selesai sewa.
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Metode Pengembalian</label>
                        <select name="return_method" class="w-full text-xs sm:text-sm p-2.5 rounded-xl border border-gray-300 focus:ring-orange-500 focus:border-orange-500 font-medium">
                            <option value="ambil_toko">Diantar langsung ke toko rental</option>
                            <option value="kurir">Dijemput kurir / dikirim via ekspedisi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Keterangan Kondisi Barang</label>
                        <textarea name="notes" rows="3" required placeholder="Contoh: Masa sewa telah selesai, seluruh unit bersih dan lengkap siap diantar Sabtu pagi." class="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:ring-orange-500 focus:border-orange-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="returnModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-sm bg-orange-500 hover:bg-orange-600 text-white transition shadow-sm">
                        Ajukan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Konfirmasi Terima Barang -->
    <div x-show="receiveModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center" @click.away="receiveModalOpen = false">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                📦
            </div>
            <h3 class="font-bold text-lg text-gray-900 mb-2">Konfirmasi Terima Barang?</h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-6 leading-relaxed">
                Pastikan seluruh perlengkapan pesanan <b class="font-mono text-gray-800" x-text="receiveOrderNumber"></b> sudah sampai di tangan Anda dalam kondisi baik dan berfungsi normal.
            </p>
            <form :action="receiveActionUrl" method="POST" class="flex gap-3">
                @csrf
                <button type="button" @click="receiveModalOpen = false" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Belum Diterima
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                    Ya, Sudah Diterima
                </button>
            </form>
        </div>
    </div>

    <!-- Modal 4: Lightbox Image Viewer -->
    <div x-show="lightboxOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden p-4" @click.away="lightboxOpen = false">
            <div class="flex justify-between items-center pb-2 mb-2 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900" x-text="lightboxCaption"></span>
                <button type="button" @click="lightboxOpen = false" class="text-gray-400 hover:text-gray-700 text-lg">✕</button>
            </div>
            <div class="p-1 bg-gray-50 rounded-xl flex items-center justify-center">
                <img :src="lightboxImg" :alt="lightboxCaption" class="max-h-[75vh] w-auto max-w-full rounded-lg object-contain">
            </div>
            <div class="pt-3 text-right">
                <button type="button" @click="lightboxOpen = false" class="px-4 py-1.5 bg-gray-100 text-gray-800 font-semibold rounded-xl text-xs hover:bg-gray-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white text-xs sm:text-sm font-semibold px-4 py-3 rounded-xl shadow-xl flex items-center gap-2 border-l-4 border-emerald-400" 
         style="display: none;">
        <span>✅</span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
