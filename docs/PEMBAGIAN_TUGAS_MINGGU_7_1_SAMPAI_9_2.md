# RENTALBASE — DOKUMEN MASTER PEMBAGIAN TUGAS & SPESIFIKASI HALAMAN

**Cakupan:** Minggu 7 Bagian 1, Minggu 7 Bagian 2, Minggu 8 Bagian 1, Minggu 8 Bagian 2, Minggu 9 Bagian 1, dan Minggu 9 Bagian 2 (finalisasi).  
**Tujuan dokumen:** Menyatukan seluruh pembagian tugas empat anggota menjadi satu acuan kerja yang menjelaskan halaman apa yang dibuat, siapa penanggung jawabnya, route yang dituju, tujuan halaman, susunan menu/komponen, field, aksi, validasi, status, integrasi, dan kriteria selesai.  
**Status dokumen:** Spesifikasi kerja gabungan. Dokumen ini **bukan** pernyataan bahwa semua halaman atau API sudah selesai diimplementasikan.

---

## DAFTAR ISI

1. Identitas tim dan pembagian tanggung jawab
2. Gambaran aplikasi dan aturan umum UI
3. Peta 40 halaman/pekerjaan utama
4. Minggu 7 Bagian 1 — Layout utama
5. Minggu 7 Bagian 2 — Keranjang, checkout, order, dan inspeksi
6. Minggu 8 Bagian 1 — Katalog admin, autentikasi customer, dashboard & client Owner, kerusakan customer
7. Minggu 8 Bagian 2 — Unit fisik, branding & metode pembayaran, approval tenant & admin toko, retur customer
8. Minggu 9 Bagian 1 — Kasus kerusakan admin, tenant suspended & error UI, laporan transaksi & subscription, notifikasi customer & admin
9. Minggu 9 Bagian 2 — Profil customer, integrasi UI, notifikasi & commands Owner, activity log
10. Aturan integrasi lintas halaman
11. Perbedaan spesifikasi lama dengan rancangan RentalBase terkini yang harus disinkronkan
12. Checklist final per anggota

---

## 1. IDENTITAS TIM DAN PEMBAGIAN TANGGUNG JAWAB

Nama/label penanggung jawab ditulis mengikuti enam dokumen tugas sumber. Nama panggilan di dokumen sumber tidak selalu disertai nama lengkap.

| Label pada dokumen tugas | Nama formal yang diketahui dari roster tim | Peran umum | Lingkup tugas dalam dokumen sumber |
|---|---|---|---|
| **Iqo** | **Tersiqo Alfarezel** | Project Manager | Katalog dan detail produk customer, keranjang, checkout, laporan kerusakan customer, kategori/produk admin, unit fisik, kasus kerusakan admin, profil customer, serta integrasi transaksi. (10 tugas) |
| **Yanuar** | **Yanuar Alda Baran** | Front End Developer | Landing page publik, pesanan customer (riwayat), login gate & status verifikasi tenant, detail pesanan customer, profil & branding toko admin (tema/logo), metode pembayaran admin (bank & QRIS), autentikasi customer & reset password, halaman error 403/404 & tenant suspend, serta integrasi UI. (10 tugas) |
| **Fiza** | **Fiza Rahmatus Sholikha** | Back End Developer | Dashboard Admin, form pendaftaran tenant, login Admin/Owner, laporan transaksi admin, Base Layout Owner, dashboard Owner, manajemen client/admin Owner, antrean persetujuan tenant Owner, subscription Owner, notifikasi Owner, integrasi backend, alokasi unit & scheduled commands. (10 tugas) |
| **Iak** | Nama formal tidak tercantum di enam dokumen sumber; cocokkan dengan roster resmi tim sebelum penyerahan | Anggota ke-4 pada dokumen sumber | Pengiriman admin, penerimaan & denda retur admin, pengembalian customer, inspeksi kondisi fisik, order admin (daftar & detail pesanan admin: verifikasi identitas, pembayaran, alokasi unit fisik, alur refund), notifikasi customer & admin, activity log admin & owner, serta integrasi logistik & operasional toko. (10 tugas) |

> **Penting untuk penamaan anggota:** roster tim yang tersedia memuat Zaskia Maulidina Mutiara Hati sebagai anggota keempat, sedangkan enam file sumber memakai label **Iak**. Dokumen ini mempertahankan label **Iak** agar penugasan tidak dipindahkan secara diam-diam. Jika Iak adalah nama panggilan Zaskia, samakan label tersebut menjadi **Zaskia Maulidina Mutiara Hati** sebelum dokumen final diserahkan.

### 1.1 Prinsip tanggung jawab

- Penanggung jawab halaman bertugas menyiapkan UI, state interaksi, validasi sisi UI, empty/loading/error state, dan koneksi ke endpoint yang disepakati untuk halaman tersebut.
- Pemilik modul backend perlu memastikan endpoint, validasi, hak akses, status, dan struktur respons sesuai dokumentasi API. UI tidak boleh menjadi satu-satunya tempat validasi aturan bisnis.
- Layout dasar yang dipakai bersama harus disepakati sebelum setiap anggota membuat halaman turunannya. Perubahan header, sidebar, warna, atau komponen global harus dikomunikasikan kepada pemilik layout.
- Enam dokumen sumber memakai istilah/rute/status versi awal. Bagian 11 menjelaskan konflik yang perlu disinkronkan dengan aturan RentalBase terkini; jangan mengimplementasikan dua aturan yang bertentangan sekaligus.

---

## 2. GAMBARAN APLIKASI DAN ATURAN UMUM UI

RentalBase adalah aplikasi pengelolaan rental berbasis banyak client/tenant. Root platform, halaman toko tenant, panel Admin Rental, dan panel Owner harus dibedakan dengan jelas.

### 2.1 Jenis pengguna dan area aplikasi

1. **Pengunjung publik** — melihat landing page RentalBase, informasi produk SaaS, paket, fitur, formulir pendaftaran tenant, dan portal autentikasi.
2. **Customer** — melihat katalog pada toko/tenant tertentu (guest browsing), memilih periode sewa, memasukkan barang ke keranjang / menekan "Sewa Alat", checkout (wajib login), mengirim identitas/jaminan, mengunggah bukti pembayaran, melihat status pesanan, pelacakan refund, pengiriman, pengembalian, notifikasi, dan profil.
3. **Admin Rental** — mengelola data satu toko/tenant: login admin (dengan pengecekan status toko belum terverifikasi/ditolak/aktif), pesanan, konfirmasi/pembatalan order, verifikasi identitas & pembayaran, kategori, produk, unit fisik, pengiriman, retur, pemeriksaan fisik, kasus kerusakan, metode pembayaran, laporan, branding, manajemen/pembuatan akun sesama Admin toko (via menu Profil sesuai kuota paket), notifikasi, dan activity log.
4. **Owner/Super Admin** — mengelola platform dan tenant: pengajuan tenant, client, subscription, dashboard monitoring, notifikasi Owner, dan activity log tingkat platform. Panel Owner tidak menampilkan detail transaksi customer lintas tenant sebagai data operasional.

### 2.2 Layout dasar yang dibutuhkan

| Layout | Dipakai oleh | Komponen minimum |
|---|---|---|
| **Public Layout** | Landing page, pendaftaran tenant multi-step, halaman publik lainnya | Navbar RentalBase, menu Fitur/Harga/Kontak, CTA Login/Daftar ("Memulai"), footer dan tautan legal. |
| **Customer Layout** | Katalog toko, detail produk, cart, checkout, pesanan, retur customer, laporan kerusakan customer, notifikasi, profil | Header toko (logo, Beranda, Produk, Cara Sewa, Cart badge, Pesanan Saya, Notifikasi, Profile/Login), footer kontak/jam operasional/kebijakan. Dibuat oleh Iqo. |
| **Admin Layout** | Dashboard Admin dan seluruh halaman pengelolaan tenant | Sidebar Admin (Operasional, Katalog, Laporan & Audit, Pengaturan), topbar nama toko, lonceng notifikasi, **menu dropdown profil** (Kelola/Buat Akun Admin Toko, Tema & Branding, Logout), breadcrumb, responsif HP. Dibuat oleh Fiza. |
| **Owner Layout** | Dashboard dan halaman manajemen pusat | Sidebar Owner (Dashboard, Registrasi, Clients, Subscriptions, Activity Log, Notifikasi), topbar profil Super Admin, konten terpisah dari panel tenant. Dibuat oleh Fiza. |
| **Auth Layout** | Login, register, lupa/reset password | Form terpusat tanpa sidebar; validasi per-field, show/hide password bila tersedia, pesan error dan loading. |
| **Global Error/Suspended Layout** | 403, 404, tenant suspend/nonaktif | Tampilan fokus pada pesan dan aksi yang sesuai, tidak menampilkan menu yang justru dilarang pada halaman suspended. Dibuat oleh Yanuar. |

### 2.3 Aturan desain dan perilaku global

- Semua halaman harus memiliki **loading state**, **empty state**, **error state**, dan **success feedback** untuk aksi yang berkomunikasi dengan backend.
- Form harus menunjukkan field wajib, format yang diterima, kesalahan validasi, dan keadaan tombol ketika request sedang berlangsung. Hindari submit ganda.
- Tabel admin perlu mendukung pencarian/filter/pagination jika datanya berbentuk daftar. Pada layar kecil, tabel harus tetap dapat digunakan—misalnya scroll horizontal atau tampilan kartu.
- Status harus memakai satu label/warna yang konsisten pada dashboard, tabel, detail, notifikasi, dan riwayat. Jangan membuat status baru hanya untuk mempercantik UI.
- Semua data tenant harus terisolasi berdasarkan client aktif. Customer/Admin tidak boleh dapat mengakses data tenant lain dengan mengubah URL atau ID.
- Harga, durasi sewa, denda, ketersediaan unit, otorisasi, serta perubahan status final dihitung/diperiksa oleh backend. Hitungan di frontend hanya untuk preview.
- Foto produk, KTP, selfie, bukti pembayaran, foto kondisi, bukti penggantian kerusakan, dan bukti refund memerlukan preview, validasi tipe/ukuran, pesan gagal unggah, serta akses file yang sesuai hak pengguna.
- Jangan meninggalkan hardcoded JSON/dummy data setelah integrasi selesai. Data contoh boleh dipakai ketika fase UI belum terhubung, tetapi harus diberi label dan dihapus pada Minggu 9 Bagian 2.

---

## 3. PETA 40 HALAMAN UTAMA

Peta berikut membagi beban secara seimbang: **tepat 10 pekerjaan per anggota (total 40 pekerjaan)**. Pertukaran tugas telah disesuaikan: Fiza mengerjakan seluruh modul Owner (termasuk Dashboard Owner). Modul Order Admin (Daftar & Detail Pesanan Admin) dikerjakan oleh **Iak** (Zaskia) agar seluruh siklus operasional fisik toko terpusat; modul Detail Pesanan Customer & Pembayaran dikerjakan oleh **Iqo** (Project Manager) menyambung alur Checkout; sedangkan **Yanuar** (Front End Developer) mengerjakan Laporan Kerusakan Customer, Profil & Branding Toko Admin serta Metode Pembayaran Admin. Form Pendaftaran Tenant resmi menjadi tanggung jawab Fiza.

| ID | Minggu | Halaman / pekerjaan | Penanggung jawab definitif |
|---|---|---|---|
| 7.1-I1 | 7 Bagian 1 | Katalog Toko Customer | Iqo | ✔
| 7.1-I2 | 7 Bagian 1 | Detail Produk Customer | Iqo | ✔
| 7.1-Y1 | 7 Bagian 1 | Landing Page RentalBase | Yanuar | ✔
| 7.1-Y2 | 7 Bagian 1 | Pesanan Saya Customer | Yanuar | ✔
| 7.1-F1 | 7 Bagian 1 | Dashboard Admin | Fiza | ✔
| 7.1-F2 | 7 Bagian 1 | Form Pendaftaran Tenant | Fiza | ✔
| 7.1-A1 | 7 Bagian 1 | Pengiriman Admin | Iak | ✔
| 7.1-A2 | 7 Bagian 1 | Penerimaan dan Denda Retur Admin | Iak | ✔
| 7.2-I1 | 7 Bagian 2 | Keranjang Customer | Iqo | ✔
| 7.2-I2 | 7 Bagian 2 | Checkout Customer | Iqo | ✔
| 7.2-Y1 | 7 Bagian 2 | Login Gate & Status Verifikasi Tenant | Yanuar | ✔
| 7.2-Y2 | 7 Bagian 2 | Detail Pesanan Customer | Yanuar | ✔
| 7.2-F1 | 7 Bagian 2 | Login Admin dan Owner | Fiza | ✔
| 7.2-A3 | 7 Bagian 2 | Daftar Pesanan Admin | Iak (dialihkan dari Iqo / 7.2-I3) | ✔
| 7.2-A1 | 7 Bagian 2 | Detail Pesanan Admin | Iak (dialokasikan di Minggu 7.2) | ✔
| 8.1-I1 | 8 Bagian 1 | Kategori Admin | Iqo | ✔
| 8.1-I2 | 8 Bagian 1 | Produk Admin | Iqo | ✔
| 8.1-Y1 | 8 Bagian 1 | Login/Register Customer | Yanuar | ✔
| 8.1-Y2 | 8 Bagian 1 | Lupa/Reset Password | Yanuar | ✔
| 8.1-A1 | 8 Bagian 1 | Pemeriksaan Kondisi Fisik Admin | Iak (dialokasikan di Minggu 8.1) | ✔
| 8.1-F1 | 8 Bagian 1 | Dashboard Owner | Fiza (dialihkan ke Minggu 8.1 / sebelumnya 8.2-F3) | ✔
| 8.1-F2 | 8 Bagian 1 | Manajemen Client Owner | Fiza (dialihkan ke Minggu 8.1 / sebelumnya 8.2-F1) | ✔
| 8.1-I4 | 8 Bagian 1 | Laporan Kerusakan Customer | Iqo (dialihkan dari Iak / 8.1-A2) | ✔
| 8.2-I1 | 8 Bagian 2 | Unit Fisik Admin | Iqo | ✔
| 8.2-Y3 | 8 Bagian 2 | Profil/Branding Admin | Yanuar (dialihkan dari Iqo / 8.2-I2) | ✔
| 8.2-Y1 | 8 Bagian 2 | Metode Pembayaran Admin | Yanuar (dimajukan ke Minggu 8.2) | ✔
| 8.2-F4 | 8 Bagian 2 | Antrean Persetujuan Tenant Owner | Fiza (dimajukan dari 9.1 / dialihkan dari Yanuar) | ✔
| 8.2-F2 | 8 Bagian 2 | Manajemen Akun Admin Toko (Via Profil Admin) | Fiza | ✔
| 8.2-A3 | 8 Bagian 2 | Pengembalian Customer | Iak (dialihkan ke Minggu 8.2) | ✔
| 9.1-I1 | 9 Bagian 1 | Kasus Kerusakan Admin | Iqo (dialihkan dari Minggu 8.1 / sebelumnya 8.1-I3) | ✔
| 9.1-Y2 | 9 Bagian 1 | Halaman Tenant Tidak Aktif/Suspended | Yanuar (dialihkan dari Minggu 8.2 / sebelumnya 8.2-Y2) |
| 9.1-Y1 | 9 Bagian 1 | Error 403 dan 404 | Yanuar (dialihkan ke Minggu 9.1) |
| 9.1-F2 | 9 Bagian 1 | Laporan Transaksi Admin | Fiza (dialihkan dari Minggu 8.1 / sebelumnya 8.1-F2) |
| 9.1-F1 | 9 Bagian 1 | Subscription Tenant Owner | Fiza |
| 9.1-A1 | 9 Bagian 1 | Notifikasi Customer | Iak (dialihkan dari Minggu 8.2 / sebelumnya 8.2-A1) |
| 9.1-A2 | 9 Bagian 1 | Notifikasi Admin | Iak (dialihkan dari Minggu 8.2 / sebelumnya 8.2-A2) |
| 9.2-I1 | 9 Bagian 2 | Profil Customer dan Integrasi Frontend | Iqo |
| 9.2-F1 | 9 Bagian 2 | Notifikasi Owner, Backend Order & Scheduled Commands | Fiza |
| 9.2-A1 | 9 Bagian 2 | Activity Log Admin | Iak (dialihkan dari Minggu 9.1 / sebelumnya 9.1-A1) |
| 9.2-A2 | 9 Bagian 2 | Activity Log Owner dan Integrasi Logistik | Iak |

---

# 4. MINGGU 7 BAGIAN 1 — LAYOUT UTAMA

**Tujuan minggu:** Membuat pintu masuk tiap area: halaman publik, halaman awal customer, dashboard Admin, pendaftaran tenant, dan awal proses pengiriman/retur. Dokumen sumber menyebut penggunaan data dummy JSON untuk tahap layout awal.

## 4.1 — Iqo: Katalog Toko Customer

**ID:** 7.1-I1  
**Nama halaman:** Katalog Toko Publik / Beranda Toko  
**Route pada sumber:** `http://127.0.0.1:8000/`  
**Layout:** Base Layout Customer; Iqo juga bertanggung jawab membuat header/footer customer untuk digunakan halaman customer lain.

### Tujuan
Menjadi halaman pertama bagi customer yang mengunjungi sebuah toko rental. Customer dapat menemukan barang, menyaring katalog, menentukan periode sewa, melihat indikasi ketersediaan, lalu membuka detail produk.

### Bagian dan komponen UI
1. **Header toko (Customer Layout)**
   - Logo dan nama toko (branding tenant).
   - Menu navigasi: Beranda, Produk, Cara Sewa.
   - Ikon keranjang belanja (Cart) dengan badge jumlah item.
   - Tombol Pesanan Saya dan Lonceng Notifikasi (jika sudah login).
   - Tombol Login / Profil sesuai status autentikasi.
   - Header konsisten dipakai di detail produk, keranjang, checkout, pesanan, notifikasi, dan profil.
2. **Area pencarian/filter**
   - Input pencarian nama produk.
   - Filter kategori melalui checkbox (kategori sesuai data tenant, bukan daftar hardcoded permanen).
   - Filter rentang harga minimum–maksimum.
   - Tombol reset filter bila filter diterapkan.
