# RentalBase — Database Design

## 1. Database Overview
```text
Database: PostgreSQL
Platform: Supabase
ORM: Laravel Eloquent
Migration: Laravel Migration
```

Satu database digunakan oleh banyak client. Data bisnis client dipisahkan menggunakan `client_id` sesuai konteks client.

## 2. Entity Overview
```text
clients
subscriptions
users
categories
products
equipment_units
orders
order_items
order_item_units
payments
shipments
returns
identity_guarantees
condition_checks
damage_reports
damage_cases
activity_logs
carts
cart_items
client_payment_methods
notifications
client_registrations
```

Total: **22 tabel**.

## 3. Relationship Overview
```text
clients
 ├── subscriptions
 ├── users
 ├── categories ─── products
 ├── products ─── equipment_units
 ├── carts ─── cart_items
 ├── client_payment_methods
 ├── orders
 │    ├── order_items ─── products
 │    │      └── order_item_units ─── equipment_units
 │    ├── payments ─── client_payment_methods
 │    ├── shipments
 │    ├── returns
 │    ├── identity_guarantees
 │    ├── condition_checks
 │    └── damage_reports ─── damage_cases
 │
 ├── activity_logs
 └── notifications

users
 ├── orders (sebagai customer)
 ├── payments (sebagai verifier)
 ├── condition_checks (sebagai pemeriksa)
 ├── damage_reports (sebagai pelapor)
 ├── carts (sebagai customer)
 ├── client_registrations (sebagai reviewer, Owner)
 ├── notifications (sebagai penerima)
 └── activity_logs

client_registrations
 └── reviewed_by (FK ke users.id / Owner)
```

## 4. Tables

### clients
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| business_name | varchar | no | - | Nama usaha client |
| description | text | yes | NULL | Deskripsi singkat usaha client |
| logo | varchar | yes | NULL | Path/URL logo usaha client |
| subdomain | varchar | no | - | Subdomain client |
| theme_color | varchar | yes | NULL | Warna/tema client |
| status | varchar | no | `active` | Status client |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `subdomain` **UNIQUE**.
- `status` hanya boleh: `active`, `suspended`.
- `business_name`, `description`, dan `logo` dapat diubah Admin Rental melalui Profil dan Branding.
- `theme_color` hanya dapat diubah jika subscription aktif client adalah `Business` atau `Professional`; Starter menggunakan tema bawaan RentalBase.
- `subdomain` hanya dikelola Owner.
- Client tidak dihapus melalui fitur operasional; status digunakan untuk menonaktifkan client.

### subscriptions
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| plan_name | varchar | no | - | Subscription plan name |
| start_date | date | no | - | Subscription start date |
| end_date | date | no | - | Subscription end date |
| status | varchar | no | `active` | Subscription status |
| payment_proof | varchar | yes | NULL | Path/URL foto bukti transfer pembayaran perpanjangan / upgrade |
| payment_status | varchar | no | `diverifikasi` | Status verifikasi pembayaran perpanjangan (`menunggu`, `diverifikasi`, `ditolak`) |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `client_id` → `clients.id`.
- `plan_name` hanya boleh: `Starter`, `Business`, `Professional`.
- `status` hanya boleh: `active`, `expired`, `suspended`.
- `payment_status` hanya boleh: `menunggu`, `diverifikasi`, `ditolak`.
- `end_date` tidak boleh lebih awal dari `start_date`.
- Durasi subscription mengikuti paket: Starter 3 bulan, Business 6 bulan, Professional 12 bulan.

Relationship: `clients 1 ─── N subscriptions`.

## Package / Subscription Benefits

RentalBase menggunakan 3 paket subscription. Batas paket berlaku untuk setiap client dan hanya mencakup benefit berikut; fitur operasional inti tetap sama untuk semua paket.

### Starter
- Maks. **10 jenis produk**
- Maks. **50 unit peralatan total**
- Maks. **5 kategori**
- Maks. **1 admin**
- **3 bulan** durasi pemakaian aplikasi

### Business
- Maks. **50 jenis produk**
- Maks. **100 unit peralatan total**
- Maks. **20 kategori**
- Maks. **3 admin**
- **6 bulan** durasi pemakaian aplikasi
- **Dapat mengubah warna beberapa elemen desain halaman**

### Professional
- **Unlimited jenis produk**
- **Unlimited unit peralatan total**
- **Unlimited kategori**
- Maks. **10 admin**
- **12 bulan** durasi pemakaian aplikasi
- **Dapat mengubah warna beberapa elemen desain halaman**

