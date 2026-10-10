<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        get grandTotal() {
            let total = 0;
            $wire.selectedItems.forEach(hash => {
                if($wire.cart[hash]) {
                    total += $wire.cart[hash].subtotal;
                }
            });
            return total;
        },
        get selectedCount() {
            let count = 0;
            $wire.selectedItems.forEach(hash => {
                if($wire.cart[hash]) {
                    count += $wire.cart[hash].quantity;
                }
            });
            return count;
        },
        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        },
        changeQty(hash, delta) {
            let item = $wire.cart[hash];
            if (!item) return;
            
            let newQty = parseInt(item.quantity) + delta;
            if (isNaN(newQty) || newQty < 1) newQty = 1;
            
            item.quantity = newQty;
            item.subtotal = newQty * item.price * item.duration_days;
            
            $wire.updateQuantity(hash, newQty);
        },
        setQty(hash, value) {
            let item = $wire.cart[hash];
            if (!item) return;
            
            let newQty = parseInt(value);
            if (isNaN(newQty) || newQty < 1) newQty = 1;
            
            item.quantity = newQty;
            item.subtotal = newQty * item.price * item.duration_days;
            
            $wire.updateQuantity(hash, newQty);
        }
    }">
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
                    <span class="ml-1 text-sm font-medium text-gray-400 md:ml-2">Keranjang Sewa</span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Keranjang Sewa</h1>

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm flex gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(empty($cart))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Keranjang kamu masih kosong</h3>
            <p class="text-gray-500 mb-8 max-w-md mx-auto">Mulai cari perlengkapan impianmu dan tambahkan ke keranjang sebelum kehabisan stok tanggal sewa.</p>
            <a href="{{ route('customer.home', $client->subdomain) }}" wire:navigate class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-lg shadow-primary-500/30">
                Eksplorasi Katalog
            </a>
        </div>
    @else
        <!-- Desktop Check All (Optional above cart list) -->
        <div class="hidden lg:flex items-center mb-4 px-2">
            <input type="checkbox" 
                x-on:change="$event.target.checked ? $wire.selectedItems = Object.keys($wire.cart) : $wire.selectedItems = []"
                x-bind:checked="Object.keys($wire.cart).length > 0 && $wire.selectedItems.length === Object.keys($wire.cart).length"
                id="selectAllDesktop" class="w-5 h-5 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 cursor-pointer">
            <label for="selectAllDesktop" class="ml-3 text-sm font-semibold text-gray-700 cursor-pointer">Pilih Semua</label>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-8 items-start mb-20 lg:mb-0">
            <!-- Cart Items List -->
            <div class="w-full lg:flex-1 space-y-6">
                @foreach($groupedCart as $dateGroup => $items)
                    @php
                        $dates = explode('|', $dateGroup);
                        $start = \Carbon\Carbon::parse($dates[0])->format('d M Y');
                        $end = \Carbon\Carbon::parse($dates[1])->format('d M Y');
                        $groupHashes = $items->pluck('hash')->toJson();
                    @endphp
                    <div x-data="{ groupHashes: {{ $groupHashes }} }" 
                         x-show="groupHashes.some(hash => $wire.cart[hash] !== undefined)" 
                         x-transition.opacity.duration.300ms 
                         class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative group">
                        <!-- Group Header -->
                        <div class="bg-gray-50 border-b border-gray-100 px-4 sm:px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 leading-tight">Jadwal Sewa</h4>
                                    <p class="text-xs sm:text-sm text-gray-500">{{ $start }} - {{ $end }}</p>
                                </div>
                            </div>
                            <span class="text-xs sm:text-sm font-semibold text-primary-600 bg-primary-50 px-3 py-1 rounded-full">{{ $items->first()['duration_days'] }} Hari</span>
                        </div>
                        
                        <!-- Items -->
                        <div class="divide-y divide-gray-100">
                            @foreach($items as $item)
                                @php $hash = $item['hash']; @endphp
                                <div wire:key="cart-item-{{ $hash }}" x-data="{ visible: true }" x-show="visible" x-transition.opacity.duration.300ms class="p-4 sm:p-6 transition-all hover:bg-gray-50/50 relative flex gap-3 sm:gap-4">
                                    
                                    <!-- Checkbox -->
                                    <div class="flex items-start pt-2 sm:pt-4">
                                        <input type="checkbox" wire:model.live="selectedItems" value="{{ $hash }}" class="w-5 h-5 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 cursor-pointer">
                                    </div>
                                    
                                    <div class="flex-grow flex flex-col sm:flex-row gap-5 relative">
                                        <!-- Remove button -->
                                        <button x-on:click="
                                            visible = false;
                                            setTimeout(() => {
                                                $wire.selectedItems = $wire.selectedItems.filter(id => id !== '{{ $hash }}');
                                                delete $wire.cart['{{ $hash }}'];
                                                $wire.removeItem('{{ $hash }}');
                                            }, 250);
                                        " class="absolute -top-2 -right-2 sm:-top-4 sm:-right-4 text-gray-300 hover:text-red-500 transition-colors p-2" title="Hapus Item">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>

                                        <!-- Image -->
                                        <div class="w-full sm:w-28 h-28 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0 border border-gray-200">
                                            <img src="{{ $item['main_image'] ? Storage::url($item['main_image']) : 'https://placehold.co/400x300/e2e8f0/475569?text=Image' }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                        </div>

                                        <!-- Details -->
                                        <div class="flex-grow flex flex-col justify-between">
                                            <div class="pr-8">
                                                <h3 class="text-lg font-bold text-gray-900 mb-1 leading-tight"><a href="{{ route('customer.product.show', ['subdomain' => $client->subdomain, 'product' => $item['product_id']]) }}" wire:navigate class="hover:text-primary-600">{{ $item['name'] }}</a></h3>
                                                <div class="text-sm text-gray-500 mb-3">
                                                    Biaya per hari: Rp {{ number_format($item['price'], 0, ',', '.') }}
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-end justify-between mt-4 sm:mt-0">
                                                <div class="flex items-center gap-2">
                                                    <label class="hidden sm:block text-xs font-semibold text-gray-500 uppercase tracking-wide">Qty</label>
                                                    <div class="flex items-center border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm">
                                                        <button x-on:click="changeQty('{{ $hash }}', -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors" x-bind:disabled="$wire.cart['{{ $hash }}'].quantity <= 1" x-bind:class="$wire.cart['{{ $hash }}'].quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                        </button>
                                                        <input type="number" x-bind:value="$wire.cart['{{ $hash }}'].quantity" x-on:change="setQty('{{ $hash }}', $event.target.value)" min="1" class="w-10 text-center border-0 focus:ring-0 text-sm font-semibold p-0 h-8 bg-transparent">
                                                        <button x-on:click="changeQty('{{ $hash }}', 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xs text-gray-500 mb-0.5">Subtotal</div>
                                                    <div class="text-xl font-bold text-primary-600 leading-none" x-text="'Rp ' + formatRupiah($wire.cart['{{ $hash }}'].subtotal)">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Order Summary Sticky Sidebar -->
            <div class="w-full lg:w-96 flex-shrink-0">
                <div class="bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 p-6 lg:sticky lg:top-8">
                    <h2 class="text-lg font-extrabold text-gray-900 mb-6">Ringkasan Sewa</h2>
                    
                    <div class="space-y-4 mb-6 relative">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal (<span x-text="selectedCount">{{ $selectedCount }}</span> alat)</span>
                            <span class="font-semibold text-gray-900" x-text="'Rp ' + formatRupiah(grandTotal)">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mb-8">
                        <div class="flex justify-between items-end relative">
                            <span class="text-base font-bold text-gray-900">Total Tagihan</span>
                            <div class="text-right">
                                <span class="block text-2xl font-black text-primary-600 leading-none transition-opacity duration-200" x-text="'Rp ' + formatRupiah(grandTotal)">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-gray-400 font-medium">*Belum termasuk ongkos kirim/kurir (jika ada)</span>
                            </div>
                        </div>
                    </div>

                    <button wire:click="proceedToCheckout" class="w-full bg-primary-600 text-white font-bold text-lg py-4 px-6 rounded-xl text-center transition-all flex items-center justify-center gap-2"
                        x-bind:class="$wire.selectedItems.length > 0 ? 'hover:bg-primary-700 shadow-lg shadow-primary-500/30' : 'opacity-50 cursor-not-allowed shadow-none'"
                        x-bind:disabled="$wire.selectedItems.length === 0">
                        <span>Lanjut Pembayaran <span x-show="$wire.selectedItems.length > 0" x-text="'(' + $wire.selectedItems.length + ')'"></span></span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                    
                    <div class="mt-4 flex items-start gap-2 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <p class="text-xs text-blue-800/80 leading-relaxed">
                            Sewa aman bebas deposit tunai cukup dengan verifikasi e-KTP. Transaksi 100% diproses melalui sistem pembayaran resmi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Floating Checkout Bar -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50 lg:hidden shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <div class="flex justify-between items-center max-w-7xl mx-auto relative">
                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                        x-on:change="$event.target.checked ? $wire.selectedItems = Object.keys($wire.cart) : $wire.selectedItems = []"
                        x-bind:checked="Object.keys($wire.cart).length > 0 && $wire.selectedItems.length === Object.keys($wire.cart).length"
                        id="selectAllMobile" class="w-5 h-5 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 cursor-pointer">
                    <div>
                        <div class="text-[10px] text-gray-500 font-medium">Semua</div>
                        <div class="text-base font-bold text-primary-600 leading-none mt-0.5 transition-opacity duration-200" x-text="'Rp ' + formatRupiah(grandTotal)">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                    </div>
                </div>
                <button wire:click="proceedToCheckout" class="bg-primary-600 text-white font-bold py-2.5 px-6 rounded-xl transition-all"
                    x-bind:class="$wire.selectedItems.length > 0 ? 'hover:bg-primary-700 shadow-md shadow-primary-500/30' : 'opacity-50 cursor-not-allowed shadow-none'"
                    x-bind:disabled="$wire.selectedItems.length === 0">
                    <span>Checkout <span x-show="$wire.selectedItems.length > 0" x-text="'(' + $wire.selectedItems.length + ')'"></span></span>
                </button>
            </div>
        </div>
    @endif
</div>
