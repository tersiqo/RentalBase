@extends('layouts.admin')

@section('title', 'Pengaturan Tema Tampilan')
@section('page-title', 'Tema Tampilan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-5">
            <h2 class="text-lg font-bold text-gray-900">Tema Tampilan</h2>
            <p class="text-sm text-gray-500 mt-1">Sesuaikan warna utama aplikasi untuk mempresentasikan brand Anda.</p>
        </div>

        <div class="p-6">
            <div class="mb-8">
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Subscription Anda</h3>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-sm font-bold uppercase tracking-wide">
                    {{ $subscription->plan_name ?? 'STARTER' }}
                </div>
            </div>

            @php
                $plan = strtolower($subscription->plan_name ?? 'starter');
                $isLocked = ($plan === 'starter' && !empty($client->theme_color));
                $currentColor = $client->theme_color ?? '#F97316';
            @endphp

            <div class="mb-8">
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">Tema Saat Ini</h3>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full shadow-sm border border-gray-200" style="background-color: {{ $currentColor }};"></div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 uppercase">{{ $currentColor }}</p>
                        <p class="text-xs text-gray-500 font-medium">Warna Utama</p>
                    </div>
                </div>
            </div>

            @if($isLocked)
                <div class="bg-stone-50 border border-stone-200 rounded-xl p-5 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 text-stone-200/50">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a5 5 0 00-5 5v2H6a2 2 0 00-2 2v11a2 2 0 002 2h12a2 2 0 002-2V10a2 2 0 00-2-2h-1V6a5 5 0 00-5-5zm3 7V6a3 3 0 10-6 0v2h6z"/></svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <h3 class="text-base font-bold text-gray-900">Tema Terkunci</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4 max-w-lg">Tema telah ditentukan saat pendaftaran awal. Upgrade subscription ke <strong>Business</strong> atau <strong>Professional</strong> untuk membuka akses perubahan tema tak terbatas.</p>
                        <button class="bg-gray-900 text-white hover:bg-gray-800 transition-colors px-4 py-2 rounded-lg text-sm font-semibold shadow-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Upgrade Subscription
                        </button>
                    </div>
                </div>
            @else
                <form action="{{ route('admin.theme.update') }}" method="POST" x-data="{ theme: '{{ $currentColor }}' }" class="border-t border-gray-100 pt-8 mt-4">
                    @csrf
                    
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Pilih Warna Baru</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-3">Preset Color</p>
                            <div class="flex gap-4">
                                <button type="button" @click="theme = '#F97316'" :class="theme === '#F97316' ? 'ring-2 ring-offset-2 ring-orange-500' : ''" class="w-10 h-10 rounded-full transition-all bg-[#F97316] hover:scale-110 shadow-sm border border-gray-200" title="Orange"></button>
                                <button type="button" @click="theme = '#2563EB'" :class="theme === '#2563EB' ? 'ring-2 ring-offset-2 ring-blue-500' : ''" class="w-10 h-10 rounded-full transition-all bg-[#2563EB] hover:scale-110 shadow-sm border border-gray-200" title="Blue"></button>
                                <button type="button" @click="theme = '#16A34A'" :class="theme === '#16A34A' ? 'ring-2 ring-offset-2 ring-green-500' : ''" class="w-10 h-10 rounded-full transition-all bg-[#16A34A] hover:scale-110 shadow-sm border border-gray-200" title="Green"></button>
                                <button type="button" @click="theme = '#9333EA'" :class="theme === '#9333EA' ? 'ring-2 ring-offset-2 ring-purple-500' : ''" class="w-10 h-10 rounded-full transition-all bg-[#9333EA] hover:scale-110 shadow-sm border border-gray-200" title="Purple"></button>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-3">Warna Custom</p>
                            <div class="flex items-center gap-3">
                                <div class="relative w-12 h-12 rounded-xl overflow-hidden border border-gray-300 shadow-sm focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-gray-900" :style="`background-color: ${theme};`">
                                    <input type="color" name="theme_color" x-model="theme" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                    <span class="text-sm font-mono font-bold text-gray-700 uppercase" x-text="theme"></span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Klik kotak warna di atas untuk memilih warna spesifik brand Anda.</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="bg-orange-500 text-white hover:bg-orange-600 transition-colors px-6 py-2.5 rounded-xl text-sm font-semibold shadow-sm shadow-orange-500/30">
                            Simpan Tema
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</div>
@endsection
