<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $client->nama_usaha }} - RentalBase Client</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F4F5F8;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 16px;
            width: 16px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #15803d;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="bg-[#F4F5F8] text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Left Logo & Domain Badge -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-emerald-400 font-bold text-xs shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">RentalBase</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-1 rounded">CLIENT</span>
                        </div>
                        <div class="text-base font-extrabold text-slate-900 leading-tight">{{ $client->nama_usaha }}</div>
                    </div>
                </div>

                <!-- Subdomain Tag -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50/80 text-indigo-700 border border-indigo-100 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <span>{{ $client->subdomain }}.rentalbase.com</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain]) }}" class="px-4 py-1.5 rounded-xl bg-indigo-100/70 text-indigo-800 font-bold text-xs sm:text-sm transition">Katalog</a>
                <a href="#" class="px-4 py-1.5 rounded-xl text-slate-600 hover:text-slate-900 font-medium text-xs sm:text-sm transition">Cara Sewa</a>
                <a href="#" class="px-4 py-1.5 rounded-xl text-slate-600 hover:text-slate-900 font-medium text-xs sm:text-sm transition">Bantuan</a>
            </nav>

            <!-- Right Actions -->
            <div class="flex items-center gap-4">
                <!-- Shopping Cart -->
                <button class="relative p-2 text-slate-700 hover:text-slate-900 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cart-count" class="absolute top-1 right-1 w-4 h-4 bg-emerald-700 text-white rounded-full text-[10px] font-bold flex items-center justify-center">0</span>
                </button>

                <!-- Profile -->
                <div class="flex items-center gap-2 cursor-pointer pl-2 border-l border-slate-200" onclick="openAuthModal()">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" alt="Akun Saya" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                    <span class="text-xs font-bold text-slate-800 hidden sm:inline">Akun Saya</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-grow">

        <!-- Store Header Banner Card -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $client->nama_usaha }}</h1>
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] sm:text-[11px] font-bold tracking-wider px-2.5 py-0.5 rounded-full uppercase">KATALOG PENYEWAAN RESMI</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sistem Siap Reservasi</span>
                </div>
            </div>
            
            <p class="text-slate-600 text-xs sm:text-sm mt-2 max-w-4xl leading-relaxed">
                {{ $client->deskripsi }}
            </p>

            <div class="flex flex-wrap items-center gap-3 sm:gap-6 mt-4 pt-3.5 border-t border-slate-100 text-xs text-slate-600 font-medium">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Subdomain: <strong class="text-slate-800">{{ $client->subdomain }}</strong></span>
                </div>
                <span class="text-slate-300 hidden sm:inline">•</span>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Buka: <strong class="text-slate-800">08.00 – 20.00 WIB</strong></span>
                </div>
                <span class="text-slate-300 hidden sm:inline">•</span>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Bebas deposit uang tunai <span class="text-slate-400">(Cukup KTP/SIM valid)</span></span>
                </div>
            </div>
        </div>

        <!-- Catalog Sidebar & Products Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            <!-- Left Filter Sidebar -->
            <aside class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs lg:col-span-1 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 font-bold text-slate-900 text-sm">
                        <svg class="w-4.5 h-4.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter Peralatan</span>
                    </div>
                    <button onclick="resetFilters()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition">Reset</button>
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">KATEGORI</h3>
                    <div class="space-y-2 text-xs font-medium text-slate-700">
                        <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="category" value="all" checked onchange="filterProducts()" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                <span class="font-bold text-slate-900">Semua Kategori</span>
                            </div>
                            <span class="text-slate-400 font-normal">({{ count($products) }})</span>
                        </label>
                        @foreach($categories as $cat)
                            <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="category" value="{{ strtolower($cat->nama) }}" onchange="filterProducts()" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                    <span>{{ $cat->nama }}</span>
                                </div>
                                <span class="text-slate-400 font-normal">
                                    ({{ $products->where('category_id', $cat->id)->count() }})
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Price Range -->
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">BIAYA SEWA / HARI</h3>
                    <div class="px-1 mb-3">
                        <input type="range" min="10000" max="300000" value="300000" id="priceRange" class="w-full h-1.5 bg-emerald-600 rounded-lg appearance-none cursor-pointer">
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <div class="flex-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                            <div class="text-[10px] text-slate-400 font-medium">Min</div>
                            <div class="font-bold text-slate-800">Rp 10.000</div>
                        </div>
                        <span class="text-slate-300">-</span>
                        <div class="flex-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                            <div class="text-[10px] text-slate-400 font-medium">Maks</div>
                            <div id="maxPriceText" class="font-bold text-slate-800">Rp 300.000</div>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Stock Availability -->
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">KETERSEDIAAN STOK</h3>
                    <div class="space-y-2 text-xs font-medium text-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span class="font-semibold text-slate-800">Siap Sewa (In-Stock)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Tersedia Hari Ini</span>
                        </label>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Delivery Method -->
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">METODE SERAH TERIMA</h3>
                    <div class="space-y-2 text-xs font-medium text-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Ambil di Hub Logistik</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Kirim Kurir / Armada</span>
                        </label>
                    </div>
                </div>

                <!-- Callout Box -->
                <div class="bg-indigo-50/80 border border-indigo-100/90 rounded-xl p-3.5 flex items-start gap-2.5">
                    <div class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-indigo-950">Bebas Deposit Tunai</h4>
                        <p class="text-[11px] text-indigo-700 leading-snug mt-0.5">Verifikasi KTP/SIM aman & terenkripsi via sistem RentalBase.</p>
                    </div>
                </div>
            </aside>

            <!-- Right Main Products Section -->
            <section class="lg:col-span-3 space-y-4">

                <!-- Search & Sort Controls Bar -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Cari nama alat..." class="w-full bg-white border border-slate-200/80 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 shadow-xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div class="relative">
                        <select class="appearance-none bg-white border border-slate-200/80 rounded-xl px-4 pr-9 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 shadow-xs cursor-pointer">
                            <option>Urutan: Rekomendasi</option>
                            <option>Harga: Terendah</option>
                            <option>Harga: Tertinggi</option>
                            <option>Stok Terbanyak</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Result Status & Active Filter Pills -->
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="text-slate-500 font-medium">Menampilkan <strong id="visible-count" class="text-slate-800">{{ count($products) }}</strong> dari <strong class="text-slate-800">{{ count($products) }}</strong> unit siap sewa</span>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-200/70 text-slate-700 font-semibold text-[11px]">
                            Semua Kategori
                            <button class="hover:text-slate-900 ml-0.5">✕</button>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-semibold text-[11px]">
                            In-Stock
                            <button class="hover:text-indigo-950 ml-0.5">✕</button>
                        </span>
                    </div>
                </div>

                <!-- Products Grid -->
                <div id="product-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">

                    @forelse($products as $product)
                        @php
                            $categoryName = $product->category->nama ?? 'Peralatan';
                            $img = $product->foto ?: 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=600&auto=format&fit=crop&q=80';
                            $tags = array_filter(array_map('trim', explode(',', $product->deskripsi ?? '')));
                        @endphp
                        <div class="product-item bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:shadow-md transition group" data-name="{{ $product->nama }}" data-category="{{ strtolower($categoryName) }}" data-price="{{ (int)$product->harga_sewa }}">
                            <div class="relative aspect-[4/3] w-full shrink-0 overflow-hidden bg-slate-100">
                                <img src="{{ $img }}" alt="{{ $product->nama }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                
                                @if($product->stok <= 1)
                                    <span class="absolute top-3 left-3 bg-amber-50/95 backdrop-blur-sm text-amber-800 border border-amber-200/80 text-[11px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Sisa {{ $product->stok }} unit
                                    </span>
                                @else
                                    <span class="absolute top-3 left-3 bg-emerald-50/95 backdrop-blur-sm text-emerald-800 border border-emerald-200/60 text-[11px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia ({{ $product->stok }} unit)
                                    </span>
                                @endif

                                <span class="absolute bottom-3 left-3 bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-semibold px-2.5 py-1 rounded-lg">
                                    {{ $categoryName }}
                                </span>
                            </div>
                            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug line-clamp-1 group-hover:text-emerald-700 transition">{{ $product->nama }}</h3>
                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                        @foreach($tags as $tag)
                                            <span class="bg-indigo-50/70 text-indigo-700 text-[11px] font-semibold px-2 py-0.5 rounded-md">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] text-slate-400 font-medium">Biaya Sewa</div>
                                        <div class="text-slate-900 font-extrabold text-base">Rp {{ number_format($product->harga_sewa, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">/ hari</span></div>
                                    </div>
                                    <button onclick="openAuthModal()" class="bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold text-xs px-3.5 py-2.5 rounded-xl transition flex items-center gap-1.5 shadow-xs">
                                        <span>Sewa Alat</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white rounded-2xl p-12 text-center text-slate-500 border border-slate-200/80">
                            <p class="font-bold text-slate-700 text-base">Belum ada peralatan yang tersedia</p>
                            <p class="text-xs text-slate-400 mt-1">Client ini belum memiliki produk aktif dalam katalog.</p>
                        </div>
                    @endforelse

                </div>

                <!-- Pagination Footer -->
                <div class="pt-6 pb-2 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-medium text-slate-500 border-t border-slate-200/80 mt-6">
                    <span>Menampilkan <strong class="text-slate-800">1–{{ count($products) }}</strong> dari <strong class="text-slate-800">{{ count($products) }}</strong> peralatan</span>
                    <div class="flex items-center gap-1.5">
                        <button class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-white hover:text-slate-700 disabled:opacity-50 transition cursor-not-allowed" disabled>‹</button>
                        <button class="w-8 h-8 rounded-lg bg-emerald-700 text-white font-bold flex items-center justify-center shadow-xs">1</button>
                        <button class="w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 font-medium flex items-center justify-center transition">›</button>
                    </div>
                </div>

            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Left Info Column -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-slate-900 flex items-center justify-center text-emerald-400 font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="font-extrabold text-slate-900 text-lg tracking-tight">{{ $client->nama_usaha }}</span>
                    </div>
                    <p class="text-xs text-slate-500 max-w-md leading-relaxed">
                        {{ $client->deskripsi }}
                    </p>
                    <div class="inline-flex items-center gap-1.5 text-xs text-slate-600 pt-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Dedicated Client Instance: <strong class="text-slate-800">{{ $client->subdomain }}.rentalbase.com</strong></span>
                    </div>
                </div>

                <!-- Middle Info Column -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">OPERASIONAL & KONTAK</h4>
                    <ul class="space-y-2.5 text-xs text-slate-600 font-medium">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Senin – Sabtu: 08:00 – 17:00 WIB</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Hub Logistik Utama, Kawasan Industri Blok C-4</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h32a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg>
                            <span>+62 (21) 555-0192</span>
                        </li>
                    </ul>
                </div>

                <!-- Right Links Column -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">PUSAT LAYANAN</h4>
                    <ul class="space-y-2 text-xs text-slate-600 font-medium">
                        <li><a href="#" class="hover:text-slate-900 transition">Syarat & Ketentuan Sewa</a></li>
                        <li><a href="#" class="hover:text-slate-900 transition">Prosedur Pengembalian Unit</a></li>
                        <li><a href="#" class="hover:text-slate-900 transition">Hubungi Tim Support</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                <span>© 2024 {{ $client->nama_usaha }}. Didukung oleh infrastruktur RentalBase.</span>
                <span class="font-semibold text-slate-600">Status: Sistem Siap / Reservasi Aktif</span>
            </div>
        </div>
    </footer>

    <!-- Auth Required Modal Popup -->
    <div id="auth-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl transform scale-95 transition-all duration-300 border border-slate-100 relative">
            <button onclick="closeAuthModal()" class="absolute top-3.5 right-3.5 text-slate-400 hover:text-slate-700 p-1 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>

            <h3 class="text-lg font-extrabold text-slate-900 mb-2">login/register dlu bos</h3>
            <p class="text-xs text-slate-500 mb-6 leading-relaxed">Silakan masuk atau buat akun baru terlebih dahulu untuk melanjutkan proses penyewaan peralatan ini.</p>

            <div class="flex items-center gap-2.5">
                <button onclick="closeAuthModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-4 rounded-xl transition">
                    Tutup
                </button>
                <button onclick="closeAuthModal()" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition shadow-xs">
                    Masuk / Daftar
                </button>
            </div>
        </div>
    </div>

    <!-- Interactive JavaScript -->
    <script>
        function openAuthModal() {
            const modal = document.getElementById('auth-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.firstElementChild.classList.remove('scale-95');
            }, 10);
        }

        function closeAuthModal() {
            const modal = document.getElementById('auth-modal');
            if (!modal) return;
            modal.classList.add('opacity-0');
            modal.firstElementChild.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('auth-modal');
            if (modal && e.target === modal) {
                closeAuthModal();
            }
        });

        const priceRange = document.getElementById('priceRange');
        const maxPriceText = document.getElementById('maxPriceText');
        if (priceRange && maxPriceText) {
            priceRange.addEventListener('input', function() {
                const formatted = new Intl.NumberFormat('id-ID').format(this.value);
                maxPriceText.innerText = 'Rp ' + formatted;
                filterProducts();
            });
        }

        function filterProducts() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const maxPrice = priceRange ? parseInt(priceRange.value) : 300000;
            const selectedCategory = document.querySelector('input[name="category"]:checked')?.value || 'all';
            const items = document.querySelectorAll('.product-item');
            let count = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const category = item.getAttribute('data-category').toLowerCase();
                const price = parseInt(item.getAttribute('data-price'));

                const matchesQuery = name.includes(query);
                const matchesPrice = price <= maxPrice;
                const matchesCategory = (selectedCategory === 'all' || category.includes(selectedCategory));

                if (matchesQuery && matchesPrice && matchesCategory) {
                    item.style.display = 'flex';
                    count++;
                } else {
                    item.style.display = 'none';
                }
            });

            document.getElementById('visible-count').innerText = count;
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            if (priceRange) priceRange.value = 300000;
            if (maxPriceText) maxPriceText.innerText = 'Rp 300.000';
            document.querySelectorAll('input[name="category"]').forEach(r => r.checked = (r.value === 'all'));
            filterProducts();
        }
    </script>
</body>
</html>