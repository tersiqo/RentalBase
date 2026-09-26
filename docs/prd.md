# Product Requirements Document (PRD)

# RentalBase: Equipment Rental System

## 1. Project Overview

### 1.1 Nama Produk
**RentalBase: Equipment Rental System**

### 1.2 Deskripsi
RentalBase adalah sistem informasi penyewaan peralatan berbasis web yang membantu penyedia rental peralatan dalam mengelola produk, stok, ketersediaan, pemesanan, pembayaran, pengiriman, pengembalian, dan pemeriksaan kondisi barang.

RentalBase menggunakan konsep **multi-client**, sehingga satu aplikasi dapat digunakan oleh beberapa penyedia rental dengan data masing-masing yang tetap terpisah berdasarkan `client_id`.

Customer mengakses layanan penyewaan melalui domain atau subdomain milik masing-masing client. Contoh implementasi utama pada tahap proyek menggunakan skenario rental perlengkapan bayi (stroller, baby box, baby walker, car seat, high chair), namun struktur fitur inti tetap generik untuk jenis usaha rental peralatan lainnya.

### 1.3 Tujuan Produk
1. Membantu penyedia rental mengelola peralatan.
2. Mempermudah customer melihat peralatan dan ketersediaannya.
3. Mempermudah proses pemesanan dan pembayaran.
4. Membantu Admin Rental mengelola transaksi.
5. Mencatat pengiriman dan pengembalian.
6. Mencatat kondisi peralatan sebelum dan setelah penyewaan.
7. Mendukung beberapa client dalam satu aplikasi.
8. Membantu Owner memonitor client dan lisensi.

## 2. Target Users

### 2.1 Customer
- Mengakses website client.
- Melihat katalog dan detail peralatan, termasuk ketentuan jaminan identitas yang ditampilkan sebagai informasi pada halaman produk.
- Melihat ketersediaan.
- Memilih periode penyewaan.
- Booking dan checkout, termasuk mengisi alamat pengiriman.
- Mengisi data jaminan identitas (KTP, foto wajah, alamat identitas).
- Mengunggah bukti pembayaran.
- Melihat status pembayaran, pesanan, dan pengiriman.
- Mengonfirmasi penerimaan barang.
- Melakukan pengembalian.
- Melihat riwayat.

### 2.2 Admin Rental
- Mengelola profil dan branding usaha (nama usaha, deskripsi, logo, warna tema) dalam batas konfigurasi yang disediakan sistem.
- Mengelola kategori, peralatan, harga, stok, dan teks ketentuan jaminan identitas per produk.
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
- Mengelola lisensi.
- Mengaktifkan/menonaktifkan client.
- Melihat dashboard monitoring ringkas seluruh client.

Owner **bukan** operator transaksi rental harian, dan tidak mengakses detail transaksi customer.

## 3. Business Model
RentalBase adalah software untuk beberapa penyedia rental peralatan. Setiap penyedia rental menjadi satu **client**.

Setiap client memiliki:
- `client_id`
- akun Admin Rental (dibuat oleh Owner)
- domain/subdomain (dikelola oleh Owner)
- lisensi
- data produk sendiri
- data transaksi sendiri

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
Customer tidak memilih client melalui marketplace, melainkan melalui landing page pemilihan usaha rental yang mengarahkan ke subdomain/katalog client yang dipilih.

### 4.3 Produk
Produk memiliki nama, deskripsi, kategori, harga sewa, stok, foto, dan status. Admin Rental dapat mengisi teks ketentuan jaminan identitas per produk (misalnya syarat dokumen yang perlu dibawa saat pengambilan barang). Teks ini murni informasi yang ditampilkan ke customer dan tidak memicu validasi atau logika otomatis apa pun pada sistem.

### 4.4 Availability
Ketersediaan ditentukan berdasarkan stok, jumlah yang sedang dipesan, dan periode penyewaan. Sistem harus mencegah booking melebihi stok pada periode yang sama.

