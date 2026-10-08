@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pesanan Saya</h1>
    </div>

    <!-- Alpine Tab Navigation & Card Filter & Modals State -->
    <div x-data="{ 
        activeTab: 'Belum Dibayar', 
        showDetailModal: false, 
        showCancelModal: false, 
        showReturnModal: false, 
        selectedOrder: null 
    }">
        
        <div class="border-b border-gray-100 mb-6 pb-3">
            <!-- Scroll di Mobile, Wrap (turun ke bawah) di Desktop -->
            <div class="flex gap-2 overflow-x-auto md:overflow-visible md:flex-wrap hide-scroll-bar whitespace-nowrap snap-x">
                @php
                    $tabs = ['Semua', 'Belum Dibayar', 'Menunggu Verifikasi', 'Diproses', 'Dikirim', 'Sedang Disewa', 'Selesai', 'Batal'];
                @endphp
                
                @foreach($tabs as $tab)
                    <button 
                        @click.prevent="activeTab = '{{ $tab }}'"
                        :class="activeTab === '{{ $tab }}' ? 'bg-orange-500 text-white' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors focus:outline-none shrink-0 snap-start">
                        {{ $tab }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Daftar Card Pesanan -->
        <div class="space-y-4">
            @foreach ($orders as $order)
                <div x-show="activeTab === 'Semua' || activeTab === '{{ $order['status'] }}'"
                     x-transition.opacity.duration.300ms
                     class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm"
                     style="display: none;">
                    
                    <!-- Header Card -->
                    <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-900 text-base">{{ $order['id'] }}</span>
                            <span class="text-sm text-gray-500 mt-0.5">Tgl: {{ $order['date'] }}</span>
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold tracking-wide {{ $order['status_color'] }}">
                                {{ $order['status'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Body Card -->
                    <div class="flex gap-4 items-center mb-5">
                        <div class="shrink-0 flex flex-col items-center">
                            <img src="{{ $order['image'] }}" alt="Produk" class="w-20 h-20 object-cover rounded-lg bg-gray-50">
                            @if($order['items_count'] > 1)
                                <span class="text-[11px] text-gray-500 mt-1.5">
                                    + {{ $order['items_count'] - 1 }} barang lainnya
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-xs text-gray-500 mb-0.5">Total:</span>
                            <span class="text-lg font-bold text-gray-900">{{ $order['total'] }}</span>
                        </div>
                    </div>

                    <!-- Footer Card -->
                    <div class="flex gap-3 mt-4">
                        <button @click.prevent="selectedOrder = { id: '{{ $order['id'] }}', total: '{{ $order['total'] }}' }; showDetailModal = true" 
                                class="flex-1 bg-orange-500 text-white font-semibold py-2 rounded-lg hover:bg-orange-600 transition text-sm">
                            Lihat Detail
                        </button>
                        
                        @if($order['status'] == 'Belum Dibayar')
                            <button @click.prevent="selectedOrder = { id: '{{ $order['id'] }}' }; showCancelModal = true" 
                                    class="flex-1 bg-white text-red-500 border border-red-500 font-semibold py-2 rounded-lg hover:bg-red-50 transition text-sm">
                                Batalkan Pesanan
                            </button>
                        @elseif($order['status'] == 'Sedang Disewa')
                            <button @click.prevent="selectedOrder = { id: '{{ $order['id'] }}' }; showReturnModal = true" 
                                    class="flex-1 bg-white text-gray-600 border border-gray-300 font-semibold py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                                Ajukan Pengembalian
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
        
        <!-- AREA MODALS -->
        
        <!-- Modal Detail Pesanan -->
        <div x-show="showDetailModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm" style="display: none;" x-transition>
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden" @click.away="showDetailModal = false">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-900">Detail Pesanan</h3>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-2">Menampilkan detail untuk pesanan:</p>
                    <p class="text-xl font-bold text-gray-900" x-text="selectedOrder?.id"></p>
                    <p class="text-gray-500 mt-4 text-sm bg-gray-50 p-4 rounded-lg border border-gray-100">
                        Ini adalah teks dummy untuk detail barang. Pada implementasi sebenarnya, list produk, spesifikasi, serta rincian pembayaran akan dirender di sini.
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button @click="showDetailModal = false" class="px-6 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Batalkan Pesanan -->
        <div x-show="showCancelModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm" style="display: none;" x-transition>
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" @click.away="showCancelModal = false">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="font-bold text-xl text-gray-900 mb-2">Batalkan Pesanan?</h3>
                    <p class="text-gray-500 mb-6">Apakah Anda yakin ingin membatalkan pesanan <span class="font-bold text-gray-700" x-text="selectedOrder?.id"></span>? Tindakan ini tidak dapat diurungkan.</p>
                </div>
                <div class="flex gap-3">
                    <button @click="showCancelModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                        Kembali
                    </button>
                    <button @click.prevent="" class="flex-1 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Pengembalian -->
        <div x-show="showReturnModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm" style="display: none;" x-transition>
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4" @click.away="showReturnModal = false">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-900">Form Pengajuan Pengembalian</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-500 mb-4">Silakan isi alasan pengembalian untuk pesanan <span class="font-bold text-gray-700" x-text="selectedOrder?.id"></span>.</p>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Alasan Pengembalian</label>
                    <textarea class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-orange-500 focus:border-orange-500" rows="4" placeholder="Tuliskan alasan secara detail..."></textarea>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button @click="showReturnModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button @click.prevent="" class="px-5 py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition">
                        Ajukan Sekarang
                    </button>
                </div>
            </div>
        </div>
        
    </div>

</div>
@endsection