3. **Pemilih tanggal sewa**
   - Tanggal mulai dan tanggal selesai (mendukung presisi jam/timestamp).
   - Validasi bahwa tanggal selesai tidak lebih awal daripada tanggal mulai.
   - Saat periode berubah, ketersediaan produk diperbarui secara dinamis berdasarkan periode itu.
4. **Grid kartu produk**
   - Foto/cover produk (rasio seragam 4:3).
   - Nama produk dan badge kategori.
   - Harga sewa per hari dan tarif denda per jam.
   - Badge ketersediaan unit yang jelas.
   - Tombol **Lihat Detail** dan tombol cepat **Sewa Alat**.
   - Pagination di bagian bawah.
5. **Guest Browsing & Login Gate ("Sewa Alat")**
   - Pengunjung dapat bebas menjelajahi katalog, mencari produk, dan melihat availability tanpa harus login (*guest browsing*).
   - Menekan tombol **"Sewa Alat"** memicu login gate: jika customer belum login, munculkan modal/popup login/register yang mengikat produk dan periode sewa yang dipilih, lalu melakukan *intended redirect* ke checkout/cart setelah login berhasil.
6. **Footer toko**
   - Kontak toko dan tautan media sosial jika ada.
   - Jam operasional dan lokasi toko.
   - Kebijakan pengembalian/retur dan informasi bantuan.

### Data dan perilaku
- Katalog dan kategori harus milik tenant yang sedang dibuka (`client_id`).
- Search, kategori, harga, pagination, dan periode sewa harus bekerja bersama; perubahan filter tidak boleh menghapus filter lain secara tidak sengaja.
- Jika tidak ada produk sesuai filter, tampilkan empty state beserta aksi membersihkan filter.
- Jika request gagal, tampilkan pesan dan aksi muat ulang; jangan menampilkan seolah-olah stok tersedia.

### Kriteria selesai
Header/footer reusable tersedia; filter/search berjalan; kartu menggunakan data produk; tanggal sewa memengaruhi availability; tombol Sewa Alat menerapkan login gate; tautan detail membuka produk yang dipilih; layout responsif.

## 4.2 — Iqo: Detail Produk Customer

**ID:** 7.1-I2  
**Nama halaman:** Detail Produk  
**Route pada sumber:** `/products/{id}`  
**Route resmi tenant:** `/client/{subdomain}/products/{product}`  
**Layout:** Base Layout Customer.

### Tujuan
Menampilkan informasi lengkap satu jenis produk agar customer dapat memeriksa foto, harga, spesifikasi, aturan sewa, ketersediaan unit, dan memilih jumlah sebelum menambahkan ke keranjang atau menyewa langsung.

### Bagian dan komponen UI
1. **Galeri foto di sisi kiri/atas pada mobile**
   - Foto utama besar.
   - Carousel/slider untuk foto lainnya (dari array `products.image`).
   - Thumbnail yang dapat diklik untuk mengganti foto utama.
2. **Informasi inti di sisi kanan/berikutnya pada mobile**
   - Nama produk dan kategori.
   - Harga sewa per hari.
   - Tarif denda keterlambatan per jam (`late_fee_per_hour`).
   - Ringkasan ketersediaan unit untuk periode yang dipilih.
   - Kontrol jumlah: tombol minus, angka, tombol plus; jumlah minimum 1.
   - Tombol **Tambah ke Keranjang** dan tombol **Sewa Alat (Direct Checkout)**.
3. **Tab informasi di bawah**
   - **Deskripsi:** penjelasan barang dan manfaatnya.
   - **Spesifikasi teknis:** informasi spesifik perlengkapan.
   - **Syarat penyewaan/jaminan:** teks informasi ketentuan jaminan identitas toko (bersifat informasi administratif, bukan uang deposit).

### Perilaku dan validasi
- Quantity tidak boleh kurang dari 1 dan harus dibatasi oleh ketersediaan unit fisik yang diperoleh dari backend.
- Jika periode belum dipilih, arahkan customer memilih periode sebelum memastikan ketersediaan.
- Tambah ke keranjang memberi feedback berhasil dan mempertahankan tenant serta periode sewa.
- Tombol **Sewa Alat** menerapkan *login gate*: jika belum login, arahkan ke login dengan *intended redirect* langsung ke direct checkout `/client/{subdomain}/checkout/{product}`.
- Ketentuan jaminan identitas pada produk bersifat teks informasi; sistem tidak memungut uang deposit.

### Kriteria selesai
Galeri berfungsi, tab dapat berpindah, harga/denda berasal dari data produk, jumlah tervalidasi ketersediaan unit, tombol Sewa Alat dan Tambah ke Keranjang bekerja sesuai login status.

## 4.3 — Yanuar: Landing Page Utama RentalBase

**ID:** 7.1-Y1  
**Nama halaman:** Landing Page Platform SaaS RentalBase  
**Route pada sumber:** `/`  
**Layout:** Base Layout Public; Yanuar bertanggung jawab membuat navbar dan footer publik.

### Tujuan
Memperkenalkan platform RentalBase kepada calon penyedia rental (calon client), menjelaskan keunggulan sistem SaaS multi-client, menyajikan perbandingan paket subscription, dan mengarahkan calon tenant ke alur pendaftaran.

### Bagian dan komponen UI
1. **Navbar publik**
   - Logo dan nama brand RentalBase.
   - Navigasi menu: Fitur, Paket & Harga, Kontak/Tentang.
   - Tombol CTA **Buka Toko / Memulai** dan tombol **Login Admin** (mengarahkan admin ke login panel tenant).
2. **Hero section**
   - Headline: *"RentalBase: Equipment Rental System — Kelola Bisnis Rental Lebih Efisien"*.
   - Subheadline penjelasan sistem manajemen sewa multi-client.
   - Tombol CTA utama: **"Memulai / Get Started"** mengarah langsung ke Form Pendaftaran Tenant (`/register-tenant`).
   - Tombol sekunder: **"Pelajari Fitur"** (scroll halus ke section fitur).
3. **Section platform features (Keunggulan RentalBase)**
   - Multi-Tenant & Multi-Client Architecture (data terisolasi aman per client).
   - Dynamic Availability & Timestamp Precision (kalkulasi jam & tanggal sewa akurat).
   - Hybrid Late Fees Calculation (kalkulasi denda otomatis dengan opsi override admin).
   - Identity Guarantee Workflow (verifikasi KTP & selfie tanpa deposit uang tunai).
   - Automated Operations & In-App Notifications (notifikasi real-time dan auto-completion).
4. **Section harga & paket subskripsi (Pricing Showcase)**
   - Data paket diambil dari konfigurasi aplikasi (`config/packages.php`):
     - **Starter:** Durasi 3 bulan, maks 10 produk, maks 50 unit, maks 5 kategori, 1 Admin Rental.
     - **Business:** Durasi 6 bulan, maks 50 produk, maks 100 unit, maks 20 kategori, 3 Admin Rental, custom warna tema toko.
     - **Professional:** Durasi 12 bulan, unlimited produk, unit & kategori, maks 10 Admin Rental, custom warna tema toko.
   - Masing-masing kartu paket dilengkapi tombol **"Pilih Paket & Mulai"** yang membawa parameter paket ke formulir pendaftaran.
5. **Footer publik**
   - Link legal: Terms of Service, Privacy Policy.
   - Link Login Admin (`/login`) untuk masuk dan memeriksa status aktivasi akun toko.
   - Kontak bantuan, media sosial, dan copyright RentalBase.

### Perilaku dan validasi
- CTA pendaftaran diarahkan ke alur pendaftaran tenant (`/register-tenant`).
- Tombol login membawa pengguna ke portal login yang sesuai.
- Tautan legal, navigasi anchor, dan kontak harus berfungsi dengan baik.
- Tampilan harus responsif dan profesional, bebas dari dummy hardcoded setelah integrasi.

### Kriteria selesai
Navbar/footernya reusable; CTA terhubung ke registrasi; informasi paket selaras dengan `config/packages.php` dan batasan backend; layout responsif dan bersih.

## 4.4 — Yanuar: Pesanan Saya Customer

**ID:** 7.1-Y2  
**Nama halaman:** Pesanan Saya  
**Route pada sumber:** `/my-orders`  
**Layout:** Base Layout Customer buatan Iqo.

### Tujuan
Memberi customer tempat melihat semua pesanan dalam tenant yang sedang aktif dan melakukan aksi berdasarkan status pesanan.

### Komponen UI
1. **Tab status horizontal** — Semua, Belum Dibayar, Menunggu Verifikasi, Diproses, Dikirim, Sedang Disewa, Selesai, Batal menurut dokumen sumber. Di mobile tab dapat di-swipe horizontal.
2. **Kartu pesanan**
   - Nomor/ID order.
   - Tanggal dan periode sewa.
   - Thumbnail produk.
   - Jika berisi beberapa produk, teks “+ X barang lainnya”.
   - Total tagihan.
   - Badge status.
3. **Aksi per pesanan**
   - Lihat Detail selalu tersedia.
   - Batalkan Pesanan hanya jika status dan aturan backend mengizinkan.
   - Ajukan Pengembalian ketika statusnya memungkinkan sesuai aturan bisnis.
4. **State UI** — empty state ketika tidak ada pesanan pada tab terpilih, loading skeleton, pesan error, serta pagination bila diperlukan.

### Perilaku dan keamanan
- Daftar hanya berisi pesanan milik customer yang sedang login serta dibatasi pada tenant yang aktif.
- Mengubah filter status tidak boleh mengambil pesanan milik pengguna lain.
- Tombol pembatalan/pengembalian tidak boleh muncul atau berhasil hanya berdasarkan manipulasi frontend; backend memvalidasi ulang.

### Kriteria selesai
Tab filter, kartu, detail link, dan aksi kondisional berfungsi; status konsisten dengan backend; halaman nyaman di HP.

## 4.5 — Fiza: Dashboard Admin Rental

**ID:** 7.1-F1  
**Nama halaman:** Dashboard Admin  
**Route pada sumber:** `/admin/dashboard`  
**Layout:** Base Layout Admin; Fiza membuat sidebar Admin, topbar profil, dan layout dasar admin yang reusable.

### Tujuan
Memberi gambaran operasional ringkas untuk Admin Rental atas pesanan masuk, verifikasi pembayaran, unit yang sedang disewa pelanggan, omzet berjalan, serta pemantauan kuota limit paket langganan.

### Struktur navigasi sidebar Admin (Lengkap & Terstruktur)
1. **Navigasi Utama / Operasional:**
   - 📊 **Dashboard** (`/admin/dashboard`)
   - 📦 **Pesanan** (`/admin/orders`)
   - 🚚 **Pengiriman** (`/admin/shipments`)
   - 🔄 **Pengembalian** (`/admin/returns`)
   - 🔍 **Inspeksi Fisik** (`/admin/condition-checks`)
   - ⚠️ **Kasus Kerusakan** (`/admin/damage-cases`)
2. **Katalog Peralatan:**
   - 🏷️ **Kategori** (`/admin/categories`)
   - 📦 **Produk** (`/admin/products`)
   - 🔢 **Unit Fisik** (`/admin/units`)
3. **Laporan & Audit:**
   - 📈 **Laporan Transaksi** (`/admin/reports`)
   - 📜 **Activity Log** (`/admin/activity-logs`)
4. **Pengaturan Usaha:**
   - 🎨 **Profil & Branding Toko** (`/admin/settings/profile` / `/admin/theme`)
   - 💳 **Metode Pembayaran** (`/admin/settings/payments`)
5. **Topbar Admin:**
   - Tombol hamburger (toggle collapse sidebar).
   - Nama toko / branding client aktif.
   - Tanggal operasional hari ini.
   - Badge status paket subscription (contoh: *Paket: Business (Aktif)*).
   - Lonceng notifikasi in-app dengan indicator unread badge.
   - Dropdown profil Admin (Avatar, Nama, Tombol Logout).

### Komponen utama halaman Dashboard
1. **Empat kartu metrik utama:**
   - **Order Baru:** Total pesanan baru berstatus `menunggu_konfirmasi` yang perlu ditinjau.
   - **Verifikasi Pembayaran:** Total pesanan `menunggu_pembayaran` dengan bukti bayar yang diunggah dan butuh verifikasi.
   - **Unit Sedang Disewa:** Total unit fisik yang sedang aktif disewa customer.
   - **Pendapatan Bulan Ini:** Total omzet transaksi selesai bulan berjalan (format Rupiah ringkas, misal `Rp 12.5 Juta`).
2. **Indikator kuota limit paket:**
   - Progress bar penggunaan kuota Produk (mis. 8 / 10 produk untuk Starter).
   - Progress bar penggunaan kuota Unit Fisik (mis. 24 / 50 unit untuk Starter).
   - Indikator peringatan jika kuota mencapai 80% (kuning) atau 100% penuh (merah) beserta tombol upgrade paket.
3. **Tabel pesanan terbaru (Recent Orders):**
   - 5 pesanan terkini berisi nomor order, nama customer, jadwal sewa, total tagihan, badge status, dan link langsung ke detail pesanan.
4. **State UI:**
   - Loading skeleton, empty state bila belum ada transaksi, error state penanganan request, serta tampilan responsif mobile.

### Perilaku dan keamanan
- Seluruh metrik, daftar order, dan kuota terisolasi ketat berdasarkan `client_id` Admin yang sedang login.
- Tidak membocorkan transaksi toko rental lain.
- Sidebar dan topbar menjadi komponen master layout untuk seluruh halaman Admin.

### Kriteria selesai
Layout Admin reusable terpasang; seluruh metrik dan kuota berasal dari query backend riil; navigasi sidebar lengkap dan terarah; tidak ada data dummy hardcoded.

## 4.6 — Fiza: Form Pendaftaran Tenant

**ID:** 7.1-F2  
**Nama halaman:** Pendaftaran Tenant (Buka Toko Rental)  
**Route pada sumber:** `/register-tenant`  
**Layout:** Base Layout Public (navbar & footer platform RentalBase).

### Tujuan
Mengumpulkan data bisnis, verifikasi ketersediaan subdomain secara real-time, dan akun PIC dari calon pemilik usaha rental untuk diajukan ke Owner platform.

### Form dan komponen multi-step
1. **Step Indicator:**
   - Step 1: Informasi Usaha & Subdomain
   - Step 2: Akun Admin / PIC Toko
   - Step 3: Konfirmasi & Pilihan Paket
2. **Step 1 — Info Usaha:**
   - Nama usaha / nama toko rental.
   - Deskripsi singkat layanan sewa.
   - Input usulan subdomain dengan preview suffix (misal: `[jaya] .rentalbase.com`).
   - Pengecekan real-time ketersediaan subdomain secara asynchronous (debounce 500ms) dengan feedback visual (tersedia, sudah dipakai, atau format tidak valid).
3. **Step 2 — Info PIC / Admin:**
   - Nama lengkap penanggung jawab.
   - Email aktif (digunakan untuk login dan menerima notifikasi approval).
   - Nomor WhatsApp aktif (format Indonesia, divalidasi untuk kebutuhan kontak Owner via WhatsApp).
   - Password dan konfirmasi password akun admin.
4. **Step 3 — Paket & Persetujuan:**
   - Pilihan paket subscription (Starter, Business, Professional) dengan ringkasan fitur & limit.
   - Checkbox persetujuan Syarat & Ketentuan platform RentalBase.
   - Tombol submit **"Kirim Pengajuan Pendaftaran"**.
5. **Rate Limiting & Feedback:**
   - Terapkan rate limit `throttle:5,60` pada submission endpoint untuk mencegah spam bot.
   - Loading spinner saat pengiriman berlangsung; tombol otomatis disabled mencegah submit ganda.
   - Layar sukses yang menginformasikan pengajuan berhasil dibuat dengan status `menunggu_verifikasi`, disertai penjelasan bahwa verifikasi sedang diproses oleh Owner, serta tombol CTA utama **"Menuju Halaman Login"** (`/login`). Calon tenant diarahkan langsung untuk mencoba login menggunakan email & password yang baru didaftarkan guna mengetahui status aktivasi tokonya.

### Validasi dan aturan backend
- Subdomain wajib unik pada seluruh database (tidak boleh terpakai di `clients.subdomain` maupun antrean `client_registrations.subdomain`).
- Subdomain hanya boleh berupa huruf kecil alfanumerik dan tanda hubung minus (`^[a-z0-9-]+$`).
- Status awal tersimpan di `client_registrations` adalah `menunggu_verifikasi`.
- Saat disetujui Owner nantinya, sistem secara otomatis mengirimkan email konfirmasi ke email PIC dan membuatkan/mengaktifkan akun admin.

### Kriteria selesai
Formulir multi-step berfungsi lancar; validasi subdomain realtime bekerja; proteksi rate limit aktif; data tersimpan di tabel `client_registrations`; pengguna diarahkan ke halaman login untuk verifikasi status autentikasi.

## 4.7 — Iak: Pengiriman Admin

**ID:** 7.1-A1  
**Nama halaman:** Pengiriman Admin  
**Route pada sumber:** `/admin/shipments`  
**Layout:** Base Layout Admin.

### Tujuan
Membantu Admin mengemas dan mengirim unit fisik yang dialokasikan untuk pesanan atau menandai barang yang diambil langsung di toko.

### Tabel, filter, dan kolom
- Filter hanya menampilkan pesanan yang perlu diproses untuk pengiriman, sesuai status final di backend.
- ID order dan nama customer.
- Periode/jadwal sewa, terutama tanggal dan jam mulai.
- Ringkasan barang dan jumlah yang harus disiapkan.
- Metode serah-terima: kurir atau ambil sendiri/pickup.
- Badge alokasi unit: belum disiapkan/sudah disiapkan.
- Aksi “Proses Kirim” atau “Serahkan Barang” untuk pickup.

### Popup form pengiriman
- Informasi read-only nama customer dan alamat tujuan.
- Dropdown ekspedisi pada opsi kurir (contoh yang disebut sumber: JNE, GoSend, Kurir Toko).
- Input nomor resi atau link lacak.
- Tombol “Kirim Sekarang”.
- Jika pickup, field ekspedisi/resi disembunyikan atau dinonaktifkan.

