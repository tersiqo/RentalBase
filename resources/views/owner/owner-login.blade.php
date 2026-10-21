<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Masuk Owner Platform — RentalBase</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/rentalbase-icon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="h-full text-slate-100 antialiased selection:bg-orange-500 selection:text-white bg-[#0f172a] relative overflow-x-hidden">

    <!-- Ambient Glow / Background Visuals -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-orange-500/10 blur-[120px]"></div>
        <div class="absolute top-1/2 -right-40 w-[600px] h-[600px] rounded-full bg-amber-500/10 blur-[140px]"></div>
        <div class="absolute -bottom-40 left-1/3 w-[500px] h-[500px] rounded-full bg-slate-800/40 blur-[100px]"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col justify-center items-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">

        <!-- Top Navigation / Back to Home -->
        <div class="w-full max-w-md mb-6 flex items-center justify-between">
            <a href="{{ route('landing.home') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors group">
                <svg class="w-4 h-4 text-slate-400 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase bg-orange-500/15 text-orange-400 border border-orange-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-400 animate-pulse"></span>
                Owner Portal
            </span>
        </div>

        <!-- Main Card -->
        <div class="w-full max-w-md bg-slate-800/70 backdrop-blur-xl rounded-3xl shadow-2xl shadow-black/40 border border-slate-700/60 p-6 sm:p-9 transition-all">

            <!-- Brand Header -->
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white font-black text-2xl shadow-lg shadow-orange-500/30 mb-4">
                    R
                </div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">
                    Portal Owner Platform
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5">
                    Akses kontrol pusat & pemantauan seluruh tenant RentalBase
                </p>
            </div>

            <!-- Flash Alerts -->
            @if ($errors->any())
                <div class="mb-5 rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-rose-200 text-xs sm:text-sm flex items-start gap-3">
                    <div class="p-1 rounded-full bg-rose-500/20 text-rose-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-rose-300">Gagal Masuk</p>
                        <p class="mt-0.5 text-rose-200">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 text-rose-200 text-xs sm:text-sm flex items-start gap-3">
                    <div class="p-1 rounded-full bg-rose-500/20 text-rose-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-rose-300">Peringatan</p>
                        <p class="mt-0.5 text-rose-200">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 p-4 text-emerald-200 text-xs sm:text-sm flex items-start gap-3">
                    <div class="p-1 rounded-full bg-emerald-500/20 text-emerald-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-emerald-300">Berhasil</p>
                        <p class="mt-0.5 text-emerald-200">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-5 rounded-2xl bg-blue-500/15 border border-blue-500/30 p-4 text-blue-200 text-xs sm:text-sm flex items-start gap-3">
                    <div class="p-1 rounded-full bg-blue-500/20 text-blue-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-blue-300">Informasi</p>
                        <p class="mt-0.5 text-blue-200">{{ session('info') }}</p>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('owner.login.submit') }}" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Email / Username Owner <span class="text-orange-400">*</span>
                    </label>
                    <div class="relative rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                            </svg>
                        </div>
                        <input id="email" 
                               name="email" 
                               type="text" 
                               value="{{ old('email') }}"
                               required 
                               autofocus
                               placeholder="owner@rentalbase.id" 
                               class="block w-full pl-10 pr-4 py-2.5 sm:py-3 text-sm bg-slate-900/80 text-white placeholder-slate-500 rounded-xl border border-slate-700 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 transition duration-150">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Password <span class="text-orange-400">*</span>
                    </label>
                    <div class="relative rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               placeholder="••••••••" 
                               class="block w-full pl-10 pr-4 py-2.5 sm:py-3 text-sm bg-slate-900/80 text-white placeholder-slate-500 rounded-xl border border-slate-700 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 transition duration-150">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-orange-500 focus:ring-orange-500/30">
                        <span class="text-xs text-slate-400">Ingat sesi saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/25 transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer">
                        <span>Masuk ke Dashboard Owner</span>
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

        </div>

        <!-- Security Badge -->
        <div class="mt-6 text-center">
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                </svg>
                RentalBase Platform Security &bull; Area Terbatas Super Admin
            </span>
        </div>
    </div>
</body>
</html>
