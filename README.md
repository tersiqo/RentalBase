# RentalBase

**RentalBase: Equipment Rental System** adalah sistem informasi penyewaan peralatan berbasis web yang dirancang untuk membantu penyedia rental peralatan mengelola proses penyewaan secara terstruktur dan terintegrasi.

Sistem ini dikembangkan untuk mendukung berbagai jenis usaha rental peralatan, seperti rental perlengkapan bayi, camping/outdoor, fotografi, party equipment, dan peralatan lainnya.

## Tentang Project

RentalBase menggunakan konsep **multi-client**, sehingga beberapa penyedia rental dapat menggunakan satu aplikasi dan satu database dengan data masing-masing tetap terpisah berdasarkan `client_id`.

Setiap client memiliki:

* Data peralatan dan kategori sendiri
* Data transaksi sendiri
* Akun Admin Rental sendiri
* Domain atau subdomain sendiri
* Masa lisensi penggunaan sistem

Contoh:

```text
jaya.rentalbase.com
outdoor.rentalbase.com
```

RentalBase bukan merupakan marketplace. Customer mengakses rental tertentu melalui domain atau subdomain client tersebut.

## Aktor Sistem

RentalBase memiliki tiga aktor utama:

| Aktor            | Peran                                                                                                                                           |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| **Owner**        | Mengelola client, akun Admin Rental, lisensi, subdomain, dan monitoring sistem                                                                  |
| **Admin Rental** | Mengelola operasional rental, peralatan, stok, booking, pembayaran, pengiriman, pengembalian, dan kondisi barang                                |
| **Customer**     | Melihat peralatan, melakukan booking, mengunggah bukti pembayaran, melihat status penyewaan, serta mengelola proses pengiriman dan pengembalian |

## Fitur Utama

### Customer

* Melihat katalog peralatan
* Mencari dan memfilter peralatan
* Melihat detail peralatan
* Mengecek ketersediaan
* Melakukan booking
* Mengisi data jaminan identitas
* Mengunggah bukti pembayaran
* Melihat status transaksi
* Melihat informasi pengiriman dan pengembalian
* Memberikan tanggapan terhadap laporan kondisi atau kerusakan

### Admin Rental

* Mengelola profil dan branding rental
* Mengelola kategori
* Mengelola peralatan
* Mengelola stok dan ketersediaan
* Mengelola booking
* Memverifikasi pembayaran
* Memeriksa data jaminan identitas
* Mengelola informasi pengiriman
* Mengelola pengembalian
* Melakukan pemeriksaan kondisi barang
* Mengelola laporan kerusakan
* Melihat laporan operasional

### Owner

* Mengelola client
* Mengelola akun Admin Rental
* Mengelola subdomain
* Mengelola lisensi
* Melihat monitoring sistem

## Alur Penyewaan

```text
Customer
   ↓
Melihat Peralatan
   ↓
Cek Ketersediaan
   ↓
Booking
   ↓
Jaminan Identitas
   ↓
Pembayaran
   ↓
Upload Bukti Pembayaran
   ↓
Verifikasi Admin Rental
   ↓
Pengiriman / Pengambilan
   ↓
Penyewaan
   ↓
Pengembalian
   ↓
Pemeriksaan Kondisi
   ↓
Selesai
```

## Teknologi

* **Backend:** Laravel / PHP
* **Database:** PostgreSQL
* **Database Platform:** Supabase
* **Frontend:** Laravel Blade / Web Frontend
* **Version Control:** Git & GitHub

## Struktur Multi-Client

Data antar client dipisahkan menggunakan `client_id`.

```text
                 RentalBase
                     │
             ┌───────┴───────┐
             │               │
        Client A          Client B
             │               │
        Products          Products
        Bookings          Bookings
        Payments          Payments
             │               │
             └───────┬───────┘
                     │
              PostgreSQL
```

Setiap proses yang berhubungan dengan data client harus memastikan data hanya dapat diakses oleh client yang sesuai.

## Aturan Utama

* Satu transaksi hanya berasal dari satu client.
* Tidak terdapat marketplace atau fitur pemilihan rental oleh customer.
* Tidak terdapat konsep branch/cabang dalam sistem.
* Tidak menggunakan deposit uang.
* Pembayaran dilakukan secara manual melalui transfer bank dan bukti pembayaran.
* Sistem tidak melakukan integrasi payment gateway.
* Sistem tidak melakukan integrasi API kurir atau GPS.
* Pengiriman menggunakan jasa kurir pihak ketiga atau pengambilan/pengantaran langsung.
* Data identitas customer digunakan sebagai jaminan administratif dan tidak menggunakan verifikasi identitas eksternal.
* Data antar client harus tetap terisolasi.
* Core layout dan fitur sistem dikendalikan oleh RentalBase, sedangkan client dapat melakukan kustomisasi branding seperti nama, logo, dan warna tema.

## Status Project

**Development**

Project saat ini berada pada tahap pengembangan dan implementasi bertahap.

Tahap pengembangan awal berfokus pada:

```text
Project Setup
    ↓
Database Foundation
    ↓
Authentication
    ↓
Customer Home
    ↓
Equipment
    ↓
Availability
    ↓
Booking
    ↓
Payment
    ↓
Shipping & Return
    ↓
Condition & Damage
    ↓
Admin
    ↓
Owner
    ↓
Testing
```

## Development

Clone repository:

```bash
git clone https://github.com/USERNAME/RentalBase.git
cd RentalBase
```

Install dependency:

```bash
composer install
```

Salin konfigurasi environment:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Konfigurasikan koneksi PostgreSQL pada `.env`, kemudian jalankan migration:

```bash
php artisan migrate
```

Jalankan aplikasi:

```bash
php artisan serve
```

Aplikasi dapat diakses melalui:

```text
http://127.0.0.1:8000
```

## Repository

Repository ini digunakan sebagai pusat source code, dokumentasi, dan pengembangan RentalBase.

```text
RentalBase/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
└── README.md
```

## Tim Pengembang

RentalBase dikembangkan sebagai proyek pembelajaran dan pengembangan sistem informasi oleh tim mahasiswa.

| Anggota | Peran                                |
| ------- | ------------------------------------ |
| Tersiqo | Project Manager & System Analyst     |
| Yanuar  | Frontend & UI/UX                     |
| Fiza    | Backend & Database                   |
| Zaskia  | Quality Assurance & Technical Writer |
