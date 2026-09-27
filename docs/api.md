# RentalBase — API Specification

## 1. API Overview
Laravel menjadi backend utama RentalBase.

Base URL development:
```text
http://127.0.0.1:8000/api
```

## 2. Authentication
```text
POST /api/register
POST /api/login
POST /api/logout
GET  /api/me
```

Akun Customer bersifat **global/lintas-client**: satu akun dapat digunakan untuk menyewa di banyak usaha rental (client) yang berbeda. Registrasi dan login tidak terikat pada satu subdomain tertentu, meskipun form-nya tampil di dalam tampilan subdomain client yang sedang dikunjungi.

### Register
```http
POST /api/register
```

Request:
```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response:
```json
{
  "message": "Registrasi berhasil",
  "user": {
    "id": 20,
    "name": "Budi Santoso",
    "role": "customer",
    "client_id": null
  }
}
```

Akun customer dibuat dengan `client_id = NULL` karena tidak terikat pada satu client.

### Login
```http
POST /api/login
```

Request:
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

Response:
```json
{
  "message": "Login berhasil",
  "user": {
    "id": 1,
    "name": "User",
    "role": "customer",
    "client_id": null
  }
}
```

## 3. Client
### Get Current Client
```http
GET /api/client
```

**Akses: publik (guest), tanpa login.**

Response:
```json
{
  "id": 1,
  "nama_usaha": "Jaya Equipment",
  "deskripsi": "Penyedia rental alat camping di Malang",
  "logo": "/storage/clients/jaya-logo.png",
  "subdomain": "jaya",
  "warna_tema": "#000000",
  "status": "aktif"
}
```

## 4. Categories
```http
GET /api/categories
GET /api/categories/{id}
```

**Akses: publik (guest), tanpa login.**

Response example:
```json
{
  "data": [
    {
      "id": 1,
      "nama": "Camping",
      "deskripsi": "Peralatan camping"
    }
  ]
}
```

## 5. Products
```http
GET /api/products
GET /api/products/{id}
```

**Akses: publik (guest), tanpa login.** Customer dapat melihat katalog, detail produk, dan foto sepenuhnya sebelum diminta login.

Query:
```text
category_id
search
page
per_page
```

Example:
```text
GET /api/products?category_id=1&search=tenda
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "nama": "Tenda Dome 4P",
      "deskripsi": "Tenda untuk empat orang",
      "harga_sewa": 50000,
      "stok": 5,
      "foto": "/storage/products/tenda.jpg",
      "ketentuan_jaminan": "Wajib menyerahkan KTP asli saat pengambilan barang.",
      "status": "aktif"
    }
  ]
}
```

`ketentuan_jaminan` adalah teks informasi bebas yang diisi Admin Rental dan hanya ditampilkan ke customer; tidak ada validasi otomatis terhadap isinya.

Data harus dibatasi berdasarkan client aktif (dari subdomain), meskipun endpoint ini publik.

## 6. Availability
```http
GET /api/products/{id}/availability
```

**Akses: publik (guest), tanpa login.** Customer bisa cek ketersediaan sebelum login.

Parameter:
```text
tanggal_mulai
tanggal_selesai
```

Example:
```text
GET /api/products/1/availability?tanggal_mulai=2026-10-01&tanggal_selesai=2026-10-03
```

Response:
```json
{
  "product_id": 1,
  "tanggal_mulai": "2026-10-01",
  "tanggal_selesai": "2026-10-03",
  "stok_total": 5,
  "stok_tersedia": 3,
  "tersedia": true
}
```

## 7. Orders
**Titik mulai wajib login.** Saat customer menekan tombol "Sewa Alat" pada halaman detail produk, frontend memeriksa status login sebelum meneruskan ke form booking. Jika belum login, tampilkan modal login/daftar terlebih dahulu (gunakan pola *intended redirect* agar setelah login customer langsung kembali ke produk dan tanggal yang tadi dipilih, bukan ke halaman awal).

### Create Order
```http
POST /api/orders
```

Sesuai alur booking & checkout pada proposal (6.2), request order menyertakan periode sewa, item, dan alamat pengiriman. Data jaminan identitas (KTP, nomor KTP, foto wajah, alamat sesuai identitas) dikirim terpisah melalui endpoint Identity Guarantee (lihat bagian 8) karena melibatkan upload file, sebelum bukti pembayaran diunggah.

Request:
```json
{
  "tanggal_mulai": "2026-10-01",
  "tanggal_selesai": "2026-10-03",
  "alamat_pengiriman": "Jl. Contoh No. 10, Malang",
  "items": [
    {
      "product_id": 1,
      "jumlah": 2
    }
  ]
}
```

Backend harus:
1. Memastikan customer authenticated.
2. Menentukan client berdasarkan konteks subdomain.
3. Memastikan product berasal dari client yang sama.
4. Memeriksa availability.
5. Menghitung total.
6. Membuat order beserta `alamat_pengiriman` dan `client_id` sesuai subdomain saat itu.
7. Membuat order items.

Response:
```json
{
  "message": "Order berhasil dibuat",
  "data": {
    "id": 10,
    "kode_order": "ORD-000010",
    "status": "menunggu_konfirmasi",
    "total_harga": 150000
  }
}
```

### Customer Orders
```http
GET /api/orders
GET /api/orders/{id}
```

**Penting:** meskipun akun customer bersifat global (lintas-client), endpoint ini tetap dibatasi berdasarkan `client_id` dari subdomain yang sedang diakses. Artinya daftar order yang tampil adalah riwayat transaksi customer **pada usaha rental yang sedang dikunjungi saja**, bukan gabungan seluruh usaha rental yang pernah ia sewa. Ini untuk menjaga isolasi data antar-client dan menyamakan ekspektasi tampilan "riwayat saya" di tiap subdomain.

Admin hanya dapat melihat order client-nya.

### Order Status
Status order yang digunakan:
```text
menunggu_konfirmasi
menunggu_pembayaran
pembayaran_terverifikasi
diproses
dikirim
diterima
dikembalikan
selesai
ditolak
dibatalkan
```

Status yang aktif untuk perhitungan availability:
```text
menunggu_konfirmasi
menunggu_pembayaran
pembayaran_terverifikasi
diproses
dikirim
diterima
```

Status yang tidak mengurangi availability:
```text
dikembalikan
selesai
ditolak
dibatalkan
```

Alur umum status:
```text
menunggu_konfirmasi
        ↓