Tidak ada benefit paket lain yang dibedakan. Fitur inti seperti katalog online, booking rental, availability, pembayaran, pengiriman, pengembalian, condition check, dan damage handling tersedia sama pada seluruh paket.


### users
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | yes | NULL | Client untuk Admin Rental |
| name | varchar | no | - | User name |
| email | varchar | no | - | Email akun |
| password | varchar | no | - | Hashed password |
| role | varchar | no | - | User role |
| status | varchar | no | `aktif` | Account status |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Role:
```text
owner
admin_rental
customer
```

Account status:
```text
aktif
nonaktif
```

Rules:
- `owner`: `client_id = NULL`.
- `customer`: `client_id = NULL` karena akun Customer bersifat global/lintas-client.
- `admin_rental`: **wajib** memiliki `client_id` dan hanya dapat digunakan untuk client tersebut.
- `email` harus **UNIQUE** secara global.

### categories
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| name | varchar | no | - | Category name |
| description | text | yes | NULL | Description |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `client_id` → `clients.id`.
- `UNIQUE(client_id, name)`.
- Satu nama kategori boleh digunakan pada client berbeda, tetapi tidak boleh duplikat dalam client yang sama.

### products
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| category_id | bigint | no | - | Category |
| name | varchar | no | - | Equipment name |
| description | text | yes | NULL | Description |
| rental_price | decimal(12,2) | no | - | Rental price |
| late_fee_per_hour | decimal(12,2) | no | `0` | Tarif denda keterlambatan per jam |
| image | json | yes | NULL | JSON array berisi path/URL foto produk (multi foto) |
| identity_guarantee_requirements | text | yes | NULL | Teks informasi ketentuan jaminan identitas |
| status | varchar | no | `aktif` | Product status |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Product status:
```text
aktif
nonaktif
```

Constraint:
- `client_id` → `clients.id`.
- `category_id` → `categories.id`.
- Product dan category harus berasal dari client yang sama.
- `rental_price >= 0`.
- `late_fee_per_hour >= 0` (digunakan sebagai basis kalkulasi denda otomatis per jam keterlambatan).
- `identity_guarantee_requirements` hanya berupa teks informasi yang ditampilkan kepada customer; sistem tidak memvalidasi isi teks tersebut secara otomatis.
- Jumlah stock tidak disimpan langsung pada tabel ini. Total stock dan available stock dihitung dari `equipment_units`.
- `image` menyimpan JSON array berisi path/URL foto produk. Foto pertama (`image[0]`) digunakan sebagai foto utama di katalog. Contoh: `["depan.jpg", "samping.jpg", "dalam.jpg"]`. Gunakan `$casts = ['image' => 'array']` di Eloquent Model.


### equipment_units
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| product_id | bigint | no | - | Product |
| asset_code | varchar | no | - | Unique physical equipment code |
| status | varchar | no | `available` | Current operational status of the unit |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Equipment unit status:
```text
available
maintenance
damaged
lost
inactive
```

Constraint:
- `client_id` → `clients.id`.
- `product_id` → `products.id`.
- `UNIQUE(client_id, asset_code)`.
- Equipment unit dan product harus berasal dari client yang sama.
- Unit dengan status `maintenance`, `damaged`, `lost`, atau `inactive` tidak dapat dialokasikan untuk rental baru.
- Status `available` menunjukkan unit secara fisik dapat dipakai; apakah unit tersedia pada periode tertentu tetap dihitung berdasarkan assignment rental dan overlap tanggal.

### carts
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| customer_id | bigint | no | - | Customer global |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `client_id` → `clients.id`.
- `customer_id` → `users.id` dengan role `customer`.
- `UNIQUE(client_id, customer_id)` (opsional, jika 1 customer hanya boleh punya 1 cart per client).

### cart_items
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| cart_id | bigint | no | - | Cart |
| product_id | bigint | no | - | Product |
| quantity | integer | no | - | Quantity |
| start_date | timestamp | no | - | Rental start (tanggal & jam) |
| end_date | timestamp | no | - | Rental end (tanggal & jam) |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `cart_id` → `carts.id`.
- `product_id` → `products.id`.
- Product pada item harus berasal dari client yang sama dengan `carts.client_id`.
- `start_date` dan `end_date` disimpan per-item pada `cart_items` agar customer dapat memasukkan barang dengan periode tanggal sewa yang berbeda-beda ke dalam satu keranjang.
- Aturan Checkout: Item dalam keranjang dikelompokkan secara visual di UI berdasarkan kesamaan `(start_date, end_date)` (Shopee-style grouping). Customer men-ceklis satu grup tanggal sewa untuk di-checkout. 1 Checkout mengekstrak item-item yang ber-tanggal sama menjadi 1 `Order`, sehingga `orders.start_date` dan `orders.end_date` bernilai tunggal dan kalkulasi total transaksi berlaku konsisten.

