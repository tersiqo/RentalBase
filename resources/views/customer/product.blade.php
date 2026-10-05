@extends('layouts.customer')

@section('content')

@php
// Dummy data for the specific product detail
$product = [
    'name' => 'Sony Alpha A7 IV Body Only',
    'category' => 'Kamera Mirrorless',
    'specs' => 'Full-Frame 33MP',
    'chips' => ['4K 60p', '10-Bit 4:2:2', 'Dual Card Slot', 'BIONZ XR'],
    'price' => '350.000',
    'late_fee' => '35.000',
    'stock' => 3,
    'description' => 'Sony a7 IV adalah kamera hybrid tangguh yang dilengkapi sensor 33MP Full-Frame Exmor R CMOS terbaru dan prosesor BIONZ XR. Kamera ini menawarkan performa autofocus yang luar biasa dengan Real-time Eye AF untuk manusia, hewan, dan burung. Sangat cocok untuk fotografer wedding, portrait, maupun videografer profesional yang membutuhkan perekaman 4K 60p.',
    'images' => [
        'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=800&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=800&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1617005082833-1e09210214eb?q=80&w=800&auto=format&fit=crop'
    ]
];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="#" class="hover:text-primary-600 transition-colors">Katalog</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <a href="#" class="hover:text-primary-600 transition-colors">{{ $product['category'] }}</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-gray-900 font-medium line-clamp-1">{{ $product['name'] }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Top Split Section -->
    <div class="flex flex-col lg:flex-row gap-10 xl:gap-16 mb-16">
        
        <!-- Left: Image Gallery -->
        <div class="w-full lg:w-1/2 flex-shrink-0">
            <!-- Main Image -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-4 p-8 flex items-center justify-center aspect-square relative">
                <img id="main-image" src="{{ $product['images'][0] }}" alt="{{ $product['name'] }}" class="object-contain w-full h-full mix-blend-multiply">
                
                <!-- Stock Badge -->
                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full border border-orange-200 shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-sm font-semibold text-orange-600">Tersedia ({{ $product['stock'] }} unit)</span>
                </div>
            </div>
            
            <!-- Thumbnails -->
            <div class="grid grid-cols-4 gap-4">
                @foreach($product['images'] as $index => $img)
                <button onclick="document.getElementById('main-image').src='{{ $img }}'" class="bg-white border border-gray-200 rounded-xl overflow-hidden p-2 aspect-square hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all">
                    <img src="{{ $img }}" class="object-contain w-full h-full mix-blend-multiply opacity-80 hover:opacity-100">
                </button>
                @endforeach
            </div>
        </div>

        <!-- Right: Info & Actions -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center">
            
            <div class="mb-2">
                <span class="inline-block px-3 py-1 text-xs font-semibold text-primary-700 bg-primary-50 border border-primary-200 rounded-full mb-3">{{ $product['category'] }}</span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4 tracking-tight">{{ $product['name'] }}</h1>
            
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($product['chips'] as $chip)
                <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-1 rounded-md">{{ $chip }}</span>
                @endforeach
            </div>

            <hr class="border-gray-200 mb-6">

            <!-- Pricing -->
            <div class="mb-8">
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-4xl font-black text-gray-900">Rp {{ $product['price'] }}</span>
                    <span class="text-gray-500 font-medium mb-1">/ hari</span>
                </div>
                <div class="flex items-center gap-1.5 text-sm text-red-600 bg-red-50 inline-flex px-2.5 py-1 rounded-md border border-red-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Denda Keterlambatan: Rp {{ $product['late_fee'] }} / jam
                </div>
            </div>

            <!-- Booking form simulation -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
                
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <!-- Date Picker (Visual) -->
                    <div class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tanggal Sewa</label>
                        <div class="flex items-center gap-2">
                            <input type="date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <span class="text-gray-500">s/d</span>
                            <input type="date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <!-- Qty -->
                    <div class="w-full sm:w-32">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <div class="flex items-center border border-gray-300 rounded-md bg-white overflow-hidden shadow-sm">
                            <button type="button" class="px-3 py-2 bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-primary-600 transition-colors border-r border-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <input type="text" value="1" class="w-full text-center border-none focus:ring-0 sm:text-sm font-medium text-gray-900 py-2">
                            <button type="button" class="px-3 py-2 bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-primary-600 transition-colors border-l border-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <button type="button" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold text-lg py-4 px-8 rounded-lg shadow-lg shadow-primary-500/30 transition-all flex items-center justify-center gap-2 transform active:scale-[0.99]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Tambah ke Keranjang
                </button>
            </div>
            
            <div class="flex items-start gap-3 bg-blue-50/50 p-4 rounded-lg border border-blue-100">
                <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <p class="text-sm text-blue-800 font-medium">Bebas Deposit Uang. Jaminan cukup diverifikasi menggunakan KTP asli dan Swafoto saat proses Checkout.</p>
            </div>

        </div>
    </div>

    <!-- Bottom Tabs Section -->
    <div class="mt-12 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <!-- Tab Headers -->
        <div class="flex border-b border-gray-200 overflow-x-auto hide-scrollbar">
            <button class="px-8 py-4 text-sm font-bold text-primary-600 border-b-2 border-primary-500 whitespace-nowrap bg-primary-50/30">
                Deskripsi
            </button>
            <button class="px-8 py-4 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors whitespace-nowrap">
                Spesifikasi Teknis
            </button>
            <button class="px-8 py-4 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors whitespace-nowrap">
                Syarat Penyewaan
            </button>
        </div>
        
        <!-- Tab Content -->
        <div class="p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Informasi Produk</h3>
            <p class="text-gray-600 leading-relaxed max-w-4xl">
                {{ $product['description'] }}
            </p>
            <p class="text-gray-600 leading-relaxed max-w-4xl mt-4">
                Dilengkapi dengan baterai NP-FZ100 yang tahan lama, Anda tidak perlu khawatir kehabisan daya saat momen penting. Desain body yang ergonomis dan weather-sealed memastikan kenyamanan dan keamanan penggunaan di berbagai kondisi cuaca. Termasuk dalam paket penyewaan: Body Kamera, 2 Baterai, 1 Charger, Strap Original, dan Tas Kamera.
            </p>
        </div>
    </div>

</div>

@endsection
