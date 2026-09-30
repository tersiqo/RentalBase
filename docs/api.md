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
  "business_name": "Jaya Equipment",
  "description": "Penyedia rental alat camping di Malang",
  "logo": "/storage/clients/jaya-logo.png",
  "subdomain": "jaya",
  "theme_color": "#000000",
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
      "name": "Camping",
      "description": "Peralatan camping"
    }
  ]
}
```

## 5. Products
```http
GET /api/products
GET /api/products/{id}
```

**Akses: publik (guest), tanpa login.** Customer dapat melihat katalog, detail produk, dan image sepenuhnya sebelum diminta login.

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
      "name": "Tenda Dome 4P",
      "description": "Tenda untuk empat orang",
      "rental_price": 50000,
      "total_units": 5,
      "available_units": 4,
      "image": "/storage/products/tenda.jpg",
      "identity_guarantee_requirements": "Wajib menyerahkan KTP asli saat pengambilan barang.",
      "status": "aktif"
    }
  ]
}
```

`identity_guarantee_requirements` adalah teks informasi bebas yang diisi Admin Rental dan hanya ditampilkan ke customer; tidak ada validasi otomatis terhadap isinya.

`total_units` dihitung dari jumlah `equipment_units` yang dimiliki product. `available_units` dihitung berdasarkan status unit dan availability pada konteks yang digunakan.

Data harus dibatasi berdasarkan client aktif (dari subdomain), meskipun endpoint ini publik.

### Equipment Unit Concept
Setiap product dapat memiliki beberapa physical equipment unit. Customer tidak perlu mengetahui `equipment_unit_id` atau `asset_code` saat browsing; identitas unit digunakan untuk pengelolaan inventory dan transaksi di sisi Admin Rental.

Contoh:
```text
Product: Stroller A
├── ST-001 → available
├── ST-002 → available
├── ST-003 → damaged
└── ST-004 → available
```

## 6. Availability
```http
GET /api/products/{id}/availability
```

**Akses: publik (guest), tanpa login.** Customer bisa cek ketersediaan sebelum login.

Parameter:
```text
start_date
end_date
```

Example:
```text
GET /api/products/1/availability?start_date=2026-10-01&end_date=2026-10-03
```

Response:
```json
{
  "product_id": 1,
  "start_date": "2026-10-01",
  "end_date": "2026-10-03",
  "total_units": 5,
  "available_units": 3,
  "available": true
}
```

Availability dihitung berdasarkan `equipment_units`, unit yang dialokasikan melalui `order_item_units`, status operasional unit, status order, dan overlap periode rental.

Unit dengan status `maintenance`, `damaged`, `lost`, atau `inactive` tidak dapat dialokasikan untuk rental baru.

Satu equipment unit tidak boleh dialokasikan ke dua order aktif yang periode sewanya saling bertabrakan.

## 7. Orders
**Titik mulai wajib login.** Saat customer menekan tombol "Sewa Alat" pada halaman detail produk, frontend memeriksa status login sebelum meneruskan ke form booking. Jika belum login, tampilkan modal login/daftar terlebih dahulu (gunakan pola *intended redirect* agar setelah login customer langsung kembali ke produk dan tanggal yang tadi dipilih, bukan ke halaman awal).

### Create Order
```http
POST /api/orders
```

Request order menyertakan periode sewa, item, dan alamat pengiriman. Data Identity Guarantee dikirim terpisah sebelum payment proof diunggah.

