<?php

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public $client;
    public $categories;
    public $availableOnly = false;
    public $selectedCategory = null;
    public $minPrice = 0;
    public $maxPrice = 1000000;
    public $search = '';

    // Use with() to expose computed properties to the Blade view below
    public function with()
    {
        $productsQuery = Product::where('client_id', $this->client->id)
            ->where('status', 'aktif')
            ->with('category')
            ->withCount(['units as available_units_count' => function ($query) {
                $query->where('status', 'tersedia');
            }]);

        if ($this->search) {
            // ILIKE is used for case-insensitive search in PostgreSQL
            $productsQuery->where('name', 'ilike', '%' . $this->search . '%');
        }

        if ($this->availableOnly) {
            $productsQuery->whereHas('units', function ($query) {
                $query->where('status', 'tersedia');
            });
        }

        if ($this->selectedCategory) {
            $productsQuery->where('category_id', $this->selectedCategory);
        }

        $productsQuery->whereBetween('rental_price_per_day', [(int) $this->minPrice, (int) $this->maxPrice]);

        $allProductsCount = Product::where('client_id', $this->client->id)->where('status', 'aktif')->count();

        return [
            'products' => $productsQuery->orderBy('name')->get(),
            'allProductsCount' => $allProductsCount
        ];
    }
};
?>