### Perilaku dan validasi
- Wajib memastikan unit sudah dialokasikan sebelum pesanan dikirim.
- Nomor resi harus divalidasi sesuai metode; metode pickup tidak membutuhkan resi kurir.
- Simpan status shipment dan order melalui backend; frontend hanya menampilkan hasil respons.
- Catat waktu kirim dan identitas petugas jika didukung skema.

### Kriteria selesai
Filter, popup, kondisi pickup/kurir, validasi resi, pengiriman request, dan status setelah sukses berfungsi.

## 4.8 — Iak: Penerimaan dan Denda Retur Admin

**ID:** 7.1-A2  
**Nama halaman:** Penerimaan/Pemrosesan Pengembalian Admin  
**Route pada sumber:** `/admin/returns`  
**Layout:** Base Layout Admin.

### Tujuan
Menerima barang yang kembali, membandingkan waktu pengembalian dengan tenggat, menghitung preview denda keterlambatan, dan mencatat hasil penerimaan.

### Tabel
- Daftar pesanan yang sedang disewa/menunggu pengembalian menurut status final backend.
- ID order dan nama customer.
- Ringkasan barang.
- Tenggat tanggal/jam akhir sewa.
- Indikator terlambat yang berubah ketika batas waktu telah lewat.
- Tombol “Proses Pengembalian”.

### Popup kalkulator denda
- Tenggat sewa dan waktu aktual penerimaan.
- Ringkasan durasi terlambat dalam jam/hari.
- Preview rumus: jumlah jam terlambat × tarif denda per jam dari produk.
- Field override nominal oleh Admin jika kebijakan bisnis mengizinkan, beserta alasan/catatan perubahan apabila dibutuhkan audit.
- Aksi akhir: tandai barang kembali normal/selesaikan pengembalian atau terbitkan tagihan denda.

### Perilaku dan validasi
- Hitungan frontend hanya preview; hasil final wajib diverifikasi dan ditetapkan backend.
- Nilai denda tidak boleh negatif. Override tidak boleh melewati otorisasi Admin.
- Jangan langsung menandai pesanan selesai jika masih ada tagihan denda atau kasus kerusakan yang belum ditangani, sesuai aturan status final.

### Kriteria selesai
Daftar retur berfungsi, perhitungan preview konsisten, Admin dapat menyelesaikan penerimaan atau menerbitkan tagihan melalui backend, status final dan histori tercatat.

---

# 5. MINGGU 7 BAGIAN 2 — KERANJANG, CHECKOUT, ORDER, DAN PENGEMBALIAN

## 5.1 — Iqo: Keranjang Customer

**ID:** 7.2-I1  
**Nama halaman:** Keranjang  
**Route pada sumber:** `/cart`  
**Layout:** Base Layout Customer.

### Tujuan
Memperlihatkan semua item keranjang dan memisahkannya berdasarkan jadwal sewa. Customer dapat memilih kelompok periode tertentu untuk checkout.

### Susunan UI
1. Header “Keranjang” dan total jumlah item.
2. Kelompok item berdasarkan **tanggal dan jam sewa yang sama**.
3. Checkbox per item dan checkbox per kelompok/select-all yang cakupannya jelas.
4. Kartu item berisi foto, nama, kategori, harga per hari, periode, jumlah, subtotal, indikasi availability, dan tombol hapus.
5. Tombol kuantitas minus/plus dan input angka.
6. Floating bottom bar/ringkasan harga sementara serta tombol checkout.
7. Konfirmasi sebelum menghapus item; empty state jika keranjang kosong.

### Logika yang wajib
- Item dikelompokkan menurut periode start/end yang sama, bukan hanya menurut nama produk.
- Kuantitas minimum 1 dan tidak boleh melebihi unit yang dapat dialokasikan untuk periode itu.
- Checkout hanya aktif ketika terdapat item valid yang dipilih.
- Satu checkout dari cart hanya memproses **satu kelompok periode sewa**; periode berbeda harus menjadi order terpisah, kecuali backend secara resmi mendukung aturan lain.
- Durasi dihitung berdasarkan kelipatan 24 jam yang dibulatkan ke atas, minimum 1 hari, bila inilah aturan kalkulasi yang dipakai di backend.

### Kriteria selesai
Tidak ada total hardcoded; pengelompokan dan pilihan item bekerja; perubahan jumlah/hapus item sinkron dengan API; tombol checkout meneruskan periode, tenant, dan item yang benar.

## 5.2 — Iqo: Checkout Customer

**ID:** 7.2-I2  
**Nama halaman:** Checkout  
**Route pada sumber:** `/checkout`  
**Layout:** Base Layout Customer.

### Tujuan
Memastikan customer meninjau order dan periode, memasukkan alamat, memberikan data identitas/jaminan, lalu membuat pesanan.

### Susunan UI
1. **Stepper/alur checkout** — ringkasan → alamat/periode → identitas/jaminan → konfirmasi.
2. **Ringkasan pesanan** — nama/foto produk, jumlah, tanggal/jam mulai dan selesai, harga satuan, durasi, subtotal per item, total akhir. Biaya layanan hanya tampil jika memang ada aturan resmi.
3. **Alamat** — textarea alamat pengiriman/penjemputan lengkap. Dokumen sumber menyebut kode pos; cek apakah skema backend menyimpan field terpisah sebelum membuat field khusus.
4. **Identitas dan jaminan penyewa** — nama sesuai identitas, nomor identitas/NIK bila kebijakan menyebutnya, upload foto dokumen identitas, upload selfie memegang dokumen, dan alamat identitas jika diperlukan oleh skema.
5. **Persetujuan** — checkbox konfirmasi ketentuan sewa dan tombol “Buat Pesanan & Lanjut Pembayaran”.
6. **Validasi upload** — format, ukuran file, preview, hapus/ganti file, progress upload, dan pesan error.

### Logika dan aturan
- Backend menghitung ulang periode, durasi, total harga, dan ketersediaan; frontend menampilkan preview yang sama.
- Data identitas harus dikirim dengan format request yang disepakati (multipart/form-data apabila mengandung file).
- Bukti pembayaran baru boleh diunggah **setelah status jaminan identitas diverifikasi**, sesuai aturan RentalBase terkini.
- Jaminan pada aturan saat ini adalah **jaminan identitas penyewa**, bukan uang deposit. Jangan menambahkan alur pengembalian uang deposit jika tidak ada persetujuan produk.
- Kegagalan pembuatan order tidak boleh meninggalkan order duplikat atau mengosongkan cart tanpa konfirmasi yang benar.

### Kriteria selesai
Form memiliki validasi, preview file, summary akurat, request order berhasil, dan status lanjutan diambil dari backend.

## 5.3 — Yanuar: Login Gate & Notifikasi Status Verifikasi Tenant

**ID:** 7.2-Y1  
**Nama alur/komponen:** Login Gate & Feedback Status Pendaftaran Tenant  
**Route integrasi:** `/login` (Konteks Autentikasi Admin Tenant)  
**Layout:** Auth Layout.

### Tujuan
Menghilangkan ketergantungan pada halaman terpisah dengan kolom input email. Calon tenant langsung menggunakan portal Login (`/login`) untuk menguji status aktivasi tokonya. Sistem secara cerdas memvalidasi kredensial (email & password), lalu memberikan notifikasi kontekstual mengenai status peninjauan akun toko oleh Owner (belum diverifikasi, ditolak, atau aktif).

### Alur Kerja & Interaksi UI
1. **Percobaan Login Calon Tenant:**
   - Calon tenant memasukkan email PIC dan password yang baru saja didaftarkan di form login.
   - Sistem memverifikasi kecocokan kredensial akun.
2. **Kondisi 1 — Akun Belum Diverifikasi Owner (`menunggu_verifikasi`):**
   - Autentikasi menahan akses masuk ke dashboard.
   - Form login menampilkan **Alert Notifikasi Khusus (Kuning/Biru Info)**:
     - Ikon jam / *pending verification*.
     - Judul: *"Pendaftaran Toko Sedang Ditinjau"*.
     - Pesan deskriptif: *"Akun toko Anda saat ini sedang dalam proses peninjauan oleh tim Owner/Super Admin RentalBase. Anda belum dapat mengakses panel admin sampai pengajuan disetujui. Silakan tunggu notifikasi email atau hubungi tim dukungan jika membutuhkan bantuan segera."*
     - Tombol bantuan cepat: link WhatsApp support atau reload status.
3. **Kondisi 2 — Pendaftaran Ditolak oleh Owner (`ditolak`):**
   - Form login menampilkan **Alert Notifikasi Penolakan (Merah Error)**:
     - Ikon silang / *rejected*.
     - Judul: *"Pengajuan Pendaftaran Toko Ditolak"*.
     - Pesan deskriptif memuat **catatan alasan penolakan resmi** dari Owner yang tersimpan di database: *"Alasan penolakan: [Alasan Penolakan dari Owner]"*.
     - Tombol CTA: *"Daftar Ulang"* (mengarahkan kembali ke `/register-tenant`).
4. **Kondisi 3 — Pendaftaran Telah Disetujui Owner (`disetujui` / `active`):**
   - Kredensial valid dan tenant aktif.
   - Muncul feedback toast sukses singkat: *"Login Berhasil! Mengalihkan ke panel toko..."*
   - Sistem secara otomatis mengarahkan (redirect) ke Dashboard Admin Tenant (`/admin/dashboard`).
5. **Kondisi 4 — Toko Ditangguhkan (`suspended`):**
   - Menampilkan alert peringatan bahwa toko dinonaktifkan sementara oleh Owner, mengarahkan ke halaman kontak bantuan platform.

### Validasi & Keamanan
- Tidak ada form/kolom publik sembarangan untuk menebak-nebak status pendaftaran orang lain tanpa password.
- Pengecekan status pendaftaran hanya ditampilkan apabila pasangan email dan password yang dimasukkan valid/cocok (mencegah enumerasi akun dan kebocoran informasi penolakan).
- Status peninjauan dan alasan penolakan ditarik dinamis dari respons backend (`403 Forbidden` atau respons JSON terstruktur dengan detail status), bukan data statis hardcoded.

### Kriteria Selesai
Calon tenant dapat langsung mengetahui status pendaftaran tokonya hanya dengan mencoba login di `/login`; alert peninjauan (`menunggu_verifikasi`) muncul jelas saat belum disetujui; alert penolakan (`ditolak`) menampilkan alasan spesifik dari Owner; dan akun yang telah disetujui langsung sukses masuk ke dashboard admin tanpa hambatan.

## 5.4 — Yanuar: Detail Pesanan Customer

**ID:** 7.2-Y2  
**Nama halaman:** Detail Pesanan Customer  
**Route pada sumber:** `/my-orders/{id}`  
**Layout:** Base Layout Customer.

### Tujuan
Memberi detail penuh satu order: progres status, pembayaran, identitas, pengiriman, dan tindakan customer yang sedang diizinkan.

### Panel halaman
1. **Stepper status** — Menunggu Konfirmasi → Dikonfirmasi / Menunggu Pembayaran → Pembayaran Terverifikasi → Siap Kirim / Dikirim → Diterima / Sedang Disewa → Dikembalikan → Selesai (atau Dibatalkan/Ditolak), disesuaikan dengan enum status backend final.
2. **Detail order** — ID, item, jumlah, periode sewa (presisi tanggal & jam), alamat pengiriman/ambil toko, subtotal dan total.
3. **Panel identitas** — status pemeriksaan dokumen/jaminan (KTP & selfie); jika ditolak, tampilkan alasan dan opsi unggah ulang.
4. **Panel pembayaran** — instruksi rekening bank transfer atau QRIS aktif dari endpoint payment methods; uploader bukti transfer jika status identitas telah diverifikasi.
5. **Panel pengiriman** — metode serah-terima (`kurir` / `ambil_toko`), nama kurir, nomor resi, tautan pelacakan, dan tombol konfirmasi "Terima Barang" saat barang sampai.
6. **Panel status refund & pembatalan** — jika pesanan dibatalkan atau ditolak setelah customer membayar, tampilkan badge status refund (`tidak_ada`, `menunggu_refund`, `refund_selesai`), catatan refund dari admin, serta preview bukti transfer pengembalian dana (`refund_proof`) saat dana telah dikembalikan.
7. **Aksi customer** — konfirmasi penerimaan barang atau ajukan pengembalian barang hanya bila diizinkan status/backend.

### Validasi
- Customer hanya bisa membuka order miliknya dalam konteks tenant yang relevan.
- Informasi resi, bukti pembayaran, dan bukti refund tidak boleh diambil dari input URL bebas.
- Aksi “Terima Barang” harus mengirim request ke backend dan memperbarui status berdasarkan respons API; jangan hanya mengganti badge di browser.

### Kriteria selesai
Detail lengkap, stepper konsisten, status refund & bukti transfer tampil akurat saat terjadi pembatalan berbayar, upload bukti dan aksi status terhubung API, serta akses order diotorisasi aman.

## 5.5 — Fiza: Login Admin dan Owner

**ID:** 7.2-F1  
**Nama halaman:** Login Admin/Owner  
**Route pada sumber:** `/login` untuk kedua konteks  
**Layout:** Auth Layout, tanpa sidebar/navbar.

### Tujuan
Menyediakan form masuk dengan tampilan konsisten untuk Admin Rental dan Owner tanpa mencampur hak akses keduanya.

### Komponen UI
- Logo platform atau logo tenant sesuai konteks.
- Input email.
- Input password; opsi show/hide jika disetujui.
- Checkbox “Ingat Saya” bila mekanisme autentikasi mendukungnya.
- Tombol “Masuk”.
- Link “Lupa Password?”.
- Pesan error login, status loading, dan pesan akun diblokir/nonaktif bila backend memberikan status tersebut.

### Perilaku
- Setelah login, backend menentukan role dan redirect ke area yang sesuai.
- Admin hanya bisa masuk ke tenant yang menjadi wewenangnya; Owner ke panel pusat.
- UI tidak boleh mengandalkan role dari form yang dikirim pengguna.
- Fiza bertanggung jawab membuat Base Layout Owner agar konsisten di seluruh modul Owner (Dashboard, Registrations, Clients, Subscriptions, Notifications).

### Kriteria selesai
Login tidak mengirim pengguna ke panel yang salah, session/token ditangani dengan benar, logout mengakhiri session sesuai implementasi, dan error tidak membocorkan rincian sensitif.

## 5.6 — Iak: Daftar Pesanan Admin

**ID:** 7.2-A3 (dialihkan dari Iqo / 7.2-I3)  
**Nama halaman:** Master Pesanan Admin  
**Route pada sumber:** `/admin/orders`  
**Layout:** Base Layout Admin.

### Tujuan
Memberi Admin Rental satu daftar order untuk memantau pesanan, mencari customer, memfilter status/periode, dan membuka detail.

### Filter dan tabel
- Filter rentang tanggal dari–sampai.
- Input pencarian nama customer/kode order.
- Filter status sesuai daftar status final backend.
- Kolom ID order, tanggal masuk, customer, periode sewa, total harga, dan badge status.
- Aksi “Lihat Detail”.
- Pagination.
- Export CSV opsional sesuai cakupan yang disetujui.

### Perilaku dan keamanan
- Semua query dibatasi ke client Admin.
- Filter bisa dikombinasikan; jika filter kosong daftar kembali ke default.
- Status harus berasal dari backend, bukan dari label status yang dibuat bebas di frontend.
- Export hanya memuat data yang memang boleh dilihat Admin.

### Kriteria selesai
List/filter/pagination bekerja, tautan detail benar, data tenant terisolasi, export (jika diaktifkan) sesuai filter.

## 5.7 — Iak: Detail Pesanan Admin

**ID:** 7.2-A1 (dialokasikan di Minggu 7.2 / sebelumnya 8.2-A3)  
**Route pada sumber:** `/admin/orders/{id}` (dan Modal Verifikasi di `/admin/orders`)  
**Layout:** Base Layout Admin.

### Tujuan
Admin meninjau satu order, memeriksa identitas/jaminan, memverifikasi bukti pembayaran, mengalokasikan unit fisik, serta menangani pembatalan dan refund bila berlaku.

### Susunan halaman
1. **Header & Aksi Cepat**
   - ID order, tanggal pemesanan, badge status order terkini, dan badge status refund (jika dalam proses refund).
   - Tombol **Konfirmasi Pesanan** (`menunggu_konfirmasi` → `dikonfirmasi`/`menunggu_pembayaran`) dan tombol **Tolak Pesanan** (`menunggu_konfirmasi` → `ditolak` dengan modal input alasan penolakan).
   - Tombol bahaya **Batalkan Pesanan** (membuka popup pembatalan & alur refund manual).
2. **Rincian order** — data kontak customer (nama, email, WhatsApp), periode sewa (presisi tanggal & jam mulai s/d selesai), alamat tujuan / metode ambil di toko, daftar barang, jumlah, tarif sewa, denda per jam, dan total pembayaran.
3. **Panel review identitas & jaminan** — preview gambar dokumen identitas (KTP) dan foto wajah/selfie yang dapat di-zoom; tombol **Verifikasi Identitas** (`diverifikasi`); tombol **Tolak Identitas** (`ditolak`) dengan modal catatan alasan penolakan jaminan.
4. **Panel review bukti pembayaran** — preview bukti transfer / resi QRIS yang diunggah customer; rincian rekening tujuan; tombol **Verifikasi Pembayaran** (`pembayaran_terverifikasi`); tombol **Tolak Pembayaran** (`ditolak`) dengan input alasan.
5. **Panel alokasi unit fisik** — per baris produk pesanan, dropdown pemilihan unit fisik (`equipment_units`) yang berstatus `available` dan bebas konflik jadwal pada periode tersebut; jumlah unit yang dialokasikan harus sama dengan kuantitas yang disewa; ringkasan kode aset / barcode unit terpilih.
6. **Panel logistik (pengiriman/pengembalian)** — link dan status serah-terima barang, nama ekspedisi/kurir, nomor resi, serta riwayat tanggal penyerahan dan penerimaan unit.
7. **Popup pembatalan & manajemen refund** — jika pesanan dibatalkan setelah pembayaran diverifikasi:
   - Input pilihan status refund: `tidak_ada`, `menunggu_refund`, `refund_selesai`.
   - Textarea catatan alasan pembatalan dan detail rekening/e-wallet pengembalian dana customer.
   - Uploader file bukti transfer refund manual (`refund_proof`) sebagai bukti resmi pengembalian dana di luar sistem.
