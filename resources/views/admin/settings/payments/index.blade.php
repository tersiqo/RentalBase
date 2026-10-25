@extends('layouts.admin')

@section('title', 'Metode Pembayaran')

@section('content')
<div class="max-w-6xl mx-auto space-y-6"
     x-data="paymentMethodsApp({
         routes: {
             store: '{{ route('admin.settings.payments.store') }}',
             baseUpdate: '{{ url('/admin/settings/payments') }}'
         }
     })">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1">
                <span>Pengaturan</span>
                <span>/</span>
                <span class="text-orange-600 font-semibold">Metode Pembayaran</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Metode Pembayaran Toko</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola rekening bank dan barcode QRIS untuk penerimaan pembayaran order sewa dari customer.</p>
        </div>

        <div>
            <button type="button"
                    @click="openCreateModal()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 active:scale-95 text-white text-xs font-bold transition-all shadow-md shadow-orange-500/20">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Metode Pembayaran</span>
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm text-emerald-800 text-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-emerald-900">Berhasil!</p>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-800 p-1.5 rounded-lg hover:bg-emerald-100/50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             class="flex items-center justify-between p-4 bg-rose-50 border border-rose-200 rounded-2xl shadow-sm text-rose-800 text-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-rose-900">Perhatian!</p>
                    <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
            <button @click="show = false" type="button" class="text-rose-500 hover:text-rose-800 p-1.5 rounded-lg hover:bg-rose-100/50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2 text-sm font-bold text-red-900 mb-1.5">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Validasi gagal, silakan periksa input Anda:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-red-700 space-y-1 pl-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Spesifikasi 1: Alert Peringatan Jika is_active = 0 --}}
    @php
        $activeCount = $paymentMethods->where('is_active', true)->count();
    @endphp

    @if($activeCount === 0)
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300 rounded-3xl p-5 sm:p-6 shadow-sm relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/15 border border-amber-300/80 flex items-center justify-center shrink-0 text-amber-700">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-base font-extrabold text-amber-950">Toko Belum Memiliki Metode Pembayaran Aktif!</h3>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300">
                                Perhatian Kritis
                            </span>
                        </div>
                        <p class="text-xs text-amber-800 leading-relaxed max-w-2xl">
                            Saat ini tidak ada rekening bank atau QRIS yang berstatus aktif. <strong>Customer tidak akan dapat melakukan konfirmasi pembayaran order sewa</strong> pada katalog online toko Anda sampai minimal satu metode pembayaran diaktifkan.
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="openCreateModal()"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-all shadow-md shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Rekening / QRIS</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Spesifikasi 2: Tabel / Grid List Metode Pembayaran --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-stone-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-gray-900">Daftar Rekening & QRIS</h2>
                <p class="text-xs text-gray-500 mt-0.5">Seluruh instruksi pembayaran yang dapat dipilih oleh customer saat menyelesaikan pesanan.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500">
                <span>Total: <strong>{{ $paymentMethods->count() }}</strong></span>
                <span>&bull;</span>
                <span class="text-emerald-600">Aktif: <strong>{{ $activeCount }}</strong></span>
            </div>
        </div>

        @if($paymentMethods->isEmpty())
            {{-- Empty State --}}
            <div class="p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-3xl bg-orange-50 border border-orange-100 flex items-center justify-center mx-auto text-orange-500 shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div class="max-w-md mx-auto">
                    <h3 class="text-base font-bold text-gray-900">Belum Ada Metode Pembayaran</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Toko Anda belum memiliki rekening bank ataupun barcode QRIS. Daftarkan rekening transfer atau unggah QRIS toko Anda agar pelanggan dapat membayar sewa secara mandiri.
                    </p>
                </div>
                <div class="pt-2">
                    <button type="button"
                            @click="openCreateModal()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold transition-all shadow-md shadow-orange-500/20">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Metode Pertama</span>
                    </button>
                </div>
            </div>
        @else
            {{-- Table List --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-stone-50/40 text-[11px] font-extrabold uppercase tracking-wider text-gray-400">
                            <th class="py-3.5 px-6">Tipe Metode</th>
                            <th class="py-3.5 px-6">Nama Bank / Merchant</th>
                            <th class="py-3.5 px-6">Detail Rekening / Barcode</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach($paymentMethods as $method)
                            <tr class="hover:bg-stone-50/60 transition-colors group">
                                {{-- Kolom Tipe --}}
                                <td class="py-4 px-6">
                                    @if($method->type === 'bank_transfer')
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200/60 font-bold">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                            </svg>
                                            <span>Transfer Bank</span>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 border border-purple-200/60 font-bold">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                            <span>QRIS Statis</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Kolom Nama Bank / Merchant --}}
                                <td class="py-4 px-6">
                                    @if($method->type === 'bank_transfer')
                                        <p class="font-extrabold text-gray-900 text-sm tracking-tight">{{ $method->bank_name }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">a.n. <strong class="text-gray-700">{{ $method->account_name }}</strong></p>
                                    @else
                                        <p class="font-extrabold text-gray-900 text-sm tracking-tight">{{ $method->qris_merchant_name }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">NMID / Label Merchant</p>
                                    @endif
                                </td>

                                {{-- Kolom Detail Rekening / QRIS --}}
                                <td class="py-4 px-6">
                                    @if($method->type === 'bank_transfer')
                                        <div class="inline-flex items-center gap-2 bg-stone-100 border border-gray-200/80 px-3 py-1.5 rounded-xl font-mono text-xs font-bold text-gray-800">
                                            <span>{{ $method->account_number }}</span>
                                        </div>
                                    @else
                                        @if($method->qris_image_path)
                                            <div class="flex items-center gap-2.5">
                                                <button type="button"
                                                        @click="openImageLightbox('{{ Storage::url($method->qris_image_path) }}', '{{ $method->qris_merchant_name }}')"
                                                        class="w-12 h-12 rounded-xl border border-gray-200 overflow-hidden p-1 bg-white hover:ring-2 hover:ring-orange-500 transition-all shrink-0 group/img">
                                                    <img src="{{ Storage::url($method->qris_image_path) }}"
                                                         alt="QRIS Barcode"
                                                         class="w-full h-full object-contain">
                                                </button>
                                                <span class="text-[11px] text-gray-500 font-medium">Klik untuk perbesar</span>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Belum ada gambar barcode</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Kolom Status Toggle --}}
                                <td class="py-4 px-6 text-center">
                                    <form action="{{ route('admin.settings.payments.toggle', $method->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                title="Klik untuk mengubah status aktif"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all {{ $method->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $method->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                            <span>{{ $method->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                                @click="openEditModal(@js($method), @js($method->qris_image_path ? Storage::url($method->qris_image_path) : null))"
                                                class="w-8 h-8 rounded-lg bg-stone-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600 flex items-center justify-center transition-colors"
                                                title="Edit Metode">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>

                                        {{-- Tombol Hapus (SoftDeletes) --}}
                                        <button type="button"
                                                @click="openDeleteModal('{{ route('admin.settings.payments.destroy', $method->id) }}', '{{ $method->type === 'bank_transfer' ? $method->bank_name . ' (' . $method->account_number . ')' : $method->qris_merchant_name }}')"
                                                class="w-8 h-8 rounded-lg bg-stone-100 text-gray-600 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-colors"
                                                title="Hapus Metode">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Spesifikasi 3: Modal Tambah & Edit Metode Pembayaran (Alpine.js) --}}
    <div x-show="modalFormOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">

        {{-- Backdrop --}}
        <div x-show="modalFormOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeFormModal()"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="modalFormOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative w-full max-w-xl rounded-3xl bg-white shadow-2xl border border-gray-100 overflow-hidden">

                {{-- Modal Header --}}
                <div class="px-6 py-5 border-b border-gray-100 bg-stone-50/70 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900" x-text="isEditing ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'"></h3>
                        <p class="text-xs text-gray-500 mt-0.5">Tentukan jenis metode pembayaran dan instruksi bagi pelanggan.</p>
                    </div>
                    <button @click="closeFormModal()" type="button" class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form :action="formAction"
                      method="POST"
                      enctype="multipart/form-data"
                      class="p-6 space-y-6">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Radio Button Pilihan Tipe Metode Pembayaran --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                            Pilih Tipe Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                                   :class="form.type === 'bank_transfer' ? 'bg-orange-50/60 border-orange-500 ring-2 ring-orange-500/20' : 'bg-white border-gray-200 hover:bg-stone-50'">
                                <input type="radio"
                                       name="type"
                                       value="bank_transfer"
                                       x-model="form.type"
                                       class="sr-only">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                     :class="form.type === 'bank_transfer' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600'">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-900">Transfer Bank</span>
                                    <span class="block text-[11px] text-gray-500">BCA, Mandiri, BRI, dll.</span>
                                </div>
                            </label>

                            <label class="relative flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                                   :class="form.type === 'qris' ? 'bg-purple-50/60 border-purple-500 ring-2 ring-purple-500/20' : 'bg-white border-gray-200 hover:bg-stone-50'">
                                <input type="radio"
                                       name="type"
                                       value="qris"
                                       x-model="form.type"
                                       class="sr-only">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                     :class="form.type === 'qris' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600'">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-900">QRIS Statis</span>
                                    <span class="block text-[11px] text-gray-500">Scan barcode instan</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Form Khusus: Transfer Bank (x-show) --}}
                    <div x-show="form.type === 'bank_transfer'" x-transition class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                Nama Bank <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="bank_name"
                                   x-model="form.bank_name"
                                   list="bank-presets"
                                   placeholder="Contoh: BCA / Bank Central Asia"
                                   :required="form.type === 'bank_transfer'"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <datalist id="bank-presets">
                                <option value="BCA">
                                <option value="Bank Mandiri">
                                <option value="BNI">
                                <option value="BRI">
                                <option value="Bank Syariah Indonesia (BSI)">
                                <option value="CIMB Niaga">
                                <option value="Permata Bank">
                            </datalist>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                    Nomor Rekening <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       name="account_number"
                                       x-model="form.account_number"
                                       placeholder="Contoh: 1234567890"
                                       :required="form.type === 'bank_transfer'"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono font-bold text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                    Nama Pemilik Rekening (a.n) <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       name="account_name"
                                       x-model="form.account_name"
                                       placeholder="Contoh: Budi Santoso"
                                       :required="form.type === 'bank_transfer'"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            </div>
                        </div>
                    </div>

                    {{-- Form Khusus: QRIS (x-show) --}}
                    <div x-show="form.type === 'qris'" x-transition class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                Nama Merchant QRIS / Usaha <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="qris_merchant_name"
                                   x-model="form.qris_merchant_name"
                                   placeholder="Contoh: RENTALBASE SURABAYA / NMID..."
                                   :required="form.type === 'qris'"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        </div>

                        {{-- Uploader Gambar Barcode QRIS dengan Instant Preview --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                                Upload Gambar Barcode QRIS <span x-show="!isEditing" class="text-red-500">*</span>
                            </label>

                            <div class="flex items-center gap-4">
                                {{-- Preview Box --}}
                                <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 bg-stone-50 overflow-hidden flex items-center justify-center shrink-0">
                                    <template x-if="form.qrisPreview">
                                        <img :src="form.qrisPreview" alt="Preview QRIS" class="w-full h-full object-contain p-1">
                                    </template>
                                    <template x-if="!form.qrisPreview">
                                        <div class="text-center p-2">
                                            <svg class="w-6 h-6 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span class="text-[9px] text-gray-400 block mt-0.5">Pilih QRIS</span>
                                        </div>
                                    </template>
                                </div>

                                <div class="space-y-1.5 flex-1">
                                    <input type="file"
                                           name="qris_image"
                                           accept="image/png,image/jpeg,image/jpg,image/webp"
                                           @change="handleQrisFileSelect($event)"
                                           class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                                    <p class="text-[11px] text-gray-400">
                                        Format: <strong>JPG, PNG, WebP</strong>. Maksimal <strong>2MB</strong>.
                                    </p>
                                    <template x-if="form.qrisError">
                                        <p class="text-[11px] text-red-600 font-bold" x-text="form.qrisError"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Instruksi Pembayaran (Opsional) --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                            Instruksi Tambahan (Opsional)
                        </label>
                        <textarea name="instructions"
                                  x-model="form.instructions"
                                  rows="2"
                                  placeholder="Contoh: Mohon cantumkan ID Pesanan pada berita transfer, simpan bukti struk transfer..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"></textarea>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="flex items-center gap-2.5 pt-1">
                        <input type="checkbox"
                               id="modal_is_active"
                               name="is_active"
                               value="1"
                               x-model="form.is_active"
                               class="w-4 h-4 rounded text-orange-500 focus:ring-orange-500 border-gray-300">
                        <label for="modal_is_active" class="text-xs font-bold text-gray-700 cursor-pointer">
                            Aktifkan metode pembayaran ini sekarang
                        </label>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="closeFormModal()"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-stone-50 text-gray-700 text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-extrabold shadow-md shadow-orange-500/25 transition">
                            <span x-text="isEditing ? 'Perbarui Metode' : 'Simpan Metode'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus (SoftDeletes) --}}
    <div x-show="modalDeleteOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalDeleteOpen = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-3xl bg-white shadow-2xl p-6 border border-gray-100 text-center space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900">Hapus Metode Pembayaran?</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Metode <strong class="text-gray-800" x-text="deleteTargetName"></strong> akan dinonaktifkan dan dihapus menggunakan <strong>SoftDeletes</strong>. Data riwayat transaksi sebelumnya tetap aman.
                    </p>
                </div>
                <form :action="deleteActionUrl" method="POST" class="flex items-center justify-center gap-3 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="modalDeleteOpen = false" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 text-xs font-bold hover:bg-stone-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md transition">
                        Ya, Hapus Metode
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Preview Barcode QRIS --}}
    <div x-show="modalLightboxOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/75 backdrop-blur-sm" @click="modalLightboxOpen = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative max-w-sm w-full bg-white rounded-3xl p-6 shadow-2xl text-center space-y-3">
                <button @click="modalLightboxOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <h4 class="text-sm font-bold text-gray-900" x-text="lightboxMerchant"></h4>
                <div class="w-64 h-64 mx-auto rounded-2xl border border-gray-200 overflow-hidden p-2 bg-white shadow-inner">
                    <img :src="lightboxUrl" alt="Barcode QRIS" class="w-full h-full object-contain">
                </div>
                <p class="text-[11px] text-gray-400">Barcode QRIS siap di-scan menggunakan seluruh aplikasi e-wallet & mobile banking.</p>
            </div>
        </div>
    </div>

