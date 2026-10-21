@extends('layouts.admin')

@section('page-title', 'Buat Tiket Kerusakan')

@section('content')
<div x-data="damageCaseCreate()" class="space-y-6 pb-16 font-sans max-w-5xl">

    {{-- BREADCRUMBS --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.damage-cases.index') }}" class="hover:text-slate-800">Kasus Kerusakan</a>
        <span>/</span>
        <span class="text-rose-600 font-bold">Buat Tiket Baru</span>
    </nav>

    {{-- HEADER PAGE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-rose-600 mb-1">
                <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                <span>Pencatatan Insiden Fisik Unit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Buat Tiket Kasus Kerusakan</h1>
            <p class="text-sm text-slate-500 mt-1">
                Laporkan kerusakan unit sewa setelah penerimaan barang, unggah 3–5 foto bukti, dan tetapkan nominal kompensasi yang ditagihkan kepada penyewa.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.damage-cases.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-900 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Harap Periksa Kembali Formulir Anda:</h4>
                    <ul class="list-disc list-inside text-xs text-red-700 mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.damage-cases.store') }}"
          method="POST"
          enctype="multipart/form-data"
          @submit="submitting = true"
          class="space-y-6">
        @csrf

        {{-- 1. PILIH PESANAN & UNIT FISIK --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Identitas Pesanan & Unit Fisik</h3>
                    <p class="text-xs text-slate-500">Pilih pesanan yang terkait dan tentukan unit fisik perlengkapan yang cacat.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                {{-- Dropdown Order --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilih Pesanan Terkait <span class="text-rose-500">*</span>
                    </label>
                    <select name="order_id"
                            id="order_id"
                            x-model="selectedOrderId"
                            @change="onOrderChange()"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 bg-white transition-all">
                        <option value="">-- Pilih Nomor Pesanan / Customer --</option>
                        @foreach($orders as $ord)
                            <option value="{{ $ord->id }}"
                                    data-customer="{{ $ord->user->name ?? 'Penyewa' }}"
                                    data-email="{{ $ord->user->email ?? '-' }}"
                                    data-status="{{ $ord->status_label }}"
                                    {{ old('order_id', $selectedOrderId) == $ord->id ? 'selected' : '' }}>
                                {{ $ord->order_number ?? ('ORD-' . $ord->id) }} — {{ $ord->user->name ?? 'Penyewa' }} ({{ $ord->status_label }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Hanya menampilkan pesanan milik toko Anda.</p>
                </div>

                {{-- Dropdown Unit Fisik --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Unit Fisik yang Rusak <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="equipment_unit_id"
                                id="equipment_unit_id"
                                x-model="selectedUnitId"
                                required
                                :disabled="loadingUnits || availableUnits.length === 0"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 bg-white transition-all disabled:bg-slate-100 disabled:text-slate-400">
                            <option value="">
                                <span x-text="loadingUnits ? 'Memuat unit...' : (availableUnits.length === 0 ? '-- Pilih pesanan terlebih dahulu --' : '-- Pilih Unit Fisik --')"></span>
                            </option>
                            <template x-for="u in availableUnits" :key="u.id">
                                <option :value="u.id" x-text="'[' + u.unit_code + '] ' + u.product_name + ' (SN: ' + u.serial_number + ')'"></option>
                            </template>
                        </select>
                        <span x-show="loadingUnits" class="absolute right-8 top-3 text-rose-500">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Daftar unit fisik otomatis disaring sesuai item barang dalam pesanan.</p>
                </div>
            </div>

            {{-- Status Unit Fisik Pasca Laporan --}}
            <div class="pt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Pembaruan Status Unit Fisik di Sistem:
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer hover:border-rose-300 transition-all">
                        <input type="radio" name="unit_status" value="rusak" checked class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Status: Rusak (Damaged)</span>
                            <p class="text-[11px] text-slate-500">Unit tidak dapat disewa hingga diselesaikan.</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer hover:border-amber-300 transition-all">
                        <input type="radio" name="unit_status" value="maintenance" class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Status: Perawatan (Maintenance)</span>
                            <p class="text-[11px] text-slate-500">Unit langsung masuk proses servis toko.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- 2. DESKRIPSI & FOTO BUKTI KERUSAKAN --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Deskripsi & Bukti Foto Cacat Fisik</h3>
                    <p class="text-xs text-slate-500">Jelaskan kronologi/bagian yang rusak dan sertakan 3–5 foto bukti dari berbagai sudut.</p>
                </div>
            </div>

            {{-- Textarea Deskripsi --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Deskripsi Kerusakan <span class="text-rose-500">*</span>
                </label>
                <textarea name="description"
                          rows="4"
                          required
                          placeholder="Jelaskan secara spesifik kerusakan yang terjadi (contoh: sambungan rangka patah, kain robek pada bagian sandaran, engsel kanopi macet, atau ada part yang hilang)..."
                          class="w-full p-3.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">{{ old('description') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">Deskripsi ini akan dibaca oleh penyewa di halaman Laporan Kerusakan Customer.</p>
            </div>

            {{-- Multi-upload 3–5 Foto Bukti --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Unggah Foto Bukti Kerusakan (3–5 Foto Disarankan)
                    </label>
                    <span class="text-xs text-slate-400" x-text="selectedFiles.length + ' foto terpilih (maks 5)'"></span>
                </div>

                {{-- Drop Area --}}
                <div class="border-2 border-dashed border-slate-200 hover:border-rose-400 rounded-2xl p-6 text-center cursor-pointer bg-slate-50/50 hover:bg-rose-50/30 transition-all"
                     @click="$refs.fileInput.click()">
                    <input type="file"
                           x-ref="fileInput"
                           name="photos[]"
                           multiple
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           @change="handleFiles($event)"
                           class="hidden">

                    <div class="w-12 h-12 rounded-2xl bg-white shadow-xs text-rose-500 flex items-center justify-center mx-auto mb-2 border border-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-800">Klik di sini atau seret foto bukti kerusakan</p>
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 3 MB per foto. Maksimal 5 foto.</p>
                </div>

                {{-- Previews Grid --}}
                <div x-show="selectedFiles.length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3" x-cloak>
                    <template x-for="(file, index) in selectedFiles" :key="index">
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-white aspect-square shadow-xs">
                            <img :src="file.preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button"
                                        @click="removeFile(index)"
                                        class="p-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                            <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded text-[10px] bg-black/60 text-white font-mono" x-text="'Foto ' + (index + 1)"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- 3. PENETAPAN KLAIM KOMPENSASI FINANSIAL --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Kalkulasi Klaim Finansial Kompensasi</h3>
                    <p class="text-xs text-slate-500">Tentukan estimasi biaya perbaikan dan denda kelalaian (jika ada).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                {{-- Biaya Perbaikan --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Biaya Perbaikan / Sparepart (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">
                            Rp
                        </span>
                        <input type="number"
                               name="repair_cost"
                               x-model.number="repairCost"
                               min="0"
                               step="1000"
                               required
                               placeholder="150000"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Estimasi pengeluaran riil untuk servis / suku cadang.</p>
                </div>

                {{-- Denda Tambahan --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Denda Kelalaian / Penalti Kerusakan (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">
                            Rp
                        </span>
                        <input type="number"
                               name="fine_amount"
                               x-model.number="fineAmount"
                               min="0"
                               step="1000"
                               value="0"
                               placeholder="0"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Opsional: denda administrasi tambahan jika diatur oleh kebijakan toko.</p>
                </div>
            </div>

            {{-- Total Klaim Card --}}
            <div class="rounded-2xl bg-rose-50/70 border border-rose-200/80 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Total Klaim Kompensasi Ganti Rugi</span>
                    <p class="text-xs text-rose-600/80 mt-0.5">Jumlah yang akan ditagihkan ke penyewa di portal customer.</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl sm:text-3xl font-extrabold text-rose-700" x-text="formatRupiah(totalClaim)"></span>
                </div>
            </div>
        </div>

        {{-- BUTTONS --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.damage-cases.index') }}"
               class="px-5 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition-all">
                Batal
            </a>
            <button type="submit"
                    :disabled="submitting || !selectedOrderId || !selectedUnitId || repairCost < 0"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-sm bg-rose-600 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-md shadow-rose-600/20 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span x-text="submitting ? 'Menerbitkan...' : 'Terbitkan Tiket Kasus Kerusakan'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function damageCaseCreate() {
    return {
        selectedOrderId: '{{ old('order_id', $selectedOrderId) }}',
        selectedUnitId: '{{ old('equipment_unit_id') }}',
        repairCost: {{ old('repair_cost', 0) }},
        fineAmount: {{ old('fine_amount', 0) }},
        submitting: false,
        loadingUnits: false,
        availableUnits: [],
        selectedFiles: [],

        init() {
            if (this.selectedOrderId) {
                this.onOrderChange();
            }
        },

        get totalClaim() {
            const r = parseFloat(this.repairCost) || 0;
            const f = parseFloat(this.fineAmount) || 0;
            return Math.max(0, r + f);
        },

        formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        },

        onOrderChange() {
            if (!this.selectedOrderId) {
                this.availableUnits = [];
                this.selectedUnitId = '';
                return;
            }

            this.loadingUnits = true;
            fetch('{{ route('admin.damage-cases.units') }}?order_id=' + this.selectedOrderId)
                .then(res => res.json())
                .then(data => {
                    this.availableUnits = data;
                    if (data.length > 0) {
                        this.selectedUnitId = data[0].id;
                    } else {
                        this.selectedUnitId = '';
                    }
                })
                .catch(() => {
                    this.availableUnits = [];
                    this.selectedUnitId = '';
                })
                .finally(() => {
                    this.loadingUnits = false;
                });
        },

        handleFiles(event) {
            const files = Array.from(event.target.files);
            if (this.selectedFiles.length + files.length > 5) {
                alert('Maksimal 5 foto bukti kerusakan yang dapat diunggah.');
                return;
            }

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.selectedFiles.push({
                        file: file,
                        preview: e.target.result
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        }
    };
}
</script>
@endsection
