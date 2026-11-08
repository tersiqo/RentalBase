<div x-data="{ showFilters: false }">
    <!-- Hero Section -->
    <section class="relative overflow-hidden border-b border-gray-100 tenant-hero-bg">
        <div class="max-w-[1260px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-12 items-center pt-14 pb-16">
            <div>
                <div class="animate-fade-in-up" style="animation-delay: 60ms;">
                    <div class="flex flex-wrap items-center gap-2 mb-5">
                        <span class="inline-flex items-center gap-2 bg-white border border-gray-100 rounded-full px-3.5 py-1.5 text-[13px] font-semibold text-gray-800 shadow-[0_6px_16px_-8px_rgba(0,0,0,0.15)] hover:border-primary-200 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-primary-500" style="box-shadow: 0 0 0 4px color-mix(in srgb, var(--tenant-theme) 22%, white);"></span>
                            {{ $client->subdomain }}.rentalbase.id
                        </span>
                        @guest
                            <span class="inline-flex items-center gap-1.5 tenant-badge rounded-full px-3 py-1 text-[12px] font-bold shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full tenant-badge-dot animate-pulse"></span>
                                Pratinjau Koleksi
                            </span>
                        @endguest
                    </div>
                </div>
                
                <h1 class="text-[clamp(32px,4vw,46px)] leading-[1.18] font-extrabold tracking-tight text-gray-900 mb-4 max-w-[20ch] animate-fade-in-up" style="animation-delay: 140ms;">
                    Sewa Perlengkapan di {{ $client->business_name }}
                </h1>
                
                <p class="text-[17px] leading-relaxed max-w-[52ch] mb-7 text-gray-600 animate-fade-in-up" style="animation-delay: 220ms;">
                    {{ $client->description ?: ('Eksplorasi koleksi peralatan ' . $client->business_name . ' dan cek ketersediaannya secara real-time. Proses sewa mudah dan transparan.') }}
                </p>
                
                <!-- Search Component -->
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm focus-within:border-primary-500 focus-within:shadow-md p-2 flex items-center max-w-[680px] transition-all duration-300 animate-fade-in-up" style="animation-delay: 300ms;">
                    <div class="flex items-center px-3.5 py-1.5 flex-1 min-w-0">
                        <input id="q" wire:model.live.debounce.300ms="search" placeholder="Cari perlengkapan..." class="border-0 bg-transparent outline-none w-full font-medium text-[15px] p-0 focus:ring-0 placeholder-gray-400">
                    </div>
                    <button class="inline-flex items-center justify-center gap-2 rounded-xl font-bold px-6 py-2.5 bg-primary-500 text-white tenant-shadow-sm hover:bg-primary-600 active:scale-95 transition flex-none ml-1 cursor-pointer" onclick="document.getElementById('katalog').scrollIntoView()">Cari</button>
                </div>

                <div class="flex flex-wrap gap-2 mt-4 items-center text-[13px] animate-fade-in-up" style="animation-delay: 380ms;">
                    <span class="text-gray-600 mr-1 font-medium">Sering dicari:</span>
                    @foreach($categories->take(4) as $cat)
                        <button type="button" wire:click="$set('search', '{{ addslashes($cat->name) }}')" class="border border-gray-200 bg-white rounded-full px-3.5 py-1.5 font-semibold hover:border-primary-500 hover:text-primary-500 hover:scale-105 active:scale-95 transition cursor-pointer">{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>
            
            @if($this->heroShowcaseMode === 'featured' && $this->featuredProduct)
                @php
                    $feat = $this->featuredProduct;
                    $featAvailable = (int) ($feat->available_units_count ?? 0);
                    $featImageUrl = $feat->main_image ? storage_url($feat->main_image) : null;
                @endphp
                <div class="relative h-[400px] hidden lg:block animate-fade-in-up" style="animation-delay: 180ms;">
                    <div class="animate-float h-full w-full relative">
                        <!-- Main Card -->
                        <div class="absolute inset-0 right-[20px] bottom-[30px] rounded-[28px] overflow-hidden tenant-hero-card group transition-all duration-500 hover:shadow-2xl flex flex-col justify-between p-6 border border-white/20">
                            <!-- Background Decorative Circles -->
                            <div class="absolute w-[320px] h-[320px] rounded-full bg-white/15 -top-[80px] -right-[80px] transition-transform duration-700 ease-out group-hover:scale-110 pointer-events-none"></div>
                            <div class="absolute w-[200px] h-[200px] rounded-full bg-white/10 -bottom-[60px] -left-[40px] transition-transform duration-700 ease-out group-hover:scale-110 pointer-events-none"></div>

                            <!-- Top Bar: Category & Status Badge -->
                            <div class="relative z-10 flex items-center justify-between gap-2">
                                <div class="inline-flex items-center gap-1.5 bg-black/25 backdrop-blur-md border border-white/20 text-white rounded-full px-3 py-1 text-[12px] font-bold shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-amber-300 fill-amber-300" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span>Produk Unggulan</span>
                                    @if($feat->category)
                                        <span class="text-white/60">·</span>
                                        <span class="text-white/90 truncate max-w-[120px]">{{ $feat->category->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Center: Image or Visual Fallback -->
                            <div class="relative z-10 flex-1 flex items-center justify-center my-3 overflow-hidden">
                                <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $feat->id]) }}"
                                   wire:navigate
                                   class="relative w-full h-[175px] rounded-2xl overflow-hidden flex items-center justify-center group/img">
                                    @if($featImageUrl)
                                        <img src="{{ $featImageUrl }}"
                                             alt="{{ $feat->name }}"
                                             class="w-full h-full object-contain filter drop-shadow-[0_12px_16px_rgba(0,0,0,0.2)] transition-transform duration-500 ease-out group-hover/img:scale-108"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                        <div class="hidden text-white/90 text-center p-4">
                                            <svg class="w-16 h-16 mx-auto mb-2 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-xs font-bold">{{ $feat->name }}</span>
                                        </div>
                                    @else
                                        <div class="text-white text-center p-4 flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center mb-2 shadow-inner">
                                                <svg class="w-8 h-8 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <span class="text-xs font-bold text-white/90 line-clamp-1">{{ $feat->name }}</span>
                                        </div>
                                    @endif
                                </a>
                            </div>

                            <!-- Bottom Glass Card: Name, Price & CTA Buttons -->
                            <div class="relative z-10 bg-white/95 backdrop-blur-md rounded-2xl p-3.5 border border-white/60 shadow-[0_12px_24px_-6px_rgba(0,0,0,0.18)]">
                                <div class="flex items-start justify-between gap-3 mb-2.5">
                                    <div class="min-w-0">
                                        <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $feat->id]) }}"
                                           wire:navigate
                                           class="text-[14px] font-extrabold text-gray-900 truncate block hover:text-primary-600 transition-colors"
                                           title="{{ $feat->name }}">
                                            {{ $feat->name }}
                                        </a>
                                        <div class="text-[13px] font-bold text-primary-600 mt-0.5">
                                            Rp {{ number_format($feat->rental_price_per_day, 0, ',', '.') }}
                                            <span class="text-[11px] font-medium text-gray-500">/ hari</span>
                                        </div>
                                    </div>
                                    @if($featAvailable > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            {{ $featAvailable }} Tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                                            Booking Penuh
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 pt-1 border-t border-gray-100">
                                    <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $feat->id]) }}"
                                       wire:navigate
                                       class="flex-1 inline-flex justify-center items-center py-2 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-[12px] font-bold transition-colors">
                                        Lihat Detail
                                    </a>
                                    @if($featAvailable > 0)
                                        @auth
                                            <button type="button"
                                                    wire:click="openAddToCartModal({{ $feat->id }})"
                                                    class="inline-flex items-center justify-center gap-1.5 py-2 px-3.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-[12px] font-extrabold shadow-sm active:scale-95 transition-all cursor-pointer">
                                                <span>+ Sewa</span>
                                            </button>
                                        @else
                                            <a href="{{ route('login') }}"
                                               class="inline-flex items-center justify-center gap-1.5 py-2 px-3.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-[12px] font-extrabold shadow-sm active:scale-95 transition-all">
                                                <span>+ Sewa</span>
                                            </a>
                                        @endauth
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Top-Right Floating Pill -->
                        <div class="absolute -right-1 top-4 animate-float-slow z-20">
                            <div class="bg-white/95 backdrop-blur-md border border-gray-100 rounded-full px-3.5 py-1.5 text-[12px] font-bold text-gray-800 shadow-[0_14px_28px_-10px_rgba(0,0,0,0.25)] flex items-center gap-2 hover:scale-105 transition-transform">
                                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse" style="box-shadow: 0 0 0 4px color-mix(in srgb, var(--tenant-theme) 22%, white);"></span>
                                {{ $featAvailable > 0 ? 'Unit siap antar' : 'Koleksi Unggulan' }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="relative h-[400px] hidden lg:block animate-fade-in-up" style="animation-delay: 180ms;" aria-hidden="true">
                    <div class="animate-float h-full w-full relative">
                        <div class="absolute inset-0 right-[30px] bottom-[40px] rounded-[28px] overflow-hidden flex items-center justify-center tenant-hero-card group cursor-pointer transition-all duration-500 hover:scale-[1.01]">
                            <div class="absolute w-[340px] h-[340px] rounded-full bg-white/25 -top-[90px] -right-[90px] transition-transform duration-700 ease-out group-hover:scale-110"></div>
                            <div class="absolute w-[220px] h-[220px] rounded-full bg-white/20 -bottom-[70px] -left-[50px] transition-transform duration-700 ease-out group-hover:scale-110"></div>
                            <svg width="230" height="230" viewBox="0 0 120 120" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" class="relative z-10 filter drop-shadow-[0_18px_20px_rgba(0,0,0,0.18)] transition-transform duration-500 ease-out group-hover:scale-105 group-hover:-rotate-2">
                                <!-- Store Roof / Awning -->
                                <path d="M22 46L31 24h58l9 22H22z" fill="#fff" fill-opacity="0.22" />
                                <path d="M22 46c0 4.5 4.5 7 9 7s9-2.5 9-7 4.5-7 9-7 9 2.5 9 7 4.5 7 9 7 9-2.5 9-7 4.5-7 9-7 9 2.5 9 7" />
                                <!-- Store Base -->
                                <rect x="26" y="53" width="68" height="45" rx="6" />
                                <!-- Door -->
                                <rect x="35" y="65" width="22" height="33" rx="3" stroke-width="2.6" />
                                <circle cx="51" cy="81" r="2" fill="#fff" />
                                <!-- Display Window with Rental Items -->
                                <rect x="63" y="65" width="23" height="22" rx="3" stroke-width="2.6" fill="#fff" fill-opacity="0.18" />
                                <line x1="63" y1="76" x2="86" y2="76" stroke-width="2" stroke-dasharray="2 3" />
                                <!-- Shop Sign / Star -->
                                <circle cx="60" cy="35" r="4" fill="#fff" />
                            </svg>
                        </div>
                        <div class="absolute -right-1.5 top-5 animate-float-slow">
                            <div class="bg-white/95 backdrop-blur-md border border-gray-100 rounded-full px-4 py-2 text-[13px] font-bold text-gray-800 shadow-[0_14px_28px_-10px_rgba(0,0,0,0.25)] flex items-center gap-2 hover:scale-105 transition-transform">
                                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse" style="box-shadow: 0 0 0 4px color-mix(in srgb, var(--tenant-theme) 22%, white);"></span>Unit siap antar
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Main Catalog Section -->
    <main class="max-w-[1260px] mx-auto px-6 py-10 grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-9" id="katalog">
        
        <!-- Mobile Filter Toggle -->
        <div class="lg:hidden mb-4">
            <button @click="showFilters = !showFilters" class="w-full bg-white border border-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-sm transition-colors hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                <span x-text="showFilters ? 'Tutup Filter' : 'Tampilkan Filter'"></span>
            </button>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6 lg:sticky lg:top-[92px] lg:self-start animate-fade-in-up" :class="showFilters ? 'block' : 'hidden lg:block'">
            
            <!-- Category -->
            <div>
                <h3 class="text-[15px] font-bold text-gray-900 mb-3 flex justify-between items-center">
                    Kategori
                    @if($search || $selectedCategory || $availableOnly || $minPrice > 0 || $maxPrice < $absoluteMaxPrice || $startDate || $endDate)
                        <button type="button" wire:click="resetFilters" class="text-primary-500 text-[12px] font-bold hover:underline">Reset</button>
                    @endif
                </h3>
                <div class="flex flex-col gap-1">
                    <button type="button" wire:click="$set('selectedCategory', null)" class="w-full flex justify-between items-center p-[10px_14px] rounded-xl font-semibold text-left transition-colors {{ $selectedCategory === null ? 'bg-primary-50 text-primary-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        Semua peralatan
                        <small class="text-[12px] font-semibold {{ $selectedCategory === null ? 'bg-white text-primary-500 px-2 py-[1px] rounded-full shadow-2xs' : 'text-gray-400' }}">{{ $allProductsCount }}</small>
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" wire:click="$set('selectedCategory', {{ $cat->id }})" class="w-full flex justify-between items-center p-[10px_14px] rounded-xl font-semibold text-left transition-colors {{ $selectedCategory === $cat->id ? 'bg-primary-50 text-primary-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $cat->name }}
                            <small class="text-[12px] font-semibold {{ $selectedCategory === $cat->id ? 'bg-white text-primary-500 px-2 py-[1px] rounded-full shadow-2xs' : 'text-gray-400' }}">{{ $categoryCounts[$cat->id] ?? 0 }}</small>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Periode Sewa (Cek Stok Tanggal) -->
            <div class="pb-2">
                <h3 class="text-[15px] font-bold text-gray-900 mb-2.5 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Periode Sewa
                    </span>
                    @if($startDate || $endDate)
                        <button type="button" wire:click="$set('startDate', ''); $set('endDate', '');" class="text-primary-500 text-[11px] font-bold hover:underline">Hapus</button>
                    @endif
                </h3>
                <div class="space-y-2">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Mulai</label>
                        <input type="date" 
                               wire:model.live="startDate" 
                               min="{{ date('Y-m-d') }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-[13px] font-semibold text-gray-800 focus:border-primary-500 focus:ring-0 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Selesai</label>
                        <input type="date" 
                               wire:model.live="endDate" 
                               min="{{ $startDate ?: date('Y-m-d') }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-[13px] font-semibold text-gray-800 focus:border-primary-500 focus:ring-0 transition-colors">
                    </div>
                    @if($startDate && $endDate)
                        <div class="p-2 tenant-badge rounded-lg text-[11.5px] font-medium flex items-center justify-between">
                            <span>Filter stok aktif per tanggal</span>
                            <span class="w-2 h-2 rounded-full tenant-badge-dot animate-pulse"></span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Availability -->
            <div>
                <h3 class="text-[15px] font-bold text-gray-900 mb-3">Ketersediaan</h3>
                <div class="flex justify-between items-center gap-3 text-[14px] font-medium text-gray-700">
                    <span>Hanya tampilkan unit tersedia</span>
                    <button type="button" 
                            wire:click="$toggle('availableOnly')"
                            class="relative w-[46px] h-[26px] rounded-full border-0 p-[3px] transition-colors cursor-pointer flex-none {{ $availableOnly ? 'bg-primary-500' : 'bg-gray-200' }}"
                            aria-pressed="{{ $availableOnly ? 'true' : 'false' }}">
                        <span class="block w-5 h-5 rounded-full bg-white shadow-[0_2px_4px_rgba(0,0,0,0.2)] transition-transform {{ $availableOnly ? 'translate-x-[20px]' : '' }}"></span>
                    </button>
                </div>
            </div>

            <!-- Price -->
            <div class="pb-2">
                <h3 class="text-[15px] font-bold text-gray-900 mb-3">Harga sewa per hari</h3>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex-1 flex items-center gap-1.5 border border-gray-200 rounded-xl p-[8px_10px] text-[12px] font-semibold text-gray-400 focus-within:border-primary-500 transition-colors">
                        Rp <input type="number" wire:model.live.debounce.500ms="minPrice" min="0" step="5000" class="w-full border-0 outline-none font-semibold text-[13px] text-gray-900 min-w-0 p-0 focus:ring-0">
                    </div>
                    <span class="text-gray-400 font-bold">-</span>
                    <div class="flex-1 flex items-center gap-1.5 border border-gray-200 rounded-xl p-[8px_10px] text-[12px] font-semibold text-gray-400 focus-within:border-primary-500 transition-colors">
                        Rp <input type="number" wire:model.live.debounce.500ms="maxPrice" min="0" step="5000" class="w-full border-0 outline-none font-semibold text-[13px] text-gray-900 min-w-0 p-0 focus:ring-0">
                    </div>
                </div>
                <input type="range" wire:model.live.debounce.300ms="maxPrice" min="0" max="{{ $absoluteMaxPrice > 0 ? $absoluteMaxPrice : 150000 }}" step="5000" class="w-full accent-primary-500">
            </div>

            <!-- Note Box -->
            <div class="bg-primary-50 border border-primary-100 rounded-2xl p-[18px] flex gap-3">
                <span class="w-[38px] h-[38px] flex-none rounded-xl bg-white text-primary-500 flex items-center justify-center shadow-sm">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg>
                </span>
                <div>
                    <b class="text-primary-900 block text-[14px] mb-0.5 leading-tight font-bold">Verifikasi Identitas Digital (Bebas Deposit)</b>
                    <p class="text-[12.5px] text-primary-700 leading-[1.5]">Verifikasi foto KTP &amp; selfie online tanpa uang jaminan dan tanpa perlu titip fisik KTP.</p>
                </div>
            </div>
        </aside>

        <!-- Product Grid Section -->
        <section class="relative">
            
            <div class="flex justify-between items-center mb-6 gap-3 flex-wrap">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-gray-900 text-[24px] font-extrabold tracking-tight">Katalog peralatan</h2>
                        @guest
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold tenant-badge">
                                Koleksi Pilihan
                            </span>
                        @endguest
                    </div>
                    @auth
                        <p class="text-[13px] text-gray-500">{{ $this->products->total() ?? $this->products->count() }} peralatan ditemukan</p>
                    @else
                        <p class="text-[13px] text-gray-500">
                            Menampilkan koleksi pilihan • 
                            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="text-primary-600 font-bold hover:underline">Masuk</a> untuk melihat seluruh {{ $this->allProductsCount }} alat siap sewa
                        </p>
                    @endauth
                </div>
                <div class="flex items-center gap-2">
                    <label for="so" class="text-[13px] text-gray-600 font-semibold">Urutkan</label>
                    <select id="so" wire:model.live="sort" class="border border-gray-200 rounded-xl p-[9px_34px_9px_14px] bg-white font-semibold text-[13px] cursor-pointer focus:ring-0 focus:border-primary-500 transition-colors">
                        <option value="terbaru">Terbaru</option>
                        <option value="termurah">Harga terendah</option>
                        <option value="termahal">Harga tertinggi</option>
                        <option value="abjad">Nama A-Z</option>
                    </select>
                </div>
            </div>

            <!-- Flash notification if item added to cart -->
            @if(session()->has('cart_success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in-up">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-sm font-semibold">{{ session('cart_success') }}</span>
                    </div>
                    <a href="{{ route('customer.cart', ['subdomain' => $client->subdomain]) }}" wire:navigate class="px-3 py-1 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition shrink-0 ml-2">
                        Buka Keranjang &rarr;
                    </a>
                </div>
            @endif

            <!-- Loading State overlay -->
            <div wire:loading.delay.shortest wire:target="search,selectedCategory,availableOnly,minPrice,maxPrice,sort,startDate,endDate,resetFilters" class="absolute inset-0 z-30 bg-white/70 backdrop-blur-sm rounded-2xl flex items-start justify-center pt-24 transition-all">
                <div class="bg-white px-6 py-4 rounded-full shadow-xl border border-gray-200 flex items-center gap-3.5">
                    <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-800 font-bold text-base">Memperbarui...</span>
                </div>
            </div>

            <div wire:loading.class="opacity-30 pointer-events-none" wire:target="search,selectedCategory,availableOnly,minPrice,maxPrice,sort,startDate,endDate,resetFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-[22px] min-h-[420px]">
                
                @if($this->products->isEmpty())
                    <div class="col-span-full text-center p-[70px_20px] border-2 border-dashed border-gray-200 rounded-[28px] self-start mt-8">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-primary-500"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        <h3 class="text-gray-900 text-[20px] font-bold mb-[6px]">Belum ada alat yang cocok</h3>
                        <p class="max-w-[36ch] mx-auto mb-5 text-gray-500">Coba kata kunci lain, perluas rentang harga, atau matikan filter unit tersedia.</p>
                        @if($search || $selectedCategory || $availableOnly || $minPrice > 0 || $maxPrice < $absoluteMaxPrice || $startDate || $endDate)
                            <button type="button" wire:click="resetFilters" class="inline-flex items-center justify-center gap-2 rounded-xl font-bold px-[22px] py-3 bg-primary-500 text-white tenant-shadow-sm hover:bg-primary-600 transition">Reset filter</button>
                        @endif
                    </div>
                @else
                    @php
                        $isGuest = !auth()->check();
                        $displayedProducts = $isGuest ? $this->products->take(3) : $this->products;
                        $hiddenCount = max(0, $this->allProductsCount - $displayedProducts->count());
                    @endphp

                    @foreach($displayedProducts as $index => $item)
                        <!-- Card -->
                        <article wire:key="product-{{ $item->id }}" class="bg-white border border-gray-100 rounded-[24px] overflow-hidden flex flex-col shadow-[0_10px_30px_-14px_rgba(0,0,0,0.1)] transition-all duration-300 hover:-translate-y-1 hover:border-primary-200 hover:shadow-xl {{ ($item->total_units_count ?? 0) > 0 ? '' : 'opacity-60 grayscale-[0.2]' }} group animate-fade-in-up" style="animation-delay: {{ $index * 130 }}ms">
                            
                            <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $item->id]) }}" wire:navigate class="relative h-[190px] flex items-center justify-center overflow-hidden bg-gray-100">
                                @if($item->main_image)
                                    <img src="{{ storage_url($item->main_image) }}" 
                                         alt="{{ $item->name }}" 
                                         loading="lazy"
                                         class="object-cover w-full h-full transition-transform duration-500 ease-out group-hover:scale-110"
                                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                     <div class="hidden absolute inset-0" style="background: linear-gradient(135deg, color-mix(in srgb, var(--tenant-theme) 25%, white), color-mix(in srgb, var(--tenant-theme) 60%, white));">
                                         <div class="absolute w-[180px] h-[180px] rounded-full bg-white/35 -top-[60px] -right-[40px]"></div>
                                         <div class="h-full w-full flex items-center justify-center">
                                             <svg width="90" height="90" viewBox="0 0 120 120" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="relative filter drop-shadow-[0_12px_12px_rgba(0,0,0,0.18)] transition-transform duration-500 group-hover:scale-105 group-hover:-rotate-2">
                                                 <path d="M22 46L31 24h58l9 22H22z" fill="#fff" fill-opacity="0.22" />
                                                 <path d="M22 46c0 4.5 4.5 7 9 7s9-2.5 9-7 4.5-7 9-7 9 2.5 9 7 4.5 7 9 7 9-2.5 9-7 4.5-7 9-7 9 2.5 9 7" />
                                                 <rect x="26" y="53" width="68" height="45" rx="6" />
                                                 <rect x="35" y="65" width="22" height="33" rx="3" stroke-width="2.6" />
                                                 <circle cx="51" cy="81" r="2" fill="#fff" />
                                                 <rect x="63" y="65" width="23" height="22" rx="3" stroke-width="2.6" fill="#fff" fill-opacity="0.18" />
                                                 <circle cx="60" cy="35" r="4" fill="#fff" />
                                             </svg>
                                         </div>
                                     </div>
                                @else
                                    <div class="absolute inset-0" style="background: linear-gradient(135deg, color-mix(in srgb, var(--tenant-theme) 25%, white), color-mix(in srgb, var(--tenant-theme) 60%, white));">
                                        <div class="absolute w-[180px] h-[180px] rounded-full bg-white/35 -top-[60px] -right-[40px]"></div>
                                        <div class="h-full w-full flex items-center justify-center">
                                             <svg width="90" height="90" viewBox="0 0 120 120" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="relative filter drop-shadow-[0_12px_12px_rgba(0,0,0,0.18)] transition-transform duration-500 group-hover:scale-105 group-hover:-rotate-2">
                                                 <path d="M22 46L31 24h58l9 22H22z" fill="#fff" fill-opacity="0.22" />
                                                 <path d="M22 46c0 4.5 4.5 7 9 7s9-2.5 9-7 4.5-7 9-7 9 2.5 9 7 4.5 7 9 7 9-2.5 9-7 4.5-7 9-7 9 2.5 9 7" />
                                                 <rect x="26" y="53" width="68" height="45" rx="6" />
                                                 <rect x="35" y="65" width="22" height="33" rx="3" stroke-width="2.6" />
                                                 <circle cx="51" cy="81" r="2" fill="#fff" />
                                                 <rect x="63" y="65" width="23" height="22" rx="3" stroke-width="2.6" fill="#fff" fill-opacity="0.18" />
                                                 <circle cx="60" cy="35" r="4" fill="#fff" />
                                             </svg>
                                         </div>
                                    </div>
                                @endif
                                
                                @if($item->available_units_count > 0)
                                    <span class="absolute right-3.5 top-3.5 bg-white/95 backdrop-blur-sm rounded-full px-3 py-1 text-[12px] font-bold flex items-center gap-1.5 text-green-700 shadow-sm border border-green-200">
                                        <i class="w-[7px] h-[7px] rounded-full bg-green-500 block"></i> Tersedia {{ $item->available_units_count }}
                                    </span>
                                @elseif(($item->total_units_count ?? 0) > 0)
                                    <span class="absolute right-3.5 top-3.5 bg-white/95 backdrop-blur-sm rounded-full px-3 py-1 text-[12px] font-bold flex items-center gap-1.5 text-amber-700 shadow-sm border border-amber-200">
                                        <i class="w-[7px] h-[7px] rounded-full bg-amber-500 block"></i> Sedang Disewa
                                    </span>
                                @else
                                    <span class="absolute right-3.5 top-3.5 bg-white/95 backdrop-blur-sm rounded-full px-3 py-1 text-[12px] font-bold flex items-center gap-1.5 text-gray-500 shadow-sm border border-gray-200">
                                        <i class="w-[7px] h-[7px] rounded-full bg-gray-400 block"></i> Stok Habis
                                    </span>
                                @endif
                            </a>
                            
                            <div class="p-[20px_22px_22px] flex flex-col flex-1">
                                <!-- Category Badge -->
                                <div class="mb-1.5">
                                    <span class="inline-block tenant-badge text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                        {{ $item->category->name ?? 'Peralatan' }}
                                    </span>
                                </div>

                                <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $item->id]) }}" wire:navigate>
                                    <h3 class="text-gray-900 text-[17px] font-bold leading-[1.3] line-clamp-2 hover:text-primary-600 transition-colors">{{ $item->name }}</h3>
                                </a>
                                <p class="text-[13px] mt-1 mb-4 text-gray-500 line-clamp-2">{{ strip_tags($item->description) }}</p>
                                
                                <div class="mt-auto flex items-baseline gap-1.5 text-gray-900 mb-4">
                                    <b class="text-[22px] font-extrabold tracking-tight">Rp{{ number_format($item->rental_price_per_day, 0, ',', '.') }}</b>
                                    <span class="text-[13px] text-gray-400 font-semibold">per hari</span>
                                </div>
                                
                                <!-- Card Action Buttons -->
                                <div class="flex items-center gap-2 mt-2">
                                    <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $item->id]) }}" wire:navigate class="flex-1 inline-flex justify-center items-center rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 transition-all text-[13px]">
                                        Lihat detail
                                    </a>
                                    @if(($item->total_units_count ?? 0) > 0)
                                        @auth
                                            <button type="button" 
                                                    wire:click="openAddToCartModal({{ $item->id }})" 
                                                    title="{{ $item->available_units_count > 0 ? 'Tambah ke Keranjang' : 'Pilih Jadwal Booking' }}"
                                                    aria-label="{{ $item->available_units_count > 0 ? 'Tambah ke Keranjang' : 'Pilih Jadwal Booking' }}"
                                                    class="flex-none inline-flex justify-center items-center gap-1 rounded-xl bg-primary-500 text-white font-bold px-3 py-2.5 tenant-shadow-sm hover:bg-primary-600 active:scale-95 transition-all cursor-pointer">
                                                <span class="text-[14px] font-black leading-none">+</span>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.5 12h11L21 7H6"/></svg>
                                            </button>
                                        @else
                                            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
                                               title="Masuk untuk menyewa alat ini"
                                               class="flex-none inline-flex justify-center items-center gap-1 rounded-xl bg-primary-500 text-white font-bold px-3 py-2.5 tenant-shadow-sm hover:bg-primary-600 active:scale-95 transition-all cursor-pointer">
                                                <span class="text-[14px] font-black leading-none">+</span>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.5 12h11L21 7H6"/></svg>
                                            </a>
                                        @endauth
                                    @else
                                        <button disabled 
                                                title="Stok Habis"
                                                class="flex-none inline-flex justify-center items-center gap-1 rounded-xl bg-gray-100 text-gray-400 font-bold px-3 py-2.5 cursor-not-allowed">
                                            <span class="text-[14px] font-black leading-none">+</span>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.5 12h11L21 7H6"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach

                    @if($isGuest && $this->allProductsCount > 3)
                        <!-- Locked Catalog Teaser Section for Guests -->
                        <div class="col-span-full relative mt-4">
                            <!-- Blurred Skeleton Cards in background -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-[22px] filter blur-[6px] opacity-30 select-none pointer-events-none" aria-hidden="true">
                                @for($k = 1; $k <= 3; $k++)
                                    <div class="bg-white border border-gray-200 rounded-[24px] overflow-hidden flex flex-col p-5 shadow-sm">
                                        <div class="h-[180px] bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl mb-4 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </div>
                                        <div class="h-4 bg-gray-200 rounded-full w-3/4 mb-2.5"></div>
                                        <div class="h-3 bg-gray-100 rounded-full w-1/2 mb-4"></div>
                                        <div class="h-7 bg-gray-200 rounded-xl w-1/3 mt-auto"></div>
                                    </div>
                                @endfor
                            </div>

                            <!-- Overlaid Login Required Frosted Card -->
                            <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-6">
                                <div class="w-full max-w-xl bg-white/95 backdrop-blur-xl border border-primary-200 rounded-3xl p-6 sm:p-8 tenant-shadow-lg text-center animate-fade-in-up">
                                    <div class="w-14 h-14 mx-auto rounded-2xl bg-primary-500 text-white flex items-center justify-center tenant-shadow-sm mb-4">
                                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold tenant-badge mb-2.5">
                                        <span class="w-1.5 h-1.5 rounded-full tenant-badge-dot animate-pulse"></span>
                                        Buka Akses Penuh
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 mb-2.5 tracking-tight">
                                        Mau Lihat Pilihan Alat Lainnya?
                                    </h3>
                                    <p class="text-[13.5px] text-gray-600 mb-6 max-w-md mx-auto leading-relaxed">
                                        Yuk masuk atau bikin akun dulu! Cuma butuh 1 menit buat lihat <strong>{{ $hiddenCount > 0 ? $hiddenCount : 'semua' }} koleksi lainnya</strong>, cek ketersediaan tanggal sewa, dan langsung pesan.
                                    </p>
                                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5">
                                        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-bold text-sm tenant-shadow-sm active:scale-95 transition-all flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                            Masuk ke Akun
                                        </a>
                                        <a href="{{ route('login', ['tab' => 'register', 'redirect' => request()->fullUrl()]) }}" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-800 font-bold text-sm border border-gray-200 active:scale-95 transition-all flex items-center justify-center gap-2">
                                            Daftar Akun Baru
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Pagination Section -->
            @auth
                @if($this->products->hasPages())
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        {{ $this->products->links() }}
                    </div>
                @endif
            @else
                @if($this->allProductsCount > 3)
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-400 font-medium">
                            Mau jelajahi halaman berikutnya?
                            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="text-primary-600 font-bold hover:underline ml-1">Masuk sekarang &rarr;</a>
                        </p>
                    </div>
                @endif
            @endauth
        </section>
    </main>

    <!-- Add To Cart Modal -->
    @if($showModal && $modalProduct)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="catalog-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/75 transition-opacity backdrop-blur-sm z-0" wire:click="closeAddToCartModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start mb-5">
                            <h3 class="text-xl leading-6 font-bold text-gray-900" id="catalog-modal-title">Pilih Jadwal Sewa</h3>
                            <button type="button" wire:click="closeAddToCartModal" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <span class="sr-only">Tutup</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Product Summary -->
                        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl mb-6">
                            <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center border border-gray-200 overflow-hidden shrink-0">
                                <img src="{{ $modalProduct->main_image ? storage_url($modalProduct->main_image) : 'https://placehold.co/100x100/e2e8f0/475569?text=IMG' }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 line-clamp-1">{{ $modalProduct->name }}</h4>
                                <p class="text-sm text-primary-600 font-medium">Rp {{ number_format($modalProduct->rental_price_per_day, 0, ',', '.') }} <span class="text-gray-500 text-xs">/ hari</span></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Date Pickers -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                                    <input type="date" wire:model.live="modalStartDate" wire:change="checkModalAvailability" min="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                                    <input type="date" wire:model.live="modalEndDate" wire:change="checkModalAvailability" min="{{ $modalStartDate ?: date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                </div>
                            </div>

                            <!-- Availability Banner -->
                            <div>
                                <div wire:loading wire:target="checkModalAvailability, modalStartDate, modalEndDate" class="flex items-center gap-2 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                    <svg class="animate-spin h-3.5 w-3.5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Memeriksa ketersediaan unit...</span>
                                </div>
                                <div wire:loading.remove wire:target="checkModalAvailability, modalStartDate, modalEndDate">
                                    @if($modalAvailableUnits > 0)
                                        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                            <span>Tersedia <strong>{{ $modalAvailableUnits }} unit</strong> pada periode tanggal ini.</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                            <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                            <span>Semua unit penuh dipesan pada tanggal ini. Silakan pilih tanggal lain.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($modalErrorMessage)
                                <div class="text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    {{ $modalErrorMessage }}
                                </div>
                            @endif

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Unit (Maks: {{ $modalAvailableUnits }})</label>
                                <input type="number" wire:model.live="modalQuantity" min="1" max="{{ max(1, $modalAvailableUnits) }}" class="w-full sm:w-1/3 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>

                            @php
                                $mStart = $modalStartDate ? \Carbon\Carbon::parse($modalStartDate) : null;
                                $mEnd = $modalEndDate ? \Carbon\Carbon::parse($modalEndDate) : null;
                                $mDur = ($mStart && $mEnd && $mEnd->gte($mStart)) ? $mStart->diffInDays($mEnd) + 1 : 0;
                                $mTotal = $mDur * $modalProduct->rental_price_per_day * max(1, (int)$modalQuantity);
                            @endphp

                            <!-- Calculation Auto-Update -->
                            <div class="bg-primary-50 border border-primary-100 p-4 rounded-xl mt-6">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-primary-800">Durasi Sewa:</span>
                                    <span class="font-bold text-primary-900">{{ $mDur }} Hari</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-primary-800">Kuantitas:</span>
                                    <span class="font-bold text-primary-900">{{ $modalQuantity }} Unit</span>
                                </div>
                                <hr class="border-primary-200 my-2">
                                <div class="flex justify-between items-end">
                                    <span class="text-sm font-bold text-primary-900">Estimasi Total Biaya:</span>
                                    <span class="text-2xl font-black text-primary-600">Rp {{ number_format($mTotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-4 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-gray-100">
                        <button type="button" wire:click="closeAddToCartModal" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="confirmModalAddToCart" 
                                @if($modalAvailableUnits < 1 || $mDur < 1) disabled @endif
                                class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:w-auto sm:text-sm transition-colors @if($modalAvailableUnits < 1 || $mDur < 1) opacity-50 cursor-not-allowed @endif">
                            <span wire:loading.remove wire:target="confirmModalAddToCart">Tambahkan ke Keranjang</span>
                            <span wire:loading wire:target="confirmModalAddToCart" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>