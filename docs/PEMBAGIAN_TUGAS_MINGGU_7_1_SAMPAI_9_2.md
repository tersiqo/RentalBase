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
6. Minggu 8 Bagian 1 — Katalog admin, autentikasi, transaksi, dan kerusakan
7. Minggu 8 Bagian 2 — Unit, branding, Owner, dan notifikasi
8. Minggu 9 Bagian 1 — Pembayaran, persetujuan tenant, subscription, activity log
9. Minggu 9 Bagian 2 — Profil, notifikasi Owner, activity log Owner, dan integrasi
10. Aturan integrasi lintas halaman
11. Perbedaan spesifikasi lama dengan rancangan RentalBase terkini yang harus disinkronkan
12. Checklist final per anggota

---

## 1. IDENTITAS TIM DAN PEMBAGIAN TANGGUNG JAWAB

Nama/label penanggung jawab ditulis mengikuti enam dokumen tugas sumber. Nama panggilan di dokumen sumber tidak selalu disertai nama lengkap.

| Label pada dokumen tugas | Nama formal yang diketahui dari roster tim | Peran umum | Lingkup tugas dalam dokumen sumber |
|---|---|---|---|
| **Iqo** | **Tersiqo Alfarezel** | Project Manager | Katalog dan detail produk customer, keranjang, checkout, kategori/produk admin, unit fisik, branding toko, metode pembayaran, profil customer, sebagian integrasi katalog dan checkout. |
| **Yanuar** | **Yanuar Alda Baran** | Front End Developer | Landing page publik, halaman pesanan customer, status pendaftaran tenant, autentikasi, reset password, halaman error dan tenant suspend, dashboard Owner, persetujuan tenant, integrasi autentikasi. |
| **Fiza** | **Fiza Rahmatus Sholikha** | Back End Developer | Dashboard Admin, pendaftaran tenant pada dokumen Minggu 7 Bagian 1, login Admin/Owner pada dokumen Minggu 7 Bagian 2, master order, review identitas/pembayaran, laporan, manajemen client/admin Owner, subscription, notifikasi Owner, integrasi order dan alokasi unit. |
| **Iak** | Nama formal tidak tercantum di enam dokumen sumber; cocokkan dengan roster resmi tim sebelum penyerahan | Anggota ke-4 pada dokumen sumber | Pengiriman, retur, inspeksi kondisi fisik, kasus kerusakan, notifikasi customer/Admin, activity log Admin/Owner, integrasi logistik dan foto kondisi. |

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

1. **Pengunjung publik** — melihat landing page RentalBase, informasi produk SaaS, paket, fitur, dan formulir pendaftaran tenant.
2. **Customer** — melihat katalog pada toko/tenant tertentu, memilih periode sewa, memasukkan barang ke keranjang, checkout, mengirim identitas/jaminan, mengunggah bukti pembayaran, melihat status pesanan, pengiriman, pengembalian, notifikasi, dan profil.
3. **Admin Rental** — mengelola data satu toko/tenant: pesanan, kategori, produk, unit fisik, pengiriman, retur, pemeriksaan fisik, kasus kerusakan, metode pembayaran, laporan, branding, notifikasi, dan activity log.
4. **Owner/Super Admin** — mengelola platform dan tenant: pengajuan tenant, client, admin rental lintas tenant, subscription, dashboard, notifikasi Owner, dan activity log tingkat platform. Panel Owner tidak menampilkan detail transaksi customer lintas tenant sebagai data operasional.

### 2.2 Layout dasar yang dibutuhkan

| Layout | Dipakai oleh | Komponen minimum |
|---|---|---|
| **Public Layout** | Landing page, pendaftaran tenant, cek status pendaftaran, halaman publik lainnya | Navbar RentalBase, menu Fitur/Harga/Kontak, CTA Login/Daftar, footer dan tautan legal. |
| **Customer Layout** | Katalog toko, detail produk, cart, checkout, pesanan, retur customer, laporan kerusakan customer, notifikasi, profil | Header toko, logo/nama toko, navigasi Beranda/Produk/Cara Sewa, keranjang dengan badge, menu login/profil, footer kontak/jam operasional/kebijakan. |
| **Admin Layout** | Dashboard Admin dan seluruh halaman pengelolaan tenant | Sidebar Admin, topbar nama toko, menu profil/logout, area konten, breadcrumb/judul halaman, flash message/alert, responsif pada HP. |
| **Owner Layout** | Dashboard dan halaman manajemen pusat | Sidebar Owner, topbar profil Super Admin, navigasi dashboard/pendaftaran/client/admin/subscription/activity log/notifikasi, konten terpisah dari panel tenant. |
| **Auth Layout** | Login, register, lupa/reset password | Form terpusat tanpa sidebar; validasi per-field, show/hide password bila tersedia, pesan error dan loading. |
| **Global Error/Suspended Layout** | 403, 404, tenant suspend/nonaktif | Tampilan fokus pada pesan dan aksi yang sesuai, tidak menampilkan menu yang justru dilarang pada halaman suspended. |

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

Peta berikut mempertahankan kelompok tanggung jawab dari enam dokumen sumber. Total yang dihitung sebagai halaman/alur halaman adalah 40 (10 per anggota). Integrasi khusus pada Minggu 9 Bagian 2 dicatat terpisah sebagai pekerjaan integrasi, bukan halaman baru. Bentuk final route/backend harus dicek pada bagian 11.

