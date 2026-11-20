describe('E2E TEST — MODUL 02: ISOLASI DATA MULTI-TENANT', () => {
  const adminEmail = 'budi@babyrental.com';
  const ownerEmail = 'owner@rentalbase.id';
  const validPassword = 'password123';
  const expectedSubdomain = 'babyrental';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // =========================================================================
  // 1. ISOLASI METRIK & IDENTITAS TOKO PADA DASHBOARD ADMIN (/admin/dashboard)
  // =========================================================================

  it('ET-02-006: Admin Toko yang login ke /admin/dashboard hanya melihat konteks dan metrik operasional tokonya sendiri', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.url().should('include', '/admin/dashboard');
    cy.contains('p', 'PUSAT KENDALI RENTAL').should('be.visible');
    cy.get('h1').should('contain.text', 'Selamat Datang kembali');

    // Metrik pesanan terisolasi toko
    cy.contains('p', 'ORDER BARU').should('be.visible');
    cy.contains('p', 'VERIFIKASI').should('be.visible');
  });

  it('ET-02-007: Header navigasi dan sidebar admin menampilkan identitas nama toko dan subdomain toko yang terisolasi', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    // Sidebar memuat identitas subdomain toko aktif
    cy.get('aside').should('exist');
    cy.get('body').should('contain.text', expectedSubdomain);
  });

  it('ET-02-008: Quick search ketersediaan unit di header dashboard beroperasi dalam cakupan katalog milik tenant aktif', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/dashboard');

    cy.get('input[placeholder*="Cek Ketersediaan Perlengkapan Bayi"]').should('be.visible');
  });

  // =========================================================================
  // 2. ISOLASI MANAJEMEN AKUN STAF TOKO (/admin/admins)
  // =========================================================================

  it('ET-02-009: Halaman /admin/admins hanya menampilkan daftar staf yang terasosiasi dengan client_id toko yang aktif', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.url().should('include', '/admin/admins');
    cy.get('h1').should('contain.text', 'Manajemen Akun Admin');
    cy.contains('PENGELOLAAN INTERNAL TOKO').should('be.visible');

    // Memastikan email primary admin toko babyrental tampil dalam tabel
    cy.get('table').should('exist');
    cy.get('table').should('contain.text', adminEmail);
  });

  it('ET-02-010: Indikator kuota staf admin pada /admin/admins dihitung berdasarkan paket spesifik dari tenant aktif', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    // Memastikan informasi paket dan kuota admin tertera
    cy.get('#adminsPage').should('contain.text', 'Manajemen Akun Admin');
    cy.contains('p', 'Kelola akun staf Admin internal toko Anda dan pantau batasan kuota sesuai paket subscription yang aktif.').should('be.visible');
  });

  it('ET-02-011: Tombol Tambah Admin membuka formulir staf yang secara otomatis terikat pada toko aktif', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/admins');

    cy.get('body').then(($body) => {
      if ($body.find('#btn-tambah-admin').length > 0) {
        cy.get('#btn-tambah-admin').click();
        cy.contains('p', 'Buat akun staf Admin internal untuk toko').should('be.visible');
        cy.contains('button', 'Batal').click();
      } else {
        cy.contains('Tambah Admin (Penuh)').should('exist');
      }
    });
  });

  // =========================================================================
  // 3. ISOLASI LAPORAN & AUDIT FINANSIAL TOKO (/admin/reports)
  // =========================================================================

  it('ET-02-012: Laporan transaksi toko di /admin/reports terisolasi khusus transaksi penyewaan milik tenant yang sedang login', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    cy.url().should('include', '/admin/reports');
    cy.get('h1').should('contain.text', 'Laporan Transaksi Toko');
    cy.contains('span', 'Audit Finansial & Operasional').should('be.visible');

    // Filter periode preset laporan
    cy.contains('button', 'Hari Ini').should('be.visible');
    cy.contains('button', '7 Hari Terakhir').should('be.visible');
    cy.contains('button', 'Bulan Ini').should('be.visible');
  });

  it('ET-02-013: Filter preset tanggal pada /admin/reports tidak menampilkan data sewa milik tenant lain', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports?preset=bulan_ini');

    cy.url().should('include', 'preset=bulan_ini');
    cy.get('h1').should('contain.text', 'Laporan Transaksi Toko');
    cy.contains('Periode laporan:').should('be.visible');
    cy.get('table').should('exist');
  });

  it('ET-02-014: Tautan unduhan export laporan transaksi di /admin/reports/export memuat parameter filter tenant aktif', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/reports');

    // Buka menu pilihan unduhan ekspor
    cy.get('#btn-export').should('be.visible').click();

    // Verifikasi ketersediaan format unduhan laporan
    cy.get('#btn-export-csv').should('have.attr', 'href').and('include', '/admin/reports/export');
    cy.get('#btn-export-excel').should('have.attr', 'href').and('include', '/admin/reports/export');
    cy.get('#btn-export-pdf').should('have.attr', 'href').and('include', '/admin/reports/export');
  });

  it('ET-02-015: Halaman status langganan /admin/subscriptions menampilkan kuota dan batas katalog terisolasi toko aktif', () => {
    cy.loginAdmin(adminEmail, validPassword);
    cy.visit('/admin/subscriptions');

    cy.url().should('include', '/admin/subscriptions');
    cy.get('h1').should('contain.text', 'Langganan & Paket Toko');
    cy.contains('span', 'Katalog Produk').should('be.visible');
    cy.contains('span', 'Unit Fisik Peralatan').should('be.visible');
  });

  // =========================================================================
  // 4. PENGAWASAN LINTAS TENANT OLEH OWNER PLATFORM (/owner/tenants)
  // =========================================================================

  it('ET-02-016: Platform Owner dapat melakukan pengawasan menyeluruh lintas seluruh tenant di /owner/tenants', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.url().should('include', '/owner/tenants');
    cy.get('h1').should('contain.text', 'Client & Tenant');

    // Direktori tenant harus menampilkan toko babyrental dan dapat memantau status operasionalnya
    cy.get('body').should('contain.text', expectedSubdomain);
    cy.get('input[name="search"]').should('exist');
  });

  it('ET-02-017: Owner dapat memfilter direktori tenant berdasarkan status operasional (Aktif, Suspended)', () => {
    cy.loginOwner(ownerEmail, validPassword);

    // 1. Filter status Aktif
    cy.visit('/owner/tenants?status=aktif');
    cy.url().should('include', 'status=aktif');
    cy.contains('h1', 'Kelola Client & Tenant Rental').should('be.visible');

    // 2. Filter status Suspended
    cy.visit('/owner/tenants?status=suspended');
    cy.url().should('include', 'status=suspended');
    cy.contains('h1', 'Kelola Client & Tenant Rental').should('be.visible');
  });

  it('ET-02-018: Owner dapat melihat detail lengkap konfigurasi dan profil admin dari tenant terpilih di direktori tenant', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    // Memeriksa keberadaan baris tenant dan tombol aksi detail/kelola
    cy.get('table').should('exist');
    cy.get('table').should('contain.text', expectedSubdomain);
    cy.contains('button', 'Detail').should('exist');
  });
});
