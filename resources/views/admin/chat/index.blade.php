@extends('layouts.admin')

@section('title', 'Chat Penyewa — ' . ($client->nama_usaha ?? 'Admin Toko'))
@section('page-title', 'Chat & Layanan Penyewa')

@section('content')
<div class="space-y-6 pb-12 font-sans">

    {{-- HEADER PAGE (Standar RentalBase) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Chat & Layanan Penyewa</h1>
            <p class="text-sm text-slate-500 mt-1">
                Komunikasi langsung dan tanggapi konsultasi ketersediaan barang serta info sewa pelanggan secara real-time.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-bold shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>Live Chat Aktif</span>
            </div>
        </div>
    </div>

    <!-- MAIN CHAT INBOX CONTAINER -->
    <div x-data="adminChatInbox({
            clientId: {{ $client->id }},
            activeConvId: {{ $activeConversation?->id ?? 'null' }},
            initialConversations: {{ json_encode($conversations->map(fn($c) => [
                'id' => $c->id,
                'customer_id' => $c->customer_id,
                'customer_name' => $c->customer?->name ?? 'Customer',
                'customer_email' => $c->customer?->email ?? '-',
                'customer_phone' => $c->customer?->phone ?? '-',
                'customer_avatar' => $c->customer?->avatar_url,
                'last_message_preview' => $c->last_message_preview ?: 'Belum ada pesan',
                'last_message_at' => $c->last_message_at ? $c->last_message_at->diffForHumans() : '',
                'unread_by_admin' => $c->unread_by_admin,
            ])) }}
         })"
         x-init="initInbox()"
         class="bg-white border border-gray-200/80 rounded-2xl shadow-sm overflow-hidden flex flex-col md:flex-row h-[calc(100vh-240px)] min-h-[560px] max-h-[760px]">

        <!-- PANEL KIRI: DAFTAR KONTAK & PERCAKAPAN (Width: 340px - 380px) -->
        <div class="w-full md:w-80 lg:w-96 border-r border-gray-100 flex flex-col shrink-0 bg-stone-50/40">
            
            <!-- Header Panel Kiri: Aligned at h-16 (64px) with Right Panel Header -->
            <div class="px-4 h-16 border-b border-gray-100 bg-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 leading-tight">Daftar Percakapan</h2>
                        <span class="text-[11px] text-gray-400" x-text="conversations.length + ' penyewa'"></span>
                    </div>
                    <span x-show="totalUnread > 0"
                          x-cloak
                          class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-500 text-white shadow-2xs ml-1"
                          x-text="totalUnread">
                    </span>
                </div>
                <button type="button"
                        @click="refreshConversations()"
                        class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition cursor-pointer"
                        title="Segarkan daftar">
                    <svg class="w-4 h-4" :class="isRefreshingList ? 'animate-spin text-orange-500' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>

            <!-- Subheader Panel Kiri: Search Bar & Filter Kapsul -->
            <div class="p-3 border-b border-gray-100 bg-stone-50/70 space-y-2 shrink-0">
                <!-- Search Bar -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari nama atau email..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-orange-500 text-gray-800 placeholder-gray-400 transition">
                </div>

                <!-- Filter Tab Kapsul -->
                <div class="flex items-center gap-1.5">
                    <button type="button"
                            @click="activeFilter = 'all'"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer"
                            :class="activeFilter === 'all' ? 'bg-orange-500 text-white shadow-2xs' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/60'">
                        Semua
                    </button>
                    <button type="button"
                            @click="activeFilter = 'unread'"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer flex items-center gap-1.5"
                            :class="activeFilter === 'unread' ? 'bg-orange-500 text-white shadow-2xs' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/60'">
                        <span>Belum Dibaca</span>
                        <span x-show="totalUnread > 0" class="w-1.5 h-1.5 rounded-full bg-orange-400" :class="activeFilter === 'unread' ? 'bg-white' : ''"></span>
                    </button>
                </div>
            </div>

        <!-- Daftar Percakapan List -->
        <div class="flex-1 overflow-y-auto divide-y divide-gray-100/80">
            <template x-for="conv in filteredConversations" :key="conv.id">
                <div @click="selectConversation(conv.id)"
                     class="p-3.5 transition-colors cursor-pointer flex items-start gap-3 relative group"
                     :class="activeConvId === conv.id 
                         ? 'bg-orange-50/80 border-l-4 border-orange-500' 
                         : 'hover:bg-gray-100/70 bg-transparent border-l-4 border-transparent'">
                    
                    <!-- Avatar Customer -->
                    <div class="relative shrink-0">
                        <template x-if="conv.customer_avatar">
                            <img :src="conv.customer_avatar" class="w-10 h-10 rounded-full object-cover bg-white border border-gray-200">
                        </template>
                        <template x-if="!conv.customer_avatar">
                            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 font-bold flex items-center justify-center text-xs"
                                 x-text="conv.customer_name.charAt(0)"></div>
                        </template>
                        <span x-show="conv.unread_by_admin > 0" class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-orange-500 border-2 border-white rounded-full"></span>
                    </div>

                    <!-- Info Obrolan -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <h4 class="text-xs font-bold text-gray-900 truncate"
                                :class="conv.unread_by_admin > 0 ? 'font-extrabold text-orange-950' : 'text-gray-800'"
                                x-text="conv.customer_name"></h4>
                            <span class="text-[10px] text-gray-400 shrink-0" x-text="conv.last_message_at"></span>
                        </div>
                        <p class="text-xs truncate text-gray-500"
                           :class="conv.unread_by_admin > 0 ? 'font-bold text-gray-900' : 'text-gray-500'"
                           x-text="conv.last_message_preview"></p>
                    </div>

                    <!-- Badge Unread -->
                    <span x-show="conv.unread_by_admin > 0"
                          class="shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-orange-500 text-white shadow-2xs"
                          x-text="conv.unread_by_admin"></span>
                </div>
            </template>

            <!-- Empty State Percakapan -->
            <div x-show="filteredConversations.length === 0" class="p-8 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-xs">Tidak ada obrolan yang cocok.</p>
            </div>
        </div>
    </div>

    <!-- PANEL KANAN: RUANG OBROLAN AKTIF & KONTEKS PESANAN -->
    <div class="flex-1 flex flex-col bg-white min-w-0">
        
        <!-- JIKA BELUM ADA PERCAKAPAN YANG TERPILIH -->
        <div x-show="!activeConvId" class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-gray-50/40">
            <div class="w-16 h-16 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center mb-3 shadow-2xs">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-800">Pusat Layanan Chat Toko</h3>
            <p class="text-xs text-gray-500 max-w-sm mt-1">
                Pilih salah satu penyewa di panel kiri untuk membuka riwayat pesan dan membalas konsultasi secara real-time.
            </p>
        </div>

        <!-- JIKA SUDAH ADA PERCAKAPAN AKTIF -->
        <div x-show="activeConvId" x-cloak class="flex-1 flex flex-col min-h-0">
            
            <!-- Header Obrolan Aktif -->
            <div class="px-5 sm:px-6 h-16 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="relative shrink-0">
                        <template x-if="activeCustomer?.avatar">
                            <img :src="activeCustomer.avatar" class="w-10 h-10 rounded-full object-cover bg-white border border-gray-200">
                        </template>
                        <template x-if="!activeCustomer?.avatar">
                            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 font-bold flex items-center justify-center text-sm"
                                 x-text="activeCustomer?.name?.charAt(0) || 'C'"></div>
                        </template>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900 truncate" x-text="activeCustomer?.name || 'Customer'"></h3>
                        <p class="text-xs text-gray-400 truncate flex items-center gap-2">
                            <span x-text="activeCustomer?.email || '-'"></span>
                            <span>&bull;</span>
                            <span x-text="activeCustomer?.phone || '-'"></span>
                        </p>
                    </div>
                </div>

                <!-- Tombol Segarkan Pesan -->
                <button type="button"
                        @click="loadActiveMessages()"
                        class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition cursor-pointer"
                        title="Segarkan pesan">
                    <svg class="w-4 h-4" :class="isLoadingMessages ? 'animate-spin text-orange-500' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>

            <!-- PINNED INFO: PESANAN AKTIF CUSTOMER DI TOKO INI -->
            <template x-if="activeOrders && activeOrders.length > 0">
                <div class="px-5 py-2.5 bg-amber-50/70 border-b border-amber-200/60 flex items-center justify-between gap-3 text-xs shrink-0 overflow-x-auto scrollbar-none">
                    <div class="flex items-center gap-2 shrink-0 text-amber-900 font-bold">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Pesanan Aktif Penyewa:</span>
                    </div>
                    <div class="flex items-center gap-2 overflow-x-auto">
                        <template x-for="ord in activeOrders" :key="ord.id">
                            <a :href="ord.detail_url" target="_blank"
                               class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-white border border-amber-200 text-gray-800 hover:border-amber-400 hover:shadow-2xs transition text-[11px] whitespace-nowrap">
                                <span class="font-bold font-mono" x-text="ord.order_number"></span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold" :class="ord.status_color" x-text="ord.status_label"></span>
                                <span class="text-gray-500" x-text="ord.total"></span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </template>
                    </div>
                </div>
            </template>

            <!-- STREAM PESAN CHAT -->
            <div id="admin-chat-messages-container"
                 class="flex-1 overflow-y-auto p-5 bg-stone-50/60 scroll-smooth">
                
                <div class="max-w-4xl mx-auto w-full space-y-4">
                    <template x-for="(m, idx) in activeMessages" :key="m.id || idx">
                        <div class="flex items-end gap-2.5" :class="m.sender_role === 'admin' ? 'justify-end' : 'justify-start'">
                            
                            <!-- Avatar Customer (Khusus Pesan dari Customer) -->
                            <template x-if="m.sender_role === 'customer'">
                                <div class="shrink-0 mb-0.5">
                                    <template x-if="activeCustomer?.avatar">
                                        <img :src="activeCustomer.avatar" :alt="activeCustomer.name" class="w-7 h-7 rounded-full object-cover bg-white ring-1 ring-gray-200 shadow-2xs">
                                    </template>
                                    <template x-if="!activeCustomer?.avatar">
                                        <div class="w-7 h-7 rounded-full bg-orange-100 text-orange-600 font-bold text-[10px] flex items-center justify-center ring-1 ring-orange-200 shadow-2xs"
                                             x-text="activeCustomer?.name?.charAt(0) || 'C'"></div>
                                    </template>
                                </div>
                            </template>

                            <div class="flex flex-col max-w-[75%]" :class="m.sender_role === 'admin' ? 'items-end' : 'items-start'">
                                
                                <!-- Header Nama Pengirim Jika Customer -->
                                <template x-if="m.sender_role === 'customer'">
                                    <div class="flex items-center gap-1.5 mb-1 ml-1">
                                        <span class="text-[11px] font-bold text-gray-700 leading-none" x-text="m.sender_name || activeCustomer?.name"></span>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-stone-100 text-stone-600 leading-none">Penyewa</span>
                                    </div>
                                </template>

                                <!-- Bubble Box -->
                                <div class="rounded-2xl px-4 py-2.5 shadow-2xs space-y-2 w-full"
                                     :class="m.sender_role === 'admin' 
                                         ? 'bg-orange-500 text-white rounded-br-xs shadow-orange-500/10' 
                                         : 'bg-white border border-gray-200/80 text-gray-800 rounded-bl-xs'">

                                    <!-- Sematan Kartu Konteks (Produk / Pesanan) -->
                                    <template x-if="m.context_data">
                                        <div class="p-2.5 rounded-xl border text-left"
                                             :class="m.sender_role === 'admin' ? 'bg-black/10 border-white/20 text-white' : 'bg-gray-50 border-gray-200 text-gray-900'">
                                            
                                            <!-- Produk Card -->
                                            <template x-if="m.context_data.type === 'product'">
                                                <a :href="m.context_data.url" target="_blank" class="flex items-center gap-3 group">
                                                    <img :src="m.context_data.image" class="w-11 h-11 rounded-lg object-cover bg-white shrink-0">
                                                    <div class="min-w-0">
                                                        <div class="text-[10px] uppercase font-bold opacity-80">Pertanyaan Produk</div>
                                                        <div class="text-xs font-bold truncate group-hover:underline" x-text="m.context_data.name"></div>
                                                        <div class="text-[11px] font-semibold opacity-90" x-text="m.context_data.price"></div>
                                                    </div>
                                                </a>
                                            </template>

                                            <!-- Pesanan Card -->
                                            <template x-if="m.context_data.type === 'order'">
                                                <div class="text-xs space-y-1">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="font-bold font-mono" x-text="m.context_data.order_number"></span>
                                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                                                              :class="m.context_data.status_color"
                                                              x-text="m.context_data.status_label"></span>
                                                    </div>
                                                    <div class="text-[11px] opacity-85" x-text="'Total Sewa: ' + m.context_data.total"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Lampiran Gambar -->
                                    <template x-if="m.attachment_url">
                                        <div class="rounded-xl overflow-hidden cursor-pointer" @click="window.open(m.attachment_url, '_blank')">
                                            <img :src="m.attachment_url" alt="Lampiran" class="max-h-56 w-full object-cover rounded-xl hover:opacity-95 transition">
                                        </div>
                                    </template>

                                    <!-- Teks Pesan -->
                                    <p class="text-xs leading-relaxed whitespace-pre-line break-words" x-text="m.message"></p>

                                    <!-- Jam & Status Baca -->
                                    <div class="flex items-center justify-end gap-1.5 text-[10px] pt-0.5 select-none"
                                         :class="m.sender_role === 'admin' ? 'text-white/80' : 'text-gray-400'">
                                        <span x-text="m.time_formatted"></span>
                                        
                                        <!-- Centang Khusus Admin Pengirim -->
                                        <template x-if="m.sender_role === 'admin'">
                                            <span class="inline-flex items-center ml-0.5">
                                                <!-- Centang Dua (Dibaca) -->
                                                <template x-if="m.is_read">
                                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Dibaca oleh penyewa">
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

                    <!-- Loading Indicator Pengiriman -->
                    <div x-show="isSending" x-cloak class="flex items-center gap-2 text-xs text-gray-400 italic">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-bounce"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-bounce [animation-delay:0.4s]"></span>
                        <span>Mengirim balasan...</span>
                    </div>
                </div>
            </div>

            <!-- Pending Lampiran Foto Box (Sebelum Dikirim) -->
            <div x-show="attachmentPreview" x-cloak class="px-5 py-2.5 bg-gray-100 border-t border-gray-200 shrink-0">
                <div class="max-w-4xl mx-auto w-full flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <img :src="attachmentPreview" class="w-9 h-9 rounded-lg object-cover border border-gray-300">
                        <span class="text-xs text-gray-600 truncate">Foto siap dikirim sebagai lampiran</span>
                    </div>
                    <button type="button" @click="removeAttachment()" class="text-gray-400 hover:text-gray-700 p-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Quick Replies Chips -->
            <div class="px-4 py-2 bg-stone-50/70 border-t border-gray-100 flex items-center gap-2 overflow-x-auto scrollbar-none shrink-0">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider shrink-0 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Pesan Cepat:
                </span>
                <button type="button" @click="insertQuickReply('Halo kak, unit barang masih tersedia dan siap disewa!')"
                        class="px-2.5 py-1 rounded-lg text-xs bg-white hover:bg-orange-50 hover:text-orange-600 hover:border-orange-200 border border-gray-200 text-gray-600 transition shrink-0 cursor-pointer">
                    Unit Ready
                </button>
                <button type="button" @click="insertQuickReply('Bisa langsung checkout via website dengan melampirkan foto KTP fisik ya kak.')"
                        class="px-2.5 py-1 rounded-lg text-xs bg-white hover:bg-orange-50 hover:text-orange-600 hover:border-orange-200 border border-gray-200 text-gray-600 transition shrink-0 cursor-pointer">
                    Syarat Checkout
                </button>
                <button type="button" @click="insertQuickReply('Pengambilan unit bisa diambil langsung ke toko rental kami atau dikirim kurir.')"
                        class="px-2.5 py-1 rounded-lg text-xs bg-white hover:bg-orange-50 hover:text-orange-600 hover:border-orange-200 border border-gray-200 text-gray-600 transition shrink-0 cursor-pointer">
                    Pengambilan Unit
                </button>
            </div>

            <!-- Input Bar Form Balasan Admin -->
            <form @submit.prevent="sendMessage()" class="p-3.5 bg-white border-t border-gray-100 shrink-0">
                <div class="max-w-4xl mx-auto w-full flex items-end gap-2.5">
                    <!-- Unggah Foto -->
                    <label class="p-2.5 text-gray-400 hover:text-orange-600 rounded-xl hover:bg-stone-50 transition cursor-pointer shrink-0" title="Kirim gambar">
                        <input type="file" accept="image/*" class="hidden" @change="handleFileSelect($event)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </label>

                    <!-- Input Pesan -->
                    <div class="flex-1 min-w-0">
                        <textarea x-model="messageText"
                                  @keydown.enter.exact.prevent="sendMessage()"
                                  placeholder="Balas pesan penyewa... (Tekan Enter untuk kirim)"
                                  rows="1"
                                  class="w-full text-xs sm:text-sm text-gray-800 placeholder-gray-400 bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:bg-white resize-none max-h-28"></textarea>
                    </div>

                    <!-- Tombol Kirim -->
                    <button type="submit"
                            :disabled="isSending || (!messageText.trim() && !attachmentFile)"
                            class="px-4 py-2.5 rounded-xl font-bold text-white bg-orange-500 hover:bg-orange-600 transition disabled:opacity-40 disabled:cursor-not-allowed shrink-0 shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <span class="text-xs">Kirim</span>
                        <svg class="w-3.5 h-3.5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
