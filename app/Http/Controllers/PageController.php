<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // Dummy data untuk Pesanan Saya
        $orders = [
            [
                'id' => 'ID ORD-94821',
                'date' => '10-12 Okt 2026',
                'status' => 'Belum Dibayar',
                'status_color' => 'bg-red-600 text-white',
                'total' => 'Rp 320.000',
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=200',
                'items_count' => 3, 
            ],
            [
                'id' => 'ID ORD-94710',
                'date' => '05-08 Okt 2026',
                'status' => 'Sedang Disewa',
                'status_color' => 'bg-blue-600 text-white',
                'total' => 'Rp 150.000',
                'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&q=80&w=200',
                'items_count' => 1,
            ],
            [
                'id' => 'ID ORD-94503',
                'date' => '28 Sep - 1 Okt 2026',
                'status' => 'Selesai',
                'status_color' => 'bg-green-600 text-white',
                'total' => 'Rp 75.000',
                'image' => 'https://images.unsplash.com/photo-1527011045970-132ed6dc480d?auto=format&fit=crop&q=80&w=200',
                'items_count' => 1,
            ]
        ];

        // PERBAIKAN: Tambahkan data subdomain di sini
        $client = (object)[
            'id' => 1,
            'name' => 'Yanuar Alda Baran',
            'subdomain' => 'kamerakita', 
        ];

        // PERBAIKAN: Tambahkan 'client' ke dalam fungsi compact()
        return view('customer.orders.my-orders', compact('orders', 'client'));
    }
}