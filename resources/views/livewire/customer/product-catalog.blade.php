<div x-data="{ showFilters: false }">
    <!-- Hero Section -->
    <section class="relative overflow-hidden border-b border-gray-100 bg-white" style="background: radial-gradient(circle at 8% 0%, rgba(253,186,116,0.28), transparent 42%), radial-gradient(circle at 95% 10%, rgba(251,146,60,0.2), transparent 45%), #fff;">
        <div class="max-w-[1260px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-12 items-center pt-14 pb-16">
            <div>
                <span class="inline-flex items-center gap-2 bg-white border border-gray-100 rounded-full px-3.5 py-1.5 text-[13px] font-semibold text-gray-800 shadow-[0_6px_16px_-8px_rgba(0,0,0,0.15)] mb-5">
                    <span class="w-2 h-2 rounded-full bg-primary-500 shadow-[0_0_0_4px_#FFEDD5]"></span>
                    {{ $client->subdomain }}.rentalbase.id
                </span>
                <h1 class="text-[clamp(34px,4.6vw,52px)] leading-[1.1] font-extrabold tracking-tight text-gray-900 mb-4 max-w-[15ch]">
                    Perlengkapan bayi, sewa tanpa deposit tunai
                </h1>
                <p class="text-[18px] max-w-[46ch] mb-7 text-gray-600">
                    Eksplorasi koleksi peralatan kami dan cek ketersediaannya secara real-time. Bebas repot, bebas deposit tunai.
                </p>
                
                <!-- Search Component -->
                <div class="bg-white border border-gray-100 rounded-2xl shadow-[0_16px_32px_-14px_rgba(249,115,22,0.25)] p-2 flex items-center max-w-[680px]">
                    <div class="flex items-center px-3.5 py-1.5 flex-1 min-w-0">
                        <input id="q" wire:model.live.debounce.300ms="search" placeholder="Cari perlengkapan..." class="border-0 bg-transparent outline-none w-full font-medium text-[15px] p-0 focus:ring-0 placeholder-gray-400">
                    </div>
                    <button class="inline-flex items-center justify-center gap-2 rounded-xl font-bold px-6 py-2.5 bg-primary-500 text-white shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] hover:bg-primary-600 active:scale-95 transition flex-none ml-1 cursor-pointer" onclick="document.getElementById('katalog').scrollIntoView()">Cari</button>
                </div>

                <div class="flex flex-wrap gap-2 mt-4 items-center text-[13px]">
                    <span class="text-gray-600 mr-1 font-medium">Sering dicari:</span>
                    @foreach($categories->take(4) as $cat)
                        <button type="button" wire:click="$set('search', '{{ addslashes($cat->name) }}')" class="border border-gray-200 bg-white rounded-full px-3.5 py-1.5 font-semibold hover:border-primary-500 hover:text-primary-500 transition cursor-pointer">{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>
            
            <div class="relative h-[400px] hidden lg:block" aria-hidden="true">
                <div class="absolute inset-0 right-[30px] bottom-[40px] rounded-[28px] overflow-hidden flex items-center justify-center shadow-[0_30px_60px_-20px_rgba(234,88,12,0.45)]" style="background: linear-gradient(150deg, #FED7AA, #FDBA74 55%, #FB923C);">
                    <div class="absolute w-[340px] h-[340px] rounded-full bg-white/25 -top-[90px] -right-[90px]"></div>
                    <div class="absolute w-[220px] h-[220px] rounded-full bg-white/20 -bottom-[70px] -left-[50px]"></div>
                    <svg width="230" height="230" viewBox="0 0 120 120" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" class="relative z-10 filter drop-shadow-[0_18px_20px_rgba(124,45,18,0.3)]"><path d="M14 30h14l8 42h46" /><path d="M30 44h60c0 18-12 28-30 28H36"/><path d="M60 44V30c14 0 24 6 28 14"/><circle cx="42" cy="90" r="9" fill="#FFEDD5"/><circle cx="82" cy="90" r="9" fill="#FFEDD5"/></svg>
                </div>
                <div class="absolute -right-1.5 top-5 bg-white/90 backdrop-blur border border-gray-100 rounded-full px-4 py-2 text-[13px] font-bold text-gray-800 shadow-[0_14px_28px_-10px_rgba(0,0,0,0.25)] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary-500 shadow-[0_0_0_4px_#FFEDD5]"></span>Unit siap antar
                </div>
            </div>
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
        <aside class="space-y-6 lg:sticky lg:top-[92px] lg:self-start" :class="showFilters ? 'block' : 'hidden lg:block'">
            
            <!-- Category -->
            <div>
                <h3 class="text-[15px] font-bold text-gray-900 mb-3 flex justify-between items-center">
                    Kategori
                    @if($search || $selectedCategory || $availableOnly || $minPrice > 0 || $maxPrice < $absoluteMaxPrice)
                        <button type="button" wire:click="resetFilters" class="text-primary-500 text-[12px] font-bold hover:underline">Reset</button>
                    @endif
                </h3>
                <div class="flex flex-col gap-1">
                    <button type="button" wire:click="$set('selectedCategory', null)" class="w-full flex justify-between items-center p-[10px_14px] rounded-xl font-semibold text-left transition-colors {{ $selectedCategory === null ? 'bg-primary-50 text-[#C2410C]' : 'text-gray-600 hover:bg-gray-50' }}">
                        Semua peralatan
                        <small class="text-[12px] font-semibold {{ $selectedCategory === null ? 'bg-white text-primary-500 px-2 py-[1px] rounded-full' : 'text-gray-400' }}">{{ $allProductsCount }}</small>
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" wire:click="$set('selectedCategory', {{ $cat->id }})" class="w-full flex justify-between items-center p-[10px_14px] rounded-xl font-semibold text-left transition-colors {{ $selectedCategory === $cat->id ? 'bg-primary-50 text-[#C2410C]' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $cat->name }}
                            <small class="text-[12px] font-semibold {{ $selectedCategory === $cat->id ? 'bg-white text-primary-500 px-2 py-[1px] rounded-full' : 'text-gray-400' }}">{{ $categoryCounts[$cat->id] ?? 0 }}</small>
                        </button>
                    @endforeach
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
                    <b class="text-[#7C2D12] block text-[14px] mb-0.5 leading-tight">Tanpa menahan uang tunai</b>
                    <p class="text-[12.5px] text-[#9A3412] leading-[1.5]">Admin memeriksa KTP dan swafoto sebelum Anda membayar.</p>
                </div>
            </div>
        </aside>

        <!-- Product Grid Section -->
        <section class="relative">
            
            <div class="flex justify-between items-center mb-6 gap-3 flex-wrap">
                <div>
                    <h2 class="text-gray-900 text-[24px] font-extrabold tracking-tight mb-1">Katalog peralatan</h2>
                    <p class="text-[13px] text-gray-500">{{ $this->products->count() }} peralatan ditemukan</p>
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

            <!-- Loading State overlay -->
            <div wire:loading.delay.shortest wire:target="search,selectedCategory,availableOnly,minPrice,maxPrice,sort,resetFilters" class="absolute inset-0 z-30 bg-white/70 backdrop-blur-sm rounded-2xl flex items-start justify-center pt-24 transition-all">
                <div class="bg-white px-6 py-4 rounded-full shadow-xl border border-gray-200 flex items-center gap-3.5">
                    <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-800 font-bold text-base">Memperbarui...</span>
                </div>
            </div>

            <div wire:loading.class="opacity-30 pointer-events-none" wire:target="search,selectedCategory,availableOnly,minPrice,maxPrice,sort,resetFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-[22px] min-h-[420px]">
                
                @if($this->products->isEmpty())
                    <div class="col-span-full text-center p-[70px_20px] border-2 border-dashed border-gray-200 rounded-[28px] self-start mt-8">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#FB923C" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        <h3 class="text-gray-900 text-[20px] font-bold mb-[6px]">Belum ada alat yang cocok</h3>
                        <p class="max-w-[36ch] mx-auto mb-5 text-gray-500">Coba kata kunci lain, perluas rentang harga, atau matikan filter unit tersedia.</p>
                        @if($search || $selectedCategory || $availableOnly || $minPrice > 0 || $maxPrice < $absoluteMaxPrice)
                            <button type="button" wire:click="resetFilters" class="inline-flex items-center justify-center gap-2 rounded-xl font-bold px-[22px] py-3 bg-primary-500 text-white shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] hover:bg-primary-600 transition">Reset filter</button>
                        @endif
                    </div>
                @else
                    @foreach($this->products as $index => $item)
                        <!-- Card -->
                        <article wire:key="product-{{ $item->id }}" class="bg-white border border-gray-100 rounded-[24px] overflow-hidden flex flex-col shadow-[0_10px_30px_-14px_rgba(0,0,0,0.1)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_26px_44px_-18px_rgba(249,115,22,0.28)] {{ $item->available_units_count > 0 ? '' : 'opacity-60 grayscale-[0.2]' }} group animate-fade-in-up" style="animation-delay: {{ $index * 40 }}ms">
                            
                            <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $item->id]) }}" wire:navigate class="relative h-[190px] flex items-center justify-center overflow-hidden bg-gray-100">
                                @if($item->main_image)
                                    <img src="{{ Storage::url($item->main_image) }}" alt="{{ $item->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700 ease-in-out">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-orange-200 to-primary-400">
                                        <div class="absolute w-[180px] h-[180px] rounded-full bg-white/35 -top-[60px] -right-[40px]"></div>
                                        <div class="h-full w-full flex items-center justify-center">
                                            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="relative filter drop-shadow-[0_12px_12px_rgba(0,0,0,0.18)] transition-transform duration-500 group-hover:scale-105 group-hover:-rotate-2"><path d="M14 30h14l8 42h46"/><path d="M30 44h60c0 18-12 28-30 28H36"/><circle cx="42" cy="90" r="9"/><circle cx="82" cy="90" r="9"/></svg>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($item->available_units_count > 0)
                                    <span class="absolute right-3.5 top-3.5 bg-white/95 backdrop-blur-sm rounded-full px-3 py-1 text-[12px] font-bold flex items-center gap-1.5 text-green-700 shadow-sm border border-gray-100">
                                        <i class="w-[7px] h-[7px] rounded-full bg-green-600 block"></i> Tersedia {{ $item->available_units_count }}
                                    </span>
                                @else
                                    <span class="absolute right-3.5 top-3.5 bg-white/95 backdrop-blur-sm rounded-full px-3 py-1 text-[12px] font-bold flex items-center gap-1.5 text-gray-500 shadow-sm border border-gray-100">
                                        <i class="w-[7px] h-[7px] rounded-full bg-gray-400 block"></i> Unit Kosong
                                    </span>
                                @endif
                            </a>
                            
                            <div class="p-[20px_22px_22px] flex flex-col flex-1">
                                <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $item->id]) }}" wire:navigate>
                                    <h3 class="text-gray-900 text-[17px] font-bold leading-[1.3] line-clamp-2 hover:text-primary-600 transition-colors">{{ $item->name }}</h3>
                                </a>
                                <p class="text-[13px] mt-1 mb-4 text-gray-500 line-clamp-2">{{ strip_tags($item->description) }}</p>
                                
                                <div class="mt-auto flex items-baseline gap-1.5 text-gray-900 mb-4">
                                    <b class="text-[22px] font-extrabold tracking-tight">Rp{{ number_format($item->rental_price_per_day, 0, ',', '.') }}</b>
                                    <span class="text-[13px] text-gray-400 font-semibold">per hari</span>
                                </div>
                                
                                <div class="flex gap-2 mt-2">
                                    <a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $item->id]) }}" wire:navigate class="flex-1 inline-flex justify-center items-center rounded-xl bg-primary-500 text-white font-bold py-2.5 shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] hover:bg-primary-600 active:scale-95 transition-all text-[14px]">
                                        Lihat detail
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>
        </section>
    </main>
</div>