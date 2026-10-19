@extends('layouts.customer')

@section('content')
<div class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full" 
     x-data="{
        lightboxOpen: false,
        lightboxImg: '',
        lightboxCaption: '',
        cancelModalOpen: false,
        cancelActionUrl: '',
        cancelOrderNumber: '',
        returnModalOpen: false,
        returnActionUrl: '',
        returnOrderNumber: '',
        receiveModalOpen: false,
        receiveActionUrl: '',
        receiveOrderNumber: '',
        toastMessage: '',
        showToast: false,

        openLightbox(img, caption) {
            this.lightboxImg = img;
            this.lightboxCaption = caption;
            this.lightboxOpen = true;
        },
        openCancelModal(orderNum, actionUrl) {
            this.cancelOrderNumber = orderNum;
            this.cancelActionUrl = actionUrl;
            this.cancelModalOpen = true;
        },
        openReturnModal(orderNum, actionUrl) {
            this.returnOrderNumber = orderNum;
            this.returnActionUrl = actionUrl;
            this.returnModalOpen = true;
        },
        openReceiveModal(orderNum, actionUrl) {
            this.receiveOrderNumber = orderNum;
            this.receiveActionUrl = actionUrl;
            this.receiveModalOpen = true;
        },
        copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
                this.toast('Nomor disalin: ' + text);
            }
        },
        toast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3500);
        }
     }">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm font-medium">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3.5 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm font-medium">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Render Detail Content Partial -->
    @include('customer.orders.detail-content', [
        'order' => $order, 
        'client' => $client, 
        'backRoute' => route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)]))
    ])

    <!-- Interactive Action Modals -->
    
    <!-- Modal 1: Batalkan Pesanan -->
    <div x-show="cancelModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center" @click.away="cancelModalOpen = false">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                ⚠️
            </div>
            <h3 class="font-bold text-lg text-gray-900 mb-2">Batalkan Pesanan?</h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-5 leading-relaxed">
                Pesanan <b class="font-mono text-gray-800" x-text="cancelOrderNumber"></b> akan dibatalkan dan proses sewa dihentikan.
            </p>
            <form :action="cancelActionUrl" method="POST">
                @csrf
                <div class="text-left mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Pembatalan (Opsional)</label>
                    <textarea name="reason" rows="2" placeholder="Tulis alasan pembatalan..." class="w-full text-xs p-2.5 rounded-xl border border-gray-300 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="cancelModalOpen = false" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Kembali
                    </button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-red-600 hover:bg-red-700 text-white transition shadow-sm">
                        Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Ajukan Pengembalian -->
    <div x-show="returnModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="returnModalOpen = false">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">↩️</span>
                    <h3 class="font-bold text-base text-gray-900">Ajukan Pengembalian Barang</h3>
                </div>
                <button type="button" @click="returnModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="returnActionUrl" method="POST" class="p-6">
                @csrf
                <p class="text-xs sm:text-sm text-gray-500 mb-4">
                    Pesanan <b class="font-mono text-gray-900" x-text="returnOrderNumber"></b>. Silakan pilih rencana pengembalian dan berikan keterangan kondisi barang saat selesai sewa.
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Metode Pengembalian</label>
                        <select name="return_method" class="w-full text-xs sm:text-sm p-2.5 rounded-xl border border-gray-300 focus:ring-orange-500 focus:border-orange-500 font-medium">
                            <option value="ambil_toko">Diantar langsung ke toko rental</option>
                            <option value="kurir">Dijemput kurir / dikirim via ekspedisi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Keterangan Kondisi Barang</label>
                        <textarea name="notes" rows="3" required placeholder="Contoh: Masa sewa telah selesai, seluruh unit bersih dan lengkap siap diantar Sabtu pagi." class="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:ring-orange-500 focus:border-orange-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="returnModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-sm bg-orange-500 hover:bg-orange-600 text-white transition shadow-sm">
                        Ajukan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Konfirmasi Terima Barang -->
    <div x-show="receiveModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center" @click.away="receiveModalOpen = false">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                📦
            </div>
            <h3 class="font-bold text-lg text-gray-900 mb-2">Konfirmasi Terima Barang?</h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-6 leading-relaxed">
                Pastikan seluruh perlengkapan pesanan <b class="font-mono text-gray-800" x-text="receiveOrderNumber"></b> sudah sampai di tangan Anda dalam kondisi baik dan berfungsi normal.
            </p>
            <form :action="receiveActionUrl" method="POST" class="flex gap-3">
                @csrf
                <button type="button" @click="receiveModalOpen = false" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Belum Diterima
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                    Ya, Sudah Diterima
                </button>
            </form>
        </div>
    </div>

    <!-- Modal 4: Lightbox Image Viewer -->
    <div x-show="lightboxOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden p-4" @click.away="lightboxOpen = false">
            <div class="flex justify-between items-center pb-2 mb-2 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900" x-text="lightboxCaption"></span>
                <button type="button" @click="lightboxOpen = false" class="text-gray-400 hover:text-gray-700 text-lg">✕</button>
            </div>
            <div class="p-1 bg-gray-50 rounded-xl flex items-center justify-center">
                <img :src="lightboxImg" :alt="lightboxCaption" class="max-h-[75vh] w-auto max-w-full rounded-lg object-contain">
            </div>
            <div class="pt-3 text-right">
                <button type="button" @click="lightboxOpen = false" class="px-4 py-1.5 bg-gray-100 text-gray-800 font-semibold rounded-xl text-xs hover:bg-gray-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white text-xs sm:text-sm font-semibold px-4 py-3 rounded-xl shadow-xl flex items-center gap-2 border-l-4 border-emerald-400" 
         style="display: none;">
        <span>✅</span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