| ID | Minggu | Halaman / pekerjaan | Penanggung jawab pada sumber |
|---|---|---|---|
| 7.1-I1 | 7 Bagian 1 | Katalog Toko Customer | Iqo |
| 7.1-I2 | 7 Bagian 1 | Detail Produk Customer | Iqo |
| 7.1-Y1 | 7 Bagian 1 | Landing Page RentalBase | Yanuar |
| 7.1-Y2 | 7 Bagian 1 | Pesanan Saya Customer | Yanuar |
| 7.1-F1 | 7 Bagian 1 | Dashboard Admin | Fiza |
| 7.1-F2 | 7 Bagian 1 | Form Pendaftaran Tenant | Fiza pada dokumen sumber |
| 7.1-A1 | 7 Bagian 1 | Pengiriman Admin | Iak |
| 7.1-A2 | 7 Bagian 1 | Penerimaan dan Denda Retur Admin | Iak |
| 7.2-I1 | 7 Bagian 2 | Keranjang Customer | Iqo |
| 7.2-I2 | 7 Bagian 2 | Checkout Customer | Iqo |
| 7.2-Y1 | 7 Bagian 2 | Cek Status Pendaftaran Tenant | Yanuar |
| 7.2-Y2 | 7 Bagian 2 | Detail Pesanan Customer | Yanuar |
| 7.2-F1 | 7 Bagian 2 | Login Admin dan Owner | Fiza |
| 7.2-F2 | 7 Bagian 2 | Daftar Pesanan Admin | Fiza |
| 7.2-A1 | 7 Bagian 2 | Pengembalian Customer | Iak |
| 7.2-A2 | 7 Bagian 2 | Pemeriksaan Kondisi Fisik Admin | Iak |
| 8.1-I1 | 8 Bagian 1 | Kategori Admin | Iqo |
| 8.1-I2 | 8 Bagian 1 | Produk Admin | Iqo |
| 8.1-Y1 | 8 Bagian 1 | Login/Register Customer | Yanuar |
| 8.1-Y2 | 8 Bagian 1 | Lupa/Reset Password | Yanuar |
| 8.1-F1 | 8 Bagian 1 | Detail Pesanan Admin | Fiza |
| 8.1-F2 | 8 Bagian 1 | Laporan Transaksi Admin | Fiza |
| 8.1-A1 | 8 Bagian 1 | Daftar dan Detail Kasus Kerusakan Admin | Iak |
| 8.1-A2 | 8 Bagian 1 | Laporan Kerusakan Customer | Iak |
| 8.2-I1 | 8 Bagian 2 | Unit Fisik Admin | Iqo |
| 8.2-I2 | 8 Bagian 2 | Profil/Branding Admin | Iqo |
| 8.2-Y1 | 8 Bagian 2 | Error 403 dan 404 | Yanuar |
| 8.2-Y2 | 8 Bagian 2 | Halaman Tenant Tidak Aktif/Suspended | Yanuar |
| 8.2-Y3 | 8 Bagian 2 | Dashboard Owner | Yanuar |
| 8.2-F1 | 8 Bagian 2 | Manajemen Client Owner | Fiza |
| 8.2-F2 | 8 Bagian 2 | Akun Admin Rental Owner | Fiza |
| 8.2-A1 | 8 Bagian 2 | Notifikasi Customer | Iak |
| 8.2-A2 | 8 Bagian 2 | Notifikasi Admin | Iak |
| 9.1-I1 | 9 Bagian 1 | Metode Pembayaran Admin | Iqo |
| 9.1-Y1 | 9 Bagian 1 | Antrean Persetujuan Tenant Owner | Yanuar |
| 9.1-F1 | 9 Bagian 1 | Subscription Tenant Owner | Fiza |
| 9.1-A1 | 9 Bagian 1 | Activity Log Admin | Iak |
| 9.2-I1 | 9 Bagian 2 | Profil Customer dan Integrasi Katalog/Checkout | Iqo |
| 9.2-F1 | 9 Bagian 2 | Notifikasi Owner dan Integrasi Order | Fiza |
| 9.2-A1 | 9 Bagian 2 | Activity Log Owner dan Integrasi Logistik | Iak |

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
1. **Header toko**
   - Logo dan nama toko.
   - Menu Beranda, Produk, Cara Sewa.
   - Ikon keranjang dengan badge jumlah item.
   - Tombol Login atau Profil sesuai status autentikasi.
   - Header harus konsisten dipakai di detail produk, keranjang, checkout, pesanan, notifikasi, dan profil.
2. **Area pencarian/filter**
   - Input pencarian nama produk.
   - Filter kategori melalui checkbox (kategori sesuai data tenant, bukan daftar hardcoded permanen).
   - Filter rentang harga minimum–maksimum.
   - Tombol reset filter bila filter diterapkan.
3. **Pemilih tanggal sewa**
   - Tanggal mulai dan tanggal selesai.
   - Validasi bahwa tanggal selesai tidak lebih awal daripada tanggal mulai.
   - Saat periode berubah, ketersediaan produk diperbarui berdasarkan periode itu.
4. **Grid kartu produk**
   - Foto/cover produk.
   - Nama produk dan kategori.
   - Harga sewa per hari.
   - Badge ketersediaan yang jelas.
   - Tombol Lihat Detail.
   - Pagination di bagian bawah.
5. **Footer toko**
   - Kontak toko dan tautan media sosial jika ada.
   - Jam operasional.
   - Kebijakan pengembalian/retur dan informasi bantuan.

