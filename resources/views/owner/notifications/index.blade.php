@extends('layouts.owner')

@section('title', 'Pusat Notifikasi Platform — RentalBase')
@section('page-title', 'Pusat Notifikasi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-xs" role="alert">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs" role="alert">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('owner.dashboard') }}" class="hover:text-orange-600 transition-colors">Dashboard Owner</a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-800 font-bold">Pusat Notifikasi</span>
    </nav>

    {{-- Header Section --}}
    <div class="bg-white rounded-2xl p-6 lg:p-8 border border-orange-200/70 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6"
         style="background: linear-gradient(135deg, #FFFFFF 0%, #FFF7ED 100%);">
        <div class="space-y-1.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-md"
                     style="background: linear-gradient(135deg, #F97316 0%, #EA580C 100%); box-shadow: 0 4px 12px rgba(249,115,22,0.25);">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Notifikasi Platform</h1>
                    <p class="text-xs text-slate-500 font-medium">Pantau pendaftaran tenant baru, perpanjangan subscription, dan peringatan kuota platform secara terpusat.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            @if($countUnread > 0)
                <form action="{{ route('owner.notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-orange-700 bg-orange-100 hover:bg-orange-200 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Tandai Semua Sudah Dibaca ({{ $countUnread }})</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Tabs Filter --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200">
        <a href="{{ route('owner.notifications.index', ['tab' => 'all']) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                  {{ $currentTab === 'all' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-orange-50 hover:text-orange-600' }}">
            <span>Semua</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $currentTab === 'all' ? 'bg-orange-700 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $countAll }}
            </span>
        </a>

        <a href="{{ route('owner.notifications.index', ['tab' => 'unread']) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                  {{ $currentTab === 'unread' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-orange-50 hover:text-orange-600' }}">
            <span>Belum Dibaca</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $currentTab === 'unread' ? 'bg-orange-700 text-white' : 'bg-orange-100 text-orange-700' }}">
                {{ $countUnread }}
            </span>
        </a>

        <a href="{{ route('owner.notifications.index', ['tab' => 'alert']) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                  {{ $currentTab === 'alert' ? 'bg-orange-600 text-white shadow-xs' : 'text-slate-600 hover:bg-orange-50 hover:text-orange-600' }}">
            <span>Kategori Alert</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $currentTab === 'alert' ? 'bg-orange-700 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $countAlert }}
            </span>
        </a>
    </div>

    {{-- Notification List --}}
    <div class="space-y-3">
        @forelse($notifications as $item)
            @php
                $badge = $item->type_badge;
                $targetUrl = $item->owner_target_url;
            @endphp
            <div class="relative bg-white rounded-2xl p-5 border transition-all duration-150 shadow-xs
                        {{ ! $item->is_read ? 'border-orange-300 bg-orange-50/20 ring-1 ring-orange-200' : 'border-slate-200/80 hover:border-slate-300' }}">

                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5 flex-1 min-w-0">
                        {{-- Icon / Indicator --}}
                        <div class="shrink-0 mt-0.5">
                            @if(! $item->is_read)
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-600"></span>
                                </span>
                            @else
                                <span class="inline-flex rounded-full h-2.5 w-2.5 bg-slate-300"></span>
                            @endif
                        </div>

                        {{-- Main Content --}}
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Badge Kategori --}}
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['bg'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    <span>{{ $badge['label'] }}</span>
                                </span>

                                {{-- Tenant Tag (jika terhubung ke Client) --}}
                                @if($item->client)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $item->client->business_name }}
                                    </span>
                                @endif

                                {{-- Waktu --}}
                                <span class="text-[11px] text-slate-400 font-medium">
                                    {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }})
                                </span>
                            </div>

                            <h3 class="text-sm font-bold text-slate-900 leading-snug">
                                {{ $item->title }}
                            </h3>

                            <p class="text-xs text-slate-600 leading-relaxed">
                                {{ $item->message }}
                            </p>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                        @if(! $item->is_read)
                            <form action="{{ route('owner.notifications.read', $item) }}" method="POST">
                                @csrf
                                <input type="hidden" name="redirect_to" value="{{ $targetUrl }}">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-orange-600 hover:bg-orange-700 transition-colors shadow-xs">
                                    <span>Buka & Tindak Lanjuti</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ $targetUrl }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                                <span>Lihat Halaman</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-xs space-y-4">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-orange-100 flex items-center justify-center text-orange-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-slate-900">Tidak Ada Notifikasi</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        @if($currentTab === 'unread')
                            Semua notifikasi penting platform telah Anda baca.
                        @elseif($currentTab === 'alert')
                            Belum ada peringatan atau tindakan kritis yang memerlukan penanganan saat ini.
                        @else
                            Saat ini belum ada notifikasi platform baru yang masuk.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection
