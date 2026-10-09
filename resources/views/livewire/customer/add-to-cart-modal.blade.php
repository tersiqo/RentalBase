<div x-data="{
        isOpen: false,
        startDate: '',
        endDate: '',
        quantity: 1,
        pricePerDay: {{ $product->rental_price_per_day }},
        
        get duration() {
            if (!this.startDate || !this.endDate) return 0;
            const start = new Date(this.startDate);
            const end = new Date(this.endDate);
            start.setHours(0,0,0,0);
            end.setHours(0,0,0,0);
            if (end < start) return 0;
            const diffTime = Math.abs(end - start);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        },
        
        get totalPrice() {
            return this.duration * this.pricePerDay * this.quantity;
        },
        
        get formattedTotal() {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(this.totalPrice);
        },
        
        submit() {
            if (this.duration === 0) return;
            $wire.confirm(this.startDate, this.endDate, this.quantity);
        }
    }"
    @close-cart-modal.window="isOpen = false"
    @keydown.escape.window="isOpen = false">
    
    <!-- Button Trigger -->
    <button @click="isOpen = true" type="button" class="flex-none bg-white border-2 border-primary-200 text-primary-600 hover:bg-primary-50 hover:border-primary-300 font-bold text-lg py-4 px-6 rounded-xl text-center transition-all flex items-center justify-center gap-2 {{ $product->units->count() == 0 ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}" title="Tambah ke Keranjang">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.5 12h11L21 7H6"/></svg>
        <span class="hidden xl:inline">Keranjang</span>
    </button>

    <!-- Modal Backdrop & Body -->
    <div x-show="isOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/75 transition-opacity backdrop-blur-sm z-0" aria-hidden="true" @click="isOpen = false"></div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start mb-5">
                        <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Tambah ke Keranjang</h3>
                        <button @click="isOpen = false" class="text-gray-400 hover:text-gray-500 transition-colors">
                            <span class="sr-only">Tutup</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Product Summary -->
                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl mb-6">
                        <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center border border-gray-200 overflow-hidden flex-shrink-0">
                            <img src="{{ $product->main_image ? Storage::url($product->main_image) : 'https://placehold.co/100x100/e2e8f0/475569?text=IMG' }}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 line-clamp-1">{{ $product->name }}</h4>
                            <p class="text-sm text-primary-600 font-medium">Rp {{ number_format($product->rental_price_per_day, 0, ',', '.') }} <span class="text-gray-500 text-xs">/ hari</span></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Date Pickers -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" x-model="startDate" min="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date" x-model="endDate" :min="startDate || '{{ date('Y-m-d') }}'" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Unit (Maks: {{ $product->units->count() }})</label>
                            <input type="number" x-model="quantity" min="1" max="{{ $product->units->count() }}" class="w-full sm:w-1/3 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        </div>

                        <!-- Calculation Auto-Update -->
                        <div class="bg-primary-50 border border-primary-100 p-4 rounded-xl mt-6">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-primary-800">Durasi Sewa:</span>
                                <span class="font-bold text-primary-900" x-text="duration + ' Hari'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-primary-800">Kuantitas:</span>
                                <span class="font-bold text-primary-900" x-text="quantity + ' Unit'"></span>
                            </div>
                            <hr class="border-primary-200 my-2">
                            <div class="flex justify-between items-end">
                                <span class="text-sm font-bold text-primary-900">Estimasi Total Biaya:</span>
                                <span class="text-2xl font-black text-primary-600" x-text="'Rp ' + formattedTotal"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-4 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-gray-100">
                    <button type="button" @click="isOpen = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Batal
                    </button>
                    <!-- Loading Indicator on button -->
                    <button type="button" @click="submit" 
                            :disabled="duration === 0"
                            :class="duration === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                            class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:w-auto sm:text-sm transition-colors relative">
                        <span wire:loading.remove wire:target="confirm">Tambahkan ke Keranjang</span>
                        <span wire:loading wire:target="confirm" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