### Data dan perilaku
- Katalog dan kategori harus milik tenant yang sedang dibuka.
- Search, kategori, harga, pagination, dan periode sewa harus bekerja bersama; perubahan filter tidak boleh menghapus filter lain secara tidak sengaja.
- Jika tidak ada produk sesuai filter, tampilkan empty state beserta aksi membersihkan filter.
- Jika request gagal, tampilkan pesan dan aksi muat ulang; jangan menampilkan seolah-olah stok tersedia.

### Kriteria selesai
Header/footer reusable tersedia; filter/search berjalan; kartu menggunakan data produk; tanggal sewa memengaruhi availability; tautan detail membuka produk yang dipilih; layout responsif.

## 4.2 — Iqo: Detail Produk Customer

**ID:** 7.1-I2  
**Nama halaman:** Detail Produk  
**Route pada sumber:** `/products/{id}`  
**Layout:** Base Layout Customer.

### Tujuan
Menampilkan informasi lengkap satu jenis produk agar customer dapat memeriksa foto, harga, spesifikasi, aturan sewa, dan memilih jumlah sebelum menambahkan ke keranjang.

### Bagian dan komponen UI
1. **Galeri foto di sisi kiri/atas pada mobile**
   - Foto utama besar.
   - Carousel/slider untuk foto lainnya.
   - Thumbnail yang dapat diklik untuk mengganti foto utama.
2. **Informasi inti di sisi kanan/berikutnya pada mobile**
   - Nama produk.
   - Kategori.
   - Harga sewa per hari.
   - Tarif denda keterlambatan per jam.
   - Ringkasan ketersediaan untuk periode yang dipilih.
   - Kontrol jumlah: tombol minus, angka, tombol plus; jumlah minimum 1.
   - Tombol utama **Tambah ke Keranjang**.
3. **Tab informasi di bawah**
   - **Deskripsi:** penjelasan barang dan manfaatnya.
   - **Spesifikasi teknis:** informasi sesuai produk.
   - **Syarat penyewaan/jaminan:** teks aturan khusus dari pemilik toko.

### Perilaku dan validasi
- Quantity tidak boleh kurang dari 1 dan harus dibatasi oleh ketersediaan yang diperoleh dari backend.
- Jika periode belum dipilih, arahkan customer memilih periode sebelum memastikan ketersediaan.
- Tambah ke keranjang memberi feedback berhasil dan mempertahankan tenant serta periode sewa.
- Jangan mengasumsikan bahwa frasa “deposit” pada contoh lama berarti deposit uang. Aturan RentalBase terkini menggunakan **identitas/jaminan penyewa**, bukan uang deposit; samakan teks persyaratan dengan ketentuan bisnis yang disetujui.

### Kriteria selesai
Galeri berfungsi, tab dapat berpindah, harga/denda berasal dari data produk, jumlah tervalidasi, tambah ke keranjang memakai data yang dipilih.

## 4.3 — Yanuar: Landing Page Utama RentalBase

**ID:** 7.1-Y1  
**Nama halaman:** Landing Page SaaS RentalBase  
**Route pada sumber:** `/`  
**Layout:** Base Layout Public; Yanuar bertanggung jawab membuat navbar dan footer publik.

### Tujuan
Memperkenalkan RentalBase kepada calon pelanggan bisnis rental dan mengarahkan mereka untuk mendaftar, melihat fitur, atau mengecek paket layanan.

### Bagian dan komponen UI
1. **Navbar publik** — logo RentalBase; menu Fitur, Harga, Kontak; tombol Login Admin/Owner.
2. **Hero section** — headline utama, deskripsi singkat manfaat, CTA “Mulai Buka Toko Gratis”.
3. **Section fitur** — grid ikon dan keterangan fitur inti, contoh katalog, pengelolaan rental, order, unit fisik, pembayaran, retur, dan laporan sesuai fitur yang benar-benar disediakan.
4. **Section harga/paket** — kartu Starter, Business, Professional/Pro; tiap kartu menampilkan harga aktual, batasan paket, dan CTA memilih paket. Angka kuota tidak boleh berupa angka yang berbeda antara landing dan backend.
5. **Footer publik** — link Terms/Privacy, kontak pengelola/developer, dan informasi umum.

### Perilaku dan validasi
- CTA pendaftaran diarahkan ke alur pendaftaran tenant.
- Tombol login membawa pengguna ke halaman login sesuai peran/konteks.
- Tautan legal dan kontak harus memiliki tujuan valid.
- Jangan menjanjikan fitur yang belum didukung backend.

### Kriteria selesai
Navbar/footernya reusable; CTA benar; informasi paket konsisten dengan aturan paket; desain responsif dan tidak memakai data dummy setelah integrasi.

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
**Layout:** Base Layout Admin; Fiza membuat sidebar Admin dan topbar profil dasar.

### Tujuan
Memberi gambaran operasional ringkas untuk Admin Rental atas pesanan, verifikasi, rental aktif, pendapatan, dan penggunaan kuota paket.

### Navigasi dasar sidebar
Dashboard, Pesanan, Pengiriman, Pengembalian, Inspeksi Fisik, Kasus Kerusakan, Kategori, Produk, Unit, Laporan. Menu Pengaturan/Metode Pembayaran/Notifikasi dapat ditambahkan sesuai route yang telah disetujui.

