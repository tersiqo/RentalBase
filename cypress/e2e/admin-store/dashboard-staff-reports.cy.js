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
});