8. **Riwayat aktivitas & audit trail** — linimasa perubahan status pesanan dengan pencatatan timestamp dan pelaku admin.

### Validasi dan aturan
- Admin hanya berhak membuka dan mengubah pesanan pada tenant-nya sendiri (`client_id`).
- Verifikasi identitas merupakan gerbang utama sebelum customer dapat mengunggah bukti pembayaran.
- Alokasi unit wajib memvalidasi jadwal agar tidak terjadi double booking (benturan periode).
- Pembatalan pesanan yang telah dibayar wajib mencatat status refund dan mengunggah bukti transfer pengembalian dana manual jika diselesaikan.
- Transisi status diverifikasi ketat oleh backend sesuai alur resmi.

### Kriteria selesai
Admin dapat mengonfirmasi/menolak pesanan baru, meninjau identitas, memverifikasi pembayaran, mengalokasikan unit fisik tanpa tabrakan jadwal, membatalkan pesanan disertai pencatatan refund dan unggah bukti transfer, serta status terbarui secara real-time.

---

# 6. MINGGU 8 BAGIAN 1 — KATALOG ADMIN, AUTENTIKASI, INSPEKSI FISIK, DAN KERUSAKAN

**Tujuan minggu:** Melengkapi CRUD katalog, autentikasi customer, pemeriksaan kondisi fisik (inspeksi) di sisi Admin, laporan transaksi, serta penyelesaian kasus kerusakan.

## 6.1 — Iqo: Kategori Admin

**ID:** 8.1-I1  
**Route pada sumber:** `/admin/categories`  
**Layout:** Base Layout Admin.

### Tujuan
Admin mengelola kategori yang dipakai untuk mengelompokkan produk tenant.

### Komponen UI
- Judul “Kategori” dan tombol **+ Tambah Kategori Baru**.
- Tabel dengan kolom nama kategori, deskripsi, jumlah produk dalam kategori, dan aksi.
- Input pencarian bila daftar kategori cukup banyak.
- Alert penggunaan limit paket; jika limit tercapai, tampilkan informasi yang jelas dan nonaktifkan aksi tambah.
- Popup/modal Tambah dan Edit berisi nama kategori, deskripsi, tombol simpan, tombol batal.
- Konfirmasi penghapusan dan pesan jika kategori masih digunakan.
- Empty state, loading, success/error feedback.

### Aturan data dan validasi
- Kategori diisolasi berdasarkan client.
- Nama kategori wajib diisi dan tidak boleh duplikat pada ruang lingkup tenant sesuai aturan backend.
- Jangan mengizinkan penghapusan kategori yang masih direferensikan produk kecuali ada kebijakan penggantian/pemindahan kategori.
- Limit paket terkini: Starter 5 kategori, Business 20 kategori, Professional unlimited. Limit harus ditampilkan dan ditegakkan backend.

### Kriteria selesai
CRUD kategori berfungsi, jumlah produk per kategori benar, limit terlihat, serta error duplikat/penghapusan ditangani.

## 6.2 — Iqo: Produk Admin

**ID:** 8.1-I2  
**Route pada sumber:** `/admin/products`  
**Layout:** Base Layout Admin.

### Tujuan
Admin mengelola jenis produk rental beserta deskripsi, media, harga, denda, kategori, syarat penyewaan, dan status tayang.

### Daftar produk
- Thumbnail/cover utama.
- Nama produk.
- Kategori.
- Harga sewa per hari.
- Tarif denda per jam.
- Jumlah unit fisik yang tersedia/total sesuai hasil backend.
- Status aktif/nonaktif.
- Aksi tambah, edit/detail, aktif/nonaktif, dan hapus bila aman dilakukan.
- Pencarian, filter kategori/status, pagination, empty/loading/error state.

### Form tambah/edit produk
1. **Info dasar:** nama, kategori dropdown, deskripsi (teks atau rich text sederhana).
2. **Media:** multi-foto uploader, drag-and-drop bila didukung, preview, set cover utama, urutkan, hapus/ganti foto.
3. **Harga & denda:** harga sewa per hari dan nominal denda per jam dalam format Rupiah; validasi angka nonnegatif.
4. **Persyaratan sewa/jaminan:** textarea untuk syarat yang tampil pada detail/checkout. Contoh lama “wajib deposit Rp1 juta” tidak boleh dianggap aturan final karena RentalBase saat ini menggunakan jaminan identitas, bukan deposit uang.
5. **Status:** toggle aktif/nonaktif.
6. Tombol simpan dan batal; konfirmasi bila keluar dengan perubahan belum disimpan.

### Data, limit dan keamanan
- Kategori terpilih harus berasal dari client yang sama.
- Tenant harus memiliki batas produk: Starter 10 jenis produk, Business 50, Professional unlimited.
- Jumlah unit aktual dikelola dalam `equipment_units`; jangan menggunakan `products.stock` sebagai sumber kebenaran jika skema resmi menggunakan unit fisik.
- Gambar produk disimpan sebagai daftar/array gambar sesuai kontrak backend (`products.image` dalam dokumen skema terkini); jangan memperkenalkan `main_image` jika skema tersebut tidak menyediakannya.
- File harus divalidasi tipe/ukuran. Bila penyimpanan gagal, jangan menganggap gambar tersimpan.

### Kriteria selesai
Produk dapat dibuat/diubah/diaktifkan/nonaktifkan; gambar memiliki cover; kategori, harga, denda dan syarat tersimpan; kuota paket diberlakukan; ketersediaan dihitung dari unit fisik.

## 6.3 — Yanuar: Login/Register Customer

**ID:** 8.1-Y1  
**Route pada sumber:** `/login` dan `/register`  
**Layout:** Base Layout Customer atau Auth Layout yang konsisten dengan tampilan customer.

### Tujuan
Menyediakan satu akun customer global yang dapat digunakan untuk transaksi di beberapa tenant, bukan akun terpisah per toko.

### Tampilan
- Kotak form dengan tab “Masuk” dan “Daftar Baru”.
- Tab Masuk: email, password, checkbox “Ingat Saya” bila didukung, tombol Masuk, link lupa password.
- Tab Daftar: nama lengkap, email, nomor WhatsApp sesuai kebijakan aplikasi, password, konfirmasi password, tombol Daftar.
- Banner informasi bahwa akun dapat digunakan di semua toko RentalBase.
- Loading, validasi field, success message, error login/registrasi, dan error koneksi.

### Perilaku
- Setelah autentikasi berhasil, redirect kembali ke halaman tujuan semula apabila ada (intended redirect), misalnya cart/checkout.
- Customer account bukan akun yang menempel ke satu tenant; data order tetap ditampilkan dalam konteks tenant yang relevan.
- User tidak dapat menetapkan role, status, atau `client_id` miliknya lewat form daftar.

### Kriteria selesai
Login/register terhubung ke backend, error email terdaftar/password benar ditampilkan dengan baik, intended redirect bekerja, session/token tidak hilang saat berpindah halaman yang dilindungi.

## 6.4 — Yanuar: Lupa dan Reset Password

**ID:** 8.1-Y2  
**Route pada sumber:** `/forgot-password` dan route reset yang diperoleh dari tautan email  
**Layout:** Auth Layout; alur berlaku global.

### Layar request reset
- Input email.
- Tombol “Kirim Link Reset”.
- Validasi format email.
- Indikator proses dan pesan untuk memeriksa email.
- Pesan tidak boleh membocorkan apakah email terdaftar jika kebijakan keamanan mengharuskan respons generik.

### Layar reset password
- Token dari link reset yang dikirim sistem.
- Password baru dan konfirmasi password baru.
- Tombol “Simpan Password Baru”.
- Pesan token kedaluwarsa/tidak valid, password tidak memenuhi syarat, sukses, dan request gagal.

### Kriteria selesai
Flow reset memakai mekanisme Laravel/backend resmi, token tidak hardcoded, password baru tersimpan secara aman, dan setelah sukses pengguna mendapat instruksi masuk kembali.

## 6.5 — Iak: Pemeriksaan Kondisi Fisik Admin

**ID:** 8.1-A1 (dialokasikan di Minggu 8.1 / sebelumnya 7.2-A2)  
**Nama halaman:** Condition Checks / Pemeriksaan Kondisi  
**Route pada sumber:** `/admin/condition-checks`  
**Layout:** Base Layout Admin.

### Tujuan
Mendokumentasikan keadaan unit fisik sebelum diserahkan ke customer dan setelah kembali, untuk melacak kerusakan secara dapat diaudit.

### Form pemeriksaan
- Dropdown cari/pilih order ID.
- Dropdown unit fisik yang berasal dari item/order tersebut.
- Pilihan fase: sebelum diserahkan atau setelah dikembalikan.
- Checklist sesuai kategori produk (contoh kamera menyala, lensa bersih, tenda tidak robek); checklist sebaiknya dapat dikonfigurasi atau sesuai tipe item, tidak disamaratakan jika tidak relevan.
- Upload beberapa foto kondisi, sumber menyebut maksimal 5 foto.
- Textarea catatan tambahan/cacat minor.
- Tombol “Simpan Rekaman Fisik”.

### Validasi
- Unit harus benar-benar terkait dengan order.
- Fase pemeriksaan wajib dipilih.
- Validasi file (format/ukuran/jumlah) dan tampilkan preview serta progres.
- Catatan/foto disimpan pada order dan unit yang tepat agar bisa dipakai saat kasus kerusakan.

### Kriteria selesai
Admin dapat menyimpan dua fase inspeksi; record/foto bisa ditinjau kembali; input tidak dapat dikaitkan ke order atau unit yang salah.

## 6.6 — Fiza: Dashboard Owner Pusat

**ID:** 8.1-F1 (dialihkan ke Minggu 8.1 / sebelumnya 8.2-F3)  
**Route pada sumber:** `/owner/dashboard`  
**Layout:** Base Layout Owner yang reusable.

### Tujuan
Menampilkan ringkasan kesehatan platform dan kondisi tenant kepada pemilik platform.

### Komponen UI
- Topbar profil Super Admin.
- Sidebar Owner: Dashboard, Pendaftaran Baru, Client/Tenant, Admin List, Subscriptions, Activity Log, Notifikasi.
- Empat kartu metrik: total tenant terdaftar, tenant aktif, tenant suspended, tenant yang langganannya segera berakhir sesuai definisi rentang waktu.
- Tabel ringkas tenant: nama/subdomain, paket, status. Urutan alfabetis dapat digunakan.
- Loading/error/empty state.

### Aturan
- **Tidak boleh menampilkan detail transaksi atau order customer tenant pada dashboard Owner** menurut spesifikasi sumber.
- Angka kuota dan langganan harus bersumber dari backend.
- Link mengarah ke halaman manajemen Owner yang sesuai.

### Kriteria selesai
Dashboard menampilkan agregat tenant, tidak membocorkan transaksi customer, menu Owner konsisten, dan data status aktual.

## 6.7 — Fiza: Manajemen Client Owner

**ID:** 8.1-F2 (dialihkan ke Minggu 8.1 / sebelumnya 8.2-F1)  
**Route pada sumber:** `/owner/clients`  
**Layout:** Base Layout Owner.

### Tujuan
Memungkinkan Owner mencari, meninjau, memperbarui status, dan mengelola informasi tenant di seluruh platform.

### Tabel
- Client ID.
- Nama usaha.
- Subdomain.
- Paket subscription.
- Tanggal kedaluwarsa/akhir subscription.
- Status client.
- Aksi lihat/edit/suspend/aktifkan.
- Pencarian dan filter status/paket bila diperlukan.

### Form/detail client
- Informasi nama dan deskripsi bisnis, subdomain, paket dan status.
- Edit subdomain (jika benar-benar dibutuhkan) wajib menjalankan validasi unik, pembaruan routing/tenant, dan konfirmasi karena berdampak pada URL. Tidak boleh hanya mengganti string tanpa memperbarui dependensi.
- Tombol Suspend/Aktifkan dengan dialog konfirmasi.
- Saat suspend, minta alasan sesuai kebutuhan audit/notifikasi.

### Aturan
- Hanya Owner yang dapat melakukan aksi lintas tenant.
- Perubahan status harus memicu perilaku middleware/tenant sesuai kontrak backend.
- Jangan mengubah data pelanggan atau order sebagai efek samping dari pengubahan client tanpa aturan yang disetujui.

### Kriteria selesai
Owner dapat mencari, meninjau, mengubah data yang diizinkan, suspend/aktifkan dengan alasan, dan melihat perubahan status secara konsisten.

## 6.8 — Iqo: Laporan Kerusakan Customer

**ID:** 8.1-I4 (dialihkan dari Iak / 8.1-A2)  
**Route pada sumber:** `/my-orders/{id}/damage`  
**Layout:** Base Layout Customer.

### Tujuan
Customer melihat pemberitahuan kasus kerusakan atas barang yang disewa, membaca bukti dan nominal, mengirim tanggapan, dan mengunggah bukti bayar jika menyetujui/menyelesaikan pembayaran.

### Komponen UI
- Banner pemberitahuan kasus kerusakan.
- Informasi read-only: nomor order, item/unit, deskripsi Admin, galeri foto, tanggal laporan, nominal kompensasi yang diminta, status kasus.
- Textarea tanggapan customer dan tombol “Kirim Tanggapan”.
- Panel pembayaran: instruksi rekening/metode yang aktif, uploader bukti bayar kompensasi, tombol “Kirim Bukti”.
- Timeline status atau pesan admin/customer bila tersedia.

### Aturan
- Customer hanya melihat kasus yang terkait dengan order miliknya.
- Tanggapan tidak otomatis berarti kasus dibayar/ditutup; status final berasal dari backend.
- Jangan mengunggah bukti transfer pada kasus yang tidak memiliki tagihan aktif.

### Kriteria selesai
Customer dapat melihat bukti dan nominal, memberi tanggapan, mengunggah bukti pembayaran sesuai kondisi, dan melihat status resolusi.

---

# 7. MINGGU 8 BAGIAN 2 — UNIT FISIK, BRANDING, METODE PEMBAYARAN, OWNER APPROVAL, ADMIN AKUN, RETUR CUSTOMER

## 7.1 — Iqo: Unit Fisik Admin

**ID:** 8.2-I1  
**Route pada sumber:** `/admin/units`  
**Layout:** Base Layout Admin.

### Tujuan
Mengelola unit fisik individual yang menjadi stok nyata rental. Satu jenis produk dapat mempunyai banyak unit dengan kode aset/nomor seri masing-masing.

### Komponen UI
- Kartu/panel kuota unit yang sudah dipakai dibanding limit tenant.
- Filter produk dan status; pencarian kode aset/nomor seri.
- Tabel unit: produk induk, asset code/nomor seri, status, riwayat pemakaian jika tersedia, dan aksi.
- Form Tambah Unit: dropdown jenis produk induk, input kode aset/nomor seri, status awal yang valid.
- Dropdown status inline atau modal edit status.
- Konfirmasi perubahan status yang berdampak pada availability.

### Status unit pada rancangan terkini
`available`, `maintenance`, `damaged`, `lost`, `inactive`. Label Indonesia di UI boleh ditampilkan sebagai Tersedia, Perawatan, Rusak, Hilang, Nonaktif, tetapi nilai API/database jangan diubah tanpa kesepakatan.

### Aturan kuota dan validasi
- Limit total seluruh unit per tenant: Starter maksimum 50 unit; Business maksimum 100 unit; Professional unlimited.
- Asset code unik pada tenant.
- Unit yang sedang dialokasikan/disewa tidak boleh diubah menjadi available secara tidak sah.
- Ketersediaan produk dihitung dari unit fisik serta periode alokasi; bukan dari input stok manual pada produk.

### Kriteria selesai
Tambah/cari/filter/update unit berfungsi; limit kuota benar; status tidak merusak order berjalan; availability di katalog mengacu pada unit fisik.

## 7.2 — Yanuar: Profil dan Branding Admin

**ID:** 8.2-Y3 (dialihkan dari Iqo / 8.2-I2)  
**Route pada sumber:** `/admin/settings/profile`  
**Route saat ini yang pernah terdaftar pada repository:** `/admin/theme` (perlu diselaraskan; lihat Bagian 11).  
**Layout:** Base Layout Admin.

### Tujuan
Mengubah informasi identitas dan tampilan dasar toko yang terlihat customer.

### Komponen UI
1. **Identitas toko** — nama toko/usaha, deskripsi singkat.
2. **Subdomain** — tampil read-only; perubahan URL tidak dilakukan melalui form biasa tanpa aturan dan proses yang disetujui.
3. **Logo** — upload, preview, ganti atau hapus logo bila kebijakan mengizinkan; sumber lama memberi batas contoh 2 MB, finalnya harus sama dengan validasi backend.
4. **Theme color picker** — pilihan warna utama (HEX/RGB) yang mengubah elemen terbatas pada Customer Layout, misalnya aksen tombol/header.
5. **Kondisi paket** — paket Starter menampilkan panel terkunci dan CTA upgrade jika custom color tidak tersedia; Business dan Professional dapat memakai custom color.
6. Tombol simpan, batal, state upload, validasi warna dan gambar.

### Aturan
- Tema warna hanya boleh memengaruhi elemen yang diizinkan dan tidak mengubah layout inti atau menghilangkan kontras/aksesibilitas.
- Starter tidak boleh menyimpan custom color lewat manipulasi request jika fitur hanya tersedia pada paket Business/Professional.
- Nama/logo/deskripsi milik client yang aktif saja.

### Kriteria selesai
Nama, deskripsi, dan logo dapat disimpan; warna sesuai kelayakan paket; preview cocok dengan halaman customer; route dan API selaras.

## 7.3 — Yanuar: Metode Pembayaran Admin

