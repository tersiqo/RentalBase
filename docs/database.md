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
licenses
users
categories
products
orders
order_items
payments
shipments
returns
identity_guarantees
condition_checks
damage_reports
damage_cases
activity_logs
```

Total: **15 tabel**.

## 3. Relationship Overview
```text
clients
 ├── licenses
 ├── users
 ├── categories ─── products
 ├── products
 ├── orders
 │    ├── order_items ─── products
 │    ├── payments
 │    ├── shipments
 │    ├── returns
 │    ├── identity_guarantees
 │    ├── condition_checks
 │    └── damage_reports ─── damage_cases
 │
 └── activity_logs

users
 ├── orders (sebagai customer)
 ├── payments (sebagai verifier)
 ├── condition_checks (sebagai pemeriksa)
 ├── damage_reports (sebagai pelapor)
 └── activity_logs
```

## 4. Tables

### clients
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| nama_usaha | varchar | no | - | Nama usaha client |
| deskripsi | text | yes | NULL | Deskripsi singkat usaha client |
| logo | varchar | yes | NULL | Path/URL logo usaha client |
| subdomain | varchar | no | - | Subdomain client |
| warna_tema | varchar | yes | NULL | Warna/tema client |
| status | varchar | no | `aktif` | Status client |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `subdomain` **UNIQUE**.
- `status` hanya boleh: `aktif`, `nonaktif`.
- `nama_usaha`, `deskripsi`, `logo`, dan `warna_tema` dapat diubah Admin Rental melalui Profil dan Branding.
- `subdomain` hanya dikelola Owner.
- Client tidak dihapus melalui fitur operasional; status digunakan untuk menonaktifkan client.

### licenses
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| tanggal_mulai | date | no | - | Start date |
| tanggal_berakhir | date | no | - | End date |
| status | varchar | no | `active` | License status |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `client_id` → `clients.id`.
- `status` hanya boleh: `active`, `expired`, `suspended`.

Relationship: `clients 1 ─── N licenses`.

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
| nama | varchar | no | - | Category name |
| deskripsi | text | yes | NULL | Description |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `client_id` → `clients.id`.
- `UNIQUE(client_id, nama)`.
- Satu nama kategori boleh digunakan pada client berbeda, tetapi tidak boleh duplikat dalam client yang sama.

### products
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| category_id | bigint | no | - | Category |
| nama | varchar | no | - | Equipment name |
| deskripsi | text | yes | NULL | Description |
| harga_sewa | decimal(12,2) | no | - | Rental price |
| stok | integer | no | `0` | Total stock |
| foto | varchar | yes | NULL | Product image path/URL |
| ketentuan_jaminan | text | yes | NULL | Teks informasi ketentuan jaminan identitas |
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
- `stok >= 0`.
- `harga_sewa >= 0`.
- `ketentuan_jaminan` hanya berupa teks informasi yang ditampilkan kepada customer; sistem tidak memvalidasi isi teks tersebut secara otomatis.

### orders
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | no | - | Client |
| customer_id | bigint | no | - | Customer global |
| kode_order | varchar | no | - | Order code |
| tanggal_mulai | date | no | - | Rental start |
| tanggal_selesai | date | no | - | Rental end |
| alamat_pengiriman | text | no | - | Alamat tujuan pengiriman untuk order |
| total_harga | decimal(12,2) | no | `0` | Total transaction |
| status | varchar | no | `menunggu_konfirmasi` | Order status |
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

Rules:
- `1 order = 1 client`.
- `client_id` → `clients.id`.
- `customer_id` → `users.id` dengan role `customer`.
- Customer global dapat memiliki order pada client yang berbeda.
- `alamat_pengiriman` diisi customer saat checkout dan digunakan kembali saat Admin Rental membuat shipment.
- `tanggal_selesai` tidak boleh lebih awal dari `tanggal_mulai`.
- `total_harga >= 0`.

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
| jumlah | integer | no | - | Quantity |
| harga_satuan | decimal(12,2) | no | - | Harga produk pada saat transaksi |
| subtotal | decimal(12,2) | no | - | Subtotal item |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Constraint:
- `order_id` → `orders.id`.
- `product_id` → `products.id`.
- `jumlah > 0`.
- `harga_satuan >= 0`.
- `subtotal >= 0`.
- Product pada item harus berasal dari client yang sama dengan `orders.client_id`.

### payments
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| metode | varchar | no | `bank_transfer` | Payment method |
| bukti_pembayaran | varchar | yes | NULL | Path/URL proof file |
| tanggal_bayar | timestamp | yes | NULL | Payment timestamp |
| status | varchar | no | `menunggu` | Payment status |
| diverifikasi_oleh | bigint | yes | NULL | User admin verifier |
| catatan | text | yes | NULL | Notes |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Payment method:
```text
bank_transfer
```

Payment status:
```text
menunggu
diverifikasi
ditolak
```

Constraint:
- `order_id` → `orders.id`.
- `diverifikasi_oleh` → `users.id`, nullable.
- Bukti pembayaran diperlukan ketika customer mengirim pembayaran.
- Payment hanya dapat diunggah setelah `identity_guarantees.status = diverifikasi`.

### shipments
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| metode_pengiriman | varchar | no | - | Delivery method |
| nama_kurir | varchar | yes | NULL | Courier name |
| nomor_resi | varchar | yes | NULL | Tracking number |
| tanggal_kirim | date | yes | NULL | Shipping date |
| tanggal_diterima | date | yes | NULL | Received date, diisi saat customer konfirmasi |
| status_pengiriman | varchar | no | `menunggu` | Shipping status |
| catatan | text | yes | NULL | Notes |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Metode:
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
- Alamat tujuan tidak disimpan ulang; gunakan `orders.alamat_pengiriman`.
- `nama_kurir` dan `nomor_resi` dapat kosong bila metode `langsung`.
- `tanggal_diterima` diisi saat customer melakukan konfirmasi penerimaan.

### returns
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| metode_pengembalian | varchar | no | - | Return method |
| nama_kurir | varchar | yes | NULL | Courier name |
| nomor_resi | varchar | yes | NULL | Return tracking |
| tanggal_pengembalian | date | yes | NULL | Return date |
| status_pengembalian | varchar | no | `diajukan` | Return status |
| catatan | text | yes | NULL | Notes |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Metode:
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

Rules:
- `order_id` → `orders.id`.
- `nama_kurir` dan `nomor_resi` dapat kosong bila metode `langsung`.

### identity_guarantees
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| customer_id | bigint | no | - | Customer global |
| nama_lengkap | varchar | no | - | Full name |
| nomor_identitas | varchar | no | - | Nomor KTP |
| foto_identitas | varchar | no | - | Foto KTP |
| foto_wajah | varchar | no | - | Selfie wajah |
| alamat | text | no | - | Alamat sesuai identitas (KTP) |
| status | varchar | no | `menunggu` | Identity guarantee review status |
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
| tipe | varchar | no | - | Check type |
| catatan | text | no | - | Condition notes/checklist |
| foto | varchar | yes | NULL | Condition photo |
| diperiksa_oleh | bigint | no | - | User checker |
| created_at | timestamp | no | auto | Created |
| updated_at | timestamp | no | auto | Updated |

Tipe:
```text
sebelum
sesudah
```

Constraint:
- `order_id` → `orders.id`.
- `diperiksa_oleh` → `users.id`.

### damage_reports
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| order_id | bigint | no | - | Order |
| dilaporkan_oleh | bigint | no | - | User reporter |
| deskripsi | text | no | - | Damage description |
| foto | varchar | yes | NULL | Damage photo |
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
- `dilaporkan_oleh` → `users.id`.

### damage_cases
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| damage_report_id | bigint | no | - | Damage report |
| status | varchar | no | `dibuka` | Case status |
| tanggapan_customer | text | yes | NULL | Customer response |
| catatan_admin | text | yes | NULL | Admin notes |
| hasil_penyelesaian | text | yes | NULL | Resolution |
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

Constraint:
- `damage_report_id` → `damage_reports.id` dan **UNIQUE**.
- Satu laporan kerusakan memiliki paling banyak satu case.
- Penyelesaian mengikuti kebijakan client, bukan otomatisasi AI.

### activity_logs
| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint | no | auto | Primary key |
| client_id | bigint | yes | NULL | Client |
| user_id | bigint | no | - | User |
| aktivitas | varchar | no | - | Activity name |
| deskripsi | text | yes | NULL | Activity description |
| created_at | timestamp | no | auto | Created |

Constraint:
- `client_id` → `clients.id`, nullable.
- `user_id` → `users.id`.
- Tidak menggunakan `updated_at` karena activity log bersifat catatan kejadian.

## 5. Important Relationships
```text
Client
 ├── hasMany Users (Admin Rental)
 ├── hasMany Licenses
 ├── hasMany Categories
 ├── hasMany Products
 ├── hasMany Orders
 └── hasMany ActivityLogs