### Komponen utama
1. **Topbar** — tombol hamburger untuk buka/tutup sidebar; nama toko; dropdown profil dengan setelan dan logout.
2. **Empat kartu metrik** — booking baru; menunggu verifikasi; rental aktif; pendapatan bulan berjalan. Definisi angka harus sama dengan query backend.
3. **Indikator kuota** — progress bar penggunaan kuota produk/unit sesuai paket tenant.
4. **Lima pesanan terbaru** — tabel/kartu ringkas berisi nomor order, customer, waktu, status dan tautan ke detail.
5. **State UI** — loading, data kosong, error pemuatan, dan tampilan mobile.

### Perilaku
- Semua metrik dan daftar hanya dari `client_id` Admin yang sedang masuk.
- Jangan menampilkan transaksi tenant lain atau metrik Owner di dashboard Admin.
- Sidebar menjadi komponen reusable untuk semua halaman Admin.

### Kriteria selesai
Layout Admin reusable; seluruh metrik berasal dari sumber data nyata saat integrasi; menu mengarah ke route valid; dashboard tidak mengandalkan angka hardcoded.

## 4.6 — Fiza pada sumber: Form Pendaftaran Tenant

**ID:** 7.1-F2  
**Nama halaman:** Pendaftaran Tenant  
**Route pada sumber:** `/register-tenant`  
**Layout:** Public Layout.

> Catatan asal: tugas ini ditempatkan dalam bagian Fiza di file Minggu 7 Bagian 1, meskipun bagian Yanuar juga diberi judul “Pintu Depan Platform SaaS & Pendaftaran”. Pembagian sumber dipertahankan; tim perlu menyepakati pemilik akhir agar tidak ada duplikasi implementasi.

### Tujuan
Mengumpulkan data bisnis dan PIC calon tenant, subdomain yang diminta, serta paket yang akan diajukan.

### Form dan komponen
1. **Step indicator** — langkah 1 Info Usaha, langkah 2 Info Pemilik/PIC.
2. **Info usaha** — nama usaha/toko, deskripsi singkat, subdomain.
3. **Validasi subdomain** — preview suffix domain; feedback tersedia/tidak tersedia; karakter diperbolehkan; menangani status sedang dicek dan error request.
4. **Info PIC** — nama lengkap, email, nomor WhatsApp aktif, password, konfirmasi password.
5. **Paket** — Starter, Business, Professional/Pro sesuai nama paket final.
6. **Persetujuan** — checkbox Syarat dan Ketentuan dan tombol submit.
7. **Feedback** — validasi per field, loading submit, pesan sukses dan petunjuk langkah selanjutnya.

### Validasi
- Subdomain harus unik dan divalidasi backend, tidak cukup hanya pemeriksaan UI.
- Email dan nomor kontak memiliki format yang dapat diterima.
- Password dan konfirmasi harus sesuai dengan kebijakan aplikasi.
- Jangan menganggap status “approved” berarti client dapat diakses sebelum provisioning tenant selesai.

### Kriteria selesai
Alur multi-step berjalan, subdomain dicek secara asynchronous, data terkirim ke backend, dan hasilnya menuntun calon tenant ke halaman cek status atau langkah yang disetujui.

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

# 5. MINGGU 7 BAGIAN 2 — KERANJANG, CHECKOUT, ORDER, DAN INSPEKSI

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

## 5.3 — Yanuar: Cek Status Pendaftaran Tenant

**ID:** 7.2-Y1  
**Nama halaman:** Status Pendaftaran  
**Route pada sumber:** `/register-tenant/status`  
**Layout:** Public Layout.

### Tujuan
Memungkinkan calon tenant mengecek hasil peninjauan pengajuan tanpa harus menghubungi tim pusat untuk setiap pembaruan.

### Komponen UI
- Input email PIC yang digunakan saat pendaftaran.
- Tombol “Cek Status”.
- State loading ketika sistem mencari pengajuan.
- **Menunggu verifikasi:** ikon jam/pending, teks bahwa pendaftaran sedang ditinjau; contoh sumber menyebut “1×24 jam”, sehingga copy harus disesuaikan dengan SLA yang benar-benar disepakati.
- **Disetujui:** ikon centang, pesan subdomain aktif, tombol menuju panel Admin.
- **Ditolak:** ikon silang dan alasan penolakan yang diberikan backend.
- State email tidak ditemukan atau request gagal.

### Validasi/keamanan
- Jangan membocorkan data pengajuan hanya berdasarkan tebakan email; endpoint harus membatasi data yang ditampilkan.
- Status pada layar tidak boleh hardcoded.
- Akses panel Admin diberikan hanya jika tenant/account telah aktif sesuai aturan backend.

### Kriteria selesai
Semua status ditangani; CTA hanya tersedia pada kondisi yang sah; error dan email tidak ditemukan memiliki pesan yang jelas.

## 5.4 — Yanuar: Detail Pesanan Customer

**ID:** 7.2-Y2  
**Nama halaman:** Detail Pesanan  
**Route pada sumber:** `/my-orders/{id}`  
**Layout:** Base Layout Customer.

### Tujuan
Memberi detail penuh satu order: progres status, pembayaran, identitas, pengiriman, dan tindakan customer yang sedang diizinkan.

### Panel halaman
1. **Stepper status** — Pesan → Bayar → Verifikasi → Dikirim → Diterima, disesuaikan dengan status backend yang final.
2. **Detail order** — ID, item, jumlah, periode sewa, alamat, subtotal dan total.
3. **Panel identitas** — status pemeriksaan dokumen/jaminan; jika ditolak, tampilkan alasan dan jalur unggah ulang jika backend mendukungnya.
4. **Panel pembayaran** — instruksi rekening atau QRIS aktif dari endpoint payment methods; uploader bukti jika status dan identitas telah memenuhi syarat.
5. **Panel pengiriman** — metode serah-terima, nama kurir, nomor resi/link tracking jika ada.
6. **Aksi customer** — menerima barang atau ajukan pengembalian hanya bila diizinkan status/backend.

