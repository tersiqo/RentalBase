@extends('layouts.customer')

@section('title', 'Pusat Pemberitahuan — ' . ($tenantProfile?->name ?? $client->nama_usaha ?? 'RentalBase'))

@section('content')
<div class="max-w-[1000px] mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb & Judul --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-400 mb-1.5">
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? session('active_client_subdomain', 'babyrental')]) }}" class="hover:text-primary-500 transition">Beranda</a>
                <span>/</span>
                <span class="text-gray-700 font-semibold">Pemberitahuan</span>
            </nav>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span>Pusat Pemberitahuan</span>
                @if($totalUnread > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
                          style="background-color: var(--tenant-theme-50, #fff7ed); color: var(--tenant-theme-700, var(--tenant-theme, #c2410c)); border-color: var(--tenant-theme-200, #fed7aa);">
                        {{ $totalUnread }} baru
                    </span>
                @endif
            </h1>
            <p class="text-xs text-gray-500 mt-1">Pantau perkembangan status sewa, verifikasi KTP, pembayaran, dan pengiriman barang kamu di {{ $client->business_name ?? $client->nama_usaha ?? 'toko ini' }}.</p>
        </div>

        {{-- Tombol Tandai Semua Dibaca --}}
        @if($totalUnread > 0)
            <form action="{{ route('customer.notifications.mark-all-read') }}" method="POST">
                @csrf
                @if(isset($client) && $client?->id)
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                @endif
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </form>
        @endif
    </div>

    {{-- Alert Notifikasi Sukses --}}
    @if(session('success'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-medium flex items-center gap-2 shadow-2xs">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Web Push OS Device Notification Card (Customer) --}}
    <div x-data="rbWebPush()" x-init="init()" class="mb-6 p-4 rounded-2xl bg-white border border-gray-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                     :class="isSubscribed ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-600'">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs font-bold text-gray-900 truncate" x-text="isSubscribed ? 'Notifikasi Perangkat OS: Aktif' : 'Terima Notifikasi Saat Web Ditutup'"></h4>
                    <p class="text-[11px] text-gray-500 mt-0.5 truncate" x-text="isSubscribed ? 'Status pesanan & balasan chat toko akan muncul di layar perangkat Anda.' : 'Aktifkan agar Anda tidak ketinggalan konfirmasi sewa dan balasan toko.'"></p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                <button type="button"
                        x-show="isSubscribed"
                        @click="sendTestNotification()"
                        :disabled="isLoading"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer"
                        title="Kirim notifikasi uji coba ke layar Anda">
                    Uji Coba
                </button>
                <button type="button"
                        @click="toggleSubscription()"
                        :disabled="isLoading"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs"
                        :class="isSubscribed 
                            ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' 
                            : 'bg-primary-500 text-white hover:opacity-90'"
                        :style="!isSubscribed ? 'background-color: var(--tenant-theme, #f97316);' : ''">
                    <span x-show="!isLoading" x-text="isSubscribed ? 'Aktif ✓' : 'Aktifkan'"></span>
                    <span x-show="isLoading" class="animate-spin inline-block w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full"></span>
                </button>
            </div>
        </div>
        <p x-show="feedbackMessage" x-text="feedbackMessage" class="text-xs text-orange-600 mt-2 font-medium"></p>
    </div>

    {{-- Tab Filter Kategori --}}
    @php
        $activeSubdomain = $client->subdomain ?? session('active_client_subdomain', null);
        $tabUrl = fn($f) => $activeSubdomain 
            ? route('customer.notifications.tenant', ['subdomain' => $activeSubdomain, 'filter' => $f])
            : route('customer.notifications.index', ['filter' => $f]);
    @endphp
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6 border-b border-gray-100">
        <a href="{{ $tabUrl('all') }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'all' ? 'bg-primary-500 text-white shadow-xs shadow-primary-500/25' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
           @if($filter === 'all') style="background-color: var(--tenant-theme, #f97316);" @endif>
            Semua ({{ $totalAll }})
        </a>
        <a href="{{ $tabUrl('unread') }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'unread' ? 'bg-primary-500 text-white shadow-xs shadow-primary-500/25' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
           @if($filter === 'unread') style="background-color: var(--tenant-theme, #f97316);" @endif>
            Belum Dibaca ({{ $totalUnread }})
        </a>
        <a href="{{ $tabUrl('orders') }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'orders' ? 'bg-primary-500 text-white shadow-xs shadow-primary-500/25' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
           @if($filter === 'orders') style="background-color: var(--tenant-theme, #f97316);" @endif>
            Pesanan &amp; Verifikasi
        </a>
        <a href="{{ $tabUrl('logistics') }}"
           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'logistics' ? 'bg-primary-500 text-white shadow-xs shadow-primary-500/25' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
           @if($filter === 'logistics') style="background-color: var(--tenant-theme, #f97316);" @endif>
            Pengiriman &amp; Pengembalian
        </a>
    </div>

    {{-- Daftar Feed Notifikasi --}}
    <div class="space-y-3">
        @forelse($notifications as $notif)
            @php
                $badge = $notif->type_badge;
                $isUnread = ! $notif->is_read;
                $targetUrl = $notif->customer_target_url;
                $isLateFee = ($notif->type === 'late_fee')
                    || str_contains(strtolower($notif->title . ' ' . $notif->message), 'denda')
                    || str_contains(strtolower($notif->title . ' ' . $notif->message), 'keterlambatan');
            @endphp
            <div class="relative group p-4 sm:p-5 rounded-2xl border transition-all duration-200 {{ $isUnread ? 'bg-white shadow-xs' : 'bg-gray-50/60 border-gray-200/80 hover:bg-white hover:border-gray-300' }}"
                 @if($isUnread) style="border-color: var(--tenant-theme-200, #fed7aa);" @endif>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        {{-- Dot Indikator Unread --}}
                        <div class="mt-1 shrink-0">
                            @if($isUnread)
                                <span class="block w-2.5 h-2.5 rounded-full"
                                      style="background-color: var(--tenant-theme, #f97316); box-shadow: 0 0 0 4px var(--tenant-theme-100, #ffedd5);"></span>
                            @else
                                <span class="block w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                            @endif
                        </div>

                        <div>
                            {{-- Badge Tipe & Waktu --}}
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $badge['bg'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $notif->created_at->diffForHumans() }}
                                </span>
                            </div>

                            {{-- Judul & Pesan --}}
                            <h3 class="text-sm font-bold {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }}">
                                <a href="{{ $targetUrl }}" class="hover:underline">
                                    {{ $notif->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                {{ $notif->message }}
                            </p>

                            {{-- CTA Bayar Denda Cepat --}}
                            @if($isLateFee && $isUnread)
                                <div class="mt-2.5">
                                    <a href="{{ $targetUrl }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition shadow-2xs">
                                        <span>Bayar Denda Sekarang</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tombol Aksi Tandai Baca --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if($isUnread)
                            <form action="{{ route('customer.notifications.read', $notif->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer border"
                                        style="background-color: var(--tenant-theme-50, #fff7ed); color: var(--tenant-theme-700, var(--tenant-theme, #c2410c)); border-color: var(--tenant-theme-200, #fed7aa);"
                                        title="Tandai sudah dibaca">
                                    Tandai Dibaca
                                </button>
                            </form>
                        @endif

                        <a href="{{ $targetUrl }}"
                           class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                           title="Lihat Pesanan Saya">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="py-16 text-center bg-white rounded-2xl border border-gray-100 shadow-2xs p-8">
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-50 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-800">Tidak ada pemberitahuan</h3>
                <p class="text-xs text-gray-400 max-w-sm mx-auto mt-1">
                    @if($filter === 'unread')
                        Semua pemberitahuan di toko ini sudah kamu baca.
                    @else
                        Saat ini belum ada riwayat pemberitahuan untuk akun kamu di toko {{ $client->business_name ?? $client->nama_usaha ?? 'ini' }}.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection
