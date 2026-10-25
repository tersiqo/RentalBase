@extends('layouts.owner')

@section('title', 'Pendaftaran Baru — RentalBase Platform')
@section('page-title', 'Pendaftaran Baru')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    showModal: false,
    activeTenant: null,
    showRejectModal: false,
    rejectId: null,
    rejectName: '',
    rejectUrl: '',
    showVerifyModal: false,
    verifyId: null,
    verifyName: '',
    verifyUrl: '',

    normalizeWhatsAppNumber(phone) {
        if (!phone || typeof phone !== 'string') return null;
        let cleaned = phone.trim();
        if (!cleaned || cleaned === '-') return null;

        // Hilangkan format tanda tambah di awal bila ada
        if (cleaned.startsWith('+')) {
            cleaned = cleaned.substring(1);
        }

        // Hilangkan spasi, tanda hubung, tanda kurung, dan karakter pemformatan non-digit lainnya
        cleaned = cleaned.replace(/\D/g, '');

        // Normalisasi nomor Indonesia: ubah awalan 08... atau 8... menjadi 628...
        if (cleaned.startsWith('08')) {
            cleaned = '628' + cleaned.substring(2);
        } else if (cleaned.startsWith('8') && cleaned.length >= 9 && cleaned.length <= 13) {
            cleaned = '628' + cleaned.substring(1);
        }

        // Validasi:
        // Standar nomor WhatsApp internasional E.164 (panjang 9 - 15 digit)
        // Tidak boleh diawali angka 0 (karena nomor WA internasional membutuhkan kode negara)
        if (cleaned.length < 9 || cleaned.length > 15 || cleaned.startsWith('0')) {
            return null;
        }

        // Hindari nomor dummy/berulang (misal: 0000000000, 1111111111)
        if (/^(\d)\1+$/.test(cleaned)) {
            return null;
        }

        return cleaned;
    },

    getWhatsAppLink(tenant) {
        if (!tenant || !tenant.admin_phone) return null;
        const normalized = this.normalizeWhatsAppNumber(tenant.admin_phone);
        if (!normalized) return null;

        const adminName = (tenant.admin_name || '').trim();
        const businessName = (tenant.business_name || '').trim();

        // Pesan pembuka percakapan yang sopan untuk menanyakan kelengkapan pengajuan
        let message = 'Halo';
        if (adminName) {
            message += ' Bapak/Ibu ' + adminName;
        }
        message += ', saya dari tim RentalBase terkait pengajuan pendaftaran';
        if (businessName) {
            message += ' toko *' + businessName + '*';
        } else {
            message += ' toko Anda';
        }
        message += '. Kami ingin mengonfirmasi perihal kelengkapan data pengajuan Anda. Apakah ada berkas atau informasi yang perlu kami bantu lengkapi? Terima kasih.';

        return 'https://wa.me/' + normalized + '?text=' + encodeURIComponent(message);
    }
}">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm shadow-sm">
            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('info') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm shadow-sm">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium">{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200/60">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.dashboard') }}" class="text-xs text-orange-600 hover:underline flex items-center gap-1 font-semibold">
                    &larr; Dashboard
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Pendaftaran Tenant</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight sm:text-3xl mt-1">
                Kelola Pendaftaran Baru Tenant
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Verifikasi pengajuan toko rental baru, periksa bukti transfer pembayaran, dan setujui operasional toko.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                {{ $counts['menunggu'] }} Menunggu Verifikasi
            </span>
        </div>
    </div>

    {{-- Filter Tabs & Search Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Status Tabs --}}
        <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200/60 self-start sm:self-auto overflow-x-auto max-w-full">
            <a href="{{ route('owner.registrations.index', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Semua ({{ $counts['total'] }})
            </a>    
            <a href="{{ route('owner.registrations.index', array_merge(request()->query(), ['status' => 'menunggu_verifikasi', 'page' => 1])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'menunggu_verifikasi' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Menunggu Verifikasi ({{ $counts['menunggu'] }})
            </a>
            <a href="{{ route('owner.registrations.index', array_merge(request()->query(), ['status' => 'disetujui', 'page' => 1])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'disetujui' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Disetujui ({{ $counts['disetujui'] }})
            </a>
            <a href="{{ route('owner.registrations.index', array_merge(request()->query(), ['status' => 'ditolak', 'page' => 1])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'ditolak' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Ditolak ({{ $counts['ditolak'] }})
            </a>
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('owner.registrations.index') }}" class="w-full sm:w-72">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari toko, admin, email..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                <div class="absolute left-3 top-2.5 text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </form>
    </div>

    @php
        $getSortUrl = function ($column) use ($sort, $direction) {
            $nextDir = 'asc';
            if ($sort === $column) {
                $nextDir = $direction === 'asc' ? 'desc' : 'asc';
            } elseif ($column === 'created_at') {
                $nextDir = 'desc';
            }

            return route('owner.registrations.index', array_merge(request()->query(), [
                'sort' => $column,
                'direction' => $nextDir,
                'page' => 1,
            ]));
        };
    @endphp

    {{-- Registrations Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        @if($registrations->isEmpty())
            <div class="py-16 px-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Tidak Ada Pendaftaran Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    @if(request('search'))
                        Tidak ada pendaftaran yang sesuai dengan pencarian "{{ request('search') }}".
                    @else
                        Saat ini tidak ada pendaftaran pada kategori filter status ini.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                            {{-- Tenant Header --}}
                            <th scope="col" class="py-3.5 px-6">
                                <a href="{{ $getSortUrl('business_name') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-slate-800 transition-colors {{ $sort === 'business_name' ? 'text-orange-600 font-extrabold' : 'text-slate-500 font-bold' }}"
                                   title="Urutkan berdasarkan Nama Tenant">
                                    <span>Tenant</span>
                                    <span class="inline-flex items-center">
                                        @if($sort === 'business_name')
                                            @if($direction === 'asc')
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-3.5 h-3.5 text-slate-300 opacity-60 group-hover:opacity-100 group-hover:text-slate-500 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @endif
                                    </span>
                                </a>
                            </th>

                            {{-- Pemohon Header --}}
                            <th scope="col" class="py-3.5 px-6">
                                <a href="{{ $getSortUrl('admin_name') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-slate-800 transition-colors {{ $sort === 'admin_name' ? 'text-orange-600 font-extrabold' : 'text-slate-500 font-bold' }}"
                                   title="Urutkan berdasarkan Nama Pemohon / Admin">
                                    <span>Pemohon</span>
                                    <span class="inline-flex items-center">
                                        @if($sort === 'admin_name')
                                            @if($direction === 'asc')
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-3.5 h-3.5 text-slate-300 opacity-60 group-hover:opacity-100 group-hover:text-slate-500 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @endif
                                    </span>
                                </a>
                            </th>

                            {{-- Telepon Header --}}
                            <th scope="col" class="py-3.5 px-6">
                                <a href="{{ $getSortUrl('admin_phone') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-slate-800 transition-colors {{ $sort === 'admin_phone' ? 'text-orange-600 font-extrabold' : 'text-slate-500 font-bold' }}"
                                   title="Urutkan berdasarkan Nomor Telepon / WA">
                                    <span>Telepon</span>
                                    <span class="inline-flex items-center">
                                        @if($sort === 'admin_phone')
                                            @if($direction === 'asc')
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-3.5 h-3.5 text-slate-300 opacity-60 group-hover:opacity-100 group-hover:text-slate-500 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @endif
                                    </span>
                                </a>
                            </th>

                            {{-- Paket Header --}}
                            <th scope="col" class="py-3.5 px-6">
                                <a href="{{ $getSortUrl('plan_name') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-slate-800 transition-colors {{ $sort === 'plan_name' ? 'text-orange-600 font-extrabold' : 'text-slate-500 font-bold' }}"
                                   title="Urutkan berdasarkan Paket Langganan">
                                    <span>Paket</span>
                                    <span class="inline-flex items-center">
                                        @if($sort === 'plan_name')
                                            @if($direction === 'asc')
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-3.5 h-3.5 text-slate-300 opacity-60 group-hover:opacity-100 group-hover:text-slate-500 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @endif
                                    </span>
                                </a>
                            </th>

                            {{-- Status Header --}}
                            <th scope="col" class="py-3.5 px-6">
                                <a href="{{ $getSortUrl('status') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-slate-800 transition-colors {{ $sort === 'status' ? 'text-orange-600 font-extrabold' : 'text-slate-500 font-bold' }}"
                                   title="Urutkan berdasarkan Status Pengajuan">
                                    <span>Status</span>
                                    <span class="inline-flex items-center">
                                        @if($sort === 'status')
                                            @if($direction === 'asc')
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-3.5 h-3.5 text-slate-300 opacity-60 group-hover:opacity-100 group-hover:text-slate-500 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @endif
                                    </span>
                                </a>
                            </th>

                            {{-- Diajukan Header --}}
                            <th scope="col" class="py-3.5 px-6">
                                <a href="{{ $getSortUrl('created_at') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-slate-800 transition-colors {{ $sort === 'created_at' ? 'text-orange-600 font-extrabold' : 'text-slate-500 font-bold' }}"
                                   title="Urutkan berdasarkan Waktu Pengajuan">
                                    <span>Diajukan</span>
                                    <span class="inline-flex items-center">
                                        @if($sort === 'created_at')
                                            @if($direction === 'asc')
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-3.5 h-3.5 text-slate-300 opacity-60 group-hover:opacity-100 group-hover:text-slate-500 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @endif
                                    </span>
                                </a>
                            </th>

                            <th scope="col" class="py-3.5 px-6 text-right font-bold text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 text-sm">
                        @foreach($registrations as $reg)
                            @php
                                $logo = $reg->client?->logo;
                                $logoUrl = $logo ? asset('storage/' . $logo) : null;
                                $words = array_values(array_filter(explode(' ', trim($reg->business_name))));
                                if (count($words) >= 2) {
                                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                } else {
                                    $initials = strtoupper(substr($reg->business_name, 0, 2));
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                {{-- Tenant --}}
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-center gap-3">
                                        @if($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $reg->business_name }}"
                                                 class="w-10 h-10 rounded-xl object-cover border border-slate-200/80 shadow-2xs shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-200/70 text-orange-600 font-extrabold text-xs flex items-center justify-center shrink-0">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="block font-bold text-slate-900 leading-snug">{{ $reg->business_name }}</span>
                                            <span class="text-xs text-slate-400 leading-normal">{{ $reg->subdomain }}.rentalbase.id</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Pemohon --}}
                                <td class="py-4 px-6 align-middle">
                                    <span class="block font-semibold text-slate-800 leading-snug">{{ $reg->admin_name }}</span>
                                    <span class="block text-xs text-slate-400 leading-normal">{{ $reg->admin_email }}</span>
                                    @if($reg->description)
                                        <span class="block text-xs italic text-slate-400 mt-0.5" title="{{ $reg->description }}">
                                            "{{ \Illuminate\Support\Str::words($reg->description, 3, '...') }}"
                                        </span>
                                    @endif
                                </td>

                                {{-- Telepon --}}
                                <td class="py-4 px-6 align-middle text-xs font-medium text-slate-600 whitespace-nowrap">
                                    {{ $reg->admin_phone ?: '-' }}
                                </td>

                                {{-- Paket --}}
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-600 border border-orange-200/60 capitalize">
                                        {{ $reg->plan_name }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    @if($reg->status === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#FEF3C7] text-[#92400E]">
                                            Menunggu Verifikasi
                                        </span>
                                    @elseif($reg->status === 'disetujui')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                            Disetujui
                                        </span>
                                    @elseif($reg->status === 'ditolak')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ ucfirst($reg->status) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Diajukan --}}
                                <td class="py-4 px-6 align-middle text-xs text-slate-500 whitespace-nowrap">
                                    {{ $reg->created_at ? $reg->created_at->diffForHumans() : '-' }}
                                </td>

                                {{-- Aksi --}}
                                <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Tinjau Button --}}
                                        <button type="button"
                                                @click="activeTenant = {
                                                    id: {{ $reg->id }},
                                                    business_name: '{{ addslashes($reg->business_name) }}',
                                                    subdomain: '{{ $reg->subdomain }}',
                                                    admin_name: '{{ addslashes($reg->admin_name) }}',
                                                    admin_email: '{{ $reg->admin_email }}',
                                                    admin_phone: '{{ $reg->admin_phone }}',
                                                    plan_name: '{{ $reg->plan_name }}',
                                                    status: '{{ $reg->status }}',
                                                    description: '{{ addslashes($reg->description ?? '') }}',
                                                    rejection_reason: '{{ addslashes($reg->rejection_reason ?? '') }}',
                                                    payment_proof: '{{ $reg->payment_proof ? asset('storage/' . $reg->payment_proof) : '' }}',
                                                    created_at: '{{ $reg->created_at ? $reg->created_at->translatedFormat('d F Y, H:i') : '-' }}',
                                                    verify_url: '{{ route('owner.registrations.verify', $reg->id) }}',
                                                    reject_url: '{{ route('owner.registrations.reject', $reg->id) }}'
                                                }; showModal = true"
                                                class="px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            Tinjau
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($registrations->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $registrations->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- MODAL POPUP TINJAU DETAIL & BUKTI BAYAR --}}
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showModal = false"></div>

        {{-- Modal Dialog --}}
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-3xl max-h-[92vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100 text-left">

            {{-- Modal Header (Fixed top) --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-500 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 leading-tight" x-text="activeTenant?.business_name"></h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Subdomain: <span class="font-mono font-semibold text-slate-700" x-text="activeTenant?.subdomain + '.rentalbase.id'"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="showModal = false"
                        class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body (Scrollable inside if needed, fits screen nicely) --}}
            <div class="p-6 overflow-y-auto space-y-5 flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">

                    {{-- Left Column: Data Detail Tenant & Pemohon --}}
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Informasi Usaha</h4>
                            <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 space-y-2">
                                <div>
                                    <span class="block text-[11px] font-medium text-slate-400">Nama Usaha/Toko</span>
                                    <span class="text-sm font-bold text-slate-900" x-text="activeTenant?.business_name"></span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-medium text-slate-400">Pilihan Paket Langganan</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 capitalize mt-0.5" x-text="activeTenant?.plan_name"></span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-medium text-slate-400">Deskripsi Lengkap</span>
                                    <p class="text-xs text-slate-700 mt-1 italic whitespace-pre-line bg-white p-2.5 rounded-lg border border-slate-200/60 max-h-28 overflow-y-auto"
                                       x-text="activeTenant?.description || 'Tidak ada deskripsi usaha.'"></p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Data Pemohon / Admin</h4>
                            <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 space-y-2">
                                <div>
                                    <span class="block text-[11px] font-medium text-slate-400">Nama Lengkap</span>
                                    <span class="text-xs font-semibold text-slate-900" x-text="activeTenant?.admin_name"></span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-medium text-slate-400">Alamat Email</span>
                                    <span class="text-xs text-slate-700 font-medium" x-text="activeTenant?.admin_email"></span>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-medium text-slate-400">Nomor WhatsApp / Telepon</span>
                                    <div class="flex items-center justify-between gap-2 mt-1">
                                        <span class="text-xs text-slate-700 font-medium" x-text="activeTenant?.admin_phone || '-'"></span>
                                        <template x-if="getWhatsAppLink(activeTenant)">
                                            <a :href="getWhatsAppLink(activeTenant)"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-colors shrink-0"
                                               title="Buka percakapan WhatsApp dengan PIC">
                                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.101.005.241-.038.375.285.144.346.491 1.198.534 1.285.044.086.073.188.015.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.181-.076.355.101.173.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                                </svg>
                                                <span>Hubungi PIC</span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <span class="block text-[11px] font-medium text-slate-400">Waktu Pengajuan</span>
                                    <span class="text-xs text-slate-500" x-text="activeTenant?.created_at"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Bukti Pembayaran --}}
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Bukti Pembayaran Manual</h4>
                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100">
                            <template x-if="activeTenant?.payment_proof">
                                <div class="space-y-2.5">
                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 bg-white shadow-xs flex items-center justify-center">
                                        <img :src="activeTenant.payment_proof"
                                             :alt="'Bukti Bayar ' + activeTenant.business_name"
                                             class="w-full h-56 sm:h-64 object-contain bg-slate-900/5">
                                    </div>
                                    <div class="flex items-center justify-between text-xs px-0.5">
                                        <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Foto Bukti Terunggah
                                        </span>
                                        <a :href="activeTenant.payment_proof" target="_blank"
                                           class="font-semibold text-orange-600 hover:underline flex items-center gap-1">
                                            Buka Ukuran Penuh &rarr;
                                        </a>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!activeTenant?.payment_proof">
                                <div class="py-12 px-4 text-center">
                                    <div class="w-12 h-12 mx-auto rounded-xl bg-slate-200/80 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h5 class="text-xs font-bold text-slate-700">Tidak Ada Bukti Pembayaran</h5>
                                    <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                                        Pendaftar belum mengunggah gambar bukti transfer untuk pengajuan ini.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Modal Footer (Fixed bottom) --}}
            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <button type="button" @click="showModal = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Tutup
                </button>

                <template x-if="activeTenant?.status === 'menunggu_verifikasi'">
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="rejectId = activeTenant.id; rejectName = activeTenant.business_name; rejectUrl = activeTenant.reject_url; showModal = false; showRejectModal = true"
                                class="px-4 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-colors">
                            Tolak Pengajuan
                        </button>

                        <button type="button"
                                @click="verifyId = activeTenant.id; verifyName = activeTenant.business_name; verifyUrl = activeTenant.verify_url; showModal = false; showVerifyModal = true"
                                class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-md shadow-orange-500/20 transition-colors flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Verifikasi & Aktifkan
                        </button>
                    </div>
                </template>
            </div>

        </div>
    </div>

    {{-- MODAL KONFIRMASI VERIFIKASI PENDAFTARAN --}}
    <div x-show="showVerifyModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-verify-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showVerifyModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showVerifyModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">

                <form method="POST" :action="verifyUrl">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 border border-orange-200/60 flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Konfirmasi Verifikasi Tenant</h3>
                            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                Anda akan memverifikasi dan mengaktifkan operasional toko untuk <strong class="text-slate-800" x-text="verifyName"></strong>. Akun admin tenant dan langganan akan segera diaktifkan.
                            </p>
                        </div>

                        <div class="bg-orange-50/70 border border-orange-200/60 rounded-xl p-3 text-[11px] text-orange-900 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-orange-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Pastikan bukti transfer dan data pemohon telah sesuai sebelum menyetujui.</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showVerifyModal = false; showModal = true"
                                class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-md shadow-orange-500/20 transition-colors flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Ya, Verifikasi & Aktifkan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- MODAL TOLAK PENDAFTARAN --}}
    <div x-show="showRejectModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-reject-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showRejectModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showRejectModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">

                <form method="POST" :action="rejectUrl">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Tolak Pendaftaran Tenant</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Anda akan menolak pengajuan untuk <strong class="text-slate-800" x-text="rejectName"></strong>.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alasan Penolakan (akan dicatat di sistem):</label>
                            <textarea name="rejection_reason" rows="3" required
                                      placeholder="Contoh: Bukti pembayaran buram / nominal transfer tidak sesuai..."
                                      class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showRejectModal = false; showModal = true"
                                class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors">
                            Ya, Tolak Pengajuan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