**ID:** 8.2-Y1 (dimajukan dari Minggu 9.1 / sebelumnya 9.1-Y1)  
**Route pada sumber:** `/admin/settings/payments`  
**Layout:** Base Layout Admin.

### Tujuan
Mengatur instruksi pembayaran yang ditampilkan kepada customer saat membayar order.

### Daftar metode pembayaran
- Jenis/nama metode.
- Untuk transfer bank: nama bank, nomor rekening, nama pemilik rekening.
- Untuk QRIS: nama/label QRIS dan preview gambar barcode.
- Status aktif/nonaktif.
- Aksi tambah/edit/nonaktifkan/hapus sesuai aturan backend.
- Empty state jika belum ada metode aktif.

### Form transfer bank
- Pilih jenis bank dari daftar yang disetujui.
- Nomor rekening.
- Nama pemilik rekening.
- Label instruksi bila perlu.
- Toggle aktif.

### Form QRIS
- Label usaha/metode.
- Uploader gambar QRIS.
- Preview gambar dan validasi tipe/ukuran.
- Toggle aktif.

### Perilaku dan aturan
- Rancangan API RentalBase terkini membatasi tipe metode menjadi **`bank_transfer`** dan **`qris`**. Daftar E-Wallet seperti OVO/Gopay yang disebut di sumber lama jangan ditawarkan sebagai jenis mandiri kecuali spesifikasi backend diubah terlebih dahulu.
- Customer melihat metode pembayaran aktif tenant saat ini.
- Perubahan metode tidak boleh mengubah bukti pembayaran order lama tanpa aturan eksplisit.
- Aksi hapus/nonaktif harus mempertimbangkan apakah metode sudah direferensikan transaksi; pilihan nonaktif biasanya lebih aman daripada menghapus sejarah.

### Kriteria selesai
Admin dapat menambah/mengubah/menonaktifkan metode, file QRIS tervalidasi, halaman customer hanya menunjukkan metode aktif, dan API dipakai sesuai kontrak.

## 7.4 — Fiza: Antrean Persetujuan Tenant Owner

**ID:** 8.2-F4 (dimajukan dari 9.1-F2 / dialihkan dari Yanuar / 9.1-Y1)  
**Route pada sumber:** `/owner/registrations`  
**Layout:** Base Layout Owner.

### Tujuan
Memberi tim pusat kemampuan meninjau pengajuan tenant baru (dari Form Pendaftaran Tenant buatan Fiza), memverifikasi data PIC/usaha, menghubungi calon tenant via WhatsApp, dan memproses persetujuan toko.

### Antrean pendaftaran
- Tabel tanggal pengajuan, nama calon usaha, subdomain yang diminta, paket langganan (Starter/Business/Professional), nomor kontak WhatsApp PIC, dan status pengajuan.
- Filter/pencarian status (`menunggu_verifikasi`, `disetujui`, `ditolak`) atau pencarian nama calon usaha.
- Empty state saat tidak ada antrean pengajuan baru.

### Review detail/modal
- Informasi lengkap usaha dan PIC: nama usaha, deskripsi, nama lengkap PIC, email, dan nomor WhatsApp.
- Subdomain yang diajukan dan paket yang dipilih.
- Bukti pembayaran biaya platform/langganan jika tenant mengunggahnya pada saat registrasi.
- **Tombol WhatsApp Direct**: Tautan cepat `https://wa.me/{admin_phone}` dengan template pesan pembuka otomatis untuk verifikasi cepat langsung ke PIC.
- Tombol **Tolak**: membuka modal input alasan penolakan yang akan dicatat di database dan dapat dibaca calon tenant di halaman cek status.
- Tombol **Setujui & Buat Toko**: menampilkan konfirmasi, memicu otomatisasi provisioning database tenant, membuat akun Admin Rental awal, dan mengirim email notifikasi resmi berisi instruksi aktivasi dan kredensial login admin.

### Aturan
- Fitur ini merupakan bagian integral dari panel Owner yang dikelola Fiza dan dimajukan ke Minggu 8.2 agar alur verifikasi calon tenant selaras dengan pengelolaan client di panel Owner.
- Persetujuan wajib memvalidasi kembali keunikan subdomain dan kelengkapan data.
- Semua alur provisioning tenant, pembuatan akun admin, dan pengiriman email berjalan transaksional di backend (DB transaction).
- Jika proses provisioning gagal, rollback dilakukan dan UI menampilkan pesan error teknis yang informatif tanpa mengubah status menjadi `disetujui`.

### Kriteria selesai
Owner dapat meninjau data pengajuan, menghubungi via WhatsApp direct, menolak dengan alasan tercatat, menyetujui pengajuan dengan pembuatan toko & admin otomatis serta trigger email konfirmasi, dan tenant aktif siap diakses via subdomain.

## 7.5 — Fiza: Manajemen Akun Admin Toko (Via Profil Admin)

**ID:** 8.2-F2  
**Route kanonis:** `/admin/admins` (diakses melalui **Dropdown Menu Profil Admin** di topbar/sidebar)  
**Layout:** Base Layout Admin.

### Tujuan
Memungkinkan Admin Rental (pemilik/pengelola toko) untuk membuat akun Admin Rental baru bagi rekan kerja/staf tokonya sendiri serta mengelola daftar akun Admin di dalam tokonya secara mandiri (*self-service*), tanpa harus meminta Owner platform membuatkannya secara manual.

### Alur Akses (Via Menu Profil Admin)
- Di pojok kanan atas Topbar Admin (avatar bulat gelap) dan/atau kartu profil di bagian bawah Sidebar Admin, saat diklik akan memunculkan **Menu Dropdown Profil** berisi:
  1. **Kelola / Buat Akun Admin** (`/admin/admins`) — dengan badge kuota (misal `1/3 Admin`).
  2. **Profil & Tema Toko** (`/admin/theme`).
  3. **Keluar / Logout**.

### Komponen UI Halaman `/admin/admins`
- **Banner / Kartu Indikator Kuota Admin Paket:** menampilkan pemakaian kuota akun Admin aktif saat ini dibandingkan batas maksimal paket langganan toko (contoh: *"Kuota Admin Toko: 1 dari 3 Akun (Paket Business)"* lengkap dengan progress bar).
- **Tabel Daftar Akun Admin Toko (`client_id` milik toko yang sedang login):** nama, email, status akun (`aktif` / `nonaktif`), penanda `"Akun Anda"` pada baris admin yang sedang login, waktu dibuat, dan tombol aksi.
- **Modal / Form "Buat Akun Admin Rental Baru":**
  - Input Nama Lengkap Admin/Staf.
  - Input Email Login.
  - Input Password & Konfirmasi Password (dengan fitur show/hide).
  - Otomatis mengikat akun baru ke `client_id` toko milik Admin yang sedang login dan `role = 'admin_rental'` (tanpa dropdown pilih tenant agar tidak bocor lintas toko).
- **Aksi Nonaktifkan / Aktifkan Akun Rekan Admin:** dapat mengubah status akun rekan admin di toko yang sama (`aktif` ↔ `nonaktif`), dengan proteksi **tidak boleh menonaktifkan akun dirinya sendiri** yang sedang dipakai login.
- Pesan error/peringatan jelas ketika email sudah terpakai atau kuota maksimum Admin pada paket langganan toko sudah penuh.

### Aturan limit & keamanan
- **Starter:** maksimum **1** Admin Rental aktif (tombol *"Buat Akun Admin Baru"* otomatis terkunci/disabled disertai info upgrade paket karena kuota 1/1 sudah dipakai oleh akun utama toko).
- **Business:** maksimum **3** Admin Rental aktif.
- **Professional:** maksimum **10** Admin Rental aktif.
- UI wajib memberi peringatan & menonaktifkan tombol tambah saat kuota penuh, dan **backend wajib menolak request `POST`** yang melampaui limit paket aktif tenant.
- Isolasi multi-tenant ketat: query daftar admin, pembuatan admin baru, dan ubah status wajib selalu difilter dengan `where('client_id', Auth::user()->client_id)` dan `where('role', 'admin_rental')`.

### Kriteria selesai
Admin Rental dapat membuka menu **Kelola / Buat Akun Admin** dari dropdown Profil, melihat sisa kuota admin sesuai paket tokonya, menambahkan akun Admin Rental baru untuk tokonya selama kuota masih tersedia, serta mengelola status aktif rekan admin tanpa bisa mengakses admin milik toko lain.

## 7.6 — Iak: Pengembalian Customer

**ID:** 8.2-A3 (dialihkan dari Minggu 7.2 / sebelumnya 7.2-A1)  
**Nama halaman:** Form Pengembalian Customer  
**Route pada sumber:** `/my-orders/{id}/return`  
**Layout:** Base Layout Customer.

### Tujuan
Membantu customer mengajukan pengembalian barang sewa, memilih opsi kurir atau antar langsung, mengirimkan informasi nomor resi retur, serta menyelesaikan tagihan denda keterlambatan jika ada.

### Komponen UI
- Detail order dan ringkasan barang yang akan dikembalikan.
- Instruksi pengembalian barang berdasarkan kebijakan tenant toko.
- Pilihan metode pengembalian: kirim via kurir/ekspedisi atau dikembalikan langsung ke toko fisik.
- Input nama ekspedisi dan nomor resi retur (wajib diisi bila memilih opsi ekspedisi kurir).
- Panel denda keterlambatan kondisional jika sistem mendeteksi keterlambatan dari jadwal sewa: rincian jam keterlambatan, tarif denda per jam, total nominal denda, serta instruksi nomor rekening/QRIS pembayaran denda toko.
- Uploader bukti pembayaran denda jika terdapat denda keterlambatan yang harus dibayar.
- Tombol “Konfirmasi Pengembalian Barang”.

### Validasi
- Customer hanya dapat mengajukan pengembalian atas order miliknya sendiri (`user_id`) dan pada pesanan yang sedang aktif/dalam masa sewa (`sedang_disewa` / `siap_kembali`).
- Nomor resi diwajibkan untuk metode pengembalian yang menggunakan kurir.
- Tagihan denda harus berasal dari kalkulasi backend presisi per jam; UI tidak boleh menetapkan nominal denda sendiri secara lokal.
- Tampilkan status request setelah submit dan cegah pengajuan retur ganda pada pesanan yang sama.

### Kriteria selesai
Form pengembalian terkirim sukses, data resi dan bukti bayar denda (jika ada) tersimpan ke storage dan database, status order terbarui menjadi menunggu konfirmasi retur di sisi Admin, dan penanganan error tertangani dengan baik.

---

# 8. MINGGU 9 BAGIAN 1 — KASUS KERUSAKAN ADMIN, TENANT SUSPENDED & ERROR UI, LAPORAN TRANSAKSI, SUBSCRIPTION, NOTIFIKASI

## 8.1 — Iqo: Daftar dan Detail Kasus Kerusakan Admin

**ID:** 9.1-I1 (dialihkan dari Minggu 8.1 / sebelumnya 8.1-I3)  
**Route pada sumber:** `/admin/damage-cases`  
**Layout:** Base Layout Admin.

### Tujuan
Mencatat kerusakan setelah barang kembali, mengomunikasikan penilaian kepada customer, dan memantau penyelesaian kompensasi.

### Daftar kasus
- Filter/pencarian order, nama produk/unit, dan status kasus.
- Kolom ID order, nama barang/unit, tanggal laporan, status kasus, nominal kompensasi dan aksi detail.
- Status contoh dalam sumber: menunggu respons, negosiasi, lunas/selesai; label final harus disepakati dengan backend.
- Tombol untuk membuat laporan baru jika kasus dapat dibuat manual oleh Admin.

### Form laporan baru
- Pilih order.
- Pilih produk/unit yang rusak dari barang yang memang terkait dengan order/inspeksi.
- Textarea deskripsi kerusakan.
- Multi-upload 3–5 foto bukti, dengan preview, hapus/ganti, dan validasi tipe/ukuran.
- Input nominal tuntutan ganti rugi dalam Rupiah.
- Tombol simpan/kirim laporan.

### Detail dan resolusi
- Galeri foto, deskripsi, nominal dan status kasus.
- Timeline/percakapan admin-customer jika fitur respons tersedia.
- Ringkasan respons customer dan bukti pembayaran kompensasi.
- Tombol “Konfirmasi Lunas & Tutup Kasus” dengan konfirmasi modal.

### Aturan
- Kasus kerusakan terpisah dari denda terlambat.
- Nilai kompensasi dan status pembayaran disimpan backend.
- Jangan menutup kasus sebelum pembayaran/keputusan penyelesaian sesuai kebijakan.

### Kriteria selesai
Kasus dapat dibuat, dilihat, direspons, pembayaran dapat diverifikasi, bukti disimpan, dan status penutupan tercatat.

## 8.2 — Yanuar: Halaman Tenant Tidak Aktif/Suspended

**ID:** 9.1-Y2 (dialihkan dari Minggu 8.2 / sebelumnya 8.2-Y2)  
**Tipe halaman:** UI global untuk middleware tenant suspend/nonaktif.

### Tujuan
Memblokir akses ke area tenant jika tenant disuspend/nonaktif sesuai respons backend, dan menjelaskan keadaan itu kepada pengunjung.

### UI
- Halaman penuh dengan ikon gembok/toko tutup.
- Judul “Toko Sewa Tidak Aktif”.
- Penjelasan singkat bahwa toko sedang ditangguhkan/tidak aktif.
- Sesuai spesifikasi sumber, halaman ini tidak menyediakan navigasi ke katalog toko yang diblokir. Tombol bantuan hanya boleh ditambahkan jika disetujui.

### Perilaku
- Middleware/backend yang menentukan tenant suspended; UI tidak menggunakan daftar tenant hardcoded.
- Tangani HTTP/status error yang disepakati dan jangan menampilkan layar suspend untuk masalah jaringan umum.
- Halaman hanya memblokir area tenant terkait, bukan otomatis memblokir root RentalBase atau tenant lain.

### Kriteria selesai
Saat backend menandai client `suspended`, halaman tenant menampilkan layar ini; ketika status kembali active, akses normal pulih.

## 8.3 — Yanuar: Global Error 403 dan 404

**ID:** 9.1-Y1 (dialihkan ke Minggu 9.1 / sebelumnya 8.2-Y1)  
**Tipe halaman:** Global Error UI; tidak memakai sidebar/navbar utama.  
**File Target:** `resources/views/errors/403.blade.php`, `resources/views/errors/404.blade.php`

### Tujuan
Menampilkan umpan balik yang mudah dipahami saat route tidak ditemukan atau pengguna tidak punya izin mengaksesnya.

### UI
- Ilustrasi SVG sederhana atau visual standar.
- Headline untuk “404 — Halaman Tidak Ditemukan” atau “403 — Akses Ditolak”.
- Deskripsi singkat sesuai jenis error.
- Tombol “Kembali ke Beranda” yang mengarah ke root/area yang sesuai konteks.
- Tidak menampilkan stack trace, SQL, token, nama kelas internal, atau rincian sensitif.

### Perilaku
- 404 digunakan untuk halaman/resource yang tidak tersedia.
- 403 digunakan saat resource ada tetapi akses ditolak.
- Tombol kembali harus memiliki tujuan aman dan bukan melakukan redirect loop.

### Kriteria selesai
403 dan 404 berbeda dengan benar, responsif, aman, dan mudah dipahami.

## 8.4 — Fiza: Laporan Transaksi Admin

**ID:** 9.1-F2 (dialihkan dari Minggu 8.1 / sebelumnya 8.1-F2)  
**Route pada sumber:** `/admin/reports`  
**Layout:** Base Layout Admin.

### Tujuan
Memberi laporan kinerja transaksi tenant dalam rentang waktu yang dipilih.

### Komponen UI
- Filter Start Date/End Date dan tombol Terapkan Filter.
- Summary cards: jumlah order selesai, pendapatan sewa kotor, pendapatan tambahan (denda dan ganti rugi) sesuai definisi akuntansi yang disepakati.
- Tabel transaksi: tanggal, ID order, customer, biaya sewa, biaya denda, kompensasi kerusakan, grand total.
- Tombol unduh CSV/PDF sesuai kemampuan yang disepakati.
- Loading, data kosong, error, dan indicator filter aktif.

### Aturan
- Perhitungan total harus berasal dari agregasi backend dan dapat diaudit.
- Denda dan kompensasi tidak boleh dihitung ganda pada total.
- Filter tanggal inklusif/eksklusif harus konsisten dengan API dan ditulis jelas.
- Laporan hanya untuk tenant aktif milik Admin.

### Kriteria selesai
Ringkasan dan baris tabel sesuai data transaksi; filter mengubah hasil; download (jika diaktifkan) memakai filter yang sama dan tidak bocor data tenant lain.

## 8.5 — Fiza: Subscription Paket Owner

**ID:** 9.1-F1  
**Route pada sumber:** `/owner/subscriptions`  
**Layout:** Base Layout Owner.

### Tujuan
Memantau masa langganan tenant dan mengelola perpanjangan secara manual bila proses pembayaran dilakukan di luar sistem.

### Tabel
- Nama/subdomain tenant.
- Paket Starter/Business/Professional.
- Tanggal mulai atau tagihan terakhir bila tersedia.
- Tanggal berakhir/jatuh tempo.
- Status subscription.
- Indikator visual untuk tenant mendekati expired atau sudah expired.
- Filter status/paket/tanggal dan link ke detail tenant.

### Form perpanjangan manual
- Pilih tenant dan periode perpanjangan (misalnya satu bulan/satu tahun hanya jika didukung paket bisnis yang disepakati).
- Ringkasan paket dan tanggal akhir saat ini.
- Upload bukti pembayaran manual bila flow ini memang disetujui.
- Tombol konfirmasi perpanjangan.
- Feedback hasil dan tanggal baru setelah backend mengonfirmasi.

