<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout {{ $product->nama }} - {{ $client->nama_usaha }}</title>
    
    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F4F5F8;
        }
    </style>
</head>
<body class="bg-[#F4F5F8] text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Left Logo & Domain Badge -->
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain]) }}" class="flex items-center gap-2.5 hover:opacity-80 transition">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-emerald-400 font-bold text-xs shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">RentalBase</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-1 rounded">CLIENT</span>
                        </div>
                        <div class="text-base font-extrabold text-slate-900 leading-tight">{{ $client->nama_usaha }}</div>
                    </div>
                </a>

                <!-- Subdomain Tag -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50/80 text-indigo-700 border border-indigo-100 text-xs font-semibold ml-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <span>{{ $client->subdomain }}.rentalbase.com</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain]) }}" class="px-4 py-1.5 rounded-xl text-slate-600 hover:text-slate-900 font-medium text-xs sm:text-sm transition">Katalog</a>
                <a href="#" class="px-4 py-1.5 rounded-xl text-slate-600 hover:text-slate-900 font-medium text-xs sm:text-sm transition">Cara Sewa</a>
                <a href="#" class="px-4 py-1.5 rounded-xl text-slate-600 hover:text-slate-900 font-medium text-xs sm:text-sm transition">Bantuan</a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
        
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-6 font-medium">
            <a href="{{ route('customer.home', ['subdomain' => $client->subdomain]) }}" class="hover:text-emerald-600 transition">Katalog</a>
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-slate-900">Checkout</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Column: Checkout Details -->
            <div class="lg:w-2/3 space-y-6">
                <!-- Product Overview -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row gap-6">
                    <div class="w-full sm:w-40 h-40 bg-slate-100 rounded-xl overflow-hidden shrink-0 border border-slate-200 flex items-center justify-center">
                        @if($product->foto)
                            <img src="{{ Storage::url($product->foto) }}" alt="{{ $product->nama }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        @endif
                    </div>
                    <div class="flex flex-col justify-center">
                        <div class="text-xs font-bold text-emerald-600 mb-1 tracking-wider uppercase">{{ $product->category->nama ?? 'Produk' }}</div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight mb-2">{{ $product->nama }}</h2>
                        <div class="text-2xl font-bold text-slate-900 mb-2">Rp <span id="product-price">{{ number_format($product->harga_sewa, 0, ',', '.') }}</span><span class="text-sm font-medium text-slate-500"> / hari</span></div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Stok tersedia: <span id="max-stock">{{ $product->stok }}</span> unit
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Rental Details Form -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Rincian Sewa
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                        <div>
                            <label for="start_date" class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" id="start_date" name="start_date" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block p-2.5 font-medium" required>
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" id="end_date" name="end_date" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block p-2.5 font-medium" required>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="units" class="block text-sm font-bold text-slate-700 mb-1.5">Jumlah Unit <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-3">
                            <button type="button" id="btn-minus" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 flex items-center justify-center text-slate-700 font-bold transition focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <input type="number" id="units" name="units" value="1" min="1" max="{{ $product->stok }}" class="w-20 text-center bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block p-2.5 font-bold" required readonly>
                            <button type="button" id="btn-plus" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 flex items-center justify-center text-slate-700 font-bold transition focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-bold text-slate-700 mb-1.5">Alamat Pengiriman / Rincian <span class="text-red-500">*</span></label>
                        <textarea id="address" name="address" rows="3" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block p-3 font-medium placeholder-slate-400" placeholder="Contoh: Jl. Sudirman No. 123, RT 01/RW 02, Jakarta Selatan. Dekat gedung xyz." required></textarea>
                        <p class="text-xs text-slate-500 mt-1.5">* Tuliskan alamat lengkap jika alat dikirim, atau catatan jika diambil sendiri.</p>
                    </div>
                </div>

                <!-- Terms -->
                @if($product->ketentuan_jaminan)
                <div class="bg-amber-50 rounded-2xl p-5 border border-amber-200">
                    <h4 class="text-sm font-bold text-amber-800 mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Ketentuan & Jaminan
                    </h4>
                    <p class="text-sm text-amber-700 font-medium leading-relaxed">
                        {{ $product->ketentuan_jaminan }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Right Column: Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm sticky top-24">
                    <h3 class="text-lg font-bold text-slate-900 mb-5 border-b border-slate-200 pb-4">Ringkasan Pesanan</h3>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-slate-600 font-medium">Harga Sewa (per hari)</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($product->harga_sewa, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-slate-600 font-medium">Durasi Sewa</span>
                            <span class="font-bold text-slate-900" id="summary-duration">- hari</span>
                        </div>
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-slate-600 font-medium">Jumlah Unit</span>
                            <span class="font-bold text-slate-900" id="summary-units">1 unit</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4 mb-6">
                        <div class="flex justify-between items-end">
                            <span class="text-base font-bold text-slate-800">Total Pembayaran</span>
                            <div class="text-right">
                                <span class="block text-2xl font-extrabold text-emerald-600" id="summary-total">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="btn-submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl py-3.5 px-4 transition duration-200 flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Buat Pesanan
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                    
                    <p class="text-center text-xs text-slate-500 mt-4 font-medium">
                        Dengan membuat pesanan, Anda menyetujui syarat & ketentuan yang berlaku.
                    </p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-500 font-medium">&copy; {{ date('Y') }} {{ $client->nama_usaha }}. Didukung oleh</span>
                    <span class="text-sm font-bold text-slate-800">RentalBase</span>
                </div>
                <div class="flex gap-4 text-sm font-medium">
                    <a href="#" class="text-slate-500 hover:text-slate-900 transition">Kebijakan Privasi</a>
                    <a href="#" class="text-slate-500 hover:text-slate-900 transition">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Logic Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pricePerDay = {{ $product->harga_sewa }};
            const maxStock = {{ $product->stok }};
            
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            const unitsInput = document.getElementById('units');
            const btnMinus = document.getElementById('btn-minus');
            const btnPlus = document.getElementById('btn-plus');
            
            const summaryDuration = document.getElementById('summary-duration');
            const summaryUnits = document.getElementById('summary-units');
            const summaryTotal = document.getElementById('summary-total');
            const btnSubmit = document.getElementById('btn-submit');

            // Setup minimum date to today
            const today = new Date().toISOString().split('T')[0];
            startDateInput.min = today;
            
            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
                calculateTotal();
            });

            endDateInput.addEventListener('change', calculateTotal);

            // Units Increment / Decrement
            btnMinus.addEventListener('click', () => {
                let current = parseInt(unitsInput.value);
                if (current > 1) {
                    unitsInput.value = current - 1;
                    calculateTotal();
                }
            });

            btnPlus.addEventListener('click', () => {
                let current = parseInt(unitsInput.value);
                if (current < maxStock) {
                    unitsInput.value = current + 1;
                    calculateTotal();
                }
            });

            function calculateTotal() {
                let durationDays = 0;
                let units = parseInt(unitsInput.value) || 1;

                if (startDateInput.value && endDateInput.value) {
                    const start = new Date(startDateInput.value);
                    const end = new Date(endDateInput.value);
                    const diffTime = Math.abs(end - start);
                    // Minimum 1 day rent even if start and end are the same
                    durationDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
                }

                summaryUnits.textContent = units + ' unit';

                if (durationDays > 0) {
                    summaryDuration.textContent = durationDays + ' hari';
                    const total = pricePerDay * durationDays * units;
                    summaryTotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                    btnSubmit.disabled = false;
                } else {
                    summaryDuration.textContent = '- hari';
                    summaryTotal.textContent = 'Rp 0';
                    btnSubmit.disabled = true; // Disable if dates are not fully selected
                }
            }

            // Initial calculation
            calculateTotal();
            
            // Dummy Submit
            btnSubmit.addEventListener('click', () => {
                const address = document.getElementById('address').value;
                if(!startDateInput.value || !endDateInput.value) {
                    alert('Silakan pilih tanggal mulai dan selesai sewa.');
                    return;
                }
                if(!address.trim()) {
                    alert('Silakan isi alamat pengiriman atau rincian.');
                    return;
                }

                btnSubmit.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...`;
                btnSubmit.disabled = true;

                setTimeout(() => {
                    alert('Pesanan berhasil dibuat! (Simulasi)');
                    btnSubmit.innerHTML = `Buat Pesanan <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>`;
                    btnSubmit.disabled = false;
                    window.location.href = "{{ route('customer.home', ['subdomain' => $client->subdomain]) }}";
                }, 1500);
            });
        });
    </script>
</body>
</html>