### Validasi
- Customer hanya bisa membuka order miliknya.
- Informasi resi dan bukti pembayaran tidak boleh diambil dari input URL bebas.
- Aksi “Terima Barang” harus mengirim request dan memperbarui status berdasarkan respons; jangan hanya mengganti badge di browser.

### Kriteria selesai
Detail lengkap, stepper konsisten, upload bukti dan aksi status terhubung API, akses order diotorisasi.

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
- Yanuar bertanggung jawab membuat Base Layout Owner agar konsisten dengan halaman Owner lainnya.

### Kriteria selesai
Login tidak mengirim pengguna ke panel yang salah, session/token ditangani dengan benar, logout mengakhiri session sesuai implementasi, dan error tidak membocorkan rincian sensitif.

## 5.6 — Fiza: Daftar Pesanan Admin

**ID:** 7.2-F2  
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

## 5.7 — Iak: Pengembalian Customer

**ID:** 7.2-A1  
**Nama halaman:** Form Pengembalian Customer  
**Route pada sumber:** `/my-orders/{id}/return`  
**Layout:** Base Layout Customer.

### Tujuan
Membantu customer mengajukan pengembalian dan mengirim informasi resi retur bila barang dikirim kembali melalui kurir.

### Komponen UI
- Detail order dan ringkasan barang yang akan dikembalikan.
- Instruksi pengembalian berdasarkan kebijakan tenant.
- Pilihan cara pengembalian: kurir/ekspedisi atau dikembalikan langsung jika opsi itu tersedia.
- Input nama ekspedisi dan nomor resi retur (opsional ketika pengembalian langsung ke toko).
- Panel denda kondisional jika backend menyatakan terlambat: nominal, dasar/perhitungan singkat, instruksi pembayaran.
- Uploader bukti pembayaran denda jika ada tagihan dan pembayaran manual disetujui.
- Tombol “Konfirmasi Pengembalian Barang”.

### Validasi
- Customer hanya dapat mengajukan pengembalian atas order miliknya dan dalam status yang sesuai.
- Resi diwajibkan hanya untuk metode yang menggunakan kurir.
- Tagihan denda harus berasal dari backend; UI tidak boleh menetapkan nominal final sendiri.
- Tampilkan status request setelah submit dan cegah pengajuan ganda.

### Kriteria selesai
Form pengembalian terkirim, file bukti bila diperlukan valid, status retur tampil dari backend, dan halaman dapat menangani gagal submit.

## 5.8 — Iak: Pemeriksaan Kondisi Fisik Admin

**ID:** 7.2-A2  
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

---

# 6. MINGGU 8 BAGIAN 1 — KATALOG ADMIN, AUTENTIKASI, ORDER, DAN KERUSAKAN

**Tujuan minggu:** Melengkapi CRUD katalog, autentikasi customer, validasi order di sisi Admin, laporan transaksi, serta penyelesaian kasus kerusakan.

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

## 6.5 — Fiza: Detail Pesanan Admin

**ID:** 8.1-F1  
**Route pada sumber:** `/admin/orders/{id}`  
**Layout:** Base Layout Admin.

### Tujuan
Admin meninjau satu order, memeriksa identitas/jaminan, memverifikasi bukti pembayaran, mengalokasikan unit fisik, serta menangani pembatalan dan refund bila berlaku.

### Susunan halaman
1. **Header** — ID order, badge status saat ini, tombol aksi yang memang diizinkan. Sumber lama mencantumkan tombol Batalkan Pesanan berwarna merah.
2. **Rincian order** — data customer, periode sewa, alamat, item, jumlah, harga dan total.
3. **Panel review identitas** — preview foto dokumen identitas dan selfie; tombol verifikasi/terima; tombol tolak dengan modal input alasan; status dan catatan review sebelumnya.
4. **Panel review pembayaran** — preview bukti pembayaran yang dapat diperbesar; tombol verifikasi pembayaran dan tolak bukti disertai alasan.
5. **Panel alokasi unit** — per baris item/jenis produk, dropdown unit fisik yang siap untuk periode pesanan; mendukung jumlah unit sesuai kuantitas; ringkasan asset code yang terpilih.
6. **Panel pengiriman/pengembalian** — link ke informasi shipment/return setelah statusnya tersedia.
7. **Popup pembatalan/refund** — jika order sudah dibayar dan dibatalkan oleh Admin sesuai aturan, input catatan/status refund dan upload bukti refund bila transfer dikembalikan di luar sistem.
8. **Riwayat aktivitas** — jika API menyediakan, tampilkan timestamp dan perubahan status penting.

### Validasi dan aturan
- Admin hanya boleh membuka order pada tenant-nya.
- Verifikasi identitas tidak boleh disamakan dengan verifikasi pembayaran; keduanya langkah berbeda.
- Upload bukti pembayaran customer hanya diterima jika persyaratan identitas telah diverifikasi.
- Pemilihan unit harus mengecek benturan periode dan status unit. Setelah sukses disimpan, kontrol alokasi tidak dapat diubah sembarangan.
- Pembatalan dibatasi status yang diizinkan; refund off-system harus dicatat dan bukti ditautkan ke order.
- Backend memeriksa semua transisi status, walau tombol tidak terlihat.

