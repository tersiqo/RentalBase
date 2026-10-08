@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar Siap Kirim</h1>
        {{-- Tanggal di pojok kanan sudah DIHAPUS sesuai permintaan PM --}}
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Desain tabel kembali menggunakan desain awal yang bersih --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order & Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal Sewa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode & Tujuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Unit</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                
                @forelse($orders as $order)
                    @php
                        $isPickup = empty($order->shipping_address) || str_contains(strtolower($order->shipping_address), 'toko');
                        $isToday = \Carbon\Carbon::parse($order->rental_start_date)->isToday();
                    @endphp

                    <tr x-data="{ openModal: false }">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900">#{{ $order->order_number ?? $order->id }}</div>
                            <div class="text-sm text-gray-600">{{ $order->user->name ?? 'Tanpa Nama' }}</div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold {{ $isToday ? 'text-red-500' : 'text-gray-800' }}">
                                @if($isToday)
                                    Hari ini, {{ \Carbon\Carbon::parse($order->rental_start_date)->format('H:i') }} WIB
                                @else
                                    {{ \Carbon\Carbon::parse($order->rental_start_date)->translatedFormat('l, d M Y') }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Durasi: {{ $order->duration_days ?? 0 }} Hari</div>
                        </td>

                        <td class="px-6 py-4">
                            <ul class="text-sm text-gray-700 list-disc list-inside">
                                @forelse($order->orderItems as $item)
                                    <li>{{ $item->quantity }}x {{ $item->product->name ?? 'Barang' }}</li>
                                @empty
                                    <li class="text-gray-400 italic">Tidak ada detail</li>
                                @endforelse
                            </ul>
                        </td>
                        
                        <td class="px-6 py-4">
                            @if($isPickup)
                                <div class="text-sm font-semibold text-purple-600">Ambil di Toko</div>
                            @else
                                <div class="text-sm font-semibold text-blue-500">Kirim via Kurir</div>
                                <div class="text-xs text-gray-600 mt-1 truncate max-w-[200px]" title="{{ $order->shipping_address }}">{{ $order->shipping_address }}</div>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Sudah Disiapkan
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            {{-- Tombol untuk membuka Pop Up --}}
                            <button @click="openModal = true" class="{{ $isPickup ? 'bg-purple-600 hover:bg-purple-700' : 'bg-orange-500 hover:bg-orange-600' }} text-white px-4 py-2 rounded-md font-semibold text-sm transition">
                                {{ $isPickup ? 'Serahkan Barang' : 'Proses Kirim' }}
                            </button>

                            {{-- ================= POP UP BARU SESUAI INSTRUKSI PM ================= --}}
                            <div x-show="openModal" 
                                 class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center text-left"
                                 x-cloak 
                                 style="display: none; background-color: rgba(0, 0, 0, 0.5);">
                                
                                <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6 whitespace-normal" @click.away="openModal = false">
                                    <div class="flex justify-between items-center mb-4 border-b pb-3">
                                        <h3 class="text-lg font-bold text-gray-900">
                                            {{ $isPickup ? 'Penyerahan Pesanan' : 'Proses Pengiriman' }} #{{ $order->order_number ?? $order->id }}
                                        </h3>
                                        <button type="button" @click="openModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">✕</button>
                                    </div>

                                    <form action="{{ route('admin.shipments.process', $order->id) }}" method="POST">
                                        @csrf
                                        
                                        {{-- 1. Info Read-Only --}}
                                        <div class="mb-5 bg-gray-50 border border-gray-200 p-3 rounded-md">
                                            <p class="text-xs text-gray-500 font-bold mb-1 uppercase tracking-wider">Info Pelanggan (Pastikan Benar!)</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-2">{{ $order->user->name ?? 'Tanpa Nama' }}</p>
                                            
                                            <p class="text-xs text-gray-500 font-bold mb-1 uppercase tracking-wider">Alamat Tujuan</p>
                                            <p class="text-sm font-semibold text-gray-800">
                                                {{ $isPickup ? 'Pelanggan akan mengambil langsung di toko.' : ($order->shipping_address ?? '-') }}
                                            </p>
                                        </div>

                                        {{-- 2. Input Resi (HILANG jika ambil di toko) --}}
                                        @if(!$isPickup)
                                            <div class="mb-4">
                                                <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Ekspedisi</label>
                                                <select name="courier_name" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                                    <option value="" disabled selected>-- Pilih Kurir --</option>
                                                    <option value="JNE">JNE</option>
                                                    <option value="GoSend">GoSend</option>
                                                    <option value="Kurir Toko">Kurir Toko</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label class="block text-sm font-bold text-gray-700 mb-1">Nomor Resi / Link Lacak</label>
                                                <input type="text" name="tracking_number" required class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Masukkan resi...">
                                            </div>
                                        @else
                                            {{-- Info jika Pickup (pengganti form resi) --}}
                                            <div class="mb-4 text-center text-sm text-purple-700 bg-purple-50 p-3 rounded border border-purple-200">
                                                Tidak perlu input resi karena barang diambil di toko. Klik tombol di bawah untuk menyelesaikan.
                                            </div>
                                        @endif

                                        <div class="flex justify-end mt-6 gap-2">
                                            <button type="button" @click="openModal = false" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded">Batal</button>
                                            <button type="submit" class="{{ $isPickup ? 'bg-purple-600 hover:bg-purple-700' : 'bg-orange-500 hover:bg-orange-600' }} text-white px-4 py-2 rounded font-semibold transition">
                                                Kirim Sekarang
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            {{-- ================= END POP UP ================= --}}
                            
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                            Tidak ada pesanan yang siap dikirim saat ini.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>
</div>
@endsection