<div>
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
                <form wire:submit.prevent="$refresh" class="flex bg-white rounded-lg shadow-sm border border-gray-200 p-1.5 focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 transition-all">
                    <div class="flex-grow flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-3 pr-3 py-2 border-none focus:ring-0 text-gray-700 placeholder-gray-400" placeholder="Cari kamera, lensa, atau aksesoris...">
                    </div>
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-6 rounded-md transition-colors whitespace-nowrap">
                        Cari Alat
                    </button>
                </form>
                
                <!-- Popular Searches -->
                <div class="flex flex-wrap items-center gap-2 mt-4 text-sm">
                    <span class="text-gray-500 mr-1">Pencarian Populer:</span>
                    <button wire:click="$set('search', 'Sony')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Sony</button>
                    <button wire:click="$set('search', 'Canon')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Canon</button>
                    <button wire:click="$set('search', 'Lensa')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Lensa</button>
                    <button wire:click="$set('search', 'Lighting')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Lighting</button>
                    <button wire:click="$set('search', 'Tripod')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-full transition-colors text-xs font-medium border border-gray-200">Tripod</button>
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
                    <a href="#" wire:click.prevent="$set('selectedCategory', null)" class="flex items-center justify-between px-3 py-2 text-sm font-medium {{ $selectedCategory === null ? 'bg-primary-50 text-primary-700 border-l-4 border-primary-500 rounded-r' : 'text-gray-600 hover:bg-gray-50 rounded border-l-4 border-transparent hover:border-gray-300 transition-colors' }}">
                        Semua Peralatan
                        <span class="text-xs {{ $selectedCategory === null ? 'bg-white text-primary-600 border border-primary-200' : 'text-gray-400' }} px-1.5 py-0.5 rounded-full">({{ $allProductsCount }})</span>
                    </a>
                </li>
                @foreach($categories as $cat)
                <li>
                    <a href="#" wire:click.prevent="$set('selectedCategory', {{ $cat->id }})" class="flex items-center justify-between px-3 py-2 text-sm font-medium {{ $selectedCategory === $cat->id ? 'bg-primary-50 text-primary-700 border-l-4 border-primary-500 rounded-r' : 'text-gray-600 hover:bg-gray-50 rounded border-l-4 border-transparent hover:border-gray-300 transition-colors' }}">
                        {{ $cat->name }}
                        <span class="text-xs {{ $selectedCategory === $cat->id ? 'bg-white text-primary-600 border border-primary-200 px-1.5 py-0.5 rounded-full' : 'text-gray-400' }}"></span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        
        <hr class="border-gray-200">

        <!-- Availability Filter -->
        <div>
            <h3 class="font-semibold text-gray-900 mb-4">Status Ketersediaan</h3>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Hanya tampilkan unit tersedia</span>
                <button type="button" 
                        wire:click="$toggle('availableOnly')"
                        class="{{ $availableOnly ? 'bg-primary-500 justify-end' : 'bg-gray-200 justify-start' }} flex items-center h-6 w-11 flex-shrink-0 cursor-pointer rounded-full p-0.5 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" 
                        role="switch" 
                        aria-checked="{{ $availableOnly ? 'true' : 'false' }}">
                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200 ease-in-out"></span>
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
                    <input type="number" wire:model.live.debounce.500ms="minPrice" min="0" step="10000" class="block w-full rounded-md border-0 py-1.5 pl-8 pr-2 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                </div>
                <span class="text-gray-400">-</span>
                <div class="relative rounded-md shadow-sm flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                        <span class="text-gray-500 sm:text-xs">Rp</span>
                    </div>
                    <input type="number" wire:model.live.debounce.500ms="maxPrice" min="0" step="10000" class="block w-full rounded-md border-0 py-1.5 pl-8 pr-2 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                </div>
            </div>
            
            <style>
                input[type=range]::-webkit-slider-thumb {
                    pointer-events: all;
                    width: 20px;
                    height: 20px;
                    -webkit-appearance: none;
                }
            </style>
            
            <div x-data="{
                    minPrice: @entangle('minPrice').live,
                    maxPrice: @entangle('maxPrice').live,
                    min: 0,
                    max: 1000000,
                    minThumb: 0,
                    maxThumb: 0,
                    updateThumbs() {
                        let rawMinThumb = ((this.minPrice - this.min) / (this.max - this.min)) * 100;
                        let rawMaxThumb = 100 - (((this.maxPrice - this.min) / (this.max - this.min)) * 100);
                        this.minThumb = Math.max(0, Math.min(100, rawMinThumb));
                        this.maxThumb = Math.max(0, Math.min(100, rawMaxThumb));
                    }
                }" 
                x-init="updateThumbs(); $watch('minPrice', value => updateThumbs()); $watch('maxPrice', value => updateThumbs())"
                class="relative max-w-xl w-full mt-6 mb-2">
                
                <div>
                    <!-- Hidden native range inputs -->
                    <input type="range" step="10000" x-bind:min="min" x-bind:max="max" x-on:input="minPrice = Math.min($event.target.value, maxPrice - 10000)" x-model="minPrice" class="absolute pointer-events-none appearance-none z-20 h-2 w-full opacity-0 cursor-pointer">
                    <input type="range" step="10000" x-bind:min="min" x-bind:max="max" x-on:input="maxPrice = Math.max($event.target.value, minPrice + 10000)" x-model="maxPrice" class="absolute pointer-events-none appearance-none z-20 h-2 w-full opacity-0 cursor-pointer">
                    
                    <!-- Custom track and thumbs -->
                    <div class="relative z-10 h-1">
                        <div class="absolute z-10 left-0 right-0 bottom-0 top-0 rounded-md bg-gray-200"></div>
                        <div class="absolute z-20 top-0 bottom-0 rounded-md bg-primary-500" x-bind:style="'right:' + maxThumb + '%; left:' + minThumb + '%'"></div>
                        <div class="absolute z-30 w-4 h-4 top-0 left-0 bg-white border-2 border-primary-500 rounded-full -mt-1.5" x-bind:style="'left: ' + minThumb + '%'"></div>
                        <div class="absolute z-30 w-4 h-4 top-0 right-0 bg-white border-2 border-primary-500 rounded-full -mt-1.5" x-bind:style="'right: ' + maxThumb + '%'"></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between text-[10px] text-gray-400 mt-2">
                <span>Min: 0</span>
                <span>Maks: 1Jt+</span>
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
            <p class="text-sm text-gray-500">Menampilkan <span class="font-semibold text-gray-900">{{ $products->count() }}</span> Peralatan</p>
            <div class="flex items-center gap-2">
                <label for="sort" class="text-sm text-gray-500">Urutkan:</label>
                <select id="sort" class="block w-40 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6">
                    <option>Terbaru</option>
                    <option>Harga: Rendah ke Tinggi</option>
                    <option>Harga: Tinggi ke Rendah</option>
                    <option>Abjad: A-Z</option>
                </select>
            </div>
        </div>

        <div wire:loading class="w-full text-center py-10 text-gray-500">
            <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-primary-500 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memperbarui Katalog...
        </div>

        <!-- Product Grid -->
        <div wire:loading.remove class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($products as $item)
            <!-- Card -->
            <a href="/client/kameraku/products/{{ $item->id }}" class="block group h-full">
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col h-full group relative">
                    
                    <!-- Image -->
                    <div class="relative h-48 w-full bg-gray-100 overflow-hidden">
                        <img src="{{ $item->main_image ? Storage::url($item->main_image) : 'https://placehold.co/400x300/e2e8f0/475569?text=No+Image' }}" alt="{{ $item->name }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-500" onerror="this.src='https://placehold.co/400x300/e2e8f0/475569?text=Image+Error'">
                        <!-- Badge -->
                        @if($item->available_units_count > 0)
                        <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full border border-green-200 shadow-sm flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-xs font-semibold text-green-600">Tersedia ({{ $item->available_units_count }})</span>
                        </div>
                        @else
                        <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full border border-gray-200 shadow-sm flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span class="text-xs font-semibold text-gray-600">Kosong</span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="font-bold text-gray-900 text-base mb-1 line-clamp-1">{{ $item->name }}</h3>
                        <p class="text-xs text-gray-500 mb-3">{{ $item->category->name ?? 'Kategori' }}</p>
                        
                        <div class="mt-auto">
                            <div class="flex items-baseline gap-1 mb-3">
                                <span class="font-bold text-xl text-gray-900">Rp {{ number_format($item->rental_price_per_day, 0, ',', '.') }}</span>
                                <span class="text-xs text-gray-500 font-medium">/ hari</span>
                            </div>
                            
                            <div class="flex items-start gap-1.5 mb-5">
                                <svg class="w-3.5 h-3.5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-[10px] text-gray-500 leading-tight">Syarat: KTP + Selfie (Deposit Rp {{ number_format($item->deposit_fee, 0, ',', '.') }})</p>
                            </div>
                            
                            <div class="w-full bg-primary-500 text-white font-semibold py-2.5 px-4 rounded-lg text-center shadow-sm shadow-primary-500/30 group-hover:bg-primary-600 transition-colors">
                                Sewa Alat
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between border-t border-gray-200 pt-6 mt-10">
            <p class="text-sm text-gray-500 hidden sm:block">Menampilkan halaman <span class="font-semibold text-gray-900">1</span> dari <span class="font-semibold text-gray-900">1</span></p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                <button class="p-2 border border-transparent rounded hover:bg-gray-100 text-gray-400 cursor-not-allowed" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button class="w-8 h-8 rounded shadow-sm bg-primary-500 text-white font-medium flex items-center justify-center text-sm">1</button>
                <button class="p-2 border border-transparent rounded hover:bg-gray-100 text-gray-400 cursor-not-allowed" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    </div>
</div>