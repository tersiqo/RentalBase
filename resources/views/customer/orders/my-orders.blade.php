@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pesanan Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau status penyewaan alat dan riwayat transaksi Anda</p>
        </div>
        <div>
            <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? '']) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-orange-600 bg-orange-50 hover:bg-orange-100 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Sewa Alat Baru
            </a>
        </div>
    </div>

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

    <!-- Alpine Tab Navigation & Modals State -->
    <div x-data="{ 
        activeTab: 'Semua', 
        showDetailModal: false, 
        showCancelModal: false, 
        showReturnModal: false, 
        selectedOrder: null 
    }">
        
        <div class="border-b border-gray-100 mb-6 pb-3">
            <!-- Scroll di Mobile, Wrap di Desktop -->
            <div class="flex gap-2 overflow-x-auto md:overflow-visible md:flex-wrap hide-scroll-bar whitespace-nowrap snap-x">
                @foreach($tabs as $tab)
                    <button 
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
            <!-- Empty State jika belum ada pesanan sama sekali -->
            <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
                <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Pesanan</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                    Anda belum memiliki riwayat pesanan sewa. Temukan dan sewa berbagai peralatan berkualitas di toko kami!
                </p>
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? '']) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition shadow-sm">
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
                    @endphp
                    <div x-show="activeTab === 'Semua' || activeTab === '{{ $order->status_label }}' || activeTab === '{{ $order->status }}'"
                         x-transition.opacity.duration.300ms
                         class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow transition-shadow">
                        
                        <!-- Header Card -->
                        <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 text-base">{{ $order->order_number ?? ('ORD-' . $order->id) }}</span>
                                    @if($order->client)
                                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-medium">{{ $order->client->nama_usaha }}</span>
                                    @endif
                                </div>
                                <span class="text-sm text-gray-500 mt-1">
                                    Jadwal Sewa: <strong class="text-gray-700 font-semibold">{{ $order->date_range }}</strong>
                                </span>
                            </div>
                            <div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold tracking-wide {{ $order->status_color }}">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                        </div>

                        <!-- Body Card -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                            <div class="flex gap-4 items-center min-w-0">
                                <div class="shrink-0 flex flex-col items-center">
                                    <img src="{{ $order->primary_image }}" alt="Produk" class="w-20 h-20 object-cover rounded-xl bg-gray-50 border border-gray-100">
                                    @if($order->items_count > 1)
                                        <span class="text-[11px] text-gray-500 mt-1.5 font-medium">
                                            + {{ $order->items_count - 1 }} barang lainnya
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <h3 class="font-bold text-gray-900 text-base truncate">{{ $firstItem?->product?->name ?? 'Produk Sewa' }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $order->duration_days ?? 1 }} hari sewa &bull; {{ $order->isPickup() ? 'Ambil di Toko' : 'Pengiriman Kurir' }}
                                    </p>
                                    @if($order->shipping_address)
                                        <p class="text-xs text-gray-400 mt-1 truncate max-w-md">
                                            Alamat: {{ $order->shipping_address }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col sm:items-end justify-center shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-50">
                                <span class="text-xs text-gray-500 mb-0.5">Total Biaya:</span>
                                <span class="text-lg font-extrabold text-gray-900">{{ $order->formatted_total }}</span>
                            </div>
                        </div>

                        <!-- Footer Card -->
                        <div class="flex flex-wrap sm:flex-nowrap gap-3 mt-4 pt-3 border-t border-gray-50">
                            <button @click.prevent="selectedOrder = { 
                                        id: '{{ $order->order_number ?? ('ORD-' . $order->id) }}', 
                                        total: '{{ $order->formatted_total }}',
                                        status: '{{ $order->status_label }}',
                                        statusColor: '{{ $order->status_color }}',
                                        date: '{{ $order->date_range }}',
                                        duration: '{{ $order->duration_days ?? 1 }} hari',
                                        client: '{{ addslashes($order->client?->nama_usaha ?? '-') }}',
                                        shippingMethod: '{{ $order->isPickup() ? 'Ambil Sendiri di Toko' : 'Pengiriman Kurir' }}',
                                        shippingAddress: '{{ addslashes($order->shipping_address ?? '-') }}',
                                        items: [
                                            @foreach($order->orderItems as $item)
                                            {
                                                name: '{{ addslashes($item->product?->name ?? 'Produk') }}',
                                                qty: {{ $item->quantity ?? 1 }},
                                                price: 'Rp {{ number_format($item->price_per_day, 0, ',', '.') }}',
                                                subtotal: 'Rp {{ number_format($item->subtotal, 0, ',', '.') }}',
                                                image: '{{ $item->product?->main_image ? (str_starts_with($item->product->main_image, 'http') ? $item->product->main_image : Storage::url($item->product->main_image)) : 'https://placehold.co/100x100/f3f4f6/9ca3af?text=Produk' }}'
                                            },
                                            @endforeach
                                        ]
                                    }; showDetailModal = true" 
                                    class="flex-1 bg-orange-500 text-white font-semibold py-2.5 rounded-xl hover:bg-orange-600 transition text-sm flex items-center justify-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Lihat Detail
                            </button>
                            
                            @if($order->status_label === 'Belum Dibayar')
                                <button @click.prevent="selectedOrder = { id: '{{ $order->order_number ?? ('ORD-' . $order->id) }}' }; showCancelModal = true" 
                                        class="flex-1 bg-white text-red-600 border border-red-300 font-semibold py-2.5 rounded-xl hover:bg-red-50 transition text-sm">
                                    Batalkan Pesanan
                                </button>
                            @elseif($order->status_label === 'Sedang Disewa')
                                <button @click.prevent="selectedOrder = { id: '{{ $order->order_number ?? ('ORD-' . $order->id) }}' }; showReturnModal = true" 
                                        class="flex-1 bg-white text-gray-700 border border-gray-300 font-semibold py-2.5 rounded-xl hover:bg-gray-50 transition text-sm">
                                    Ajukan Pengembalian
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
        
        <!-- AREA MODALS -->
        
        <!-- Modal Detail Pesanan -->
        <div x-show="showDetailModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4 overflow-y-auto" style="display: none;" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden my-8" @click.away="showDetailModal = false">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">Detail Pesanan</h3>
                        <p class="text-xs text-gray-500" x-text="selectedOrder?.id"></p>
                    </div>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-lg hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    <!-- Status & Info Toko -->
                    <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <div>
                            <span class="text-xs text-gray-500 block">Status Pesanan:</span>
                            <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold" 
                                  :class="selectedOrder?.statusColor"
                                  x-text="selectedOrder?.status"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 block">Toko Rental:</span>
                            <span class="text-sm font-bold text-gray-900 mt-1 block" x-text="selectedOrder?.client"></span>
                        </div>
                    </div>

                    <!-- Jadwal & Pengiriman -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                            <span class="text-xs text-gray-500 block font-medium">Periode Sewa</span>
                            <span class="font-semibold text-gray-900 mt-1 block" x-text="selectedOrder?.date"></span>
                            <span class="text-xs text-gray-500 block mt-0.5" x-text="'Durasi: ' + selectedOrder?.duration"></span>
                        </div>
                        <div class="p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                            <span class="text-xs text-gray-500 block font-medium">Metode Penerimaan</span>
                            <span class="font-semibold text-gray-900 mt-1 block" x-text="selectedOrder?.shippingMethod"></span>
                            <span class="text-xs text-gray-500 block mt-0.5 truncate" x-text="selectedOrder?.shippingAddress"></span>
                        </div>
                    </div>

                    <!-- Daftar Item -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Peralatan yang Disewa</h4>
                        <div class="space-y-2">
                            <template x-for="(item, idx) in (selectedOrder?.items || [])" :key="idx">
                                <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img :src="item.image" :alt="item.name" class="w-12 h-12 rounded-lg object-cover bg-gray-50 border border-gray-100 shrink-0">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-sm text-gray-900 truncate" x-text="item.name"></p>
                                            <p class="text-xs text-gray-500" x-text="item.qty + ' unit &bull; ' + item.price + '/hari'"></p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="font-bold text-sm text-gray-900" x-text="item.subtotal"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Rincian Biaya -->
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="font-bold text-gray-800 text-sm">Total Tagihan:</span>
                        <span class="text-xl font-extrabold text-orange-600" x-text="selectedOrder?.total"></span>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button @click="showDetailModal = false" class="px-6 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-300 transition text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Batalkan Pesanan -->
        <div x-show="showCancelModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4" style="display: none;" x-transition>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.away="showCancelModal = false">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="font-bold text-xl text-gray-900 mb-2">Batalkan Pesanan?</h3>
                    <p class="text-gray-500 mb-6 text-sm">
                        Apakah Anda yakin ingin membatalkan pesanan <span class="font-bold text-gray-800" x-text="selectedOrder?.id"></span>? Tindakan ini tidak dapat diurungkan.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button @click="showCancelModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition text-sm">
                        Kembali
                    </button>
                    <button @click="showCancelModal = false; alert('Fitur pembatalan pesanan akan segera aktif.');" class="flex-1 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition text-sm">
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Pengembalian -->
        <div x-show="showReturnModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm p-4" style="display: none;" x-transition>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="showReturnModal = false">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900">Form Pengajuan Pengembalian</h3>
                    <button @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-500">
                        Silakan isi keterangan untuk pengembalian pesanan <span class="font-bold text-gray-800" x-text="selectedOrder?.id"></span>.
                    </p>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Alasan / Catatan Pengembalian</label>
                        <textarea class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-orange-500 focus:border-orange-500" rows="4" placeholder="Tuliskan catatan pengembalian (mis. kondisi barang saat selesai dipakai)..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button @click="showReturnModal = false" class="px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-300 transition text-sm">
                        Batal
                    </button>
                    <button @click="showReturnModal = false; alert('Pengajuan pengembalian berhasil dikirim ke admin.');" class="px-5 py-2.5 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition text-sm">
                        Ajukan Sekarang
                    </button>
                </div>
            </div>
        </div>
        
    </div>

</div>
@endsection

