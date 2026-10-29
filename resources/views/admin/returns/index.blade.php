@extends('layouts.admin')

@section('title', 'Penerimaan & Denda Retur')
@section('page-title', 'Penerimaan & Denda Retur')

@section('content')
<div x-data="returnCalculator()" class="space-y-5">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Penerimaan & Denda Retur</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola penerimaan barang kembali dari customer, pemeriksaan kondisi fisik unit, dan perhitungan denda keterlambatan.</p>
    </div>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Notifikasi error --}}
    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            <p class="font-bold mb-1">Gagal memproses pengembalian:</p>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Filter (Desain Seragam dengan Fitur Pengiriman) --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-5">
        <form method="GET" action="{{ route('admin.returns.index') }}"
              class="flex flex-col lg:flex-row gap-3 items-end">

            {{-- Input pencarian --}}
            <div class="flex-1 w-full">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Cari Pesanan</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nama customer atau kode order..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            {{-- Filter status --}}
            <div class="w-full lg:w-56">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Status Pengembalian</label>
                <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">-- Semua Status --</option>
                    <option value="telat" {{ request('status') === 'telat' ? 'selected' : '' }}>Melewati Jatuh Tempo (Telat)</option>
                    <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Sudah Diajukan Customer</option>
                    <option value="menunggu_denda" {{ request('status') === 'menunggu_denda' ? 'selected' : '' }}>Menunggu Pembayaran Denda</option>
                </select>
            </div>

            {{-- Filter tanggal dari --}}
            <div class="w-full lg:w-44">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            {{-- Filter tanggal sampai --}}
            <div class="w-full lg:w-44">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            {{-- Tombol aksi --}}
            <div class="flex gap-2 shrink-0 w-full lg:w-auto">
                <button type="submit"
                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-md transition shadow-xs">
                    Filter
                </button>
                <a href="{{ route('admin.returns.index') }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-md transition">
                    Reset
                </a>
            </div>

        </form>
    </div>

    {{-- Tabel Penerimaan & Denda Retur --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Order & Customer</th>
                        <th class="px-6 py-4">Detail Barang</th>
                        <th class="px-6 py-4 whitespace-nowrap">Jatuh Tempo</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Keterlambatan</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Fisik</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($orders as $order)
                        @php
                            $ret = $order->returnModel;
                            $compareTime = $ret?->return_date ? \Carbon\Carbon::parse($ret->return_date) : now();
                            $isLate = $order->rental_end_date ? $compareTime->greaterThan($order->rental_end_date) : false;

                            // Cek apakah unit pada order ini sudah dilakukan inspeksi fisik 'setelah_kembali'
                            $allocatedUnits = $order->orderItems->flatMap(fn($it) => $it->orderItemUnits->map(fn($oiu) => $oiu->equipmentUnit)->filter());
                            $checkedReturnCount = $allocatedUnits->filter(function ($u) {
                                return $u->conditionChecks && $u->conditionChecks->where('check_type', 'setelah_kembali')->isNotEmpty();
                            })->count();
                            $totalUnitsCount = $allocatedUnits->count();

                            $modalPayload = [
                                'id' => $order->id,
                                'order_number' => $order->order_number ?? (string) $order->id,
                                'order_status' => $order->status,
                                'customer_name' => $order->user->name ?? 'Customer',
                                'due_date_iso' => $order->rental_end_date ? $order->rental_end_date->format('Y-m-d\TH:i:s') : now()->format('Y-m-d\TH:i:s'),
                                'due_date_formatted' => $order->rental_end_date ? $order->rental_end_date->translatedFormat('d M Y, H:i') . ' WIB' : '-',
                                'return_submitted' => $ret !== null,
                                'return_date_iso' => $ret?->return_date ? \Carbon\Carbon::parse($ret->return_date)->format('Y-m-d\TH:i:s') : null,
                                'return_date_formatted' => $ret?->return_date ? \Carbon\Carbon::parse($ret->return_date)->translatedFormat('d M Y, H:i') . ' WIB' : null,
                                'return_method' => $ret?->return_method ?? ($order->isPickup() ? 'ambil_toko' : 'kurir'),
                                'courier_name' => $ret?->courier_name,
                                'tracking_number' => $ret?->tracking_number,
                                'customer_notes' => $ret?->notes,
                                'existing_fee' => $ret ? (float) $ret->late_fee_amount : null,
                                'late_fee_status' => $ret?->late_fee_status ?? 'tidak_ada',
                                'late_fee_proof_url' => $ret?->late_fee_proof ? (str_starts_with($ret->late_fee_proof, 'http') ? $ret->late_fee_proof : \Illuminate\Support\Facades\Storage::url($ret->late_fee_proof)) : null,
                                'checked_return_count' => $checkedReturnCount,
                                'total_units_count' => $totalUnitsCount,
                                'items' => $order->orderItems->map(function ($i) {
                                    $dbFee = (float) ($i->product->late_fee_per_hour ?? 0);
                                    $dailyPrice = (float) ($i->product->rental_price_per_day ?? 0);
                                    $effectiveFee = $dbFee > 0 ? $dbFee : ($dailyPrice > 0 ? round($dailyPrice * 0.1, -3) : 10000);

                                    return [
                                        'name' => $i->product->name ?? 'Barang',
                                        'qty' => (int) $i->quantity,
                                        'fee_per_hour' => $effectiveFee,
                                    ];
                                })->values()->toArray(),
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- 1. Order & Customer --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-bold text-gray-900 hover:text-orange-600 transition-colors block">
                                    #{{ $order->order_number ?? $order->id }}
                                </a>
                                <div class="text-xs text-gray-600 font-medium mt-0.5">
                                    {{ $order->user->name ?? 'Customer' }}
                                </div>
                                @if($ret)
                                    <div class="text-[11px] font-semibold {{ $ret->return_method === 'kurir' ? 'text-blue-600' : 'text-purple-600' }} mt-1">
                                        {{ $ret->return_method === 'kurir' ? 'Retur via Kurir (' . ($ret->courier_name ?? '-') . ')' : 'Antar ke Toko' }}
                                    </div>
                                @else
                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                        Durasi: {{ $order->duration_days ?? 0 }} Hari
                                    </div>
                                @endif
                            </td>

                            {{-- 2. Detail Barang --}}
                            <td class="px-6 py-4 align-top">
                                <ul class="text-xs text-gray-700 list-disc list-inside space-y-1">
                                    @forelse($order->orderItems as $item)
                                        @php
                                            $dbFee = (float) ($item->product->late_fee_per_hour ?? 0);
                                            $daily = (float) ($item->product->rental_price_per_day ?? 0);
                                            $displayFee = $dbFee > 0 ? $dbFee : ($daily > 0 ? round($daily * 0.1, -3) : 10000);
                                        @endphp
                                        <li>
                                            <span class="font-semibold">{{ $item->quantity }}x</span> {{ $item->product->name ?? 'Barang' }}
                                            <span class="text-[11px] text-gray-400 block ml-4">
                                                Denda: Rp {{ number_format($displayFee, 0, ',', '.') }}/jam
                                            </span>
                                        </li>
                                    @empty
                                        <li class="text-gray-400 italic">Tidak ada barang</li>
                                    @endforelse
                                </ul>
                            </td>

                            {{-- 3. Jatuh Tempo --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <div class="text-xs font-bold {{ $isLate ? 'text-red-500 font-extrabold' : 'text-gray-800' }}">
                                    {{ $order->rental_end_date ? $order->rental_end_date->translatedFormat('l, d M Y') : '-' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Pukul {{ $order->rental_end_date ? $order->rental_end_date->format('H:i') : '-' }} WIB
                                </div>
                            </td>

                            {{-- 4. Status Keterlambatan --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap space-y-1.5">
                                <div>
                                    @if($isLate)
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                            Telat
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Tepat Waktu
                                        </span>
                                    @endif
                                </div>

                                @if($order->status === 'menunggu_pembayaran_denda')
                                    <div>
                                        <span class="px-2.5 py-0.5 inline-flex text-[11px] leading-5 font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                            Menunggu Bayar Denda
                                        </span>
                                    </div>
                                @elseif($order->status === 'dikembalikan')
                                    <div>
                                        <span class="px-2.5 py-0.5 inline-flex text-[11px] leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                            Diajukan Customer
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- 5. Status Fisik --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                @if($checkedReturnCount > 0)
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Sudah Dicek ({{ $checkedReturnCount }}/{{ max($totalUnitsCount, $checkedReturnCount) }})
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                        Belum Cek Fisik
                                    </span>
                                @endif
                            </td>

                            {{-- 6. Aksi --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.condition-checks.index', ['order_id' => $order->id]) }}"
                                       title="Klik untuk mencatat pemeriksaan kondisi fisik barang"
                                       class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-md transition">
                                        Cek Fisik
                                    </a>

                                    <button type="button"
                                            @click="openModal(@js($modalPayload))"
                                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-md transition-colors focus:ring-2 focus:ring-orange-500 focus:outline-none shadow-sm cursor-pointer">
                                        Proses Pengembalian
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                Tidak ada pesanan dalam antrean pengembalian saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL KALKULATOR DENDA & PENERIMAAN PENGEMBALIAN --}}
    <div x-show="isOpen"
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true"
         @keydown.escape.window="closeModal()">

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

            {{-- Backdrop (Di belakang modal panel) --}}
            <div x-show="isOpen"
                 x-transition.opacity
                 class="fixed inset-0 transition-opacity bg-black/50"
                 @click="closeModal()"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel (Relative z-50 di atas backdrop) --}}
            <div x-show="isOpen"
                 x-transition.scale.origin.center
                 @click.stop
                 class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full whitespace-normal border border-gray-200">

                <form :action="'/admin/returns/' + (activeOrder.id || '') + '/process'" method="POST">
                    @csrf

                    {{-- Modal Header --}}
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-base font-bold text-gray-900" id="modal-title">
                            Proses Pengembalian #<span x-text="activeOrder.order_number"></span>
                        </h3>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 font-bold text-lg p-1">✕</button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">

                        {{-- Info Read-Only (Desain Seragam dengan Modal Pengiriman) --}}
                        <div class="bg-gray-50 border border-gray-200 p-3.5 rounded-md text-xs space-y-2">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Info Pelanggan</p>
                                    <p class="text-sm font-semibold text-gray-800" x-text="activeOrder.customer_name"></p>
                                </div>
                                <div class="text-right">
                                    <a :href="'/admin/orders/' + activeOrder.id"
                                       class="inline-block text-xs font-semibold text-orange-600 hover:text-orange-700 hover:underline">
                                        Lihat Detail Pesanan
                                    </a>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-200 grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Jatuh Tempo Sewa</p>
                                    <p class="text-xs font-semibold text-gray-800 mt-0.5" x-text="activeOrder.due_date_formatted"></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Diterima Kembali</p>
                                    <p class="text-xs font-semibold text-gray-800 mt-0.5" x-text="currentTimeFormatted"></p>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-200">
                                <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px] mb-1">Daftar Barang & Tarif Denda</p>
                                <template x-for="item in activeOrder.items" :key="item.name">
                                    <div class="flex justify-between text-gray-700 py-0.5">
                                        <span x-text="'• ' + item.name + ' (' + item.qty + 'x)'"></span>
                                        <span class="font-semibold text-gray-600" x-text="'Rp ' + formatNumber(item.fee_per_hour) + '/jam'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Info Pengajuan Retur dari Customer (Jika Ada) --}}
                        <template x-if="activeOrder.return_submitted">
                            <div class="bg-blue-50 border border-blue-200 p-3.5 rounded-md text-xs space-y-1.5">
                                <p class="text-blue-800 font-bold uppercase tracking-wider text-[10px]">Info Pengembalian dari Customer</p>
                                <p class="text-blue-900">
                                    Metode: <strong x-text="activeOrder.return_method === 'kurir' ? 'Kirim via Kurir (' + (activeOrder.courier_name || '-') + ')' : 'Diantar Langsung ke Toko'"></strong>
                                    <template x-if="activeOrder.tracking_number">
                                        <span> | No. Resi: <strong class="font-mono" x-text="activeOrder.tracking_number"></strong></span>
                                    </template>
                                </p>
                                <template x-if="activeOrder.customer_notes">
                                    <p class="text-blue-800" x-text="'Catatan: ' + activeOrder.customer_notes"></p>
                                </template>
                                <template x-if="activeOrder.late_fee_proof_url">
                                    <div class="pt-2 space-y-2">
                                        <p class="text-[11px] font-bold text-blue-900">Bukti Bayar Denda dari Customer:</p>
                                        <a :href="activeOrder.late_fee_proof_url" target="_blank" rel="noopener noreferrer" class="block">
                                            <img :src="activeOrder.late_fee_proof_url"
                                                 alt="Bukti Pembayaran Denda"
                                                 class="max-h-40 w-auto rounded-md border border-blue-200 bg-white p-1 object-contain shadow-xs">
                                        </a>
                                        <a :href="activeOrder.late_fee_proof_url" target="_blank" rel="noopener noreferrer"
                                           class="inline-block px-2.5 py-1 bg-white text-blue-700 font-semibold rounded border border-blue-300 hover:bg-blue-100 transition">
                                            Lihat Bukti Pembayaran Denda
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Pintasan Pengecekan Fisik & Laporan Kerusakan --}}
                        <div class="bg-gray-50 border border-gray-200 p-3.5 rounded-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
                            <div>
                                <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Pemeriksaan Kondisi Barang</p>
                                <p class="text-gray-700 font-medium mt-0.5"
                                   x-text="activeOrder.checked_return_count > 0 ? 'Unit sudah diperiksa (' + activeOrder.checked_return_count + ' unit).' : 'Pastikan kondisi fisik barang telah diperiksa.'"></p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a :href="'/admin/condition-checks?order_id=' + activeOrder.id"
                                   class="px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold rounded-md transition">
                                    Cek Fisik
                                </a>
                                <a :href="'/admin/damage-cases/create?order_id=' + activeOrder.id"
                                   class="px-3 py-1.5 bg-red-50 border border-red-200 hover:bg-red-100 text-red-700 font-semibold rounded-md transition">
                                    Lapor Kerusakan
                                </a>
                            </div>
                        </div>

                        {{-- Kalkulasi Otomatis Keterlambatan --}}
                        <template x-if="lateHours > 0">
                            <div class="p-3.5 bg-red-50 border border-red-200 rounded-md space-y-2 text-xs">
                                <div>
                                    <p class="text-sm font-bold text-red-800">
                                        Terdeteksi Telat <span x-text="lateDurationText"></span> (<span x-text="lateHours"></span> Jam)
                                    </p>
                                    <p class="text-xs text-red-600 mt-0.5">
                                        Rincian kalkulasi otomatis (Jam Telat x Nominal Denda/Jam):
                                    </p>
                                </div>

                                <div class="bg-white rounded border border-red-200 p-2.5 space-y-1">
                                    <template x-for="(item, idx) in activeOrder.items" :key="idx">
                                        <div class="flex justify-between text-gray-700">
                                            <span x-text="item.name + ' (' + item.qty + 'x @ Rp ' + formatNumber(item.fee_per_hour) + '/jam x ' + lateHours + ' jam)'"></span>
                                            <span class="font-semibold text-red-700" x-text="'Rp ' + formatNumber(item.fee_per_hour * item.qty * lateHours)"></span>
                                        </div>
                                    </template>
                                    <div class="pt-1.5 mt-1 border-t border-red-100 flex justify-between font-bold text-red-900">
                                        <span>Total Denda Otomatis</span>
                                        <span x-text="'Rp ' + calculatedFeeFormatted"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="lateHours <= 0">
                            <div class="p-3 text-center text-xs text-emerald-700 bg-emerald-50 rounded-md border border-emerald-200 font-medium">
                                Barang dikembalikan tepat waktu sebelum batas jatuh tempo sewa (Total Denda: Rp 0).
                            </div>
                        </template>

                        {{-- Input Override Admin --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Nominal Denda / Override Admin (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-2 text-xs" x-show="calculatedFee > 0">
                                    <button type="button"
                                            @click="overrideFee = calculatedFee"
                                            class="font-semibold text-orange-600 hover:underline">
                                        Sesuai Sistem
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button"
                                            @click="overrideFee = 0"
                                            class="font-semibold text-gray-600 hover:underline">
                                        Hapus Denda (0)
                                    </button>
                                </div>
                            </div>
                            <input type="number"
                                   name="late_fee_amount"
                                   min="0"
                                   step="1000"
                                   required
                                   x-model.number="overrideFee"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm font-semibold text-gray-900 focus:ring-orange-500 focus:border-orange-500 px-3 py-2 border">
                            <p class="text-xs text-gray-500 mt-1">
                                Kalkulasi otomatis: <strong class="text-gray-800">Rp <span x-text="calculatedFeeFormatted"></span></strong>. Admin dapat mengubah nominal denda atau mengisinya dengan 0.
                            </p>
                        </div>

                        <input type="hidden" name="action_type" x-ref="actionTypeInput" :value="(Number(overrideFee) > 0 && !activeOrder.late_fee_proof_url) ? 'denda' : 'normal'">

                    </div>

                    {{-- Modal Footer (Desain Seragam dengan Modal Pengiriman) --}}
                    <div class="flex flex-wrap justify-end gap-2 px-6 py-3 bg-gray-50 border-t border-gray-200">
                        <button type="button"
                                @click="closeModal()"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold rounded-md transition">
                            Batal
                        </button>

                        {{-- Jika denda = 0 -> Barang Kembali Normal --}}
                        <template x-if="Number(overrideFee) <= 0">
                            <button type="submit"
                                    @click="$refs.actionTypeInput.value = 'normal'"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-md transition shadow-xs cursor-pointer">
                                Barang Kembali Normal
                            </button>
                        </template>

                        {{-- Jika denda > 0 -> Terbitkan Tagihan Denda / Selesaikan --}}
                        <template x-if="Number(overrideFee) > 0">
                            <div class="flex flex-wrap gap-2">
                                <button type="submit"
                                        @click="$refs.actionTypeInput.value = 'denda'"
                                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-md transition shadow-xs cursor-pointer">
                                    Terbitkan Tagihan Denda
                                </button>

                                <button type="submit"
                                        @click="$refs.actionTypeInput.value = 'normal'"
                                        title="Gunakan jika customer sudah melunasi denda"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-md transition shadow-xs cursor-pointer">
                                    Denda Lunas & Selesai
                                </button>
                            </div>
                        </template>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>

{{-- Alpine.js Manager --}}
<script>
    function returnCalculator() {
        return {
            isOpen: false,
            activeOrder: {
                id: null,
                order_number: '',
                customer_name: '',
                due_date_iso: '',
                due_date_formatted: '',
                items: []
            },
            currentTime: new Date(),
            lateHours: 0,
            calculatedFee: 0,
            overrideFee: 0,

            formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(Number(num) || 0);
            },

            get currentTimeFormatted() {
                if (this.activeOrder && this.activeOrder.return_date_formatted) {
                    return this.activeOrder.return_date_formatted;
                }
                return this.currentTime.toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                }) + ' WIB';
            },

            get calculatedFeeFormatted() {
                return this.formatNumber(this.calculatedFee);
            },

            get lateDurationText() {
                if (this.lateHours <= 0) return '';
                const days = Math.floor(this.lateHours / 24);
                const hours = this.lateHours % 24;

                if (days > 0 && hours > 0) return days + ' Hari ' + hours + ' Jam';
                if (days > 0) return days + ' Hari';
                return hours + ' Jam';
            },

            openModal(order) {
                this.activeOrder = order;
                this.currentTime = new Date();
                this.calculateLateFee();
                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
            },

            calculateLateFee() {
                const dueDate = new Date(this.activeOrder.due_date_iso);
                const compareTime = this.activeOrder.return_date_iso
                    ? new Date(this.activeOrder.return_date_iso)
                    : this.currentTime;
                const diffMs = compareTime - dueDate;

                if (diffMs > 0 && !isNaN(dueDate.getTime())) {
                    this.lateHours = Math.ceil(diffMs / (1000 * 60 * 60));

                    let totalFee = 0;
                    (this.activeOrder.items || []).forEach(item => {
                        const feePerHour = parseFloat(item.fee_per_hour) || 0;
                        const qty = parseInt(item.qty) || 1;
                        totalFee += (feePerHour * qty * this.lateHours);
                    });

                    this.calculatedFee = totalFee;
                    this.overrideFee = (this.activeOrder.existing_fee !== null && this.activeOrder.existing_fee > 0)
                        ? this.activeOrder.existing_fee
                        : totalFee;
                } else {
                    this.lateHours = 0;
                    this.calculatedFee = 0;
                    this.overrideFee = 0;
                }
            }
        };
    }
</script>
@endsection
