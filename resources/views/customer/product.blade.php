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

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
            <!-- Product Image -->
            <div class="relative bg-gray-50 p-8 flex items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
                <img src="{{ $product->main_image ? Storage::url($product->main_image) : 'https://placehold.co/800x600/e2e8f0/475569?text=No+Image' }}" alt="{{ $product->name }}" class="w-full h-auto object-contain max-h-[500px] drop-shadow-xl rounded-lg" onerror="this.src='https://placehold.co/800x600/e2e8f0/475569?text=Image+Error'">
                
                @if($product->units->count() > 0)
                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full border border-green-200 shadow-sm flex items-center gap-1.5">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                    <span class="text-sm font-semibold text-green-600">{{ $product->units->count() }} Unit Tersedia</span>
                </div>
                @else
                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full border border-red-200 shadow-sm flex items-center gap-1.5">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                    <span class="text-sm font-semibold text-red-600">Stok Kosong</span>
                </div>
                @endif
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
                    
                    @if($product->units->count() > 0)
                    <a href="{{ route('customer.checkout', ['subdomain' => $client->subdomain, 'product' => $product->id]) }}" wire:navigate class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold text-lg py-4 px-8 rounded-xl text-center transition-all shadow-lg shadow-primary-500/30 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Sewa Sekarang
                    </a>
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
