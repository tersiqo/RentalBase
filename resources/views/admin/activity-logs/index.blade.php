@extends('layouts.admin')

@section('title', 'Log Aktivitas Toko')
@section('page-title', 'Log Aktivitas Toko')

@section('content')
<div class="space-y-6"
     x-data="{
        detailModalOpen: false,
        activeLog: null,
        loadingDetail: false,

        openDetail(logId) {
            this.loadingDetail = true;
            this.detailModalOpen = true;
            this.activeLog = null;

            fetch('{{ url('/admin/activity-logs') }}/' + logId, {
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
                console.error('Gagal mengambil detail log:', err);
            })
            .finally(() => {
                this.loadingDetail = false;
            });
        }
     }">

    {{-- Header Banner & Statistik --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Log Aktivitas Toko</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Jejak audit operasional internal toko {{ $client->business_name ?? 'Rental' }}. Pantau tindakan verifikasi, pengiriman, denda, dan inspeksi unit.
            </p>
        </div>

        {{-- Statistik Cepat --}}
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-white rounded-xl border border-gray-200/80 shadow-2xs text-right">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Hari Ini</span>
                <span class="text-base font-extrabold text-orange-600">{{ number_format($logsToday) }}</span>
            </div>
            <div class="px-4 py-2 bg-white rounded-xl border border-gray-200/80 shadow-2xs text-right">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Log</span>
                <span class="text-base font-extrabold text-gray-900">{{ number_format($totalLogs) }}</span>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-2xs">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="space-y-3.5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                
                {{-- Filter Tanggal Mulai --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $filters['from'] }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>

                {{-- Filter Tanggal Selesai --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $filters['to'] }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>

                {{-- Filter Staf Pelaku --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Staf Pelaksana</label>
                    <select name="user_id" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Semua Staf Toko</option>
                        @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}" {{ $filters['user_id'] == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ ucfirst($staff->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Kategori Modul --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Kategori Modul</label>
                    <select name="module" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ $filters['module'] == $mod ? 'selected' : '' }}>
                                {{ ucfirst($mod) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Baris Pencarian & Tombol Aksi --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-gray-100">
                <div class="w-full sm:w-80 relative">
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Cari kata kunci deskripsi atau aksi..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('admin.activity-logs.index') }}"
                       class="px-4 py-2 text-xs font-semibold rounded-xl text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                        Reset Filter
                    </a>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-semibold rounded-xl text-white bg-orange-500 hover:bg-orange-600 shadow-2xs transition">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Log Aktivitas --}}
    <div class="bg-white border border-gray-200/80 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200/70 bg-stone-50/70 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Staf Pelaku</th>
                        <th class="py-3.5 px-4">Modul &amp; Aksi</th>
                        <th class="py-3.5 px-4">Deskripsi Tindakan</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($logs as $log)
                        <tr class="hover:bg-stone-50/50 transition">
                            {{-- Waktu --}}
                            <td class="py-3.5 px-4 whitespace-nowrap text-gray-600">
                                <span class="font-bold text-gray-900 block">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '-' }}</span>
                                <span class="text-[11px] text-gray-400 font-mono">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }} WIB</span>
                            </td>

                            {{-- Pelaku --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log->user)
                                    <span class="font-bold text-gray-900 block">{{ $log->user->name }}</span>
                                    <span class="text-[10px] uppercase font-bold text-gray-400">{{ $log->user->role }}</span>
                                @else
                                    <span class="text-gray-400 italic">Sistem Otomatis</span>
                                @endif
                            </td>

                            {{-- Modul & Aksi --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider block w-max mb-1 {{ $log->module_badge['bg'] }}">
                                    {{ $log->module_badge['label'] }}
                                </span>
                                <span class="text-[11px] font-mono text-gray-500">{{ $log->action }}</span>
                            </td>

                            {{-- Deskripsi --}}
                            <td class="py-3.5 px-4 max-w-md text-gray-700 leading-relaxed">
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
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-stone-100 flex items-center justify-center text-gray-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-gray-700">Belum Ada Log Aktivitas</h3>
                                <p class="text-xs text-gray-400 mt-1">Belum ada rekaman tindakan staf atau filter tidak menemukan hasil.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Detail Log Teknis (Alpine.js) --}}
    <div x-show="detailModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
         style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 space-y-4"
             @click.away="detailModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900">Detail Rekam Aktivitas Staf</h3>
                    <p class="text-xs text-gray-400" x-text="'Log ID #' + (activeLog ? activeLog.id : '')"></p>
                </div>
                <button @click="detailModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="loadingDetail">
                <div class="py-12 text-center text-gray-400">
                    <span class="text-xs">Memuat detail rekam log...</span>
                </div>
            </template>

            <template x-if="!loadingDetail && activeLog">
                <div class="space-y-4 text-xs">
                    {{-- Baris Informasi Utama --}}
                    <div class="grid grid-cols-2 gap-4 bg-stone-50/70 p-4 rounded-xl border border-gray-100">
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[10px]">Waktu Eksekusi</span>
                            <span class="font-bold text-gray-800" x-text="activeLog.created_at"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[10px]">Staf Pelaksana</span>
                            <span class="font-bold text-gray-800" x-text="activeLog.actor_name + ' [' + activeLog.actor_role + ']'"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[10px]">Modul Sistem</span>
                            <span class="font-mono font-bold text-orange-600" x-text="activeLog.module"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[10px]">Aksi Teknis</span>
                            <span class="font-mono font-bold text-gray-700" x-text="activeLog.action"></span>
                        </div>
                    </div>

                    {{-- Deskripsi Penuh --}}
                    <div>
                        <span class="font-bold text-gray-500 uppercase tracking-wider block mb-1 text-[10px]">Narasi Tindakan</span>
                        <div class="p-3 bg-white border border-gray-200 rounded-xl font-medium text-gray-800 leading-relaxed whitespace-pre-wrap"
                             x-text="activeLog.description"></div>
                    </div>

                    {{-- Informasi Forensik Jaringan --}}
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100">
                            <span class="font-bold text-gray-400 block text-[10px] uppercase">Alamat IP</span>
                            <span class="font-mono text-gray-700" x-text="activeLog.ip_address"></span>
                        </div>
                        <div class="p-3 bg-stone-50 rounded-xl border border-gray-100">
                            <span class="font-bold text-gray-400 block text-[10px] uppercase">Perangkat / Browser</span>
                            <span class="font-mono text-gray-700 truncate block" :title="activeLog.user_agent" x-text="activeLog.user_agent"></span>
                        </div>
                    </div>
                </div>
            </template>

            <div class="pt-3 border-t border-gray-100 text-right">
                <button type="button" @click="detailModalOpen = false"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
