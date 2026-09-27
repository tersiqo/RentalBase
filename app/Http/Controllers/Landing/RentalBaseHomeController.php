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
                'duration' => '12 Bulan',
                'price' => 'Hubungi kami',
                'features' => [
                    'Pengelolaan peralatan',
                    'Katalog rental',
                    'Booking',
                    'Pembayaran',
                    'Pengembalian',
                ],
            ],
            [
                'name' => 'Business',
                'description' => 'Untuk usaha rental dengan kebutuhan operasional yang lebih lengkap.',
                'duration' => '12 Bulan',
                'price' => 'Hubungi kami',
                'features' => [
                    'Semua fitur Starter',
                    'Custom branding',
                    'Laporan',
                    'Pengelolaan admin',
                ],
            ],
            [
                'name' => 'Professional',
                'description' => 'Untuk penggunaan sistem dengan kebutuhan yang lebih luas.',
                'duration' => '12 Bulan',
                'price' => 'Hubungi kami',
                'features' => [
                    'Semua fitur Business',
                    'Dukungan penggunaan',
                    'Pengelolaan client',
                ],
            ],
        ];

        return view()->file(
            resource_path('views/landing/rentalbase.home.blade.php'),
            compact('packages')
        );
    }
}
