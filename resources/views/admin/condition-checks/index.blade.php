@extends('layouts.admin')

@section('content')
<div x-data="conditionCheckForm()" class="space-y-8">

    {{-- ===== HEADER ===== --}}
    <div>
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Pemeriksaan Kondisi Fisik</h2>
        <p class="text-sm text-gray-500 mt-1">Dokumentasi kondisi unit barang sebelum diserahkan dan setelah dikembalikan customer.</p>
    </div>

    {{-- ===== PESAN SUKSES / ERROR ===== --}}
    @if(session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- ===== BANNER KONTEKSTUAL JIKA DATANG DARI ORDER TERTENTU ===== --}}
    @if($selectedOrder)
        <div class="p-4 rounded-xl bg-orange-50 border border-orange-200/90 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-orange-700 bg-orange-100 px-2 py-0.5 rounded">Konteks Otomatis</span>
                            <h3 class="font-bold text-gray-900 text-sm">Pesanan #{{ $selectedOrder->order_number }}</h3>
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Customer: <strong class="text-gray-900">{{ $selectedOrder->user->name ?? 'Customer' }}</strong> • 
                            Status Pesanan: <span class="font-semibold text-orange-700">{{ $selectedOrder->status_label }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.returns.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Ke Pengembalian</span>
                    </a>
                    <a href="{{ route('admin.orders.show', $selectedOrder->id) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-white transition shadow-2xs">
                        <span>Detail Pesanan</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== FORM TAMBAH REKAMAN ===== --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-5 pb-3 border-b">Tambah Rekaman Baru</h3>

        <form action="{{ route('admin.condition-checks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Hidden input order_id terikat ke Alpine x-model --}}
            <input type="hidden" name="order_id" :value="selectedOrderId">
            <input type="hidden" name="return_to" value="{{ old('return_to', $returnTo ?? '') }}">

            {{-- Error validasi --}}
            @if($errors->any())
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Pilih Order --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Pilih Pesanan <span class="text-red-500">*</span>
                </label>
                <select
                    x-model="selectedOrderId"
                    @change="loadUnits()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                    <option value="">-- Pilih Order --</option>
                    @foreach($orders as $ord)
                        <option value="{{ $ord->id }}" {{ (string)old('order_id', $selectedOrderId ?? '') === (string)$ord->id ? 'selected' : '' }}>
                            {{ $ord->order_number }} — {{ $ord->user->name ?? 'Customer' }} ({{ $ord->status_label }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Hanya pesanan yang sudah dikonfirmasi atau dalam masa sewa/kembali yang ditampilkan.</p>
            </div>

            {{-- Pilih Unit Fisik (Dinamis) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Pilih Unit Fisik <span class="text-red-500">*</span>
                </label>

                {{-- Loading state --}}
                <div x-show="isLoadingUnits" class="text-sm text-gray-400 italic py-2 flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Memuat daftar unit fisik pesanan...</span>
                </div>

                {{-- Dropdown unit --}}
                <select
                    x-show="!isLoadingUnits"
                    name="equipment_unit_id"
                    x-model="selectedUnitId"
                    :disabled="units.length === 0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:bg-gray-100 disabled:text-gray-400"
                    required
                >
                    <option value="" x-text="selectedOrderId ? (units.length === 0 ? '-- Belum ada unit fisik dialokasikan pada order ini --' : '-- Pilih Unit Fisik --') : '-- Pilih pesanan terlebih dahulu --'"></option>
                    <template x-for="unit in units" :key="unit.id">
                        <option :value="unit.id" :selected="String(unit.id) === String(selectedUnitId)" x-text="unit.label"></option>
                    </template>
                </select>

                {{-- Warning jika belum dialokasikan unit --}}
                <p x-show="selectedOrderId && !isLoadingUnits && units.length === 0" class="text-xs text-amber-600 mt-1.5 font-medium flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Pesanan ini belum memiliki alokasi unit fisik. Pastikan unit telah dialokasikan pada saat konfirmasi/pengiriman pesanan.</span>
                </p>
                <p x-show="!selectedOrderId" class="text-xs text-gray-400 mt-1">Unit fisik akan muncul setelah memilih pesanan.</p>
            </div>

            {{-- Dua kolom: Fase & Kondisi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Fase Pemeriksaan --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Fase Pemeriksaan <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="check_type" value="sebelum_serah" x-model="checkType" class="text-orange-600 focus:ring-orange-500">
                            <span class="text-sm text-gray-700">Sebelum Diserahkan ke Customer</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="check_type" value="setelah_kembali" x-model="checkType" class="text-orange-600 focus:ring-orange-500">
                            <span class="text-sm text-gray-700">Setelah Dikembalikan Customer</span>
                        </label>
                    </div>
                </div>

                {{-- Kondisi Barang --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kondisi Barang <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="baik" x-model="condition" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">Baik</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="rusak_ringan" x-model="condition" class="text-amber-600 focus:ring-amber-500">
                            <span class="text-sm text-gray-700">Rusak Ringan</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="rusak_berat" x-model="condition" class="text-red-600 focus:ring-red-500">
                            <span class="text-sm text-gray-700">Rusak Berat</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                <textarea
                    name="notes"
                    rows="3"
                    placeholder="Jelaskan kondisi fisik barang secara detail..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                >{{ old('notes') }}</textarea>
            </div>

            {{-- Upload Foto + Preview --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Kondisi</label>
                <input
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                    @change="previewImage($event)"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
                >
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WebP. Maks 5 MB.</p>

                {{-- Thumbnail Preview --}}
                <div x-show="imagePreview" class="mt-3">
                    <p class="text-xs font-semibold text-gray-600 mb-1">Preview Foto:</p>
                    <div class="relative inline-block border rounded-lg overflow-hidden shadow-sm bg-gray-50">
                        <img :src="imagePreview" class="h-40 w-auto object-cover rounded-md" alt="Preview Foto">
                    </div>
                </div>
            </div>

            {{-- Tombol Submit --}}
            <div class="pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-orange-500 rounded-md hover:bg-orange-600 focus:ring-2 focus:ring-orange-500 focus:outline-none transition-colors shadow-sm"
                >
                    Simpan Rekaman Fisik
                </button>
            </div>
        </form>
    </div>

    {{-- ===== TABEL REKAMAN YANG SUDAH ADA ===== --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800">Daftar Rekaman Kondisi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">Pesanan</th>
                        <th class="px-6 py-4">Unit Fisik</th>
                        <th class="px-6 py-4">Fase</th>
                        <th class="px-6 py-4 text-center">Kondisi</th>
                        <th class="px-6 py-4">Catatan</th>
                        <th class="px-6 py-4 text-center">Foto</th>
                        <th class="px-6 py-4">Diperiksa Oleh</th>
                        <th class="px-6 py-4">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($conditionChecks as $check)
                        <tr class="hover:bg-gray-50">
                            {{-- Kolom Pesanan --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $relatedOrder = $check->equipmentUnit?->orderItemUnits?->last()?->orderItem?->order;
                                @endphp
                                <div class="font-bold text-gray-900">{{ $relatedOrder->order_number ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $relatedOrder->user->name ?? '-' }}</div>
                            </td>
                            {{-- Unit Fisik --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $check->equipmentUnit->unit_code ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $check->equipmentUnit->product->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($check->check_type === 'sebelum_serah')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Sebelum Serah
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        Setelah Kembali
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($check->condition === 'baik')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Baik</span>
                                @elseif($check->condition === 'rusak_ringan')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Rusak Ringan</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Rusak Berat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-gray-600 truncate">{{ $check->notes ?: '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($check->image)
                                    <button 
                                        type="button" 
                                        @click="openImageModal('{{ storage_url($check->image) }}')" 
                                        class="text-orange-600 hover:text-orange-700 hover:underline text-xs font-semibold focus:outline-none"
                                    >
                                        Lihat Foto
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-700">{{ $check->checker->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-700">{{ $check->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $check->created_at->format('H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                Belum ada rekaman kondisi fisik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL POP-UP FOTO (LIGHTBOX) ===== --}}
    <div 
        x-show="modalImage" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="closeImageModal()"
    >
        {{-- Area Klik di Luar Gambar untuk Menutup --}}
        <div class="fixed inset-0" @click="closeImageModal()"></div>

        {{-- Konten Pop-up --}}
        <div 
            x-show="modalImage"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative z-10 max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl p-2 overflow-hidden flex flex-col items-center"
        >
            {{-- Tombol Silang (Close) --}}
            <button 
                @click="closeImageModal()" 
                type="button" 
                class="absolute top-3 right-3 p-1.5 rounded-full bg-black/50 text-white hover:bg-black/75 transition-colors focus:outline-none"
                title="Tutup (Esc)"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            {{-- Gambar --}}
            <img 
                :src="modalImage" 
                alt="Foto Kondisi Fisik" 
                class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-sm"
            >
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('conditionCheckForm', () => ({
        selectedOrderId: '{{ old('order_id', $selectedOrderId ?? '') }}',
        selectedUnitId: '{{ old('equipment_unit_id', $selectedUnitId ?? '') }}',
        checkType: '{{ old('check_type', $defaultCheckType ?? 'setelah_kembali') }}',
        condition: '{{ old('condition', 'baik') }}',
        units: [],
        isLoadingUnits: false,
        imagePreview: null,
        modalImage: null,

        async init() {
            if (this.selectedOrderId) {
                await this.loadUnits();
            }
        },

        openImageModal(url) {
            this.modalImage = url;
        },
        closeImageModal() {
            this.modalImage = null;
        },

        /**
         * Preview gambar lokal saat file dipilih
         */
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.imagePreview = null;
            }
        },

        /**
         * Dipanggil saat order dipilih atau saat inisialisasi awal.
         * Mengambil daftar unit dari server via fetch (AJAX).
         */
        async loadUnits() {
            this.units = [];

            if (!this.selectedOrderId) {
                this.selectedUnitId = '';
                return;
            }

            this.isLoadingUnits = true;

            try {
                const response = await fetch(
                    `{{ route('admin.condition-checks.units') }}?order_id=${this.selectedOrderId}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    }
                );

                if (!response.ok) throw new Error('Request gagal');

                this.units = await response.json();

                // Auto-select unit:
                // 1. Jika ada unit_id sebelumnya (atau dari URL) yang cocok, pertahankan
                // 2. Jika order hanya punya 1 unit fisik, otomatis pilih unit tersebut
                // 3. Jika datang dari konteks order spesifik dan ada unit, default ke unit pertama
                if (this.selectedUnitId && this.units.some(u => String(u.id) === String(this.selectedUnitId))) {
                    // Biarkan selectedUnitId yang sudah valid
                } else if (this.units.length === 1) {
                    this.selectedUnitId = String(this.units[0].id);
                } else if (this.units.length > 1 && '{{ $selectedOrderId ?? '' }}') {
                    this.selectedUnitId = String(this.units[0].id);
                } else {
                    this.selectedUnitId = '';
                }
            } catch (error) {
                console.error('Gagal memuat unit:', error);
                this.units = [];
                this.selectedUnitId = '';
            } finally {
                this.isLoadingUnits = false;
            }
        }
    }));
});
</script>

@endsection