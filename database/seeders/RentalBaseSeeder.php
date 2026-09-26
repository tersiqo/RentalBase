<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Database\Seeder;

class RentalBaseSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::create([
            'nama' => 'Jaya Baby Rental',
            'deskripsi' => 'Penyewaan perlengkapan bayi untuk kebutuhan keluarga.',
            'logo' => null,
            'warna_tema' => '#D9A441',
            'subdomain' => 'jaya',
            'status' => 'aktif',
        ]);

        $stroller = Category::create([
            'client_id' => $client->id,
            'nama' => 'Stroller',
            'deskripsi' => 'Berbagai pilihan stroller bayi.',
        ]);

        $babyBox = Category::create([
            'client_id' => $client->id,
            'nama' => 'Baby Box',
            'deskripsi' => 'Perlengkapan tempat tidur bayi.',
        ]);

        $carSeat = Category::create([
            'client_id' => $client->id,
            'nama' => 'Car Seat',
            'deskripsi' => 'Kursi pengaman untuk perjalanan bayi.',
        ]);

        Product::create([
            'client_id' => $client->id,
            'category_id' => $stroller->id,
            'nama' => 'Stroller Compact',
            'deskripsi' => 'Stroller praktis untuk perjalanan bersama bayi.',
            'harga_sewa' => 50000,
            'stok' => 5,
            'gambar' => null,
            'status' => 'aktif',
        ]);

        Product::create([
            'client_id' => $client->id,
            'category_id' => $stroller->id,
            'nama' => 'Stroller Premium',
            'deskripsi' => 'Stroller dengan fitur lengkap dan nyaman digunakan.',
            'harga_sewa' => 75000,
            'stok' => 3,
            'gambar' => null,
            'status' => 'aktif',
        ]);

        Product::create([
            'client_id' => $client->id,
            'category_id' => $babyBox->id,
            'nama' => 'Baby Box Portable',
            'deskripsi' => 'Baby box yang mudah dibawa dan digunakan.',
            'harga_sewa' => 60000,
            'stok' => 4,
            'gambar' => null,
            'status' => 'aktif',
        ]);

        Product::create([
            'client_id' => $client->id,
            'category_id' => $carSeat->id,
            'nama' => 'Car Seat Infant',
            'deskripsi' => 'Car seat untuk bayi dengan desain aman dan nyaman.',
            'harga_sewa' => 70000,
            'stok' => 2,
            'gambar' => null,
            'status' => 'aktif',
        ]);
    }
}