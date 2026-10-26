@extends('layouts.admin')

@section('title', 'Profil & Tema Toko')

@section('content')
<div class="max-w-5xl mx-auto space-y-8"
     x-data="tenantProfileApp({
         initialLogo: @js($profile->logo_path ? Storage::url($profile->logo_path) : ($client->logo ? Storage::url($client->logo) : null)),
         initialColor: @js(old('theme_color', $profile->theme_color ?? $client->theme_color ?? '#F97316')),
         isStarter: @js(str_contains(strtolower($client->plan ?? $subscription?->plan_name ?? 'starter'), 'starter'))
     })">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1">
                <span>Pengaturan</span>
                <span>/</span>
                <span class="text-orange-600 font-semibold">Profil & Tema Toko</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Profil & Tema Toko</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola identitas publik, logo, kontak operasional, serta kustomisasi tema visual katalog toko rental Anda.</p>
        </div>

        {{-- Subscription Plan Badge --}}
        @php
            $plan = strtolower($client->plan ?? $subscription?->plan_name ?? 'starter');
            $isStarter = str_contains($plan, 'starter');
            $currentColor = $profile->theme_color ?? $client->theme_color ?? '#F97316';
        @endphp
        <div class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-white border border-gray-200/80 shadow-sm shrink-0">
            <div class="w-2.5 h-2.5 rounded-full {{ $isStarter ? 'bg-amber-400' : 'bg-emerald-500 ring-4 ring-emerald-50' }}"></div>
            <div class="text-xs">
                <span class="text-gray-400 font-medium">Paket: </span>
                <span class="font-extrabold uppercase {{ $isStarter ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $subscription->plan_name ?? strtoupper($plan) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl flex items-center gap-3.5 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-emerald-900">Berhasil Disimpan!</p>
                <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl flex items-center gap-3.5 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center shrink-0 text-red-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-red-900">Perhatian</p>
                <p class="text-xs text-red-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2 text-sm font-bold text-red-900 mb-1.5">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terdapat beberapa kesalahan input:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-red-700 space-y-1 pl-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Mode Read-Only untuk Admin Staf --}}
    @if (! Auth::user()?->isPrimaryAdmin())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4.5 flex items-start sm:items-center gap-3.5 text-amber-900 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div class="text-xs leading-relaxed">
                <p class="font-bold text-amber-950">Mode Lihat Saja (Read-Only)</p>
                <p class="text-amber-800 mt-0.5">Anda sedang login sebagai <strong>Admin Staf</strong>. Pengubahan profil, unggah logo, dan pengaturan warna tema hanya dapat disimpan oleh <strong>Admin Utama</strong> toko.</p>
            </div>
        </div>
    @endif

    {{-- Form Utama Profil & Tema --}}
    <form action="{{ route('admin.settings.profile.update') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-8">
        @csrf

        {{-- Hidden Flag Hapus Logo --}}
        <input type="hidden" name="remove_logo" :value="removeLogo ? '1' : '0'">

        {{-- Section 1: Identitas & Logo Toko --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-stone-50/60 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Identitas & Logo Toko</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Informasi utama yang ditampilkan pada header dan identitas katalog online Anda.</p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">
                    01
                </span>
            </div>

            <div class="p-6 lg:p-8 space-y-6">
                {{-- Logo Uploader --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                        Logo Toko
                    </label>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        {{-- Preview Box --}}
                        <div class="relative w-28 h-28 rounded-2xl border-2 border-dashed border-gray-300 bg-stone-50 overflow-hidden flex items-center justify-center shrink-0 group">
                            <template x-if="logoPreview">
                                <img :src="logoPreview"
                                     alt="Preview Logo"
                                     class="w-full h-full object-contain p-2">
                            </template>

                            <template x-if="!logoPreview">
                                <div class="text-center p-3">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[10px] text-gray-400 block mt-1 font-semibold">Belum Ada Logo</span>
                                </div>
                            </template>

                            {{-- Watermark Overlay Saat Ada Logo --}}
                            <div x-show="logoPreview"
                                 class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                <span class="text-[10px] font-bold text-white bg-black/50 px-2 py-1 rounded-md">Preview</span>
                            </div>
                        </div>

                        {{-- Upload Controls --}}
                        <div class="space-y-3 flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-bold transition-colors cursor-pointer border border-orange-200/80 shadow-sm {{ !Auth::user()?->isPrimaryAdmin() ? 'opacity-60 pointer-events-none' : '' }}">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span>Pilih Gambar Logo</span>
                                    <input type="file"
                                           name="logo"
                                           accept="image/png,image/jpeg,image/jpg,image/webp"
                                           x-ref="logoInput"
                                           @change="handleFileSelect($event)"
                                           class="sr-only"
                                           {{ !Auth::user()?->isPrimaryAdmin() ? 'disabled' : '' }}>
                                </label>

                                <button type="button"
                                        x-show="logoPreview"
                                        @click="clearLogo()"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors border border-red-200/60 {{ !Auth::user()?->isPrimaryAdmin() ? 'opacity-60 pointer-events-none' : '' }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus Logo</span>
                                </button>
                            </div>

                            <p class="text-xs text-gray-500 leading-normal">
                                Format yang didukung: <strong>JPG, PNG, WebP</strong>. Ukuran file maksimal <strong>2MB</strong>. Disarankan gambar berbentuk persegi dengan rasio 1:1 untuk tampilan optimal.
                            </p>

                            {{-- File Size Error Alert --}}
                            <template x-if="fileError">
                                <div class="text-xs text-red-600 font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="fileError"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nama Toko --}}
                    <div>
                        <label for="input-name" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                            Nama Toko / Rental <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="input-name"
                               name="name"
                               value="{{ old('name', $profile->name ?? $client->business_name) }}"
                               required
                               maxlength="255"
                               placeholder="Contoh: RentBase Baby & Kids Surabaya"
                               {{ !Auth::user()?->isPrimaryAdmin() ? 'readonly' : '' }}
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors {{ !Auth::user()?->isPrimaryAdmin() ? 'bg-gray-50 cursor-not-allowed' : 'bg-white' }}">
                    </div>

                    {{-- Subdomain (Readonly) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="input-subdomain" class="block text-xs font-bold uppercase tracking-wider text-gray-600">
                                Subdomain Toko
                            </label>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-2 py-0.5 rounded-md">
                                Read-Only
                            </span>
                        </div>
                        <div class="relative flex rounded-xl border border-gray-200 bg-gray-100/90 overflow-hidden shadow-inner">
                            <input type="text"
                                   id="input-subdomain"
                                   name="subdomain"
                                   value="{{ $profile->subdomain ?? $client->subdomain }}"
                                   readonly
                                   class="w-full px-4 py-2.5 text-sm text-gray-600 font-mono font-bold bg-transparent border-0 cursor-not-allowed focus:ring-0">
                            <span class="inline-flex items-center px-3.5 bg-gray-200/70 text-gray-500 text-xs font-mono font-semibold border-l border-gray-200">
                                .rentalbase.id
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1.5">
                            Subdomain bersifat permanen sebagai tautan publik toko Anda dan tidak dapat diubah sembarangan.
                        </p>
                    </div>
                </div>

                {{-- Deskripsi Toko --}}
                <div>
                    <label for="input-description" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                        Deskripsi Singkat Toko
                    </label>
                    <textarea id="input-description"
                              name="description"
                              rows="3"
                              maxlength="2000"
                              placeholder="Ceritakan profil singkat usaha rental Anda, jaminan kebersihan peralatan, keunggulan layanan, atau info jam operasional..."
                              {{ !Auth::user()?->isPrimaryAdmin() ? 'readonly' : '' }}
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors {{ !Auth::user()?->isPrimaryAdmin() ? 'bg-gray-50 cursor-not-allowed' : 'bg-white' }}">{{ old('description', $profile->description ?? $client->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 2: Kontak & Lokasi Operasional --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-stone-50/60 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Kontak & Lokasi Operasional</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Informasi kontak dan titik alamat fisik toko yang terlihat oleh customer.</p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">
                    02
                </span>
            </div>

            <div class="p-6 lg:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- WhatsApp --}}
                    <div>
                        <label for="input-whatsapp" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                            Nomor WhatsApp Resmi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   id="input-whatsapp"
                                   name="whatsapp"
                                   value="{{ old('whatsapp', $profile->whatsapp) }}"
                                   placeholder="Contoh: 081234567890"
                                   maxlength="30"
                                   {{ !Auth::user()?->isPrimaryAdmin() ? 'readonly' : '' }}
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors {{ !Auth::user()?->isPrimaryAdmin() ? 'bg-gray-50 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1.5">Digunakan oleh customer untuk konfirmasi atau pertanyaan terkait sewa.</p>
                    </div>

                    {{-- Kota --}}
                    <div>
                        <label for="input-city" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                            Kota Operasional
                        </label>
                        <input type="text"
                               id="input-city"
                               name="city"
                               value="{{ old('city', $profile->city) }}"
                               placeholder="Contoh: Surabaya, Jawa Timur"
                               maxlength="100"
                               {{ !Auth::user()?->isPrimaryAdmin() ? 'readonly' : '' }}
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors {{ !Auth::user()?->isPrimaryAdmin() ? 'bg-gray-50 cursor-not-allowed' : 'bg-white' }}">
                    </div>
                </div>

                {{-- Alamat Lengkap --}}
                <div>
                    <label for="input-address" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                        Alamat Fisik / Workshop
                    </label>
                    <textarea id="input-address"
                              name="address"
                              rows="3"
                              maxlength="1000"
                              placeholder="Tuliskan alamat lengkap toko fisik atau workshop untuk keperluan pengambilan barang (self-pickup) atau pengembalian..."
                              {{ !Auth::user()?->isPrimaryAdmin() ? 'readonly' : '' }}
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors {{ !Auth::user()?->isPrimaryAdmin() ? 'bg-gray-50 cursor-not-allowed' : 'bg-white' }}">{{ old('address', $profile->address) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 3: Tema Warna Toko (Branding) --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-stone-50/60 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Tema Warna Toko (Branding)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Sesuaikan warna utama elemen antarmuka katalog customer agar selaras dengan identitas brand Anda.</p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">
                    03
                </span>
            </div>

            <div class="p-6 lg:p-8 space-y-6">

                {{-- Kondisi 4: Banner Khusus Paket Starter --}}
                @if ($isStarter)
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl p-5 relative overflow-hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-200 flex items-center justify-center shrink-0 text-amber-700">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-sm font-bold text-amber-950">Warna Tema Terkunci</h3>
                                        {{-- Badge Khusus Paket Business & Pro --}}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-amber-200/80 text-amber-900 border border-amber-300">
                                            Khusus Paket Business & Pro
                                        </span>
                                    </div>
                                    <p class="text-xs text-amber-800 leading-relaxed max-w-xl">
                                        Tenant pada paket <strong>Starter</strong> menggunakan warna tema standar bawaan RentalBase (<span class="font-mono font-bold">{{ $currentColor }}</span>). Untuk kebebasan memilih warna kustom brand Anda, silakan tingkatkan paket langganan.
                                    </p>
                                </div>
                            </div>

                            {{-- Tombol CTA Upgrade --}}
                            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20RentalBase,%20saya%20ingin%20upgrade%20ke%20paket%20Business/Pro%20untuk%20kustomisasi%20tema"
                               target="_blank"
                               rel="noopener"
                               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition-all shadow-md hover:shadow-lg shrink-0">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                <span>Upgrade Paket Sekarang</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-3.5 py-2 rounded-xl">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Fitur Kustomisasi Tema Aktif (Paket {{ strtoupper($plan) }})</span>
                    </div>
                @endif

                {{-- Color Picker Control (dengan pengurangan opacity & disabled bila Starter) --}}
                <div class="space-y-6 {{ $isStarter || !Auth::user()?->isPrimaryAdmin() ? 'opacity-50 select-none' : '' }}">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-3">
                            Pilih Warna Tema Brand
                        </label>

                        <div class="flex flex-wrap items-center gap-4">
                            {{-- Native Input Type Color --}}
                            <div class="relative w-14 h-14 rounded-2xl overflow-hidden border-2 border-gray-200 shadow-sm focus-within:ring-2 focus-within:ring-orange-500 transition-all cursor-pointer"
                                 :style="`background-color: ${themeColor};`">
                                <input type="color"
                                       name="theme_color"
                                       x-model="themeColor"
                                       @if ($isStarter || !Auth::user()?->isPrimaryAdmin()) disabled @endif
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed">
                            </div>

                            {{-- Kode HEX Display --}}
                            <div class="flex items-center gap-2 bg-stone-100 border border-gray-200 px-3.5 py-2.5 rounded-xl">
                                <span class="text-xs font-mono font-bold text-gray-500">HEX:</span>
                                <input type="text"
                                       x-model="themeColor"
                                       @if ($isStarter || !Auth::user()?->isPrimaryAdmin()) disabled @endif
                                       maxlength="7"
                                       class="text-xs font-mono font-extrabold text-gray-900 uppercase bg-transparent border-0 p-0 focus:ring-0 w-20">
                            </div>

                            {{-- Preset Color Palettes --}}
                            <div class="flex items-center gap-2.5 pl-2 sm:border-l sm:border-gray-200">
                                <span class="text-xs text-gray-400 font-medium hidden sm:inline">Pilihan Cepat:</span>
                                <button type="button"
                                        @click="if(!isStarter) themeColor = '#F97316'"
                                        class="w-7 h-7 rounded-full bg-[#F97316] border border-white shadow-sm hover:scale-110 transition-transform"
                                        title="RentalBase Orange (#F97316)"></button>
                                <button type="button"
                                        @click="if(!isStarter) themeColor = '#1E3A8A'"
                                        class="w-7 h-7 rounded-full bg-[#1E3A8A] border border-white shadow-sm hover:scale-110 transition-transform"
                                        title="Navy Blue (#1E3A8A)"></button>
                                <button type="button"
                                        @click="if(!isStarter) themeColor = '#059669'"
                                        class="w-7 h-7 rounded-full bg-[#059669] border border-white shadow-sm hover:scale-110 transition-transform"
                                        title="Emerald (#059669)"></button>
                                <button type="button"
                                        @click="if(!isStarter) themeColor = '#7C3AED'"
                                        class="w-7 h-7 rounded-full bg-[#7C3AED] border border-white shadow-sm hover:scale-110 transition-transform"
                                        title="Violet (#7C3AED)"></button>
                                <button type="button"
                                        @click="if(!isStarter) themeColor = '#0F172A'"
                                        class="w-7 h-7 rounded-full bg-[#0F172A] border border-white shadow-sm hover:scale-110 transition-transform"
                                        title="Slate Dark (#0F172A)"></button>
                            </div>
                        </div>
                    </div>

                    {{-- Spesifikasi 3: Live Preview "Contoh Tombol" dengan Kontras YIQ Realtime --}}
                    <div class="p-5 rounded-2xl border border-gray-200/90 bg-stone-50/70 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Live Preview Komponen Tombol</span>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    Warna teks (<span class="font-mono font-bold" x-text="getTextColor(themeColor)"></span>) dihitung secara instan menggunakan kalkulasi kontras YIQ untuk memenuhi standar aksesibilitas UI katalog customer.
                                </p>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 bg-white px-2 py-0.5 rounded border border-gray-200">
                                YIQ Contrast
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 pt-1">
                            {{-- Contoh Tombol Utama --}}
                            <button type="button"
                                    class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all duration-200 flex items-center gap-2 cursor-default select-none tracking-wide"
                                    :style="`background-color: ${themeColor}; color: ${getTextColor(themeColor)};`">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Contoh Tombol: Sewa Sekarang</span>
                            </button>

                            {{-- Contoh Badge Status Terkait --}}
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-extrabold border"
                                 :style="`background-color: ${themeColor}15; color: ${themeColor}; border-color: ${themeColor}40;`">
                                <span class="w-1.5 h-1.5 rounded-full" :style="`background-color: ${themeColor};`"></span>
                                <span>Aksen Badge Brand</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Form Actions (Submit & Batal) --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.dashboard') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-stone-50 text-gray-700 text-xs font-bold transition-colors">
                Batal
            </a>

            @if (Auth::user()?->isPrimaryAdmin())
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 active:scale-[0.98] text-white text-xs font-extrabold shadow-md shadow-orange-500/25 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan Profil & Tema</span>
                </button>
            @else
                <button type="button"
                        disabled
                        class="px-6 py-2.5 rounded-xl bg-gray-200 text-gray-400 text-xs font-bold cursor-not-allowed shadow-none"
                        title="Hanya Admin Utama yang berwenang menyimpan profil toko">
                    Simpan Perubahan (Terkunci)
                </button>
            @endif
        </div>
    </form>
</div>

{{-- Alpine.js Application Component --}}
<script>
    function tenantProfileApp(config) {
        return {
            logoPreview: config.initialLogo || null,
            themeColor: config.initialColor || '#F97316',
            isStarter: Boolean(config.isStarter),
            removeLogo: false,
            fileError: '',

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;

                // Batas 2MB (2048 KB)
                if (file.size > 2 * 1024 * 1024) {
                    this.fileError = 'Ukuran file gambar melebihi 2MB. Silakan pilih foto dengan resolusi lebih kecil.';
                    event.target.value = '';
                    return;
                }

                this.fileError = '';
                this.removeLogo = false;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            clearLogo() {
                this.logoPreview = null;
                this.removeLogo = true;
                this.fileError = '';
                if (this.$refs.logoInput) {
                    this.$refs.logoInput.value = '';
                }
            },

            // Formula Kontras Warna YIQ
            // ((r * 299) + (g * 587) + (b * 114)) / 1000
            // Threshold >= 150 (terang -> teks hitam), < 150 (gelap/vibrant -> teks putih)
            getTextColor(hex) {
                if (!hex) return '#ffffff';
                let clean = hex.replace('#', '').trim();

                if (clean.length === 3) {
                    clean = clean[0] + clean[0] + clean[1] + clean[1] + clean[2] + clean[2];
                }

                if (clean.length !== 6) {
                    return '#ffffff';
                }

                const r = parseInt(clean.substring(0, 2), 16);
                const g = parseInt(clean.substring(2, 4), 16);
                const b = parseInt(clean.substring(4, 6), 16);

                if (isNaN(r) || isNaN(g) || isNaN(b)) {
                    return '#ffffff';
                }

                const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;

                return yiq >= 150 ? '#000000' : '#ffffff';
            }
        };
    }
</script>
@endsection