</div>

{{-- Alpine.js Payment Methods Controller --}}
<script>
    function paymentMethodsApp(config) {
        return {
            modalFormOpen: false,
            modalDeleteOpen: false,
            modalLightboxOpen: false,
            isEditing: false,
            formAction: config.routes.store,
            deleteActionUrl: '',
            deleteTargetName: '',
            lightboxUrl: '',
            lightboxMerchant: '',

            form: {
                id: null,
                type: 'bank_transfer',
                bank_name: '',
                account_number: '',
                account_name: '',
                qris_merchant_name: '',
                instructions: '',
                is_active: true,
                qrisPreview: null,
                qrisError: ''
            },

            openCreateModal() {
                this.isEditing = false;
                this.formAction = config.routes.store;
                this.form = {
                    id: null,
                    type: 'bank_transfer',
                    bank_name: '',
                    account_number: '',
                    account_name: '',
                    qris_merchant_name: '',
                    instructions: '',
                    is_active: true,
                    qrisPreview: null,
                    qrisError: ''
                };
                this.modalFormOpen = true;
            },

            openEditModal(item, qrisUrl) {
                this.isEditing = true;
                this.formAction = config.routes.baseUpdate + '/' + item.id;
                this.form = {
                    id: item.id,
                    type: item.type || 'bank_transfer',
                    bank_name: item.bank_name || '',
                    account_number: item.account_number || '',
                    account_name: item.account_name || '',
                    qris_merchant_name: item.qris_merchant_name || '',
                    instructions: item.instructions || '',
                    is_active: Boolean(item.is_active),
                    qrisPreview: qrisUrl || null,
                    qrisError: ''
                };
                this.modalFormOpen = true;
            },

            closeFormModal() {
                this.modalFormOpen = false;
            },

            openDeleteModal(actionUrl, name) {
                this.deleteActionUrl = actionUrl;
                this.deleteTargetName = name;
                this.modalDeleteOpen = true;
            },

            openImageLightbox(url, merchant) {
                this.lightboxUrl = url;
                this.lightboxMerchant = merchant;
                this.modalLightboxOpen = true;
            },

            handleQrisFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    this.form.qrisError = 'Ukuran barcode melebihi batas 2MB. Silakan pilih gambar lain.';
                    event.target.value = '';
                    return;
                }

                this.form.qrisError = '';
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.qrisPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
    }
</script>
@endsection
