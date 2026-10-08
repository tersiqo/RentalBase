@extends('layouts.public')

@section('content')

    <!-- Hero Section -->
    <section class="pt-16 pb-20 bg-gray-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row items-center gap-12">
            <!-- Text Content -->
            <div class="flex-1 text-center lg:text-left z-10 pt-8 lg:pt-0">
                <div class="inline-block px-4 py-1.5 bg-orange-100 text-orange-600 rounded-full text-sm font-bold tracking-wide mb-6">
                    BETA RENTALBASE ENGINE
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight mb-6 leading-tight">
                    Kelola Bisnis Rental Peralatan <br class="hidden lg:block" />
                    <span class="text-orange-600">dalam Satu Sistem</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-500 mb-10 max-w-2xl mx-auto lg:mx-0">
                    Sistem multi-tenant untuk manajemen inventaris, booking online, hingga pelacakan pengembalian barang. Serahkan kerumitan operasional pada kami.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="#" class="bg-orange-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-orange-700 transition shadow-xl shadow-orange-500/30 flex items-center justify-center gap-2">
                        Mulai Buka Toko Gratis
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="#" class="bg-white text-gray-700 border-2 border-gray-200 px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-50 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Lihat Demo
                    </a>
                </div>
            </div>
            
            <!-- Hero Image / Mockup -->
            <div class="flex-1 relative w-full max-w-2xl mx-auto">
                <div class="absolute inset-0 bg-gradient-to-tr from-orange-100 to-orange-50 rounded-[2.5rem] transform rotate-3 scale-105 -z-10"></div>
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=1200" alt="Dashboard Preview" class="rounded-2xl shadow-2xl border border-gray-200 w-full object-cover">
                
                <!-- Floating Card Detail -->
                <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-xl border border-gray-100 flex items-center gap-4 animate-bounce" style="animation-duration: 3s;">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Order Masuk</p>
                        <p class="text-sm font-bold text-gray-900">ID ORD-94821 Lunas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LANGKAH MUDAH Section -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="text-orange-600 font-bold tracking-widest text-sm uppercase mb-2">Langkah Mudah</div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">Dari Daftar Sampai Toko Online Aktif</h2>
                <p class="text-gray-500 mt-4 text-lg">Proses onboarding yang praktis, hanya butuh beberapa menit.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-black text-xl">01</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pilih Paket</h3>
                    <p class="text-gray-500 leading-relaxed">Daftar akun gratis dan pilih paket sesuai dengan skala dan kebutuhan bisnis rental Anda.</p>
                </div>
                <!-- Step 2 -->
                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-black text-xl">02</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Upload Produk</h3>
                    <p class="text-gray-500 leading-relaxed">Masukkan detail spesifikasi produk, harga sewa harian, serta stok unit yang tersedia.</p>
                </div>
                <!-- Step 3 -->
                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-black text-xl">03</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Terima Order</h3>
                    <p class="text-gray-500 leading-relaxed">Sistem web storefront aktif. Pelanggan bisa langsung booking dan Anda menerima order.</p>
                </div>
                <!-- Step 4 -->
                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-black text-xl">04</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Tracking Mudah</h3>
                    <p class="text-gray-500 leading-relaxed">Pantau barang keluar, cek kalender pengembalian, dan kelola denda keterlambatan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR UTAMA Section -->
    <section class="py-24 bg-gray-50 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="text-orange-600 font-bold tracking-widest text-sm uppercase mb-2">Fitur Utama</div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">Dibuat Khusus untuk Sewa Barang Fisik</h2>
                <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Tidak seperti e-commerce biasa, kami mendesain platform ini dengan kalender dan durasi khusus rental.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <div class="flex gap-6 p-8 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-16 h-16 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Kelola Ketersediaan Barang</h3>
                        <p class="text-gray-500 leading-relaxed">Sistem mencegah double booking dengan mengecek ketersediaan inventaris berbasis tanggal secara otomatis.</p>
                    </div>
                </div>
                
                <div class="flex gap-6 p-8 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-16 h-16 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Cetak Struk & Invoice</h3>
                        <p class="text-gray-500 leading-relaxed">Buat dokumen sewa dan tanda terima resmi secara otomatis yang dapat dicetak atau dikirim via WhatsApp.</p>
                    </div>
                </div>
                
                <div class="flex gap-6 p-8 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-16 h-16 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Otomatisasi Denda Telat</h3>
                        <p class="text-gray-500 leading-relaxed">Perhitungan otomatis biaya tambahan jika barang dikembalikan melewati batas waktu sewa yang ditentukan.</p>
                    </div>
                </div>

                <div class="flex gap-6 p-8 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-16 h-16 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Verifikasi Pelanggan</h3>
                        <p class="text-gray-500 leading-relaxed">Simpan identitas KTP pelanggan dengan aman dan fitur blacklist untuk menghindari penyewa bermasalah.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WORKFLOW Section -->
    <section class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center gap-16">
            <div class="flex-1">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">Satu Alur, dari Booking sampai Barang Kembali</h2>
                <p class="text-gray-500 mb-8 text-lg">Pekerjaan administrasi dan pencatatan manual sering memakan waktu. Dengan RentalBase, semua tahapan terpusat dan saling terintegrasi dalam dashboard Anda.</p>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-700 font-medium">Approval order praktis</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-700 font-medium">Check-out barang (Pengambilan)</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-700 font-medium">Check-in barang (Pengembalian)</span>
                    </li>
                </ul>
            </div>
            <div class="flex-1 w-full relative">
                <div class="absolute inset-0 bg-orange-100 rounded-[2rem] transform -rotate-3 scale-105 -z-10"></div>
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&q=80&w=800" class="rounded-2xl shadow-xl border border-white" alt="Dashboard Workflow">
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-24 bg-gray-50 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">Pilih Paket Sesuai Skala Usaha Anda</h2>
                <p class="text-gray-500 mt-4 text-lg">Mulai gratis, upgrade kapan saja saat bisnis Anda makin besar.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto items-stretch">
                @foreach ($pricingPlans as $plan)
                    <div class="bg-white rounded-[2rem] p-8 border flex flex-col {{ $plan['highlight'] ? 'border-orange-500 shadow-2xl ring-4 ring-orange-50 relative transform md:-translate-y-4' : 'border-gray-200 shadow-md hover:shadow-lg' }}">
                        @if($plan['highlight'])
                            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-orange-600 to-orange-400 text-white px-6 py-1.5 rounded-full text-sm font-bold tracking-wider shadow-md uppercase">
                                Terlaris
                            </div>
                        @endif
                        
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
                        <p class="text-sm text-gray-500 mb-4">Cocok untuk bisnis berkembang.</p>
                        <div class="text-4xl font-black text-gray-900 my-4">
                            {{ $plan['price'] }} <span class="text-lg text-gray-500 font-medium">/bln</span>
                        </div>
                        
                        <ul class="space-y-4 mb-8 mt-4 flex-1">
                            @foreach ($plan['features'] as $feature)
                            <li class="flex items-start gap-3">
                                <div class="mt-1 bg-orange-100 p-0.5 rounded-full text-orange-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-gray-600 font-medium">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                        
                        <button class="w-full py-4 rounded-xl font-bold transition-all duration-200 {{ $plan['highlight'] ? 'bg-orange-600 text-white hover:bg-orange-700 shadow-lg shadow-orange-500/30' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' }}">
                            Pilih Paket
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-20 bg-orange-600">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-8 leading-tight">Siap Membawa Rental Anda Online?</h2>
            <a href="#" class="inline-block bg-white text-orange-600 font-extrabold px-10 py-5 rounded-xl text-lg hover:bg-gray-50 transition shadow-2xl hover:scale-105 duration-200">
                Buka Toko Sekarang - Gratis
            </a>
            <p class="text-orange-200 mt-6 text-sm">Tidak perlu kartu kredit. Langsung bisa digunakan.</p>
        </div>
    </section>

@endsection
