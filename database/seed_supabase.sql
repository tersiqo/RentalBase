-- Seed data RentalBase untuk dimasukkan ke Supabase tanpa menghapus data yang sudah ada.
-- Semua baris memakai ON CONFLICT DO NOTHING, jadi data yang sudah ada akan dilewati.
-- Aman dijalankan berulang kali (idempotent). JANGAN pakai ini bersamaan migrate:fresh/refresh.
--
-- Cara pakai: copy seluruh isi file ini ke Supabase SQL Editor, lalu Run.

BEGIN;

-- ============================ clients ============================
INSERT INTO clients (id, business_name, description, logo, subdomain, theme_color, status, created_at, updated_at) VALUES
(1, 'Baby Rental Studio', 'Sewa perlengkapan bayi profesional Malang.', 'clients/logos/babyrental.png', 'babyrental', '#F97316', 'aktif', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ subscriptions ============================
INSERT INTO subscriptions (id, client_id, plan_name, max_products, max_users, price, billing_cycle, start_date, end_date, status, payment_proof, payment_status, created_at, updated_at) VALUES
(1, 1, 'Pro Enterprise', 200, 15, 299000.00, 'bulanan', NOW() - INTERVAL '1 month', NOW() + INTERVAL '11 months', 'aktif', 'subscriptions/proofs/tf-sub-001.jpg', 'diverifikasi', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ users ============================
-- password untuk semua user: password123
INSERT INTO users (id, client_id, name, email, password, role, phone, address, avatar, status, created_at, updated_at) VALUES
(1, NULL, 'Owner RentalBase SaaS', 'owner@rentalbase.id', '$2y$10$4djiGl6tcj/qJclT7/uNIuaVujfBa6mgwajsJQF/rGnkHZ2DhXB8K', 'owner', '081122334455', 'HQ RentalBase Jakarta', NULL, 'aktif', NOW(), NOW()),
(2, 1, 'Budi Admin Baby Rental', 'budi@babyrental.com', '$2y$10$4djiGl6tcj/qJclT7/uNIuaVujfBa6mgwajsJQF/rGnkHZ2DhXB8K', 'admin_rental', '081234567890', 'Jl. Soekarno Hatta No. 45 Malang', NULL, 'aktif', NOW(), NOW()),
(3, 1, 'Siti Admin Baby Rental', 'siti@babyrental.com', '$2y$10$4djiGl6tcj/qJclT7/uNIuaVujfBa6mgwajsJQF/rGnkHZ2DhXB8K', 'admin_rental', '081299887766', 'Jl. Suhat No. 12 Malang', NULL, 'aktif', NOW(), NOW()),
(4, 1, 'Rizky Pelanggan', 'rizky@gmail.com', '$2y$10$4djiGl6tcj/qJclT7/uNIuaVujfBa6mgwajsJQF/rGnkHZ2DhXB8K', 'customer', '085711223344', 'Jl. Borobudur No. 88 Malang', NULL, 'aktif', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ client_registrations ============================
INSERT INTO client_registrations (id, business_name, description, subdomain, plan_name, admin_name, admin_email, admin_phone, admin_password, status, payment_proof, payment_status, rejection_reason, reviewed_by, reviewed_at, created_at, updated_at) VALUES
(1, 'Outdoor Malang Rental', 'Penyewaan tenda dan alat kamping gunung.', 'outdoormalang', 'Basic', 'Dedi Outdoor', 'dedi@outdoormalang.com', '081334455667', '$2y$10$4djiGl6tcj/qJclT7/uNIuaVujfBa6mgwajsJQF/rGnkHZ2DhXB8K', 'menunggu_verifikasi', 'registrations/proofs/tf-reg-dedi.jpg', 'menunggu', NULL, NULL, NULL, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ categories ============================
INSERT INTO categories (id, client_id, name, slug, description, created_at, updated_at) VALUES
(1, 1, 'Stroller', 'stroller', 'Kereta dorong bayi berbagai merk.', NOW(), NOW()),
(2, 1, 'Car Seat', 'car-seat', 'Kursi keselamatan bayi untuk di mobil.', NOW(), NOW()),
(3, 1, 'Box Bayi & Tempat Tidur', 'box-bayi', 'Tempat tidur dan box bayi.', NOW(), NOW()),
(4, 1, 'Sterilizer & Pompa ASI', 'sterilizer-pompa-asi', 'Alat pembersih dan perlengkapan menyusui.', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ products ============================
INSERT INTO products (id, client_id, category_id, name, slug, description, rental_price_per_day, deposit_fee, main_image, status, created_at, updated_at) VALUES
(1, 1, 1, 'Stroller Babyzen YOYO2 Black Frame', 'stroller-babyzen-yoyo2', 'Kereta dorong ringkas untuk travel.', 150000.00, 300000.00, 'products/babyzen-yoyo2.jpg', 'aktif', NOW(), NOW()),
(2, 1, 2, 'Car Seat Joie Spin 360 ISOFIX', 'car-seat-joie-spin-360', 'Car seat bisa berputar 360 derajat.', 125000.00, 250000.00, 'products/joie-spin360.jpg', 'aktif', NOW(), NOW()),
(3, 1, 3, 'Box Bayi Nuna Sena AIRE Timber', 'box-bayi-nuna-sena-aire', 'Box bayi portabel sangat nyaman.', 100000.00, 200000.00, 'products/nuna-sena.jpg', 'aktif', NOW(), NOW()),
(4, 1, 4, 'Haenim UV Sterilizer 4G Plus', 'haenim-uv-sterilizer-4g', 'Mesin sterilisasi botol dan perlengkapan.', 80000.00, 150000.00, 'products/haenim-4g.jpg', 'aktif', NOW(), NOW()),
(5, 1, 1, 'Cocolatte Pockit Gen 7', 'cocolatte-pockit-gen7', 'Stroller pockit ultra kompak.', 90000.00, 150000.00, 'products/cocolatte-pockit.jpg', 'aktif', NOW(), NOW()),
(6, 1, 4, 'Spectra S1 Plus Breast Pump', 'spectra-s1-plus', 'Pompa ASI elektrik hospital grade.', 110000.00, 200000.00, 'products/spectra-s1.jpg', 'aktif', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ equipment_units ============================
INSERT INTO equipment_units (id, client_id, product_id, unit_code, serial_number, "condition", status, created_at, updated_at) VALUES
(1, 1, 1, 'BBR-YOYO-01', 'SN-BBY-001', 'baik', 'tersedia', NOW(), NOW()),
(2, 1, 1, 'BBR-YOYO-02', 'SN-BBY-002', 'baik', 'disewa', NOW(), NOW()),
(3, 1, 2, 'BBR-JOIE-01', 'SN-JOIE-001', 'baik', 'tersedia', NOW(), NOW()),
(4, 1, 3, 'BBR-NUNA-01', 'SN-NUN-001', 'baik', 'tersedia', NOW(), NOW()),
(5, 1, 4, 'BBR-HNM-01', 'SN-HNM-001', 'baik', 'tersedia', NOW(), NOW()),
(6, 1, 5, 'BBR-CCLT-01', 'SN-CCL-001', 'baik', 'tersedia', NOW(), NOW()),
(7, 1, 6, 'BBR-SPC-01', 'SN-SPC-001', 'baik', 'tersedia', NOW(), NOW()),
(8, 1, 6, 'BBR-SPC-02', 'SN-SPC-002', 'baik', 'tersedia', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ client_payment_methods ============================
INSERT INTO client_payment_methods (id, client_id, bank_name, account_number, account_holder, qris_image, is_active, created_at, updated_at) VALUES
(1, 1, 'BCA', '8830991122', 'Baby Rental Studio Malang', 'payments/qris-bca.png', TRUE, NOW(), NOW()),
(2, 1, 'Mandiri', '1440008877665', 'Baby Rental Studio Malang', NULL, TRUE, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ notifications ============================
INSERT INTO notifications (id, client_id, user_id, title, message, type, is_read, created_at, updated_at) VALUES
(1, 1, 2, 'Pesanan Baru #ORD-20261001-001', 'Rizky Pelanggan telah membuat pesanan sewa baru.', 'pesanan', FALSE, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ carts ============================
INSERT INTO carts (id, client_id, user_id, created_at, updated_at) VALUES
(1, 1, 4, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ cart_items ============================
INSERT INTO cart_items (id, cart_id, product_id, start_date, end_date, duration_days, quantity, subtotal, created_at, updated_at) VALUES
(1, 1, 2, date_trunc('day', NOW()) + INTERVAL '5 days 9 hours', date_trunc('day', NOW()) + INTERVAL '7 days 9 hours', 2, 1, 500000.00, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ orders ============================
INSERT INTO orders (id, client_id, user_id, order_number, rental_start_date, rental_end_date, duration_days, total_amount, shipping_address, cancellation_reason, refund_status, refund_proof, status, created_at, updated_at) VALUES
(1, 1, 4, 'ORD-20261001-001', date_trunc('day', NOW()) + INTERVAL '1 day 10 hours', date_trunc('day', NOW()) + INTERVAL '3 days 10 hours', 2, 300000.00, 'Jl. Borobudur No. 88 Malang', NULL, 'tidak_ada', NULL, 'menunggu_pembayaran', NOW() - INTERVAL '2 hours', NOW() - INTERVAL '2 hours'),
(2, 1, 4, 'ORD-20261002-002', date_trunc('day', NOW()) + INTERVAL '2 days 9 hours', date_trunc('day', NOW()) + INTERVAL '3 days 9 hours', 1, 125000.00, 'Jl. Soekarno Hatta No. 10 Malang', NULL, 'tidak_ada', NULL, 'menunggu_konfirmasi', NOW() - INTERVAL '5 hours', NOW() - INTERVAL '5 hours'),
(3, 1, 4, 'ORD-20261003-003', date_trunc('day', NOW()) + INTERVAL '-2 days 10 hours', date_trunc('day', NOW()) + INTERVAL '2 days 10 hours', 4, 400000.00, 'Jl. MT Haryono No. 5 Malang', NULL, 'tidak_ada', NULL, 'sedang_disewa', NOW() - INTERVAL '3 days', NOW() - INTERVAL '3 days'),
(4, 1, 4, 'ORD-20261004-004', date_trunc('day', NOW()) + INTERVAL '1 day 8 hours', date_trunc('day', NOW()) + INTERVAL '3 days 8 hours', 2, 160000.00, 'Jl. Sigura-gura No. 2 Malang', NULL, 'tidak_ada', NULL, 'siap_kirim', NOW() - INTERVAL '1 day', NOW() - INTERVAL '1 day'),
(5, 1, 4, 'ORD-20261005-005', date_trunc('day', NOW()) + INTERVAL '5 days 12 hours', date_trunc('day', NOW()) + INTERVAL '10 days 12 hours', 5, 450000.00, 'Jl. Galunggung No. 4 Malang', NULL, 'tidak_ada', NULL, 'menunggu_pembayaran', NOW() - INTERVAL '30 minutes', NOW() - INTERVAL '30 minutes')
ON CONFLICT DO NOTHING;

-- ============================ order_items ============================
INSERT INTO order_items (id, order_id, product_id, quantity, price_per_day, subtotal, created_at, updated_at) VALUES
(1, 1, 1, 1, 150000.00, 300000.00, NOW(), NOW()),
(2, 2, 2, 1, 125000.00, 125000.00, NOW(), NOW()),
(3, 3, 3, 1, 100000.00, 400000.00, NOW(), NOW()),
(4, 4, 4, 1, 80000.00, 160000.00, NOW(), NOW()),
(5, 5, 5, 1, 90000.00, 450000.00, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ order_item_units ============================
INSERT INTO order_item_units (id, order_item_id, equipment_unit_id, created_at, updated_at) VALUES
(1, 1, 2, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ identity_guarantees ============================
INSERT INTO identity_guarantees (id, order_id, customer_id, full_name, identity_number, identity_document_image, face_image, identity_address, status, reviewed_by, created_at, updated_at) VALUES
(1, 1, 4, 'Rizky Pelanggan', '3573019988770001', 'guarantees/ktp-rizky.jpg', 'guarantees/selfie-rizky.jpg', 'Jl. Borobudur No. 88 Malang', 'diverifikasi', 3, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ payments ============================
INSERT INTO payments (id, order_id, client_payment_method_id, amount, payment_proof, status, notes, verified_by, paid_at, verified_at, created_at, updated_at) VALUES
(1, 1, 1, 300000.00, 'payments/proofs/tf-rizky-001.jpg', 'dikonfirmasi', 'Transfer via M-BCA senilai 300rb (Biaya Sewa 2 Hari)', 3, NOW() - INTERVAL '2 hours', NOW() - INTERVAL '1 hour', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ shipments ============================
INSERT INTO shipments (id, order_id, shipping_method, tracking_number, delivery_address, courier_name, status, shipped_at, delivered_at, created_at, updated_at) VALUES
(1, 1, 'ambil_di_toko', NULL, 'Diambil langsung oleh Rizky di toko Baby Rental Studio', NULL, 'siap_diambil', NULL, NULL, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ returns ============================
INSERT INTO "returns" (id, order_id, return_method, courier_name, tracking_number, return_date, late_hours, calculated_late_fee, late_fee_amount, late_fee_status, late_fee_proof, return_status, notes, processed_by, created_at, updated_at) VALUES
(1, 1, 'langsung', NULL, NULL, date_trunc('day', NOW()) + INTERVAL '3 days 10 hours', 0, 0.00, 0.00, 'tidak_ada', NULL, 'menunggu_pengecekan', NULL, NULL, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ condition_checks ============================
INSERT INTO condition_checks (id, equipment_unit_id, check_type, "condition", notes, image, checked_by, created_at, updated_at) VALUES
(1, 2, 'sebelum_sewa', 'baik', 'Roda lancar, kain bersih, sudah disterilkan UV.', 'checks/yoyo-02-before.jpg', 3, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ damage_reports ============================
INSERT INTO damage_reports (id, order_id, equipment_unit_id, description, image, reported_by, created_at, updated_at) VALUES
(1, 1, 2, 'Terdapat noda makanan membandel di dudukan.', 'damages/stain-seat.jpg', 3, NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ damage_cases ============================
INSERT INTO damage_cases (id, damage_report_id, repair_cost, fine_amount, payment_status, case_status, resolution_notes, created_at, updated_at) VALUES
(1, 1, 50000.00, 50000.00, 'belum_dibayar', 'terbuka', 'Biaya cuci khusus Rp 50rb + denda kotor Rp 50rb ditagihkan ke customer.', NOW(), NOW())
ON CONFLICT DO NOTHING;

-- ============================ sinkronisasi sequence ============================
-- Agar auto-increment berikutnya lanjut setelah id terbesar, tidak bentrok.
SELECT setval('clients_id_seq', (SELECT COALESCE(MAX(id), 1) FROM clients), true);
SELECT setval('subscriptions_id_seq', (SELECT COALESCE(MAX(id), 1) FROM subscriptions), true);
SELECT setval('users_id_seq', (SELECT COALESCE(MAX(id), 1) FROM users), true);
SELECT setval('client_registrations_id_seq', (SELECT COALESCE(MAX(id), 1) FROM client_registrations), true);
SELECT setval('categories_id_seq', (SELECT COALESCE(MAX(id), 1) FROM categories), true);
SELECT setval('products_id_seq', (SELECT COALESCE(MAX(id), 1) FROM products), true);
SELECT setval('equipment_units_id_seq', (SELECT COALESCE(MAX(id), 1) FROM equipment_units), true);
SELECT setval('client_payment_methods_id_seq', (SELECT COALESCE(MAX(id), 1) FROM client_payment_methods), true);
SELECT setval('notifications_id_seq', (SELECT COALESCE(MAX(id), 1) FROM notifications), true);
SELECT setval('carts_id_seq', (SELECT COALESCE(MAX(id), 1) FROM carts), true);
SELECT setval('cart_items_id_seq', (SELECT COALESCE(MAX(id), 1) FROM cart_items), true);
SELECT setval('orders_id_seq', (SELECT COALESCE(MAX(id), 1) FROM orders), true);
SELECT setval('order_items_id_seq', (SELECT COALESCE(MAX(id), 1) FROM order_items), true);
SELECT setval('order_item_units_id_seq', (SELECT COALESCE(MAX(id), 1) FROM order_item_units), true);
SELECT setval('identity_guarantees_id_seq', (SELECT COALESCE(MAX(id), 1) FROM identity_guarantees), true);
SELECT setval('payments_id_seq', (SELECT COALESCE(MAX(id), 1) FROM payments), true);
SELECT setval('shipments_id_seq', (SELECT COALESCE(MAX(id), 1) FROM shipments), true);
SELECT setval('returns_id_seq', (SELECT COALESCE(MAX(id), 1) FROM "returns"), true);
SELECT setval('condition_checks_id_seq', (SELECT COALESCE(MAX(id), 1) FROM condition_checks), true);
SELECT setval('damage_reports_id_seq', (SELECT COALESCE(MAX(id), 1) FROM damage_reports), true);
SELECT setval('damage_cases_id_seq', (SELECT COALESCE(MAX(id), 1) FROM damage_cases), true);

COMMIT;
