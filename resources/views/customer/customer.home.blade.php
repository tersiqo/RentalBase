@extends('layouts.customer')

@section('content')

@php
// Dummy data matching the image exactly
$dummyProducts = [
    [
        'name' => 'Sony Alpha A7 IV Body Only',
        'category' => 'Kamera Mirrorless',
        'specs' => 'Full-Frame 33MP',
        'chips' => '4K 60p • 10-Bit • Dual Slot',
        'price' => '350.000',
        'stock' => 3,
        'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Canon EOS R5 Body Only',
        'category' => 'Kamera Mirrorless',
        'specs' => '45MP 8K Raw',
        'chips' => 'IBIS 8-Stop • Dual CFexpress/SD',
        'price' => '450.000',
        'stock' => 2,
        'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Sony FE 24-70mm f/2.8 GM II',
        'category' => 'Lensa Profesional',
        'specs' => 'E-Mount Zoom',
        'chips' => 'Aperture f/2.8 • Filter 82mm',
        'price' => '200.000',
        'stock' => 4,
        'image' => 'https://images.unsplash.com/photo-1617005082833-1e09210214eb?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Sony FE 70-200mm f/2.8 GM',
        'category' => 'Lensa Profesional',
        'specs' => 'Telephoto Zoom',
        'chips' => 'Optical SteadyShot • Nano AR II',
        'price' => '275.000',
        'stock' => 2,
        'image' => 'https://images.unsplash.com/photo-1621217333550-9831969e6b4e?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Godox SL-150W II Continuous',
        'category' => 'Lighting & Studio',
        'specs' => 'Bowens Mount',
        'chips' => '5600K Daylight • CRI 96+ • Stand',
        'price' => '150.000',
        'stock' => 5,
        'image' => 'https://images.unsplash.com/photo-1589225529342-9908ce910cd5?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'DJI RS 3 Pro Gimbal Combo',
        'category' => 'Aksesoris & Stabilizer',
        'specs' => 'Carbon Fiber',
        'chips' => 'Payload 4.5kg • LIDAR Autofocus',
        'price' => '220.000',
        'stock' => 3,
        'image' => 'https://images.unsplash.com/photo-1631481546738-f86d63493e84?q=80&w=400&auto=format&fit=crop'
    ]
];
@endphp

<!-- Hero Section -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Breadcrumb / Status -->
        <div class="flex items-center gap-3 mb-6 text-sm">
            <div class="flex items-center gap-1.5 text-gray-500 font-medium">
                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                malang-camera.rentalbase.id
            </div>
            <span class="text-gray-300">•</span>
            <div class="flex items-center gap-1.5 text-green-600 font-medium text-xs bg-green-50 px-2 py-0.5 rounded-full border border-green-100">
                <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                Toko Buka
            </div>
            <div class="flex items-center gap-1.5 text-green-600 font-medium text-xs bg-green-50 px-2 py-0.5 rounded-full border border-green-100">
                <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                Siap Reservasi
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3 tracking-tight">Katalog Peralatan {{ $client->nama_usaha ?? 'KameraKu Studio' }}</h1>
        <p class="text-gray-500 text-lg mb-8 max-w-2xl">Sewa kamera dan perlengkapan fotografi profesional di Malang. Cek ketersediaan secara real-time.</p>

        <!-- Big Search Bar -->
        <div class="max-w-3xl">
            <div class="flex bg-white rounded-lg shadow-sm border border-gray-200 p-1.5 focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 transition-all">
                <div class="flex-grow flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" class="w-full pl-3 pr-3 py-2 border-none focus:ring-0 text-gray-700 placeholder-gray-400" placeholder="Cari kamera, lensa, atau aksesoris...">
                </div>
                <button class="bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-6 rounded-md transition-colors whitespace-nowrap">
                    Cari Alat
                </button>
            </div>
            
            <!-- Popular Searches -->
            <div class="flex flex-wrap items-center gap-2 mt-4 text-sm">
                <span class="text-gray-500 mr-1">Pencarian Populer:</span>
                <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Sony A7 IV</a>
                <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Canon R5</a>
                <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Lensa 24-70mm GM</a>
                <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Godox Lighting</a>
                <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Tripod Manfrotto</a>
            </div>
        </div>
    </div>
</div>

