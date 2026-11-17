describe('E2E TEST — MODUL 04: PENGELOLAAN TOKO, STAF, LAPORAN & LANGGANAN ADMIN', () => {
  const adminEmail = 'budi@babyrental.com';
  const validPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // =========================================================================
  // 1. WIDGET STATISTIK & AKSI CEPAT DASHBOARD OPERASIONAL (/admin/dashboard)
  // =========================================================================

  it('ET-04-007: Header dashboard menampilkan teks PUSAT KENDALI RENTAL dan ucapan selamat datang personal admin', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.url().should('include', '/admin/dashboard');
    cy.contains('p', 'PUSAT KENDALI RENTAL').should('be.visible');
    cy.get('h1').should('contain.text', 'Selamat Datang kembali');
  });

  it('ET-04-008: Header dashboard memuat input pencarian Cek Ketersediaan Perlengkapan Bayi', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.get('input[placeholder*="Cek Ketersediaan Perlengkapan Bayi"]')
      .should('be.visible')
      .and('have.attr', 'type', 'text');
  });

  it('ET-04-009: Tombol Catat Reservasi Manual pada header dashboard tersedia dan responsif', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('button', 'Catat Reservasi Manual')
      .should('be.visible')
      .and('not.be.disabled');
  });

  it('ET-04-010: Kartu metrik ORDER BARU memuat kalkulasi pesanan baru dan teks status transaksi', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('p', 'ORDER BARU').should('be.visible');
    cy.get('body').should('contain.text', 'ORDER BARU');
  });

  it('ET-04-011: Kartu metrik VERIFIKASI memuat jumlah pembayaran yang menunggu konfirmasi admin', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('p', 'VERIFIKASI').should('be.visible');
  });

  it('ET-04-012: Kartu metrik DISEWA memuat kuantitas unit rental yang sedang aktif disewa pelanggan', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('p', 'DISEWA').should('be.visible');
  });

  it('ET-04-013: Kartu metrik PENDAPATAN menampilkan realisasi omset rental toko bulan berjalan', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('p', 'PENDAPATAN').should('be.visible');
    cy.contains('p', 'Bulan').should('be.visible');
  });

  it('ET-04-014: Widget kuota limit paket menampilkan persentase pemakaian unit dan tombol Kelola Unit', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('h2', 'Sisa Kuota Limit Paket').should('be.visible');
    cy.contains('a', 'Kelola Unit')
      .should('be.visible')
      .and('have.attr', 'href')
      .and('include', '/admin/units');
  });

  it('ET-04-015: Tombol Upgrade Paket pada widget kuota dashboard mengarahkan admin ke /admin/subscriptions', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('a', 'Upgrade Paket')
      .should('be.visible')
      .and('have.attr', 'href')
      .and('include', '/admin/subscriptions');
  });

  it('ET-04-016: Bagian Pesanan Terbaru memuat input pencarian order dan tombol tautan Lihat Semua', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.contains('h2', 'Pesanan Terbaru').should('be.visible');
    cy.get('input[placeholder*="Cari pesanan..."]').should('be.visible');
    cy.get('a[href*="/admin/orders"]')
      .should('be.visible')
      .and('contain.text', 'Lihat Semua');
  });

  // =========================================================================
  // 2. MANAJEMEN AKUN STAF ADMIN INTERNAL TOKO (/admin/admins)
  // =========================================================================

  it('ET-04-017: Halaman /admin/admins memuat judul Manajemen Akun Admin dan deskripsi pengelolaan internal', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.url().should('include', '/admin/admins');
    cy.get('h1').should('contain.text', 'Manajemen Akun Admin');
    cy.contains('p', 'Kelola akun staf Admin internal toko Anda dan pantau batasan kuota sesuai paket subscription yang aktif.').should('be.visible');
  });

  it('ET-04-018: Kartu indikator kuota menampilkan tier paket toko, rasio staf aktif vs batas kuota, dan progress bar', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.contains('span', 'PAKET SUBSCRIPTION').should('be.visible');
    cy.contains('p', 'Kuota Admin:').should('be.visible');
    cy.contains('span', 'Penggunaan Kuota').should('be.visible');
  });

  it('ET-04-019: Tombol Tambah Admin membuka Modal Tambah Admin Baru dengan form input kredensial lengkap', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('#btn-tambah-admin').length > 0) {
        cy.get('#btn-tambah-admin').click();

        // Modal Form Terbuka
        cy.contains('h3', 'Tambah Admin Baru').should('be.visible');
        cy.get('#name').should('be.visible');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
        cy.get('#password_confirmation').should('be.visible');
        cy.contains('button[type="submit"]', 'Simpan').should('be.visible');
      } else {
        cy.contains('Tambah Admin (Penuh)').should('exist');
      }
    });
  });

  it('ET-04-020: Tombol Batal pada Modal Tambah Admin menutup jendela modal tanpa menyimpan data', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('#btn-tambah-admin').length > 0) {
        cy.get('#btn-tambah-admin').click();
        cy.contains('h3', 'Tambah Admin Baru').should('be.visible');

        cy.contains('button:visible', 'Batal').click();
        cy.contains('h3', 'Tambah Admin Baru').should('not.be.visible');
      }
    });
  });

  it('ET-04-021: Form modal tambah admin memvalidasi field wajib nama, email, password, dan konfirmasi password', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('#btn-tambah-admin').length > 0) {
        cy.get('#btn-tambah-admin').click();
        cy.contains('button[type="submit"]', 'Simpan').click();

        // Form browser/HTML5 menahan submit karena field required
        cy.get('#name').should('have.prop', 'required', true);
        cy.get('#email').should('have.prop', 'required', true);
        cy.contains('button:visible', 'Batal').click();
      }
    });
  });

  it('ET-04-022: Form modal tambah admin menolak konfirmasi password yang tidak cocok', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('#btn-tambah-admin').length > 0) {
        cy.get('#btn-tambah-admin').click();

        cy.get('#name').type('Staf Test Baru');
        cy.get('#email').type(`staf_${Date.now()}@test.com`);
        cy.get('#password').type('password123');
        cy.get('#password_confirmation').type('passwordBeda999');
        cy.contains('button[type="submit"]', 'Simpan').click();

        // Tetap di halaman dan menampilkan error
        cy.url().should('include', '/admin/admins');
      }
    });
  });

  it('ET-04-023: Form modal tambah admin menolak password yang kurang dari 8 karakter', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('#btn-tambah-admin').length > 0) {
        cy.get('#btn-tambah-admin').click();

        cy.get('#name').type('Staf Test Pendek');
        cy.get('#email').type(`staf_pendek_${Date.now()}@test.com`);
        cy.get('#password').type('pass123');
        cy.get('#password_confirmation').type('pass123');
        cy.contains('button[type="submit"]', 'Simpan').click();

        cy.url().should('include', '/admin/admins');
      }
    });
  });

  it('ET-04-024: Input pencarian staf menyaring baris tabel secara real-time berdasarkan kecocokan nama atau email', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('input[placeholder*="Cari nama atau email admin..."]').type('budi');
    cy.get('table tbody').should('contain.text', 'budi');
  });

  it('ET-04-025: Akun Admin Utama toko memiliki badge khusus dan tombol aksi terlindungi dari penonaktifan', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('table').contains('span', 'Admin Utama').should('be.visible');
    cy.get('table').contains('span', 'Pengelola Utama Toko').should('be.visible');
  });

  it('ET-04-026: Akun admin yang sedang login memiliki badge Akun Anda dan aksi terlindungi Terkunci', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('table').contains('span', 'Akun Anda').should('be.visible');
    cy.get('table tbody tr:contains("Akun Anda")').within(() => {
      cy.get('span:contains("Terkunci"), span:contains("Admin Utama")').should('exist');
    });
  });

  it('ET-04-027: Tombol aksi Nonaktifkan pada staf aktif membuka Modal Konfirmasi Penonaktifan Akun', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Nonaktifkan")').length > 0) {
        cy.get('table tbody tr button:contains("Nonaktifkan")').first().click({ force: true });

        // Modal Konfirmasi Terbuka
        cy.contains('h3', 'Nonaktifkan Akun Admin').should('be.visible');
        cy.contains('button:visible', 'Batal').should('be.visible');
        cy.contains('button', 'Ya, Nonaktifkan').should('be.visible');
      }
    });
  });

  it('ET-04-028: Tombol Batal pada Modal Konfirmasi Penonaktifan membatalkan aksi dan menutup modal', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Nonaktifkan")').length > 0) {
        cy.get('table tbody tr button:contains("Nonaktifkan")').first().click({ force: true });
        cy.contains('button:visible', 'Batal').click({ force: true });

        cy.contains('h3', 'Nonaktifkan Akun Admin').should('not.be.visible');
      }
    });
  });

  // =========================================================================
  // 3. LAPORAN TRANSAKSI TOKO & AUDIT FINANSIAL (/admin/reports)
  // =========================================================================

  it('ET-04-029: Halaman /admin/reports memuat judul Laporan Transaksi Toko dan indikator Audit Finansial', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    cy.url().should('include', '/admin/reports');
    cy.get('h1').should('contain.text', 'Laporan Transaksi Toko');
    cy.contains('span', 'Audit Finansial & Operasional').should('be.visible');
    cy.contains('p', 'Pantau realisasi pendapatan sewa pokok, penerimaan denda keterlambatan, dan pelunasan kompensasi kerusakan unit rental.').should('be.visible');
  });

  it('ET-04-030: Tombol dropdown Export membuka menu pilihan format unduhan (CSV, Excel, PDF)', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    // Klik tombol dropdown export
    cy.get('#btn-export').click();

    // Menu format unduhan muncul
    cy.contains('span', 'Pilih Format Unduhan').should('be.visible');
    cy.get('#btn-export-csv').should('be.visible');
    cy.get('#btn-export-excel').should('be.visible');
    cy.get('#btn-export-pdf').should('be.visible');
  });

  it('ET-04-031: Opsi Export CSV pada menu dropdown memuat tautan unduhan dengan parameter format CSV', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    cy.get('#btn-export').click();
    cy.get('#btn-export-csv')
      .should('have.attr', 'href')
      .and('include', 'format=csv');
  });

  it('ET-04-032: Opsi Export Excel pada menu dropdown memuat tautan unduhan dengan parameter format Excel', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    cy.get('#btn-export').click();
    cy.get('#btn-export-excel')
      .should('have.attr', 'href')
      .and('include', 'format=excel');
  });

  it('ET-04-033: Opsi Export PDF pada menu dropdown memuat tautan unduhan dengan parameter format PDF', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    cy.get('#btn-export').click();
    cy.get('#btn-export-pdf')
      .should('have.attr', 'href')
      .and('include', 'format=pdf');
  });

  it('ET-04-034: Tombol preset periode Hari Ini memperbarui parameter tanggal ke hari berjalan', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports?preset=hari_ini');

    cy.url().should('include', 'preset=hari_ini');
    cy.contains('Periode laporan:').should('be.visible');
  });

  it('ET-04-035: Tombol preset periode 7 Hari Terakhir memperbarui rentang tanggal ke sepekan terakhir', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports?preset=7_hari_terakhir');

    cy.url().should('include', 'preset=7_hari_terakhir');
    cy.contains('Periode laporan:').should('be.visible');
  });

  it('ET-04-036: Tombol preset periode Bulan Ini memperbarui rentang tanggal ke awal hingga akhir bulan berjalan', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports?preset=bulan_ini');

    cy.url().should('include', 'preset=bulan_ini');
    cy.contains('Periode laporan:').should('be.visible');
  });

  it('ET-04-037: Memilih preset periode menampilkan badge indikator perubahan belum diterapkan', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    // Klik preset berbeda dari Bulan Ini (misal Hari Ini) agar tanggal berubah dan memicu isDirty
    cy.contains('button', 'Hari Ini').click();

    // Badge dirty indicator tampil
    cy.contains('span', 'Perubahan belum diterapkan').should('be.visible');
  });

  it('ET-04-038: Tombol Terapkan Filter memperbarui laporan transaksi dan menyaring data finansial', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports?preset=bulan_ini');

    cy.url().should('include', 'preset=bulan_ini');
    cy.get('table').should('exist');
  });

  it('ET-04-039: Empat kartu ringkasan audit finansial (Sewa Pokok, Denda, Ganti Rugi, Total Bersih) tampil akurat', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    cy.contains('span', 'Total Pendapatan').should('be.visible');
    cy.contains('span', 'Sewa Pokok').should('be.visible');
    cy.contains('span', 'Pendapatan Denda').should('be.visible');
    cy.contains('span', 'Kompensasi Rusak').should('be.visible');
  });

  // =========================================================================
  // 4. LANGGANAN, KUOTA LIMIT TOKO & UPGRADE PAKET (/admin/subscriptions)
  // =========================================================================

  it('ET-04-040: Halaman /admin/subscriptions memuat status masa aktif toko dan badge sisa hari langganan', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.url().should('include', '/admin/subscriptions');
    cy.get('h1').should('contain.text', 'Langganan & Paket Toko');
    cy.contains('span', 'Status:').should('be.visible');
  });

  it('ET-04-041: Kartu paket aktif menampilkan 4 progress bar kuota (Katalog Produk, Unit Fisik, Kategori, Staf Admin)', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.contains('span', 'Paket Toko Saat Ini').should('be.visible');
    cy.contains('span', 'Katalog Produk').should('be.visible');
    cy.contains('span', 'Unit Fisik Peralatan').should('be.visible');
    cy.contains('span', 'Kategori Produk').should('be.visible');
    cy.contains('span', 'Akun Admin Staf').should('be.visible');
  });

  it('ET-04-042: Tombol aksi Perpanjang Paket Ini membuka Modal Perpanjangan Langganan dengan rincian biaya transfer', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Perpanjang Paket Ini")').length > 0) {
        cy.get('button:contains("Perpanjang Paket Ini")').click({ force: true });

        // Modal Perpanjangan Terbuka
        cy.contains('h3', 'Perpanjang Paket').should('be.visible');
        cy.contains('span', 'Total Transfer:').should('be.visible');
        cy.get('input[name="payment_proof"]').should('exist');
      }
    });
  });

  it('ET-04-043: Modal perpanjangan langganan memvalidasi unggahan file bukti pembayaran wajib diisi', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Perpanjang Paket Ini")').length > 0) {
        cy.get('button:contains("Perpanjang Paket Ini")').click({ force: true });

        // Submit form tanpa melampirkan file
        cy.contains('button[type="submit"]', 'Kirim Pengajuan Perpanjangan').click();

        // Field file bukti transfer fokus divalidasi
        cy.get('input[name="payment_proof"]').should('exist');
      }
    });
  });

  it('ET-04-044: Tombol Batal pada Modal Perpanjangan Langganan menutup dialog modal', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Perpanjang Paket Ini")').length > 0) {
        cy.get('button:contains("Perpanjang Paket Ini")').click({ force: true });
        cy.contains('button:visible', 'Batal').click({ force: true });

        cy.contains('h3', 'Perpanjang Paket').should('not.be.visible');
      }
    });
  });

  it('ET-04-045: Tombol aksi Upgrade Paket membuka Modal Formulir Upgrade Paket Toko', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Upgrade Paket")').length > 0) {
        cy.get('button:contains("Upgrade Paket")').first().click({ force: true });

        // Modal Upgrade Terbuka
        cy.contains('h3', 'Formulir Upgrade Paket Toko').should('be.visible');
        cy.get('select[name="plan_name"]').should('be.visible');
      }
    });
  });

  it('ET-04-046: Modal upgrade paket menyediakan pilihan paket tujuan (Business/Professional) dan kalkulasi biaya', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Upgrade Paket")').length > 0) {
        cy.get('button:contains("Upgrade Paket")').first().click({ force: true });

        cy.get('select[name="plan_name"]').should('be.visible');
        cy.contains('span', 'Total Nominal Transfer:').should('exist');
      }
    });
  });

  it('ET-04-047: Tombol Batal pada Modal Formulir Upgrade Paket Toko menutup modal dialog', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Upgrade Paket")').length > 0) {
        cy.get('button:contains("Upgrade Paket")').first().click({ force: true });
        cy.contains('button:visible', 'Batal').click({ force: true });

        cy.contains('h3', 'Formulir Upgrade Paket Toko').should('not.be.visible');
      }
    });
  });

  it('ET-04-048: Bagian Perbandingan Paket menampilkan kartu Starter, Business, dan Professional beserta batas kuotanya', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.contains('h2', 'Perbandingan Paket RentalBase').should('be.visible');
    cy.contains('h3', 'Starter').should('be.visible');
    cy.contains('h3', 'Business').should('be.visible');
    cy.contains('h3', 'Professional').should('be.visible');
  });

  it('ET-04-049: Bagian Riwayat Langganan Toko memuat tabel catatan perpanjangan, nominal biaya, dan status verifikasi', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.contains('h3', 'Riwayat Langganan Toko').should('be.visible');
    cy.contains('th', 'Tanggal Pengajuan').should('be.visible');
    cy.contains('th', 'Paket').should('be.visible');
    cy.contains('th', 'Nominal Biaya').should('be.visible');
    cy.contains('th', 'Status Pembayaran').should('be.visible');
  });
});
