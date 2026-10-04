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
15. Pembayaran transfer manual atau QRIS. Admin Rental mengelola metode pembayaran toko (multi rekening bank dan QRIS) melalui fitur pengaturan. Customer melihat daftar metode pembayaran tersedia dan mengunggah bukti pembayaran.
16. Bukti pembayaran diverifikasi Admin Rental dan customer hanya dapat mengunggah bukti pembayaran setelah identity guarantee order berstatus `diverifikasi`.
17. Pengiriman melalui kurir atau metode langsung; customer mengonfirmasi penerimaan barang melalui endpoint tersendiri.
18. Tidak ada GPS/API kurir pada tahap awal.
19. Pengembalian dicatat terpisah dari pengiriman. Admin dapat menambahkan tagihan denda keterlambatan (late fees) jika barang dikembalikan terlambat.
20. Kondisi barang dicatat sebelum dan sesudah rental.
21. Kerusakan dapat dibuat sebagai damage case.
22. Penyelesaian kerusakan mengikuti kebijakan client, bukan otomatisasi AI.
23. Customer dapat menambahkan produk ke dalam Keranjang Belanja (Cart) dan melakukan checkout sekaligus.
24. Client (Admin Rental) dapat mengatur nama usaha, deskripsi, dan logo; custom warna beberapa elemen desain halaman hanya tersedia pada paket Business dan Professional; subdomain tetap dikelola Owner.
25. Core UI tidak boleh diubah sembarangan.
26. Laravel adalah backend dan business logic utama.
27. PostgreSQL adalah database utama.
28. Supabase digunakan sebagai platform PostgreSQL dan layanan terkait yang diperlukan.
29. Dashboard dan laporan Admin/Owner diambil dari agregasi tabel yang ada, tidak memerlukan tabel baru. Availability dihitung dari physical equipment unit, assignment pada `order_item_units`, status unit, dan order aktif yang periodenya bertabrakan.
30. Owner dashboard hanya menampilkan ringkasan client dan subscription, tidak menampilkan detail transaksi.
31. Pendaftaran client baru dilakukan secara semi self-service: calon client memilih paket dan mengisi form pendaftaran di Landing Page (`client_registrations`), kemudian Owner me-review dan menyetujui (ACC) pendaftaran tersebut melalui Owner Dashboard. Setelah disetujui, akun Admin Rental otomatis dibuat dan terikat ke `client_id` terkait.
32. Identity guarantee requirements pada product (`products.identity_guarantee_requirements`) bersifat teks informasi yang ditampilkan ke customer, bukan aturan yang divalidasi otomatis oleh sistem.
33. Setiap product dapat memiliki banyak `equipment_units` sebagai physical inventory unit.
34. Setiap equipment unit memiliki `asset_code` unik dalam client dan status operasional `available`, `maintenance`, `damaged`, `lost`, atau `inactive`.
35. Jumlah unit tidak disimpan sebagai angka stock utama pada `products`; total dan available unit dihitung dari `equipment_units`.
36. `order_item_units` menghubungkan order item dengan physical equipment unit yang dialokasikan sehingga unit tertentu dapat dilacak sepanjang histori rental.
37. Produk mendukung multi foto (JSON array); foto pertama digunakan sebagai foto utama di katalog.
38. Sistem memiliki notifikasi in-app untuk customer dan admin.
39. Order yang dibatalkan/ditolak harus menyertakan alasan (`cancellation_reason`).
40. Keranjang Belanja (`cart_items`) mendukung penyimpanan produk dengan periode tanggal sewa (`start_date` dan `end_date`) yang berbeda-beda. Di UI keranjang, item dikelompokkan berdasarkan kesamaan periode tanggal sewa (Shopee-style grouping). Customer memilih grup tanggal sewa saat checkout, di mana 1 Checkout menghasilkan 1 Order dengan periode tanggal sewa tunggal.
41. Order diselesaikan (`orders.status = selesai`) secara manual oleh Admin Rental setelah kondisi barang pasca-rental divalidasi. Jika Admin Rental tidak menekan tombol konfirmasi selagi barang sudah dikembalikan (`returns.return_status = diterima`), sistem akan menjalankan **Auto-Complete 3 Hari (72 jam)** untuk mengubah status order dan pengembalian menjadi `selesai`, selama tidak ada laporan kerusakan aktif atau denda yang belum lunas.
42. Data pendaftaran calon client disimpan pada tabel `client_registrations` dengan status `menunggu_verifikasi`, `disetujui`, atau `ditolak`. Form pendaftaran mencakup validasi ketersediaan subdomain secara realtime & rate limit `throttle:5,60`. Calon client dapat mengecek status pendaftaran miliknya secara publik di `/register-tenant/status` menggunakan email PIC. Owner dapat mengontak calon client via WhatsApp (`wa.me/{admin_phone}`) dan menyetujui pengajuan di Owner Dashboard. Admin Rental yang sudah aktif dapat melakukan login melalui tombol "Login Admin" di Landing Page dan otomatis di-redirect ke subdomain miliknya.
43. Harga paket langganan dikonfigurasi secara statis pada file `config/packages.php` tanpa menggunakan tabel database tambahan.
44. Aplikasi menjalankan 3 scheduled background commands: `orders:auto-complete` (72 jam setelah dikirim), `orders:auto-cancel-unpaid` (24 jam setelah identitas ACC tanpa bayar), dan `subscriptions:check-expired` (harian).

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
clients.status → active, suspended
users.status → aktif, nonaktif
subscriptions.status → active, expired, suspended
products.status → aktif, nonaktif
equipment_units.status → available, maintenance, damaged, lost, inactive
orders.status → menunggu_konfirmasi, menunggu_pembayaran, pembayaran_terverifikasi, diproses, dikirim, diterima, dikembalikan, selesai, ditolak, dibatalkan
orders.refund_status → tidak_ada, menunggu_refund, refund_selesai
payments.status → menunggu, diverifikasi, ditolak
shipments.shipping_status → menunggu, diproses, dikirim, diterima, dibatalkan
returns.return_status → diajukan, diproses, dalam_pengembalian, diterima, selesai, dibatalkan
returns.late_fee_status → tidak_ada, menunggu_pembayaran, lunas
identity_guarantees.status → menunggu, diverifikasi, ditolak
damage_reports.status → dilaporkan, ditinjau, ditindaklanjuti, selesai, ditolak
damage_cases.status → dibuka, menunggu_tanggapan_customer, diproses, selesai, dibatalkan
damage_cases.compensation_status → tidak_ada, menunggu_pembayaran, lunas
client_registrations.status → menunggu_verifikasi, disetujui, ditolak
```

Availability aktif menggunakan status order `menunggu_konfirmasi`, `menunggu_pembayaran`, `pembayaran_terverifikasi`, `diproses`, `dikirim`, dan `diterima`. Unit dengan status `maintenance`, `damaged`, `lost`, atau `inactive` tidak dapat dialokasikan untuk rental baru.

Kalkulasi Total Transaksi:
- Durasi Sewa (Hari) = `ceil((end_date - start_date) / 24 jam)` (min 1 hari).
- Total Order = `sum(unit_price * quantity * durasi_hari)`.

Pembatalan Order oleh Customer:
- Customer dapat membatalkan order sendiri jika status masih `menunggu_konfirmasi` atau `menunggu_pembayaran`.
- Sistem membatalkan order secara otomatis (Auto-Cancel 24 Jam) jika customer tidak mengunggah bukti bayar 24 jam setelah jaminan identitas diverifikasi.

Penyelesaian Order & Auto-Complete:
- Admin Rental dapat menyelesaikan order secara manual setelah pemeriksaan kondisi pasca-rental.
- Auto-complete otomatis aktif 3 hari (72 jam) setelah `shipments.shipping_status = dikirim`, apabila tidak ada laporan kerusakan (`damage_reports`) aktif atau tagihan denda keterlambatan (`late_fee_status = menunggu_pembayaran`).

Notification Triggers Matrix:
- Notifikasi dikirimkan pada 15 event transaksi & registrasi utama (Pendaftaran Tenant Baru, Order Baru, Cancel Customer, Review Identitas, Review Pembayaran, Shipping, Auto-Complete 72 Jam, Auto-Cancel Unpaid 24 Jam, Return/Denda, Damage Case, Refund, dan Subscription Expiring).ran, Shipping, Return/Denda, Damage Case, dan Refund).

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
3. Platform Landing Page (Root Domain - Hero, Features, Pricing Table Paket Subskripsi)
4. Authentication (termasuk Register global untuk Customer)
5. Customer Home (publik/guest, tanpa login)
6. Product
7. Equipment Unit
8. Availability
9. Cart & Booking (mulai wajib login, dipicu tombol "Sewa Alat" atau lihat Keranjang)
10. Identity Guarantee + Admin Review
11. Payment & Payment Methods
12. Shipping & Confirm Receipt
13. Return & Late Fees
14. Condition & Damage
15. Notifications
16. Admin Profile & Branding
17. Admin Dashboard & Reports
18. Owner Admin Rental Account Management
19. Subscription Package Limit Enforcement
20. Owner Dashboard
21. Testing
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