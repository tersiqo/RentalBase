@extends('layouts.admin')

@section('title', 'Daftar Siap Kirim')
@section('page-title', 'Daftar Siap Kirim')

@section('content')
<div x-data="shipmentManager()" class="space-y-5">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Daftar Siap Kirim</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola penyerahan barang di toko dan proses pengiriman kurir ke customer.</p>
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

    {{-- Form Filter (Desain Seragam dengan Fitur Master Pesanan) --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-5">
        <form method="GET" action="{{ route('admin.shipments.index') }}"
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

            {{-- Filter metode pengiriman --}}
            <div class="w-full lg:w-52">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Metode Pengiriman</label>
                <select name="method"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">-- Semua Metode --</option>
                    <option value="kurir" {{ request('method') === 'kurir' ? 'selected' : '' }}>Kirim via Kurir</option>
                    <option value="ambil_toko" {{ request('method') === 'ambil_toko' ? 'selected' : '' }}>Ambil di Toko</option>
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
                <a href="{{ route('admin.shipments.index') }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-md transition">
                    Reset
                </a>
            </div>

        </form>
    </div>

    {{-- Tabel Siap Kirim --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Order & Customer</th>
                        <th class="px-6 py-4 whitespace-nowrap">Jadwal Sewa</th>
                        <th class="px-6 py-4">Detail Barang</th>
                        <th class="px-6 py-4">Metode & Tujuan</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status Unit</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($orders as $order)
                        @php
                            $isPickup = $order->isPickup();
                            $isToday = $order->rental_start_date ? \Carbon\Carbon::parse($order->rental_start_date)->isToday() : false;
                            $totalReq = $order->orderItems->sum('quantity');
                            $totalAlloc = $order->orderItems->sum(fn($i) => $i->orderItemUnits->count());
                            $isReadyToShip = ($totalAlloc >= $totalReq && $totalReq > 0);
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
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    {{ $order->created_at ? $order->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                </div>
                            </td>

                            {{-- 2. Jadwal Sewa --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <div class="text-xs font-bold {{ $isToday ? 'text-red-500 font-extrabold' : 'text-gray-800' }}">
                                    @if($isToday)
                                        Hari ini, {{ \Carbon\Carbon::parse($order->rental_start_date)->format('H:i') }} WIB
                                    @else
                                        {{ $order->rental_start_date ? \Carbon\Carbon::parse($order->rental_start_date)->translatedFormat('l, d M Y') : '-' }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Durasi: {{ $order->duration_days ?? 0 }} Hari
                                </div>
                            </td>

                            {{-- 3. Detail Barang --}}
                            <td class="px-6 py-4 align-top">
                                <ul class="text-xs text-gray-700 list-disc list-inside space-y-1">
                                    @forelse($order->orderItems as $item)
                                        <li>
                                            <span class="font-semibold">{{ $item->quantity }}x</span> {{ $item->product->name ?? 'Barang' }}
                                        </li>
                                    @empty
                                        <li class="text-gray-400 italic">Tidak ada barang</li>
                                    @endforelse
                                </ul>
                            </td>

                            {{-- 4. Metode & Tujuan --}}
                            <td class="px-6 py-4 align-top max-w-xs">
                                @if($isPickup)
                                    <div class="text-sm font-semibold text-purple-600">Ambil di Toko</div>
                                    <div class="text-xs text-gray-500 mt-1">Pelanggan ambil langsung di toko rental</div>
                                @else
                                    <div class="text-sm font-semibold text-blue-500">Kirim via Kurir</div>
                                    <div class="text-xs text-gray-600 mt-1 truncate max-w-[220px]" title="{{ $order->shipping_address ?? '-' }}">
                                        {{ $order->shipping_address ?: 'Alamat belum diatur' }}
                                    </div>
                                @endif
                            </td>

                            {{-- 5. Status Unit --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                @if($isReadyToShip)
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Unit Siap ({{ $totalAlloc }}/{{ $totalReq }})
                                    </span>
                                @elseif($totalAlloc > 0)
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                        Sebagian Siap ({{ $totalAlloc }}/{{ $totalReq }})
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                        Siap Disiapkan (0/{{ $totalReq }})
                                    </span>
                                @endif
                            </td>

                            {{-- 6. Aksi --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                @if(! $isReadyToShip)
                                    <a href="{{ route('admin.orders.show', $order->id) }}" 
                                       title="Klik untuk memilih unit fisik di Detail Pesanan"
                                       class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-md font-semibold text-xs transition shadow-sm">
                                        <span>Alokasikan Unit</span>
                                    </a>
                                @else
                                    <button type="button" 
                                            @click="openShipmentModal(@js([
                                                'id' => $order->id,
                                                'order_number' => $order->order_number ?? (string) $order->id,
                                                'customer_name' => $order->user->name ?? 'Customer',
                                                'is_pickup' => $isPickup,
                                                'shipping_address' => $order->shipping_address ?? 'Pelanggan ambil langsung di toko',
                                                'items' => $order->orderItems->map(fn($it) => [
                                                    'name' => $it->product->name ?? 'Barang',
                                                    'qty' => $it->quantity
                                                ])->toArray()
                                            ]))"
                                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white {{ $isPickup ? 'bg-purple-600 hover:bg-purple-700' : 'bg-orange-500 hover:bg-orange-600' }} rounded-md transition-colors focus:ring-2 focus:ring-orange-500 focus:outline-none shadow-sm cursor-pointer">
                                        {{ $isPickup ? 'Serahkan Barang' : 'Proses Kirim' }}
                                    </button>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                Tidak ada pesanan yang siap dikirim saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL PENGIRIMAN / PENYERAHAN BARANG --}}
    <div x-show="modalOpen" 
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="closeModal()">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Backdrop (Di belakang modal panel) --}}
            <div x-show="modalOpen" 
                 x-transition.opacity 
                 class="fixed inset-0 transition-opacity bg-black/50" 
                 @click="closeModal()" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel (Relative z-50 di atas backdrop) --}}
            <div x-show="modalOpen" 
                 x-transition.scale.origin.center 
                 class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full whitespace-normal border border-gray-200">
                
                {{-- Form Kirim --}}
                <form :action="'/admin/shipments/' + (selectedOrder.id || '') + '/process'" method="POST">
                    @csrf

                    {{-- Modal Header --}}
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-base font-bold text-gray-900" id="modal-title">
                            <span x-text="selectedOrder.is_pickup ? 'Penyerahan Pesanan #' : 'Proses Pengiriman #'"></span><span x-text="selectedOrder.order_number"></span>
                        </h3>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 font-bold text-lg p-1">✕</button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4">
                        {{-- Info Read-Only --}}
                        <div class="bg-gray-50 border border-gray-200 p-3.5 rounded-md text-xs space-y-2">
                            <div>
                                <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Info Pelanggan</p>
                                <p class="text-sm font-semibold text-gray-800" x-text="selectedOrder.customer_name"></p>
                            </div>
                            
                            <div class="pt-2 border-t border-gray-200">
                                <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">Alamat Tujuan / Lokasi</p>
                                <p class="text-xs font-semibold text-gray-800" x-text="selectedOrder.shipping_address"></p>
                            </div>

                            <div class="pt-2 border-t border-gray-200">
                                <p class="text-gray-500 font-bold uppercase tracking-wider text-[10px] mb-1">Daftar Barang Diserahkan</p>
                                <template x-for="item in selectedOrder.items" :key="item.name">
                                    <div class="flex justify-between text-gray-700 py-0.5">
                                        <span x-text="'• ' + item.name"></span>
                                        <span class="font-bold" x-text="item.qty + 'x'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Form Resi jika Kirim via Kurir --}}
                        <template x-if="!selectedOrder.is_pickup">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Pilih Ekspedisi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="courier_name" required class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-orange-500 focus:border-orange-500 px-3 py-2 border">
                                        <option value="" disabled selected>-- Pilih Kurir --</option>
                                        <option value="JNE">JNE</option>
                                        <option value="J&T Express">J&T Express</option>
                                        <option value="SiCepat">SiCepat</option>
                                        <option value="GoSend">GoSend</option>
                                        <option value="GrabExpress">GrabExpress</option>
                                        <option value="Kurir Toko">Kurir Toko</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Nomor Resi / Link Lacak <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="tracking_number" required class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-orange-500 focus:border-orange-500 px-3 py-2 border" placeholder="Masukkan nomor resi...">
                                </div>
                            </div>
                        </template>

                        {{-- Info jika Ambil di Toko --}}
                        <template x-if="selectedOrder.is_pickup">
                            <div class="p-3 text-center text-xs text-purple-700 bg-purple-50 rounded-md border border-purple-200">
                                Tidak perlu input resi karena barang diambil langsung di toko. Pastikan fisik barang telah dicek bersama pelanggan sebelum klik tombol serahkan di bawah.
                            </div>
                        </template>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end gap-2 px-6 py-3 bg-gray-50 border-t border-gray-200">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold rounded-md transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-white text-sm font-semibold rounded-md transition shadow-xs"
                                :class="selectedOrder.is_pickup ? 'bg-purple-600 hover:bg-purple-700' : 'bg-orange-500 hover:bg-orange-600'">
                            <span x-text="selectedOrder.is_pickup ? 'Serahkan Barang' : 'Kirim Sekarang'"></span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>

{{-- Alpine.js Manager --}}
<script>
    function shipmentManager() {
        return {
            modalOpen: false,
            selectedOrder: {
                id: null,
                order_number: '',
                customer_name: '',
                is_pickup: false,
                shipping_address: '',
                items: []
            },

            openShipmentModal(data) {
                this.selectedOrder = data;
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
            }
        };
    }
</script>
@endsection