### Status dan aturan
- Rancangan terkini menggunakan **subscription**, bukan lisensi berbasis license key, token aktivasi, atau kode aktivasi.
- Status subscription terkini: `active`, `expired`, `suspended`.
- Paket hanya memiliki perbedaan limit yang disepakati: Starter 10 jenis produk/50 unit total/5 kategori/1 Admin/3 bulan; Business 50 jenis produk/100 unit total/20 kategori/3 Admin/6 bulan dan warna tema kustom; Professional unlimited produk/unit/kategori, maksimal 10 Admin, 12 bulan, warna kustom.
- Perpanjangan dan perubahan status harus diproses backend; UI tidak bisa mengaktifkan tenant hanya dengan dropdown lokal.

### Kriteria selesai
Daftar subscription, indikator jatuh tempo, dan perpanjangan manual terhubung API; status dan masa berlaku konsisten dengan tenant/middleware.

## 8.6 — Iak: Notifikasi Customer

**ID:** 9.1-A1 (dialihkan dari Minggu 8.2 / sebelumnya 8.2-A1)  
**Route pada sumber:** `/notifications`  
**Layout:** Base Layout Customer.

### Tujuan
Menampilkan pemberitahuan yang relevan dengan order dan aktivitas customer.

### Komponen UI
- Daftar/feed yang dapat di-scroll.
- Ikon berdasarkan tipe notifikasi (misalnya pengiriman, pembayaran/denda, verifikasi identitas).
- Setiap baris: judul, cuplikan pesan, waktu relatif/timestamp, status sudah/belum dibaca.
- Unread mempunyai penanda visual yang berbeda.
- Aksi “Tandai Semua Dibaca”.
- Klik notifikasi mengarah ke order yang sesuai, bila tersedia.
- Loading, empty, error, dan pagination/load-more jika daftar panjang.

### Aturan
- Hanya notifikasi untuk user yang sedang login.
- Link order divalidasi agar user tidak membuka order milik orang lain.
- Tandai dibaca harus tersimpan di backend, bukan hanya mengubah warna lokal.

### Kriteria selesai
Notifikasi tampil dari API, status baca tersimpan, tautan konteks bekerja, dan tidak ada notifikasi milik pengguna lain.

## 8.7 — Iak: Notifikasi Admin

**ID:** 9.1-A2 (dialihkan dari Minggu 8.2 / sebelumnya 8.2-A2)  
**Route pada sumber:** `/admin/notifications`  
**Layout:** Base Layout Admin.

### Tujuan
Memperlihatkan kejadian yang perlu ditindaklanjuti Admin Rental di tenant-nya.

### Jenis alert dalam sumber
- Booking/order baru.
- Customer mengunggah identitas.
- Customer mengunggah bukti pembayaran.
- Peringatan masa subscription tenant hampir habis.

### Komponen UI dan perilaku
- Feed list seperti notifikasi customer tetapi konteks Admin.
- Ikon/warna yang membedakan jenis alert.
- Judul, deskripsi, waktu, status baca.
- Klik notifikasi mengarah ke halaman tindakan yang sesuai: order detail, review identitas/pembayaran, atau pengaturan subscription bila memang boleh diakses Admin.
- Tandai dibaca satuan/semua jika endpoint menyediakan.

### Aturan
- Data order/identitas/pembayaran hanya dari tenant Admin tersebut.
- Peringatan subscription tidak memberikan Admin akses ke tindakan khusus Owner.
- Jangan mengirim informasi sensitif berlebihan ke isi notifikasi.

### Kriteria selesai
Feed berfungsi, link benar, status baca tersimpan, dan konten terisolasi per tenant.

---

# 9. MINGGU 9 BAGIAN 2 — PROFIL CUSTOMER, INTEGRASI FRONTEND, NOTIFIKASI & COMMANDS OWNER, ACTIVITY LOG

## 9.1 — Iqo: Profil Saya Customer dan Integrasi Frontend

**ID:** 9.2-I1  
**Route pada sumber:** `/profile`  
**Route tenant-scoped yang perlu diselaraskan dengan rancangan RentalBase:** `/client/{subdomain}/profile`  
**Layout:** Base Layout Customer.

### Tujuan
Memberi customer tempat melihat dan mengubah informasi akun dasar serta memperbarui keamanan akun.

### Panel informasi akun
- Foto profil opsional jika memang didukung model/API.
- Nama customer.
- Email akun.
- Tanggal bergabung jika API menyediakan.
- Status akun bila relevan dan aman ditampilkan.

### Form edit data diri
- Nama lengkap.
- Nomor telepon hanya jika field tersebut memang ada/ditambahkan pada model serta API.
- Email ditampilkan read-only jika email berfungsi sebagai identitas login dan perubahan email belum didukung.
- Tombol “Simpan Perubahan”.
- Feedback validasi, sukses, error, dan perubahan belum disimpan.

### Panel keamanan
- Password saat ini, password baru, konfirmasi password baru hanya jika endpoint perubahan password mendukungnya.
- Tombol “Perbarui Password”.
- Bila perubahan password langsung tidak tersedia, gunakan flow reset password resmi daripada mengirim password ke endpoint yang tidak ada.

### Integrasi Iqo (Transaksi, Checkout, dan Order Admin)
- Hapus hardcoded JSON di katalog dan keranjang saat data backend siap.
- Hubungkan kalender agar mengirim `start_date` dan `end_date` dalam format yang disepakati API.
- Pastikan identitas/alamat/foto checkout dikirim sebagai payload yang benar, termasuk multipart/form-data untuk file.
- **Integrasi Lifecycle Order Admin:**
  1. Hubungkan tabel `/admin/orders` dengan filter tanggal, pencarian nama customer/kode order, dan filter status backend.
  2. Implementasikan seluruh aksi pada `/admin/orders/{id}`: konfirmasi/tolak pesanan baru, approval/reject jaminan KTP & selfie, verifikasi bukti pembayaran, alokasi unit fisik individual (`equipment_units`) dengan validasi anti-bentrok jadwal, serta popup pembatalan berbayar (input status refund `orders.refund_status` dan upload bukti transfer refund manual `refund_proof`).
- Selesaikan route profil agar konsisten dengan konteks tenant tanpa mengubah identitas customer menjadi user terpisah per toko.

### Kriteria selesai
Profil memuat data API, edit hanya field yang didukung, keamanan akun memakai endpoint resmi, alur checkout terhubung mulus ke order lifecycle admin, dan alokasi unit fisik terkunci aman di backend.

## 9.2 — Yanuar: Integrasi Auth, Subdomain, Branding & Payments UI

**ID:** 9.2-Y1  
**Jenis pekerjaan:** Integrasi Frontend & Alur Interaksi.

### Validasi subdomain asynchronous
- Form `/register-tenant` (milik Fiza) memanggil endpoint cek ketersediaan subdomain saat pengguna mengetik.
- Terapkan debounce 500 ms agar request efisien dan tidak membebani server.
- Tampilkan visual state: sedang memeriksa (spinner), subdomain tersedia (hijau), sudah dipakai (merah), format tidak valid, dan error koneksi.

### Integrasi autentikasi & middleware
- Pastikan login Admin dan customer menyimpan session/token sesuai mekanisme autentikasi Laravel.
- Verifikasi redirect role: Customer ke area customer (`/client/{subdomain}` atau intended redirect), Admin ke `/admin/dashboard`, Owner ke `/owner/dashboard`.
- Pengunjung yang menekan tombol "Sewa Alat" pada katalog diarahkan login gate dan dikembalikan ke checkout secara mulus (*intended redirect*).
- Hubungkan UI suspended dengan status client `suspended` tanpa memblokir root platform atau tenant lain yang aktif.

### Integrasi Branding & Metode Pembayaran UI
- Hubungkan form branding toko (`/admin/settings/profile` atau `/admin/theme`) dengan preview logo dan live update warna tema toko (CSS variable / Tailwind token).
- Hubungkan form tambah rekening bank dan uploader QRIS (`/admin/settings/payments`) dengan validasi tipe file gambar dan preview barcode.

### Kriteria selesai
Validasi subdomain interaktif, login gate & intended redirect bekerja presisi, session auth stabil, live preview tema toko dan uploader QRIS berfungsi lancar.

## 9.3 — Fiza: Notifikasi Owner, Backend Order API, dan Scheduled Commands

**ID:** 9.2-F1  
**Route pada sumber:** `/owner/notifications`  
**Layout:** Base Layout Owner.

### Tujuan
Memusatkan peringatan operasional tingkat platform untuk Owner, menyediakan endpoint transaksi order yang aman & terkunci, serta mengimplementasikan scheduled console commands background task.

### Komponen UI Notifikasi Owner
- Feed notifikasi platform: pendaftaran tenant baru menunggu persetujuan, upload bukti perpanjangan langganan manual, dan peringatan kuota kapasitas tenant.
- Badge unread, filter tipe alert, tombol "Tandai Semua Dibaca", dan deep link ke halaman penanganan terkait.

### Backend Order API & Data Consistency
- Endpoint transaksional untuk konfirmasi/penolakan order, verifikasi identitas, verifikasi pembayaran, alokasi unit fisik (`equipment_units`), pembatalan pesanan, serta pencatatan `refund_status` dan penyimpanan file `refund_proof`.
- Mengunci data menggunakan database transaction (`DB::transaction`) dan pencegahan benturan jadwal (*double booking lock*).

### 3 Scheduled Console Commands (Background Tasks)
Sesuai PRD §4.14, Fiza mengimplementasikan tiga perintah konsol terjadwal di Laravel Console Kernel / Scheduler:
1. **`orders:auto-cancel-unpaid`** (Setiap 15–30 menit):
   - Membatalkan pesanan secara otomatis yang berstatus `menunggu_pembayaran` jika customer tidak mengunggah bukti pembayaran dalam batas waktu kedaluwarsa (misal 2 jam setelah dikonfirmasi).
   - Mengubah status menjadi `dibatalkan` dan melepaskan alokasi unit fisik agar kembali `available`.
2. **`orders:auto-complete`** (Harian / Tiap malam):
   - Mengubah status pesanan yang telah dikembalikan (`dikembalikan`), inspeksi fisik selesai (`diterima`), dan tidak ada kasus kerusakan atau denda menggantung menjadi `selesai` secara otomatis setelah masa tunggu 24 jam.
3. **`subscriptions:check-expired`** (Harian pukul 00:01):
   - Memeriksa tanggal jatuh tempo (`end_date`) seluruh tenant di platform.
   - Menandai subscription yang telah melewati batas waktu menjadi `expired` dan memicu pengiriman notifikasi peringatan perpanjangan kepada Admin tenant dan Owner.

### 15 In-App Notification Triggers Matrix
Mengimplementasikan pemanggilan event & listener notifikasi in-app untuk 15 kondisi krusial (PRD §4.13):
- **Customer (9 alert):** Order dikonfirmasi, Identitas disetujui, Identitas ditolak, Pembayaran diverifikasi, Pembayaran ditolak, Barang dikirim, Pengembalian diterima, Tagihan denda terbit, Kasus kerusakan diterbitkan/diselesaikan.
- **Admin Rental (4 alert):** Pesanan baru masuk, Bukti identitas baru diunggah, Bukti transfer baru diunggah, Pengajuan pengembalian barang baru diajukan customer.
- **Owner Platform (2 alert):** Pengajuan tenant baru masuk, Bukti perpanjangan subscription tenant diunggah.

### Kriteria selesai
Halaman notifikasi Owner terhubung API, endpoint order aman dari race condition, 3 command artisan terdaftar di scheduler dan berjalan sukses saat diuji, serta seluruh 15 trigger notifikasi memicu record database notification yang tepat.

## 9.4 — Iak: Activity Log Admin

**ID:** 9.2-A1 (dialihkan dari Minggu 9.1 / sebelumnya 9.1-A1)  
**Route pada sumber:** `/admin/activity-logs`  
**Layout:** Base Layout Admin.

### Tujuan
Membantu Admin/tenant menelusuri perubahan data dan aktivitas penting yang dilakukan staf di tokonya.

### Komponen UI
- Filter tanggal dan jam dari–sampai.
- Filter pelaku/admin.
- Tabel read-only berisi waktu aksi, nama pelaku, tipe aksi (badge), deskripsi detail, dan objek/ID yang berubah bila tersedia.
- Contoh aktivitas sumber: memverifikasi identitas, menghapus/mengubah produk, memperbarui pengaturan, atau tindakan order.
- Pagination dan empty/error/loading state.

### Aturan
- Halaman **read-only**: tidak ada tombol edit atau delete untuk log.
- Data hanya untuk tenant Admin terkait.
- Log harus datang dari pencatatan backend. Frontend tidak boleh mengarang audit event sendiri hanya karena tombol diklik.
- Informasi sensitif (misalnya nomor identitas penuh, password, token, file URL privat) tidak boleh dimasukkan mentah ke deskripsi log.

### Kriteria selesai
Filter bekerja, catatan aktivitas urut waktu, data hanya dari tenant aktif, dan log tidak bisa diubah dari UI.

## 9.5 — Iak: Activity Log Owner dan Integrasi Logistik

**ID:** 9.2-A2  
**Route pada sumber:** `/owner/activity-logs`  
**Layout:** Base Layout Owner.

### Tujuan
Memberikan Owner jejak audit lintas platform untuk memantau aktivitas Admin tenant dan Super Admin.

### Komponen UI
- Tabel log menyeluruh.
- Kolom waktu, pelaku, ID/nama tenant, subdomain, tipe aktivitas, deskripsi.
- Filter rentang tanggal/waktu.
- Filter pelaku.
- Filter berdasarkan tenant/subdomain.
- Pagination dan detail log bila dibutuhkan.
- Read-only, tidak menyediakan edit/hapus log.

### Aturan
- Hanya Owner yang dapat melihat log lintas tenant.
- Data log dihasilkan backend dan tidak boleh berubah dari browser.
- Hindari menampilkan credential, token, nomor identitas penuh, atau isi file pribadi pada deskripsi.
- Filter tenant harus membatasi hasil dengan benar tanpa melewati otorisasi.

### Integrasi Iak untuk logistik dan inspeksi
1. **Form resi pengiriman:** hubungkan popup ekspedisi/resi/link tracking dengan endpoint backend; validasi resi sesuai metode kirim.
2. **Kalkulator denda:** frontend menampilkan perkiraan, tetapi backend menghitung/memvalidasi denda final berdasarkan timestamp dan tarif produk. Jangan menyimpan angka yang hanya dihitung di browser sebagai keputusan final.
3. **Uploader foto pemeriksaan fisik:** kirim array file dengan format yang diterima Laravel; pastikan tiap file tetap terhubung ke order, unit, fase pemeriksaan, dan catatan yang tepat.
4. **Feedback request:** tangani loading/error/sukses; cegah duplikasi ketika Admin menekan tombol beberapa kali.

### Kriteria selesai
Owner dapat memfilter jejak aktivitas lintas tenant; Admin dapat mengirim resi, menyelesaikan kalkulasi denda, dan menyimpan foto kondisi via API; semua operasi menghasilkan jejak backend.

---

---

# 10. ATURAN INTEGRASI LINTAS HALAMAN

Bagian ini mengubah daftar tugas per halaman menjadi urutan kerja yang bisa dites dari ujung ke ujung. Endpoint di bawah berasal dari spesifikasi API RentalBase yang telah disusun dalam project context; jika implementasi repository berbeda, samakan kode, `docs/api.md`, route Laravel, dan migrasi sebelum membuat asumsi baru.

## 10.1 Jalur utama customer: katalog sampai selesai rental

1. **Masuk ke tenant** — customer membuka `/client/{subdomain}`. Backend memastikan tenant ditemukan dan berstatus aktif.
2. **Mencari produk** — katalog membaca kategori dan produk tenant. Filter periode meminta availability dari backend.
3. **Melihat detail** — detail menampilkan semua foto, harga, aturan sewa dan tarif denda yang tersimpan.
4. **Mengelola keranjang** — item punya `start_date` dan `end_date`; item dikelompokkan berdasarkan periode sewa yang sama.
5. **Checkout satu kelompok periode** — satu kelompok tanggal/jam menjadi satu order. Produk/periode yang berbeda checkout secara terpisah agar periode order tidak rancu.
6. **Mengirim data identitas/jaminan** — data jaminan dan file dikirim sesuai endpoint terpisah/kontrak backend.
7. **Menunggu review identitas** — order/jaminan menampilkan status dari backend.
8. **Membayar** — setelah identitas diverifikasi, customer memilih metode pembayaran aktif, melakukan transfer/QRIS di luar aplikasi sesuai instruksi, lalu mengunggah bukti.
9. **Menunggu verifikasi pembayaran** — customer melihat status order/pembayaran; tidak mengubah status sendiri.
10. **Pengalokasian unit** — Admin menetapkan unit fisik yang tersedia untuk setiap item dan periode order.
11. **Pengiriman atau pengambilan** — Admin memasukkan metode, kurir/resi atau menyerahkan barang untuk pickup.
12. **Konfirmasi diterima dan masa sewa** — customer melihat progres serta mengonfirmasi penerimaan jika alur ini dipakai.
13. **Pengembalian** — customer mengajukan pengembalian; Admin menerima unit dan melakukan inspeksi kondisi.
14. **Denda/kerusakan bila ada** — denda terlambat dihitung backend; kasus kerusakan dikelola sebagai proses terpisah dengan bukti/tanggapan/pembayaran.
15. **Selesai** — order, pembayaran, shipment, return, inspection dan damage case ditampilkan menurut status final yang diputuskan backend.

## 10.2 Jalur Owner: pendaftaran sampai pengelolaan subscription

1. Calon tenant melihat Landing Page RentalBase dan membuka `/register-tenant`.
2. Form melakukan pengecekan subdomain asynchronous untuk UX, namun submit tetap divalidasi backend.
3. Pengajuan disimpan dengan status `menunggu_verifikasi`. Calon tenant langsung diarahkan ke form Login (`/login`) untuk menguji status aktivasi. Saat mencoba login, jika akun masih dalam peninjauan, sistem menampilkan notifikasi bahwa pengajuan belum disetujui Owner; jika ditolak, sistem menampilkan alasan penolakan.
4. Owner meninjau antrean di `/owner/registrations`, dapat menghubungi PIC via WhatsApp direct (`wa.me`), menolak dengan alasan, atau menyetujui pengajuan.
5. Setelah disetujui, backend memproses provisioning database tenant dan mengaktifkan akun Admin secara otomatis serta mengirimkan email konfirmasi. Calon tenant kini dapat login sukses via `/login` dan langsung diarahkan ke dashboard admin tenant miliknya (`/admin/dashboard`).
6. Owner melihat tenant di `/owner/clients` dan subscription di `/owner/subscriptions`.
7. Status `active`, `expired`, atau `suspended` memengaruhi akses tenant sesuai middleware.
8. Owner mengelola Admin Rental, notifikasi pusat dan activity log lintas tenant.