### orders
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| customer_id | bigint | no | - | Customer global |
| order_code | varchar | no | - | Order code |
| start_date | timestamp | no | - | Rental start (tanggal & jam) |
| end_date | timestamp | no | - | Rental end (tanggal & jam) |
| shipping_address | text | no | - | Alamat tujuan pengiriman untuk order |
| total_amount | decimal(12,2) | no | `0` | Total transaction |
| status | varchar | no | `menunggu_konfirmasi` | Order status |
| cancellation_reason | text | yes | NULL | Alasan pembatalan/penolakan order |
| refund_status | varchar | no | `tidak_ada` | Status refund jika dibatalkan setelah bayar |
| refund_proof | varchar | yes | NULL | Path/URL foto bukti transfer refund |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Order status:
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

Refund status:
```text
tidak_ada
menunggu_refund
refund_selesai
```

Rules:
- `1 order = 1 client`.
- `client_id` → `clients.id`.
- `customer_id` → `users.id` dengan role `customer`.
- Customer global dapat memiliki order pada client yang berbeda.
- `shipping_address` diisi customer saat checkout dan digunakan kembali saat Admin Rental membuat shipment.
- `end_date` tidak boleh lebih awal dari `start_date`.
- `total_amount >= 0`.
- `cancellation_reason` diisi ketika status order diubah ke `ditolak` atau `dibatalkan`.
- `refund_status` digunakan untuk melacak pengembalian dana jika order dibatalkan setelah pembayaran terverifikasi.

Status availability aktif:
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

### order_items
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| product_id | bigint | no | - | Product |
| quantity | integer | no | - | Quantity |
| unit_price | decimal(12,2) | no | - | Harga produk pada saat transaksi |
| subtotal | decimal(12,2) | no | - | Subtotal item |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `order_id` → `orders.id`.
- `product_id` → `products.id`.
- `quantity > 0`.
- `unit_price >= 0`.
- `subtotal >= 0`.
- Product pada item harus berasal dari client yang sama dengan `orders.client_id`.
- `quantity` harus sama dengan jumlah unit pada `order_item_units` ketika order siap diproses.

### order_item_units
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_item_id | bigint | no | - | Order item |
| equipment_unit_id | bigint | no | - | Physical equipment unit |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `order_item_id` → `order_items.id`.
- `equipment_unit_id` → `equipment_units.id`.
- `UNIQUE(order_item_id, equipment_unit_id)`.
- Equipment unit yang dialokasikan harus berasal dari product yang sama dengan `order_items.product_id`.
- Satu equipment unit tidak boleh dialokasikan pada dua order aktif yang periode sewanya saling bertabrakan.
- Junction ini menyimpan unit fisik mana yang digunakan untuk suatu order item, sehingga riwayat penggunaan unit dapat dilacak.

### payments
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| client_payment_method_id | bigint | no | - | Target metode pembayaran toko (FK ke client_payment_methods.id) |
| payment_proof | varchar | no | - | Path/URL bukti pembayaran (wajib diunggah) |
| paid_at | timestamp | yes | NULL | Payment timestamp |
| status | varchar | no | `menunggu` | Payment status |
| verified_by | bigint | yes | NULL | User admin verifier |
| notes | text | yes | NULL | Notes |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Payment status:
```text
menunggu
diverifikasi
ditolak
```

Constraint:
- `order_id` → `orders.id`.
- `client_payment_method_id` → `client_payment_methods.id`.
- `verified_by` → `users.id`, nullable.
- Bukti pembayaran (`payment_proof`) wajib diunggah ketika customer mengirimkan konfirmasi pembayaran.
- Payment hanya dapat diunggah setelah `identity_guarantees.status = diverifikasi`.

### shipments
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| shipping_method | varchar | no | - | Delivery method |
| courier_name | varchar | yes | NULL | Courier name |
| tracking_number | varchar | yes | NULL | Tracking number |
| shipping_date | date | yes | NULL | Shipping date |
| received_date | date | yes | NULL | Received date, diisi saat customer konfirmasi |
| shipping_status | varchar | no | `menunggu` | Shipping status |
| notes | text | yes | NULL | Notes |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Shipping method:
```text
kurir
langsung
```

