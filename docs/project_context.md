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

Admin Rental hanya boleh melihat dan mengelola data client miliknya. Owner memiliki akses lintas client sesuai fungsi pengelolaan (client, akun Admin Rental, license, dashboard monitoring ringkas), tetapi tidak mengakses transaksi harian customer. Customer mengakses satu client melalui domain/subdomain.

## 4. Actors
- **Customer:** pengguna yang menyewa peralatan.
- **Admin Rental:** pengelola operasional rental milik client.
- **Owner:** pemilik/pengelola software RentalBase; bukan operator rental harian.

## 5. Important Business Rules
1. Tidak ada marketplace.
2. Tidak ada `branches`.
3. Tidak ada `branch_id`.
4. Satu order hanya berasal dari satu client.
5. Customer mengakses client melalui domain/subdomain.
6. Data client harus terisolasi.
7. Admin Rental tidak boleh mengakses data client lain.
8. Owner dapat mengelola client.
9. Owner bukan Admin Rental.
10. Tidak ada deposit uang.
11. Identity guarantee adalah jaminan administratif, diisi customer saat checkout sebelum bukti pembayaran diunggah.
12. Alamat pengiriman diisi customer saat checkout dan disimpan pada `orders.alamat_pengiriman`, digunakan kembali saat Admin Rental membuat data shipment.
13. Pembayaran transfer manual.
14. Bukti pembayaran diverifikasi Admin Rental.
15. Pengiriman melalui kurir atau metode langsung; customer mengonfirmasi penerimaan barang melalui endpoint tersendiri.
16. Tidak ada GPS/API kurir pada tahap awal.
17. Pengembalian dicatat terpisah dari pengiriman.
18. Kondisi barang dicatat sebelum dan sesudah rental.
19. Kerusakan dapat dibuat sebagai damage case.
20. Penyelesaian kerusakan mengikuti kebijakan client, bukan otomatisasi AI.
21. Client (Admin Rental) dapat mengatur nama usaha, deskripsi, logo, dan warna/tema miliknya sendiri; subdomain tetap dikelola Owner.
22. Core UI tidak boleh diubah sembarangan.
23. Laravel adalah backend dan business logic utama.
24. PostgreSQL adalah database utama.
25. Supabase digunakan sebagai platform PostgreSQL dan layanan terkait yang diperlukan.
26. Dashboard dan laporan Admin/Owner diambil dari agregasi tabel yang ada, tidak memerlukan tabel baru.
27. Owner dashboard hanya menampilkan ringkasan client dan license, tidak menampilkan detail transaksi.
28. Owner membuat akun Admin Rental untuk client melalui fitur tersendiri; akun tersebut otomatis terhubung ke `client_id` client yang dituju.
29. Ketentuan jaminan identitas pada produk (`products.ketentuan_jaminan`) bersifat teks informasi yang ditampilkan ke customer, bukan aturan yang divalidasi otomatis oleh sistem.

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
- Membuat validasi otomatis terhadap teks `ketentuan_jaminan` (harus tetap murni informasi).
- Menggunakan hardcode jika data seharusnya berasal dari database.
- Membuat query yang mengakses data client lain.
- Mengubah arsitektur untuk task kecil.

## 13. Development Priority
```text
1. Project setup
2. Database foundation
3. Authentication
4. Customer Home
5. Equipment
6. Availability
7. Booking
8. Identity Guarantee
9. Payment
10. Shipping & Confirm Receipt
11. Return
12. Condition & Damage
13. Admin Profile & Branding
14. Admin Dashboard & Reports
15. Owner Admin Rental Account Management
16. Owner Dashboard
17. Testing
```

Vertical slice awal:
```text
PostgreSQL
    ↓
Laravel
    ↓
Products
    ↓
Customer Home
```

## 14. Testing Notes
Rencana pengujian awal mengacu pada bagian 7 proposal (Rencana Pengujian Awal / QA Plan Awal): pengujian fungsional (Black Box Testing) untuk seluruh fitur utama termasuk isolasi data antar-client, serta pengujian non-fungsional performa (target respons availability/booking ≤ 3 detik) dan kompatibilitas (Chrome, Firefox, Edge; desktop dan mobile).

## 15. Definition of Done
Fitur dianggap selesai jika UI, backend, database, validation, authorization, client isolation (jika relevan), error handling, dan manual testing sudah sesuai serta tidak merusak fitur lain.
