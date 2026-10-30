@extends('layouts.admin')

@section('title', 'Pusat Pemberitahuan Toko')
@section('page-title', 'Pemberitahuan Operasional')

@section('content')
<div class="space-y-6">

    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Pusat Pemberitahuan Toko</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Pantau peringatan transaksi masuk, verifikasi identitas, bukti bayar, dan operasional rental unit toko Anda.
            </p>
        </div>

        @if($countUnread > 0)
            <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-orange-50 text-orange-700 border border-orange-200/80 hover:bg-orange-100/80 hover:border-orange-300 transition shadow-2xs">
                    <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </form>
        @endif
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('admin.notifications.index', ['tab' => 'all']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition shrink-0
                  {{ $currentTab === 'all' ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20' : 'bg-white text-gray-600 hover:bg-stone-50 border border-gray-200/80' }}">
            <span>Semua</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentTab === 'all' ? 'bg-orange-600/80 text-white' : 'bg-stone-100 text-gray-500' }}">
                {{ $countAll }}
            </span>
        </a>

        <a href="{{ route('admin.notifications.index', ['tab' => 'unread']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition shrink-0
                  {{ $currentTab === 'unread' ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20' : 'bg-white text-gray-600 hover:bg-stone-50 border border-gray-200/80' }}">
            <span>Belum Dibaca</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentTab === 'unread' ? 'bg-orange-600/80 text-white' : 'bg-stone-100 text-gray-500' }}">
                {{ $countUnread }}
            </span>
        </a>

        <a href="{{ route('admin.notifications.index', ['tab' => 'orders']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition shrink-0
                  {{ $currentTab === 'orders' ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20' : 'bg-white text-gray-600 hover:bg-stone-50 border border-gray-200/80' }}">
            <span>Pesanan Baru</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentTab === 'orders' ? 'bg-orange-600/80 text-white' : 'bg-stone-100 text-gray-500' }}">
                {{ $countOrders }}
            </span>
        </a>

        <a href="{{ route('admin.notifications.index', ['tab' => 'verification']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition shrink-0
                  {{ $currentTab === 'verification' ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20' : 'bg-white text-gray-600 hover:bg-stone-50 border border-gray-200/80' }}">
            <span>Verifikasi KTP & Bayar</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentTab === 'verification' ? 'bg-orange-600/80 text-white' : 'bg-stone-100 text-gray-500' }}">
                {{ $countVerification }}
            </span>
        </a>

        <a href="{{ route('admin.notifications.index', ['tab' => 'returns']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition shrink-0
                  {{ $currentTab === 'returns' ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20' : 'bg-white text-gray-600 hover:bg-stone-50 border border-gray-200/80' }}">
            <span>Pengembalian & Kompensasi</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentTab === 'returns' ? 'bg-orange-600/80 text-white' : 'bg-stone-100 text-gray-500' }}">
                {{ $countReturns }}
            </span>
        </a>

        <a href="{{ route('admin.notifications.index', ['tab' => 'system']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition shrink-0
                  {{ $currentTab === 'system' ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/20' : 'bg-white text-gray-600 hover:bg-stone-50 border border-gray-200/80' }}">
            <span>Sistem Toko</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentTab === 'system' ? 'bg-orange-600/80 text-white' : 'bg-stone-100 text-gray-500' }}">
                {{ $countSystem }}
            </span>
        </a>
    </div>

    {{-- Notification List Feed --}}
    <div class="space-y-3">
        @forelse($notifications as $notif)
            <div class="p-4 sm:p-5 rounded-2xl border transition-all duration-150 {{ ! $notif->is_read ? 'bg-orange-50/20 border-orange-200/80 shadow-2xs' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    
                    {{-- Left side info --}}
                    <div class="flex items-start gap-3 min-w-0">
                        {{-- Dot status --}}
                        <div class="mt-1 shrink-0">
                            @if(! $notif->is_read)
                                <span class="flex h-2.5 w-2.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-orange-500"></span>
                                </span>
                            @else
                                <span class="inline-flex rounded-full h-2 w-2 bg-gray-300"></span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="space-y-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wider {{ $notif->type_badge['bg'] }}">
                                    {{ $notif->type_badge['label'] }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $notif->created_at ? $notif->created_at->diffForHumans() : '-' }}
                                </span>
                                @if(! $notif->is_read)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-700">
                                        Baru
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-sm font-bold text-gray-900 leading-snug">
                                {{ $notif->title }}
                            </h3>

                            <p class="text-xs text-gray-600 leading-relaxed max-w-3xl">
                                {{ $notif->message }}
                            </p>
                        </div>
                    </div>

                    {{-- Right side actions --}}
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center mt-2 sm:mt-0">
                        {{-- Buka Tindakan Deep Link --}}
                        <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $notif->admin_target_url }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-900 text-white hover:bg-orange-600 transition shadow-2xs">
                                <span>Buka Tindakan</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </form>

                        {{-- Tombol Tandai Dibaca (jika belum dibaca) --}}
                        @if(! $notif->is_read)
                            <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        title="Tandai sudah dibaca"
                                        class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition border border-gray-200">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-white rounded-2xl border border-gray-200/80 shadow-2xs">
                <div class="w-12 h-12 mx-auto rounded-full bg-stone-100 flex items-center justify-center text-gray-400 mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900">Belum Ada Pemberitahuan</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1 leading-relaxed">
                    Semua pembaruan terkait pesanan baru, bukti pembayaran, verifikasi KTP, dan pengembalian unit toko Anda akan muncul di sini.
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
