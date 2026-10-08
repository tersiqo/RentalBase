@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pesanan Saya</h1>
    </div>

    <!-- Alpine Tab Navigation & Card Filter -->
    <div x-data="{ activeTab: 'Belum Dibayar' }">
        
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
                        <button class="flex-1 bg-orange-500 text-white font-semibold py-2 rounded-lg hover:bg-orange-600 transition text-sm">
                            Lihat Detail
                        </button>
                        
                        @if($order['status'] == 'Belum Dibayar')
                            <button class="flex-1 bg-white text-red-500 border border-red-500 font-semibold py-2 rounded-lg hover:bg-red-50 transition text-sm">
                                Batalkan Pesanan
                            </button>
                        @elseif($order['status'] == 'Sedang Disewa')
                            <button class="flex-1 bg-white text-gray-600 border border-gray-300 font-semibold py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                                Ajukan Pengembalian
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
        
    </div>

</div>
@endsection
