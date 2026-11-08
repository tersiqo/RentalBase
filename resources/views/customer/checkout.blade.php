@extends('layouts.customer')

@section('content')
@php
    $itemsList = $items->values()->map(function ($item) {
        if (empty($item['image_url']) && !empty($item['main_image'])) {
            $item['image_url'] = storage_url($item['main_image']);
        }
        if (empty($item['hash'])) {
            $item['hash'] = md5($item['product_id'].$item['start_date'].$item['end_date']);
        }
        return $item;
    });
    $shopLabel = $client->business_name ?? 'Outlet Rental';

    $profileAddress = $profileAddress ?? auth()->user()?->address;
    $lastOrderAddress = $lastOrderAddress ?? null;
    $lastIdentity = $lastIdentity ?? (auth()->check() ? \App\Models\IdentityGuarantee::where('customer_id', auth()->id())->latest('id')->first() : null);

    $defaultShippingAddress = old('shipping_address', $profileAddress ?: ($lastOrderAddress ?: ''));
    $defaultIdentityNumber = old('identity_number', $lastIdentity?->identity_number ?: '');
    $defaultIdentityAddress = old('identity_address', $lastIdentity?->identity_address ?: ($profileAddress ?: ''));
    $defaultSaveToProfile = empty(trim($profileAddress ?? ''));
