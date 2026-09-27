<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Client;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RentalBaseSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================================
        // CLIENT 1: Jaya Equipment
        // =============================================================
        $clientJaya = Client::updateOrCreate(
            [
                'subdomain' => 'jaya',
            ],
            [
                'nama_usaha' => 'Jaya Equipment',
                'deskripsi' => 'Pilih perlengkapan berkualitas untuk proyek dan kebutuhan Anda. Seluruh unit terinspeksi, terkalibrasi, dan siap pakai.',
                'logo' => null,
                'warna_tema' => '#15803D',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // CATEGORY: Jaya Equipment
        // =============================================================
        $campingJaya = Category::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Camping & Outdoor',
            ],
            [
                'deskripsi' => 'Peralatan kemping, tenda, dan kegiatan luar ruangan.',
            ]
        );

        $kameraJaya = Category::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Kamera & Video',
            ],
            [
                'deskripsi' => 'Kamera mirrorless, lensa, stabilizer gimbal, dan perlengkapan dokumentasi.',
            ]
        );

        $gensetJaya = Category::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Kelistrikan & Genset',
            ],
            [
                'deskripsi' => 'Genset silent, kabel rol, dan peralatan sumber daya listrik.',
            ]
        );

        $perkakasJaya = Category::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Perkakas & Teknik',
            ],
            [
                'deskripsi' => 'Mesin bor, mesin las, gerinda, dan perkakas pertukangan.',
            ]
        );

        // =============================================================
        // PRODUCT: Jaya Equipment
        // =============================================================
        Product::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Tenda Dome Eiger 4-Person Waterproof',
            ],
            [
                'category_id' => $campingJaya->id,
                'deskripsi' => 'Kapasitas 4 Org, Double Layer, Waterproof 3000mm',
                'harga_sewa' => 50000,
                'stok' => 4,
                'foto' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Kamera Sony Alpha A7 III Body Only',
            ],
            [
                'category_id' => $kameraJaya->id,
                'deskripsi' => 'Full-Frame 4K, 2 Baterai + Dual Slot, Sensor Cleaned',
                'harga_sewa' => 220000,
                'stok' => 2,
                'foto' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Genset Inverter Silent Honda EU22i 2000W',
            ],
            [
                'category_id' => $gensetJaya->id,
                'deskripsi' => 'Output 2.200W, Suara Silent 53dB, Bahan Bakar Bensin',
                'harga_sewa' => 175000,
                'stok' => 1,
                'foto' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Mesin Bor Rotary Hammer Bosch GBH 2-26 DRE',
            ],
            [
                'category_id' => $perkakasJaya->id,
                'deskripsi' => 'Daya 800W, Mata Bor SDS Plus, Termasuk Koper',
                'harga_sewa' => 65000,
                'stok' => 3,
                'foto' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Stabilizer Gimbal DJI RS 3 Combo',
            ],
            [
                'category_id' => $kameraJaya->id,
                'deskripsi' => 'Beban Maks 3kg, Auto Axis Locks, Tas & Focus Motor',
                'harga_sewa' => 130000,
                'stok' => 3,
                'foto' => 'https://images.unsplash.com/photo-1527011046414-4781f1f94f8c?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'nama' => 'Mesin Las Inverter Lakoni Falcon 160E',
            ],
            [
                'category_id' => $perkakasJaya->id,
                'deskripsi' => 'Hemat Listrik 900W, Bonus Kedok Las, Tang Las + Massa',
                'harga_sewa' => 45000,
                'stok' => 5,
                'foto' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // CLIENT 2: Malang Outdoor
        // =============================================================
        $clientOutdoor = Client::updateOrCreate(
            [
                'subdomain' => 'outdoor',
            ],
            [
                'nama_usaha' => 'Malang Outdoor',
                'deskripsi' => 'Pusat penyewaan perlengkapan pendakian, kemping, dan petualangan outdoor terlengkap di Malang.',
                'logo' => null,
                'warna_tema' => '#2563EB',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // CATEGORY: Malang Outdoor
        // =============================================================
        $tendaOutdoor = Category::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Tenda & Shelter',
            ],
            [
                'deskripsi' => 'Tenda dome, flysheet, dan perlengkapan shelter.',
            ]
        );

        $carrierOutdoor = Category::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Carrier & Ransel',
            ],
            [
                'deskripsi' => 'Tas gunung, daypack, dan rain cover.',
            ]
        );

        $masakOutdoor = Category::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Perlengkapan Masak',
            ],
            [
                'deskripsi' => 'Kompor gunung, nesting, dan gas canister.',
            ]
        );

        $lampuOutdoor = Category::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Penerangan & Lampu',
            ],
            [
                'deskripsi' => 'Headlamp, senter outdoor, dan lampu tenda.',
            ]
        );

        // =============================================================
        // PRODUCT: Malang Outdoor
        // =============================================================
        Product::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Tenda Consina Magnum 4 Person',
            ],
            [
                'category_id' => $tendaOutdoor->id,
                'deskripsi' => 'Kapasitas 4 Org, Double Layer, Frame Alloy',
                'harga_sewa' => 35000,
                'stok' => 6,
                'foto' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Carrier Osprey Atmos AG 65L',
            ],
            [
                'category_id' => $carrierOutdoor->id,
                'deskripsi' => 'Kapasitas 65L, Anti-Gravity Backsystem, Rain Cover',
                'harga_sewa' => 45000,
                'stok' => 4,
                'foto' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Kompor Camping Portable Kovar Windproof',
            ],
            [
                'category_id' => $masakOutdoor->id,
                'deskripsi' => 'Windproof, Pemantik Piezo, Gas Canister',
                'harga_sewa' => 15000,
                'stok' => 10,
                'foto' => 'https://images.unsplash.com/photo-1526772662000-3f88f10405ff?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Headlamp Nitecore NU32 550 Lumens',
            ],
            [
                'category_id' => $lampuOutdoor->id,
                'deskripsi' => 'Rechargeable USB, IP67 Waterproof, Light Mode White/Red',
                'harga_sewa' => 20000,
                'stok' => 8,
                'foto' => 'https://images.unsplash.com/photo-1517649763962-0c623266010b?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        Product::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'nama' => 'Matras Aluminium Foil Double Layer',
            ],
            [
                'category_id' => $tendaOutdoor->id,
                'deskripsi' => 'Insulasi Dingin, Tebal 8mm, Dimensi 200x120cm',
                'harga_sewa' => 10000,
                'stok' => 15,
                'foto' => 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?w=600&auto=format&fit=crop&q=80',
                'ketentuan_jaminan' => 'Wajib menyerahkan KTP asli saat pengambilan barang.',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // USER: OWNER
        // =============================================================
        User::updateOrCreate(
            [
                'email' => 'owner@rentalbase.test',
            ],
            [
                'client_id' => null,
                'name' => 'Owner RentalBase',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // USER: ADMIN RENTAL JAYA
        // =============================================================
        User::updateOrCreate(
            [
                'email' => 'admin@jaya.test',
            ],
            [
                'client_id' => $clientJaya->id,
                'name' => 'Admin Jaya Equipment',
                'password' => Hash::make('password'),
                'role' => 'admin_rental',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // USER: ADMIN RENTAL OUTDOOR
        // =============================================================
        User::updateOrCreate(
            [
                'email' => 'admin@outdoor.test',
            ],
            [
                'client_id' => $clientOutdoor->id,
                'name' => 'Admin Malang Outdoor',
                'password' => Hash::make('password'),
                'role' => 'admin_rental',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // USER: CUSTOMER GLOBAL
        // =============================================================
        User::updateOrCreate(
            [
                'email' => 'customer@rentalbase.test',
            ],
            [
                'client_id' => null,
                'name' => 'Customer Demo',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'aktif',
            ]
        );

        // =============================================================
        // LICENSE: Jaya Equipment
        // =============================================================
        License::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'tanggal_mulai' => now()->startOfDay()->toDateString(),
            ],
            [
                'tanggal_berakhir' => now()->addYear()->startOfDay()->toDateString(),
                'status' => 'active',
            ]
        );

        // =============================================================
        // LICENSE: Malang Outdoor
        // =============================================================
        License::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'tanggal_mulai' => now()->startOfDay()->toDateString(),
            ],
            [
                'tanggal_berakhir' => now()->addYear()->startOfDay()->toDateString(),
                'status' => 'active',
            ]
        );
    }
}