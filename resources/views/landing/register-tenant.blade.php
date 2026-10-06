<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tenant - RentalBase</title>
    
    <!-- CDN Links -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#f97316',
                            hover: '#ea580c',
                            light: '#ffedd5',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* Custom Form Controls */
        .form-control {
            @apply w-full border border-gray-300 rounded-lg shadow-sm focus:border-brand focus:ring-4 focus:ring-brand/20 px-4 py-3 outline-none transition duration-200 bg-gray-50 focus:bg-white;
        }
        .form-label {
            @apply block text-sm font-semibold text-gray-700 mb-1.5;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen flex flex-col selection:bg-brand selection:text-white">
    
    <!-- NAVBAR -->
    <nav class="bg-white border-b border-gray-100 h-20 flex items-center shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <a href="/" class="flex-shrink-0 flex items-center gap-2 w-max group">
                <div class="w-10 h-10 bg-brand text-white rounded-lg flex items-center justify-center text-xl font-bold group-hover:bg-brand-hover transition">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <span class="font-extrabold text-2xl text-gray-900 tracking-tight">Rental<span class="text-brand">Base</span></span>
            </a>
        </div>
    </nav>
    <!-- NAVBAR END -->

    <!-- REGISTRATION FORM START -->
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl w-full" x-data="registrationForm()">
            
            <div class="text-center mb-10">
                <h1 class="text-3xl font-extrabold text-gray-900">Buka Toko Anda</h1>
                <p class="mt-3 text-gray-600 text-lg">Lengkapi data di bawah ini untuk memulai digitalisasi bisnis Anda.</p>
            </div>

            <!-- Step Indicator -->
            <div class="mb-10">
                <div class="flex items-center justify-center">
                    <div class="flex items-center w-full max-w-md">
                        <!-- Step 1 Indicator -->
                        <div class="relative flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg shadow-sm transition-all duration-300"
                                 :class="step >= 1 ? 'bg-brand text-white ring-4 ring-brand-light' : 'bg-white text-gray-400 border-2 border-gray-200'">
                                1
                            </div>
                            <div class="absolute top-14 text-sm font-bold whitespace-nowrap"
                                 :class="step >= 1 ? 'text-gray-900' : 'text-gray-400'">Info Usaha</div>
                        </div>
                        
                        <!-- Line separator -->
                        <div class="flex-1 h-1.5 mx-4 rounded-full transition-all duration-500"
                             :class="step >= 2 ? 'bg-brand' : 'bg-gray-200'"></div>
                        
                        <!-- Step 2 Indicator -->
                        <div class="relative flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg shadow-sm transition-all duration-300"
                                 :class="step >= 2 ? 'bg-brand text-white ring-4 ring-brand-light' : 'bg-white text-gray-400 border-2 border-gray-200'">
                                2
                            </div>
                            <div class="absolute top-14 text-sm font-bold whitespace-nowrap"
                                 :class="step >= 2 ? 'text-gray-900' : 'text-gray-400'">Info Pemilik</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white py-10 px-6 shadow-2xl rounded-3xl sm:px-12 border border-gray-100 mt-14 relative overflow-hidden">
                <!-- Dekoratif Card -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-bl-full -z-10"></div>

                <form @submit.prevent="submitForm">
                    <!-- Token (optional placeholder for Laravel csrf) -->
                    @csrf
                    
                    <!-- ========================================= -->
                    <!-- STEP 1: Info Usaha -->
                    <!-- ========================================= -->
                    <div x-show="step === 1" 
                         x-transition:enter="transition ease-out duration-300 delay-100" 
                         x-transition:enter-start="opacity-0 transform translate-x-4" 
                         x-transition:enter-end="opacity-100 transform translate-x-0" 
                         x-cloak>
                        
                        <h3 class="text-2xl font-bold text-gray-900 mb-8 border-b pb-4">Informasi Toko / Usaha</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="form-label">Nama Usaha/Toko <span class="text-red-500">*</span></label>
                                <input type="text" x-model="form.storeName" name="store_name" class="form-control" placeholder="Contoh: Kamera Sewa Jakarta" required>
                            </div>
                            
                            <!-- Input Khusus Subdomain -->
                            <div>
                                <label class="form-label">Subdomain Toko <span class="text-red-500">*</span></label>
                                <div class="flex rounded-lg shadow-sm relative group">
                                    <input type="text" x-model="form.subdomain" name="subdomain" @input="checkSubdomain" class="form-control rounded-r-none border-r-0 focus:z-10 group-hover:border-gray-400" placeholder="namatoko" required>
                                    <span class="inline-flex items-center px-4 rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 text-gray-600 font-medium sm:text-sm group-hover:border-gray-400 transition">
                                        .rentalbase.com
                                    </span>
                                </div>
                                <!-- Real-time validation UI -->
                                <div class="mt-2 text-sm h-6 font-medium">
                                    <span x-show="subdomainStatus === 'checking'" class="text-gray-500"><i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Memeriksa ketersediaan...</span>
                                    <span x-show="subdomainStatus === 'available'" class="text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md"><i class="fa-solid fa-check-circle mr-1"></i> Tersedia!</span>
                                    <span x-show="subdomainStatus === 'taken'" class="text-red-600 bg-red-50 px-2 py-1 rounded-md"><i class="fa-solid fa-xmark-circle mr-1"></i> Tidak tersedia, pilih nama lain</span>
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Deskripsi Singkat Usaha <span class="text-red-500">*</span></label>
                                <textarea x-model="form.description" name="description" rows="3" class="form-control resize-none" placeholder="Jelaskan secara singkat tentang barang yang Anda sewakan..." required></textarea>
                            </div>
                        </div>

                        <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
                            <button type="button" @click="nextStep" class="bg-brand text-white px-8 py-3.5 rounded-xl font-bold hover:bg-brand-hover transition shadow-lg shadow-orange-200/50 flex items-center disabled:opacity-50 disabled:cursor-not-allowed" 
                                    :disabled="subdomainStatus === 'taken' || !form.storeName || !form.subdomain">
                                Selanjutnya <i class="fa-solid fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ========================================= -->
                    <!-- STEP 2: Info PIC / Pemilik -->
                    <!-- ========================================= -->
                    <div x-show="step === 2" 
                         x-transition:enter="transition ease-out duration-300 delay-100" 
                         x-transition:enter-start="opacity-0 transform translate-x-4" 
                         x-transition:enter-end="opacity-100 transform translate-x-0" 
                         x-cloak>
                        
                        <h3 class="text-2xl font-bold text-gray-900 mb-8 border-b pb-4">Data PIC / Pemilik</h3>
                        
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="form.fullName" name="name" class="form-control" placeholder="John Doe" required>
                                </div>
                                <div>
                                    <label class="form-label">No. WhatsApp Aktif <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm font-medium">+62</span>
                                        </div>
                                        <input type="tel" x-model="form.whatsapp" name="whatsapp" class="form-control pl-12" placeholder="81234567890" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="form-label">Alamat Email <span class="text-red-500">*</span></label>
                                <input type="email" x-model="form.email" name="email" class="form-control" placeholder="nama@email.com" required>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                                    <input type="password" x-model="form.password" name="password" class="form-control" required>
                                </div>
                                <div>
                                    <label class="form-label">Konfirmasi Password <span class="text-red-500">*</span></label>
                                    <input type="password" x-model="form.passwordConfirm" name="password_confirmation" class="form-control" :class="(form.password !== form.passwordConfirm && form.passwordConfirm !== '') ? 'border-red-500 focus:ring-red-200' : ''" required>
                                    <span x-show="form.password !== form.passwordConfirm && form.passwordConfirm !== ''" class="text-xs font-bold text-red-500 mt-1 block"><i class="fa-solid fa-triangle-exclamation"></i> Password tidak cocok</span>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-2xl font-bold text-gray-900 mb-6 mt-12 border-b pb-4">Pilih Paket Langganan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
                            <!-- Paket Starter -->
                            <label class="cursor-pointer">
                                <input type="radio" x-model="form.package" name="package" value="starter" class="peer sr-only">
                                <div class="rounded-2xl border-2 border-gray-200 p-5 peer-checked:border-brand peer-checked:bg-brand-light/50 transition-all text-center hover:border-gray-300 peer-checked:ring-2 peer-checked:ring-brand peer-checked:ring-offset-2">
                                    <div class="font-extrabold text-gray-900 mb-1 text-lg">Starter</div>
                                    <div class="text-sm font-medium text-gray-500">Gratis</div>
                                </div>
                            </label>
                            
                            <!-- Paket Business -->
                            <label class="cursor-pointer">
                                <input type="radio" x-model="form.package" name="package" value="business" class="peer sr-only">
                                <div class="rounded-2xl border-2 border-gray-200 p-5 peer-checked:border-brand peer-checked:bg-brand-light/50 transition-all text-center hover:border-gray-300 relative peer-checked:ring-2 peer-checked:ring-brand peer-checked:ring-offset-2">
                                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-brand text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider shadow">Rekomendasi</div>
                                    <div class="font-extrabold text-gray-900 mb-1 text-lg mt-2">Business</div>
                                    <div class="text-sm font-medium text-brand">Hubungi Kami</div>
                                </div>
                            </label>

                            <!-- Paket Pro -->
                            <label class="cursor-pointer">
                                <input type="radio" x-model="form.package" name="package" value="pro" class="peer sr-only">
                                <div class="rounded-2xl border-2 border-gray-200 p-5 peer-checked:border-brand peer-checked:bg-brand-light/50 transition-all text-center hover:border-gray-300 peer-checked:ring-2 peer-checked:ring-brand peer-checked:ring-offset-2">
                                    <div class="font-extrabold text-gray-900 mb-1 text-lg">Pro</div>
                                    <div class="text-sm font-medium text-gray-500">Hubungi Kami</div>
                                </div>
                            </label>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-xl flex items-start mb-10 border border-gray-100">
                            <div class="flex items-center h-5 mt-0.5">
                                <input type="checkbox" id="terms" x-model="form.agree" class="w-5 h-5 text-brand bg-white border-gray-300 rounded focus:ring-brand focus:ring-2" required>
                            </div>
                            <label for="terms" class="ml-3 block text-sm text-gray-600 leading-relaxed cursor-pointer">
                                Dengan mendaftar, saya menyatakan telah membaca dan setuju dengan <a href="#" class="text-brand hover:underline font-bold">Syarat & Ketentuan</a> serta <a href="#" class="text-brand hover:underline font-bold">Kebijakan Privasi</a> RentalBase.
                            </label>
                        </div>

                        <div class="flex flex-col-reverse md:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-100">
                            <button type="button" @click="step = 1" class="text-gray-500 hover:text-gray-900 font-bold flex items-center transition px-4 py-2 w-full md:w-auto justify-center">
                                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                            </button>
                            <button type="submit" class="bg-brand text-white px-10 py-4 rounded-xl font-bold text-lg hover:bg-brand-hover transition shadow-lg shadow-orange-200/50 w-full md:w-auto disabled:opacity-50 disabled:cursor-not-allowed" 
                                    :disabled="!form.agree || (form.password !== form.passwordConfirm) || !form.password">
                                Selesaikan Pendaftaran
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <!-- REGISTRATION FORM END -->

    <!-- FOOTER START -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center mt-auto">
        <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} RentalBase. All rights reserved.</p>
    </footer>
    <!-- FOOTER END -->

    <!-- Alpine.js Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('registrationForm', () => ({
                step: 1,
                subdomainStatus: '', // 'checking', 'available', 'taken'
                checkTimeout: null,
                form: {
                    storeName: '',
                    subdomain: '',
                    description: '',
                    fullName: '',
                    whatsapp: '',
                    email: '',
                    password: '',
                    passwordConfirm: '',
                    package: 'business', // Default selected
                    agree: false
                },
                
                checkSubdomain() {
                    // Filter: Hanya huruf kecil dan angka
                    this.form.subdomain = this.form.subdomain.replace(/[^a-z0-9]/g, '').toLowerCase();
                    
                    if(this.form.subdomain.length < 3) {
                        this.subdomainStatus = '';
                        return;
                    }

                    this.subdomainStatus = 'checking';
                    clearTimeout(this.checkTimeout);
                    
                    // Mock API Call dengan Timeout
                    this.checkTimeout = setTimeout(() => {
                        const takenSubdomains = ['admin', 'rental', 'test', 'demo', 'app', 'sewa'];
                        if(takenSubdomains.includes(this.form.subdomain)) {
                            this.subdomainStatus = 'taken';
                        } else {
                            this.subdomainStatus = 'available';
                        }
                    }, 800); // Simulasi delay jaringan
                },
                
                nextStep() {
                    // Validasi sebelum pindah tahap
                    if(this.form.storeName && this.form.subdomain && this.subdomainStatus === 'available') {
                        this.step = 2;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                
                submitForm() {
                    if(this.form.password === this.form.passwordConfirm && this.form.agree) {
                        // Animasi loading pada tombol bisa ditambahkan di sini
                        alert('🎉 Pendaftaran Berhasil!\n\nSelamat datang di RentalBase. Mengarahkan Anda ke Dashboard...');
                        // Contoh redirect: window.location.href = '/dashboard';
                    }
                }
            }))
        })
    </script>
</body>
</html>