@endphp
<div class="max-w-[1260px] mx-auto px-6 py-8" x-data="(() => {
    const storageKey = 'rentalbase_checkout_draft_{{ $client->id }}_{{ auth()->id() ?? 'guest' }}';
    let savedDraft = null;
    try {
        const raw = sessionStorage.getItem(storageKey);
        if (raw) savedDraft = JSON.parse(raw);
    } catch (e) {}

    const hasServerErrors = {{ $errors->any() ? 'true' : 'false' }};
    const initialStep = (savedDraft && savedDraft.currentStep && !hasServerErrors && savedDraft.currentStep >= 1 && savedDraft.currentStep <= 4)
        ? savedDraft.currentStep
        : {{ $errors->hasAny(['full_name', 'identity_number', 'identity_address', 'identity_document_image', 'face_image']) ? 3 : ($errors->hasAny(['shipping_address', 'shipping_method']) ? 2 : ($errors->hasAny(['terms']) ? 4 : 1)) }};

    return {
        storageKey: storageKey,
        currentStep: initialStep,
        periodStart: (savedDraft && savedDraft.periodStart) ? savedDraft.periodStart : {{ json_encode(old('start_date', $period['start_date'])) }},
        periodEnd: (savedDraft && savedDraft.periodEnd) ? savedDraft.periodEnd : {{ json_encode(old('end_date', $period['end_date'])) }},
        rentalTime: (savedDraft && savedDraft.rentalTime) ? savedDraft.rentalTime : ({{ json_encode(old('rental_time')) }} || (() => { const d = new Date(); return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`; })()),
        items: {{ json_encode($itemsList->all()) }},
        ship: (savedDraft && savedDraft.ship) ? savedDraft.ship : {{ json_encode(old('shipping_method') === \App\Models\Order::SHIPPING_AMBIL_TOKO ? 'ambil' : 'antar') }},
        shopName: {{ json_encode($shopLabel) }},
        profileAddress: {{ json_encode($profileAddress ?? '') }},
        lastOrderAddress: {{ json_encode($lastOrderAddress ?? '') }},
        addressText: (savedDraft && savedDraft.addressText !== undefined && savedDraft.addressText !== null && savedDraft.addressText !== '') ? savedDraft.addressText : {{ json_encode($defaultShippingAddress) }},
        saveToProfile: (savedDraft && savedDraft.saveToProfile !== undefined) ? savedDraft.saveToProfile : {{ json_encode(old('save_address_to_profile', $defaultSaveToProfile ? '1' : '0') === '1' || old('save_address_to_profile') === true) }},
        whatsapp: (savedDraft && savedDraft.whatsapp !== undefined && savedDraft.whatsapp !== null && savedDraft.whatsapp !== '') ? savedDraft.whatsapp : {{ json_encode(old('whatsapp_number', auth()->user()->phone ?? '')) }},
        fullName: (savedDraft && savedDraft.fullName !== undefined && savedDraft.fullName !== null && savedDraft.fullName !== '') ? savedDraft.fullName : {{ json_encode(old('full_name', $lastIdentity?->full_name ?? auth()->user()->name ?? '')) }},
        identityNumber: (savedDraft && savedDraft.identityNumber !== undefined && savedDraft.identityNumber !== null && savedDraft.identityNumber !== '') ? savedDraft.identityNumber : {{ json_encode($defaultIdentityNumber) }},
        identityAddress: (savedDraft && savedDraft.identityAddress !== undefined && savedDraft.identityAddress !== null && savedDraft.identityAddress !== '') ? savedDraft.identityAddress : {{ json_encode($defaultIdentityAddress) }},
        patokan: (savedDraft && savedDraft.patokan !== undefined && savedDraft.patokan !== null) ? savedDraft.patokan : '',
        isDraftRestored: Boolean(savedDraft && (savedDraft.currentStep > 1 || savedDraft.whatsapp || savedDraft.identityNumber || savedDraft.addressText || savedDraft.patokan)),
        agree: false,
        stepErrors: [],
        isSubmitting: false,

        ktpPreview: null,
        ktpError: '',
        selfiePreview: null,
        selfieError: '',

        init() {
            // Evaluasi ketersediaan unit item di keranjang
            if (this.items && Array.isArray(this.items)) {
                this.items.forEach(it => {
                    const avail = (it.available_units !== undefined && it.available_units !== null) ? it.available_units : 99;
                    if (avail <= 0) {
                        it.quantity = 0;
                    } else if (it.quantity > avail) {
                        it.quantity = avail;
                    }
                });
            }

            // Pantau seluruh field utama untuk auto-save ke sessionStorage secara reaktif
            const fieldsToWatch = [
                'currentStep', 'ship', 'addressText', 'whatsapp', 
                'fullName', 'identityNumber', 'identityAddress', 
                'rentalTime', 'periodStart', 'periodEnd', 'saveToProfile', 'patokan'
            ];
            fieldsToWatch.forEach(field => {
                this.$watch(field, () => this.saveDraft());
            });

            // Simpan draf saat ini
            this.saveDraft();

            window.addEventListener('beforeunload', (e) => {
                if (this.isSubmitting) return;

                // Peringatkan pengguna jika sudah melangkah ke step lanjutan (> 1) atau sudah memilih foto identitas
                const hasProgress = this.currentStep > 1 || this.ktpPreview || this.selfiePreview;
                if (hasProgress) {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            });
        },

        saveDraft() {
            try {
                const draft = {
                    currentStep: this.currentStep,
                    ship: this.ship,
                    addressText: this.addressText,
                    whatsapp: this.whatsapp,
                    fullName: this.fullName,
                    identityNumber: this.identityNumber,
                    identityAddress: this.identityAddress,
                    patokan: this.patokan,
                    rentalTime: this.rentalTime,
                    periodStart: this.periodStart,
                    periodEnd: this.periodEnd,
                    saveToProfile: this.saveToProfile,
                    timestamp: Date.now(),
                };
                sessionStorage.setItem(this.storageKey, JSON.stringify(draft));
            } catch (e) {
                // Abaikan jika browser mematikan storage / quota exceeded
            }
        },

        clearDraft() {
            try {
                sessionStorage.removeItem(this.storageKey);
                this.isDraftRestored = false;
            } catch (e) {}
        },

        resetDraft() {
            this.clearDraft();
            window.location.reload();
        },

    get durationDays() {
        if (!this.periodStart || !this.periodEnd) return 1;
        const start = new Date(this.periodStart);
        const end = new Date(this.periodEnd);
        if (end < start) return 1;
        const diffTime = Math.abs(end - start);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    },

    get shippingAddress() {
        if (this.ship === 'ambil') {
            return 'Ambil di toko — ' + this.shopName;
        }
        const cleanAddress = (this.addressText || '').trim();
        const cleanPatokan = (this.patokan || '').trim();
        if (cleanPatokan && !cleanAddress.includes(cleanPatokan)) {
            return cleanAddress + ' (Patokan: ' + cleanPatokan + ')';
        }
        return cleanAddress;
    },

    rp(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    },

    setNowTime() {
        const d = new Date();
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        this.rentalTime = `${h}:${m}`;
    },

    formatDateIndo(dateStr) {
        if (!dateStr) return '-';
        try {
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            }
            return dateStr;
        } catch (e) {
            return dateStr;
        }
    },

    itemSubtotal(item) {
        if ((item.available_units !== undefined && item.available_units !== null && item.available_units <= 0) || item.quantity <= 0) {
            return 0;
        }
        return item.price * this.durationDays * item.quantity;
    },

    get grandTotal() {
        return this.items.reduce((sum, item) => sum + this.itemSubtotal(item), 0);
    },

    get hasOutOfStock() {
        return this.items.some(it => (it.available_units !== undefined && it.available_units !== null && it.available_units <= 0) || it.quantity > (it.available_units ?? 99) || it.quantity < 1);
    },

    changeQty(item, delta) {
        const max = (item.available_units !== undefined && item.available_units !== null) ? item.available_units : 99;
        if (max <= 0) {
            item.quantity = 0;
            return;
        }
        item.quantity = Math.min(max, Math.max(1, item.quantity + delta));
    },

    handleFileUpload(event, type) {
        const file = event.target.files[0];
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
            if (type === 'ktp') this.ktpError = 'Format file harus JPG atau PNG.';
            if (type === 'selfie') this.selfieError = 'Format file harus JPG atau PNG.';
            event.target.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            if (type === 'ktp') this.ktpError = 'Ukuran file maksimal 2MB.';
            if (type === 'selfie') this.selfieError = 'Ukuran file maksimal 2MB.';
            event.target.value = '';
            return;
        }

        if (type === 'ktp') this.ktpError = '';
        if (type === 'selfie') this.selfieError = '';

        const reader = new FileReader();
        reader.onload = (e) => {
            if (type === 'ktp') this.ktpPreview = e.target.result;
            if (type === 'selfie') this.selfiePreview = e.target.result;
            this.stepErrors = [];
        };
        reader.readAsDataURL(file);
    },

    removeFile(type) {
        if (type === 'ktp') {
            this.ktpPreview = null;
            const input = document.getElementById('identity_document_image');
            if (input) input.value = '';
        } else if (type === 'selfie') {
            this.selfiePreview = null;
            const input = document.getElementById('face_image');
            if (input) input.value = '';
        }
    },

    validateStep(step) {
        let errs = [];
        if (step === 1) {
            if (!this.periodStart) errs.push('Tanggal mulai sewa wajib dipilih.');
            if (!this.periodEnd) errs.push('Tanggal selesai sewa wajib dipilih.');
            if (this.periodStart && this.periodEnd && new Date(this.periodEnd) < new Date(this.periodStart)) {
                errs.push('Tanggal selesai harus setelah tanggal mulai.');
            }
            if (!this.items || this.items.length === 0) {
                errs.push('Keranjang sewa tidak boleh kosong.');
            }
            for (const it of this.items) {
                const avail = (it.available_units !== undefined && it.available_units !== null) ? it.available_units : 99;
                if (avail <= 0) {
                    errs.push('Stok ' + it.name + ' habis pada periode sewa ini (0 unit tersedia). Silakan pilih tanggal sewa lain.');
                } else if (it.quantity > avail) {
                    errs.push('Jumlah sewa ' + it.name + ' (' + it.quantity + ' unit) melebihi stok yang tersedia (' + avail + ' unit).');
                } else if (it.quantity < 1) {
                    errs.push('Jumlah sewa ' + it.name + ' minimal 1 unit.');
                }
            }
        } else if (step === 2) {
            if (this.ship === 'antar') {
                if (!this.addressText || this.addressText.trim().length < 10) {
                    errs.push('Alamat pengiriman minimal 10 karakter.');
                }
            }
            if (!this.whatsapp || this.whatsapp.trim().length < 8) {
                errs.push('Nomor WhatsApp aktif penerima minimal 8 digit.');
            }
        } else if (step === 3) {
            if (!this.fullName || this.fullName.trim().length < 3) {
                errs.push('Nama lengkap sesuai KTP wajib diisi.');
            }
            if (!this.identityNumber || this.identityNumber.trim().length < 8) {
                errs.push('Nomor KTP minimal 8 digit.');
            }
            if (!this.identityAddress || this.identityAddress.trim().length < 10) {
                errs.push('Alamat sesuai KTP minimal 10 karakter.');
            }
            const ktpInput = document.getElementById('identity_document_image');
            const hasKtpFile = ktpInput && ktpInput.files && ktpInput.files.length > 0;
            if (!this.ktpPreview && !hasKtpFile) {
                errs.push('Foto KTP wajib diunggah.');
            }
            const selfieInput = document.getElementById('face_image');
            const hasSelfieFile = selfieInput && selfieInput.files && selfieInput.files.length > 0;
            if (!this.selfiePreview && !hasSelfieFile) {
                errs.push('Foto swafoto (selfie memegang KTP) wajib diunggah.');
            }
        }
        return errs;
    },

    goToStep(targetStep) {
        this.stepErrors = [];
        if (targetStep < this.currentStep) {
            this.currentStep = targetStep;
            this.saveDraft();
            return;
        }
        for (let s = this.currentStep; s < targetStep; s++) {
            const errs = this.validateStep(s);
            if (errs.length > 0) {
                this.stepErrors = errs;
                this.currentStep = s;
                this.saveDraft();
                window.scrollTo({ top: 150, behavior: 'smooth' });
                return;
            }
        }
        this.currentStep = targetStep;
        this.saveDraft();
        window.scrollTo({ top: 150, behavior: 'smooth' });
    },

    handleSubmit(e) {
        this.stepErrors = [];
        for (let s = 1; s <= 3; s++) {
            const errs = this.validateStep(s);
            if (errs.length > 0) {
                e.preventDefault();
                this.stepErrors = errs;
                this.currentStep = s;
                window.scrollTo({ top: 150, behavior: 'smooth' });
                return false;
            }
        }
        if (!this.agree) {
            e.preventDefault();
            return false;
        }

        this.isSubmitting = true;
        this.clearDraft();
    }
} })()">

    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm font-medium text-gray-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('customer.home', ['subdomain' => $client->subdomain]) }}" class="hover:text-primary-600 transition">Katalog</a>
            </li>
            <li><span class="text-gray-300">/</span></li>
            <li>
                <a href="{{ route('customer.cart', ['subdomain' => $client->subdomain]) }}" class="hover:text-primary-600 transition">Keranjang</a>
            </li>
            <li><span class="text-gray-300">/</span></li>
            <li class="text-gray-900 font-bold">Checkout</li>
        </ol>
    </nav>

    <!-- Header Title -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Checkout Pesanan</h1>
        <p class="text-sm text-gray-500 mt-1">Lengkapi rincian sewa, alamat, dan verifikasi jaminan identitas Anda.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm">
            <div class="font-bold mb-1">Terjadi kesalahan input:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Draft Restored Notification Banner -->
    <template x-if="isDraftRestored && currentStep > 1">
        <div class="mb-6 bg-amber-50/90 border border-amber-200/90 rounded-2xl p-4 flex items-center justify-between text-xs text-amber-900 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Draf formulir Anda dipulihkan otomatis ke <b>Langkah <span x-text="currentStep"></span></b>. Seluruh data teks yang Anda ketik tetap terjaga.</span>
            </div>
            <button type="button" @click="resetDraft()" class="text-amber-800 hover:text-amber-950 font-bold underline cursor-pointer ml-3 shrink-0 text-xs">
                Ulangi dari Awal
            </button>
        </div>
    </template>

    <!-- Stepper Navigation -->
    <div class="mb-8 bg-white border border-slate-100 rounded-3xl p-5 sm:p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.07)]">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <!-- Step 1 -->
            <button type="button" @click="goToStep(1)" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 1 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">1</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 1 ? 'text-primary-500' : 'text-slate-900'">Ringkasan Pesanan</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">Produk &amp; periode sewa</span>
                </div>
            </button>

            <!-- Step 2 -->
            <button type="button" @click="goToStep(2)" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 2 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">2</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 2 ? 'text-primary-500' : 'text-slate-900'">Pengiriman &amp; Kontak</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">Antar atau ambil</span>
                </div>
            </button>

            <!-- Step 3 -->
            <button type="button" @click="goToStep(3)" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 3 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">3</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 3 ? 'text-primary-500' : 'text-slate-900'">Identitas &amp; Jaminan</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">KTP &amp; swafoto</span>
                </div>
            </button>

            <!-- Step 4 -->
            <button type="button" @click="goToStep(4)" class="flex items-center gap-3.5 p-2 transition text-left cursor-pointer group">
                <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm flex-none transition-all duration-200"
                    :class="currentStep === 4 ? 'bg-primary-500 text-white shadow-[0_8px_20px_-4px_rgba(249,115,22,0.5)] scale-105' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">4</span>
                <div class="min-w-0">
                    <span class="text-sm font-bold block truncate transition-colors duration-200"
                        :class="currentStep === 4 ? 'text-primary-500' : 'text-slate-900'">Konfirmasi Final</span>
                    <span class="text-xs text-slate-400 font-normal block truncate mt-0.5">Total &amp; setuju</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('customer.checkout.store', ['subdomain' => $client->subdomain]) }}" method="POST" enctype="multipart/form-data" @submit="handleSubmit($event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-8 items-start">

            <!-- Left Form Content -->
            <div class="space-y-6">

                <!-- Hidden item selections -->
                <template x-for="(item, idx) in items" :key="item.hash">
                    <div class="hidden">
                        <input type="hidden" name="selected_hashes[]" :value="item.hash">
                        <input type="hidden" :name="'items[' + item.hash + '][quantity]'" x-model.number="item.quantity">
                    </div>
                </template>

                <!-- STEP 1: Ringkasan Pesanan & Periode -->
                <div x-show="currentStep === 1" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <!-- Card Header -->
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 3h3l2.5 12h11L21 7H6"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Ringkasan Pesanan &amp; Periode</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Periksa produk dan atur tanggal mulai &amp; selesai sewa.</p>
                        </div>
                    </div>

                    <!-- Step 1 Validation Errors -->
                    <template x-if="stepErrors.length > 0 && currentStep === 1">
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm">
                            <div class="font-bold mb-1 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Terjadi kesalahan input:
                            </div>
                            <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm font-medium">
                                <template x-for="(err, idx) in stepErrors" :key="idx">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- Out of stock alert banner -->
                    <template x-if="hasOutOfStock">
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm flex gap-3 items-center">
                            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <b class="block font-bold">Stok Unit Penuh pada Periode Ini</b>
                                <p class="text-xs text-red-600 mt-0.5">Terdapat produk yang stok unitnya habis pada rentang tanggal sewa yang dipilih. Anda harus mengganti tanggal sewa sebelum melanjutkan checkout.</p>
                            </div>
                        </div>
                    </template>

                    <!-- Item list -->
                    <div class="space-y-4">
                        <template x-for="(item, idx) in items" :key="item.hash">
                            <div class="grid grid-cols-[78px_1fr_auto] gap-4 items-center border rounded-2xl p-3.5 transition-colors"
                                 :class="(item.available_units ?? 0) <= 0 ? 'border-red-200 bg-red-50/30' : 'border-gray-100 bg-gray-50/50'">
                                <div class="w-[78px] h-[78px] rounded-xl bg-white border border-gray-200/80 overflow-hidden flex-none flex items-center justify-center relative">
                                    <template x-if="item.image_url || item.main_image">
                                        <div class="w-full h-full relative">
                                            <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                                        </div>
                                    </template>
                                    <template x-if="!item.image_url && !item.main_image">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </template>
                                </div>
                                <!-- Middle Details -->
                                <div class="min-w-0">
                                    <h4 class="text-[15px] font-bold text-gray-900 truncate leading-snug" x-text="item.name"></h4>
                                    
                                    <template x-if="(item.available_units ?? 0) > 0">
                                        <div class="text-xs font-semibold text-emerald-600 mt-0.5" x-text="rp(item.price) + ' / hari · ' + item.available_units + ' unit tersedia'"></div>
                                    </template>
                                    <template x-if="(item.available_units ?? 0) <= 0">
                                        <div class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-100 border border-red-300 px-2 py-0.5 rounded-lg mt-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                                            <span>Stok Habis (0 unit tersedia)</span>
                                        </div>
                                    </template>

                                    <!-- Qty buttons -->
                                    <div class="inline-flex items-center gap-1 border border-gray-200/80 rounded-xl p-1 mt-2"
                                         :class="(item.available_units ?? 0) <= 0 ? 'opacity-40 pointer-events-none bg-gray-100' : 'bg-white'">
                                        <button type="button" @click="changeQty(item, -1)" :disabled="(item.available_units ?? 0) <= 0 || item.quantity <= 1" aria-label="Kurangi" class="w-7 h-7 rounded-lg bg-gray-50 hover:bg-primary-50 hover:text-primary-600 text-gray-800 font-extrabold flex items-center justify-center transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed">-</button>
                                        <span class="min-w-[32px] text-center font-bold text-gray-900 text-sm" x-text="item.quantity"></span>
                                        <button type="button" @click="changeQty(item, 1)" :disabled="(item.available_units ?? 0) <= 0 || item.quantity >= (item.available_units ?? 0)" aria-label="Tambah" class="w-7 h-7 rounded-lg bg-gray-50 hover:bg-primary-50 hover:text-primary-600 text-gray-800 font-extrabold flex items-center justify-center transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed">+</button>
                                    </div>
                                </div>
                                <!-- Right Price -->
                                <div class="text-right">
                                    <template x-if="(item.available_units ?? 0) > 0">
                                        <div>
                                            <b class="text-[15px] font-extrabold text-gray-900 block" x-text="rp(itemSubtotal(item))"></b>
                                            <span class="text-xs font-semibold text-gray-400 block mt-0.5" x-text="item.quantity + ' unit × ' + durationDays + ' hari'"></span>
                                        </div>
                                    </template>
                                    <template x-if="(item.available_units ?? 0) <= 0">
                                        <div>
                                            <b class="text-[14px] font-bold text-red-500 block">Tidak Tersedia</b>
                                            <span class="text-xs font-semibold text-gray-400 block mt-0.5">0 unit sewa</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Date & Duration Section -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-gray-800 mb-2 uppercase tracking-wide">Periode Sewa <span class="text-primary-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr_auto] gap-3 items-end">
                            <div>
                                <label for="start_date" class="block text-[12.5px] font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                                <input type="date" id="start_date" name="start_date" x-model="periodStart" min="{{ date('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500 bg-white" required>
                            </div>
                            <div class="hidden sm:flex items-center justify-center text-gray-400 pb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </div>
                            <div>
                                <label for="end_date" class="block text-[12.5px] font-semibold text-gray-600 mb-1">Tanggal Selesai</label>
                                <input type="date" id="end_date" name="end_date" x-model="periodEnd" :min="periodStart" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500 bg-white" required>
                            </div>
                            <!-- Durbox -->
                            <div class="bg-primary-50/80 border border-primary-200/80 rounded-xl px-4 py-2 text-center min-w-[96px]">
                                <small class="text-[10px] font-bold text-primary-700 tracking-wider block uppercase">DURASI</small>
                                <b class="text-2xl font-extrabold text-primary-600 block leading-tight" x-text="durationDays"></b>
                                <small class="text-[10px] font-bold text-[#9A3412] tracking-wider block uppercase">HARI</small>
                            </div>
                        </div>
                        <span class="block text-[12.5px] text-gray-400 font-medium mt-2">Perhitungan otomatis: (Tanggal Selesai − Tanggal Mulai) × harga/hari. Minimal 1 hari.</span>
                    </div>

                    <!-- Rental Time & Due Time Section -->
                    <div class="pt-4 border-t border-gray-100 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label for="rental_time" class="block text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Rencana Jam Pengambilan / Pengiriman &amp; Batas Kembali</span>
                                <span class="text-primary-500">*</span>
                            </label>
                            <span class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full">
                                Hitungan Tetap Harian (1 Hari = 24 Jam)
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-4 items-start">
                            <!-- Time Input & Shortcuts -->
                            <div class="space-y-2">
                                <div class="relative">
                                    <input type="time"
                                           id="rental_time"
                                           name="rental_time"
                                           x-model="rentalTime"
                                           required
                                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-base font-bold text-gray-900 focus:ring-primary-500 focus:border-primary-500 bg-white shadow-2xs">
                                </div>

                                <!-- Shortcut chips -->
                                <div class="flex flex-wrap gap-1.5 text-[11px]">
                                    <button type="button"
                                            @click="setNowTime()"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:border-primary-300 hover:text-primary-600 font-semibold text-gray-600 transition-colors shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Sekarang</span>
                                    </button>
                                    <button type="button"
                                            @click="rentalTime = '09:00'"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:border-primary-300 hover:text-primary-600 font-semibold text-gray-600 transition-colors shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <span>09:00</span>
                                    </button>
                                    <button type="button"
                                            @click="rentalTime = '13:00'"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:border-primary-300 hover:text-primary-600 font-semibold text-gray-600 transition-colors shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <span>13:00</span>
                                    </button>
                                    <button type="button"
                                            @click="rentalTime = '16:00'"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:border-primary-300 hover:text-primary-600 font-semibold text-gray-600 transition-colors shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                        <span>16:00</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Time Summary Box -->
                            <div class="bg-stone-50 border border-gray-200/80 rounded-xl p-3.5 space-y-2 text-xs">
                                <div class="flex items-center justify-between pb-2 border-b border-gray-200/60">
                                    <span class="text-gray-500 font-medium">Mulai Sewa:</span>
                                    <span class="font-bold text-gray-900">
                                        <span x-text="formatDateIndo(periodStart)"></span> • Pukul <span x-text="rentalTime" class="text-primary-600"></span> WIB
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500 font-medium">Batas Waktu Pengembalian:</span>
                                    <span class="font-bold text-gray-900">
                                        <span x-text="formatDateIndo(periodEnd)"></span> • Pukul <span x-text="rentalTime" class="text-primary-600"></span> WIB
                                    </span>
                                </div>
                                <div class="text-[11px] text-gray-600 pt-1 leading-relaxed space-y-1.5">
                                    <p class="flex items-start gap-1.5">
                                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span><em>Jam batas pengembalian otomatis disamakan dengan jam mulai sewa. Keterlambatan retur baru dihitung jika melewati pukul <strong x-text="rentalTime + ' WIB'"></strong> pada tanggal selesai.</em></span>
                                    </p>
                                    <p class="text-primary-800 bg-primary-50/80 p-2.5 rounded-lg border border-primary-100 text-[11px] flex items-start gap-2">
                                        <svg class="w-4 h-4 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span><strong>Jaminan Durasi Utuh:</strong> Jika proses verifikasi, pembayaran, atau serah terima selesai melewati jam rencana di atas, jam mulai &amp; batas kembali akan otomatis disesuaikan saat barang diserahkan agar waktu sewa Anda tidak terpotong.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notice Info Box -->
                    <div class="bg-primary-50/70 border border-primary-100 rounded-2xl p-4 flex gap-3.5 items-start">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-primary-500 shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8h.01M11 12h1v4h1"/></svg>
                        </div>
                        <div class="text-xs leading-relaxed">
                            <b class="block text-[#7C2D12] text-[13.5px] font-bold mb-0.5">Subtotal dihitung otomatis</b>
                            <p class="text-[#9A3412]">Subtotal = Σ (harga per hari × jumlah unit) × durasi hari. Lihat rincian pada panel kanan.</p>
                        </div>
                    </div>

                    <!-- Footer Navigation -->
                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="goToStep(2)" 
                                :disabled="hasOutOfStock"
                                :class="hasOutOfStock ? 'opacity-50 cursor-not-allowed bg-gray-400 shadow-none' : 'bg-primary-500 hover:bg-primary-600 active:scale-95 shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] cursor-pointer'"
                                class="inline-flex items-center gap-2 rounded-xl font-bold px-6 py-3 text-white transition text-sm">
                            Lanjut ke Pengiriman
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Pengiriman & Kontak -->
                <div x-show="currentStep === 2" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Pengiriman &amp; Kontak</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Pilih cara menerima barang dan masukkan kontak penerima.</p>
                        </div>
                    </div>

                    <!-- Step 2 Validation Errors -->
                    <template x-if="stepErrors.length > 0 && currentStep === 2">
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm">
                            <div class="font-bold mb-1 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Terjadi kesalahan input:
                            </div>
                            <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm font-medium">
                                <template x-for="(err, idx) in stepErrors" :key="idx">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- Delivery method toggle -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Metode Penerimaan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" @click="ship = 'antar'" class="flex items-start gap-3 p-4 rounded-xl border-2 text-left transition" :class="ship === 'antar' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                                <span class="w-5 h-5 rounded-full border-2 flex-none mt-0.5 flex items-center justify-center" :class="ship === 'antar' ? 'border-primary-500' : 'border-gray-300'">
                                    <span x-show="ship === 'antar'" class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                                </span>
                                <span>
                                    <b class="block text-sm text-gray-900">Dikirim ke Alamat</b>
                                    <span class="block text-xs text-gray-500 mt-0.5 leading-relaxed">Diantar kurir pihak ketiga ke lokasi Anda (ongkir terpisah).</span>
                                </span>
                            </button>
                            <button type="button" @click="ship = 'ambil'" class="flex items-start gap-3 p-4 rounded-xl border-2 text-left transition" :class="ship === 'ambil' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                                <span class="w-5 h-5 rounded-full border-2 flex-none mt-0.5 flex items-center justify-center" :class="ship === 'ambil' ? 'border-primary-500' : 'border-gray-300'">
                                    <span x-show="ship === 'ambil'" class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                                </span>
                                <span>
                                    <b class="block text-sm text-gray-900">Ambil di Outlet</b>
                                    <span class="block text-xs text-gray-500 mt-0.5 leading-relaxed">Datang langsung ke outlet penyewa, gratis.</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden shipping address (server always expects it) -->
                    <input type="hidden" name="shipping_address" :value="shippingAddress">

                    <!-- Hidden shipping method (server always expects it) -->
                    <input type="hidden" name="shipping_method" :value="ship === 'ambil' ? '{{ \App\Models\Order::SHIPPING_AMBIL_TOKO }}' : '{{ \App\Models\Order::SHIPPING_KURIR }}'">

                    <!-- Hidden WhatsApp number (server receives it) -->
                    <input type="hidden" name="whatsapp_number" :value="whatsapp">

                    <!-- Antar mode -->
                    <div x-show="ship === 'antar'" class="space-y-4">
                        @if($profileAddress || $lastOrderAddress)
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 sm:p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                    <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">Pilih Template Alamat Tersimpan</span>
                                </div>
                                <a href="{{ route('customer.profile', ['subdomain' => $client->subdomain]) }}" target="_blank" class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline flex items-center gap-1">
                                    <span>Kelola di Profil</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @if($profileAddress)
                                <button type="button" 
                                        @click="addressText = profileAddress"
                                        class="p-3 rounded-xl border text-left transition-all relative flex flex-col justify-between group cursor-pointer"
                                        :class="addressText.trim() === profileAddress.trim() ? 'border-primary-500 bg-white ring-2 ring-primary-500/20 shadow-sm' : 'border-gray-200 bg-white/70 hover:border-gray-300 hover:bg-white'">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-900">
                                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                                <span>Alamat Utama Profil</span>
                                            </span>
                                            <span x-show="addressText.trim() === profileAddress.trim()" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-primary-100 text-primary-700">
                                                <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Aktif</span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $profileAddress }}</p>
                                    </div>
                                    <div class="mt-2 text-[11px] font-semibold text-primary-600 group-hover:text-primary-700">
                                        Gunakan alamat ini &rarr;
                                    </div>
                                </button>
                                @endif

                                @if($lastOrderAddress)
                                <button type="button" 
                                        @click="addressText = lastOrderAddress"
                                        class="p-3 rounded-xl border text-left transition-all relative flex flex-col justify-between group cursor-pointer"
                                        :class="addressText.trim() === lastOrderAddress.trim() ? 'border-primary-500 bg-white ring-2 ring-primary-500/20 shadow-sm' : 'border-gray-200 bg-white/70 hover:border-gray-300 hover:bg-white'">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-900">
                                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                <span>Pengiriman Terakhir</span>
                                            </span>
                                            <span x-show="addressText.trim() === lastOrderAddress.trim()" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-primary-100 text-primary-700">
                                                <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Aktif</span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $lastOrderAddress }}</p>
                                    </div>
                                    <div class="mt-2 text-[11px] font-semibold text-primary-600 group-hover:text-primary-700">
                                        Gunakan alamat ini &rarr;
                                    </div>
                                </button>
                                @endif
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <button type="button" 
                                        @click="addressText = ''; $nextTick(() => document.getElementById('addressText').focus())"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-800 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Ketik Alamat Baru</span>
                                </button>
                                <span class="text-[11px] text-gray-400">Atau edit langsung textarea di bawah</span>
                            </div>
                        </div>
                        @endif

                        <div>
                            <label for="addressText" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Alamat Pengiriman Lengkap <span class="text-red-500">*</span></label>
                            <textarea id="addressText" x-model="addressText" rows="3" class="w-full border border-gray-200 rounded-xl p-3.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos"></textarea>
                            @error('shipping_address')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Checkbox simpan ke profil --}}
                        <div class="flex items-start gap-2.5 pt-0.5">
                            <input type="checkbox" 
                                   id="save_address_to_profile" 
                                   name="save_address_to_profile" 
                                   value="1" 
                                   x-model="saveToProfile"
                                   class="w-4 h-4 mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer">
                            <label for="save_address_to_profile" class="text-xs text-gray-700 font-medium cursor-pointer select-none">
                                <span class="font-bold text-gray-900">Jadikan sebagai Template Alamat Utama</span> di profil saya untuk checkout berikutnya.
                            </label>
                        </div>

                        <div>
                            <label for="patokan" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Patokan Lokasi <span class="text-gray-400 font-medium normal-case">(opsional, memudahkan kurir)</span></label>
                            <input type="text" id="patokan" x-model="patokan" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="Contoh: sebelah minimarket, pagar hitam, gang setelah masjid">
                        </div>
                        <div>
                            <label for="wa" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nomor WhatsApp / Telepon Aktif Penerima <span class="text-red-500">*</span></label>
                            <input type="tel" id="wa" x-model="whatsapp" inputmode="tel" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="bg-primary-50/70 border border-primary-100 rounded-xl p-4 flex gap-3">
                            <svg class="w-5 h-5 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"></path></svg>
                            <div class="text-xs text-[#7C2D12] leading-relaxed">
                                <b>Biaya kirim dibayar terpisah.</b> Ongkos kirim tidak dihitung di sini; kurir akan menghubungi Anda via WhatsApp untuk konfirmasi biaya dan jadwal.
                            </div>
                        </div>
                    </div>

                    <!-- Ambil mode -->
                    <div x-show="ship === 'ambil'" class="space-y-4">
                        <div class="bg-primary-50/70 border border-primary-100 rounded-xl p-4 flex gap-3 items-start">
                            <svg class="w-5 h-5 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9l1-5h16l1 5M4 9v11h16V9M9 20v-6h6v6"></path></svg>
                            <div class="text-xs text-[#7C2D12] leading-relaxed">
                                <b>{{ $shopLabel }}</b>
                                <p class="mt-0.5 text-gray-500">Rincian alamat outlet akan dikonfirmasi admin via WhatsApp setelah pesanan diterima.</p>
                            </div>
                        </div>
                        <div>
                            <label for="wa2" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nomor WhatsApp / Telepon Aktif Penerima <span class="text-red-500">*</span></label>
                            <input type="tel" id="wa2" x-model="whatsapp" inputmode="tel" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="08xxxxxxxxxx">
                        </div>
                        <p class="text-xs text-gray-400">Tunjukkan nomor pesanan saat mengambil barang di outlet.</p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between">
                        <button type="button" @click="goToStep(1)" class="inline-flex items-center gap-2 rounded-xl font-bold px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            &larr; Kembali
                        </button>
                        <button type="button" @click="goToStep(3)" class="inline-flex items-center gap-2 rounded-xl font-bold px-6 py-2.5 bg-primary-500 text-white shadow-sm hover:bg-primary-600 transition">
                            Lanjut ke Jaminan Identitas &rarr;
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Identitas dan Jaminan Penyewa -->
                <div x-show="currentStep === 3" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="9" cy="11" r="2.2"/><path d="M14 10h4M14 14h4M5.5 16c.8-1.6 2-2.3 3.5-2.3s2.7.7 3.5 2.3"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Identitas &amp; Jaminan Penyewa</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Verifikasi Identitas Digital (Tanpa Deposit Uang): Cukup unggah foto KTP dan swafoto (selfie), tanpa perlu deposit uang tunai dan tanpa titip fisik KTP.</p>
                        </div>
                    </div>

                    <!-- Step 3 Validation Errors -->
                    <template x-if="stepErrors.length > 0 && currentStep === 3">
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm">
                            <div class="font-bold mb-1 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Terjadi kesalahan input:
                            </div>
                            <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm font-medium">
                                <template x-for="(err, idx) in stepErrors" :key="idx">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- Notice Box -->
                    <div class="bg-primary-50 border border-primary-100 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="text-xs text-primary-900 leading-relaxed">
                            <b>Ketentuan Verifikasi Identitas:</b> Unggah foto KTP &amp; swafoto (selfie) memegang KTP untuk verifikasi identitas penyewa saat booking online. <strong>Tidak perlu deposit uang tunai dan tidak perlu menitipkan fisik KTP asli</strong>.
                        </div>
                    </div>

                    @if($lastIdentity)
                    <div class="bg-emerald-50 border border-emerald-200/80 rounded-xl p-3 flex items-center justify-between text-xs text-emerald-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Data NIK &amp; Alamat otomatis diisi dari riwayat verifikasi KTP Anda sebelumnya.</span>
                        </div>
                        <span class="font-bold bg-white text-emerald-700 px-2 py-0.5 rounded-md border border-emerald-200 shrink-0">Auto-Filled</span>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="full_name" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nama Lengkap (Sesuai KTP) <span class="text-red-500">*</span></label>
                            <input type="text" id="full_name" name="full_name" x-model="fullName" value="{{ old('full_name', $lastIdentity?->full_name ?? auth()->user()->name ?? '') }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500" placeholder="Nama sesuai identitas" required>
                            @error('full_name')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="identity_number" class="block text-xs font-bold text-gray-700 mb-1.5 uppercase">Nomor KTP / NIK <span class="text-red-500">*</span></label>
                            <input type="text" id="identity_number" name="identity_number" x-model="identityNumber" value="{{ old('identity_number', $defaultIdentityNumber) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:ring-primary-500 focus:border-primary-500" placeholder="16 digit nomor NIK KTP" required>
                            @error('identity_number')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                            <label for="identity_address" class="block text-xs font-bold text-gray-700 uppercase">Alamat Sesuai KTP <span class="text-red-500">*</span></label>
                            
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="if (addressText && addressText.trim().length > 0) { identityAddress = addressText }"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-200 transition cursor-pointer"
                                        title="Salin alamat dari Step 2">
                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Sama dengan Alamat Pengiriman</span>
                                </button>
                                @if($profileAddress)
                                <button type="button" 
                                        @click="identityAddress = profileAddress"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition cursor-pointer"
                                        title="Gunakan alamat dari profil">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span>Gunakan Alamat Utama Profil</span>
                                </button>
                                @endif
                            </div>
                        </div>
                        <textarea id="identity_address" name="identity_address" x-model="identityAddress" rows="2" class="w-full border border-gray-200 rounded-xl p-3 text-sm font-medium text-gray-900 focus:ring-primary-500 focus:border-primary-500" placeholder="Alamat domisili sesuai KTP..." required>{{ old('identity_address', $defaultIdentityAddress) }}</textarea>
                        @error('identity_address')
                            <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Document Uploads -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <!-- Upload KTP -->
                        <div class="border border-dashed border-gray-300 rounded-2xl p-4 text-center bg-gray-50/50">
                            <label class="block text-xs font-bold text-gray-800 mb-2 uppercase">Foto KTP / Dokumen Identitas <span class="text-red-500">*</span></label>

                            <!-- Always present in DOM inside form -->
                            <input type="file" id="identity_document_image" name="identity_document_image" accept="image/jpeg,image/png,image/jpg" @change="handleFileUpload($event, 'ktp')" class="hidden">

                            <div x-show="!ktpPreview" class="space-y-2 py-3">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <label for="identity_document_image" class="cursor-pointer bg-white border border-gray-200 text-xs font-bold text-gray-700 px-3 py-1.5 rounded-lg inline-block hover:border-primary-500 shadow-2xs">Pilih File KTP</label>
                                <p class="text-[11px] text-gray-400">JPG/PNG, Maks. 2MB</p>
                            </div>

                            <div x-show="ktpPreview" class="relative">
                                <img :src="ktpPreview" class="h-32 w-full object-cover rounded-xl border border-gray-200 mb-2">
                                <div class="flex items-center justify-center gap-2">
                                    <label for="identity_document_image" class="cursor-pointer bg-slate-800 text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-slate-900 transition">Ganti KTP</label>
                                    <button type="button" @click="removeFile('ktp')" class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-red-700 transition">Hapus</button>
                                </div>
                            </div>

                            <p x-text="ktpError" class="text-xs text-red-600 mt-1 font-semibold"></p>
                            @error('identity_document_image')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Upload Selfie -->
                        <div class="border border-dashed border-gray-300 rounded-2xl p-4 text-center bg-gray-50/50">
                            <label class="block text-xs font-bold text-gray-800 mb-2 uppercase">Foto Swafoto (Selfie + KTP) <span class="text-red-500">*</span></label>

                            <!-- Always present in DOM inside form -->
                            <input type="file" id="face_image" name="face_image" accept="image/jpeg,image/png,image/jpg" @change="handleFileUpload($event, 'selfie')" class="hidden">

                            <div x-show="!selfiePreview" class="space-y-2 py-3">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                <label for="face_image" class="cursor-pointer bg-white border border-gray-200 text-xs font-bold text-gray-700 px-3 py-1.5 rounded-lg inline-block hover:border-primary-500 shadow-2xs">Pilih File Swafoto</label>
                                <p class="text-[11px] text-gray-400">JPG/PNG, Maks. 2MB</p>
                            </div>

                            <div x-show="selfiePreview" class="relative">
                                <img :src="selfiePreview" class="h-32 w-full object-cover rounded-xl border border-gray-200 mb-2">
                                <div class="flex items-center justify-center gap-2">
                                    <label for="face_image" class="cursor-pointer bg-slate-800 text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-slate-900 transition">Ganti Swafoto</label>
                                    <button type="button" @click="removeFile('selfie')" class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-red-700 transition">Hapus</button>
                                </div>
                            </div>

                            <p x-text="selfieError" class="text-xs text-red-600 mt-1 font-semibold"></p>
                            @error('face_image')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between">
                        <button type="button" @click="goToStep(2)" class="inline-flex items-center gap-2 rounded-xl font-bold px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            &larr; Kembali
                        </button>
                        <button type="button" @click="goToStep(4)" class="inline-flex items-center gap-2 rounded-xl font-bold px-6 py-2.5 bg-primary-500 text-white shadow-sm hover:bg-primary-600 transition">
                            Lanjut ke Konfirmasi &rarr;
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Konfirmasi & Persetujuan -->
                <div x-show="currentStep === 4" 
                     x-transition:enter="transition-all duration-350 ease-out transform"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-6">
                    <div class="flex items-center gap-3.5 pb-5 border-b border-gray-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center flex-none">
                            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Konfirmasi Final</h2>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Tinjau kembali detail pesanan sebelum dikirim ke admin.</p>
                        </div>
                    </div>

                    <!-- Summary Verification -->
                    <div class="space-y-3 bg-gray-50 rounded-xl p-4 text-xs font-semibold text-gray-700">
                        <template x-for="(item, idx) in items" :key="item.hash">
                            <div class="flex justify-between border-b border-gray-200/60 pb-2 last:border-b-0 last:pb-0">
                                <span class="text-gray-500" x-text="item.name + ' × ' + item.quantity"></span>
                                <span class="text-gray-900 font-bold" x-text="rp(itemSubtotal(item))"></span>
                            </div>
                        </template>
                        <div class="flex justify-between border-b border-gray-200/60 pb-2">
                            <span class="text-gray-500">Periode Sewa:</span>
                            <div class="text-right">
                                <div class="text-gray-900 font-bold" x-text="durationDays + ' hari (' + periodStart + ' s/d ' + periodEnd + ')'"></div>
                                <div class="text-[11px] text-primary-600 font-semibold" x-text="'Rencana Mulai & Kembali: Pukul ' + rentalTime + ' WIB'"></div>
                            </div>
                        </div>
                        <div class="flex justify-between border-b border-gray-200/60 pb-2">
                            <span class="text-gray-500">Metode Penerimaan:</span>
                            <span class="text-gray-900" x-text="ship === 'ambil' ? 'Ambil di outlet' : 'Dikirim ke alamat'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Biaya Sewa:</span>
                            <span class="text-primary-600 font-extrabold text-sm" x-text="rp(grandTotal)"></span>
                        </div>
                    </div>

                    <!-- Verification notice -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="text-xs text-amber-900 font-medium leading-relaxed">
                            Pesanan akan berstatus <b>Menunggu Konfirmasi</b>. Pembayaran dibuka setelah verifikasi KTP disetujui Admin (est. 1×24 jam kerja).
                        </div>
                    </div>

                    <!-- Terms Agreement Checkbox -->
                    <div class="bg-primary-50/60 border border-primary-100 rounded-xl p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" x-model="agree" class="mt-1 rounded text-primary-500 focus:ring-primary-500" required>
                            <span class="text-xs text-[#7C2D12] font-medium leading-relaxed">
                                Saya menyatakan bahwa seluruh data identitas yang diunggah adalah sah dan benar. Saya menyetujui seluruh <b>Syarat & Ketentuan Sewa RentalBase</b> serta bersedia mematuhi aturan penanganan peralatan yang berlaku.
                            </span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
                        <button type="button" @click="goToStep(3)" class="inline-flex items-center gap-2 rounded-xl font-bold px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            &larr; Kembali
                        </button>
                        <button type="submit" :disabled="!agree || hasOutOfStock" class="inline-flex items-center justify-center gap-2 rounded-xl font-extrabold px-7 py-3 bg-primary-500 text-white shadow-[0_8px_18px_-8px_rgba(249,115,22,0.7)] hover:bg-primary-600 active:scale-95 transition text-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            Buat Pesanan & Kirim Verifikasi &rarr;
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sticky Order Summary Card -->
            <div class="lg:sticky lg:top-[90px]">
                <div class="bg-gradient-to-b from-primary-500 to-primary-600 rounded-t-2xl px-6 py-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-white flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"></path></svg>
                    <h3 class="text-base font-extrabold text-white">Ringkasan Biaya</h3>
                </div>
                <div class="bg-white border border-t-0 border-slate-100/80 rounded-b-2xl p-6 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.06)] space-y-4">
                    <div class="space-y-2.5 text-xs font-medium text-gray-600">
                        <template x-for="(item, idx) in items" :key="item.hash">
                            <div class="flex justify-between gap-2">
                                <span class="truncate" x-text="item.name + ' ×' + item.quantity"></span>
                                <span class="font-bold text-gray-900 shrink-0" x-text="rp(itemSubtotal(item))"></span>
                            </div>
                        </template>
                        <div class="flex justify-between items-start">
                            <span>Durasi Sewa</span>
                            <div class="text-right">
                                <span class="font-bold text-gray-900" x-text="durationDays + ' hari'"></span>
                                <div class="text-[10px] text-gray-500 font-medium" x-text="'Rencana Pukul ' + rentalTime + ' WIB'"></div>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span>Ongkos Kirim</span>
                            <span class="font-bold" :class="ship === 'ambil' ? 'text-emerald-600' : 'text-gray-400'" x-text="ship === 'ambil' ? 'Gratis' : 'Dihitung kurir'"></span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold text-gray-800">Total Akhir</span>
                            <span class="text-xl font-extrabold text-primary-600" x-text="rp(grandTotal)"></span>
                        </div>
                    </div>

                    <div class="bg-primary-50 rounded-xl p-3 text-[11px] text-[#7C2D12] leading-relaxed border border-primary-100">
                        * Pembayaran dapat dilakukan via Transfer Bank / QRIS setelah status jaminan identitas diverifikasi oleh admin toko.
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection