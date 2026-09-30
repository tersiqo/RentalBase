# RentalBase — Project Context

Dokumen ini merupakan konteks utama untuk developer dan AI coding assistant.

## 1. Project Identity
**Project Name:** RentalBase  
**Project Title:** RentalBase: Equipment Rental System  
**Project Type:** Web-based Equipment Rental Management System  
**Backend:** Laravel  
**Database:** PostgreSQL  
**Database Platform:** Supabase  
**Version Control:** GitHub

## 2. Core Concept
RentalBase adalah software untuk membantu penyedia rental mengelola penyewaan peralatan.

RentalBase bukan marketplace. Satu aplikasi dapat digunakan oleh beberapa client melalui konsep multi-client.

```text
RentalBase
├── Client A
├── Client B
└── Client C
```

Contoh implementasi utama pada tahap proyek adalah rental perlengkapan bayi, namun struktur fitur inti dibuat generik untuk jenis usaha rental peralatan lain.

## 3. Multi-Client Rules
Semua data bisnis milik client harus memperhatikan `client_id`.

Admin Rental hanya boleh melihat dan mengelola data client miliknya, dan akunnya terikat pada satu `client_id` (tidak bisa login lintas-client). Owner memiliki akses lintas client sesuai fungsi pengelolaan (client, akun Admin Rental, subscription, dashboard monitoring ringkas), tetapi tidak mengakses transaksi harian customer. Customer mengakses satu client melalui domain/subdomain, namun akun Customer sendiri bersifat global (`users.client_id = NULL`) dan dapat dipakai untuk menyewa di client mana pun.

## 4. Actors
- **Customer:** pengguna yang menyewa peralatan.
- **Admin Rental:** pengelola operasional rental milik client.
- **Owner:** pemilik/pengelola software RentalBase; bukan operator rental harian.

## 5. Important Business Rules
1. Tidak ada marketplace.
2. Tidak ada `branches`.
3. Tidak ada `branch_id`.
4. Satu order hanya berasal dari satu client.
5. Customer mengakses client melalui domain/subdomain. Halaman `/` adalah landing page platform RentalBase, bukan katalog client.
6. Data client harus terisolasi.
7. Admin Rental tidak boleh mengakses data client lain.
8. Owner dapat mengelola client.
9. Owner bukan Admin Rental.
10. Tidak ada deposit uang.
11. Identity guarantee adalah jaminan administratif, diisi customer untuk setiap order saat checkout sebelum bukti pembayaran diunggah. Data wajib: nama lengkap, nomor KTP, foto KTP, selfie wajah, dan alamat sesuai identitas.
12. Katalog, detail produk, dan availability dapat diakses tanpa login (guest/publik). Login/registrasi baru diwajibkan saat customer menekan tombol "Sewa Alat"; gunakan intended redirect agar customer kembali ke produk/tanggal yang tadi dipilih setelah login.
13. Akun Customer bersifat global (`users.client_id = NULL`): satu akun dapat menyewa di banyak client berbeda. `GET /api/orders` tetap dibatasi oleh `client_id` dari subdomain yang sedang diakses — riwayat pesanan yang tampil adalah riwayat pada client tersebut saja, bukan gabungan semua client.
14. Alamat pengiriman diisi customer saat checkout dan disimpan pada `orders.shipping_address`, digunakan kembali saat Admin Rental membuat data shipment.
15. Pembayaran transfer manual.
16. Bukti pembayaran diverifikasi Admin Rental dan customer hanya dapat mengunggah bukti pembayaran setelah identity guarantee order berstatus `diverifikasi`.
17. Pengiriman melalui kurir atau metode langsung; customer mengonfirmasi penerimaan barang melalui endpoint tersendiri.
18. Tidak ada GPS/API kurir pada tahap awal.
19. Pengembalian dicatat terpisah dari pengiriman.
20. Kondisi barang dicatat sebelum dan sesudah rental.
21. Kerusakan dapat dibuat sebagai damage case.
22. Penyelesaian kerusakan mengikuti kebijakan client, bukan otomatisasi AI.
23. Client (Admin Rental) dapat mengatur nama usaha, deskripsi, dan logo; custom warna beberapa elemen desain halaman hanya tersedia pada paket Business dan Professional; subdomain tetap dikelola Owner.
24. Core UI tidak boleh diubah sembarangan.
25. Laravel adalah backend dan business logic utama.
26. PostgreSQL adalah database utama.
27. Supabase digunakan sebagai platform PostgreSQL dan layanan terkait yang diperlukan.
28. Dashboard dan laporan Admin/Owner diambil dari agregasi tabel yang ada, tidak memerlukan tabel baru. Availability dihitung dari physical equipment unit, assignment pada `order_item_units`, status unit, dan order aktif yang periodenya bertabrakan.
29. Owner dashboard hanya menampilkan ringkasan client dan subscription, tidak menampilkan detail transaksi.
30. Owner membuat akun Admin Rental untuk client melalui fitur tersendiri; akun tersebut otomatis terhubung ke `client_id` client yang dituju dan wajib memiliki `client_id` (berbeda dengan akun Customer).
31. Identity guarantee requirements pada product (`products.identity_guarantee_requirements`) bersifat teks informasi yang ditampilkan ke customer, bukan aturan yang divalidasi otomatis oleh sistem.
32. Setiap product dapat memiliki banyak `equipment_units` sebagai physical inventory unit.
33. Setiap equipment unit memiliki `asset_code` unik dalam client dan status operasional `available`, `maintenance`, `damaged`, `lost`, atau `inactive`.
34. Jumlah unit tidak disimpan sebagai angka stock utama pada `products`; total dan available unit dihitung dari `equipment_units`.
35. `order_item_units` menghubungkan order item dengan physical equipment unit yang dialokasikan sehingga unit tertentu dapat dilacak sepanjang histori rental.

