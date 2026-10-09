# KONSOLIDASI FINAL PEMBAGIAN TUGAS MINGGU 7-9 (REVISI AUDIT)
**Sistem Aplikasi:** RentalBase SaaS (Pelanggan, Admin Toko, dan Owner Platform)

---

## 1. YANUAR ALDA BARAN — (Auth, Customer Account & Owner Platform)
*Fokus: Seluruh gerbang masuk aplikasi, autentikasi, manajemen profil, desain layout dasar, dan manajemen Super Admin.*

### A. Customer & Public
- **Global Auth:** Halaman Login, Daftar Baru (Register), Lupa Password, Reset Password. (Melayani customer sekaligus login admin).
- **Profil Customer (`/profile`):** Form ubah data diri, upload foto, dan update password.
- **Pendaftaran Tenant (`/register-tenant`):** Form pengajuan toko baru, upload bukti transfer pendaftaran, dan sistem **Cek Status Pendaftaran** publik berdasarkan email.
- **Sistem Error Global:** Desain UI untuk 404 (Not Found), 403 (Akses Ditolak), dan **Global Middleware UI** (Halaman *fullscreen* "Toko Sedang Suspend" untuk memblokir akses customer jika toko tidak aktif).

### B. Owner (Super Admin)
- **Base Layout Owner:** Membuat kerangka *sidebar* khusus untuk Owner pusat.
- **Dashboard Owner (`/owner/dashboard`):** Menampilkan 4 kartu metrik performa SaaS (Jumlah tenant aktif, disuspend, dll).
- **Pengajuan Tenant (`/owner/registrations`):** Halaman ulasan pendaftaran toko baru, melihat bukti transfer, dan tombol krusial **"Setujui & Buat Toko"** yang akan memicu backend meng-generate database/data awal tenant.
- **Manajemen Client (`/owner/clients`):** Tabel daftar tenant aktif, beserta *toggle switch* untuk men-suspend toko jika melanggar aturan.
- **Manajemen Admin (`/owner/admins`):** Sistem *backdoor* bagi Owner untuk membuatkan akun Admin baru di toko tertentu, beserta validasi kuota admin sesuai paket langganan.

---

## 2. TERSIQO ALFAREZEL (Project Manager) — (Booking, Order & Payment)
*Fokus: Uang, keranjang, pesanan, transaksi, dan pelaporan pendapatan.*

### A. Customer
- **Base Layout Customer:** Kerangka UI untuk pelanggan (Katalog, Keranjang, Pesanan).
- **Keranjang Belanja (`/client/{subdomain}/cart`):** Menampilkan produk yang dipilih, dikelompokkan secara otomatis **berdasarkan Tanggal/Periode Sewa**.
- **Checkout (`/client/{subdomain}/checkout`):** Ringkasan biaya total, form pengisian alamat tujuan, form identitas KTP (Nama, NIK, Upload Foto KTP, Upload Foto Selfie).
- **Daftar Pesanan Saya (`/my-orders`):** Menampilkan histori pemesanan pengguna.
- **Detail Pesanan (`/my-orders/{id}`):** Visual stepper (Progress Bar Pesanan), instruksi cara transfer bank/QRIS, uploader Bukti Pembayaran, dan tombol krusial **"Terima Barang"**.

### B. Admin Rental & Owner
- **Daftar Pesanan Masuk (`/admin/orders`):** Tabel master berisi seluruh pesanan pelanggan di toko tersebut, lengkap dengan rentang filter tanggal.
- **Validasi Order (`/admin/orders/{id}`):** 
  - Panel Review KTP (Approve/Reject).
  - Panel Review Pembayaran (Approve/Reject).
  - Panel Alokasi Unit (Pilih kode *unit fisik* mana yang akan diserahkan ke pelanggan).
- **Laporan Transaksi (`/admin/reports`):** Rangkuman pendapatan bersih dan denda, diekspor ke tabel Excel/PDF.
- **Pengaturan Pembayaran (`/admin/settings/payments`):** Menambah nomor rekening / QRIS toko untuk menerima transfer pelanggan, lengkap dengan tombol on/off per rekening.
- **Langganan SaaS Owner (`/owner/subscriptions`):** Halaman tagihan di panel Owner untuk melihat status langganan tenant, lengkap dengan pop-up perpanjang langganan manual.

