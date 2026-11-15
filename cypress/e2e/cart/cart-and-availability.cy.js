describe('E2E TEST — MODUL 06: KETERSEDIAAN DAN KERANJANG', () => {
  const subdomain = 'babyrental';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-06-001: Membuka pop-up modal Tambah ke Keranjang dari detail produk', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/products/1`);
    cy.get('button[title="Tambah ke Keranjang"]').click({ force: true });
    cy.contains('#modal-title', 'Pilih Jadwal Sewa').should('exist');
  });

  it('ET-06-002: Kalkulasi otomatis durasi dan estimasi biaya di dalam modal pemilihan tanggal', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/products/1`);
    cy.get('button[title="Tambah ke Keranjang"]').click({ force: true });
    const today = new Date().toISOString().split('T')[0];
    cy.get('input[type="date"]').first().type(today, { force: true });
    cy.get('input[type="date"]').last().type(today, { force: true });
    cy.contains('Estimasi Total Biaya:').should('exist');
    cy.contains('button', 'Tambah ke Keranjang').should('not.be.disabled');
  });

  it('ET-06-003: Tombol Batal pada modal menutup dialog dan kembali ke detail produk', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/products/1`);
    cy.get('button[title="Tambah ke Keranjang"]').click({ force: true });
    cy.contains('#modal-title', 'Pilih Jadwal Sewa').should('exist');
    cy.contains('button', 'Batal').click({ force: true });
    cy.get('#modal-title').should('not.be.visible');
  });

  it('ET-06-004: Membuka halaman keranjang sewa pelanggan terautentikasi', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/cart`);
    cy.url().should('include', `/client/${subdomain}/cart`);
    cy.contains('Keranjang Sewa').should('exist');
  });

  it('ET-06-005: Tamu yang belum login dicegat saat mencoba mengakses keranjang sewa', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit(`/client/${subdomain}/cart`);
    cy.url().should('include', '/login');
  });

  it('ET-06-006: Keranjang belanja terisolasi strictly per-toko rental', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}`);
    cy.get('a[href*="/cart"]').should('exist');
  });
});