## 5.1 Subscription Package Rules

RentalBase memiliki tiga paket dengan benefit berikut:

| Benefit | Starter | Business | Professional |
|---|---:|---:|---:|
| Maks. jenis produk | 10 | 50 | Unlimited |
| Maks. unit peralatan total | 50 | 100 | Unlimited |
| Maks. kategori | 5 | 20 | Unlimited |
| Maks. Admin Rental | 1 | 3 | 10 |
| Durasi | 3 bulan | 6 bulan | 12 bulan |
| Custom warna beberapa elemen halaman | Tidak | Ya | Ya |

Tidak ada benefit pembeda lain. Fitur operasional inti seperti katalog online, booking rental, availability, payment, shipping, return, condition, dan damage tersedia sama pada semua paket. Backend wajib menegakkan limit paket sebelum data baru dibuat.

## 5.2 Final Status Values

```text
clients.status → aktif, nonaktif
users.status → aktif, nonaktif
subscriptions.status → active, expired, suspended
products.status → aktif, nonaktif
equipment_units.status → available, maintenance, damaged, lost, inactive
orders.status → menunggu_konfirmasi, menunggu_pembayaran, pembayaran_terverifikasi, diproses, dikirim, diterima, dikembalikan, selesai, ditolak, dibatalkan
payments.status → menunggu, diverifikasi, ditolak
shipments.shipping_status → menunggu, diproses, dikirim, diterima, dibatalkan
returns.return_status → diajukan, diproses, dalam_pengembalian, diterima, selesai, dibatalkan
identity_guarantees.status → menunggu, diverifikasi, ditolak
damage_reports.status → dilaporkan, ditinjau, ditindaklanjuti, selesai, ditolak
damage_cases.status → dibuka, menunggu_tanggapan_customer, diproses, selesai, dibatalkan
```

Availability aktif menggunakan status order `menunggu_konfirmasi`, `menunggu_pembayaran`, `pembayaran_terverifikasi`, `diproses`, `dikirim`, dan `diterima`. Unit dengan status `maintenance`, `damaged`, `lost`, atau `inactive` tidak dapat dialokasikan untuk rental baru.

