@extends('layouts.customer')

@section('title', 'Profil Saya — ' . ($tenantProfile?->name ?? $client->nama_usaha ?? 'RentalBase'))

@section('content')
<div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full"
     x-data="{
         avatarPreview: '{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : \Illuminate\Support\Facades\Storage::url($user->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=f97316&color=fff&bold=true' }}',
         showCurrentPass: false,
         showNewPass: false,
         showConfirmPass: false
     }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6" aria-label="Breadcrumb">
        <a href="{{ route('customer.home', ['subdomain' => $client->subdomain ?? 'babyrental']) }}" class="hover:text-primary-600 transition">Beranda</a>
        <span>/</span>
        <span class="text-gray-700 font-semibold">Profil Saya</span>
    </nav>

    {{-- Alert Notifikasi Sukses / Error --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs sm:text-sm text-emerald-800 font-medium flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('password_success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs sm:text-sm text-emerald-800 font-medium flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span>{{ session('password_success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-xs sm:text-sm text-red-800 font-medium flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0 text-red-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header Banner Profil --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm mb-8 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-primary-50 rounded-full blur-2xl pointer-events-none opacity-60"></div>
        
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 relative z-10 text-center sm:text-left">
            {{-- Avatar Besar --}}
            <div class="relative shrink-0">
                <img :src="avatarPreview"
                     alt="{{ $user->name }}"
                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-4 border-white shadow-md ring-1 ring-gray-100">
            </div>

            {{-- Info Nama & Akun --}}
            <div class="flex-grow min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1.5">
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">{{ $user->name }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                        Customer
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                </div>
                <p class="text-sm text-gray-500 font-medium mb-3 flex items-center justify-center sm:justify-start gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $user->email }}</span>
                </p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-gray-400 font-medium">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Bergabung sejak {{ $user->created_at ? $user->created_at->translatedFormat('F Y') : '-' }}
                    </span>
                    <span>•</span>
                    <span class="text-gray-500">
                        Belanja di: <b class="text-gray-800">{{ $tenantProfile?->name ?? $client->nama_usaha ?? 'RentalBase' }}</b>
                    </span>
                </div>
            </div>

            {{-- Tombol Cepat Pesanan Saya --}}
            <div class="shrink-0 flex items-center">
                <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-800 font-bold text-xs sm:text-sm rounded-xl border border-gray-200 transition-colors shadow-2xs">
                    <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Pesanan Saya</span>
                </a>
            </div>
        </div>

        {{-- 3 Kartu Statistik Riwayat Sewa (Kombo A: Clean Metric) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-8 pt-6 border-t border-gray-100">
            <div class="p-4 rounded-2xl bg-white border border-gray-200/80 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-gray-400 mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Pesanan</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="text-2xl font-black text-gray-900 tracking-tight">{{ $totalOrders }}</div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-gray-200/80 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-gray-400 mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sedang Berjalan</span>
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-2xl font-black text-amber-600 tracking-tight">{{ $activeOrders }}</div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-gray-200/80 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-gray-400 mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sewa Selesai</span>
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-2xl font-black text-emerald-600 tracking-tight">{{ $completedOrders }}</div>
            </div>
        </div>
    </div>

    {{-- Grid 2 Kolom: Edit Profil & Keamanan Password --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start mb-12">

        {{-- KOLOM KIRI (7/12): Form Edit Data Diri --}}
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 leading-tight">Data Diri</h2>
                    <p class="text-xs text-gray-500">Perbarui informasi profil dan kontak pengiriman kamu.</p>
                </div>
            </div>

            <form action="{{ route('customer.profile.update', ['subdomain' => $client->subdomain]) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Upload Ganti Foto Profil --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Foto Profil</label>
                    <div class="flex items-center gap-4">
                        <img :src="avatarPreview"
                             alt="{{ $user->name }}"
                             class="w-16 h-16 rounded-xl object-cover border border-gray-200 shadow-2xs">
                        <div class="flex-grow">
                            <input type="file"
                                   name="avatar"
                                   id="avatarInput"
                                   accept="image/png,image/jpeg,image/webp"
                                   @change="
                                       const file = $event.target.files[0];
                                       if (file) {
                                           avatarPreview = URL.createObjectURL(file);
                                       }
                                   "
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                            <p class="text-[11px] text-gray-400 mt-1">Format: JPG, PNG, atau WEBP. Maks 2MB.</p>
                        </div>
                    </div>
                    @error('avatar')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama Lengkap --}}
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition @error('name') border-red-300 ring-1 ring-red-200 @enderror"
                           placeholder="Masukkan nama lengkap kamu">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (Read-Only) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Alamat Email</span>
                        <span class="text-[10px] text-gray-400 lowercase font-normal flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Permanen (Identitas Akun)
                        </span>
                    </label>
                    <div class="relative">
                        <input type="email"
                               value="{{ $user->email }}"
                               disabled
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 text-sm cursor-not-allowed">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Email berfungsi sebagai tanda pengenal akun login dan tidak dapat diubah.</p>
                </div>

                {{-- Nomor WhatsApp / Telepon --}}
                <div>
                    <label for="phone" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Nomor WhatsApp / Telepon
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 text-xs font-bold">
                            +62
                        </div>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="81234567890"
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition @error('phone') border-red-300 ring-1 ring-red-200 @enderror">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Digunakan kurir toko untuk konfirmasi pengiriman barang sewa.</p>
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat Pengiriman Utama --}}
                <div>
                    <label for="address" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Alamat Pengiriman Utama
                    </label>
                    <textarea id="address"
                              name="address"
                              rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition @error('address') border-red-300 ring-1 ring-red-200 @enderror"
                              placeholder="Tuliskan nama jalan, nomor rumah, RT/RW, kelurahan, dan kota domisili">{{ old('address', $user->address) }}</textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Alamat ini akan otomatis digunakan saat kamu melakukan checkout barang.</p>
                    @error('address')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Simpan --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-500/20 transition-all active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Perubahan Data Diri</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- KOLOM KANAN (5/12): Form Ganti Password --}}
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 leading-tight">Keamanan Akun</h2>
                    <p class="text-xs text-gray-500">Perbarui kata sandi untuk melindungi akun kamu.</p>
                </div>
            </div>

            <form action="{{ route('customer.profile.password', ['subdomain' => $client->subdomain]) }}"
                  method="POST"
                  class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Password Saat Ini --}}
                <div>
                    <label for="current_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Password Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showCurrentPass ? 'text' : 'password'"
                               id="current_password"
                               name="current_password"
                               required
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition @error('current_password') border-red-300 ring-1 ring-red-200 @enderror">
                        <button type="button"
                                @click="showCurrentPass = !showCurrentPass"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showCurrentPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showCurrentPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Baru --}}
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showNewPass ? 'text' : 'password'"
                               id="password"
                               name="password"
                               required
                               placeholder="Minimal 8 karakter"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition @error('password') border-red-300 ring-1 ring-red-200 @enderror">
                        <button type="button"
                                @click="showNewPass = !showNewPass"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showNewPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showNewPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showConfirmPass ? 'text' : 'password'"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               placeholder="Ulangi password baru"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        <button type="button"
                                @click="showConfirmPass = !showConfirmPass"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showConfirmPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirmPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Tombol Perbarui Password --}}
                <div class="pt-3">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm shadow-md transition-all active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Perbarui Password</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Widget Riwayat Pesanan Terbaru --}}
    @if(isset($recentOrders) && $recentOrders->isNotEmpty())
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 leading-tight">Riwayat Pesanan Terbaru</h2>
                        <p class="text-xs text-gray-500">3 transaksi terakhir kamu di RentalBase.</p>
                    </div>
                </div>
                <a href="{{ route('customer.orders', array_filter(['client' => $client->subdomain ?? session('active_client_subdomain', null)])) }}"
                   class="text-xs font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1 hover:underline">
                    <span>Lihat Semua</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($recentOrders as $order)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-sm text-gray-900">#{{ $order->order_number }}</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold {{ $order->status_color ?? 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $order->status_dot_color ?? 'bg-gray-400' }}"></span>
                                        {{ $order->status_label ?? ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ $order->orderItems->count() }} item •
                                    {{ $order->rental_start_date ? $order->rental_start_date->format('d M') : '-' }} s/d {{ $order->rental_end_date ? $order->rental_end_date->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0">
                            <span class="text-sm font-black text-gray-900">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </span>
                            <a href="{{ route('customer.orders.show', $order->id) }}"
                               class="px-3.5 py-1.5 rounded-lg border border-gray-200 hover:border-primary-500 hover:text-primary-600 text-xs font-bold text-gray-700 transition">
                                Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