Shipping status:
```text
menunggu
diproses
dikirim
diterima
dibatalkan
```

Rules:
- `order_id` → `orders.id`.
- Alamat tujuan tidak disimpan ulang; gunakan `orders.shipping_address`.
- `courier_name` dan `tracking_number` dapat kosong bila metode `langsung`.
- `received_date` diisi saat customer melakukan konfirmasi penerimaan.

### returns
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| return_method | varchar | no | - | Return method |
| courier_name | varchar | yes | NULL | Courier name |
| tracking_number | varchar | yes | NULL | Return tracking |
| return_date | timestamp | yes | NULL | Return timestamp (tanggal & jam dikembalikan) |
| return_status | varchar | no | `diajukan` | Return status |
| calculated_late_fee | decimal(12,2) | no | `0` | Estimasi denda hasil kalkulasi otomatis sistem |
| late_fee_amount | decimal(12,2) | no | `0` | Nominal denda keterlambatan akhir (bisa di-override Admin) |
| late_fee_status | varchar | no | `tidak_ada` | Status denda |
| late_fee_proof | varchar | yes | NULL | Path/URL foto bukti transfer pembayaran denda dari customer |
| notes | text | yes | NULL | Notes |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Return method:
```text
kurir
langsung
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

Late fee status:
```text
tidak_ada
menunggu_pembayaran
lunas
```

Rules:
- `order_id` → `orders.id`.
- `courier_name` dan `tracking_number` dapat kosong bila metode `langsung`.

### identity_guarantees
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| customer_id | bigint | no | - | Customer global |
| full_name | varchar | no | - | Full name |
| identity_number | varchar | no | - | Nomor KTP |
| identity_document_image | varchar | no | - | Foto KTP |
| face_image | varchar | no | - | Selfie wajah |
| identity_address | text | no | - | Alamat sesuai identitas (KTP) |
| status | varchar | no | `menunggu` | Identity guarantee review status |
| reviewed_by | bigint | yes | NULL | Admin Rental yang melakukan verifikasi/penolakan (FK ke users.id) |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Identity guarantee status:
```text
menunggu
diverifikasi
ditolak
```

Rules:
- `order_id` → `orders.id` dan **UNIQUE**.
- `customer_id` → `users.id` dengan role `customer`.
- Setiap order wajib memiliki **tepat satu** identity guarantee sebelum pembayaran dapat diunggah.
- Data wajib: nama lengkap, nomor KTP, foto KTP, selfie wajah, dan alamat.
- Data ini merupakan jaminan administratif, bukan deposit uang.
- Tidak ada verifikasi identitas eksternal pada tahap awal.
- Bila status `ditolak`, customer dapat memperbaiki/mengirim ulang data untuk order yang sama.

### condition_checks
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| equipment_unit_id | bigint | no | - | Physical equipment unit |
| check_type | varchar | no | - | Check type |
| notes | text | no | - | Condition notes/checklist |
| image | varchar | yes | NULL | Condition photo |
| checked_by | bigint | no | - | User checker |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Check type:
```text
sebelum
sesudah
```

Constraint:
- `order_id` → `orders.id`.
- `equipment_unit_id` → `equipment_units.id`.
- `checked_by` → `users.id`.
- Equipment unit harus termasuk unit yang dialokasikan ke order tersebut melalui `order_item_units`.

### damage_reports
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| equipment_unit_id | bigint | no | - | Physical equipment unit |
| reported_by | bigint | no | - | User reporter |
| description | text | no | - | Damage description |
| image | varchar | yes | NULL | Damage photo |
| status | varchar | no | `dilaporkan` | Report status |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Damage report status:
```text
dilaporkan
ditinjau
ditindaklanjuti
selesai
ditolak
```

Constraint:
- `order_id` → `orders.id`.
- `equipment_unit_id` → `equipment_units.id`.
- `reported_by` → `users.id`.
- Equipment unit harus termasuk unit yang dialokasikan ke order tersebut melalui `order_item_units`.

### damage_cases
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| damage_report_id | bigint | no | - | Damage report |
| status | varchar | no | `dibuka` | Case status |
| compensation_fee | decimal(12,2) | yes | NULL | Biaya ganti rugi kerusakan |
| compensation_status | varchar | no | `tidak_ada` | Status pembayaran ganti rugi |
| compensation_proof | varchar | yes | NULL | Path/URL foto bukti transfer ganti rugi dari customer |
| customer_response | text | yes | NULL | Customer response |
| admin_notes | text | yes | NULL | Admin notes |
| resolution | text | yes | NULL | Resolution |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Damage case status:
```text
dibuka
menunggu_tanggapan_customer
diproses
selesai
dibatalkan
```

Compensation status:
```text
tidak_ada
menunggu_pembayaran
lunas
```

Constraint:
- `damage_report_id` → `damage_reports.id` dan **UNIQUE**.
- Satu laporan kerusakan memiliki paling banyak satu case.
- Penyelesaian mengikuti kebijakan client, bukan otomatisasi AI.

### client_payment_methods
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| type | varchar | no | - | Jenis metode pembayaran |
| bank_name | varchar | yes | NULL | Nama bank |
| account_number | varchar | yes | NULL | Nomor rekening |
| account_holder | varchar | yes | NULL | Nama pemilik rekening |
| qris_image | varchar | yes | NULL | Path/URL gambar QRIS |
| is_active | boolean | no | `true` | Status aktif metode pembayaran |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Payment method type:
```text
bank_transfer
qris
```

Constraint:
- `client_id` → `clients.id`.
- Jika `type` = `bank_transfer`, maka `bank_name`, `account_number`, dan `account_holder` wajib diisi.
- Jika `type` = `qris`, maka `qris_image` wajib diisi.
- Satu client dapat memiliki banyak metode pembayaran aktif.

### notifications
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| user_id | bigint | no | - | Penerima notifikasi |
| title | varchar | no | - | Judul notifikasi |
| message | text | no | - | Isi notifikasi |
| type | varchar | no | - | Jenis notifikasi |
| data | json | yes | NULL | Data tambahan (link, ID terkait) |
| is_read | boolean | no | `false` | Status sudah dibaca |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Notification type:
```text
order
payment
shipment
return
identity_guarantee
damage
registration
subscription
general
```

Constraint:
- `user_id` → `users.id`.
- Notifikasi dikirim ke customer maupun admin sesuai konteks.
- `data` menyimpan JSON berisi informasi terkait seperti `order_id`, URL, dll.

### activity_logs
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | yes | NULL | Client |
| user_id | bigint | no | - | User |
| activity | varchar | no | - | Activity name |
| description | text | yes | NULL | Activity description |
| created_at | timestamp | no | auto | Created |

Constraint:
- `client_id` → `clients.id`, nullable.
- `user_id` → `users.id`.
- Tidak menggunakan `updated_at` karena activity log bersifat catatan kejadian.

### client_registrations
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| business_name | varchar | no | - | Nama usaha client |
| description | text | yes | NULL | Deskripsi singkat usaha |
| subdomain | varchar | no | - | Subdomain yang diajukan |
| plan_name | varchar | no | - | Paket subskripsi yang dipilih (Starter, Business, Professional) |
| admin_name | varchar | no | - | Nama calon Admin Rental (PIC) |
| admin_email | varchar | no | - | Email calon Admin Rental |
| admin_phone | varchar | yes | NULL | Nomor WA/Telepon kontak PIC |
| admin_password | varchar | no | - | Hashed password calon Admin Rental |
| status | varchar | no | `menunggu_verifikasi` | Status pendaftaran |
| payment_proof | varchar | yes | NULL | Path/URL foto bukti transfer pembayaran subskripsi pendaftaran |
| payment_status | varchar | no | `menunggu` | Status verifikasi pembayaran subskripsi (`menunggu`, `diverifikasi`, `ditolak`) |
| rejection_reason | text | yes | NULL | Alasan penolakan dari Owner |
| reviewed_by | bigint | yes | NULL | Owner yang melakukan review (FK ke users.id) |
| reviewed_at | timestamp | yes | NULL | Tanggal & jam review oleh Owner |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Registration status:
```text
menunggu_verifikasi
disetujui
ditolak
```

Rules:
- Diisi dari form pendaftaran di Landing Page ketika calon client menekan tombol "Memulai" / "Get Started" pada tabel paket.
- `subdomain` harus dicek ketersediaannya secara realtime (tidak boleh sudah terpakai di `clients.subdomain` atau `client_registrations.subdomain`).
- `reviewed_by` → `users.id` (Owner).
- Ketika status diubah ke `disetujui`, sistem secara otomatis membuat record baru pada `clients`, `subscriptions`, dan `users` (sebagai `admin_rental` dengan `client_id` terkait).

## 5. Important Relationships
```text
Client
 ├── hasMany Users (Admin Rental)
 ├── hasMany Subscriptions
 ├── hasMany Categories
 ├── hasMany Products
 ├── hasMany EquipmentUnits
 ├── hasMany Orders
 ├── hasMany ClientPaymentMethods
 ├── hasMany ActivityLogs
 └── hasMany Notifications (melalui users)

