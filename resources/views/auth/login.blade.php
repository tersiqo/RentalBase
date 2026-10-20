<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk ke Akun — RentalBase</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js fallback -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased selection:bg-orange-500 selection:text-white bg-[#fffaf5] relative">
    
    <!-- Background Decorator Gradients -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-orange-100/60 blur-3xl"></div>
        <div class="absolute top-1/3 -right-32 w-96 h-96 rounded-full bg-orange-200/40 blur-3xl"></div>
        <div class="absolute -bottom-32 left-1/3 w-96 h-96 rounded-full bg-slate-100 blur-3xl"></div>
    </div>

    <div x-data="authApp()" x-cloak class="relative z-10 min-h-screen flex flex-col justify-center items-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">

        <!-- Main Auth Card Container -->
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 sm:p-9 transition-all duration-200">
            
            <!-- Brand Logo & Header -->
            <div class="text-center mb-6">
                <a href="{{ route('landing.home') }}" class="inline-flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black text-xl shadow-md shadow-orange-500/25 group-hover:scale-105 transition-transform duration-200">
                        R
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight text-slate-900 group-hover:text-orange-600 transition-colors">
                        RentalBase
                    </span>
                </a>
                
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-3" x-text="isRegister ? 'Daftar Akun Baru' : 'Masuk ke Akun Anda'">
                    Masuk ke Akun Anda
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1" x-text="isRegister ? 'Buat akun universal untuk bertransaksi di semua toko' : 'Platform Rental Multi-Tenant Terpadu'">
                    Platform Rental Multi-Tenant Terpadu
                </p>
            </div>


            <!-- ======================================================== -->
            <!-- ALERT NOTIFICATIONS (Laravel Sessions & Errors)          -->
            <!-- ======================================================== -->
            @if ($errors->any())
                <div class="mb-5 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
                    <div class="p-1 rounded-full bg-red-100 text-red-600 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-red-900">Akses Masuk Ditolak</p>
                        <p class="mt-0.5 text-red-700">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
                    <div class="p-1 rounded-full bg-red-100 text-red-600 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-red-900">Gagal Masuk Akun</p>
                        <p class="mt-0.5 text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if (session('success') || session('status'))
                <div class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
                    <div class="p-1 rounded-full bg-emerald-100 text-emerald-600 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-emerald-900">Berhasil!</p>
                        <p class="mt-0.5 text-emerald-700">{{ session('success') ?? session('status') }}</p>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-5 rounded-2xl bg-blue-50 border border-blue-200 p-4 text-blue-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- ======================================================== -->
            <!-- 1. FORM MASUK (LOGIN)                                    -->
            <!-- ======================================================== -->
            <div x-show="!isRegister" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                
                <!-- Jenis Akses Masuk: Segmented Control (Admin & Customer) -->
                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilihan Akses Masuk
                    </label>

                    <div class="grid grid-cols-2 p-1.5 bg-slate-100 rounded-2xl gap-1 border border-slate-200/60">
                        <!-- Customer Button -->
                        <button type="button" 
                                @click="selectedRole = 'customer'"
                                :class="selectedRole === 'customer' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-900 font-semibold'"
                                class="py-2.5 px-4 text-xs sm:text-sm rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Customer</span>
                        </button>

                        <!-- Admin Button -->
                        <button type="button" 
                                @click="selectedRole = 'admin'"
                                :class="selectedRole === 'admin' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-900 font-semibold'"
                                class="py-2.5 px-4 text-xs sm:text-sm rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Admin</span>
                        </button>
                    </div>

                    <!-- Micro-helper text based on selected role -->
                    <p class="text-[11px] text-slate-500 mt-1.5 px-1 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 inline-block"></span>
                        <span x-text="selectedRole === 'customer' ? 'Masuk sebagai pelanggan untuk menyewa produk di semua toko rental.' : 'Masuk sebagai Admin Toko Rental atau Owner Platform.'"></span>
                    </p>
                </div>

                <!-- Form Login -->
                <form method="POST" action="{{ route('login') }}" @submit="handleSubmitLogin($event)" novalidate class="space-y-4">
                    @csrf
                    @if(request()->filled('redirect'))
                        <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                    @endif
                    <input type="hidden" name="login_type" x-model="selectedRole" :value="selectedRole">

                    <!-- Input Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Email <span class="text-orange-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                                </svg>
                            </div>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   x-model="loginEmail"
                                   autocomplete="username"
                                   required 
                                   :placeholder="selectedRole === 'customer' ? 'nama@email.com' : 'admin@toko.com atau nama_user'"
                                   :class="loginErrors.email ? 'border-red-500 text-red-900 bg-red-50/20 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 text-slate-900 focus:border-orange-500 focus:ring-orange-500/20'"
                                   class="block w-full pl-10 pr-4 py-2.5 sm:py-3 text-sm bg-white rounded-xl border focus:outline-none focus:ring-3 transition duration-150">
                        </div>

                        <!-- Client / Server Error Message -->
                        <div x-show="loginErrors.email" class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="loginErrors.email"></span>
                        </div>
                        @error('email')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Input Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Password <span class="text-orange-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" 
                                   name="password" 
                                   :type="showPassword ? 'text' : 'password'"
                                   x-model="loginPassword"
                                   autocomplete="current-password"
                                   required 
                                   placeholder="••••••••"
                                   :class="loginErrors.password ? 'border-red-500 text-red-900 bg-red-50/20 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 text-slate-900 focus:border-orange-500 focus:ring-orange-500/20'"
                                   class="block w-full pl-10 pr-11 py-2.5 sm:py-3 text-sm bg-white rounded-xl border focus:outline-none focus:ring-3 transition duration-150">
                            
                            <!-- Toggle Show/Hide Password -->
                            <button type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                    aria-label="Tampilkan atau sembunyikan password">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Error Message for Password -->
                        <div x-show="loginErrors.password" class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="loginErrors.password"></span>
                        </div>
                        @error('password')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Lain-lain: Checkbox Ingat Saya (Kiri) dan Link Lupa Password (Kanan) -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-xs sm:text-sm text-slate-600 hover:text-slate-900 font-medium">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500 border-slate-300 transition duration-150">
                            <span>Ingat Saya</span>
                        </label>

                        <a href="{{ route('password.request') }}" class="text-xs sm:text-sm font-bold text-orange-600 hover:text-orange-700 hover:underline transition-colors">
                            Lupa password?
                        </a>
                    </div>

                    <!-- Tombol Action: Masuk (Lebar penuh / Full Width) -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="isSubmittingLogin"
                                :class="isSubmittingLogin ? 'opacity-75 cursor-not-allowed bg-orange-600' : 'bg-orange-500 hover:bg-orange-600 active:scale-[0.99] shadow-md shadow-orange-500/20'"
                                class="w-full text-white font-bold py-3.5 px-6 rounded-xl transition duration-150 flex items-center justify-center gap-2 text-sm sm:text-base">
                            
                            <!-- Normal state content -->
                            <span x-show="!isSubmittingLogin" class="inline-flex items-center justify-center gap-2">
                                <span>Masuk</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </span>

                            <!-- Loading state with spinner -->
                            <span x-show="isSubmittingLogin" class="inline-flex items-center justify-center gap-2.5">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Memproses Masuk...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Divider & Google Button -->
                <div class="relative my-6 text-center">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-slate-400 font-semibold tracking-wider">
                            atau masuk dengan
                        </span>
                    </div>
                </div>

                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 py-2.5 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm shadow-xs transition duration-150">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M23.5 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.45a5.52 5.52 0 01-2.39 3.62v3h3.87c2.27-2.09 3.57-5.17 3.57-8.81z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.91l-3.87-3c-1.07.72-2.45 1.15-4.06 1.15-3.12 0-5.77-2.11-6.71-4.95H1.29v3.1A12 12 0 0012 24z"/>
                        <path fill="#FBBC05" d="M5.29 14.29a7.2 7.2 0 010-4.58v-3.1H1.29a12 12 0 000 10.78l4-3.1z"/>
                        <path fill="#EA4335" d="M12 4.76c1.76 0 3.34.61 4.58 1.8l3.43-3.43C17.95 1.19 15.24 0 12 0A12 12 0 001.29 6.61l4 3.1C6.23 6.87 8.88 4.76 12 4.76z"/>
                    </svg>
                    <span>Masuk dengan Akun Google</span>
                </a>

                <!-- Tautan Daftar Baru di Bawah Tombol Masuk -->
                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs sm:text-sm text-slate-600 font-medium">
                        Belum punya akun RentalBase?
                        <button type="button" 
                                @click="isRegister = true"
                                class="font-bold text-orange-600 hover:text-orange-700 hover:underline ml-1 cursor-pointer">
                            Daftar Sekarang
                        </button>
                    </p>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- 2. FORM DAFTAR BARU (REGISTER)                           -->
            <!-- ======================================================== -->
            <div x-show="isRegister" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <form method="POST" action="{{ route('register.submit') }}" @submit="handleSubmitRegister($event)" novalidate class="space-y-4">
                    @csrf
                    
                    <!-- NOTE KEAMANAN: Form pendaftaran ini TIDAK menyertakan input untuk role, status, atau client_id -->

                    <!-- 1. Input Nama Lengkap -->
                    <div>
                        <label for="register-name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Lengkap <span class="text-orange-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input id="register-name" 
                                   name="name" 
                                   type="text" 
                                   x-model="registerName"
                                   value="{{ old('name') }}"
                                   required 
                                   placeholder="Contoh: Budi Santoso"
                                   :class="registerErrors.name ? 'border-red-500 text-red-900 bg-red-50/20 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 text-slate-900 focus:border-orange-500 focus:ring-orange-500/20'"
                                   class="block w-full pl-10 pr-4 py-2.5 sm:py-3 text-sm bg-white rounded-xl border focus:outline-none focus:ring-3 transition duration-150">
                        </div>

                        <div x-show="registerErrors.name" class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="registerErrors.name"></span>
                        </div>
                        @error('name')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 2. Input Email -->
                    <div>
                        <label for="register-email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Email <span class="text-orange-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input id="register-email" 
                                   name="email" 
                                   type="email" 
                                   x-model="registerEmail"
                                   value="{{ old('email') }}"
                                   required 
                                   placeholder="nama@email.com"
                                   :class="registerErrors.email ? 'border-red-500 text-red-900 bg-red-50/20 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 text-slate-900 focus:border-orange-500 focus:ring-orange-500/20'"
                                   class="block w-full pl-10 pr-4 py-2.5 sm:py-3 text-sm bg-white rounded-xl border focus:outline-none focus:ring-3 transition duration-150">
                        </div>

                        <div x-show="registerErrors.email" class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="registerErrors.email"></span>
                        </div>
                        @error('email')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 3. Input No. WhatsApp -->
                    <div>
                        <label for="register-phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            No. WhatsApp <span class="text-orange-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <input id="register-phone" 
                                   name="phone" 
                                   type="tel" 
                                   x-model="registerPhone"
                                   value="{{ old('phone') }}"
                                   required 
                                   placeholder="Contoh: 081234567890"
                                   :class="registerErrors.phone ? 'border-red-500 text-red-900 bg-red-50/20 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 text-slate-900 focus:border-orange-500 focus:ring-orange-500/20'"
                                   class="block w-full pl-10 pr-4 py-2.5 sm:py-3 text-sm bg-white rounded-xl border focus:outline-none focus:ring-3 transition duration-150">
                        </div>

                        <div x-show="registerErrors.phone" class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="registerErrors.phone"></span>
                        </div>
                        @error('phone')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 4. Input Password -->
                    <div>
                        <label for="register-password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Password <span class="text-orange-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="register-password" 
                                   name="password" 
                                   :type="showRegisterPassword ? 'text' : 'password'"
                                   x-model="registerPassword"
                                   required 
                                   placeholder="Minimal 8 karakter"
                                   :class="registerErrors.password ? 'border-red-500 text-red-900 bg-red-50/20 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 text-slate-900 focus:border-orange-500 focus:ring-orange-500/20'"
                                   class="block w-full pl-10 pr-11 py-2.5 sm:py-3 text-sm bg-white rounded-xl border focus:outline-none focus:ring-3 transition duration-150">
                            
                            <button type="button" 
                                    @click="showRegisterPassword = !showRegisterPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                    aria-label="Tampilkan atau sembunyikan password">
                                <svg x-show="!showRegisterPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showRegisterPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="registerErrors.password" class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="registerErrors.password"></span>
                        </div>
                        @error('password')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- 5. Input Konfirmasi Password -->
                    <div>
                        <label for="register-password-confirm" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Konfirmasi Password <span class="text-orange-500">*</span>
                        </label>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input id="register-password-confirm" 
                                   name="password_confirmation" 
                                   :type="showRegisterConfirmPassword ? 'text' : 'password'"
                                   x-model="registerPasswordConfirm"
                                   required 
                                   placeholder="Ulangi password Anda"
                                   :class="registerErrors.password_confirmation ? 'border-red-500 text-red-900 bg-red-50/20 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 text-slate-900 focus:border-orange-500 focus:ring-orange-500/20'"
                                   class="block w-full pl-10 pr-11 py-2.5 sm:py-3 text-sm bg-white rounded-xl border focus:outline-none focus:ring-3 transition duration-150">
                            
                            <button type="button" 
                                    @click="showRegisterConfirmPassword = !showRegisterConfirmPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                    aria-label="Tampilkan atau sembunyikan konfirmasi password">
                                <svg x-show="!showRegisterConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showRegisterConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="registerErrors.password_confirmation" class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 font-medium">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="registerErrors.password_confirmation"></span>
                        </div>
                    </div>

                    <!-- Security Rule Indicator Micro-copy -->
                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3 text-[11px] text-slate-500 leading-relaxed flex items-start gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>
                            Keamanan terjamin: Akun baru otomatis didaftarkan sebagai Customer universal yang dapat langsung digunakan menyewa di seluruh tenant.
                        </span>
                    </div>

                    <!-- Tombol Action: Daftar (Lebar Penuh / Full Width) -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="isSubmittingRegister"
                                :class="isSubmittingRegister ? 'opacity-75 cursor-not-allowed bg-orange-600' : 'bg-orange-500 hover:bg-orange-600 active:scale-[0.99] shadow-md shadow-orange-500/20'"
                                class="w-full text-white font-bold py-3.5 px-6 rounded-xl transition duration-150 flex items-center justify-center gap-2 text-sm sm:text-base">
                            
                            <!-- Normal state content -->
                            <span x-show="!isSubmittingRegister" class="inline-flex items-center justify-center gap-2">
                                <span>Daftar Akun</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </span>

                            <!-- Loading state with spinner -->
                            <span x-show="isSubmittingRegister" class="inline-flex items-center justify-center gap-2.5">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Mendaftarkan Akun...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Tautan Kembali ke Form Masuk -->
                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs sm:text-sm text-slate-600 font-medium">
                        Sudah memiliki akun RentalBase?
                        <button type="button" 
                                @click="isRegister = false"
                                class="font-bold text-orange-600 hover:text-orange-700 hover:underline ml-1 cursor-pointer">
                            Masuk di sini
                        </button>
                    </p>
                    
                    <div class="mt-3">
                        <a href="{{ route('tenant.register') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-orange-600 transition-colors">
                            <span>Ingin membuka toko rental baru? Daftar Tenant</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Back Link & Security Badge -->
        <div class="w-full max-w-lg mt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2 px-2">
            <a href="{{ route('landing.home') }}" class="flex items-center gap-1.5 hover:text-slate-900 font-semibold transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>

            <div class="flex items-center gap-1 text-slate-400">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Enkripsi SSL 256-bit Multi-Tenant</span>
            </div>
        </div>

    </div>

    <!-- Interactive Alpine.js Logic -->
    <script>
        function authApp() {
            return {
                // Switch between login and register form
                isRegister: {{ (isset($activeTab) && $activeTab === 'register') || request('tab') === 'register' ? 'true' : 'false' }},
                
                // Login Role Selector: 'customer' or 'admin'
                selectedRole: '{{ old("login_type", "customer") }}',
                
                // Password visibility toggles
                showPassword: false,
                showRegisterPassword: false,
                showRegisterConfirmPassword: false,
                
                // Form field values
                loginEmail: '{{ old("email", "") }}',
                loginPassword: '',
                registerName: '{{ old("name", "") }}',
                registerEmail: '{{ old("email", "") }}',
                registerPhone: '{{ old("phone", "") }}',
                registerPassword: '',
                registerPasswordConfirm: '',

                // Client error states
                loginErrors: {},
                registerErrors: {},

                // Submitting states
                isSubmittingLogin: false,
                isSubmittingRegister: false,

                handleSubmitLogin(e) {
                    this.loginErrors = {};
                    if (!this.loginEmail || !this.loginEmail.trim()) {
                        this.loginErrors.email = 'Email atau username tidak boleh kosong.';
                    }
                    if (!this.loginPassword) {
                        this.loginErrors.password = 'Password tidak boleh kosong.';
                    }

                    if (Object.keys(this.loginErrors).length > 0) {
                        e.preventDefault();
                        return;
                    }

                    this.isSubmittingLogin = true;
                },

                handleSubmitRegister(e) {
                    this.registerErrors = {};
                    if (!this.registerName || !this.registerName.trim()) {
                        this.registerErrors.name = 'Nama lengkap wajib diisi.';
                    }
                    if (!this.registerEmail || !this.registerEmail.includes('@')) {
                        this.registerErrors.email = 'Alamat email valid wajib diisi.';
                    }
                    if (!this.registerPhone || this.registerPhone.trim().length < 9) {
                        this.registerErrors.phone = 'Nomor WhatsApp aktif minimal 10 digit.';
                    }
                    if (!this.registerPassword || this.registerPassword.length < 8) {
                        this.registerErrors.password = 'Password minimal terdiri dari 8 karakter.';
                    }
                    if (this.registerPassword !== this.registerPasswordConfirm) {
                        this.registerErrors.password_confirmation = 'Konfirmasi password tidak cocok.';
                    }

                    if (Object.keys(this.registerErrors).length > 0) {
                        e.preventDefault();
                        return;
                    }

                    this.isSubmittingRegister = true;
                }
            }
        }
    </script>
</body>
</html>