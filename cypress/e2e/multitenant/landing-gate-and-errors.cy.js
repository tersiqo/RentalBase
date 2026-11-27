describe('E2E TEST — MODUL 02: LANDING PAGE, LOGIN GATE & HALAMAN ERROR', () => {
  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-02-001: Tampilan antarmuka Landing Page utama dan navigasi brand RentalBase', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
    cy.url().should('eq', `${Cypress.config().baseUrl}/`);
    cy.contains('RentalBase').should('be.visible');
    cy.contains('Fitur').should('be.visible');
    cy.contains('Mitra Rental').should('be.visible');
    cy.contains('Harga').should('be.visible');
    cy.contains('Kontak').should('be.visible');
    cy.contains('a', 'Login').should('be.visible');
  });

  it('ET-02-002: Navigasi tombol CTA membuka modal pendaftaran akun baru', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
    cy.contains('button', 'Daftar Sekarang').first().click({ force: true });
    cy.contains('Pilih Jenis Pendaftaran').should('be.visible');
    cy.contains(/Sebagai (Customer|Pelanggan)/i).should('be.visible');
    cy.contains('Sebagai Pemilik Toko').should('be.visible');
    cy.contains('a', 'Buka Toko Rental (Tenant)').should('have.attr', 'href').and('include', '/register-tenant');
  });

  it('ET-02-003: Login gate mengarahkan pengunjung tamu saat mencoba mengakses rute terproteksi', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/my-orders');
    cy.url().should('not.include', '/my-orders');
    cy.url().should('eq', `${Cypress.config().baseUrl}/`);
  });

  it('ET-02-004: Menampilkan respon error 404 saat mengakses subdomain tenant palsu', () => {
    cy.request({
      url: '/client/toko-palsu-tidak-terdaftar-xyz999',
      failOnStatusCode: false,
    }).its('status').should('equal', 404);

    cy.visit('/client/toko-palsu-tidak-terdaftar-xyz999', { failOnStatusCode: false });
    cy.get('body').should('contain.text', '404');
  });

  it('ET-02-005: Proteksi akses silang role saat customer membuka dashboard admin atau owner', () => {
    cy.loginCustomer('rizky@gmail.com', 'password123');

    // Coba akses portal admin rental
    cy.visit('/admin/dashboard');
    cy.url().should('not.include', '/admin/dashboard');

    // Coba akses portal platform owner
    cy.visit('/owner/dashboard');
    cy.url().should('not.include', '/owner/dashboard');
  });
});
