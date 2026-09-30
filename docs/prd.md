# Product Requirements Document (PRD)

# RentalBase: Equipment Rental System

## 1. Project Overview

### 1.1 Nama Produk
**RentalBase: Equipment Rental System**

### 1.2 Deskripsi
RentalBase adalah sistem informasi penyewaan peralatan berbasis web yang membantu penyedia rental peralatan dalam mengelola product, physical equipment unit, availability, booking, payment, shipping, return, dan pemeriksaan kondisi barang.

RentalBase menggunakan konsep **multi-client**, sehingga satu aplikasi dapat digunakan oleh beberapa penyedia rental dengan data masing-masing yang tetap terpisah berdasarkan `client_id`.

Halaman utama `/` merupakan landing page platform RentalBase. Customer mengakses layanan penyewaan melalui domain atau subdomain milik masing-masing client. Satu akun Customer bersifat global, sehingga dapat digunakan untuk menyewa di beberapa usaha rental (client) yang berbeda tanpa perlu mendaftar ulang. Customer dapat menjelajah katalog dan detail produk tanpa login; login baru diwajibkan saat customer menekan tombol "Sewa Alat" untuk mulai memesan. Contoh implementasi utama pada tahap proyek menggunakan skenario rental perlengkapan bayi (stroller, baby box, baby walker, car seat, high chair), namun struktur fitur inti tetap generik untuk jenis usaha rental peralatan lainnya.

### 1.3 Tujuan Produk
1. Membantu penyedia rental mengelola peralatan.
2. Mempermudah customer melihat peralatan dan ketersediaannya.
3. Mempermudah proses pemesanan dan pembayaran.
4. Membantu Admin Rental mengelola transaksi.
5. Mencatat pengiriman dan pengembalian.
6. Mencatat kondisi peralatan sebelum dan setelah penyewaan.
7. Mendukung beberapa client dalam satu aplikasi.
8. Membantu Owner memonitor client dan subscription.

## 2. Target Users

### 2.1 Customer
- Mengakses website client tanpa harus login (guest browsing).
- Melihat katalog dan detail peralatan, termasuk ketentuan jaminan identitas yang ditampilkan sebagai informasi pada halaman produk.
- Melihat ketersediaan physical equipment unit.
- Memilih periode penyewaan.
- Login/registrasi hanya diwajibkan saat menekan tombol "Sewa Alat" untuk melanjutkan ke booking.
- Booking dan checkout, termasuk mengisi alamat pengiriman.
- Mengisi data jaminan identitas untuk setiap order (nama lengkap, nomor KTP, foto KTP, selfie wajah, dan alamat sesuai identitas).
- Mengunggah bukti pembayaran.
- Melihat status pembayaran, pesanan, dan pengiriman.
- Mengonfirmasi penerimaan barang.
- Melakukan pengembalian.
- Melihat riwayat.

### 2.2 Admin Rental
- Mengelola profil dan branding usaha (nama usaha, deskripsi, logo, warna tema) dalam batas konfigurasi yang disediakan sistem.
- Mengelola kategori, product, physical equipment unit, harga, status unit, dan identity guarantee requirements per product.
- Memeriksa booking.
- Memverifikasi pembayaran.
- Mengelola pengiriman dan pengembalian.
- Memeriksa kondisi barang.
- Mengelola laporan kerusakan.
- Melihat dashboard operasional dan laporan transaksi.

### 2.3 Owner
- Mengelola client.
- Menambahkan client.
- Membuat dan mengelola akun Admin Rental untuk masing-masing client.
- Mengelola subdomain.
- Mengelola subscription.
- Mengaktifkan/menonaktifkan client.
- Melihat dashboard monitoring ringkas seluruh client.

Owner **bukan** operator transaksi rental harian, dan tidak mengakses detail transaksi customer.

## 3. Business Model
RentalBase adalah software untuk beberapa penyedia rental peralatan. Setiap penyedia rental menjadi satu **client**.

