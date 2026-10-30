<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | RentalBase</title>
    
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
    <main class="w-full max-w-lg bg-white rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/60 p-6 sm:p-10 text-center flex flex-col items-center gap-6 relative z-10 transition-all">

        <!-- ========================================================= -->
        <!-- Ikon SVG Modern: Kaca Pembesar & Pencarian 404            -->
        <!-- ========================================================= -->
        <div class="relative flex items-center justify-center size-24 rounded-3xl bg-indigo-50 text-indigo-600 ring-8 ring-indigo-50/60 shadow-inner">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 fill="none" 
                 viewBox="0 0 24 24" 
                 stroke-width="1.6" 
                 stroke="currentColor" 
                 class="size-12">
                <!-- Lingkaran Kaca Pembesar -->
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                <!-- Simbol Tanda Tanya / Titik Fokus di Dalam Kaca Pembesar -->
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 8.25a2.25 2.25 0 0 1 2.25 2.25c0 1.5-1.5 2.25-1.5 2.25m0 2.25h.008v.008H10.75" />
            </svg>

            <!-- Badge Mini Status Code 404 -->
            <span class="absolute -top-1.5 -right-1.5 flex">
                <span class="relative inline-flex rounded-full px-2 py-0.5 text-[10px] font-extrabold bg-indigo-600 text-white border-2 border-white shadow-sm">
                    404
                </span>
            </span>
        </div>

        <!-- Headline & Paragraf Deskripsi -->
        <div class="flex flex-col items-center gap-3 text-center">
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight text-center">
                404 — Halaman Tidak Ditemukan
            </h1>

            <p class="text-sm sm:text-base text-slate-600 text-center leading-relaxed max-w-md">
                Maaf, halaman, produk, atau tautan yang Anda tuju mungkin telah dihapus, dipindahkan, atau salah ketik URL.
            </p>
        </div>

        <!-- Tombol Call-to-Action (CTA) -->
        <div class="w-full flex flex-col sm:flex-row items-center justify-center gap-3 pt-1">
            <a href="/" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-lg shadow-sm shadow-indigo-200 transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-sm sm:text-base">
                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Kembali ke Beranda
            </a>

            <button type="button" 
                    onclick="window.history.back()" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-slate-300 text-sm sm:text-base">
                <svg class="size-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Halaman Sebelumnya
            </button>
        </div>

        <!-- Footer Kecil / Platform Branding -->
        <p class="text-xs text-slate-400 pt-1">
            Didukung oleh <a href="{{ route('landing.home') }}" class="font-semibold text-slate-600 hover:text-indigo-600 transition">RentalBase</a> &bull; Ekosistem Rental Terpercaya
        </p>

    </main>

</body>
</html>