menunggu_pembayaran
        ↓
pembayaran_terverifikasi
        ↓
diproses
        ↓
dikirim
        ↓
diterima
        ↓
dikembalikan
        ↓
selesai
```

Cabang penolakan/pembatalan dapat terjadi sesuai kondisi bisnis.

## 8. Identity Guarantee
Jaminan identitas bersifat administratif (bukan deposit uang), sesuai batasan proposal. Data dikumpulkan pada tahap checkout setelah order dibuat dan sebelum bukti pembayaran diunggah.

Setiap order wajib memiliki tepat satu data jaminan identitas. Data wajib terdiri dari nama lengkap, nomor KTP, foto KTP, selfie wajah, dan alamat sesuai identitas.

### Submit Identity Guarantee
```http
POST /api/orders/{id}/identity-guarantee
```

Request: `multipart/form-data`

Fields:
```text
nama_lengkap
nomor_identitas
foto_identitas
foto_wajah
alamat
```

Backend harus:
1. Memastikan order milik customer yang login.
2. Memastikan order berasal dari client yang sedang diakses.
3. Memvalidasi tipe dan ukuran file foto.
4. Menyimpan satu data jaminan identitas per order.
5. Memberikan status awal `menunggu`.
6. Jika data sebelumnya berstatus `ditolak`, customer dapat mengirim ulang data untuk order yang sama.

Response:
```json
{
  "message": "Jaminan identitas berhasil disimpan",
  "data": {
    "id": 5,
    "order_id": 10,
    "status": "menunggu"
  }
}
```

### View Identity Guarantee
```http
GET /api/orders/{id}/identity-guarantee
```

Dapat diakses oleh customer pemilik order dan Admin Rental pada client yang sama.

### Admin Review Identity Guarantee
```http
PATCH /api/admin/identity-guarantees/{id}/verify
```

Request:
```json
{
  "status": "diverifikasi",
  "catatan": "Data identitas sesuai dan dapat diterima."
}
```

Nilai status review:
```text
menunggu
diverifikasi
ditolak
```

Backend harus memastikan Admin Rental hanya dapat memeriksa jaminan identitas yang terkait dengan order milik client-nya. Jika status `ditolak`, customer dapat memperbaiki dan mengirim ulang data untuk order yang sama.

## 9. Payments
### Upload Payment Proof
```http
POST /api/orders/{id}/payment
```

Request: `multipart/form-data`

Fields:
```text
metode
bukti_pembayaran
```

Backend memvalidasi ownership order, file type, file size, payment status, dan memastikan jaminan identitas order sudah tersimpan dan berstatus `diverifikasi` sebelum pembayaran dapat diunggah.

### Admin Verify Payment
```http
PATCH /api/admin/payments/{id}/verify
```

Request:
```json
{
  "status": "diverifikasi",
  "catatan": "Pembayaran sesuai"
}
```

## 10. Shipping
### Create Shipment
```http
POST /api/admin/orders/{id}/shipment
```

Request:
```json
{
  "metode_pengiriman": "kurir",
  "nama_kurir": "JNE",
  "nomor_resi": "ABC123456",
  "tanggal_kirim": "2026-10-01"
}
```

Alamat tujuan pengiriman diambil dari `orders.alamat_pengiriman` yang telah diisi customer saat checkout, sehingga tidak perlu diinput ulang oleh Admin Rental.

### Update Shipment
```http
PATCH /api/admin/shipments/{id}
```

Status pengiriman:
```text
menunggu
diproses
dikirim
diterima
dibatalkan
```

### Customer View Shipment
```http
GET /api/orders/{id}/shipment
```

### Customer Confirm Receipt
```http
POST /api/orders/{id}/confirm-receipt
```

Digunakan customer untuk mengonfirmasi bahwa barang sudah diterima. Backend mengisi `shipments.tanggal_diterima` dan memperbarui `status_pengiriman`.

Response:
```json
{
  "message": "Penerimaan barang berhasil dikonfirmasi",
  "data": {
    "order_id": 10,
    "status_pengiriman": "diterima",
    "tanggal_diterima": "2026-10-04"
  }
}
```

## 11. Returns
```http
POST /api/orders/{id}/return
PATCH /api/admin/returns/{id}
```

Create request:
```json
{
  "metode_pengembalian": "langsung",
  "tanggal_pengembalian": "2026-10-03"
}
```

Status pengembalian:
```text
diajukan
diproses
dalam_pengembalian
diterima
selesai
dibatalkan
```

## 12. Condition Checks
### Create Condition Check (Admin)
```http
POST /api/admin/orders/{id}/condition-check
```

Fields:
```text
tipe
catatan
foto
```

Tipe:
```text
sebelum
sesudah
```

### View Condition Checks
```http
GET /api/orders/{id}/condition-checks
```

Menampilkan seluruh catatan kondisi (sebelum dan sesudah) untuk satu order. Dapat diakses oleh customer pemilik order dan Admin Rental pada client yang sama.

## 13. Damage Reports
### Create Damage Report (Admin)
```http
POST /api/admin/orders/{id}/damage-report
```

### View Damage Reports
```http
GET /api/orders/{id}/damage-reports
```

Status damage report:
```text
dilaporkan
ditinjau
ditindaklanjuti
selesai
ditolak
```

Status damage case:
```text
dibuka
menunggu_tanggapan_customer
diproses
selesai
dibatalkan
```

Dapat diakses oleh customer pemilik order dan Admin Rental pada client yang sama.

### Customer Response
```http
POST /api/damage-reports/{id}/response
```

Request:
```json
{
  "tanggapan": "Saya menyetujui laporan kerusakan."
}
```

## 14. Admin Equipment Management
```text
POST   /api/admin/products
PUT    /api/admin/products/{id}
DELETE /api/admin/products/{id}
```

Create Product:
```json
{
  "category_id": 1,
  "nama": "Tenda Dome 4P",
  "deskripsi": "Tenda kapasitas 4 orang",
  "harga_sewa": 50000,
  "stok": 5,
  "ketentuan_jaminan": "Wajib menyerahkan KTP asli saat pengambilan barang.",
  "status": "aktif"
}
```

Admin hanya dapat mengelola produk client sendiri.

## 15. Admin Categories
```text
GET    /api/admin/categories
POST   /api/admin/categories
PUT    /api/admin/categories/{id}
DELETE /api/admin/categories/{id}
```

## 16. Admin Orders
```text
GET /api/admin/orders
GET /api/admin/orders/{id}
PATCH /api/admin/orders/{id}
```

Status order yang dapat digunakan mengikuti daftar pada bagian 7 dan perubahan status harus mengikuti alur bisnis transaksi. Data dibatasi berdasarkan `client_id`.

## 17. Admin Profile & Branding
Mendukung fitur "Profil dan Branding" pada proposal (5.3). Admin Rental hanya dapat mengubah data usaha miliknya sendiri, dalam batas konfigurasi yang disediakan sistem (nama usaha, deskripsi, logo, warna tema). Subdomain tidak dapat diubah melalui endpoint ini karena dikelola oleh Owner.

```http
GET   /api/admin/profile
PATCH /api/admin/profile
```

Request `PATCH` (`multipart/form-data` jika menyertakan logo):
```json
{
  "nama_usaha": "Jaya Equipment",
  "deskripsi": "Penyedia rental alat camping terpercaya di Malang",
  "warna_tema": "#1F3864"
}
```

Response:
```json
{
  "message": "Profil usaha berhasil diperbarui",
  "data": {
    "id": 1,
    "nama_usaha": "Jaya Equipment",
    "deskripsi": "Penyedia rental alat camping terpercaya di Malang",
    "warna_tema": "#1F3864"
  }
}
```

## 18. Admin Dashboard & Reports
Mendukung fitur "Dashboard Rental" dan "Laporan" pada proposal (5.3), khusus untuk data milik client yang sedang login.

### Dashboard Summary
```http
GET /api/admin/dashboard
```

Response:
```json
{
  "booking_baru": 4,
  "pembayaran_menunggu_verifikasi": 2,
  "perlu_dikirim": 1,
  "perlu_dikonfirmasi_kembali": 3
}
```

### Transaction Report
```http
GET /api/admin/reports
```

Query:
```text
tanggal_mulai
tanggal_selesai
```

Response:
```json
{
  "data": [
    {
      "order_id": 10,
      "kode_order": "ORD-000010",
      "total_harga": 150000,
      "status": "selesai"
    }
  ],
  "total_transaksi": 20,
  "total_pendapatan": 3000000
}
```

## 19. Owner Client Management
```text
GET   /api/owner/clients
POST  /api/owner/clients
GET   /api/owner/clients/{id}
PUT   /api/owner/clients/{id}
PATCH /api/owner/clients/{id}/status
```

Owner dapat mengelola seluruh client.

### Create Client
```http
POST /api/owner/clients
```

Request:
```json
{
  "nama_usaha": "Jaya Equipment",
  "subdomain": "jaya",
  "deskripsi": "Penyedia rental alat camping di Malang",
  "warna_tema": "#1F3864"
}
```

Response:
```json
{
  "message": "Client berhasil dibuat",
  "data": {
    "id": 6,
    "nama_usaha": "Jaya Equipment",
    "subdomain": "jaya",
    "status": "aktif"
  }
}
```

### Update Client Status
```http
PATCH /api/owner/clients/{id}/status
```

Request:
```json
{
  "status": "nonaktif"
}
```

## 20. Owner Admin Rental Account Management
Mendukung fitur "Manajemen Akun Admin Rental" pada proposal (5.3). Owner membuat dan mengelola akun Admin Rental untuk client tertentu.

```text
GET    /api/owner/clients/{id}/admin-accounts
POST   /api/owner/clients/{id}/admin-accounts
PUT    /api/owner/admin-accounts/{id}
PATCH  /api/owner/admin-accounts/{id}/status
```

### Create Admin Rental Account
```http
POST /api/owner/clients/{id}/admin-accounts
```

Request:
```json
{
  "name": "Admin Jaya Equipment",
  "email": "admin@jaya.com",
  "password": "password123"
}
```

Response:
```json
{
  "message": "Akun Admin Rental berhasil dibuat",
  "data": {
    "id": 15,
    "name": "Admin Jaya Equipment",
    "email": "admin@jaya.com",
    "role": "admin_rental",
    "client_id": 6
  }
}
```

Backend harus memastikan akun yang dibuat otomatis terhubung ke `client_id` sesuai `{id}` pada URL. Berbeda dengan akun Customer, akun Admin Rental wajib memiliki `client_id` dan tidak dapat dipakai lintas-client.

## 21. Owner License Management
```text
GET   /api/owner/licenses
POST  /api/owner/licenses
GET   /api/owner/licenses/{id}
PUT   /api/owner/licenses/{id}
PATCH /api/owner/licenses/{id}/status
```

### Create License
```http
POST /api/owner/licenses
```

Request:
```json
{
  "client_id": 6,
  "tanggal_mulai": "2026-01-01",
  "tanggal_berakhir": "2027-01-01"
}
```

Response:
```json
{
  "message": "License berhasil dibuat",
  "data": {
    "id": 12,
    "client_id": 6,
    "status": "aktif"
  }
}
```

### Update License Status
```http
PATCH /api/owner/licenses/{id}/status
```

Request:
```json
{
  "status": "suspended"
}
```

## 22. Owner Monitoring Dashboard
Mendukung fitur "Monitoring Platform" pada proposal (5.3). Menampilkan ringkasan seluruh client tanpa mengakses detail transaksi harian customer.

```http
GET /api/owner/dashboard
```

Response:
```json
{
  "total_client": 5,
  "client_aktif": 4,
  "client_nonaktif": 1,
  "license_akan_berakhir": 2,
  "clients": [
    {
      "id": 1,
      "nama_usaha": "Jaya Equipment",
      "status": "aktif",
      "license_status": "active"
    }
  ]
}
```

## 23. Authorization Rules

### Public (Guest) — tanpa login
Dapat diakses siapa saja sebelum menekan "Sewa Alat":
```text
GET /api/client
GET /api/categories
GET /api/products
GET /api/products/{id}
GET /api/products/{id}/availability
```

### Customer (wajib login)
Login diwajibkan mulai dari titik ini, dipicu saat customer menekan tombol "Sewa Alat". Akun customer bersifat global dan sama untuk semua client, tetapi resource di bawah ini tetap dibatasi oleh `client_id` dari subdomain yang sedang dikunjungi:
```text
POST /api/register
POST /api/login
POST /api/logout
GET  /api/me
POST /api/orders
GET /api/orders
GET /api/orders/{id}
POST /api/orders/{id}/identity-guarantee
GET /api/orders/{id}/identity-guarantee
POST /api/orders/{id}/payment
GET /api/orders/{id}/shipment
POST /api/orders/{id}/confirm-receipt
POST /api/orders/{id}/return
GET /api/orders/{id}/condition-checks
GET /api/orders/{id}/damage-reports
POST /api/damage-reports/{id}/response
```

Customer tidak dapat mengakses endpoint Admin atau Owner.

### Admin Rental
Mengakses endpoint `/admin/*` (termasuk `/admin/profile`, `/admin/dashboard`, `/admin/reports`, dan review Identity Guarantee) hanya untuk data client miliknya. Berbeda dengan Customer, akun Admin Rental terikat pada satu `client_id` dan tidak dapat login lintas-client.

### Owner
Mengakses endpoint `/owner/*` (termasuk `/owner/clients/{id}/admin-accounts` dan `/owner/dashboard`) sesuai kebutuhan pengelolaan sistem. Owner tidak mengakses endpoint `/admin/*` transaksi harian.

## 24. API Response Standard
Success:
```json
{
  "message": "Success",
  "data": {}
}
```

Error:
```json
{
  "message": "Validation failed",
  "errors": {
    "field": [
      "Error message"
    ]
  }
}
```

HTTP status:
```text
200 OK
201 Created
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
422 Unprocessable Entity
500 Internal Server Error
```

## 25. API Security
Setiap endpoint harus memeriksa:
1. Authentication.
2. Authorization.
3. Ownership resource.
4. Client isolation.
5. Input validation.

Validasi akses harus dilakukan di backend, bukan hanya frontend.

## 26. API Development Rule
Prioritas awal:
```text
GET /api/client
GET /api/categories
GET /api/products
GET /api/products/{id}
GET /api/products/{id}/availability
```

Endpoint tersebut bersifat publik (tanpa auth middleware) dan cukup untuk membangun Customer Home + Equipment Listing + Equipment Detail sebagai vertical slice pertama.

Setelah itu:
```text
Register & Login
↓
Orders (mulai wajib login, dipicu tombol "Sewa Alat")
↓
Identity Guarantee
↓
Payments
↓
Shipping (termasuk confirm-receipt)
↓
Returns
↓
Condition & Damage
↓
Admin Profile & Branding
↓
Admin Dashboard & Reports
↓
Owner Admin Rental Account Management
↓
Owner Dashboard
```