### Kriteria selesai
Admin dapat membuka detail, review identitas, verifikasi/tolak pembayaran, mengalokasikan unit, membatalkan sesuai aturan, mencatat refund bila ada, dan mendapat feedback yang akurat dari API.

## 6.6 — Fiza: Laporan Transaksi Admin

**ID:** 8.1-F2  
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

## 6.7 — Iak: Daftar dan Detail Kasus Kerusakan Admin

**ID:** 8.1-A1  
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

## 6.8 — Iak: Laporan Kerusakan Customer

**ID:** 8.1-A2  
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

# 7. MINGGU 8 BAGIAN 2 — UNIT, BRANDING, OWNER, DAN NOTIFIKASI

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

## 7.2 — Iqo: Profil dan Branding Admin

**ID:** 8.2-I2  
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

## 7.3 — Yanuar: Global Error 403 dan 404

**ID:** 8.2-Y1  
**Tipe halaman:** Global Error UI; tidak memakai sidebar/navbar utama.

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

## 7.4 — Yanuar: Halaman Tenant Tidak Aktif/Suspended

**ID:** 8.2-Y2  
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

## 7.5 — Yanuar: Dashboard Owner Pusat

**ID:** 8.2-Y3  
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

## 7.6 — Fiza: Manajemen Client Owner

**ID:** 8.2-F1  
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

## 7.7 — Fiza: Akun Admin Rental Owner

**ID:** 8.2-F2  
**Route pada sumber:** `/owner/admins`  
**Layout:** Base Layout Owner.

### Tujuan
Mengelola akun Admin Rental yang berada di tenant berbeda dari panel pusat.

### Komponen UI
- Tabel staf/admin lintas tenant: nama, email, tenant/client, status akun, aksi.
- Filter tenant dan pencarian nama/email.
- Form buat Admin: nama, email, password/instruksi aktivasi, dropdown tenant penempatan.
- Form edit/ubah tenant/status bila aturan mengizinkan.
- Pesan error ketika email sudah terpakai atau limit admin tenant tercapai.

### Aturan limit
- Starter maksimum 1 Admin Rental.
- Business maksimum 3 Admin Rental.
- Professional maksimum 10 Admin Rental.
- UI dapat memberi peringatan/disable tombol saat limit tercapai, tetapi backend tetap wajib menolak request yang melampaui limit.
- Istilah “Backdoor” pada sumber tidak berarti jalur tanpa keamanan; fitur ini tetap endpoint administrasi resmi dengan otorisasi Owner, audit, dan penanganan password aman.

### Kriteria selesai
Owner bisa membuat/mengelola Admin dalam batas paket; tenant penempatan tidak tertukar; akses Admin dibatasi ke tenant yang ditugaskan.

## 7.8 — Iak: Notifikasi Customer

**ID:** 8.2-A1  
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

## 7.9 — Iak: Notifikasi Admin

**ID:** 8.2-A2  
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

# 8. MINGGU 9 BAGIAN 1 — PEMBAYARAN, PERSETUJUAN TENANT, SUBSCRIPTION, ACTIVITY LOG

## 8.1 — Iqo: Metode Pembayaran Admin

**ID:** 9.1-I1  
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

## 8.2 — Yanuar: Antrean Persetujuan Tenant Owner

**ID:** 9.1-Y1  
**Route pada sumber:** `/owner/registrations`  
**Layout:** Base Layout Owner.

### Tujuan
Memberi tim pusat kemampuan meninjau pengajuan tenant sebelum toko diaktifkan.

### Antrean pendaftaran
- Tabel tanggal pengajuan, nama calon usaha, subdomain yang diminta, paket.
- Filter/pencarian status atau calon usaha jika diperlukan.
- Empty state saat tidak ada pengajuan.

### Review detail/modal
- Informasi usaha dan PIC.
- Email dan nomor kontak.
- Paket yang diminta.
- Bukti pembayaran biaya layanan platform hanya jika biaya/flow ini memang berlaku. Sumber lama menyebutnya, tetapi keputusan bisnis/pembayaran harus dipastikan sebelum membangun field ini.
- Tombol WhatsApp direct bila nomor memang diberikan dan link sudah tervalidasi.
- Tombol **Tolak** membuka form alasan penolakan.
- Tombol **Setujui & Buat Toko** menampilkan konfirmasi dan memanggil backend untuk memproses persetujuan/provisioning tenant.

### Aturan
- Tombol tidak boleh membuat tenant hanya di frontend. Semua provisioning dikerjakan backend.
- Persetujuan harus memeriksa keunikan subdomain, kelengkapan data dan status pengajuan.
- Jika proses provisioning gagal, status harus tetap jelas dan pengguna diberi pesan bahwa proses belum selesai.

### Kriteria selesai
Owner dapat meninjau, menolak dengan alasan, menyetujui pengajuan, melihat feedback backend, dan tenant yang aktif bisa diakses melalui subdomain yang resmi.

## 8.3 — Fiza: Subscription Paket Owner

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

## 8.4 — Iak: Activity Log Admin

**ID:** 9.1-A1  
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

---

# 9. MINGGU 9 BAGIAN 2 — PROFIL, NOTIFIKASI OWNER, ACTIVITY LOG OWNER, DAN INTEGRASI FINAL

## 9.1 — Iqo: Profil Saya Customer

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

