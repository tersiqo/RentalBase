@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . $order->order_number)
@section('page-title', 'Detail Pesanan')

@section('content')

{{-- Notifikasi --}}
@if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
        {{ session('error') }}
    </div>
@endif

{{-- Tombol kembali + judul --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.orders.index') }}"
       class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-orange-600 transition">
        ← Kembali ke Daftar Pesanan
    </a>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
        Pesanan #{{ $order->order_number }}
    </h1>
    <p class="text-sm text-gray-500 mt-1">
        Masuk pada {{ $order->created_at->translatedFormat('d M Y, H:i') }}
    </p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Kolom kiri: info utama (2/3) --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- ─── KARTU: Info Customer ─── --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Informasi Customer</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Nama</p>
                    <p class="font-semibold text-gray-900">{{ $order->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Email</p>
                    <p class="font-semibold text-gray-900">{{ $order->user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">No. HP</p>
                    <p class="font-semibold text-gray-900">{{ $order->user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Alamat Pengiriman</p>
                    <p class="font-semibold text-gray-900">{{ $order->delivery_address ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- ─── KARTU: Ringkasan Pesanan ─── --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Ringkasan Pesanan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Periode Sewa</p>
                    <p class="font-semibold text-gray-900">{{ $order->date_range }}</p>
                    <p class="text-xs text-gray-500">{{ $order->duration_days }} hari</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Total Tagihan</p>
                    <p class="font-semibold text-gray-900 text-lg">{{ $order->formatted_total }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Metode Pengiriman</p>
                    <p class="font-semibold text-gray-900">
                        {{ $order->isPickup() ? '🏪 Ambil di Toko' : '🚚 Kurir' }}
                    </p>
                </div>
            </div>

            {{-- Daftar barang yang dipesan --}}
            <p class="text-xs text-gray-400 uppercase font-bold mb-2">Daftar Barang</p>
            <div class="divide-y divide-gray-100">
                @forelse($order->orderItems as $item)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @if($item->product?->main_image)
                                <img src="{{ str_starts_with($item->product->main_image, 'http')
                                    ? $item->product->main_image
                                    : \Illuminate\Support\Facades\Storage::url($item->product->main_image) }}"
                                     alt="{{ $item->product->name }}"
                                     class="w-10 h-10 rounded object-cover border border-gray-200 shrink-0">
                            @else
                                <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center shrink-0">
                                    <span class="text-gray-400 text-xs">-</span>
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $item->product->name ?? 'Produk dihapus' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->quantity }}x</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900 text-sm">
                                Rp {{ number_format((float)($item->subtotal ?? $item->unit_price * $item->quantity * $order->duration_days), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-3">Tidak ada item pesanan.</p>
                @endforelse
            </div>
        </div>

        {{-- ─── KARTU: Alokasi Unit Fisik ─── --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Alokasi Unit Fisik</h2>

            @foreach($order->orderItems as $item)
                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-800 mb-2">
                        {{ $item->product->name ?? 'Produk' }}
                        <span class="text-gray-400 font-normal">({{ $item->quantity }}x dibutuhkan)</span>
                    </p>

                    {{-- Unit yang sudah dialokasikan --}}
                    @if($item->orderItemUnits->isNotEmpty())
                        <div class="space-y-2 mb-3">
                            @foreach($item->orderItemUnits as $allocation)
                                <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2 text-sm">
                                    <span class="font-medium text-emerald-800">
                                        {{ $allocation->equipmentUnit->unit_code ?? $allocation->equipmentUnit->asset_code ?? 'Unit #' . $allocation->equipment_unit_id }}
                                    </span>
                                    <span class="text-xs text-emerald-600">✓ Dialokasikan</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 italic mb-3">Belum ada unit yang dialokasikan.</p>
                    @endif

                    {{-- Form tambah alokasi (hanya tampil jika status memungkinkan) --}}
                    @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi', 'siap_kirim']))
                        @php
                            // Filter: hanya unit yang produknya cocok dengan item ini
                            $matchingUnits = $availableUnits->filter(function($unit) use ($item) {
                                return $unit->product_id === $item->product_id;
                            });
                        @endphp
                        @if($matchingUnits->isNotEmpty())
                            <form action="{{ route('admin.orders.allocate', $order->id) }}" method="POST" class="flex gap-2 mt-2">
                                @csrf
                                <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                <select name="equipment_unit_id"
                                        class="flex-1 px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($matchingUnits as $unit)
                                        <option value="{{ $unit->id }}">
                                            {{ $unit->unit_code ?? $unit->asset_code ?? 'Unit #' . $unit->id }}
                                            ({{ $unit->product->name ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                        class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-md transition shrink-0">
                                    + Alokasikan
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-amber-600 italic">Tidak ada unit tersedia yang cocok untuk produk ini.</p>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

    </div>

    {{-- Kolom kanan: status, dokumen, aksi (1/3) --}}
    <div class="xl:col-span-1 space-y-6">

        {{-- ─── KARTU: Status Pesanan ─── --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Status Pesanan</h2>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $order->status_color }}">
                {{ $order->status_label }}
            </span>
            @if($order->cancellation_reason)
                <div class="mt-3 bg-red-50 border border-red-200 rounded-md p-3 text-sm text-red-700">
                    <p class="font-bold text-xs uppercase mb-1">Alasan Penolakan</p>
                    <p>{{ $order->cancellation_reason }}</p>
                </div>
            @endif
        </div>

        {{-- ─── KARTU: Aksi Verifikasi ─── --}}
        @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi']))
            <div class="bg-white border border-amber-300 rounded-lg shadow-sm p-5" x-data="{ showRejectForm: false }">
                <h2 class="text-sm font-bold text-amber-600 uppercase tracking-wide mb-4">Tindakan Admin</h2>

                {{-- Tombol Terima --}}
                <div x-show="!showRejectForm">
                    <form action="{{ route('admin.orders.confirm', $order->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-md transition">
                            ✓ Terima & Konfirmasi Pesanan
                        </button>
                    </form>
                    <button @click="showRejectForm = true"
                            class="w-full px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-sm font-semibold rounded-md transition">
                        ✕ Tolak Pesanan
                    </button>
                </div>

                {{-- Form Tolak --}}
                <div x-show="showRejectForm" x-cloak>
                    <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST">
                        @csrf
                        <label class="block text-sm font-bold text-gray-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="cancellation_reason"
                                  rows="4"
                                  required
                                  placeholder="Contoh: Barang tidak tersedia pada tanggal tersebut..."
                                  class="w-full border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-red-500 focus:border-red-500 mb-3"></textarea>
                        <div class="flex gap-2">
                            <button type="button" @click="showRejectForm = false"
                                    class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-md">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-md">
                                Ya, Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ─── KARTU: Bukti Pembayaran ─── --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Bukti Pembayaran</h2>

            @if($order->latestPayment)
                <div class="text-sm space-y-2 mb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Metode</span>
                        <span class="font-semibold text-gray-800">{{ $order->latestPayment->payment_method ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jumlah</span>
                        <span class="font-semibold text-gray-800">
                            Rp {{ number_format((float)($order->latestPayment->amount ?? 0), 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="font-semibold text-gray-800">{{ $order->latestPayment->status ?? '-' }}</span>
                    </div>
                    @if($order->latestPayment->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Dibayar</span>
                            <span class="font-semibold text-gray-800">{{ $order->latestPayment->paid_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Preview struk/bukti transfer --}}
                @if($paymentProofUrl)
                    <p class="text-xs text-gray-400 uppercase font-bold mb-2">Struk / Bukti Transfer</p>
                    <a href="{{ $paymentProofUrl }}" target="_blank" x-data
                       @click.prevent="$dispatch('open-lightbox', { src: '{{ $paymentProofUrl }}' })">
                        <img src="{{ $paymentProofUrl }}"
                             alt="Bukti Pembayaran"
                             class="w-full max-h-48 object-contain border border-gray-200 rounded-md cursor-zoom-in hover:opacity-90 transition">
                    </a>
                    <a href="{{ $paymentProofUrl }}" target="_blank"
                       class="block text-center text-xs text-orange-600 hover:underline mt-1">
                        Buka foto ukuran penuh ↗
                    </a>
                @else
                    <p class="text-sm text-gray-400 italic">Belum ada bukti pembayaran yang diunggah.</p>
                @endif
            @else
                <p class="text-sm text-gray-400 italic">Belum ada data pembayaran.</p>
            @endif
        </div>

        {{-- ─── KARTU: Jaminan Identitas ─── --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Jaminan Identitas</h2>

            @if($order->identityGuarantee)
                <div class="text-sm space-y-2 mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="font-semibold
                            @if($order->identityGuarantee->status === 'diverifikasi') text-emerald-600
                            @elseif($order->identityGuarantee->status === 'ditolak') text-red-600
                            @else text-amber-600 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->identityGuarantee->status ?? '-')) }}
                        </span>
                    </div>
                    @if($order->identityGuarantee->notes)
                        <div>
                            <span class="text-gray-500">Catatan</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $order->identityGuarantee->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Foto KTP --}}
                <p class="text-xs text-gray-400 uppercase font-bold mb-2">Foto KTP</p>
                @if($ktpUrl)
                    <a href="{{ $ktpUrl }}" target="_blank"
                       x-data @click.prevent="$dispatch('open-lightbox', { src: '{{ $ktpUrl }}' })">
                        <img src="{{ $ktpUrl }}"
                             alt="Foto KTP"
                             class="w-full max-h-36 object-contain border border-gray-200 rounded-md mb-3 cursor-zoom-in hover:opacity-90 transition">
                    </a>
                @else
                    <p class="text-sm text-gray-400 italic mb-3">Foto KTP belum tersedia.</p>
                @endif

                {{-- Foto Selfie --}}
                <p class="text-xs text-gray-400 uppercase font-bold mb-2">Foto Selfie</p>
                @if($selfieUrl)
                    <a href="{{ $selfieUrl }}" target="_blank"
                       x-data @click.prevent="$dispatch('open-lightbox', { src: '{{ $selfieUrl }}' })">
                        <img src="{{ $selfieUrl }}"
                             alt="Foto Selfie"
                             class="w-full max-h-36 object-contain border border-gray-200 rounded-md cursor-zoom-in hover:opacity-90 transition">
                    </a>
                @else
                    <p class="text-sm text-gray-400 italic">Foto selfie belum tersedia.</p>
                @endif

            @else
                <p class="text-sm text-gray-400 italic">Tidak ada data jaminan identitas untuk pesanan ini.</p>
            @endif
        </div>

    </div>
</div>

{{-- ─── Lightbox (pop up foto) ─── --}}
<div x-data="{
        open: false,
        src: '',
        init() {
            window.addEventListener('open-lightbox', (e) => {
                this.src = e.detail.src;
                this.open = true;
            });
        }
    }"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center"
     style="background-color: rgba(0,0,0,0.8);"
     @click.self="open = false"
     @keydown.escape.window="open = false">

    <div class="relative max-w-4xl max-h-[90vh] mx-4">
        <button @click="open = false"
                class="absolute -top-10 right-0 text-white text-3xl font-bold hover:text-gray-300 transition">
            ✕
        </button>
        <img :src="src"
             alt="Preview"
             class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
    </div>
</div>

@endsection
