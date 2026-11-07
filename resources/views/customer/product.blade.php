@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('customer.home', $client->subdomain) }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Beranda
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('customer.home', $client->subdomain) }}" wire:navigate class="ml-1 text-sm font-medium text-gray-500 hover:text-primary-600 md:ml-2">Katalog</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-sm font-medium text-gray-400 md:ml-2 line-clamp-1">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm flex gap-3 items-center">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-800 text-sm flex gap-3 items-center">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
            <!-- Product Image Gallery -->
            @php
                $galleryImages = $product->gallery_images;
            @endphp
            <div class="relative bg-gray-50/70 p-6 sm:p-8 flex flex-col items-center justify-between border-b md:border-b-0 md:border-r border-gray-200"
                 x-data="{
                     activeIdx: 0,
                     images: {{ json_encode($galleryImages) }},
                     next() { this.activeIdx = (this.activeIdx + 1) % this.images.length; },
                     prev() { this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length; }
                 }">
                
                {{-- Stock Badge (Top Left) --}}
                @php
                    $totalUsableUnits = $product->units()->whereNotIn('status', ['rusak', 'maintenance'])->count();
                    $availToday = $product->available_units_count ?? $product->getAvailableUnitsForPeriod();
                @endphp
                @if($availToday > 0)
                <div class="absolute top-4 left-4 z-10 bg-white/95 backdrop-blur-sm px-3.5 py-1.5 rounded-full border border-green-200 shadow-sm flex items-center gap-1.5">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-bold text-green-700">{{ $availToday }} Unit Tersedia</span>
                </div>
                @elseif($totalUsableUnits > 0)
                <div class="absolute top-4 left-4 z-10 bg-white/95 backdrop-blur-sm px-3.5 py-1.5 rounded-full border border-amber-200 shadow-sm flex items-center gap-1.5">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                    <span class="text-xs font-bold text-amber-700">Sedang Disewa (Bisa Booking Jadwal)</span>
                </div>
                @else
                <div class="absolute top-4 left-4 z-10 bg-white/95 backdrop-blur-sm px-3.5 py-1.5 rounded-full border border-red-200 shadow-sm flex items-center gap-1.5">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                    <span class="text-xs font-bold text-red-700">Stok Kosong</span>
                </div>
                @endif

                {{-- Photo Counter Badge (Top Right when > 1 image) --}}
                <div x-show="images.length > 1" class="absolute top-4 right-4 z-10 bg-slate-900/75 backdrop-blur-sm text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span x-text="(activeIdx + 1) + ' / ' + images.length"></span>
                </div>

                {{-- Main Image Viewport with Chevron Navigation --}}
                <div class="w-full flex-1 flex items-center justify-center min-h-[320px] max-h-[460px] relative group my-auto">
                    <img :src="images[activeIdx]" 
                         alt="{{ $product->name }}" 
                         class="w-full h-auto object-contain max-h-[420px] drop-shadow-md rounded-xl transition-all duration-300"
                         onerror="this.src='https://placehold.co/800x600/e2e8f0/475569?text=Image+Error'">

                    {{-- Prev Button --}}
                    <button type="button"
                            x-show="images.length > 1"
                            @click="prev()"
                            class="absolute left-2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-700 shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 hover:scale-105 focus:outline-none"
                            aria-label="Foto Sebelumnya">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    {{-- Next Button --}}
                    <button type="button"
                            x-show="images.length > 1"
                            @click="next()"
                            class="absolute right-2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-700 shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 hover:scale-105 focus:outline-none"
                            aria-label="Foto Berikutnya">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                {{-- Thumbnail Strip (Only shown when > 1 image) --}}
                <div x-show="images.length > 1" class="w-full mt-4 pt-3 border-t border-gray-200/70">
                    <div class="flex items-center justify-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
                        <template x-for="(imgUrl, idx) in images" :key="idx">
                            <button type="button"
                                    @click="activeIdx = idx"
                                    class="relative shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border-2 transition-all duration-200 bg-white"
                                    :class="activeIdx === idx ? 'border-orange-500 ring-2 ring-orange-500/30 scale-105 shadow-sm' : 'border-gray-200 hover:border-orange-300 opacity-70 hover:opacity-100'">
                                <img :src="imgUrl" 
                                     alt="Thumbnail" 
                                     class="w-full h-full object-cover"
                                     onerror="this.src='https://placehold.co/100x100/e2e8f0/475569?text=IMG'">
                            </button>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Product Info -->
            <div class="p-8 lg:p-12 flex flex-col">
                <div class="mb-2">
                    <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">{{ $product->category->name ?? 'Kategori' }}</span>
                </div>
                
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4 leading-tight">{{ $product->name }}</h1>
                
                <div class="flex items-baseline gap-2 mb-6">
                    <span class="text-4xl font-black text-primary-600">Rp {{ number_format($product->rental_price_per_day, 0, ',', '.') }}</span>
                    <span class="text-lg text-gray-500 font-medium">/ hari</span>
                </div>

                <!-- Info Box -->
                <div class="bg-primary-50/50 border border-primary-100 rounded-xl p-4 mb-8">
                    <h3 class="font-semibold text-primary-900 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Bebas Deposit Uang
                    </h3>
                    <ul class="space-y-2 text-sm text-primary-800/80">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-primary-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Jaminan murni menggunakan data diri yang diverifikasi.
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-primary-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Verifikasi Identitas: <strong>KTP Asli & Swafoto (Selfie)</strong>
                        </li>
                    </ul>
                </div>

                <div class="prose prose-sm text-gray-600 mb-8 flex-grow">
                    <h3 class="text-gray-900 font-bold mb-2">Deskripsi Alat</h3>
                    @if($product->description)
                        <p class="leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
                    @else
                        <p class="italic text-gray-400">Belum ada deskripsi untuk alat ini.</p>
                    @endif
                </div>

                <!-- Actions -->
                <div class="mt-auto pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                    <livewire:customer.add-to-cart-modal :product="$product" />
                    
                    @if($totalUsableUnits > 0)
                    <button type="button" 
                            x-data 
                            @click="$dispatch('open-cart-modal')" 
                            class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold text-lg py-4 px-8 rounded-xl text-center transition-all shadow-lg shadow-primary-500/30 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Sewa Sekarang
                    </button>
                    @else
                    <span aria-disabled="true" tabindex="-1" class="flex-1 bg-primary-600 opacity-50 cursor-not-allowed pointer-events-none text-white font-bold text-lg py-4 px-8 rounded-xl text-center flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Stok Habis
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