Request:
```json
{
  "start_date": "2026-10-01",
  "end_date": "2026-10-03",
  "shipping_address": "Jl. Contoh No. 10, Malang",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

Backend harus:
1. Memastikan customer authenticated.
2. Menentukan client berdasarkan konteks subdomain.
3. Memastikan product berasal dari client yang sama.
4. Memeriksa availability pada periode yang diminta.
5. Menghitung total.
6. Membuat order beserta `shipping_address` dan `client_id` sesuai subdomain saat itu.
7. Membuat order items.
8. Ketika order siap diproses, mengalokasikan physical equipment unit melalui `order_item_units` sesuai `quantity`.

Customer tidak mengirim `equipment_unit_id` saat create order. Pemilihan/alokasi unit fisik dilakukan oleh sistem/Admin Rental sesuai proses operasional.

Response:
```json
{
  "message": "Order berhasil dibuat",
  "data": {
    "id": 10,
    "order_code": "ORD-000010",
    "status": "menunggu_konfirmasi",
    "total_amount": 150000
  }
}
```

### Customer Orders
```http
GET /api/orders
GET /api/orders/{id}
```

**Penting:** meskipun akun customer bersifat global (lintas-client), endpoint ini tetap dibatasi berdasarkan `client_id` dari subdomain yang sedang diakses.

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
Identity Guarantee bersifat administratif (bukan deposit uang). Data dikumpulkan pada tahap checkout setelah order dibuat dan sebelum payment proof diunggah.

Setiap order wajib memiliki tepat satu Identity Guarantee. Data wajib terdiri dari full name, identity number, identity document image, face image, dan identity address.

### Submit Identity Guarantee
```http
POST /api/orders/{id}/identity-guarantee
```

Request: `multipart/form-data`

Fields:
```text
full_name
identity_number
identity_document_image
face_image
identity_address
```

Backend harus:
1. Memastikan order milik customer yang login.
2. Memastikan order berasal dari client yang sedang diakses.
3. Memvalidasi tipe dan ukuran file image.
4. Menyimpan satu data Identity Guarantee per order.
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
  "notes": "Data identitas sesuai dan dapat diterima."
}
```

Nilai status review:
```text
menunggu
diverifikasi
ditolak
```

Backend harus memastikan Admin Rental hanya dapat memeriksa Identity Guarantee yang terkait dengan order milik client-nya. Jika status `ditolak`, customer dapat memperbaiki dan mengirim ulang data untuk order yang sama.

## 9. Payments
### Upload Payment Proof
```http
POST /api/orders/{id}/payment
```

Request: `multipart/form-data`

Fields:
```text
payment_method
payment_proof
```

Backend memvalidasi ownership order, file type, file size, payment status, dan memastikan Identity Guarantee order sudah tersimpan dan berstatus `diverifikasi` sebelum payment proof dapat diunggah.

### Admin Verify Payment
```http
PATCH /api/admin/payments/{id}/verify
```

Request:
```json
{
  "status": "diverifikasi",
  "notes": "Pembayaran sesuai"
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
  "shipping_method": "kurir",
  "courier_name": "JNE",
  "tracking_number": "ABC123456",
  "shipping_date": "2026-10-01"
}
```

Alamat tujuan pengiriman diambil dari `orders.shipping_address` yang telah diisi customer saat checkout, sehingga tidak perlu diinput ulang oleh Admin Rental.

### Update Shipment
```http
PATCH /api/admin/shipments/{id}
```

Shipping status:
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

Digunakan customer untuk mengonfirmasi bahwa barang sudah diterima. Backend mengisi `shipments.received_date` dan memperbarui `shipping_status`.

Response:
```json
{
  "message": "Penerimaan barang berhasil dikonfirmasi",
  "data": {
    "order_id": 10,
    "shipping_status": "diterima",
    "received_date": "2026-10-04"
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
  "return_method": "langsung",
  "return_date": "2026-10-03"
}
```

Return status:
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
equipment_unit_id
check_type
notes
image
```

Check type:
```text
sebelum
sesudah
```

### View Condition Checks
```http
GET /api/orders/{id}/condition-checks
```

Menampilkan seluruh catatan kondisi (sebelum dan sesudah) untuk physical equipment unit yang terkait dengan order. Dapat diakses oleh customer pemilik order dan Admin Rental pada client yang sama.

## 13. Damage Reports
### Create Damage Report (Admin)
```http
POST /api/admin/orders/{id}/damage-report
```

Request fields:
```text
equipment_unit_id
description
image
```

### View Damage Reports
```http
GET /api/orders/{id}/damage-reports
```

Damage report status:
```text
dilaporkan
ditinjau
ditindaklanjuti
selesai
ditolak
```

Damage case status:
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
  "customer_response": "Saya menyetujui laporan kerusakan."
}
```

## 14. Admin Equipment Management

### Product Management
```text
POST   /api/admin/products
PUT    /api/admin/products/{id}
DELETE /api/admin/products/{id}
```

