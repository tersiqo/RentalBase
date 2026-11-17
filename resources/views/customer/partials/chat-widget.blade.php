@php
    $tenantProfile = $client?->tenantProfile ?? $client?->profile;
    $storeName = $tenantProfile?->name ?? $client->nama_usaha ?? ($client->business_name ?? 'Toko Rental');
    $storeLogo = $client?->logo_url;
    $isAuthenticated = auth()->check();
    $currentUserId = auth()->id();
    $clientSubdomain = $client?->subdomain ?? session('active_client_subdomain', 'babyrental');

    $activeProductContext = null;
    if (isset($product) && $product instanceof \App\Models\Product) {
        $activeProductContext = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => 'Rp ' . number_format((float) ($product->rental_price_per_day ?? 0), 0, ',', '.') . '/hari',
            'image' => $product->cover_image_url,
            'url' => route('customer.product.show', ['subdomain' => $clientSubdomain, 'product' => $product->id]),
        ];
    }

    $activeOrderContext = null;
    if (isset($order) && $order instanceof \App\Models\Order) {
        $activeOrderContext = [
            'id' => $order->id,
            'order_number' => $order->order_number ?? ('ORD-' . $order->id),
        ];
    }
@endphp

<div x-data="customerChatWidget({
        isAuthenticated: {{ $isAuthenticated ? 'true' : 'false' }},
        userId: {{ $currentUserId ?? 'null' }},
        clientId: {{ $client->id ?? 1 }},
        clientSubdomain: '{{ $clientSubdomain }}',
        storeName: '{{ addslashes($storeName) }}',
        storeLogo: '{{ $storeLogo ? addslashes($storeLogo) : '' }}',
        activeProduct: {{ $activeProductContext ? json_encode($activeProductContext) : 'null' }},
        activeOrder: {{ $activeOrderContext ? json_encode($activeOrderContext) : 'null' }}
     })"
     x-init="initWidget()"
     class="relative">

    <!-- Floating Action Button (FAB) -->
    <div class="fixed bottom-6 right-6 z-40 sm:bottom-7 sm:right-7">
        <button type="button"
                @click="toggleWidget()"
                class="relative group w-14 h-14 sm:w-15 sm:h-15 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                style="background-color: var(--tenant-theme, #f97316); color: var(--tenant-text, #ffffff); box-shadow: 0 10px 25px -5px color-mix(in srgb, var(--tenant-theme, #f97316) 40%, transparent);"
                :aria-label="isOpen ? 'Tutup obrolan' : 'Buka obrolan dengan toko'">
            
            <!-- Icon Chat (Normal State) -->
            <span x-show="!isOpen" x-transition.opacity.duration.200ms class="flex items-center justify-center">
                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </span>

            <!-- Icon Silang X (Open State) -->
            <span x-show="isOpen" x-cloak x-transition.opacity.duration.200ms class="flex items-center justify-center">
                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>

            <!-- Unread Badge (Pill Merah Menyala) -->
            <span x-show="unreadCount > 0 && !isOpen"
                  x-cloak
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="scale-0 opacity-0"
                  x-transition:enter-end="scale-100 opacity-100"
                  class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-rose-600 text-white font-extrabold text-[11px] flex items-center justify-center ring-2 ring-white shadow-md"
                  x-text="unreadCount > 99 ? '99+' : unreadCount">
            </span>
        </button>
    </div>

    <!-- Chat Modal Window Box -->
    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="fixed bottom-24 right-4 sm:right-7 z-50 w-[92vw] sm:w-[390px] h-[550px] max-h-[80vh] bg-white rounded-3xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden font-sans">

        <!-- Header Chat Window -->
        <div class="px-4 py-3.5 flex items-center justify-between border-b border-gray-100 text-white shrink-0"
             style="background-color: var(--tenant-theme, #f97316); color: var(--tenant-text, #ffffff);">
            <div class="flex items-center gap-3 min-w-0">
                <div class="relative shrink-0">
                    <template x-if="storeLogo">
                        <img :src="storeLogo" :alt="storeName" class="w-10 h-10 rounded-full object-cover bg-white ring-2 ring-white/30">
                    </template>
                    <template x-if="!storeLogo">
                        <div class="w-10 h-10 rounded-full bg-white/20 text-white font-bold flex items-center justify-center text-sm ring-2 ring-white/30" x-text="(storeName || 'T').charAt(0)"></div>
                    </template>
                </div>
                <div class="min-w-0">
                    <h4 class="text-sm font-bold truncate leading-tight" x-text="storeName"></h4>
                    <p class="text-[11px] text-white/80 leading-tight mt-0.5">Layanan Pelanggan Toko</p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <button type="button"
                        @click="toggleWidget()"
                        class="p-1.5 rounded-full hover:bg-black/10 transition cursor-pointer text-white/90 hover:text-white"
                        title="Tutup obrolan">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        </div>

        @guest
        <!-- Guest State (Belum Login) -->
        <div class="flex-1 p-6 flex flex-col items-center justify-center text-center bg-gray-50/50">
            <div class="w-16 h-16 rounded-2xl bg-orange-100 text-primary-600 flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <h5 class="text-base font-bold text-gray-900 mb-1">Mulai Obrolan dengan Toko</h5>
            <p class="text-xs text-gray-500 mb-6 max-w-xs leading-relaxed">
                Silakan masuk ke akun Anda terlebih dahulu untuk konsultasi ketersediaan barang dan info sewa langsung dengan admin.
            </p>
            <a href="{{ route('login', ['redirect' => url()->current()]) }}"
               class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md transition transform hover:scale-102"
               style="background-color: var(--tenant-theme, #f97316);">
                Masuk Sekarang
            </a>
        </div>
        @else
        <!-- Authenticated State: Message Container -->
        <div class="flex-1 flex flex-col min-h-0 bg-stone-50/50">

            <!-- Bar Notifikasi Background Perangkat (Customer Chat) -->
            <div x-data="rbWebPush()" x-init="init()" x-show="isSupported && !isSubscribed" x-cloak class="px-3.5 py-2 bg-orange-50/90 border-b border-orange-100 flex items-center justify-between gap-2 shrink-0">
                <span class="text-[11px] font-medium text-orange-900 truncate">Terima notif balasan saat web ditutup?</span>
                <button type="button"
                        @click="subscribe()"
                        :disabled="isLoading"
                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-orange-500 text-white hover:bg-orange-600 transition shrink-0 cursor-pointer">
                    <span x-show="!isLoading">Aktifkan</span>
                    <span x-show="isLoading">...</span>
                </button>
            </div>
            
            <!-- Message List Scrollable -->
            <div id="customer-chat-messages" 
                 class="flex-1 overflow-y-auto p-4 space-y-3.5">
                
                <!-- Welcome Card -->
                <div class="p-3.5 rounded-2xl bg-white border border-gray-100 text-center shadow-2xs">
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Selamat datang di layanan obrolan <strong class="text-gray-800" x-text="storeName"></strong>. Ada yang bisa kami bantu seputar perlengkapan sewa?
                    </p>
                </div>

                <!-- Messages Stream -->
                <template x-for="(m, idx) in messages" :key="m.id || idx">
                    <div class="flex items-end gap-2" :class="m.sender_role === 'customer' ? 'justify-end' : 'justify-start'">
                        
                        <!-- Avatar Toko (Khusus Pesan Masuk dari Admin) -->
                        <template x-if="m.sender_role !== 'customer'">
                            <div class="shrink-0 mb-0.5">
                                <template x-if="storeLogo">
                                    <img :src="storeLogo" :alt="storeName" class="w-6 h-6 rounded-full object-cover bg-white ring-1 ring-gray-200 shadow-2xs">
                                </template>
                                <template x-if="!storeLogo">
                                    <div class="w-6 h-6 rounded-full text-white font-bold text-[9px] flex items-center justify-center ring-1 ring-white/20 shadow-2xs"
                                         style="background-color: var(--tenant-theme, #f97316);"
                                         x-text="(storeName || 'T').charAt(0)"></div>
                                </template>
                            </div>
                        </template>

                        <div class="flex flex-col max-w-[82%]" :class="m.sender_role === 'customer' ? 'items-end' : 'items-start'">
                            
                            <!-- Label Identitas Admin Toko -->
                            <template x-if="m.sender_role !== 'customer'">
                                <div class="flex items-center gap-1.5 mb-1 ml-1">
                                    <span class="text-[11px] font-bold text-gray-700 leading-none" x-text="storeName"></span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-orange-100 text-orange-700 leading-none">Admin</span>
                                </div>
                            </template>

                            <!-- Bubble Container -->
                            <div class="rounded-2xl px-3.5 py-2.5 shadow-2xs space-y-2 w-full"
                                 :class="m.sender_role === 'customer' 
                                     ? 'text-white rounded-br-xs' 
                                     : 'bg-white border border-gray-100 text-gray-800 rounded-bl-xs'"
                                 :style="m.sender_role === 'customer' ? 'background-color: var(--tenant-theme, #f97316); color: var(--tenant-text, #ffffff);' : ''">

                                <!-- Sematan Kartu Konteks (Produk / Order) -->
                                <template x-if="m.context_data">
                                    <div class="p-2 rounded-xl text-left border"
                                         :class="m.sender_role === 'customer' ? 'bg-black/10 border-white/20 text-white' : 'bg-gray-50 border-gray-200 text-gray-900'">
                                        
                                        <!-- Konteks Produk -->
                                        <template x-if="m.context_data.type === 'product'">
                                            <a :href="m.context_data.url" target="_blank" class="flex items-center gap-2.5 group">
                                                <img :src="m.context_data.image" class="w-10 h-10 rounded-lg object-cover bg-white shrink-0">
                                                <div class="min-w-0">
                                                    <div class="text-[10px] uppercase font-bold opacity-80">Tanya Produk</div>
                                                    <div class="text-xs font-bold truncate group-hover:underline" x-text="m.context_data.name"></div>
                                                    <div class="text-[11px] font-semibold opacity-90" x-text="m.context_data.price"></div>
                                                </div>
                                            </a>
                                        </template>

                                        <!-- Konteks Pesanan -->
                                        <template x-if="m.context_data.type === 'order'">
                                            <div class="text-xs space-y-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-bold font-mono text-[11px]" x-text="m.context_data.order_number"></span>
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                                                          :class="m.context_data.status_color"
                                                          x-text="m.context_data.status_label"></span>
                                                </div>
                                                <div class="text-[11px] opacity-85" x-text="'Total: ' + m.context_data.total"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <!-- Lampiran Gambar -->
                                <template x-if="m.attachment_url">
                                    <div class="rounded-xl overflow-hidden cursor-pointer" @click="window.open(m.attachment_url, '_blank')">
                                        <img :src="m.attachment_url" alt="Lampiran" class="max-h-48 w-full object-cover rounded-xl hover:opacity-95 transition">
                                    </div>
                                </template>

                                <!-- Teks Pesan -->
                                <p class="text-xs leading-relaxed whitespace-pre-line break-words" x-text="m.message"></p>

                                <!-- Metadata Waktu & Status Baca -->
                                <div class="flex items-center justify-end gap-1 text-[10px] pt-0.5 select-none"
                                     :class="m.sender_role === 'customer' ? 'text-white/80' : 'text-gray-400'">
                                    <span x-text="m.time_formatted"></span>
                                    
                                    <!-- Centang Status Khusus Pengirim Customer -->
                                    <template x-if="m.sender_role === 'customer'">
                                        <span class="inline-flex items-center ml-0.5">
                                            <!-- Centang Dua (Dibaca) -->
                                            <template x-if="m.is_read">
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Dibaca">
                                                    <path d="M18 6L7 17l-5-5"/>
                                                    <path d="M22 10l-7.5 7.5L13 16"/>
                                                </svg>
                                            </template>
                                            <!-- Centang Satu (Terkirim) -->
                                            <template x-if="!m.is_read">
                                                <svg class="w-3.5 h-3.5 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Terkirim">
                                                    <path d="M20 6L9 17l-5-5"/>
                                                </svg>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Indicator Sedang Mengetik / Loading -->
                <div x-show="isSending" x-cloak class="flex items-center gap-2 text-xs text-gray-400 italic">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce [animation-delay:0.4s]"></span>
                    <span>Mengirim pesan...</span>
                </div>
            </div>

            <!-- Quick Reply Chips -->
            <div class="px-3 py-2 bg-white/80 border-t border-gray-100/80 overflow-x-auto scrollbar-none flex items-center gap-1.5 shrink-0">
                <template x-for="(chip, cIdx) in quickChips" :key="cIdx">
                    <button type="button"
                            @click="useChip(chip)"
                            class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 hover:bg-orange-50 hover:text-primary-600 text-gray-600 transition whitespace-nowrap shrink-0 border border-gray-200/60 cursor-pointer">
                        <span x-text="chip"></span>
                    </button>
                </template>
            </div>

            <!-- Pending Sematan Konteks Box (Sebelum Dikirim) -->
            <div x-show="pendingContext" x-cloak class="px-3 py-2 bg-orange-50 border-t border-orange-200 flex items-center justify-between text-xs text-orange-900 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="font-bold text-[10px] uppercase bg-orange-200/80 text-orange-800 px-1.5 py-0.5 rounded" x-text="pendingContext?.type === 'product' ? 'Produk' : 'Pesanan'"></span>
                    <span class="truncate font-semibold" x-text="pendingContext?.title"></span>
                </div>
                <button type="button" @click="pendingContext = null" class="text-orange-500 hover:text-orange-800 p-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Pending Lampiran Foto Box (Sebelum Dikirim) -->
            <div x-show="attachmentPreview" x-cloak class="px-3 py-2 bg-gray-100 border-t border-gray-200 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <img :src="attachmentPreview" class="w-8 h-8 rounded-lg object-cover border border-gray-300">
                    <span class="text-xs text-gray-600 truncate">Foto siap dilampirkan</span>
                </div>
                <button type="button" @click="removeAttachment()" class="text-gray-400 hover:text-gray-700 p-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Input Bar Form -->
            <form @submit.prevent="sendMessage()" class="p-3 bg-white border-t border-gray-100 flex items-end gap-2 shrink-0">
                
                <!-- Tombol Unggah Foto -->
                <label class="p-2 text-gray-400 hover:text-primary-600 rounded-xl hover:bg-gray-100 transition cursor-pointer shrink-0" title="Lampirkan foto">
                    <input type="file" accept="image/*" class="hidden" @change="handleFileSelect($event)">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>

                <!-- Input Pesan -->
                <div class="flex-1 min-w-0">
                    <textarea x-model="messageText"
                              @keydown.enter.exact.prevent="sendMessage()"
                              placeholder="Tulis pesan ke toko..."
                              rows="1"
                              class="w-full text-xs text-gray-800 placeholder-gray-400 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:bg-white resize-none max-h-24"></textarea>
                </div>

                <!-- Tombol Kirim -->
                <button type="submit"
                        :disabled="isSending || (!messageText.trim() && !attachmentFile)"
                        class="p-2.5 rounded-xl font-bold text-white transition disabled:opacity-40 disabled:cursor-not-allowed shrink-0 shadow-xs cursor-pointer"
                        style="background-color: var(--tenant-theme, #f97316); color: var(--tenant-text, #ffffff);">
                    <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                    </svg>
                </button>
            </form>
        </div>
        @endguest

    </div>
</div>

<script>
function customerChatWidget(config) {
    return {
        isOpen: false,
        isLoading: false,
        isSending: false,
        storeName: config.storeName || 'Toko Rental',
        storeLogo: config.storeLogo || '',
        activeProduct: config.activeProduct || null,
        activeOrder: config.activeOrder || null,
        conversation: null,
        messages: [],
        unreadCount: 0,
        messageText: '',
        attachmentFile: null,
        attachmentPreview: null,
        pendingContext: config.activeProduct 
            ? { type: 'product', id: config.activeProduct.id, title: config.activeProduct.name }
            : (config.activeOrder ? { type: 'order', id: config.activeOrder.id, title: 'Pesanan #' + config.activeOrder.order_number } : null),
        echoBound: false,

        get quickChips() {
            if (this.activeProduct) {
                return [
                    'Apakah ' + this.activeProduct.name + ' ready disewa?',
                    'Bisa dikirim langsung hari ini?',
                    'Bagaimana syarat verifikasi KTP?',
                    'Bisa sewa lebih dari 3 hari?',
                ];
            }
            if (this.activeOrder) {
                return [
                    'Tanya status pesanan #' + this.activeOrder.order_number,
                    'Bagaimana proses pengembalian alat?',
                    'Saya ingin konfirmasi pengambilan',
                ];
            }
            return [
                'Apakah alat sewa masih ready?',
                'Bisa dikirim langsung hari ini?',
                'Bagaimana syarat verifikasi KTP?',
                'Bisa perpanjang masa sewa?',
            ];
        },

        initWidget() {
            window.__rbCustomerChat = this;

            if (config.isAuthenticated) {
                this.loadActiveConversation();
            }

            if (window.location.search.includes('open_chat=1') || window.location.hash === '#open-chat') {
                this.isOpen = true;
                this.$nextTick(() => this.scrollToBottom());
            }

            // Dengarkan event untuk toggle/buka chat dari toast notifikasi global
            window.addEventListener('toggle-customer-chat', () => {
                this.isOpen = true;
                if (config.isAuthenticated && !this.conversation) {
                    this.loadActiveConversation();
                }
                this.$nextTick(() => this.scrollToBottom());
            });

            // Dengarkan event global dari tombol di halaman produk / pesanan
            window.addEventListener('open-chat', (e) => {
                const detail = e.detail || {};
                this.isOpen = true;
                if (detail.type === 'product') {
                    this.pendingContext = {
                        type: 'product',
                        id: detail.id,
                        title: detail.name || 'Produk Sewa'
                    };
                    this.messageText = 'Halo min, saya tertarik dengan alat ini. Apakah ready untuk disewa?';
                } else if (detail.type === 'order') {
                    this.pendingContext = {
                        type: 'order',
                        id: detail.id,
                        title: 'Pesanan #' + (detail.order_number || detail.id)
                    };
                    this.messageText = 'Halo min, saya ingin menanyakan perihal pesanan #' + (detail.order_number || detail.id);
                }

                if (config.isAuthenticated) {
                    this.loadActiveConversation();
                }
            });
        },

        toggleWidget() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                if (this.activeProduct && !this.pendingContext) {
                    this.pendingContext = {
                        type: 'product',
                        id: this.activeProduct.id,
                        title: this.activeProduct.name
                    };
                } else if (this.activeOrder && !this.pendingContext) {
                    this.pendingContext = {
                        type: 'order',
                        id: this.activeOrder.id,
                        title: 'Pesanan #' + this.activeOrder.order_number
                    };
                }
                if (config.isAuthenticated && (!this.conversation || this.unreadCount > 0)) {
                    this.loadActiveConversation();
                }
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async loadActiveConversation() {
            try {
                this.isLoading = true;
                const url = '/chat/conversation?client_id=' + config.clientId + '&subdomain=' + encodeURIComponent(config.clientSubdomain);
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;

                const data = await res.json();
                this.conversation = data.conversation;
                if (data.conversation?.store_name) this.storeName = data.conversation.store_name;
                if (data.conversation?.store_logo) this.storeLogo = data.conversation.store_logo;
                this.messages = data.messages || [];
                this.unreadCount = this.conversation.unread_by_customer || 0;

                this.bindEcho();
                this.$nextTick(() => this.scrollToBottom());
            } catch (err) {
                console.error('[RentalBase Chat] Load conversation error:', err);
            } finally {
                this.isLoading = false;
            }
        },

        bindEcho() {
            if (this.echoBound || !this.conversation) return;

            const echo = (typeof window.ensureCustomerEcho === 'function')
                ? window.ensureCustomerEcho()
                : (window.Echo && typeof window.Echo.private === 'function' ? window.Echo : null);

            if (!echo) return;

            const channelName = 'chat.conversation.' + this.conversation.id;
            console.log('[RentalBase Chat] Listening on Echo private channel:', channelName);

            echo.private(channelName)
                .listen('.chat.message', (e) => {
                    console.log('[RentalBase Chat] New message received:', e);
                    
                    // 1. Cek duplikasi ID real
                    if (this.messages.some(m => m.id === e.id)) return;

                    // 2. Cek apakah ini pesan kita sendiri yang masih berstatus temp_
                    const tempIdx = this.messages.findIndex(m => 
                        typeof m.id === 'string' && m.id.startsWith('temp_') && m.message === e.message
                    );
                    if (tempIdx !== -1) {
                        this.messages.splice(tempIdx, 1, e);
                        this.$nextTick(() => this.scrollToBottom());
                        return;
                    }

                    // 3. Tambahkan pesan baru
                    this.messages.push(e);

                    if (e.sender_role === 'admin') {
                        this.playChime();
                        if (!this.isOpen) {
                            this.unreadCount++;
                        } else {
                            this.markAsRead();
                        }
                    }

                    this.$nextTick(() => this.scrollToBottom());
                })
                .listen('.chat.read', (e) => {
                    console.log('[RentalBase Chat] Messages marked as read by admin:', e);
                    if (e.reader_role === 'admin') {
                        this.messages = this.messages.map(m => {
                            if (m.sender_role === 'customer') {
                                return { ...m, is_read: true };
                            }
                            return m;
                        });
                        this.$nextTick(() => this.scrollToBottom());
                    }
                });

            this.echoBound = true;
        },

        async sendMessage() {
            const text = this.messageText.trim();
            if (!text && !this.attachmentFile) return;

            const formData = new FormData();
            formData.append('message', text);
            formData.append('client_id', config.clientId);
            formData.append('subdomain', config.clientSubdomain);

            if (this.attachmentFile) {
                formData.append('attachment', this.attachmentFile);
            }

            if (this.pendingContext) {
                formData.append('context_type', this.pendingContext.type);
                formData.append('context_id', this.pendingContext.id);
            }

            // Optimistic message append
            const tempId = 'temp_' + Date.now();
            const tempMessage = {
                id: tempId,
                sender_role: 'customer',
                message: text,
                attachment_url: this.attachmentPreview,
                context_data: this.pendingContext ? {
                    type: this.pendingContext.type,
                    name: this.pendingContext.title,
                    order_number: this.pendingContext.title,
                    price: this.activeProduct?.price || '',
                    image: this.activeProduct?.image || '',
                    url: this.activeProduct?.url || '#'
                } : null,
                is_read: false,
                time_formatted: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':')
            };
            this.messages.push(tempMessage);

            this.messageText = '';
            this.removeAttachment();
            this.pendingContext = null;
            this.isSending = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    const realMessage = data.message;
                    if (realMessage) {
                        const alreadyExists = this.messages.some(m => m.id === realMessage.id);
                        const tempIdx = this.messages.findIndex(m => m.id === tempId);

                        if (alreadyExists) {
                            if (tempIdx !== -1) {
                                this.messages.splice(tempIdx, 1);
                            }
                        } else if (tempIdx !== -1) {
                            this.messages.splice(tempIdx, 1, realMessage);
                        } else {
                            this.messages.push(realMessage);
                        }
                    }
                }
            } catch (err) {
                console.error('[RentalBase Chat] Send message error:', err);
            } finally {
                this.isSending = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async markAsRead() {
            if (!this.conversation) return;
            try {
                this.unreadCount = 0;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                await fetch('/chat/read/' + this.conversation.id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    }
                });
            } catch (err) {}
        },

        useChip(text) {
            if (this.activeProduct && !this.pendingContext) {
                this.pendingContext = {
                    type: 'product',
                    id: this.activeProduct.id,
                    title: this.activeProduct.name
                };
            } else if (this.activeOrder && !this.pendingContext) {
                this.pendingContext = {
                    type: 'order',
                    id: this.activeOrder.id,
                    title: 'Pesanan #' + this.activeOrder.order_number
                };
            }
            this.messageText = text;
            this.sendMessage();
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.attachmentFile = file;
            const reader = new FileReader();
            reader.onload = (evt) => {
                this.attachmentPreview = evt.target.result;
            };
            reader.readAsDataURL(file);
        },

        removeAttachment() {
            this.attachmentFile = null;
            this.attachmentPreview = null;
        },

        scrollToBottom() {
            const container = document.getElementById('customer-chat-messages');
            if (container) {
                container.scrollTop = container.scrollHeight;
                setTimeout(() => {
                    if (container) container.scrollTop = container.scrollHeight;
                }, 50);
            }
        },

        playChime() {
            try {
                if (!window.__rbAudioCtx) {
                    window.__rbAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                const ctx = window.__rbAudioCtx;

                const executeChime = () => {
                    const now = ctx.currentTime;
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    // Nada cerah instan tanpa jeda: 880Hz langsung di 0ms -> 1174Hz di 80ms
                    osc.frequency.setValueAtTime(880, now);
                    osc.frequency.setValueAtTime(1174.66, now + 0.08);
                    gain.gain.setValueAtTime(0.38, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.38);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now);
                    osc.stop(now + 0.38);
                };

                if (ctx.state === 'suspended') {
                    ctx.resume().then(() => executeChime()).catch(() => executeChime());
                } else {
                    executeChime();
                }
            } catch (err) {}
        }
    };
}
</script>
