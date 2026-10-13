<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function landing()
    {
        // Dummy data untuk Pricing
        $pricingPlans = [
            [
                'name' => 'Starter',
                'price' => 'Rp 150.000',
                'features' => ['Maks 50 transaksi', 'Manajemen barang', 'Katalog standar'],
                'highlight' => false,
            ],
            [
                'name' => 'Business',
                'price' => 'Rp 350.000',
                'features' => ['Transaksi tanpa batas', 'Manajemen pelanggan', 'Kustomisasi toko', 'Laporan dasar'],
                'highlight' => true,
            ],
            [
                'name' => 'Professional',
                'price' => 'Rp 750.000',
                'features' => ['Semua fitur Business', 'Laporan lanjutan', 'Dukungan prioritas', 'Multi cabang'],
                'highlight' => false,
            ],
        ];

        return view('welcome', compact('pricingPlans'));
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['orderItems.product', 'client'])
            ->latest()
            ->get();

        $client = $orders->first()?->client
            ?? Client::where('status', 'aktif')->first()
            ?? Client::first()
            ?? (object) [
                'id' => 1,
                'nama_usaha' => 'RentalBase',
                'subdomain' => 'kamerakita',
                'address' => 'Jl. Borobudur No. 88 Malang',
                'phone' => '08123456789',
                'operational_hours' => '08:00 - 20:00',
            ];

        return view('customer.orders.my-orders', compact('orders', 'client'));
    }
}
