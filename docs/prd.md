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
- Menambahkan peralatan ke dalam Keranjang Belanja (Cart) sebelum checkout.
- Login/registrasi hanya diwajibkan saat menekan tombol "Sewa Alat" atau mengakses keranjang.
- Booking dan checkout sekaligus untuk item di dalam keranjang, termasuk mengisi alamat pengiriman.
- Mengisi data jaminan identitas untuk setiap order (nama lengkap, nomor KTP, foto KTP, selfie wajah, dan alamat sesuai identitas).
- Mengunggah bukti pembayaran.
- Melihat status pembayaran, pesanan, dan pengiriman.
- Menerima notifikasi status pesanan (verifikasi, pengiriman, dll).
- Mengonfirmasi penerimaan barang.
- Melakukan pengembalian.
- Melihat riwayat.

### 2.2 Admin Rental
- Mengelola profil dan branding usaha (nama usaha, deskripsi, logo, warna tema) dalam batas konfigurasi yang disediakan sistem.
- Mengelola kategori, product (termasuk multi foto produk), physical equipment unit, harga, status unit, dan identity guarantee requirements per product.
- Mengelola metode pembayaran toko (multi rekening bank dan QRIS).
- Memeriksa booking.
- Memverifikasi pembayaran.
- Mengelola pengiriman dan pengembalian.
- Menambahkan tagihan denda keterlambatan (Late Fees) jika customer terlambat mengembalikan barang.
- Memeriksa kondisi barang.
- Mengelola laporan kerusakan.
- Melihat dashboard operasional dan laporan transaksi.

### 2.3 Owner
- Mengelola client dan peninjauan pendaftaran toko (`client_registrations`).
- Meninjau, menyetujui (approve), atau menolak pengajuan pendaftaran client baru.
- Mengaktifkan/menonaktifkan client.
- Mengelola subdomain dan subscription.
- Melihat dashboard monitoring ringkas seluruh client.

Owner **bukan** operator transaksi rental harian, dan tidak mengakses detail transaksi customer.

## 3. Business Model
RentalBase adalah software untuk beberapa penyedia rental peralatan. Setiap penyedia rental menjadi satu **client**.

Setiap client memiliki:
- `client_id`
- akun Admin Rental (dibuat otomatis oleh sistem setelah pengajuan pendaftaran disetujui Owner)
- domain/subdomain (diajukan calon client & disetujui Owner)
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
Halaman `/` pada root domain (misal `rentalbase.id`) digunakan sebagai **Landing Page Utama Platform RentalBase** (bukan katalog client). 

**Fitur & Komponen Landing Page Utama:**
1. **Hero Section**: Penjelasan platform SaaS RentalBase.
2. **Platform Features**: Highlight fitur keunggulan (Multi-Tenant, Dynamic Availability, Timestamp Precision, Denda Hybrid, & Notifikasi).
3. **Pricing & Package Showcase**: Tabel perbandingan 3 paket subskripsi (Starter, Business, Professional) beserta fitur, harga, dan limit paket (limit produk, limit unit, & custom warna tema). Setiap paket dilengkapi tombol **"Memulai" / "Get Started"**.
4. **Alur Pendaftaran Tenant Semi Self-Service**:
   - Menekan tombol **"Memulai"** mengarahkan calon client ke Form Pendaftaran (`client_registrations`).
   - Calon client mengisi Nama Usaha, Deskripsi, Subdomain yang diinginkan (dengan realtime availability check), Paket yang dipilih, Nama Admin/PIC, Email, WhatsApp, dan Password Admin.
   - Pendaftaran tersimpan dengan status `menunggu_verifikasi`.
   - Owner meninjau pengajuan di Owner Dashboard. Begitu disetujui (ACC), sistem otomatis membuatkan record `clients`, `subscriptions`, dan `users` (Admin Rental).
   - Admin Rental dapat login langsung dari Landing Page (tombol "Login Admin") dan otomatis di-redirect ke dashboard subdomain miliknya.

### 4.2.1 Guest Browsing & Login Gate
Landing page, katalog, detail produk, dan pengecekan availability dapat diakses tanpa login (guest). Login/registrasi baru diwajibkan saat customer menekan tombol "Sewa Alat" untuk mulai booking. Setelah login berhasil, customer diarahkan kembali ke produk dan periode sewa yang sebelumnya dipilih (intended redirect), bukan ke halaman awal.

### 4.2.2 Cakupan Akun Customer
Akun Customer bersifat global: satu akun (satu email/password) dapat dipakai untuk login dan bertransaksi di subdomain client mana pun. Ini berbeda dengan akun Admin Rental yang terikat pada satu client. Meskipun akunnya global, tampilan riwayat pesanan pada satu subdomain hanya menampilkan transaksi milik client tersebut.