Subscription
 └── belongsTo Client

User
 ├── belongsTo Client (Admin Rental; nullable untuk Owner/Customer)
 ├── hasMany Orders (customer)
 ├── hasMany Payments (verifier)
 ├── hasMany ConditionChecks (checker)
 ├── hasMany DamageReports (reporter)
 ├── hasMany Notifications
 └── hasMany ActivityLogs

Category
 ├── belongsTo Client
 └── hasMany Products

Product
 ├── belongsTo Client
 ├── belongsTo Category
 ├── hasMany EquipmentUnits
 └── hasMany OrderItems

EquipmentUnit
 ├── belongsTo Client
 ├── belongsTo Product
 ├── hasMany OrderItemUnits
 ├── hasMany ConditionChecks
 └── hasMany DamageReports

Cart
 ├── belongsTo Client
 ├── belongsTo Customer (User)
 └── hasMany CartItems

CartItem
 ├── belongsTo Cart
 └── belongsTo Product

Order
 ├── belongsTo Client
 ├── belongsTo Customer (User)
 ├── hasMany OrderItems
 ├── hasMany Payments
 ├── hasMany Shipments
 ├── hasMany Returns
 ├── hasOne IdentityGuarantee
 ├── hasMany ConditionChecks
 └── hasMany DamageReports

