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

    {{-- ===== FORM TAMBAH REKAMAN ===== --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-5 pb-3 border-b">Tambah Rekaman Baru</h3>

        <form action="{{ route('admin.condition-checks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">-- Pilih Order --</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}">
                            {{ $order->order_number }} — {{ $order->user->name ?? 'Customer' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Hanya pesanan yang sudah dikonfirmasi atau sedang disewa yang ditampilkan.</p>
            </div>

            {{-- Pilih Unit Fisik (Dinamis) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Pilih Unit Fisik <span class="text-red-500">*</span>
                </label>

                {{-- Loading state --}}
                <div x-show="isLoadingUnits" class="text-sm text-gray-400 italic py-2">Memuat daftar unit...</div>

                {{-- Dropdown unit --}}
                <select
                    x-show="!isLoadingUnits"
                    name="equipment_unit_id"
                    :disabled="units.length === 0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400"
                >
                    <option value="">
                        <span x-text="selectedOrderId ? (units.length === 0 ? '-- Tidak ada unit tersedia --' : '-- Pilih Unit --') : '-- Pilih order dulu --'"></span>
                    </option>
                    <template x-for="unit in units" :key="unit.id">
                        <option :value="unit.id" x-text="unit.label"></option>
                    </template>
                </select>

                <p class="text-xs text-gray-400 mt-1">Unit fisik akan muncul setelah memilih pesanan.</p>
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
                            <input type="radio" name="check_type" value="sebelum_serah" class="text-indigo-600"
                                {{ old('check_type') === 'sebelum_serah' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Sebelum Diserahkan ke Customer</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="check_type" value="setelah_kembali" class="text-indigo-600"
                                {{ old('check_type') === 'setelah_kembali' ? 'checked' : '' }}>
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
                            <input type="radio" name="condition" value="baik" class="text-emerald-600"
                                {{ old('condition') === 'baik' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Baik</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="rusak_ringan" class="text-amber-600"
                                {{ old('condition') === 'rusak_ringan' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Rusak Ringan</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="rusak_berat" class="text-red-600"
                                {{ old('condition') === 'rusak_berat' ? 'checked' : '' }}>
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >{{ old('notes') }}</textarea>
            </div>

            {{-- Upload Foto --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Kondisi</label>
                <input
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                >
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WebP. Maks 5 MB.</p>
            </div>

            {{-- Tombol Submit --}}
            <div class="pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-colors"
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
                                    <a href="{{ Storage::url($check->image) }}" target="_blank" class="text-indigo-600 hover:underline text-xs">Lihat Foto</a>
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
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Belum ada rekaman kondisi fisik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('conditionCheckForm', () => ({
        selectedOrderId: '',
        units: [],
        isLoadingUnits: false,

        /**
         * Dipanggil saat order dipilih.
         * Mengambil daftar unit dari server via fetch (AJAX).
         */
        async loadUnits() {
            this.units = [];

            if (!this.selectedOrderId) return;

            this.isLoadingUnits = true;

            try {
                // Panggil route /admin/condition-checks/units?order_id=xxx
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
            } catch (error) {
                console.error('Gagal memuat unit:', error);
                this.units = [];
            } finally {
                this.isLoadingUnits = false;
            }
        }
    }));
});
</script>

@endsection