### Integrasi Iqo
- Hapus hardcoded JSON di katalog dan keranjang saat data backend siap.
- Hubungkan kalender agar mengirim `start_date` dan `end_date` dalam format yang disepakati API.
- Pastikan identitas/alamat/foto checkout dikirim sebagai payload yang benar, termasuk multipart/form-data untuk file.
- Selesaikan route profil agar konsisten dengan konteks tenant tanpa mengubah identitas customer menjadi user terpisah per toko.

### Kriteria selesai
Profil memuat data API, edit hanya field yang didukung, keamanan akun memakai endpoint resmi, dan route tenant-scoped konsisten.

## 9.2 — Yanuar: Integrasi Auth dan Sistem Registrasi

**ID:** 9.2-Y1  
**Jenis pekerjaan:** Integrasi; bukan halaman baru pada sumber.

### Validasi subdomain asynchronous
- Form `/register-tenant` memanggil endpoint cek ketersediaan subdomain saat pengguna mengetik.
- Terapkan debounce sekitar 500 ms seperti yang diusulkan dokumen sumber agar tidak mengirim request pada setiap ketukan tanpa jeda.
- Tampilkan state sedang dicek, tersedia, sudah dipakai, format tidak valid, dan request gagal.
- Saat submit, backend tetap melakukan validasi unik final karena keadaan dapat berubah sejak pengecekan UI.

### Integrasi autentikasi
- Pastikan login Admin dan customer menyimpan session/token sesuai pilihan autentikasi Laravel yang benar-benar dipakai project.
- Verifikasi redirect role: Customer ke area customer; Admin ke panel tenant; Owner ke panel pusat.
- Pastikan user yang belum login diarahkan ke autentikasi dan dikembalikan ke tujuan yang sah.
- Pastikan logout menghapus session dan halaman terlindungi tidak tetap terbuka dengan data sensitif dari cache.
- Uji kasus session kedaluwarsa, kredensial tidak valid, akun nonaktif, dan tenant suspended.

### Integrasi middleware tenant suspended
- Hubungkan UI suspended dengan respons/exception backend yang disepakati.
- Jangan mengartikan setiap error 403, 404, atau network failure sebagai tenant suspended.
- Pastikan suspend pada satu tenant tidak memblokir tenant lain yang aktif atau landing page platform.

### Kriteria selesai
Validasi subdomain memeriksa backend, auth/role redirect tidak tertukar, session bekerja pada refresh dan route terlindungi, dan UI suspended muncul hanya karena status client yang sesuai.

## 9.3 — Fiza: Notifikasi Owner

**ID:** 9.2-F1  
**Route pada sumber:** `/owner/notifications`  
**Layout:** Base Layout Owner.

### Tujuan
Memusatkan peringatan yang terkait dengan operasi platform dan tindakan yang harus ditangani Owner.

### Jenis notifikasi pada sumber
- Pengajuan tenant baru menunggu persetujuan.
- Peringatan kuota keseluruhan platform (misalnya ambang 90%) jika metrik tersebut memang tersedia.
- Tenant mengunggah bukti pembayaran perpanjangan manual jika flow pembayaran itu diaktifkan.

### Komponen UI
- Feed notifikasi platform.
- Ikon per jenis alert.
- Judul, ringkasan pesan, timestamp, status read/unread.
- Aksi “Tandai Semua Dibaca”.
- Klik notifikasi langsung menuju pengajuan/tenant/subscription terkait.
- Loading, empty, error, dan pagination.

### Integrasi tambahan Fiza: aksi pada detail order
- Jadikan aksi “Approve/Verifikasi Identitas”, “Verifikasi Pembayaran”, dan “Batalkan Pesanan” pada `/admin/orders/{id}` sebagai request ke endpoint backend yang sesuai.
- Tampilkan state sedang mengirim, error, dan hasil sukses dari respons API.
- Setelah alokasi unit tersimpan, kirim array ID unit fisik (`unit_id`) yang sah ke backend dan nonaktifkan kontrol alokasi jika backend menyatakan alokasi final/tidak dapat diubah.
- Jangan mengirim array ID dari tenant lain atau unit yang sedang teralokasi pada periode bentrok.

### Kriteria selesai
Notifikasi Owner terhubung ke API, status baca tersimpan, link detail benar, dan semua aksi order/alokasi benar-benar mengubah data backend.

## 9.4 — Iak: Activity Log Owner

**ID:** 9.2-A1  
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
3. Pengajuan disimpan dan calon tenant bisa memeriksa status melalui halaman status.
4. Owner meninjau antrean di `/owner/registrations`, menolak dengan alasan atau menyetujui sesuai kebijakan.
5. Setelah disetujui, provisioning tenant dan akun Admin dikerjakan backend.
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
| Form pendaftaran tenant | Minggu 7 Bagian 1 menempatkan Form Pendaftaran Tenant di daftar Fiza; bagian lain berjudul pendaftaran pada bagian Yanuar | Minggu 9 Bagian 1 Owner approval ditugaskan ke Yanuar; Minggu 9 Bagian 2 integrasi auth oleh Yanuar | Pertahankan penugasan historis untuk dokumentasi, tetapi tunjuk satu pemilik implementasi final agar tidak duplikat. |
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