OrderItem
 ├── belongsTo Order
 ├── belongsTo Product
 └── hasMany OrderItemUnits

OrderItemUnit
 ├── belongsTo OrderItem
 └── belongsTo EquipmentUnit

Payment
 ├── belongsTo Order
 ├── belongsTo ClientPaymentMethod
 └── belongsTo Verifier (User, nullable)

Shipment
 └── belongsTo Order

Return
 └── belongsTo Order

IdentityGuarantee
 ├── belongsTo Order
 └── belongsTo Customer (User)

ConditionCheck
 ├── belongsTo Order
 ├── belongsTo EquipmentUnit
 └── belongsTo Checker (User)

DamageReport
 ├── belongsTo Order
 ├── belongsTo EquipmentUnit
 ├── belongsTo Reporter (User)
 └── hasOne DamageCase

DamageCase
 └── belongsTo DamageReport

ActivityLog
 ├── belongsTo Client (nullable)
 └── belongsTo User

ClientPaymentMethod
 └── belongsTo Client

Notification
 └── belongsTo User

ClientRegistration
 └── belongsTo Reviewer (User/Owner, nullable)
```

Catatan:
- `Order → IdentityGuarantee` adalah `hasOne` karena satu order hanya memiliki satu jaminan identitas aktif untuk transaksi tersebut.
- `DamageReport → DamageCase` adalah `hasOne`.
- `identity_guarantees.order_id` dan `damage_cases.damage_report_id` harus unique.
- `OrderItem → EquipmentUnit` menggunakan tabel penghubung `order_item_units` agar satu order item dengan `quantity > 1` dapat menunjuk beberapa unit fisik yang berbeda.

## 6. Foreign Key & Delete Rules
Gunakan aturan berikut untuk menjaga integritas data dan histori transaksi:

| Relasi | On Delete |
|---|---|
| `subscriptions.client_id → clients.id` | RESTRICT |
| `users.client_id → clients.id` | RESTRICT |
| `categories.client_id → clients.id` | CASCADE |
| `products.client_id → clients.id` | CASCADE |
| `products.category_id → categories.id` | RESTRICT |
| `equipment_units.client_id → clients.id` | RESTRICT |
| `equipment_units.product_id → products.id` | RESTRICT |
| `carts.client_id → clients.id` | CASCADE |
| `carts.customer_id → users.id` | CASCADE |
| `cart_items.cart_id → carts.id` | CASCADE |
| `cart_items.product_id → products.id` | CASCADE |
| `orders.client_id → clients.id` | RESTRICT |
| `orders.customer_id → users.id` | RESTRICT |
| `order_items.order_id → orders.id` | CASCADE |
| `order_items.product_id → products.id` | RESTRICT |
| `order_item_units.order_item_id → order_items.id` | CASCADE |
| `order_item_units.equipment_unit_id → equipment_units.id` | RESTRICT |
| `payments.order_id → orders.id` | CASCADE |
| `payments.verified_by → users.id` | SET NULL |
| `shipments.order_id → orders.id` | CASCADE |
| `returns.order_id → orders.id` | CASCADE |
| `identity_guarantees.order_id → orders.id` | CASCADE |
| `identity_guarantees.customer_id → users.id` | RESTRICT |
| `condition_checks.order_id → orders.id` | CASCADE |
| `condition_checks.equipment_unit_id → equipment_units.id` | RESTRICT |
| `condition_checks.checked_by → users.id` | RESTRICT |
| `damage_reports.order_id → orders.id` | CASCADE |
| `damage_reports.equipment_unit_id → equipment_units.id` | RESTRICT |
| `damage_reports.reported_by → users.id` | RESTRICT |
| `damage_cases.damage_report_id → damage_reports.id` | CASCADE |
| `activity_logs.client_id → clients.id` | SET NULL |
| `activity_logs.user_id → users.id` | RESTRICT |
| `client_payment_methods.client_id → clients.id` | CASCADE |
| `notifications.user_id → users.id` | CASCADE |

Catatan:
- Client dan user tidak dihapus lewat alur operasional utama; status `suspended` (untuk client) atau `nonaktif` (untuk user) digunakan bila perlu menonaktifkan.
- Aturan `RESTRICT` digunakan pada data historis agar transaksi dan audit tidak terhapus secara tidak sengaja.

## 7. Multi-Client Data Isolation

Semua data operasional yang terkait dengan client harus dibatasi menggunakan `client_id`.

Contoh:
```php
Product::where('client_id', $clientId)->get();
```

Jangan menggunakan:
```php
Product::all();
```

untuk halaman Admin Rental yang hanya boleh menampilkan data client tertentu.

`client_id` harus ditentukan dari konteks user/domain/server dan tidak boleh dipercaya hanya berdasarkan input frontend.

### Customer global
Karena `users.client_id` bernilai `NULL` untuk customer, data transaksi customer tetap dibatasi melalui `orders.client_id`.

Contoh:
```php
Order::where('customer_id', $userId)
     ->where('client_id', $clientId)
     ->get();
