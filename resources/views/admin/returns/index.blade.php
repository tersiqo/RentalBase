@extends('layouts.admin')

@section('content')
<!-- Menambahkan fungsi kalkulator dengan Alpine.js -->
<div x-data="returnCalculator()" class="space-y-6">

    <!-- Header & Alert -->
    <div>
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Penerimaan & Denda Retur</h2>
        <p class="text-sm text-gray-500 mt-1">Konfirmasi penerimaan barang dan proses denda keterlambatan jika ada.</p>
    </div>

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

    <!-- Tabel Data -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">Pesanan & Pelanggan</th>
                        <th class="px-6 py-4">Daftar Barang</th>
                        <th class="px-6 py-4">Jatuh Tempo</th>
                        <th class="px-6 py-4 text-center">Status Waktu</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $order)
                        @php
                            // Cek keterlambatan untuk tampilan Badge Table
                            $isLate = now()->greaterThan($order->rental_end_date);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $order->order_number }}</div>
                                <div class="text-gray-500 mt-0.5">{{ $order->user->name ?? 'User' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <ul class="list-disc list-inside text-xs">
                                    @foreach($order->orderItems as $item)
                                        <li>{{ $item->product->name }} (x{{ $item->quantity }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900 font-semibold">{{ $order->rental_end_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $order->rental_end_date->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($isLate)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
                                        Telat
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                        Belum Telat
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <!-- Tombol ini memicu fungsi Alpine.js: openModal(dataOrder) -->
                                <button type="button" 
                                    @click="openModal({{ json_encode([
                                        'id' => $order->id,
                                        'order_number' => $order->order_number,
                                        'customer_name' => $order->user->name ?? 'User',
                                        'due_date' => $order->rental_end_date->format('Y-m-d H:i:s'),
                                        'due_date_formatted' => $order->rental_end_date->format('d M Y, H:i'),
                                        'items' => $order->orderItems->map(fn($i) => ['name' => $i->product->name, 'qty' => $i->quantity, 'fee_per_hour' => $i->product->late_fee_per_hour])
                                    ]) }})"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-md transition-colors focus:ring-2 focus:ring-orange-500 focus:outline-none">
                                    Proses
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada barang yang sedang disewa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL KALKULATOR DENDA -->
    <div x-show="isOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            <!-- Backdrop -->
            <div x-show="isOpen" @click="closeModal()" x-transition.opacity class="fixed inset-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.5);"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="isOpen" x-transition.scale.origin.bottom class="relative z-10 inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b pb-3 mb-4" id="modal-title">Proses Pengembalian Barang</h3>
                
                <form :action="`/admin/returns/${activeOrder.id}/process`" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <p class="text-gray-500">ID Order</p>
                            <p class="font-bold text-gray-900" x-text="activeOrder.order_number"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Pelanggan</p>
                            <p class="font-bold text-gray-900" x-text="activeOrder.customer_name"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Jatuh Tempo</p>
                            <p class="font-semibold text-gray-900" x-text="activeOrder.due_date_formatted"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Diterima Kembali (Saat Ini)</p>
                            <p class="font-semibold text-gray-900" x-text="currentTimeFormatted"></p>
                        </div>
                    </div>

                    <!-- Peringatan Keterlambatan -->
                    <template x-if="lateHours > 0">
                        <div class="p-3 mb-4 border rounded-md bg-red-50 border-red-200">
                            <p class="text-sm font-semibold text-red-700">Terdeteksi Telat <span x-text="lateDurationText"></span>!</p>
                            <p class="text-xs text-red-600 mt-1">Denda sistem dihitung dari durasi jam telat dikali tarif denda produk.</p>
                        </div>
                    </template>
                    <template x-if="lateHours <= 0">
                        <div class="p-3 mb-4 border rounded-md bg-emerald-50 border-emerald-200">
                            <p class="text-sm font-semibold text-emerald-700">Pengembalian Tepat Waktu</p>
                        </div>
                    </template>

                    <!-- Input Override Denda -->
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Total Denda (Rp)</label>
                        <input type="number" name="late_fee_amount" x-model="overrideFee" class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <p class="text-xs text-gray-500 mt-1">Sistem menyarankan: Rp <span x-text="calculatedFeeFormatted"></span>. Anda bisa mengubah nominal ini.</p>
                    </div>

                    <input type="hidden" name="action_type" x-model="actionType">

                    <!-- Tombol Aksi (Dinamis) -->
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Batal</button>
                        
                        <template x-if="overrideFee == 0">
                            <button type="submit" @click="actionType = 'normal'" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700">
                                Barang Kembali Normal
                            </button>
                        </template>

                        <template x-if="overrideFee > 0">
                            <button type="submit" @click="actionType = 'denda'" class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md hover:bg-orange-600">
                                Terbitkan Tagihan Denda
                            </button>
                        </template>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('returnCalculator', () => ({
        isOpen: false,
        activeOrder: {},
        currentTime: new Date(),
        lateHours: 0,
        calculatedFee: 0,
        overrideFee: 0,
        actionType: 'normal',

        get currentTimeFormatted() {
            return this.currentTime.toLocaleString('id-ID', { 
                day: '2-digit', month: 'short', year: 'numeric', 
                hour: '2-digit', minute: '2-digit' 
            }) + ' WIB';
        },

        get calculatedFeeFormatted() {
            return new Intl.NumberFormat('id-ID').format(this.calculatedFee);
            
        },

        get lateDurationText() {
            if (this.lateHours <= 0) return '';
            let days = Math.floor(this.lateHours / 24);
            let hours = this.lateHours % 24;
            
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
            // Konversi string YYYY-MM-DD HH:mm:ss ke Object Date JS
            let dueDateStr = this.activeOrder.due_date.replace(/-/g, '/');
            let dueDate = new Date(dueDateStr);
            
            // Hitung selisih jam
            let diffMs = this.currentTime - dueDate;
            if (diffMs > 0) {
                // Konversi milidetik ke jam, bulatkan ke atas
                this.lateHours = Math.ceil(diffMs / (1000 * 60 * 60));
                
                // Hitung total denda
                let totalFee = 0;
                this.activeOrder.items.forEach(item => {
                    let feePerHour = parseFloat(item.fee_per_hour) || 0;
                    totalFee += (feePerHour * item.qty * this.lateHours);
                });
                
                this.calculatedFee = totalFee;
                this.overrideFee = totalFee; // Set default override fee = system fee
            } else {
                this.lateHours = 0;
                this.calculatedFee = 0;
                this.overrideFee = 0;
            }
        }
    }));
});
</script>
@endsection