## 6. Database Rules
Gunakan PostgreSQL. Laravel Migration adalah source of truth struktur database.

Gunakan:
- Laravel Migration
- Eloquent Model
- Eloquent Relationship
- Laravel Validation

## 7. Coding Rules
Sebelum coding:
1. Baca `PRD.md`.
2. Baca `DATABASE.md`.
3. Baca `API.md` jika task berkaitan dengan API.
4. Periksa kode yang sudah ada.
5. Jangan mengubah file yang tidak diperlukan.

Sebelum fitur besar:
1. Jelaskan file yang akan diubah.
2. Jelaskan perubahan database.
3. Jelaskan route.
4. Jelaskan model/controller/service.
5. Minta persetujuan jika perubahan memengaruhi arsitektur.

## 8. Laravel Rules
Gunakan struktur Laravel standar:
```text
app/
├── Models/
├── Http/
│   ├── Controllers/
│   └── Requests/
└── Services/
```

Gunakan Controllers untuk request handling, Models untuk relationships, Form Requests untuk validasi kompleks, dan Services jika business logic cukup kompleks.

## 9. Frontend Rules
UI harus responsive, konsisten, mengikuti desain RentalBase, menggunakan komponen reusable, dan tidak mengubah halaman lain tanpa kebutuhan.

## 10. File Upload Rules
Validasi MIME type, ukuran file, storage, dan keamanan file untuk foto produk, logo client, bukti pembayaran, foto identitas, foto wajah (jaminan identitas), foto kondisi, dan foto kerusakan.

## 11. Security Rules
Perhatikan authentication, authorization, role checking, client isolation, CSRF, input validation, file validation, password hashing, dan mass assignment protection.

Jangan mempercayai `client_id` hanya dari input frontend.

## 12. AI Coding Assistant Rules
AI tidak boleh:
- Membuat fitur di luar PRD tanpa persetujuan.
- Mengubah database tanpa menjelaskan migration.
- Menghapus fitur lama tanpa alasan.
- Mengganti Laravel/PostgreSQL.
- Menambahkan MongoDB/MySQL tanpa persetujuan.
- Membuat marketplace atau sistem cabang.
- Membuat payment gateway, integrasi kurir, atau verifikasi identitas eksternal jika tidak diminta.
- Membuat validasi otomatis terhadap teks `identity_guarantee_requirements` (harus tetap murni informasi).
- Menggunakan hardcode jika data seharusnya berasal dari database.
- Membuat query yang mengakses data client lain.
- Mengubah arsitektur untuk task kecil.

## 13. Development Priority
```text
1. Project setup
2. Database foundation
3. Authentication (termasuk Register global untuk Customer)
4. Customer Home (publik/guest, tanpa login)
6. Product
7. Equipment Unit
8. Availability
8. Booking (mulai wajib login, dipicu tombol "Sewa Alat")
9. Identity Guarantee + Admin Review
10. Payment
11. Shipping & Confirm Receipt
12. Return
13. Condition & Damage
14. Admin Profile & Branding
15. Admin Dashboard & Reports
16. Owner Admin Rental Account Management
17. Subscription Package Limit Enforcement
18. Owner Dashboard
19. Testing
```

Vertical slice awal:
```text
PostgreSQL
    ↓
Laravel
    ↓
Products + Equipment Units
    ↓
Customer Home
```
## 14. Testing Notes
Rencana pengujian awal mengacu pada bagian 7 proposal (Rencana Pengujian Awal / QA Plan Awal): pengujian fungsional (Black Box Testing) untuk seluruh fitur utama termasuk isolasi data antar-client, serta pengujian non-fungsional performa (target respons availability/booking ≤ 3 detik) dan kompatibilitas (Chrome, Firefox, Edge; desktop dan mobile).

## 15. Definition of Done
Fitur dianggap selesai jika UI, backend, database, validation, authorization, package limit enforcement, client isolation (jika relevan), error handling, dan manual testing sudah sesuai serta tidak merusak fitur lain.