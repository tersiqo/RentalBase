@extends('layouts.admin')

@section('title', 'Manajemen Akun Admin')
@section('page-title', 'Manajemen Akun Admin')

@section('content')
<div id="adminsPage"
     x-data="{
        search: '',
        modalCreateOpen: {{ $errors->any() ? 'true' : 'false' }},
        modalDeactivateOpen: false,
        modalActivateOpen: false,
        targetAdmin: { id: null, name: '', email: '' },

        confirmDeactivate(id, name, email) {
            this.targetAdmin = { id: id, name: name, email: email };
            this.modalDeactivateOpen = true;
        },

        confirmActivate(id, name, email) {
            this.targetAdmin = { id: id, name: name, email: email };
            this.modalActivateOpen = true;
        }
     }"
     class="space-y-6">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm text-emerald-800 text-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Berhasil</p>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-800 p-1.5 rounded-lg hover:bg-emerald-100/50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="flex items-center justify-between p-4 bg-rose-50 border border-rose-200 rounded-2xl shadow-sm text-rose-800 text-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Peringatan</p>
                    <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
            <button @click="show = false" type="button" class="text-rose-500 hover:text-rose-800 p-1.5 rounded-lg hover:bg-rose-100/50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-orange-600">PENGELOLAAN INTERNAL TOKO</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Manajemen Akun Admin</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun staf Admin internal toko Anda dan pantau batasan kuota sesuai paket subscription yang aktif.</p>
        </div>

        <div>
            @if($isQuotaFull)
                <button type="button"
                        disabled
                        title="Batas kuota Admin aktif telah tercapai untuk paket Anda"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed shadow-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Tambah Admin (Penuh)</span>
                </button>
            @else
                <button @click="modalCreateOpen = true"
                        type="button"
                        id="btn-tambah-admin"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 active:scale-95 transition-all shadow-md shadow-orange-500/25">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>Tambah Admin</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Indikator Kuota Paket Admin --}}
    <div class="bg-white rounded-2xl border {{ $isQuotaFull ? 'border-amber-300 bg-gradient-to-r from-amber-50/40 via-white to-amber-50/20' : 'border-gray-100' }} p-5 lg:p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            {{-- Info Pokok --}}
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 {{ $isQuotaFull ? 'bg-amber-100 text-amber-600' : 'bg-orange-50 text-orange-500' }}">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">PAKET SUBSCRIPTION</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 border border-orange-200">
                            {{ $planName }}
                        </span>
                        @if($isQuotaFull)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                Kuota Penuh
                            </span>
                        @endif
                    </div>
                    <div class="mt-1 flex flex-wrap items-baseline gap-2">
                        <p class="text-lg sm:text-xl font-extrabold text-gray-900">
                            Kuota Admin: <span class="text-orange-600">{{ $activeAdminsCount }}</span> dari {{ $maxAdmins }} akun aktif
                        </p>
                        <span class="text-gray-300 hidden sm:inline">•</span>
                        <p class="text-sm font-semibold {{ $remainingQuota > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                            Sisa kuota: <span class="font-bold">{{ $remainingQuota }} akun</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Progress Bar & Status Text --}}
            <div class="w-full lg:w-72 flex flex-col justify-center">
                <div class="flex justify-between items-center text-xs font-bold mb-1.5">
                    <span class="text-gray-500">Penggunaan Kuota</span>
                    <span class="{{ $isQuotaFull ? 'text-amber-600' : 'text-orange-600' }}">{{ $usagePercentage }}%</span>
                </div>
                <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $isQuotaFull ? 'bg-amber-500' : 'bg-orange-500' }}"
                         style="width: {{ $usagePercentage }}%;"></div>
                </div>
                <p class="text-[11px] text-gray-400 font-medium mt-1.5">
                    Hanya akun dengan status <span class="font-bold text-gray-600">Aktif</span> yang dihitung dalam kuota.
                </p>
            </div>
        </div>

        @if($isQuotaFull)
            <div class="mt-4 pt-4 border-t border-amber-200/60 flex items-start sm:items-center gap-2.5 text-xs text-amber-800">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5 sm:mt-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                    <strong>Batas kuota tercapai:</strong> Toko Anda telah menggunakan seluruh kuota Admin aktif ({{ $maxAdmins }} akun). Untuk menambah staf baru atau mengaktifkan kembali akun nonaktif, silakan nonaktifkan akun staf yang ada terlebih dahulu atau lakukan upgrade paket subscription.
                </span>
            </div>
        @endif
    </div>

    {{-- Tabel Staf Admin Toko --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Search & Tools Bar --}}
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="relative max-w-sm w-full">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text"
                       x-model="search"
                       placeholder="Cari nama atau email admin..."
                       class="w-full pl-9 pr-4 py-2 text-xs font-medium rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
            </div>

            <div class="text-xs text-gray-500 font-medium">
                Total terdaftar: <span class="font-bold text-gray-800">{{ $totalAdminsCount }}</span> staf ({{ $activeAdminsCount }} Aktif)
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-50/80 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Nama Admin</th>
                        <th class="py-3.5 px-6">Email & Kontak</th>
                        <th class="py-3.5 px-6">Status Akun</th>
                        <th class="py-3.5 px-6">Keterangan</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                    @forelse($admins as $admin)
                        @php
                            $isMe = (int) $admin->id === (int) $currentUser->id;
                            $isPrimary = $admin->isPrimaryAdmin();
                            $isActive = $admin->status === 'aktif';
                        @endphp
                        <tr x-show="search === '' || '{{ strtolower($admin->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($admin->email) }}'.includes(search.toLowerCase())"
                            class="hover:bg-stone-50/60 transition-colors {{ $isMe ? 'bg-orange-50/20' : '' }}">

                            {{-- Nama Admin --}}
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs shrink-0 {{ $isPrimary ? 'bg-amber-500 shadow-sm shadow-amber-500/30' : ($isMe ? 'bg-orange-500 shadow-sm shadow-orange-500/30' : 'bg-slate-800') }}">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-gray-900 text-sm truncate">{{ $admin->name }}</p>
                                            @if($isPrimary)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                                                    Admin Utama
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                                                    Admin Staf
                                                </span>
                                            @endif
                                            @if($isMe)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 border border-orange-200 shrink-0">
                                                    Akun Anda
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-gray-400 truncate">ID: #{{ $admin->id }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Email & Kontak --}}
                            <td class="py-4 px-6">
                                <p class="text-gray-900 font-semibold">{{ $admin->email }}</p>
                                @if($admin->phone)
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $admin->phone }}</p>
                                    @endif
                            </td>

                            {{-- Status Akun --}}
                            <td class="py-4 px-6">
                                @if($isActive)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Keterangan --}}
                            <td class="py-4 px-6">
                                <div class="text-xs">
                                    @if($isPrimary)
                                        <span class="font-semibold text-amber-700">Pengelola Utama Toko</span>
                                    @else
                                        <span class="text-gray-600">Staf Operasional</span>
                                    @endif
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        Terdaftar {{ $admin->created_at ? $admin->created_at->translatedFormat('d M Y') : '-' }}
                                    </p>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="py-4 px-6 text-right">
                                @if($isPrimary)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs text-amber-700 font-semibold bg-amber-50 rounded-xl border border-amber-200 cursor-not-allowed"
                                          title="Akun Admin Utama tidak dapat dinonaktifkan">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span>Admin Utama</span>
                                    </span>
                                @elseif($isMe)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs text-gray-400 font-medium bg-gray-50 rounded-xl border border-gray-100 cursor-not-allowed"
                                          title="Anda tidak dapat menonaktifkan akun sendiri">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span>Terkunci</span>
                                    </span>
                                @else
                                    @if($isActive)
                                        <button type="button"
                                                @click="confirmDeactivate({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ addslashes($admin->email) }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-200 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <span>Nonaktifkan</span>
                                        </button>
                                    @else
                                        @if($isQuotaFull)
                                            <button type="button"
                                                    disabled
                                                    title="Kuota Admin aktif penuh ({{ $maxAdmins }} akun). Nonaktifkan staf lain terlebih dahulu."
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-400 bg-gray-100 rounded-xl border border-gray-200 cursor-not-allowed">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>Aktifkan (Penuh)</span>
                                            </button>
                                        @else
                                            <button type="button"
                                                    @click="confirmActivate({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ addslashes($admin->email) }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl border border-emerald-200 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Aktifkan</span>
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center">
                                <div class="max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 mx-auto flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">Belum ada akun staf</h3>
                                    <p class="text-xs text-gray-500 mt-1">Hanya akun Anda yang terdaftar pada toko ini. Anda dapat menambahkan staf operasional baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Helper Card below table if only 1 admin exists --}}
        @if($totalAdminsCount <= 1)
            <div class="p-4 sm:p-5 bg-stone-50 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="text-gray-600">
                        <strong>Tips:</strong> Anda belum memiliki staf tambahan. Buat akun Admin untuk anggota tim Anda agar dapat membantu mengelola pesanan dan operasional rental.
                    </p>
                </div>
                @if(!$isQuotaFull)
                    <button @click="modalCreateOpen = true"
                            type="button"
                            class="px-3.5 py-1.5 font-bold text-orange-600 hover:text-orange-700 bg-white border border-orange-200 rounded-xl hover:bg-orange-50 transition shrink-0">
                        + Tambah Staf Baru
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- MODAL: Tambah Admin Baru --}}
    <div x-show="modalCreateOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="modalCreateOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalCreateOpen = false"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Body --}}
            <div x-show="modalCreateOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                <form method="POST" action="{{ route('admin.admins.store') }}">
                    @csrf

                    {{-- Modal Header --}}
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">Tambah Admin Baru</h3>
                                <p class="text-xs text-gray-500">Buat akun staf Admin internal untuk toko {{ $client->business_name ?? 'Anda' }}</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="modalCreateOpen = false"
                                class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Content --}}
                    <div class="p-6 space-y-4">
                        {{-- Kuota Info Banner --}}
                        <div class="p-3 bg-orange-50/70 border border-orange-200/60 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-orange-900">
                                Paket: <strong>{{ $planName }}</strong> • Kuota tersisa:
                            </span>
                            <span class="font-bold text-orange-700 bg-white px-2.5 py-0.5 rounded-full border border-orange-200 shadow-xs">
                                {{ $remainingQuota }} akun
                            </span>
                        </div>

                        {{-- Field: Nama Lengkap --}}
                        <div>
                            <label for="name" class="block text-xs font-bold text-gray-700 mb-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="Contoh: Siti Rahmawati"
                                   class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border {{ $errors->has('name') ? 'border-rose-400 focus:ring-rose-500' : 'border-gray-200 focus:ring-orange-500' }} focus:outline-none focus:ring-2 transition">
                            @error('name')
                                <p class="text-[11px] font-semibold text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Field: Email --}}
                        <div>
                            <label for="email" class="block text-xs font-bold text-gray-700 mb-1">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   required
                                   placeholder="nama@contoh.com"
                                   class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border {{ $errors->has('email') ? 'border-rose-400 focus:ring-rose-500' : 'border-gray-200 focus:ring-orange-500' }} focus:outline-none focus:ring-2 transition">
                            @error('email')
                                <p class="text-[11px] font-semibold text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Field: Password --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-bold text-gray-700 mb-1">
                                    Password <span class="text-rose-500">*</span>
                                </label>
                                <input type="password"
                                       name="password"
                                       id="password"
                                       required
                                       minlength="8"
                                       placeholder="Minimal 8 karakter"
                                       class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border {{ $errors->has('password') ? 'border-rose-400 focus:ring-rose-500' : 'border-gray-200 focus:ring-orange-500' }} focus:outline-none focus:ring-2 transition">
                                @error('password')
                                    <p class="text-[11px] font-semibold text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold text-gray-700 mb-1">
                                    Konfirmasi Password <span class="text-rose-500">*</span>
                                </label>
                                <input type="password"
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       required
                                       minlength="8"
                                       placeholder="Ulangi password"
                                       class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            Akun baru secara otomatis memiliki role <strong>Admin Rental</strong> dan terhubung ke toko Anda. Akun langsung aktif setelah disimpan.
                        </p>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-stone-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalCreateOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200/60 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 active:scale-95 transition-all shadow-md shadow-orange-500/20">
                            Simpan & Buat Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Konfirmasi Nonaktifkan Admin --}}
    <div x-show="modalDeactivateOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalDeactivateOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalDeactivateOpen = false"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalDeactivateOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">

                <form :action="'{{ url('/admin/admins') }}/' + targetAdmin.id + '/status'" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="nonaktif">

                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-gray-900">Nonaktifkan Akun Admin?</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Konfirmasi penonaktifan akses staf operasional.</p>
                            </div>
                        </div>

                        <div class="mt-4 p-3.5 bg-rose-50/70 rounded-xl border border-rose-200/60 text-xs text-rose-900 space-y-1.5">
                            <p>
                                Anda akan menonaktifkan akun <strong><span x-text="targetAdmin.name"></span></strong> (<span x-text="targetAdmin.email"></span>).
                            </p>
                            <p class="text-rose-700">
                                Staf ini <strong>tidak akan dapat login</strong> ke panel Admin RentalBase hingga diaktifkan kembali. Data akun tetap aman (tidak dihapus) dan kuota Admin aktif Anda akan bertambah 1.
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-stone-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalDeactivateOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200/60 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-95 transition-all shadow-md shadow-rose-600/20">
                            Ya, Nonaktifkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Konfirmasi Aktifkan Admin --}}
    <div x-show="modalActivateOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalActivateOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalActivateOpen = false"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalActivateOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">

                <form :action="'{{ url('/admin/admins') }}/' + targetAdmin.id + '/status'" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="aktif">

                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-gray-900">Aktifkan Kembali Admin?</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Pulihkan akses staf operasional.</p>
                            </div>
                        </div>

                        <div class="mt-4 p-3.5 bg-emerald-50/70 rounded-xl border border-emerald-200/60 text-xs text-emerald-900 space-y-1.5">
                            <p>
                                Akun <strong><span x-text="targetAdmin.name"></span></strong> (<span x-text="targetAdmin.email"></span>) akan diaktifkan kembali.
                            </p>
                            <p class="text-emerald-700">
                                Akun ini akan kembali menggunakan 1 kuota Admin aktif pada paket <strong>{{ $planName }}</strong> (Sisa saat ini: {{ $remainingQuota }} akun).
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-stone-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalActivateOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200/60 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 transition-all shadow-md shadow-emerald-600/20">
                            Ya, Aktifkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
