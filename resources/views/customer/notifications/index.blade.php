@extends('layouts.customer')

@section('title', 'Pusat Pemberitahuan — ' . ($tenantProfile?->name ?? $client->nama_usaha ?? 'RentalBase'))

@section('content')
<div class="max-w-[1000px] mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb & Judul --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-400 mb-1.5">
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? 'babyrental']) }}" class="hover:text-primary-500 transition">Beranda</a>
                <span>/</span>
                <span class="text-gray-700 font-semibold">Pemberitahuan</span>
            </nav>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span>Pusat Pemberitahuan</span>
                @if($totalUnread > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 border border-orange-200">
                        {{ $totalUnread }} baru
                    </span>
                @endif
            </h1>
            <p class="text-xs text-gray-500 mt-1">Pantau perkembangan status sewa, verifikasi KTP, pembayaran, dan pengiriman barang kamu.</p>
        </div>

        {{-- Tombol Tandai Semua Dibaca --}}
        @if($totalUnread > 0)
            <form action="{{ route('customer.notifications.mark-all-read') }}" method="POST">
                @csrf
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

    {{-- Tab Filter Kategori --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6 border-b border-gray-100">
        <a href="{{ route('customer.notifications.index', ['filter' => 'all']) }}"
           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition shrink-0 {{ $filter === 'all' ? 'bg-gray-900 text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
            Semua ({{ $totalAll }})
        </a>
        <a href="{{ route('customer.notifications.index', ['filter' => 'unread']) }}"
           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition shrink-0 {{ $filter === 'unread' ? 'bg-gray-900 text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
            Belum Dibaca ({{ $totalUnread }})
        </a>
        <a href="{{ route('customer.notifications.index', ['filter' => 'orders']) }}"
           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition shrink-0 {{ $filter === 'orders' ? 'bg-gray-900 text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
            Pesanan &amp; Verifikasi
        </a>
        <a href="{{ route('customer.notifications.index', ['filter' => 'logistics']) }}"
           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition shrink-0 {{ $filter === 'logistics' ? 'bg-gray-900 text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
            Pengiriman &amp; Pengembalian
        </a>
    </div>

    {{-- Daftar Feed Notifikasi --}}
    <div class="space-y-3">
        @forelse($notifications as $notif)
            @php
                $badge = $notif->type_badge;
                $isUnread = ! $notif->is_read;
            @endphp
            <div class="relative group p-4 sm:p-5 rounded-2xl border transition-all duration-200 {{ $isUnread ? 'bg-white border-orange-200 shadow-sm hover:border-orange-300' : 'bg-gray-50/60 border-gray-200/80 hover:bg-white hover:border-gray-300' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        {{-- Dot Indikator Unread --}}
                        <div class="mt-1 shrink-0">
                            @if($isUnread)
                                <span class="block w-2.5 h-2.5 rounded-full bg-orange-500 ring-4 ring-orange-100"></span>
                            @else
                                <span class="block w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                            @endif
                        </div>

                        <div>
                            {{-- Badge Tipe & Waktu --}}
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $badge['bg'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $notif->created_at->diffForHumans() }}
                                </span>
                            </div>

                            {{-- Judul & Pesan --}}
                            <h3 class="text-sm font-bold {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }}">
                                {{ $notif->title }}
                            </h3>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                {{ $notif->message }}
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Aksi Tandai Baca --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if($isUnread)
                            <form action="{{ route('customer.notifications.read', $notif->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-semibold transition cursor-pointer"
                                        title="Tandai sudah dibaca">
                                    Tandai Dibaca
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('customer.orders') }}"
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
                        Semua pemberitahuan sudah kamu baca.
                    @else
                        Saat ini belum ada riwayat pemberitahuan untuk akun kamu.
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