### 4.3 Produk
Product memiliki name, description, category, rental price, multi foto (JSON array), identity guarantee requirements, dan status. Jumlah unit fisik tidak disimpan langsung pada product. Setiap physical equipment dicatat pada `equipment_units` dan memiliki `asset_code` serta status operasional sendiri. Admin Rental dapat mengisi identity guarantee requirements sebagai teks informasi yang ditampilkan kepada customer dan tidak memicu validasi atau logika otomatis.

### 4.4 Availability
Ketersediaan ditentukan berdasarkan jumlah `equipment_units` yang dapat digunakan, unit yang sedang dialokasikan pada `order_item_units`, status operasional unit, dan periode penyewaan. Sistem harus mencegah booking melebihi jumlah unit yang tersedia pada periode yang sama.

### 4.5 Booking & Checkout
Satu booking hanya berasal dari satu client. Customer dapat menambahkan berbagai peralatan ke dalam Keranjang (Cart) dan melakukan checkout sekaligus, atau melakukan transaksi langsung ("Sewa Alat") pada halaman produk. Pada tahap checkout, customer memvalidasi quantity, periode sewa (jam & tanggal), dan alamat pengiriman. Satu pesanan (`orders`) menggunakan 1 periode sewa yang sama untuk seluruh item. Periode sewa dicatat secara presisi (timestamp) untuk perhitungan masa sewa 24 jam.

**Shopee-Style Cart Grouping by Periode Sewa:**
Di dalam Keranjang Belanja (Cart), peralatan yang dimasukkan dikelompokkan secara otomatis berdasarkan kesamaan periode sewa (`start_date` dan `end_date`). Customer dapat memilih/menceklis sekelompok barang pada tanggal sewa yang sama untuk di-checkout sekaligus menjadi satu order (`orders.total_amount`).

**Rumus Kalkulasi Total Harga:**
- Durasi Sewa dalam Hari = $\lceil (\text{end\_date} - \text{start\_date}) / 24 \text{ jam} \rceil$ (dibulatkan ke atas, minimal 1 hari).
- Subtotal Item = $\text{unit\_price} \times \text{quantity} \times \text{durasi\_hari}$.
- Total Transaction (`orders.total_amount`) = Jumlah seluruh Subtotal Item.

Physical equipment unit dialokasikan melalui `order_item_units` sesuai quantity. Alamat pengiriman disimpan pada `orders.shipping_address` dan digunakan kembali oleh Admin Rental saat mencatat pengiriman.

### 4.6 Payment
Pembayaran menggunakan transfer manual atau QRIS. Admin Rental mengelola metode pembayaran toko (multi rekening bank dan QRIS) melalui fitur pengaturan. Customer melihat daftar metode pembayaran yang tersedia, melakukan transfer/scan QRIS, dan mengunggah bukti pembayaran setelah jaminan identitas berstatus `diverifikasi`. Admin Rental melakukan verifikasi. Tidak ada payment gateway otomatis pada tahap awal.

### 4.7 Identity Guarantee
Tidak menggunakan deposit uang. Customer mengisi data identitas (nama lengkap, nomor identitas, foto identitas, foto wajah, alamat sesuai identitas) sebagai jaminan administratif pada tahap checkout, sebelum bukti pembayaran diunggah. Tidak ada verifikasi identitas eksternal pada tahap awal.

### 4.8 Shipping
Pengiriman melalui kurir pihak ketiga atau langsung. Sistem mencatat nama kurir, nomor resi, tanggal, dan status, dengan alamat tujuan mengikuti alamat pengiriman yang telah diisi pada order. Customer mengonfirmasi penerimaan barang setelah barang diterima. Tidak ada integrasi GPS/API kurir pada tahap awal.

### 4.9 Return & Late Fees
Pengembalian melalui kurir atau langsung. Pengembalian dicatat terpisah dari pengiriman awal. Karena batas akhir masa sewa (`end_date`) dihitung dengan presisi jam (*timestamp*), Denda Keterlambatan dihitung secara **Hybrid**: Sistem secara otomatis mengkalkulasi estimasi denda (`calculated_late_fee`) berdasarkan selisih jam keterlambatan dikalikan tarif denda produk (`products.late_fee_per_hour`). Namun, Admin Rental memiliki wewenang penuh untuk meng-override (mengubah) nominal denda akhir (`late_fee_amount`) sebelum disimpan apabila ada pertimbangan khusus (diskon/toleransi). Customer mengunggah bukti transfer denda (`returns.late_fee_proof`) jika ada denda.