---

## 3. ZASKIA MAULIDINA (IAK) — (Catalog & Product)
*Fokus: Barang, manajemen stok, varian, etalase pelanggan, dan branding toko.*

### A. Customer
- **Halaman Katalog (`/client/{subdomain}`):** Etalase utama yang menampilkan seluruh produk sewa toko terkait, dengan fitur filter kategori dan pencarian.
- **Detail Produk (`/client/{subdomain}/products/{id}`):** Menampilkan info deskripsi, harga per hari, gambar *carousel*, dan form cek ketersediaan berdasarkan kalender (sebelum masuk keranjang).

### B. Admin Rental
- **Manajemen Kategori (`/admin/categories`):** Tambah/Ubah/Hapus Kategori alat sewa.
- **Manajemen Produk (`/admin/products`):** Upload multi-foto produk, mengatur harga harian, mendeskripsikan syarat sewa, mengatur batas stok total.
- **Manajemen Unit Fisik (`/admin/units`):** Mendaftarkan kode seri barang yang spesifik (misal KMR-001, KMR-002) yang merujuk pada produk induk di atas.
- **Profil & Branding Toko (`/admin/settings/profile`):** Tempat Admin Toko meng-upload logo toko, nama usaha, dan mengubah **Warna Tema Utama (Theme Color)** yang akan memengaruhi UI katalog customer. Terkunci jika menggunakan paket Starter.

---

## 4. FIZA RAHMATUS SHOLIKHA — (Shipment, Return, Damage & Logs)
*Fokus: Operasional pengiriman, retur, denda, pengecekan fisik paska sewa, dan sistem notifikasi/log.*

### A. Customer
- **Pengembalian Pesanan (`/my-orders/{id}/return`):** Form instruksi retur barang, pengisian nomor resi balik ekspedisi (jika dikirim via kurir).
- **Klaim Kerusakan (`/my-orders/{id}/damage`):** Halaman khusus jika admin melayangkan tagihan denda/ganti rugi karena barang rusak. Pelanggan membaca bukti foto dari admin dan melakukan upload bukti transfer ganti rugi.
- **Notifikasi Customer (`/notifications`):** List notifikasi status pesanan, menggunakan sistem *polling*.

### B. Admin Rental
- **Base Layout Admin:** Membangun *sidebar* dan *topbar* kerangka panel Admin Rental.
- **Pengiriman (`/admin/shipments`):** Form input nama kurir, nomor resi kirim, dan biaya ongkos kirim manual ke alamat pelanggan.
- **Penerimaan / Retur (`/admin/returns`):** Menyelesaikan pesanan saat barang kembali, **sistem otomatis** menghitung denda telat (*late fee*) jika melebihi kalender sewa.
- **Pemeriksaan Kondisi (`/admin/condition-checks`):** Checklist kondisi fisik barang sebelum dikirim dan sesudah dikembalikan (wajib upload foto bukti).
- **Kasus Kerusakan (`/admin/damage-cases`):** Admin membuka kasus baru jika ditemukan cacat saat pemeriksaan kondisi. Mencantumkan deskripsi rusak, 5 foto, dan nominal tagihan denda ke pelanggan.
- **Notifikasi Admin (`/admin/notifications`):** Alert pesanan masuk, transfer masuk, KTP masuk.
- **Log Aktivitas (`/admin/activity-logs`):** Jejak rekam seluruh aktivitas staf toko (Siapa yang approve order, siapa hapus barang).

### C. Owner
- **Notifikasi Owner (`/owner/notifications`):** Alert jika ada pendaftaran tenant baru, atau tenant bayar perpanjangan paket.
- **Log Aktivitas Owner (`/owner/activity-logs`):** Jejak rekam tingkat dewa untuk melihat riwayat aktivitas di seluruh SaaS secara transparan.

---
**Dokumen ini adalah acuan final dan menggantikan seluruh file instruksi target minggu 7, 8, dan 9 sebelumnya. Segera jadikan panduan sebelum mulai coding!**
