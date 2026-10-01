<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;

class RentalBaseHomeController extends Controller
{
    public function index()
    {
        $packages = [
            [
                'name' => 'Starter',
                'description' => 'Untuk usaha rental yang baru mulai menggunakan sistem.',
                'duration' => '3 Bulan',
                'price' => 'Hubungi kami',
                'features' => [
                    'Maks. 10 jenis produk',
                    'Maks. 50 unit peralatan total',
                    'Maks. 5 kategori',
                    'Maks. 1 admin',
                ],
            ],
            [
                'name' => 'Business',
                'description' => 'Untuk usaha rental dengan kebutuhan operasional yang lebih lengkap.',
                'duration' => '6 Bulan',
                'price' => 'Hubungi kami',
                'features' => [
                    'Maks. 50 jenis produk',
                    'Maks. 100 unit peralatan total',
                    'Maks. 20 kategori',
                    'Maks. 3 admin',
                    'Dapat mengubah warna beberapa elemen desain halaman',
                ],
            ],
            [
                'name' => 'Professional',
                'description' => 'Untuk penggunaan sistem dengan kebutuhan yang lebih luas.',
                'duration' => '12 Bulan',
                'price' => 'Hubungi kami',
                'features' => [
                    'Unlimited jenis produk',
                    'Unlimited unit peralatan total',
                    'Unlimited kategori',
                    'Maks. 10 admin',
                    'Dapat mengubah warna beberapa elemen desain halaman',
                ],
            ],
        ];

        return view()->file(
            resource_path('views/landing/rentalbase.home.blade.php'),
            compact('packages')
        );
    }
}