Setiap client memiliki:
- `client_id`
- akun Admin Rental (dibuat oleh Owner, terikat pada satu client)
- domain/subdomain (dikelola oleh Owner)
- subscription
- data produk sendiri
- data transaksi sendiri

Berbeda dengan akun Admin Rental, akun **Customer bersifat global/lintas-client**: satu akun Customer yang sama dapat digunakan untuk menyewa di beberapa client yang berbeda. Meskipun demikian, setiap order tetap tercatat pada satu client saja (`orders.client_id`), sehingga riwayat transaksi yang ditampilkan di satu subdomain hanya berisi transaksi pada client tersebut.

Data antar-client tidak boleh tercampur.

## 4. Business Rules

### 4.1 Multi-Client
Satu aplikasi dan satu database PostgreSQL digunakan oleh banyak client. Data tenant dibedakan menggunakan `client_id`.

### 4.2 Domain/Subdomain
Customer mengakses penyedia rental melalui domain/subdomain client, misalnya:
```text
jaya.rentalbase.com
outdoor.rentalbase.com
```
Halaman `/` digunakan sebagai landing page platform RentalBase dan bukan katalog client. Customer mengakses client melalui domain/subdomain yang diberikan kepada client, misalnya `jaya.rentalbase.com` atau `outdoor.rentalbase.com`.

### 4.2.1 Guest Browsing & Login Gate
Landing page, katalog, detail produk, dan pengecekan availability dapat diakses tanpa login (guest). Login/registrasi baru diwajibkan saat customer menekan tombol "Sewa Alat" untuk mulai booking. Setelah login berhasil, customer diarahkan kembali ke produk dan periode sewa yang sebelumnya dipilih (intended redirect), bukan ke halaman awal.

### 4.2.2 Cakupan Akun Customer
Akun Customer bersifat global: satu akun (satu email/password) dapat dipakai untuk login dan bertransaksi di subdomain client mana pun. Ini berbeda dengan akun Admin Rental yang terikat pada satu client. Meskipun akunnya global, tampilan riwayat pesanan pada satu subdomain hanya menampilkan transaksi milik client tersebut.

### 4.3 Produk
Product memiliki name, description, category, rental price, image, identity guarantee requirements, dan status. Jumlah unit fisik tidak disimpan langsung pada product. Setiap physical equipment dicatat pada `equipment_units` dan memiliki `asset_code` serta status operasional sendiri. Admin Rental dapat mengisi identity guarantee requirements sebagai teks informasi yang ditampilkan kepada customer dan tidak memicu validasi atau logika otomatis.

### 4.4 Availability
Ketersediaan ditentukan berdasarkan jumlah `equipment_units` yang dapat digunakan, unit yang sedang dialokasikan pada `order_item_units`, status operasional unit, dan periode penyewaan. Sistem harus mencegah booking melebihi jumlah unit yang tersedia pada periode yang sama.

### 4.5 Booking & Checkout
Satu booking hanya berasal dari satu client. Pada tahap checkout, customer mengisi quantity, periode sewa, dan alamat pengiriman. Physical equipment unit dialokasikan melalui `order_item_units` sesuai quantity. Alamat pengiriman disimpan pada `orders.shipping_address` dan digunakan kembali oleh Admin Rental saat mencatat pengiriman.

### 4.6 Payment
Pembayaran menggunakan transfer manual. Customer melakukan transfer dan mengunggah bukti pembayaran setelah jaminan identitas berstatus `diverifikasi`. Admin Rental melakukan verifikasi. Tidak ada payment gateway pada tahap awal.

### 4.7 Identity Guarantee
Tidak menggunakan deposit uang. Customer mengisi data identitas (nama lengkap, nomor identitas, foto identitas, foto wajah, alamat sesuai identitas) sebagai jaminan administratif pada tahap checkout, sebelum bukti pembayaran diunggah. Tidak ada verifikasi identitas eksternal pada tahap awal.

### 4.8 Shipping
Pengiriman melalui kurir pihak ketiga atau langsung. Sistem mencatat nama kurir, nomor resi, tanggal, dan status, dengan alamat tujuan mengikuti alamat pengiriman yang telah diisi pada order. Customer mengonfirmasi penerimaan barang setelah barang diterima. Tidak ada integrasi GPS/API kurir pada tahap awal.