- [ ] **Katalog customer:** header/footer reusable, search, filter kategori/harga, periode sewa, grid dan pagination.
- [ ] **Detail produk:** galeri, deskripsi/spesifikasi/syarat, harga dan denda, quantity, tambah ke cart.
- [ ] **Keranjang:** kelompok berdasarkan periode, jumlah, hapus, pemilihan item, ringkasan dan checkout satu periode.
- [ ] **Checkout:** ringkasan, alamat, identitas/jaminan, upload file, total/availability diverifikasi backend.
- [ ] **Kategori Admin:** CRUD, jumlah produk, validasi kategori, limit paket.
- [ ] **Produk Admin:** form detail, multi-foto, cover, harga, tarif denda, persyaratan dan status.
- [ ] **Unit fisik:** kuota total unit, asset code unik, filter/status, unit tidak merusak alokasi aktif.
- [ ] **Branding toko:** nama/deskripsi/logo, status akses custom color berdasarkan paket, route final.
- [ ] **Metode pembayaran:** transfer bank/QRIS, uploader QRIS, aktif/nonaktif, data aktif tampil di customer.
- [ ] **Profil customer/integrasi:** profil dari API, hardcoded data di katalog/cart dibersihkan, periode dan file checkout dikirim benar.

## 12.2 Yanuar Alda Baran — 10 pekerjaan/alur

- [ ] Landing Page: Public Layout, fitur/paket/CTA dan footer.
- [ ] Pesanan Saya: tab status, order card, detail/aksi sesuai status dan tenant.
- [ ] Cek status pendaftaran: email, pending/approved/rejected, alasan dan CTA yang benar.
- [ ] Detail pesanan customer: stepper, identitas, pembayaran, shipment, aksi yang diizinkan.
- [ ] Login/register customer: validasi, global customer account, intended redirect.
- [ ] Lupa/reset password: token backend, feedback dan validasi.
- [ ] Error 403/404: UI terpisah, akses aman, tombol kembali berfungsi.
- [ ] Tenant suspended: muncul hanya ketika client suspended, tidak salah menangani error jaringan.
- [ ] Dashboard Owner: agregat tenant/paket/status, tidak menunjukkan transaksi customer.
- [ ] Persetujuan tenant dan integrasi auth: subdomain async, role redirect, session/token, middleware sesuai status.

## 12.3 Fiza Rahmatus Sholikha — 10 pekerjaan/alur

- [ ] Dashboard Admin: Base Layout Admin, metrik dan order terbaru scoped ke tenant.
- [ ] Form pendaftaran tenant pada tugas sumber: koordinasi pemilik final agar tidak duplikasi dengan Yanuar.
- [ ] Login Admin/Owner: autentikasi dan redirect role aman.
- [ ] Daftar pesanan Admin: filter, status, pagination, detail link dan tenant isolation.
- [ ] Detail pesanan Admin: review identitas/pembayaran, alokasi unit, pembatalan/refund sesuai aturan.
- [ ] Laporan transaksi: filter tanggal, angka agregat, rincian dan export jika diaktifkan.
- [ ] Manajemen client Owner: list/edit/suspend/aktifkan dan validasi subdomain.
- [ ] Manajemen Admin Owner: akun lintas tenant, placement, role dan limit admin paket.
- [ ] Subscription Owner: masa berlaku, status, perpanjangan dan limit paket.
- [ ] Notifikasi Owner + integrasi: notifikasi pusat, aksi verifikasi order dan penguncian alokasi setelah tersimpan.

## 12.4 Iak / anggota ke-4 pada dokumen sumber — 10 pekerjaan/alur

- [ ] Pengiriman Admin: daftar order siap proses, metode pickup/kurir, kurir/resi dan perubahan status backend.
- [ ] Penerimaan/denda retur Admin: periksa tenggat, preview, perhitungan final backend, override terkontrol.
- [ ] Pengembalian Customer: instruksi, metode dan resi, denda/bukti jika ada, status retur.
- [ ] Pemeriksaan kondisi fisik: pilih order/unit/fase, checklist, foto dan catatan.
- [ ] Kasus kerusakan Admin: buat kasus, foto, nominal, respons dan penyelesaian.
- [ ] Laporan kerusakan Customer: galeri, nominal, tanggapan dan bukti bayar sesuai kondisi.
- [ ] Notifikasi Customer: feed, unread/read, link order dan data milik user.
- [ ] Notifikasi Admin: alert tenant, link detail dan status baca.
- [ ] Activity Log Admin: read-only, filter waktu/pelaku, tenant scoped.
- [ ] Activity Log Owner + integrasi: filter lintas tenant, pengiriman API, denda final backend dan upload array foto inspeksi.

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

## 14. URUTAN KERJA YANG DIREKOMENDASIKAN

1. **Kunci kontrak**: selaraskan route, nama status, field, dan endpoint pada `docs/api.md`, `docs/database.md`, serta route Laravel aktual.
2. **Selesaikan layout reusable**: Public, Customer, Admin, Owner dan Auth Layout; pastikan route landing/root tidak berbenturan dengan katalog tenant.
3. **Bangun katalog dan unit**: categories → products → equipment units → availability.
4. **Bangun transaksi customer**: cart → checkout → identitas/jaminan → order → payment proof.
5. **Bangun kontrol Admin**: order list/detail → verifikasi → alokasi unit → shipment → return/inspection.
6. **Bangun denda/kerusakan**: backend calculation → customer response/payment proof → resolution.
7. **Bangun Owner**: registrations → clients/admins → subscriptions → owner notifications/activity log.
8. **Selesaikan halaman akun dan audit**: profil, notifikasi customer/admin, laporan, activity log.
9. **Integrasi menyeluruh dan QA**: hapus dummy data, uji seluruh alur, route, status, file upload, role, client isolation dan error.

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