```

Jangan menghilangkan filter `client_id`, karena satu customer dapat memiliki transaksi pada beberapa client.

### Equipment unit isolation
Karena equipment unit adalah data fisik milik client, query terhadap `equipment_units` juga harus dibatasi berdasarkan `client_id` dan konteks product/client. Jangan mengambil seluruh unit tanpa filter client.

Contoh:
```php
EquipmentUnit::where('client_id', $clientId)
    ->where('product_id', $productId)
    ->where('status', 'available')
    ->get();
```

## 8. Unit-Level Inventory & Availability Logic

Inventory RentalBase menggunakan pendekatan **unit-level**. `products` menyimpan informasi jenis produk, sedangkan setiap barang fisik dicatat pada `equipment_units`.

Contoh:
```text
products
id = 1
name = Stroller A

equipment_units
id | product_id | asset_code | status
1  | 1          | ST-001     | available
2  | 1          | ST-002     | available
3  | 1          | ST-003     | damaged
4  | 1          | ST-004     | available
5  | 1          | ST-005     | available
```

Pada contoh tersebut:
```text
Total physical units = 5
Unavailable units    = 1 (damaged)
Potentially available = 4
```

### Unit ID dan Asset Code
- Setiap physical equipment memiliki primary key `equipment_units.id`.
- Setiap unit juga memiliki `asset_code` yang unik dalam satu client untuk memudahkan identifikasi fisik, misalnya `ST-001`, `ST-002`, dan seterusnya.
- `products.id` tetap mengidentifikasi jenis produk, sedangkan `equipment_units.id` mengidentifikasi unit fisik tertentu.

### Perhitungan availability
Availability tidak lagi dihitung dari `products.stock`. Sistem menghitung unit yang dapat digunakan berdasarkan `equipment_units`, assignment pada `order_item_units`, status order, dan overlap periode sewa.

Konsep:
```text
total_stock = jumlah equipment_units untuk satu product

available_stock =
total_stock
- unit dengan status maintenance/damaged/lost/inactive
- unit yang telah dialokasikan ke order aktif
  pada periode yang bertabrakan