### 4.9 Return
Pengembalian melalui kurir atau langsung. Pengembalian dicatat terpisah dari pengiriman awal.

### 4.10 Damage Handling
Kondisi physical equipment unit dicatat sebelum dan sesudah penyewaan melalui checklist dan image. Sistem menyimpan unit yang diperiksa, laporan kerusakan, status, dan tanggapan customer. Penyelesaian mengikuti kebijakan penyedia rental, bukan otomatisasi AI.

### 4.11 Client Branding
Admin Rental dapat mengubah nama usaha, deskripsi, dan logo miliknya sendiri melalui fitur Profil dan Branding. Pengubahan warna beberapa elemen desain halaman hanya tersedia untuk client dengan paket **Business** atau **Professional**. Paket **Starter** menggunakan warna/desain bawaan RentalBase. Subdomain dan pembuatan akun Admin Rental tetap menjadi wewenang Owner.

## 5. Functional Requirements

### Customer
- Register (akun global, satu kali daftar berlaku untuk semua client), login, logout, profile.
- View categories, products, equipment details (termasuk identity guarantee requirements), availability — dapat diakses tanpa login.
- Select rental period.
- Login/registrasi (jika belum) saat menekan "Sewa Alat", lalu create/view booking dengan alamat pengiriman.
- Submit jaminan identitas.
- Checkout.
- Upload payment proof.
- View payment/rental status and history.
- View shipping and tracking.
- Confirm receipt.
- Request/record return.
- View condition checks and damage reports terkait ordernya, serta memberi tanggapan atas laporan kerusakan.

### Admin Rental
- Dashboard operasional (ringkasan booking baru, pembayaran menunggu verifikasi, barang perlu dikirim/diterima).
- Update profil dan branding usaha (nama usaha, deskripsi, logo, dan warna tema jika paket Business/Professional).
- Category management.
- Product dan equipment unit management, termasuk pengisian identity guarantee requirements per product.
- Booking management.
- Identity guarantee review.
- Payment verification.
- Shipping management.
- Return management.
- Condition and damage management.
- Transaction reports.

### Owner
- Login.
- Client management.
- Membuat dan mengelola akun Admin Rental per client.
- Subdomain management.
- Subscription management.
- Client status.
- Dashboard monitoring ringkas (jumlah client, status client, status subscription), tanpa mengakses transaksi harian.

## 6. Subscription
Setiap client dapat memiliki subscription yang merepresentasikan paket layanan RentalBase yang sedang digunakan.

### 6.1 Paket dan Benefit

| Benefit | Starter | Business | Professional |
|---|---:|---:|---:|
| Maks. jenis produk | 10 | 50 | Unlimited |
| Maks. unit peralatan total | 50 | 100 | Unlimited |
| Maks. kategori | 5 | 20 | Unlimited |
| Maks. Admin Rental | 1 | 3 | 10 |
| Durasi pemakaian aplikasi | 3 bulan | 6 bulan | 12 bulan |
| Custom warna beberapa elemen desain halaman | Tidak | Ya | Ya |

Tidak ada benefit pembeda lain. Katalog online, booking rental, availability, payment, shipping, return, identity guarantee, condition check, dan damage handling tersedia sama pada semua paket.

### 6.2 Data Subscription
Data utama subscription:
- `client_id`
- `plan_name`
- `start_date`
- `end_date`
- `status`

Status subscription:
```text
active
expired
suspended
```

### 6.3 Aturan Paket
- Backend wajib membatasi jumlah jenis product, equipment unit total, kategori, dan Admin Rental sesuai paket aktif client.
- Durasi subscription mengikuti paket yang dipilih.
- Custom warna beberapa elemen desain halaman hanya dapat digunakan oleh Business dan Professional.
- Core layout dan fitur operasional RentalBase tetap sama untuk semua paket.

Tidak menggunakan license key, subscription key, token, atau activation code. Status subscription digunakan untuk mengetahui apakah layanan client masih aktif dan untuk kebutuhan monitoring Owner.

