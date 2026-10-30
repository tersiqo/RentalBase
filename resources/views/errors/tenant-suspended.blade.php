<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($client) ? ($client->nama_usaha ?? $client->business_name) . ' - ' : '' }}Toko Ditangguhkan | RentalBase</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/rentalbase-icon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS / Vite -->
    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex items-center justify-center p-4 sm:p-6 relative overflow-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Ambient Subtle Background Decor (Tetap bersih bernuansa slate-50) -->
    <div class="absolute inset-0 -z-10 pointer-events-none flex items-center justify-center overflow-hidden">
        <div class="w-[500px] h-[500px] rounded-full bg-gradient-to-tr from-indigo-100/60 to-slate-200/50 blur-3xl opacity-70"></div>
    </div>

    <!-- Container Card (Persis di tengah layar - Vertikal & Horizontal) -->
    <main class="w-full max-w-lg bg-white rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/60 p-8 sm:p-10 text-center flex flex-col items-center gap-6 relative z-10 transition-all">

        <!-- ========================================================= -->
        <!-- Ikon SVG Modern: Toko Tutup & Gembok Pengaman (Security)  -->
        <!-- ========================================================= -->
        <div class="relative flex items-center justify-center size-24 rounded-3xl bg-indigo-50 text-indigo-600 ring-8 ring-indigo-50/60 shadow-inner">
            <!-- SVG Toko dengan Gembok Pengaman Modern -->
            <svg xmlns="http://www.w3.org/2000/svg" 
                 viewBox="0 0 24 24" 
                 fill="none" 
                 stroke="currentColor" 
                 stroke-width="1.6" 
                 stroke-linecap="round" 
                 stroke-linejoin="round" 
                 class="size-12">
                <!-- Kanopi Toko (Store Awning) -->
                <path d="M3 9l1.5-5.5A1 1 0 0 1 5.5 3h13a1 1 0 0 1 1 .5L21 9" />
                <path d="M3 9a2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 3 0" />
                <!-- Dinding Toko -->
                <path d="M4 11.5V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.5" />
                <!-- Gembok Pengaman (Padlock) di tengah pintu toko -->
                <rect x="9.5" y="14" width="5" height="4" rx="1" fill="currentColor" fill-opacity="0.15" stroke-width="1.6" />
                <path d="M10.5 14v-1.5a1.5 1.5 0 0 1 3 0V14" />
            </svg>

            <!-- Badge Status Mini di sudut ikon -->
            <span class="absolute -top-1 -right-1 flex size-5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full size-5 bg-amber-500 border-2 border-white items-center justify-center">
                    <svg class="size-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </span>
            </span>
        </div>

        <!-- Tag Status, Headline & Paragraf Deskripsi -->
        <div class="flex flex-col items-center gap-3 text-center">
            @if(isset($client))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide bg-amber-50 text-amber-800 border border-amber-200/80">
                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                    {{ $client->nama_usaha ?? ($client->business_name ?? 'Toko Rental') }}
                </span>
            @endif

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                Toko Sewa Sedang Ditangguhkan
            </h1>

            <p class="text-sm sm:text-base text-slate-600 text-center leading-relaxed max-w-md">
                Mohon maaf, operasional toko ini sedang dinonaktifkan sementara oleh pengelola platform. Navigasi dan transaksi sewa tidak dapat dilakukan saat ini.
            </p>
        </div>

        <!-- Box Informasi Tambahan yang Sopan -->
        <div class="w-full bg-slate-50/80 rounded-2xl border border-slate-200/60 p-4 text-xs sm:text-sm text-slate-500 flex flex-col gap-2 text-left">
            <div class="flex items-start gap-2.5">
                <svg class="size-4 text-slate-400 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <div class="space-y-1">
                    <p class="font-medium text-slate-700">Mengapa halaman ini muncul?</p>
                    <p class="leading-relaxed">
                        Penangguhan dapat terjadi karena masa aktif langganan toko telah berakhir atau sedang dilakukan tinjauan kebijakan operasional. Seluruh data dan transaksi sebelumnya tetap aman.
                    </p>
                </div>
            </div>

            @if(isset($client->subdomain))
                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                    <span>Domain Toko:</span>
                    <span class="font-mono font-medium text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">
                        {{ $client->subdomain }}.rentalbase.id
                    </span>
                </div>
            @endif
        </div>

        <!-- Notifikasi Khusus Pemilik Toko (Jika Sedang Login Sebagai Admin Tenant) -->
        @auth
            @if(auth()->user()->role === 'admin_rental' || (isset($client) && auth()->user()->client_id == $client->id))
                <div class="w-full bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 text-left text-xs text-indigo-950 flex flex-col gap-1.5">
                    <span class="font-bold flex items-center gap-1.5 text-indigo-700">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pemberitahuan Khusus Pemilik Toko
                    </span>
                    <p class="leading-relaxed text-indigo-900/80">
                        Akun admin Anda masih terdaftar. Untuk mengaktifkan kembali etalase dan operasional toko, silakan lakukan perpanjangan paket langganan atau hubungi Owner Platform RentalBase.
                    </p>
                </div>
            @endif
        @endauth

        <!-- Tombol Aksi / Navigasi -->
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full pt-1">
            <a href="{{ route('landing.home') }}" 
               class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold transition shadow-sm shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Kembali ke RentalBase
            </a>
            
            <a href="mailto:support@rentalbase.id?subject=Bantuan%20Penangguhan%20Toko{{ isset($client) ? '%20-%20' . rawurlencode($client->nama_usaha ?? $client->business_name) : '' }}" 
               class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-slate-300">
                <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                Hubungi Bantuan
            </a>
        </div>

        <!-- Footer Kecil / Platform Branding -->
        <p class="text-xs text-slate-400 pt-1">
            Didukung oleh <a href="{{ route('landing.home') }}" class="font-semibold text-slate-600 hover:text-indigo-600 transition">RentalBase</a> &bull; Ekosistem Rental Terpercaya
        </p>

    </main>

</body>
</html>