Create Product:
```json
{
  "category_id": 1,
  "name": "Tenda Dome 4P",
  "description": "Tenda kapasitas 4 orang",
  "rental_price": 50000,
  "identity_guarantee_requirements": "Wajib menyerahkan KTP asli saat pengambilan barang.",
  "status": "aktif"
}
```

Admin hanya dapat mengelola product pada client sendiri.

Package limit: penambahan product hanya diperbolehkan jika jumlah jenis product client belum mencapai batas paket aktif. Professional tidak memiliki batas jumlah jenis product.

### Equipment Unit Management
Setiap physical equipment dibuat sebagai `equipment_unit` yang terhubung ke satu product.

```text
GET    /api/admin/products/{productId}/units
POST   /api/admin/products/{productId}/units
PUT    /api/admin/equipment-units/{id}
PATCH  /api/admin/equipment-units/{id}/status
```

Create Equipment Unit:
```json
{
  "asset_code": "ST-001",
  "status": "available"
}
```

Equipment unit status:
```text
available
maintenance
damaged
lost
inactive
```

Admin hanya dapat mengelola equipment unit client sendiri. `client_id` ditentukan oleh konteks server dan `product_id` harus berasal dari client yang sama.

Package limit: penambahan equipment unit harus menghitung total seluruh unit dari semua product dalam client tersebut. Starter maksimal 50 unit total, Business maksimal 100 unit total, dan Professional unlimited.

## 15. Admin Categories
```text
GET    /api/admin/categories
POST   /api/admin/categories
PUT    /api/admin/categories/{id}
DELETE /api/admin/categories/{id}
```

Package limit: penambahan kategori hanya diperbolehkan jika jumlah kategori client belum mencapai batas paket aktif. Starter maksimal 5, Business maksimal 20, dan Professional unlimited.

## 16. Admin Orders
```text
GET /api/admin/orders
GET /api/admin/orders/{id}
PATCH /api/admin/orders/{id}
```

Status order yang dapat digunakan mengikuti daftar pada bagian 7 dan perubahan status harus mengikuti alur bisnis transaksi. Data dibatasi berdasarkan `client_id`.

## 17. Admin Unit Allocation
Admin Rental dapat melihat dan mengalokasikan physical equipment unit untuk order yang akan diproses.

```text
GET  /api/admin/orders/{orderId}/available-units?product_id={productId}
POST /api/admin/orders/{orderId}/items/{orderItemId}/units
```

Request allocation:
```json
{
  "equipment_unit_ids": [1, 4]
}
```

Backend harus memastikan:
- jumlah `equipment_unit_ids` sesuai `order_items.quantity`;
- semua unit berasal dari product dan client yang sama;
- semua unit memiliki status operasional yang memungkinkan rental;
- tidak ada unit yang sudah dialokasikan pada order aktif lain dengan periode bertabrakan;
- allocation disimpan pada `order_item_units`.

## 18. Admin Profile & Branding
Mendukung fitur "Profil dan Branding" pada proposal (5.3). Admin Rental hanya dapat mengubah data usaha miliknya sendiri.

Aturan branding berdasarkan subscription:
- Semua paket dapat mengubah data profil usaha dasar: `business_name`, `description`, dan `logo`.
- Hanya paket **Business** dan **Professional** yang dapat mengubah `theme_color`/warna beberapa elemen desain halaman.
- Paket **Starter** menggunakan warna/desain bawaan RentalBase dan tidak dapat mengubah `theme_color` melalui endpoint ini.
- Core layout dan struktur fitur tetap dikendalikan RentalBase.
- Subdomain tidak dapat diubah melalui endpoint ini karena dikelola oleh Owner.

```http
GET   /api/admin/profile
PATCH /api/admin/profile
```

Request `PATCH` (`multipart/form-data` jika menyertakan logo):
```json
{
  "business_name": "Jaya Equipment",
  "description": "Penyedia rental alat camping terpercaya di Malang",
  "theme_color": "#1F3864"
}
```

Response:
```json
{
  "message": "Profil usaha berhasil diperbarui",
  "data": {
    "id": 1,
    "business_name": "Jaya Equipment",
    "description": "Penyedia rental alat camping terpercaya di Malang",
    "theme_color": "#1F3864"
  }
}
```