## 10.3 Matriks endpoint yang perlu disambungkan

Endpoint di tabel ini adalah acuan kontrak integrasi, bukan bukti bahwa setiap endpoint sudah memiliki implementasi final. Verifikasi route aktual sebelum melakukan request dari UI.

| Area | Endpoint/route API acuan | Dipakai oleh | Catatan |
|---|---|---|---|
| Auth | `POST /api/login`, `POST /api/logout`, `GET /api/me` | Login, redirect role, profil sesi | Ikuti mekanisme auth yang dipakai project; jangan memaksakan JWT jika Laravel menggunakan session/Sanctum atau mekanisme lain. |
| Profil | `PUT /api/profile` | Profil customer | Field yang bisa diubah wajib sesuai skema. Jangan mengizinkan customer mengubah role/status/client_id. |
| Client aktif | `GET /api/client` | Header/customer layout, konteks tenant | Backend menentukan tenant dari subdomain/konteks yang valid. |
| Kategori | `GET /api/categories`, `GET /api/categories/{id}` | Filter katalog dan detail kategori | Data dibatasi client aktif. CRUD admin memerlukan endpoint admin yang sesuai. |
| Produk | `GET /api/products`, `GET /api/products/{id}` | Katalog dan detail | Parameter yang didokumentasikan: `category_id`, `search`, `page`, `per_page`. Produk dibatasi client aktif. |
| Availability | `GET /api/products/{id}/availability` | Date picker katalog, detail, cart, checkout | Parameter `tanggal_mulai` dan `tanggal_selesai` pada dokumen API awal; samakan dengan format `start_date`/`end_date` yang digunakan cart/order. |
| Cart | `GET /api/cart`, `POST /api/cart/items`, `DELETE /api/cart/items/{id}` | Keranjang | Jika butuh update quantity/periode, endpoint update harus tersedia dan disepakati; jangan menebak endpoint baru di frontend. |
| Orders | `POST /api/orders`, `GET /api/orders`, `GET /api/orders/{id}` | Checkout, Pesanan Saya, detail order | Backend memeriksa user, client, availability, harga, item, dan status. |
| Bukti pembayaran | `POST /api/orders/{id}/payment` | Checkout/detail order customer | `multipart/form-data` untuk metode dan bukti; validasi ownership, file dan status order. |
| Metode pembayaran | `GET /api/payment-methods` | Detail/checkout order customer | Hanya metode aktif dari tenant saat ini. |
| Admin verifikasi pembayaran | `PATCH /api/admin/payments/{id}/verify` | Detail order Admin | Hanya Admin pada client terkait yang boleh mengakses. |
| Shipment | Endpoint Admin shipment yang diselaraskan di `docs/api.md` | Halaman Pengiriman/Admin dan detail customer | Simpan metode, kurir, nomor resi, status, timestamp; verifikasi bentuk request dan nama kolom aktual. |
| Return | Endpoint customer/admin return yang diselaraskan di `docs/api.md` | Pengajuan pengembalian dan penerimaan Admin | Hitungan keterlambatan final di backend. Pastikan status yang dipakai sama dengan skema. |
| Condition check | Endpoint Admin condition check sesuai `docs/api.md` | Inspeksi kondisi fisik | Menerima order, unit, fase, catatan dan array foto sesuai format backend. |
| Damage case | Endpoint admin damage case dan customer response sesuai `docs/api.md` | Kasus kerusakan Admin/customer | Jangan mencampur kasus kerusakan dengan denda keterlambatan. |
| Payment methods admin | `GET/POST/PUT/DELETE /api/admin/payment-methods` | Metode Pembayaran Admin | Jenis metode yang didukung acuan terkini: `bank_transfer` dan `qris`. |
| Owner clients | `/api/owner/clients` dan endpoint detail/update/status yang disepakati | Daftar/edit/suspend tenant | Otorisasi Owner saja. |
| Owner subscriptions | `/api/owner/licenses` pada dokumen API lama atau endpoint subscription yang sudah diselaraskan | Manajemen subscription | Nama endpoint dalam dokumen API lama perlu direkonsiliasi: produk memakai konsep subscription, bukan license key. Jangan menambah model “license” tanpa keputusan. |
| Notifications | Endpoint customer/admin/owner sesuai kontrak yang tersedia | Tiga halaman notifikasi | Periksa audience, tenant scope, status baca dan deep link sebelum integrasi. |
| Activity logs | Endpoint Admin/Owner yang disepakati | Dua halaman activity log | Read-only dari UI; pencatatan dilakukan backend. |

### 10.4 Standar request dan respons

