@extends('layouts.owner')

@section('title', 'Audit Trail Activity Log — RentalBase Platform')
@section('page-title', 'Jejak Aktivitas Platform')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto"
     x-data="{
        detailModalOpen: false,
        activeLog: null,
        loadingDetail: false,

        openDetail(logId) {
            this.loadingDetail = true;
            this.detailModalOpen = true;
            this.activeLog = null;

            fetch('{{ url('/owner/activity-logs') }}/' + logId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.activeLog = data.data;
                }
            })
            .catch(err => {
                console.error('Gagal mengambil data log:', err);
            })
            .finally(() => {
                this.loadingDetail = false;
            });
        }
     }">

    {{-- Header Banner & Statistik Cepat --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200/60">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight sm:text-3xl">
                Audit Trail &amp; Activity Log
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Pusat pengawasan rekam jejak transparansi seluruh aktivitas tenant dan platform Super Admin secara real-time.
            </p>
        </div>

        {{-- Badge Status Metrik --}}
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs text-right">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Hari Ini</span>
                <span class="text-lg font-extrabold text-orange-600">{{ number_format($logsToday) }}</span>
            </div>
            <div class="px-4 py-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs text-right">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Log</span>
                <span class="text-lg font-extrabold text-slate-900">{{ number_format($totalLogs) }}</span>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('owner.activity-logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                
                {{-- Filter Tanggal Mulai --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $filters['from'] }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>

                {{-- Filter Tanggal Selesai --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $filters['to'] }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>

                {{-- Filter Tenant --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Toko / Tenant</label>
                    <select name="client_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Semua Tenant</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ $filters['client_id'] == $t->id ? 'selected' : '' }}>
                                {{ $t->business_name }} ({{ $t->subdomain }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Pelaku --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Staf / Pelaku</label>
                    <select name="user_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Semua Pelaku</option>
                        @foreach($actors as $actor)
                            <option value="{{ $actor->id }}" {{ $filters['user_id'] == $actor->id ? 'selected' : '' }}>
                                {{ $actor->name }} ({{ ucfirst($actor->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Kategori Modul --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori Modul</label>
                    <select name="module" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ $filters['module'] == $mod ? 'selected' : '' }}>
                                {{ ucfirst($mod) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Baris Pencarian Teks & Tombol Aksi --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="w-full sm:w-96 relative">
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Cari deskripsi atau kata kunci aksi..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('owner.activity-logs.index') }}"
                       class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                        Reset Filter
                    </a>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-semibold rounded-xl text-white shadow-xs transition"
                            style="background: #F97316;">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Audit Trail --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/70 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Waktu &amp; Tanggal</th>
                        <th class="py-3.5 px-4">Tenant / Toko</th>
                        <th class="py-3.5 px-4">Pelaku &amp; Peran</th>
                        <th class="py-3.5 px-4">Modul &amp; Aksi</th>
                        <th class="py-3.5 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            {{-- Waktu --}}
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                <span class="font-bold text-slate-900 block">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '-' }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }} WIB</span>
                            </td>

                            {{-- Tenant --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log->client)
                                    <span class="font-bold text-slate-900 block">{{ $log->client->business_name }}</span>
                                    <span class="text-[11px] text-orange-600 font-mono">{{ $log->client->subdomain }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        Platform Pusat
                                    </span>
                                @endif
                            </td>

                            {{-- Pelaku --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log->user)
                                    <span class="font-bold text-slate-900 block">{{ $log->user->name }}</span>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">{{ $log->user->role }}</span>
                                @else
                                    <span class="text-slate-400 italic">Sistem Otomatis</span>
                                @endif
                            </td>

                            {{-- Modul & Aksi --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider block w-max mb-1 {{ $log->module_badge['bg'] }}">
                                    {{ $log->module_badge['label'] }}
                                </span>
                                <span class="text-[11px] font-mono text-slate-500">{{ $log->action }}</span>
                            </td>

                            {{-- Deskripsi --}}
                            <td class="py-3.5 px-4 max-w-md text-slate-700 leading-relaxed">
                                <p class="line-clamp-2" title="{{ $log->description }}">
                                    {{ $log->description }}
                                </p>
                            </td>

                            {{-- Tombol Detail --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button type="button"
                                        @click="openDetail({{ $log->id }})"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-orange-600 bg-orange-50 hover:bg-orange-100 border border-orange-200/80 transition">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700">Tidak Ada Log Aktivitas</h3>
                                <p class="text-xs text-slate-400 mt-1">Tidak ditemukan rekaman log yang sesuai dengan filter yang dipilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Detail Log Teknis (Alpine.js) --}}
    <div x-show="detailModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
         style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.away="detailModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Detail Rekam Jejak Audit</h3>
                    <p class="text-xs text-slate-400" x-text="'Log ID #' + (activeLog ? activeLog.id : '')"></p>
                </div>
                <button @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="loadingDetail">
                <div class="py-12 text-center text-slate-400">
                    <span class="text-xs">Memuat detail rekam log...</span>
                </div>
            </template>

            <template x-if="!loadingDetail && activeLog">
                <div class="space-y-4 text-xs">
                    {{-- Baris Informasi Utama --}}
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Waktu Eksekusi</span>
                            <span class="font-bold text-slate-800" x-text="activeLog.created_at"></span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Toko / Tenant</span>
                            <span class="font-bold text-slate-800" x-text="activeLog.client_name + ' (' + activeLog.subdomain + ')'"></span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Pelaku &amp; Peran</span>
                            <span class="font-bold text-slate-800" x-text="activeLog.actor_name + ' [' + activeLog.actor_role + ']'"></span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-400 uppercase tracking-wider block text-[10px]">Kategori &amp; Aksi</span>
                            <span class="font-mono font-bold text-orange-600" x-text="activeLog.module + ' -> ' + activeLog.action"></span>
                        </div>
                    </div>

                    {{-- Deskripsi Penuh --}}
                    <div>
                        <span class="font-bold text-slate-500 uppercase tracking-wider block mb-1 text-[10px]">Narasi Perubahan / Deskripsi</span>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl font-medium text-slate-800 leading-relaxed whitespace-pre-wrap"
                             x-text="activeLog.description"></div>
                    </div>

                    {{-- Informasi Forensik Jaringan --}}
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-slate-400 block text-[10px] uppercase">Alamat IP</span>
                            <span class="font-mono text-slate-700" x-text="activeLog.ip_address"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-slate-400 block text-[10px] uppercase">User Agent (Browser)</span>
                            <span class="font-mono text-slate-700 truncate block" :title="activeLog.user_agent" x-text="activeLog.user_agent"></span>
                        </div>
                    </div>
                </div>
            </template>

            <div class="pt-3 border-t border-slate-100 text-right">
                <button type="button" @click="detailModalOpen = false"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