## 7. UI/UX Requirements
- Responsive.
- Navigasi jelas.
- Tampilan konsisten.
- Status mudah dipahami.
- Card peralatan.
- Form dengan validasi, termasuk form alamat pengiriman dan jaminan identitas pada checkout.
- Feedback setelah tindakan.
- Client dapat mengubah nama usaha, deskripsi, logo, dan warna/tema melalui halaman profil.
- Core layout dan fungsi tetap dikendalikan RentalBase.

## 8. Technical Stack
- Backend: Laravel, PHP
- Database: PostgreSQL, Supabase
- Frontend: Laravel Blade / frontend yang digunakan kelompok
- Version Control: Git, GitHub

## 9. Non-Functional Requirements
### Security
Authentication, authorization, client isolation, password hashing, validation, CSRF protection, dan file validation.

### Performance
Halaman dimuat dalam waktu wajar pada koneksi internet normal; proses cek availability dan booking ditargetkan selesai tidak lebih dari 3 detik pada kondisi standar (mengacu pada rencana pengujian non-fungsional proposal).

### Compatibility
Tampilan dan fungsi sistem diuji pada beberapa browser umum (Chrome, Firefox, Edge) serta perangkat desktop dan mobile, mengingat setiap client diakses melalui subdomain masing-masing.

### Maintainability
Kode terstruktur, mengikuti Laravel conventions, memakai migration dan model relationships.

### Scalability
Penambahan client tidak memerlukan database terpisah.

## 10. Project Constraints
1. Web-based.
2. PostgreSQL.
3. Supabase sebagai layanan PostgreSQL.
4. Tidak ada marketplace.
5. Tidak ada branch/cabang dalam satu client.
6. Lokasi tambahan dengan akun/domain/subscription tersendiri diperlakukan sebagai client baru.
7. Tidak ada deposit uang.
8. Tidak ada GPS.
9. Tidak ada API kurir pada tahap awal.
10. Pembayaran transfer manual.
11. Satu order hanya berasal dari satu client.
12. Core layout dan struktur fitur dikendalikan RentalBase.
13. Ketentuan jaminan identitas pada produk bersifat teks informasi, bukan aturan yang divalidasi otomatis oleh sistem.
14. Landing page platform, katalog client, detail produk, dan availability memiliki konteks akses publik sesuai halaman masing-masing; login customer diwajibkan mulai saat customer menekan "Sewa Alat".
15. Akun Customer bersifat global (satu akun untuk semua client); akun Admin Rental tetap terikat pada satu client.

## 11. Success Criteria
1. Customer dapat mengakses website client dan menjelajah katalog tanpa login.
2. Customer dapat melihat peralatan dan ketersediaannya.
3. Customer diarahkan untuk login/registrasi saat menekan "Sewa Alat", lalu melakukan booking beserta alamat pengiriman.
4. Satu akun Customer dapat digunakan untuk menyewa di lebih dari satu client tanpa mendaftar ulang, dengan riwayat pesanan tetap terpisah per client.
5. Customer dapat mengisi jaminan identitas untuk setiap order, Admin Rental dapat meninjau jaminan identitas tersebut, dan Customer dapat mengunggah bukti pembayaran setelah jaminan identitas diverifikasi.
6. Admin dapat memverifikasi pembayaran.
7. Admin dapat mengelola product, physical equipment unit, status unit, dan profil/branding usahanya.
8. Sistem mencatat pengiriman, konfirmasi penerimaan, dan pengembalian.
9. Sistem mencatat kondisi dan kerusakan.
10. Data antar-client terisolasi dengan `client_id`.
11. Owner dapat mengelola client, akun Admin Rental, dan subscription melalui dashboard monitoring ringkas.
12. Sistem menerapkan limit paket untuk jenis product, equipment unit total, kategori, Admin Rental, durasi subscription, dan custom warna sesuai paket.
13. Fitur utama berjalan tanpa error pada skenario pengujian yang telah ditentukan (fungsional, performa, dan kompatibilitas).