<!-- Main Layout -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Left Sidebar (Filters) -->
        <div class="w-full lg:w-64 flex-shrink-0 space-y-8">
            
            <!-- Category Filter -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Kategori Produk</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="#" class="flex items-center justify-between px-3 py-2 text-sm font-medium bg-primary-50 text-primary-700 border-l-4 border-primary-500 rounded-r">
                            Semua Peralatan
                            <span class="text-xs bg-white text-primary-600 border border-primary-200 px-1.5 py-0.5 rounded-full">(24)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded border-l-4 border-transparent hover:border-gray-300 transition-colors">
                            Kamera Mirrorless
                            <span class="text-xs text-gray-400">(8)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded border-l-4 border-transparent hover:border-gray-300 transition-colors">
                            Lensa Profesional
                            <span class="text-xs text-gray-400">(10)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded border-l-4 border-transparent hover:border-gray-300 transition-colors">
                            Lighting & Studio
                            <span class="text-xs text-gray-400">(4)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded border-l-4 border-transparent hover:border-gray-300 transition-colors">
                            Aksesoris & Audio
                            <span class="text-xs text-gray-400">(2)</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <hr class="border-gray-200">

            <!-- Availability Filter -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Status Ketersediaan</h3>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Hanya tampilkan unit tersedia</span>
                    <button type="button" class="bg-primary-500 relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" role="switch" aria-checked="true">
                        <span aria-hidden="true" class="translate-x-4 pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- Price Range Filter -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Rentang Harga Sewa</h3>
                <div class="flex items-center gap-2 mb-4">
                    <div class="relative rounded-md shadow-sm flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                            <span class="text-gray-500 sm:text-xs">Rp</span>
                        </div>
                        <input type="text" value="50.000" class="block w-full rounded-md border-0 py-1.5 pl-8 pr-2 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                    </div>
                    <span class="text-gray-400">-</span>
                    <div class="relative rounded-md shadow-sm flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                            <span class="text-gray-500 sm:text-xs">Rp</span>
                        </div>
                        <input type="text" value="500.000" class="block w-full rounded-md border-0 py-1.5 pl-8 pr-2 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                    </div>
                </div>
                <!-- Fake Range Slider -->
                <div class="relative w-full h-1 bg-gray-200 rounded mt-6 mb-2">
                    <div class="absolute bg-primary-500 h-1 rounded w-3/4 left-0"></div>
                    <div class="absolute w-4 h-4 bg-white border-2 border-primary-500 rounded-full -top-1.5 left-0 shadow cursor-pointer"></div>
                    <div class="absolute w-4 h-4 bg-white border-2 border-primary-500 rounded-full -top-1.5 left-3/4 shadow cursor-pointer"></div>
                </div>
                <div class="flex justify-between text-[10px] text-gray-400 mt-2">
                    <span>Min: 50rb</span>
                    <span>Maks: 500rb</span>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 relative overflow-hidden mt-8">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-indigo-900 mb-1">Bebas Deposit Uang</h4>
                        <p class="text-xs text-indigo-700/80 leading-relaxed">Jaminan administratif cukup verifikasi KTP & Swafoto saat konfirmasi booking. Tanpa tahan uang tunai.</p>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Right Content (Grid) -->
        <div class="flex-grow">
            <!-- Top bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                <p class="text-sm text-gray-500">Menampilkan <span class="font-semibold text-gray-900">6</span> dari <span class="font-semibold text-gray-900">24</span> Peralatan</p>
                <div class="flex items-center gap-2">
                    <label for="sort" class="text-sm text-gray-500">Urutkan:</label>
                    <select id="sort" class="block w-40 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6">
                        <option>Rekomendasi</option>
                        <option>Harga: Rendah ke Tinggi</option>
                        <option>Harga: Tinggi ke Rendah</option>
                        <option>Terbaru</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @foreach($products as $item)
                <!-- Card -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col h-full group relative">
                    
                    <!-- Image -->
                    <div class="relative h-48 w-full bg-gray-100 flex items-center justify-center p-4">
                        <img src="{{ $item->main_image ? \Illuminate\Support\Facades\Storage::url($item->main_image) : 'https://placehold.co/400x300/e2e8f0/475569?text=No+Image' }}" alt="{{ $item->name }}" class="object-contain w-full h-full mix-blend-multiply group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/400x300/e2e8f0/475569?text=Image+Error'">
                        <!-- Badge -->
                        <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full border border-orange-200 shadow-sm flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-xs font-semibold text-orange-600">Tersedia</span>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="font-bold text-gray-900 text-base mb-1 line-clamp-1">{{ $item->name }}</h3>
                        <p class="text-xs text-gray-500 mb-3">{{ $item->category->name ?? 'Tanpa Kategori' }}</p>
                        
                        <div class="mt-auto">
                            <!-- Price -->
                            <div class="flex items-baseline gap-1 mb-3">
                                <span class="font-bold text-xl text-gray-900">Rp {{ number_format($item->rental_price_per_day, 0, ',', '.') }}</span>
                                <span class="text-xs text-gray-500 font-medium">/ hari</span>
                            </div>
                            
                            <!-- Check -->
                            <div class="flex items-start gap-1.5 mb-5">
                                <svg class="w-3.5 h-3.5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-[10px] text-gray-500 leading-tight">Syarat: KTP + Selfie (Deposit Rp {{ number_format($item->deposit_fee, 0, ',', '.') }})</p>
                            </div>
                            
                            <!-- Button -->
                            <a href="/client/kameraku/products/{{ $item->id }}" class="block w-full py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-lg text-center transition-colors shadow-sm shadow-primary-500/30">
                                Sewa Alat
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>

            <!-- Pagination -->
            <div class="mt-10 flex items-center justify-between border-t border-gray-200 pt-6">
                <p class="text-xs text-gray-500 hidden sm:block">Menampilkan halaman <span class="font-bold">1</span> dari <span class="font-bold">4</span> (Total 24 Alat)</p>
                <div class="flex items-center gap-1 mx-auto sm:mx-0">
                    <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-900 border border-transparent rounded hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <a href="#" class="w-8 h-8 flex items-center justify-center text-white bg-primary-500 font-medium rounded shadow-sm text-sm">1</a>
                    <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded text-sm transition-colors font-medium">2</a>
                    <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded text-sm transition-colors font-medium">3</a>
                    <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded text-sm transition-colors font-medium">4</a>
                    <a href="#" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-900 border border-transparent rounded hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection