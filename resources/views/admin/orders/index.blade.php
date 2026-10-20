@extends('layouts.admin')

@section('title', 'Master Pesanan')
@section('page-title', 'Master Pesanan')

@section('content')

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Master Pesanan</h1>
    <p class="text-sm text-gray-500 mt-1">Pantau seluruh pesanan dari semua status yang masuk ke toko kamu.</p>
</div>

{{-- Notifikasi sukses --}}
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

{{-- Form Filter --}}
<div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('admin.orders.index') }}"
          class="flex flex-col lg:flex-row gap-3 items-end">

        {{-- Input pencarian --}}
        <div class="flex-1">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Cari Pesanan</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Nama customer atau kode order..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>

        {{-- Filter status --}}
        <div class="w-full lg:w-52">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Status</label>
            <select name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <option value="">-- Semua Status --</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
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
        <div class="flex gap-2 shrink-0">
            <button type="submit"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-md transition">
                Filter
            </button>
            <a href="{{ route('admin.orders.index') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-md transition">
                Reset
            </a>
        </div>

    </form>
</div>

{{-- Tabel Pesanan --}}
<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wide border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4">ID & Tanggal Masuk</th>
                    <th class="px-6 py-4">Customer</th>
                    <th class="px-6 py-4">Periode Sewa</th>
                    <th class="px-6 py-4">Total Harga</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">

                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- ID & Tanggal --}}
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $order->order_number }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </td>

                        {{-- Nama Customer --}}
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">
                                {{ $order->user->name ?? 'Customer tidak dikenal' }}
                            </div>
                        </td>

                        {{-- Periode Sewa (pakai accessor dari Order.php) --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900">{{ $order->date_range }}</div>
                            <div class="text-xs text-gray-400">{{ $order->duration_days }} hari</div>
                        </td>

                        {{-- Total Harga (pakai accessor dari Order.php) --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-900">{{ $order->formatted_total }}</div>
                        </td>

                        {{-- Badge Status (pakai accessor dari Order.php) --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $order->status_color }}">
                                {{ $order->status_label }}
                            </span>
                        </td>

                        {{-- Kolom Aksi --}}
                        <td class="px-6 py-4 text-center" x-data="{ openModal: false }">

                            {{-- Tombol Lihat Detail: selalu tampil --}}
                            <a href="{{ route('admin.orders.show', $order->id) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-orange-600 bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-md transition mr-1">
                                Detail
                            </a>

                            {{-- Tombol Verifikasi: muncul hanya jika status menunggu konfirmasi --}}
                            @if(in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi']))
                                <button @click="openModal = true"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-md transition">
                                    Verifikasi
                                </button>

                                {{-- POP UP VERIFIKASI --}}
                                <div x-show="openModal"
                                     class="fixed inset-0 z-[9999] flex items-center justify-center"
                                     x-cloak
                                     style="display: none; background-color: rgba(0, 0, 0, 0.5);">

                                    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6 text-left whitespace-normal"
                                         @click.away="openModal = false">

                                        {{-- Header Pop Up --}}
                                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                                            <h3 class="text-lg font-bold text-gray-900">
                                                Verifikasi Pesanan #{{ $order->order_number }}
                                            </h3>
                                            <button type="button" @click="openModal = false"
                                                    class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                                        </div>

                                        {{-- Info Pesanan (Read-Only) --}}
                                        <div class="mb-5 bg-gray-50 border border-gray-200 p-3 rounded-md text-sm">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <p class="text-xs text-gray-500 uppercase font-bold">Customer</p>
                                                    <p class="font-semibold text-gray-800">{{ $order->user->name ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 uppercase font-bold">Total</p>
                                                    <p class="font-semibold text-gray-800">{{ $order->formatted_total }}</p>
                                                </div>
                                                <div class="col-span-2 mt-1">
                                                    <p class="text-xs text-gray-500 uppercase font-bold">Periode Sewa</p>
                                                    <p class="font-semibold text-gray-800">{{ $order->date_range }} ({{ $order->duration_days }} hari)</p>
                                                </div>
                                                <div class="col-span-2 mt-1">
                                                    <p class="text-xs text-gray-500 uppercase font-bold">Barang yang Dipesan</p>
                                                    <ul class="list-disc list-inside">
                                                        @foreach($order->orderItems as $item)
                                                            <li class="text-gray-800">{{ $item->quantity }}x {{ $item->product->name ?? 'Barang' }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Form Tolak: input alasan (tersembunyi, muncul pakai Alpine) --}}
                                        <div x-data="{ showReject: false }">

                                            {{-- Tampilkan input alasan jika showReject = true --}}
                                            <div x-show="showReject" class="mb-4">
                                                <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST">
                                                    @csrf
                                                    <label class="block text-sm font-bold text-gray-700 mb-1">Alasan Penolakan</label>
                                                    <textarea name="cancellation_reason" rows="3" required
                                                              placeholder="Contoh: Barang tidak tersedia pada tanggal tersebut..."
                                                              class="w-full border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-red-500 focus:border-red-500"></textarea>
                                                    <div class="flex justify-end gap-2 mt-3">
                                                        <button type="button" @click="showReject = false"
                                                                class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md">
                                                            Batal
                                                        </button>
                                                        <button type="submit"
                                                                class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md">
                                                            Ya, Tolak Pesanan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            {{-- Tombol utama: Terima dan Tolak --}}
                                            <div x-show="!showReject" class="flex justify-end gap-2">
                                                <button type="button" @click="showReject = true"
                                                        class="px-4 py-2 text-sm bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-md border border-red-200 transition">
                                                    Tolak Pesanan
                                                </button>

                                                <form action="{{ route('admin.orders.confirm', $order->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-md transition">
                                                        ✓ Terima & Konfirmasi
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                {{-- END POP UP --}}

                            @else
                                {{-- Status lain: tampilkan label saja --}}
                                <span class="text-xs text-gray-400 italic">—</span>
                            @endif

                        </td>

                    </tr>
                @empty
                    {{-- Tampilan saat tidak ada data --}}
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <p class="text-base font-medium text-gray-500">Tidak ada pesanan ditemukan.</p>
                            <p class="text-sm text-gray-400 mt-1">
                                @if(request()->hasAny(['search', 'status', 'from', 'to']))
                                    Coba ubah atau hapus filter di atas.
                                @else
                                    Belum ada pesanan masuk ke toko ini.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif

</div>

@endsection