```

Order aktif untuk availability:
```text
menunggu_konfirmasi
menunggu_pembayaran
pembayaran_terverifikasi
diproses
dikirim
diterima
```

Order dengan status berikut tidak mengurangi availability:
```text
dikembalikan
selesai
ditolak
dibatalkan
```

### Unit damaged
Jika satu unit mengalami kerusakan, hanya unit tersebut yang diubah statusnya, misalnya:
```text
ST-003
available → damaged
```

Unit tersebut otomatis tidak dapat dipilih untuk rental baru. Unit lain dari product yang sama tetap dapat disewa. Dengan demikian tidak diperlukan pengurangan `products.stock` secara manual.

### Assignment saat rental
Jika customer memesan 2 unit dari product yang sama:
```text
order_items
quantity = 2
```
Maka sistem harus mengalokasikan 2 unit fisik melalui `order_item_units`, misalnya:
```text
order_item_units
order_item_id | equipment_unit_id
10            | 1
10            | 4
```

Sistem tidak boleh mengalokasikan equipment unit yang sama pada dua order aktif dengan periode yang bertabrakan.

## 9. Identity Guarantee Flow

```text
Customer login
    ↓
Create Order
    ↓
Submit Identity Guarantee
    ├── Full name
    ├── Identity number
    ├── Identity document image
    ├── Face image
    └── Identity address
    ↓
status = menunggu
    ↓
Admin Rental review
    ├── diverifikasi
    └── ditolak
          ↓
     Customer dapat memperbaiki/mengirim ulang
```

Pembayaran baru dapat dikirim jika:
```text
identity_guarantees.status = diverifikasi
```

Identity guarantee bukan deposit uang dan tidak menggunakan verifikasi identitas eksternal.

## 10. Reporting & Dashboard Queries

Dashboard dan laporan tidak memerlukan tabel baru. Data diperoleh melalui agregasi dari tabel yang sudah ada.

- **Admin Dashboard**: agregasi `orders` dan `payments` berdasarkan `client_id` dan status.
- **Admin Reports**: agregasi `orders.total_amount` dan `orders.status` dalam rentang tanggal, dibatasi `client_id`.
- **Owner Dashboard**: agregasi `clients.status` dan `subscriptions.status` per client. Owner tidak mengakses detail transaksi harian customer.

## 10.1 Subscription Limits & Branding Authorization

Batas paket merupakan aturan bisnis yang menggunakan data dari `subscriptions`. Struktur database tidak memerlukan tabel atau kolom baru hanya untuk menyimpan limit berikut karena paketnya sudah ditentukan oleh RentalBase:

| Benefit | Starter | Business | Professional |
|---|---:|---:|---:|
| Maks. jenis produk | 10 | 50 | Unlimited |
| Maks. unit peralatan total | 50 | 100 | Unlimited |
| Maks. kategori | 5 | 20 | Unlimited |
| Maks. Admin Rental | 1 | 3 | 10 |
| Durasi | 3 bulan | 6 bulan | 12 bulan |
| Custom warna beberapa elemen | Tidak | Ya | Ya |

Limit harus diperiksa pada business logic/backend sebelum penambahan data. `clients.theme_color` sudah tersedia sehingga tidak membutuhkan kolom tambahan; yang berubah adalah aturan authorization berdasarkan subscription aktif.

## 11. Database Development Rule

Gunakan Laravel Migration sebagai source of truth:
```bash
php artisan make:migration create_clients_table
php artisan make:model Client
php artisan migrate
```

Gunakan:
- Laravel Migration
- Eloquent Model
- Eloquent Relationship
- Laravel Validation

Jangan membuat tabel baru hanya untuk kebutuhan dashboard, availability, atau reporting jika kebutuhan tersebut dapat diperoleh melalui agregasi tabel yang sudah ada.

Migration harus merepresentasikan:
- kolom dan tipe data;
- nullable dan default;
- unique constraint;
- foreign key;
- aturan `onDelete`;
- nilai status/role yang telah ditentukan pada dokumen ini;
- relasi `products` → `equipment_units` → `order_item_units` untuk inventory unit-level.

Supabase digunakan sebagai platform PostgreSQL dan storage sesuai kebutuhan aplikasi.

## 12. Recommended Database Indexes
Untuk performa query multi-tenant (`client_id`) yang optimal saat volume data membesar, buat index pada:
- `products(client_id, status)`
- `equipment_units(client_id, status)`
- `orders(client_id, status)`
- `carts(client_id, customer_id)`
- `notifications(user_id, is_read)`
- `activity_logs(client_id, created_at)`