License
 └── belongsTo Client

User
 ├── belongsTo Client (Admin Rental; nullable untuk Owner/Customer)
 ├── hasMany Orders (customer)
 ├── hasMany Payments (verifier)
 ├── hasMany ConditionChecks (checker)
 ├── hasMany DamageReports (reporter)
 └── hasMany ActivityLogs

Category
 ├── belongsTo Client
 └── hasMany Products

Product
 ├── belongsTo Client
 ├── belongsTo Category
 └── hasMany OrderItems

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
 └── belongsTo Product

Payment
 ├── belongsTo Order
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
 └── belongsTo Checker (User)

DamageReport
 ├── belongsTo Order
 ├── belongsTo Reporter (User)
 └── hasOne DamageCase

DamageCase
 └── belongsTo DamageReport

ActivityLog
 ├── belongsTo Client (nullable)
 └── belongsTo User
```

Catatan:
- `Order → IdentityGuarantee` adalah `hasOne` karena satu order hanya memiliki satu jaminan identitas aktif untuk transaksi tersebut.
- `DamageReport → DamageCase` adalah `hasOne`.
- `identity_guarantees.order_id` dan `damage_cases.damage_report_id` harus unique.

## 6. Foreign Key & Delete Rules
Gunakan aturan berikut untuk menjaga integritas data dan histori transaksi:

| Relasi | On Delete |
|---|---|
| `licenses.client_id → clients.id` | RESTRICT |
| `users.client_id → clients.id` | RESTRICT |
| `categories.client_id → clients.id` | CASCADE |
| `products.client_id → clients.id` | CASCADE |
| `products.category_id → categories.id` | RESTRICT |
| `orders.client_id → clients.id` | RESTRICT |
| `orders.customer_id → users.id` | RESTRICT |
| `order_items.order_id → orders.id` | CASCADE |
| `order_items.product_id → products.id` | RESTRICT |
| `payments.order_id → orders.id` | CASCADE |
| `payments.diverifikasi_oleh → users.id` | SET NULL |
| `shipments.order_id → orders.id` | CASCADE |
| `returns.order_id → orders.id` | CASCADE |
| `identity_guarantees.order_id → orders.id` | CASCADE |
| `identity_guarantees.customer_id → users.id` | RESTRICT |
| `condition_checks.order_id → orders.id` | CASCADE |
| `condition_checks.diperiksa_oleh → users.id` | RESTRICT |
| `damage_reports.order_id → orders.id` | CASCADE |
| `damage_reports.dilaporkan_oleh → users.id` | RESTRICT |
| `damage_cases.damage_report_id → damage_reports.id` | CASCADE |
| `activity_logs.client_id → clients.id` | SET NULL |
| `activity_logs.user_id → users.id` | RESTRICT |

Catatan:
- Client dan user tidak dihapus lewat alur operasional utama; status `nonaktif` digunakan bila perlu menonaktifkan.
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

## 8. Availability Logic

Ketersediaan tidak hanya berdasarkan `products.stok`. Sistem harus memperhitungkan order yang periode sewanya bertabrakan.

Konsep:
```text
available_stock =
product.stok - jumlah unit dari order aktif
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

Aturan overlap periode harus memastikan booking tidak dapat melebihi `products.stok` pada periode yang sama.

## 9. Identity Guarantee Flow

```text
Customer login
    ↓
Create Order
    ↓
Submit Identity Guarantee
    ├── Nama lengkap
    ├── Nomor KTP
    ├── Foto KTP
    ├── Selfie wajah
    └── Alamat sesuai KTP
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
- **Admin Reports**: agregasi `orders.total_harga` dan `orders.status` dalam rentang tanggal, dibatasi `client_id`.
- **Owner Dashboard**: agregasi `clients.status` dan `licenses.status` per client. Owner tidak mengakses detail transaksi harian customer.

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
- nilai status/role yang telah ditentukan pada dokumen ini.

Supabase digunakan sebagai platform PostgreSQL dan storage sesuai kebutuhan aplikasi.