## 19. Admin Dashboard & Reports
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
start_date
end_date
```

Response:
```json
{
  "data": [
    {
      "order_id": 10,
      "order_code": "ORD-000010",
      "total_amount": 150000,
      "status": "selesai"
    }
  ],
  "total_transaksi": 20,
  "total_pendapatan": 3000000
}
```

## 20. Owner Client Management
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
  "business_name": "Jaya Equipment",
  "subdomain": "jaya",
  "description": "Penyedia rental alat camping di Malang",
  "theme_color": "#1F3864"
}
```

Response:
```json
{
  "message": "Client berhasil dibuat",
  "data": {
    "id": 6,
    "business_name": "Jaya Equipment",
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

## 21. Owner Admin Rental Account Management
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

Package limit: sebelum membuat Admin Rental baru, backend menghitung jumlah Admin Rental yang masih aktif pada client. Starter maksimal 1, Business maksimal 3, dan Professional maksimal 10.

## 22. Owner Subscription Management

### Package Rules

Package subscription yang tersedia:

| Benefit | Starter | Business | Professional |
|---|---:|---:|---:|
| Maks. jenis produk | 10 | 50 | Unlimited |
| Maks. unit peralatan total | 50 | 100 | Unlimited |
| Maks. kategori | 5 | 20 | Unlimited |
| Maks. Admin Rental | 1 | 3 | 10 |
| Durasi pemakaian aplikasi | 3 bulan | 6 bulan | 12 bulan |
| Custom warna beberapa elemen halaman | Tidak | Ya | Ya |

Backend harus memeriksa limit package aktif ketika client menambah jenis product, equipment unit, kategori, atau Admin Rental. Saat subscription melewati `end_date` atau tidak berstatus `active`, akses layanan sesuai aturan subscription tidak boleh dianggap aktif.

Fitur operasional inti tidak dibatasi berbeda antar paket; yang dibedakan hanya benefit pada tabel di atas.

```text
GET   /api/owner/subscriptions
POST  /api/owner/subscriptions
GET   /api/owner/subscriptions/{id}
PUT   /api/owner/subscriptions/{id}
PATCH /api/owner/subscriptions/{id}/status
```

### Create Subscription
```http
POST /api/owner/subscriptions
```

Request:
```json
{
  "client_id": 6,
  "plan_name": "Professional",
  "start_date": "2026-01-01",
  "end_date": "2027-01-01"
}
```

Aturan validasi:
- `plan_name` hanya boleh `Starter`, `Business`, atau `Professional`.
- Durasi subscription harus mengikuti paket yang dipilih: Starter 3 bulan, Business 6 bulan, Professional 12 bulan.

Response:
```json
{
  "message": "Subscription berhasil dibuat",
  "data": {
    "id": 12,
    "client_id": 6,
    "plan_name": "Professional",
    "start_date": "2026-01-01",
    "end_date": "2027-01-01",
    "status": "active"
  }
}
```

### Update Subscription Status
```http
PATCH /api/owner/subscriptions/{id}/status
```

Request:
```json
{
  "status": "suspended"
}
```

## 23. Owner Monitoring Dashboard
Mendukung fitur "Monitoring Platform" pada proposal. Menampilkan ringkasan seluruh client tanpa mengakses detail transaksi harian customer.

```http
GET /api/owner/dashboard
```

Response:
```json
{
  "total_client": 5,
  "client_aktif": 4,
  "client_nonaktif": 1,
  "subscription_akan_berakhir": 2,
  "clients": [
    {
      "id": 1,
      "business_name": "Jaya Equipment",
      "status": "aktif",
      "subscription_status": "active"
    }
  ]
}
```

## 24. Authorization Rules

### Subscription Package Enforcement
Limit package berlaku pada data client yang bersangkutan:
```text
Starter      → 10 products, 50 equipment units, 5 categories, 1 admin
Business     → 50 products, 100 equipment units, 20 categories, 3 admins
Professional → unlimited products, unlimited equipment units, unlimited categories, 10 admins
```

`theme_color` hanya dapat diubah bila subscription aktif client adalah **Business** atau **Professional**.


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

## 25. API Response Standard
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

## 26. API Security
Setiap endpoint harus memeriksa:
1. Authentication.
2. Authorization.
3. Ownership resource.
4. Client isolation.
5. Input validation.

Validasi akses harus dilakukan di backend, bukan hanya frontend.

## 27. API Development Rule
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
