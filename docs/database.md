# RentalBase — Database Design

## 1. Database Overview
```text
Database: PostgreSQL
Platform: Supabase
ORM: Laravel Eloquent
Migration: Laravel Migration
```

Satu database digunakan oleh banyak client. Data dipisahkan menggunakan `client_id`.

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
 │    ├── damage_reports ─── damage_cases
 │
 └── activity_logs
```

## 4. Tables

### clients
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| nama_usaha | varchar | Nama usaha client |
| deskripsi | text nullable | Deskripsi singkat usaha, ditampilkan pada landing page pemilihan usaha rental |
| logo | varchar nullable | Path/URL logo usaha client |
| subdomain | varchar | Subdomain client |
| warna_tema | varchar | Warna/tema client |
| status | varchar | Status client |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

`nama_usaha`, `deskripsi`, `logo`, dan `warna_tema` dapat diubah oleh Admin Rental melalui fitur Profil dan Branding. `subdomain` hanya dikelola oleh Owner.

### licenses
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| client_id | bigint | Client |
| tanggal_mulai | date | Start date |
| tanggal_berakhir | date | End date |
| status | varchar | License status |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

Relationship: `clients 1 ─── N licenses`

### users
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| client_id | bigint nullable | Client |
| name | varchar | User name |
| email | varchar | Email |
| password | varchar | Hashed password |
| role | varchar | Role |
| status | varchar | Account status |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

Role:
```text
owner
admin_rental
customer
```
Owner dapat memiliki `client_id = NULL`. Akun dengan role `admin_rental` dibuat oleh Owner melalui fitur Manajemen Akun Admin Rental dan wajib memiliki `client_id`.

### categories
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| client_id | bigint | Client |
| nama | varchar | Category name |
| deskripsi | text | Description |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

### products
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| client_id | bigint | Client |
| category_id | bigint | Category |
| nama | varchar | Equipment name |
| deskripsi | text | Description |
| harga_sewa | decimal | Rental price |
| stok | integer | Total stock |
| foto | varchar nullable | Product image |
| ketentuan_jaminan | text nullable | Teks informasi ketentuan jaminan identitas, diisi Admin Rental dan ditampilkan pada halaman detail produk |
| status | varchar | Product status |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

Catatan: `ketentuan_jaminan` bersifat teks informasi saja (misalnya "Wajib menyerahkan KTP asli saat pengambilan barang"). Sistem tidak melakukan validasi atau pengecekan otomatis terhadap isi teks ini; sepenuhnya untuk ditampilkan ke customer sebagai informasi.

### orders
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| client_id | bigint | Client |
| customer_id | bigint | Customer |
| kode_order | varchar | Order code |
| tanggal_mulai | date | Rental start |
| tanggal_selesai | date | Rental end |
| alamat_pengiriman | text | Alamat tujuan pengiriman, diisi customer saat checkout |
| total_harga | decimal | Total |
| status | varchar | Order status |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

Rule: `1 order = 1 client`.

`alamat_pengiriman` diisi pada saat checkout (sesuai proposal 6.2 — Halaman Booking dan Checkout) dan dipakai kembali oleh Admin Rental saat membuat data `shipments`, sehingga alamat tidak perlu diinput ulang.

### order_items
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Order |
| product_id | bigint | Product |
| jumlah | integer | Quantity |
| harga_satuan | decimal | Transaction price |
| subtotal | decimal | Subtotal |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

### payments
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Order |
| metode | varchar | Payment method |
| bukti_pembayaran | varchar nullable | Proof file |
| tanggal_bayar | timestamp nullable | Payment date |
| status | varchar | Payment status |
| diverifikasi_oleh | bigint nullable | Admin verifier |
| catatan | text nullable | Notes |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

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

### shipments
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Order |
| metode_pengiriman | varchar | Courier/direct |
| nama_kurir | varchar nullable | Courier |
| nomor_resi | varchar nullable | Tracking number |
| tanggal_kirim | date nullable | Shipping date |
| tanggal_diterima | date nullable | Received date, diisi saat customer konfirmasi penerimaan |
| status_pengiriman | varchar | Shipping status |
| catatan | text nullable | Notes |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

Metode:
```text
kurir
langsung
```

Alamat tujuan tidak disimpan ulang di tabel ini; gunakan `orders.alamat_pengiriman`.

### returns
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Order |
| metode_pengembalian | varchar | Courier/direct |
| nama_kurir | varchar nullable | Courier |
| nomor_resi | varchar nullable | Return tracking |
| tanggal_pengembalian | date nullable | Return date |
| status_pengembalian | varchar | Return status |
| catatan | text nullable | Notes |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

### identity_guarantees
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Order |
| customer_id | bigint | Customer |
| nama_lengkap | varchar | Full name |
| nomor_identitas | varchar | Identity number (KTP) |
| foto_identitas | varchar nullable | Identity image |
| foto_wajah | varchar nullable | Face image |
| alamat | text | Alamat sesuai identitas (KTP), bukan alamat pengiriman |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

Data ini bersifat jaminan administratif, bukan deposit uang, sesuai batasan pada proposal. Satu order memiliki satu data jaminan identitas, diisi sebelum bukti pembayaran diunggah. Tidak ada verifikasi identitas eksternal pada tahap awal.

### condition_checks
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Order |
| tipe | varchar | Before/after |
| catatan | text | Condition notes/checklist |
| foto | varchar nullable | Condition photo |
| diperiksa_oleh | bigint | User |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

Tipe:
```text
sebelum
sesudah
```

### damage_reports
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Order |
| dilaporkan_oleh | bigint | User |
| deskripsi | text | Damage description |
| foto | varchar nullable | Damage photo |
| status | varchar | Report status |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

### damage_cases
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| damage_report_id | bigint | Damage report |
| status | varchar | Case status |
| tanggapan_customer | text nullable | Customer response |
| catatan_admin | text nullable | Admin notes |
| hasil_penyelesaian | text nullable | Resolution |
| created_at | timestamp | Created |
| updated_at | timestamp | Updated |

### activity_logs
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| client_id | bigint nullable | Client |
| user_id | bigint | User |
| aktivitas | varchar | Activity |
| deskripsi | text nullable | Description |
| created_at | timestamp | Created |

## 5. Important Relationships
```text
Client
 ├── hasMany Users
 ├── hasMany Licenses
 ├── hasMany Categories
 ├── hasMany Products
 └── hasMany Orders