### 4.5 Booking & Checkout
Satu booking hanya berasal dari satu client. Pada tahap checkout, customer mengisi jumlah unit, periode sewa, dan alamat pengiriman. Alamat pengiriman disimpan pada data order dan digunakan kembali oleh Admin Rental saat mencatat pengiriman.

### 4.6 Payment
Pembayaran menggunakan transfer manual. Customer melakukan transfer dan mengunggah bukti pembayaran setelah jaminan identitas tersimpan. Admin Rental melakukan verifikasi. Tidak ada payment gateway pada tahap awal.

### 4.7 Identity Guarantee
Tidak menggunakan deposit uang. Customer mengisi data identitas (nama lengkap, nomor identitas, foto identitas, foto wajah, alamat sesuai identitas) sebagai jaminan administratif pada tahap checkout, sebelum bukti pembayaran diunggah. Tidak ada verifikasi identitas eksternal pada tahap awal.

### 4.8 Shipping
Pengiriman melalui kurir pihak ketiga atau langsung. Sistem mencatat nama kurir, nomor resi, tanggal, dan status, dengan alamat tujuan mengikuti alamat pengiriman yang telah diisi pada order. Customer mengonfirmasi penerimaan barang setelah barang diterima. Tidak ada integrasi GPS/API kurir pada tahap awal.

### 4.9 Return
Pengembalian melalui kurir atau langsung. Pengembalian dicatat terpisah dari pengiriman awal.

### 4.10 Damage Handling
Kondisi barang dicatat sebelum dan sesudah penyewaan melalui checklist dan foto. Sistem menyimpan laporan kerusakan, status, dan tanggapan customer. Penyelesaian mengikuti kebijakan penyedia rental, bukan otomatisasi AI.

### 4.11 Client Branding
Admin Rental dapat mengubah nama usaha, deskripsi, logo, dan warna tema miliknya sendiri melalui fitur Profil dan Branding. Subdomain dan pembuatan akun Admin Rental tetap menjadi wewenang Owner.

## 5. Functional Requirements

### Customer
- Register, login, logout, profile.
- View categories, equipment, detail (termasuk ketentuan jaminan identitas), availability.
- Select rental period.
- Create/view booking dengan alamat pengiriman.
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
- Update profil dan branding usaha (nama usaha, deskripsi, logo, warna tema).
- Category management.
- Equipment management, termasuk pengisian ketentuan jaminan identitas per produk.
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
- License management.
- Client status.
- Dashboard monitoring ringkas (jumlah client, status client, status license), tanpa mengakses transaksi harian.

## 6. License
Setiap client memiliki tanggal mulai, tanggal berakhir, dan status lisensi:
```text
active
expired
suspended
```

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
6. Lokasi tambahan dengan akun/domain/license tersendiri diperlakukan sebagai client baru.
7. Tidak ada deposit uang.
8. Tidak ada GPS.
9. Tidak ada API kurir pada tahap awal.
10. Pembayaran transfer manual.
11. Satu order hanya berasal dari satu client.
12. Core layout dan struktur fitur dikendalikan RentalBase.
13. Ketentuan jaminan identitas bersifat teks informasi, bukan aturan yang divalidasi otomatis oleh sistem.

## 11. Success Criteria
1. Customer dapat mengakses website client.
2. Customer dapat melihat peralatan dan ketersediaannya.
3. Customer dapat melakukan booking beserta alamat pengiriman.
4. Customer dapat mengisi jaminan identitas dan mengunggah bukti pembayaran.
5. Admin dapat memverifikasi pembayaran.
6. Admin dapat mengelola peralatan, stok, dan profil/branding usahanya.
7. Sistem mencatat pengiriman, konfirmasi penerimaan, dan pengembalian.
8. Sistem mencatat kondisi dan kerusakan.
9. Data antar-client terisolasi dengan `client_id`.
10. Owner dapat mengelola client, akun Admin Rental, dan lisensi melalui dashboard monitoring ringkas.
11. Fitur utama berjalan tanpa error pada skenario pengujian yang telah ditentukan (fungsional, performa, dan kompatibilitas).