**Mekanisme Auto-Complete 3 Hari (72 Jam):**
Jika setelah barang dikirim customer tidak/lupa menekan tombol 'Konfirmasi Penerimaan', sistem akan secara otomatis mengonfirmasi penerimaan barang dan memproses penyelesaian order 3 hari (72 jam) setelah pengiriman dicatat, selama pemeriksaan fisik barang berstatus aman.

### 4.10 Damage Handling
Kondisi physical equipment unit dicatat sebelum dan sesudah penyewaan melalui checklist dan image. Sistem menyimpan unit yang diperiksa, laporan kerusakan, status, dan tanggapan customer. Penyelesaian mengikuti kebijakan penyedia rental, bukan otomatisasi AI. Admin dapat memberikan tagihan **Biaya Ganti Rugi Kerusakan (Compensation Fee)** kepada customer di dalam Damage Case yang wajib dilunasi. Customer mengunggah bukti transfer ganti rugi (`damage_cases.compensation_proof`).

### 4.11 Order Cancellation & Refund
- **Pembatalan oleh Customer**: Customer dapat membatalkan pesanan miliknya secara mandiri **hanya jika** status order masih `menunggu_konfirmasi` atau `menunggu_pembayaran` (sebelum pembayaran diverifikasi).
- **Pembatalan oleh Admin**: Admin Rental dapat menolak atau membatalkan pesanan kapan saja, dengan menyertakan alasan pembatalan. Jika pesanan dibatalkan setelah customer melakukan pembayaran, uang tidak dikembalikan secara otomatis oleh sistem. Status refund dicatat dalam sistem (`menunggu_refund`, `refund_selesai`), dan admin wajib melakukan transfer manual ke customer di luar sistem. Saat mengubah status menjadi `refund_selesai`, Admin diwajibkan mengunggah foto bukti transfer (`refund_proof`) agar dapat dilihat oleh customer sebagai bukti yang sah.

### 4.12 Client Branding
Admin Rental dapat mengubah nama usaha, deskripsi, dan logo miliknya sendiri melalui fitur Profil dan Branding. Pengubahan warna beberapa elemen desain halaman hanya tersedia untuk client dengan paket **Business** atau **Professional**. Paket **Starter** menggunakan warna/desain bawaan RentalBase. Subdomain dan pembuatan akun Admin Rental tetap menjadi wewenang Owner.

### 4.13 Notification Triggers Matrix
Sistem mengirimkan notifikasi (*in-app notification*) pada kejadian-kejadian berikut:
1. **Order Baru Dibuat (Customer → Admin Rental)**: "Pesanan baru #{order_code} telah dibuat dan menunggu konfirmasi."
2. **Order Dikonfirmasi / Ditolak (Admin → Customer)**: "Pesanan #{order_code} Anda telah dikonfirmasi / ditolak."
3. **Review Jaminan Identitas (Admin → Customer)**: "Jaminan Identitas untuk #{order_code} telah diverifikasi / ditolak."
4. **Bukti Bayar Diunggah (Customer → Admin Rental)**: "Bukti pembayaran baru untuk #{order_code} perlu diverifikasi."
5. **Review Pembayaran (Admin → Customer)**: "Pembayaran Anda untuk #{order_code} telah diverifikasi / ditolak."
6. **Pengiriman Barang (Admin → Customer)**: "Pesanan #{order_code} sedang dikirim (Resi: {tracking_number})."
7. **Penerimaan Barang Konfirmasi (Customer → Admin)**: "Customer mengonfirmasi penerimaan barang untuk #{order_code}."
8. **Pengembalian & Tagihan Denda (Admin → Customer)**: "Status pengembalian #{order_code} diperbarui / Denda Keterlambatan ditagihkan."
9. **Tagihan Ganti Rugi Kerusakan (Admin → Customer)**: "Tagihan Ganti Rugi Kerusakan ditambahkan pada pesanan #{order_code}."
10. **Refund Dikirim (Admin → Customer)**: "Refund untuk pesanan #{order_code} telah ditransfer. Bukti transfer telah dilampirkan."

## 5. Functional Requirements

### Customer
- Register (akun global, satu kali daftar berlaku untuk semua client), login, logout, profile.
- View categories, products, equipment details (termasuk identity guarantee requirements), availability — dapat diakses tanpa login.
- Manage Cart (Add/Remove items to shopping cart).
- Select rental period.
- Login/registrasi (jika belum) saat menekan "Sewa Alat" atau checkout keranjang.
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