Category
 └── hasMany Products

Product
 ├── belongsTo Client
 └── belongsTo Category

Order
 ├── belongsTo Client
 ├── belongsTo Customer
 ├── hasMany OrderItems
 ├── hasMany Payments
 ├── hasMany Shipments
 ├── hasMany Returns
 ├── hasMany ConditionChecks
 ├── hasMany DamageReports
 └── hasOne IdentityGuarantee

OrderItem
 ├── belongsTo Order
 └── belongsTo Product

DamageReport
 └── hasOne DamageCase
```

Catatan: relasi `Order → IdentityGuarantee` bersifat `hasOne` (satu jaminan identitas per order), bukan `hasMany`.

## 6. Multi-Client Data Isolation
Contoh:
```php
Product::where('client_id', $clientId)->get();
```

Jangan menggunakan `Product::all()` untuk halaman Admin Rental yang seharusnya hanya menampilkan data client tertentu.

Client ID harus ditentukan dari konteks user/domain/server dan tidak boleh dipercaya hanya berdasarkan input frontend.

## 7. Availability Logic
Ketersediaan tidak hanya berdasarkan `products.stok`. Sistem harus memperhitungkan booking yang periode sewanya bertabrakan.

Konsep:
```text
available_stock =
product.stok - jumlah_produk_yang_sedang_dipesan
```

Booking dengan status yang tidak lagi aktif tidak boleh mengurangi availability.

Implementasi final status dan query availability ditentukan saat coding setelah status order disepakati.

## 8. Reporting & Dashboard Queries
Dashboard dan laporan pada `api.md` (Admin Dashboard & Reports, Owner Monitoring Dashboard) tidak memerlukan tabel baru. Data diperoleh melalui agregasi dari tabel yang sudah ada:

- **Admin Dashboard**: hitung `orders` dan `payments` berdasarkan `client_id` dan `status`.
- **Admin Reports**: agregasi `orders.total_harga` dan `orders.status` dalam rentang `tanggal_mulai`/`tanggal_selesai`, dibatasi `client_id`.
- **Owner Dashboard**: hitung `clients` berdasarkan `status`, gabungkan dengan `licenses.status` per client. Tidak mengakses detail `orders`, `payments`, atau data transaksi harian customer.

## 9. Database Development Rule
Gunakan Laravel Migration:
```bash
php artisan make:migration create_clients_table
php artisan make:model Client
php artisan migrate
```

Migration Laravel menjadi source of truth. Supabase digunakan untuk menyediakan PostgreSQL dan storage sesuai kebutuhan.