- Gunakan format JSON untuk request/response teks dan `multipart/form-data` ketika ada file.
- Tangani status HTTP: `200 OK`, `201 Created`, `400 Bad Request` bila dipakai server, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable Entity`, dan `500 Internal Server Error` sesuai makna respons backend.
- Bentuk error validasi harus dibaca dari struktur respons API; tampilkan error pada field yang sesuai, bukan hanya `alert()` generik.
- Setelah aksi yang mengubah data sukses, perbarui data dari respons server atau fetch ulang data yang relevan.
- Jangan menganggap request sukses hanya karena tombol ditekan; jangan menandai status berhasil sebelum server mengonfirmasi.
- Hindari menyimpan credential atau data identitas sensitif pada local storage/browser state tanpa alasan arsitektural dan pengamanan yang disepakati.

## 10.5 Aturan status dan logika bisnis yang tidak boleh didefinisikan ulang oleh UI

Status berikut merupakan acuan RentalBase terkini. Jika ada migration/API lama menggunakan label lain, selesaikan melalui perubahan bersama, bukan dengan menerjemahkan bebas dalam satu halaman.

### Status order
Status order aktif yang membuat unit tetap terikat pada suatu periode:
- `menunggu_konfirmasi`
- `dikonfirmasi`
- `menunggu_pembayaran`
- `pembayaran_terverifikasi`
- `diproses`
- `siap_kirim`
- `dikirim`
- `diterima`
- `sedang_disewa`

Status lain yang diketahui:
- `menunggu_pembayaran_denda`
- `dikembalikan`
- `selesai`
- `ditolak`
- `dibatalkan`

Status di atas adalah acuan RentalBase terkini. Gunakan hanya nilai yang tercantum di sini agar konsisten di seluruh halaman, route, migrasi, controller, dan API.

### Status terkait modul
- Pembayaran: `menunggu_konfirmasi`, `diverifikasi`, `ditolak`.
- Refund: `tidak_ada`, `menunggu_refund`, `refund_selesai`.
- Pengiriman: `menunggu`, `diproses`, `dikirim`, `diterima`, `dibatalkan`.
- Pengembalian: `diajukan`, `diproses`, `dalam_pengembalian`, `diterima`, `selesai`, `dibatalkan`.
- Denda: `tidak_ada`, `menunggu_pembayaran`, `lunas`.
- Jaminan identitas: `menunggu`, `diverifikasi`, `ditolak`.
- Unit fisik: `available`, `maintenance`, `damaged`, `lost`, `inactive`.
- Client: `active`, `suspended`.
- Subscription: `active`, `expired`, `suspended`.

### Rumus dan sumber kebenaran
- Durasi sewa: hitung kelipatan 24 jam yang dibulatkan ke atas, minimum 1 hari, sesuai kontrak perhitungan yang dipakai backend.
- Total sewa: harga sewa per hari × jumlah unit × jumlah hari sewa.
- Availability: dihitung dari unit fisik dan alokasi aktif pada rentang waktu terkait, bukan dari stok hardcoded atau angka yang dimasukkan customer.
- Denda keterlambatan: berdasarkan selisih waktu aktual dan tenggat sewa serta `late_fee_per_hour`; backend menentukan angka final. Admin dapat override hanya jika aturan bisnis memberi izin dan perubahan dicatat.
- Kompensasi kerusakan: disimpan terpisah dari denda keterlambatan.
- Pembayaran: transfer bank atau QRIS dengan bukti yang diverifikasi manual; jangan mengklaim integrasi payment gateway otomatis.
- Refund manual: pembatalan setelah pembayaran mewajibkan pencatatan `orders.refund_status` dan pengunggahan berkas bukti transfer pengembalian dana `refund_proof`.
- Identitas: jaminan berbentuk data/dokumen identitas; tidak ada uang deposit yang otomatis dikembalikan.

---

# 11. PERBEDAAN SPESIFIKASI LAMA DAN RANCANGAN RENTALBASE TERKINI

Bagian ini sengaja dipisahkan agar perubahan tidak dilakukan diam-diam. Enam dokumen mingguan memuat beberapa route, nama field, dan status versi lama. Sebelum coding, tim perlu mengunci keputusan lalu menyamakan dokumen, route, migrasi, controller, frontend, dan API.

| Topik | Yang tertulis pada tugas sumber | Acuan RentalBase yang lebih baru | Tindakan sebelum integrasi |
|---|---|---|---|
| Root `/` | Katalog toko customer dan Landing Page RentalBase sama-sama disebut di root | `/` adalah landing RentalBase; katalog tenant di `/client/{subdomain}` | Jangan mengimplementasikan dua halaman berbeda pada root. Ubah link katalog ke tenant route. |
| Katalog/detail produk | `/` dan `/products/{id}` | `/client/{subdomain}` dan `/client/{subdomain}/products/{product}` | Sinkronkan named route/link, termasuk logo/header/customer layout. |
| Cart | `/cart` | `/client/{subdomain}/cart` | Gunakan tenant-scoped route resmi. |
| Checkout | `/checkout` | Direct checkout yang ada `/client/{subdomain}/checkout/{product}`; checkout hasil cart satu kelompok periode memerlukan route/alur yang diselaraskan | Sepakati route checkout cart, jangan mengarahkan item beda periode dalam satu order secara tidak sengaja. |
| Pesanan customer | `/my-orders` dan `/my-orders/{id}` | `/my-orders` tercatat ada, tetapi customer-facing order harus dibatasi tenant aktif | Pastikan subdomain/context mengikat data riwayat dengan benar, tanpa membuka order tenant lain. |
| Profil customer | `/profile` | Rute yang direncanakan: `/client/{subdomain}/profile`; belum tercatat pada routes/web.php yang ditinjau | Tambahkan/sinkronkan route dan auth middleware sesuai keputusan. |
| Profil/branding Admin | `/admin/settings/profile` | Route yang ada pernah tercatat `/admin/theme` | Pilih satu route kanonis, lalu update menu, test, dokumentasi, dan controller. |
| Metode pembayaran Admin | `/admin/settings/payments` | Endpoint API admin `/api/admin/payment-methods` terdokumentasi; route web perlu diselaraskan | Tentukan route UI dan endpoint; hindari membuat halaman ganda untuk metode pembayaran. |
| Metode pembayaran | Sumber lama menyebut beberapa e-wallet (OVO/Gopay) | API terkini mendukung `bank_transfer` dan `qris` | Jangan menambah kategori metode baru sebelum kontrak API dan model diubah. |
| Produk multi-foto | Sumber menyebut multi-upload dan cover | Skema terkini memakai `products.image` berbentuk daftar/array gambar | Samakan format penyimpanan dan serialisasi; jangan memakai `main_image` kecuali skema dimigrasikan. |
| Stok produk | Sumber menampilkan “stok tersedia” atau input stok | Sumber kebenaran adalah `equipment_units`; tidak ada `products.stock` sebagai sumber stok final | Availability harus dihitung dari unit fisik dan alokasi order. |
| Jaminan checkout | Contoh lama menyebut “deposit KTP/deposit uang” | Model terkini memakai `full_name`, `identity_number`, `identity_document_image`, `face_image`, `identity_address`; tidak ada uang deposit | Ganti copy agar jelas bahwa jaminan identitas berbeda dari deposit uang; jangan buat refund deposit uang. |
| Kode pos | Form sumber menyebut kode pos terpisah | Skema final yang diketahui menggunakan `shipping_address`; field kode pos terpisah belum dipastikan | Jika ingin field tersendiri, sepakati perubahan skema/API dulu. |
| Status order | Sumber lama menyebut `sedang_disewa`, `siap_kirim`, `menunggu_pembayaran_denda` | Status tersebut kini resmi tercantum di Bagian 10.5 dan dipakai backend | Gunakan nilai dari Bagian 10.5 secara konsisten; jangan membuat varian ejaan baru di satu halaman. |
| Denda | Sumber menyarankan frontend kalkulasi otomatis lalu Admin mengirim | Backend menghitung/memvalidasi final, frontend hanya preview; override Admin opsional bila disetujui | Verifikasi controller dan skema agar tidak menyimpan angka dari frontend sebagai keputusan final. |
| License vs subscription | Salah satu label lama memakai License | Rancangan terkini memakai `subscriptions`; tidak ada license key/token aktivasi | Ganti copy/menu/API ke subscription setelah endpoint dikunci. |
| Paket Pro/Professional | Nama paket berganti-ganti di beberapa sumber | Nama final yang diketahui: Starter, Business, Professional | Pilih ejaan final konsisten di UI/database/docs. |
| Login global/tenant | Sumber menyebut login Admin/Owner sama route `/login` | Customer global; Admin tenant; Owner pusat; redirect role dan middleware wajib | Pastikan role/context berasal dari autentikasi backend, bukan parameter URL/form. |
| Cek status pendaftaran tenant | Dokumen awal menyebut form/route terpisah `/register-tenant/status` dengan input email PIC | Alur disederhanakan: calon tenant langsung login di `/login`; jika belum disetujui Owner, muncul notifikasi bahwa akun masih dalam peninjauan; jika ditolak, tampil alasan penolakan | Hapus kebutuhan form mandiri `/register-tenant/status`. Pengecekan status pendaftaran ditangani langsung pada antarmuka autentikasi login admin. |
| Form pendaftaran tenant | Sempat terdapat keraguan kepemilikan antara Fiza dan Yanuar | Form pendaftaran tenant resmi 100% milik Fiza (7.1-F2); antrean persetujuan tenant di panel Owner juga dipegang Fiza (dimajukan ke Minggu 8.2 sebagai 8.2-F4) | Fiza mengimplementasikan formulir pendaftaran multi-step publik dan panel approval Owner; Yanuar fokus mengintegrasikan debounce async cek subdomain di sisi UI. |
| Label anggota ke-4 | Enam file menggunakan “Iak” | Roster formal yang diketahui berisi Zaskia Maulidina Mutiara Hati; hubungan nama panggilan belum dinyatakan di file sumber | Konfirmasi bahwa Iak = Zaskia sebelum mengganti nama atau menyerahkan tugas. |
| Alamat field Shipment/Return | Controller/skema memakai `shipments.status`, `delivery_address`, `shipped_at`, `delivered_at`, dan metode `kurir`/`ambil_toko` | API/database docs sudah disinkronkan ke skema terkini | Gunakan kontrak di API/database docs sebagai acuan final untuk request/response/controller/migration. |

## 11.1 Route Laravel yang perlu dicek sebelum dinyatakan selesai

Berdasarkan audit route sebelumnya, route publik dan tenant yang penting adalah:

- `/` — Landing Page RentalBase.
- `/client/{subdomain}` — Beranda/katalog customer tenant.
- `/client/{subdomain}/products/{product}` — Detail produk.
- `/client/{subdomain}/checkout/{product}` — Checkout produk langsung.
- `/client/{subdomain}/cart` — Keranjang.
- `/my-orders` — Riwayat/pesanan customer, perlu memastikan tenant scoping.
- `/register-tenant`, `/login`, `/register`, `/forgot-password`, `/reset-password/{token}` — flow publik/auth.
- Beberapa route Admin seperti kategori/produk/unit/pengiriman/retur/inspection/damage sudah pernah tercatat, tetapi sebagian route bisa masih berupa placeholder atau belum sesuai dengan kontrak terbaru.
- `/admin/theme` — route tema yang pernah tercatat; perlu diselaraskan dengan spesifikasi `/admin/settings/profile`.
- Halaman `/profile`, `/admin/settings/payments`, `/owner/activity-logs`, `/owner/notifications`, dan route lain yang hanya ada di brief harus diverifikasi/didaftarkan sebelum link di UI dianggap final.

**Checklist audit route:** buka `routes/web.php` dan `routes/api.php`; pastikan route punya controller/action yang benar (bukan sekadar placeholder), middleware role-nya sesuai, named route konsisten, link UI tidak memakai path lama, serta endpoint membaca/mengubah model berdasarkan skema final.

---

# 12. CHECKLIST FINAL PER ANGGOTA

Checklist ini adalah ringkasan hasil yang harus bisa diperagakan pada demo/QA. Jika satu poin belum benar-benar terhubung, tandai belum selesai—jangan hanya mencentang karena tampilan visual sudah jadi.

## 12.1 Iqo / Tersiqo Alfarezel — 10 pekerjaan

- [x] **Katalog customer (7.1-I1):** header/footer reusable, search, filter kategori/harga, periode sewa, grid dan pagination, guest browsing.
- [x] **Detail produk (7.1-I2):** galeri foto multi-image, deskripsi/spesifikasi/syarat jaminan, harga dan denda, quantity selector, direct checkout.
- [x] **Keranjang (7.2-I1):** kelompok berdasarkan periode, hitung subtotal dinamis, pemilihan item, checkout per kelompok tanggal.
- [x] **Checkout (7.2-I2):** ringkasan sewa, alamat, data jaminan KTP & selfie, upload file, validasi ketersediaan unit.
- [x] **Kategori Admin (8.1-I1):** CRUD kategori, jumlah produk, validasi unik per tenant, batas kuota kategori paket.
- [x] **Produk Admin (8.1-I2):** form detail, multi-foto, cover image, harga per hari, tarif denda per jam, syarat jaminan dan toggle aktif.
- [x] **Laporan Kerusakan Customer (8.1-I4):** galeri foto kerusakan, rincian biaya ganti rugi, textarea tanggapan/negosiasi, uploader bukti transfer pembayaran kompensasi (dialihkan dari Iak / 8.1-A2).
- [x] **Unit fisik (8.2-I1):** kuota total unit paket, asset code unik, filter status fisik unit, isolasi jadwal sewa.
- [x] **Kasus Kerusakan Admin (9.1-I1):** pembuatan tiket kerusakan barang, unggah 3–5 foto bukti cacat, klaim nominal ganti rugi, update status unit menjadi rusak/perawatan, verifikasi bukti kompensasi, penutupan kasus (dialihkan ke Minggu 9.1 / sebelumnya 8.1-I3).
- [ ] **Profil customer & integrasi (9.2-I1):** profil via API, pembersihan data dummy katalog/cart, payload multipart file checkout.

## 12.2 Yanuar Alda Baran — 10 pekerjaan

- [x] **Landing Page (7.1-Y1):** Public Layout, Hero CTA "Memulai", showcase fitur SaaS multi-tenant, paket subskripsi (3, 6, 12 bulan), footer legal.
- [x] **Pesanan Saya Customer (7.1-Y2):** tab filter status, order cards, badge status terpadu, deep link ke detail pesanan.
- [x] **Login Gate & Status Verifikasi Tenant (7.2-Y1):** integrasi pengecekan langsung via form login admin, alert toko masih ditinjau Owner (`menunggu_verifikasi`), alert alasan penolakan (`ditolak`), dan auto-redirect ke dashboard saat disetujui.
- [x] **Detail pesanan customer (7.2-Y2):** stepper status lengkap, panel identitas, panel pembayaran, resi pengiriman, panel refund & preview bukti transfer refund.
- [x] **Login & Register Customer (8.1-Y1):** form tab masuk/daftar, akun customer global multi-tenant, validasi input, intended redirect setelah login gate.
- [x] **Lupa & Reset Password (8.1-Y2):** request reset email, token Laravel resmi, validasi password baru, instruksi login kembali.
- [x] **Profil & Branding Admin (8.2-Y3):** form nama/deskripsi/logo toko, live color picker styling tema toko kustom (Business/Pro), named route kanonis (dialihkan dari Iqo / 8.2-I2).
- [x] **Metode Pembayaran Admin (8.2-Y1):** bank transfer & QRIS toko, uploader barcode QRIS, toggle status aktif, validasi minimum 1 metode aktif (dimajukan ke Minggu 8.2).
- [ ] **Halaman Tenant Suspended (9.1-Y2):** layar blokir toko nonaktif, responsif terhadap middleware client suspended, isolasi per tenant (dialihkan dari Minggu 8.2 / sebelumnya 8.2-Y2).
- [ ] **Halaman Error 403 & 404 (9.1-Y1):** visual responsif, pesan jelas tanpa leak informasi sensitif, tombol kembali ke beranda aman (dialihkan ke Minggu 9.1).

## 12.3 Fiza Rahmatus Sholikha — 10 pekerjaan

- [x] **Dashboard Admin (7.1-F1):** Base Layout Admin reusable, 4 kartu metrik, progres kuota paket produk & unit fisik, tabel 5 pesanan terbaru tenant.
- [x] **Form pendaftaran tenant (7.1-F2):** form pendaftaran multi-step publik, pilihan paket, upload logo/dokumen, rate limit `throttle:5,60`, status `menunggu_verifikasi`.
- [x] **Login Admin & Owner (7.2-F1):** Auth Layout, isolasi kredensial Admin (tenant) vs Super Admin (Owner), redirect otomatis berdasarkan role.
- [x] **Dashboard Owner (8.1-F1):** Base Layout Owner reusable, 4 kartu metrik platform (total, aktif, suspended, expiring soon), daftar tenant (dialihkan ke Minggu 8.1 / sebelumnya 8.2-F3).
- [x] **Manajemen client Owner (8.1-F2):** tabel seluruh tenant, edit profil tenant, validasi perubahan subdomain, aksi suspend/aktifkan dengan input alasan (dialihkan ke Minggu 8.1 / sebelumnya 8.2-F1).
- [x] **Antrean persetujuan tenant Owner (8.2-F4):** tabel pengajuan baru, review detail usaha/PIC, tombol WhatsApp direct (`wa.me`), tolak beralasan, persetujuan transaksional & trigger email aktivasi (dimajukan dari Minggu 9.1 ke Minggu 8.2).
- [x] **Manajemen Akun Admin Toko via Profil Admin (8.2-F2):** menu dropdown profil Admin, tabel akun admin internal toko, form buat akun admin rental baru (`client_id` otomatis), dan penegakan kuota admin sesuai paket (Starter 1, Business 3, Pro 10).
- [ ] **Laporan transaksi Admin (9.1-F2):** filter tanggal transaksi, ringkasan pendapatan kotor sewa/denda/ganti rugi, tabel transaksi, export CSV/PDF (dialihkan ke Minggu 9.1 / sebelumnya 8.1-F2).
- [ ] **Subscription Owner (9.1-F1):** masa berlaku langganan, indikator jatuh tempo, form perpanjangan manual, pencatatan histori perpanjangan.
- [ ] **Notifikasi Owner, Backend Order API & Scheduled Commands (9.2-F1):** feed notifikasi pusat, transactional locking order & alokasi unit, 3 scheduled console commands (`orders:auto-cancel-unpaid`, `orders:auto-complete`, `subscriptions:check-expired`), dan 15 in-app notification triggers.

## 12.4 Iak / Anggota ke-4 — 10 pekerjaan

- [x] **Pengiriman Admin (7.1-A1):** daftar order siap kirim, pemilihan metode pengiriman (`kurir`/`ambil_toko`), input nama kurir & nomor resi, update status pesanan.
- [x] **Penerimaan & Denda Retur Admin (7.1-A2):** konfirmasi barang kembali, perhitungan keterlambatan jam presisi, kalkulator tarif denda backend, override denda dengan catatan.
- [x] **Daftar Pesanan Admin (7.2-A3):** filter rentang tanggal sewa, filter status operasional, pencarian order/customer, pagination, isolasi data tenant (dialihkan dari Iqo / 7.2-I3).
- [x] **Detail Pesanan Admin (7.2-A1):** konfirmasi/tolak pesanan baru, verifikasi identitas (KTP/selfie), verifikasi pembayaran bank/QRIS, alokasi unit fisik, pembatalan & upload bukti refund manual (dialokasikan di Minggu 7.2).
- [x] **Pemeriksaan kondisi fisik (8.1-A1):** checklist inspeksi sebelum serah-terima & sesudah pengembalian, multi-upload foto kondisi (maks 5), catatan cacat fisik (dialokasikan di Minggu 8.1).
- [x] **Pengembalian Customer (8.2-A3):** instruksi retur, pilihan ekspedisi/antar langsung, input nomor resi retur, invoice denda terlambat jika ada, submit bukti bayar denda (dialihkan ke Minggu 8.2).
- [ ] **Notifikasi Customer (9.1-A1):** feed notifikasi aktivitas customer, unread badge, filter alert, deep link ke detail order/tiket kerusakan (dialihkan ke Minggu 9.1 / sebelumnya 8.2-A1).
- [ ] **Notifikasi Admin (9.1-A2):** feed alert operasional toko (order baru, unggah KTP, bukti bayar, retur masuk), penanda sudah dibaca, deep link penanganan (dialihkan ke Minggu 9.1 / sebelumnya 8.2-A2).
- [ ] **Activity Log Admin (9.2-A1):** feed audit trail riwayat perubahan data toko, filter waktu & staf admin, format read-only tanpa aksi hapus (dialihkan ke Minggu 9.2 / sebelumnya 9.1-A1).
- [ ] **Activity Log Owner & Integrasi Logistik (9.2-A2):** jejak audit menyeluruh tingkat platform lintas tenant, filter tenant & pelaku, validasi integrasi resi kurir, foto kondisi fisik & kalkulator denda.

---

## 13. DEFINITION OF DONE GLOBAL

Satu halaman baru dinyatakan selesai ketika seluruh poin yang relevan berikut lulus:

1. **Tampilan:** struktur halaman sesuai spesifikasi; layout responsif di desktop dan HP; komponen memakai desain/layout bersama.
2. **Navigasi:** menu, breadcrumb, tombol back, CTA, dan tautan detail menuju route yang benar.
3. **Data:** tidak bergantung pada hardcoded dummy setelah integrasi; data kosong dan error ditangani.
4. **Validasi:** frontend membantu pengguna, backend tetap menegakkan validasi dan aturan bisnis.
5. **Hak akses:** Customer/Admin/Owner hanya melihat atau mengubah resource yang diperbolehkan; tenant isolation diuji dengan mengganti ID/subdomain.
6. **Status:** label dan transisi sesuai enum/API final; tidak ada status tambahan buatan halaman.
7. **File:** tipe/ukuran dicek; ada preview/progress/error; file disimpan dan ditampilkan dari lokasi yang benar dengan otorisasi sesuai.
8. **Request:** loading state tersedia; submit ganda dicegah; pesan sukses hanya sesudah server merespons sukses.
9. **Keamanan:** tidak ada password/token/identitas penuh dalam log UI; tidak mengandalkan tombol tersembunyi untuk otorisasi.
10. **Uji alur:** sedikitnya satu alur sukses dan satu alur gagal diuji; contoh order duplikat, stok/unit tidak tersedia, file salah, session habis, role tidak sesuai, tenant suspended.
11. **Dokumentasi:** route dan endpoint yang benar dicatat ke `docs/api.md`; perubahan skema dicatat di `docs/database.md`; aturan tampilan di `docs/design_system.md` bila perlu.
12. **Repository:** perubahan terintegrasi ke branch yang disepakati, konflik diselesaikan, dan tidak ada file konfigurasi/secret sensitif yang ter-commit.

## 14. URUTAN KERJA YANG DIREKOMENDASIKAN (ROADMAP 6 FASE)

Untuk meminimalkan blocker dan saling tunggu antar-anggota tim, urutan implementasi dibagi menjadi 6 fase ketergantungan:

### Fase 1: Fondasi Platform, Tata Letak (Layouts), dan Autentikasi Dasar
1. **Kunci Kontrak Data:** Selaraskan nama status, route, dan relasi tabel pada `docs/api.md` dan `docs/database.md`.
2. **Bangun 5 Layout Bersama:**
   - Public Layout (Yanuar)
   - Customer Base Layout + Header Reusable (Iqo)
   - Admin Base Layout + Sidebar 5 Grup (Fiza)
   - Owner Base Layout + Sidebar Platform (Fiza)
   - Auth Layout (Yanuar / Fiza)
3. **Halaman Publik Utama:**
   - Landing Page RentalBase (Yanuar: `7.1-Y1`)
   - Form Pendaftaran Tenant Multi-step (Fiza: `7.1-F2`)
   - Login Gate & Status Verifikasi Tenant (Yanuar: `7.2-Y1`)
   - Login & Register Customer Multi-tenant (Yanuar: `8.1-Y1`)
   - Login Admin Tenant & Owner Platform (Fiza: `7.2-F1`)
   - Lupa & Reset Password (Yanuar: `8.1-Y2`)

### Fase 2: Katalog Produk, Kategori, dan Inventaris Unit Fisik
1. **Modul Kategori Admin:** Kategori Admin CRUD & batas limit paket (Iqo: `8.1-I1`).
2. **Modul Produk Admin:** Produk Admin form, multi-foto, tarif denda & syarat jaminan (Iqo: `8.1-I2`).
3. **Modul Unit Fisik Admin:** Input serial number, kode aset unik, status unit (Iqo: `8.2-I1`).
4. **Katalog & Detail Customer:**
   - Katalog Toko Customer dengan Guest Browsing & Login Gate "Sewa Alat" (Iqo: `7.1-I1`).
   - Detail Produk Customer dengan galeri foto & direct checkout (Iqo: `7.1-I2`).
5. **Kalkulasi Dynamic Availability:** Backend endpoint memeriksa jadwal sewa unit fisik vs tanggal sewa yang dipilih customer.

### Fase 3: Transaksi Customer, Checkout, dan Metode Pembayaran
1. **Pengaturan Metode Pembayaran:** Admin mengonfigurasi Bank Transfer & QRIS aktif (Yanuar: `8.2-Y1`).
2. **Profil & Branding Toko:** Konfigurasi logo toko & warna tema kustom tenant (Yanuar: `8.2-Y3`).
3. **Keranjang Belanja:** Cart customer mengelompokkan item berdasarkan rentang periode sewa (Iqo: `7.2-I1`).
4. **Checkout & Form Jaminan:** Customer mengisi alamat kirim/ambil toko, unggah foto KTP & selfie jaminan identitas (Iqo: `7.2-I2`).
5. **Unggah Bukti Bayar:** Customer melihat instruksi transfer/QRIS tenant dan mengunggah struk pembayaran.
6. **Riwayat Pesanan Customer:** Halaman Pesanan Saya Customer dengan tab status (Yanuar: `7.1-Y2`).

### Fase 4: Operasional Admin, Verifikasi Order, Logistik, dan Retur
1. **Manajemen Order Admin:**
   - Daftar Pesanan Admin dengan filter tanggal & status (Iak: `7.2-A3`).
   - Detail Pesanan Admin: Konfirmasi/tolak booking, verifikasi KTP, verifikasi bukti bayar, alokasi unit fisik, pembatalan pesanan & pencatatan refund manual (Iak: `7.2-A1`).
2. **Detail Pesanan Customer:** Pelacakan progres status, panel identitas, pengiriman, dan panel status refund (Yanuar: `7.2-Y2`).
3. **Logistik Pengiriman:** Admin memasukkan metode ekspedisi/pickup dan nomor resi kurir (Iak: `7.1-A1`).
4. **Pemeriksaan Fisik Unit:** Dokumen inspeksi foto unit fisik sebelum serah-terima dan setelah pengembalian (Iak: `8.1-A1`).
5. **Pengembalian Barang & Denda:** Customer mengajukan retur (Iak: `8.2-A3`), Admin memverifikasi unit kembali & menghitung denda keterlambatan (Iak: `7.1-A2`).
6. **Kasus Kerusakan:** Admin membuat tiket ganti rugi kerusakan fisik (Iqo: `9.1-I1`), Customer meninjau & mengunggah bukti bayar kompensasi (Iqo: `8.1-I4`).

### Fase 5: Panel Owner Platform, Manajemen Tenant, dan Manajemen Staf Admin Toko
1. **Dashboard Owner Pusat:** Pemantauan kesehatan platform & 4 kartu metrik agregat (Fiza: `8.1-F1`).
2. **Antrean Persetujuan Tenant:** Owner meninjau pengajuan toko baru, kontak WhatsApp direct (`wa.me`), tolak atau approve dengan otomatisasi provisioning tenant & akun Admin utama (Fiza: `8.2-F4`).
3. **Manajemen Client Owner & Manajemen Akun Admin Toko:** Pengelolaan status/suspend tenant oleh Owner (Fiza: `8.1-F2`) serta fitur *self-service* pembuatan/pengelolaan akun Admin tambahan oleh Admin Rental melalui menu Profil sesuai kuota paket (Fiza: `8.2-F2`).
4. **Penanganan Tenant Suspended:** Halaman Error 403/404 (Yanuar: `9.1-Y1`) dan halaman Tenant Suspended via middleware (Yanuar: `9.1-Y2`).
5. **Subscription & Siklus Tagihan:** Pelacakan masa aktif langganan tenant & form perpanjangan manual (Fiza: `9.1-F1`).
6. **Laporan Transaksi Admin:** Filter tanggal, ringkasan pendapatan kotor sewa/denda/ganti rugi, dan export laporan (Fiza: `9.1-F2`).

### Fase 6: Background Tasks, Notifikasi, Audit Trail, dan Final QA
1. **Scheduled Console Commands Laravel:** Implementasi 3 background worker cron scheduler untuk auto-cancel pesanan belum dibayar, auto-complete pesanan retur, dan check-expired langganan tenant (Fiza: `9.2-F1`).
2. **Matriks Notifikasi In-App:** Pemanggilan 15 trigger alert untuk Customer, Admin, dan Owner (Fiza: `9.2-F1`, Iak: `9.1-A1` & `9.1-A2`).
3. **Audit Trail & Activity Logs:** Pencatatan log aktivitas Admin toko (Iak: `9.2-A1`) dan Owner platform lintas tenant (Iak: `9.2-A2`).
4. **Profil Akun & Integrasi Akhir:** Halaman profil customer (Iqo: `9.2-I1`), pembersihan seluruh dummy JSON, dan sinkronisasi rute kanonis.
5. **End-to-End Testing & Bug Fixing:** Uji coba skenario lengkap dari registrasi tenant → katalog → sewa alat → verifikasi KTP/bayar → kirim → retur → denda/kerusakan → laporan transaksi.

---

## 15. SUMBER PENYUSUNAN DAN BATASAN DOKUMEN

Dokumen ini disusun dengan menggabungkan enam sumber tugas yang diberikan:

- `minggu_7_1.md`
- `minggu_7_2.md`
- `minggu_8_1.md`
- `minggu_8_2.md`
- `minggu_9_1.md`
- `minggu_9_2.md`

**Cara membaca:** Detail halaman pada Bagian 4–9 mempertahankan maksud fitur pada dokumen sumber sambil mengembangkan tujuan, susunan UI, field, validasi, interaksi, dan kriteria selesai. Bagian 10–14 adalah pengembangan spesifikasi agar tugas dapat dipakai untuk integrasi dan QA. Jika ditemukan konflik dengan kode/API/database aktual, gunakan keputusan teknis project yang disepakati tim dan catat perubahannya—jangan menganggap semua route, status, atau endpoint pada brief lama sudah tersedia/benar.

**Target hasil akhir:** Satu acuan bersama untuk empat anggota, mencakup seluruh pembagian Minggu 7 Bagian 1 sampai Minggu 9 Bagian 2 dan seluruh 40 pekerjaan/halaman/alur pada enam file tugas.
