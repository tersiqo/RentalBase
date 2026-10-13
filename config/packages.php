<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Paket Subscription RentalBase
    |--------------------------------------------------------------------------
    |
    | File ini merupakan satu-satunya sumber acuan (single source of truth) untuk
    | definisi paket langganan, batasan kuota penggunaan (limits), ketersediaan fitur (features),
    | harga, dan durasi bagi setiap tenant / client di platform RentalBase.
    |
    | Ketentuan Nilai Limit:
    | - Nilai integer: kuota maksimal yang diizinkan untuk paket tersebut.
    | - Nilai null: kuota tanpa batas (unlimited), sesuai spesifikasi dokumen PRD & project context.
    |
    */

    'starter' => [
        'name' => 'Starter',
        'price' => 0,
        'duration_months' => 3,
        'description' => 'Untuk usaha rental yang baru mulai menggunakan sistem.',
        'limits' => [
            'products'   => 10,  // Maksimal 10 jenis produk
            'units'      => 50,  // Maksimal 50 unit peralatan fisik total
            'categories' => 5,   // Maksimal 5 kategori
            'admins'     => 1,   // Maksimal 1 Admin Rental
        ],
        'features' => [
            'custom_theme' => false, // Tidak dapat mengubah tema setelah registrasi awal
        ],
    ],

    'business' => [
        'name' => 'Business',
        'price' => 99000,
        'duration_months' => 6,
        'description' => 'Untuk usaha rental dengan kebutuhan operasional yang lebih lengkap.',
        'limits' => [
            'products'   => 50,  // Maksimal 50 jenis produk
            'units'      => 100, // Maksimal 100 unit peralatan fisik total
            'categories' => 20,  // Maksimal 20 kategori
            'admins'     => 3,   // Maksimal 3 Admin Rental
        ],
        'features' => [
            'custom_theme' => true, // Dapat menyesuaikan warna tema toko
        ],
    ],

    'professional' => [
        'name' => 'Professional',
        'price' => 249000,
        'duration_months' => 12,
        'description' => 'Untuk penggunaan sistem dengan kapasitas dan fitur operasional maksimal.',
        'limits' => [
            'products'   => null, // Unlimited jenis produk (sesuai dokumen PRD & project context)
            'units'      => null, // Unlimited unit peralatan fisik total (sesuai dokumen PRD & project context)
            'categories' => null, // Unlimited kategori (sesuai dokumen PRD & project context)
            'admins'     => 10,   // Maksimal 10 Admin Rental
        ],
        'features' => [
            'custom_theme' => true, // Dapat menyesuaikan warna tema toko
        ],
    ],

];