</div>

<script>
function adminChatInbox(config) {
    return {
        conversations: config.initialConversations || [],
        activeConvId: config.activeConvId,
        activeCustomer: null,
        activeMessages: [],
        activeOrders: [],
        searchQuery: '',
        activeFilter: 'all', // 'all' | 'unread'
        messageText: '',
        attachmentFile: null,
        attachmentPreview: null,
        isRefreshingList: false,
        isLoadingMessages: false,
        isSending: false,

        insertQuickReply(text) {
            this.messageText = (this.messageText ? this.messageText + ' ' : '') + text;
        },
        currentEchoChannel: null,

        get totalUnread() {
            return this.conversations.reduce((sum, c) => sum + (c.unread_by_admin || 0), 0);
        },

        get filteredConversations() {
            return this.conversations.filter(c => {
                if (this.activeFilter === 'unread' && c.unread_by_admin <= 0) return false;
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();
                return (c.customer_name && c.customer_name.toLowerCase().includes(q)) ||
                       (c.customer_email && c.customer_email.toLowerCase().includes(q));
            });
        },

        initInbox() {
            if (this.activeConvId) {
                this.selectConversation(this.activeConvId);
            }

            // Dengarkan event broadcast baru dari listener layout global
            window.addEventListener('admin-new-chat-message', (e) => {
                const msg = e.detail;
                this.handleIncomingGlobalMessage(msg);
            });
        },

        async selectConversation(convId) {
            this.activeConvId = convId;
            const conv = this.conversations.find(c => c.id === convId);
            if (conv) {
                this.activeCustomer = {
                    name: conv.customer_name,
                    email: conv.customer_email,
                    phone: conv.customer_phone,
                    avatar: conv.customer_avatar
                };
                conv.unread_by_admin = 0;
            }

            await this.loadActiveMessages();
            this.bindEchoChannel();
        },

        async loadActiveMessages() {
            if (!this.activeConvId) return;
            try {
                this.isLoadingMessages = true;
                const res = await fetch(`/admin/chat/conversations/${this.activeConvId}/messages`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;

                const data = await res.json();
                this.activeMessages = data.messages || [];
                this.activeOrders = data.active_orders || [];
                if (data.conversation) {
                    this.activeCustomer = {
                        name: data.conversation.customer_name,
                        email: data.conversation.customer_email,
                        phone: data.conversation.customer_phone,
                        avatar: data.conversation.customer_avatar
                    };
                }

                // Update unread counter in list
                const c = this.conversations.find(x => x.id === this.activeConvId);
                if (c) c.unread_by_admin = 0;

                this.$nextTick(() => this.scrollToBottom());
            } catch (err) {
                console.error('[RentalBase Admin Chat] Load messages error:', err);
            } finally {
                this.isLoadingMessages = false;
            }
        },

        bindEchoChannel() {
            if (!this.activeConvId || !window.Echo) return;

            // Lepas channel lama jika ada
            if (this.currentEchoChannel) {
                window.Echo.leave(this.currentEchoChannel);
            }

            this.currentEchoChannel = 'chat.conversation.' + this.activeConvId;
            console.log('[RentalBase Admin Chat] Listening on Echo:', this.currentEchoChannel);

            window.Echo.private(this.currentEchoChannel)
                .listen('.chat.message', (e) => {
                    console.log('[RentalBase Admin Chat] Message received:', e);
                    if (this.activeMessages.some(m => m.id === e.id)) return;

                    // Cek jika ini pesan temp kita sendiri
                    const tempIdx = this.activeMessages.findIndex(m => 
                        typeof m.id === 'string' && m.id.startsWith('temp_') && m.message === e.message
                    );
                    if (tempIdx !== -1) {
                        this.activeMessages.splice(tempIdx, 1, e);
                        this.$nextTick(() => this.scrollToBottom());
                        return;
                    }

                    this.activeMessages.push(e);

                    if (e.sender_role === 'customer') {
                        if (document.hidden) {
                            this.playChime();
                        }
                        this.markActiveAsRead();
                    }

                    // Update list preview
                    const conv = this.conversations.find(c => Number(c.id) === Number(this.activeConvId));
                    if (conv) {
                        conv.last_message_preview = e.message;
                        conv.last_message_at = 'Baru saja';
                    }

                    this.$nextTick(() => this.scrollToBottom());
                })
                .listen('.chat.read', (e) => {
                    console.log('[RentalBase Admin Chat] Read event received:', e);
                    if (e.reader_role === 'customer') {
                        this.activeMessages.forEach(m => {
                            if (m.sender_role === 'admin') {
                                m.is_read = true;
                            }
                        });
                    }
                });
        },

        handleIncomingGlobalMessage(msg) {
            // Update preview di kontak terkait
            const conv = this.conversations.find(c => Number(c.id) === Number(msg.conversation_id));
            if (conv) {
                conv.last_message_preview = msg.message;
                conv.last_message_at = 'Baru saja';
                if (Number(this.activeConvId) !== Number(msg.conversation_id)) {
                    conv.unread_by_admin = (conv.unread_by_admin || 0) + 1;
                    this.playChime();
                } else {
                    conv.unread_by_admin = 0;
                }
            } else {
                // Percakapan baru belum ada di list -> refresh
                this.refreshConversations();
            }
        },

        async sendMessage() {
            const text = this.messageText.trim();
            if ((!text && !this.attachmentFile) || !this.activeConvId) return;

            const formData = new FormData();
            formData.append('message', text);

            if (this.attachmentFile) {
                formData.append('attachment', this.attachmentFile);
            }

            // Optimistic message append
            const tempId = 'temp_' + Date.now();
            const tempMessage = {
                id: tempId,
                sender_role: 'admin',
                message: text,
                attachment_url: this.attachmentPreview,
                is_read: false,
                time_formatted: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':')
            };
            this.activeMessages.push(tempMessage);

            this.messageText = '';
            this.removeAttachment();
            this.isSending = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/admin/chat/conversations/${this.activeConvId}/send`, {
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
                        const alreadyExists = this.activeMessages.some(m => m.id === realMessage.id);
                        const tempIdx = this.activeMessages.findIndex(m => m.id === tempId);

                        if (alreadyExists) {
                            if (tempIdx !== -1) {
                                this.activeMessages.splice(tempIdx, 1);
                            }
                        } else if (tempIdx !== -1) {
                            this.activeMessages.splice(tempIdx, 1, realMessage);
                        } else {
                            this.activeMessages.push(realMessage);
                        }
                    }

                    const conv = this.conversations.find(c => Number(c.id) === Number(this.activeConvId));
                    if (conv) {
                        conv.last_message_preview = text;
                        conv.last_message_at = 'Baru saja';
                    }
                }
            } catch (err) {
                console.error('[RentalBase Admin Chat] Send error:', err);
            } finally {
                this.isSending = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async markActiveAsRead() {
            if (!this.activeConvId) return;
            const conv = this.conversations.find(c => Number(c.id) === Number(this.activeConvId));
            if (conv) {
                const prev = conv.unread_by_admin || 0;
                conv.unread_by_admin = 0;
                window.dispatchEvent(new CustomEvent('admin-chat-read', { detail: { count: prev } }));
            }
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                await fetch(`/admin/chat/conversations/${this.activeConvId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    }
                });
            } catch (err) {}
        },

        async refreshConversations() {
            try {
                this.isRefreshingList = true;
                const res = await fetch('/admin/chat/conversations', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                this.conversations = data.conversations || [];
            } catch (err) {
            } finally {
                this.isRefreshingList = false;
            }
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
            const container = document.getElementById('admin-chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
                setTimeout(() => {
                    if (container) container.scrollTop = container.scrollHeight;
                }, 50);
            }
        },

        playChime() {
            try {
                if (typeof unlockAudio === 'function') unlockAudio();
                if (!window.__rbAudioCtx) {
                    window.__rbAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                const ctx = window.__rbAudioCtx;

                const executeChime = () => {
                    const now = ctx.currentTime;
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    // Nada cerah instan (A5 -> D6) tanpa delay: 880Hz langsung di 0ms, lalu 1174Hz di 80ms
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
                    let allowed = true;
                    const timer = setTimeout(() => { allowed = false; }, 60);
                    ctx.resume().then(() => {
                        clearTimeout(timer);
                        if (allowed) executeChime();
                    }).catch(() => {});
                } else {
                    executeChime();
                }
            } catch (err) {
                console.warn('[RentalBase] Chime error:', err);
            }
        }
    };
}
</script>
@endsection
