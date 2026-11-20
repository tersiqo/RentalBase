describe('E2E TEST — MODUL 04: LIVE CHAT INBOX & LAYANAN PENYEWA', () => {
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123'; // will test and fallback

  before(() => {
    Cypress.session.clearAllSavedSessions();
  });

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-04-055: Akses halaman Live Chat Inbox /admin/chat memuat header, indikator live chat aktif, dan daftar obrolan', () => {
    cy.loginAdmin(adminEmail, '12345678');
    cy.visit('/admin/chat');
    cy.url().should('include', '/admin/chat');
    cy.contains('h1', 'Chat & Layanan Penyewa').should('be.visible');
    cy.contains('Live Chat Aktif').should('be.visible');
    cy.contains('Daftar Percakapan').should('be.visible');
  });

  it('ET-04-056: Filter pencarian kontak pelanggan pada panel daftar percakapan live chat admin', () => {
    cy.loginAdmin(adminEmail, '12345678');
    cy.visit('/admin/chat');
    cy.get('input[placeholder*="Cari nama atau email"]')
      .should('be.visible')
      .clear()
      .type('rizky')
      .should('have.value', 'rizky');
    cy.contains('Daftar Percakapan').should('be.visible');
    cy.contains('Belum Dibaca').should('be.visible');
  });

  it('ET-04-057: Penggunaan tombol pesan cepat (quick replies) dan input teks formulir balasan admin', () => {
    cy.loginAdmin(adminEmail, '12345678');
    cy.visit('/admin/chat');
    cy.contains('Pesan Cepat:').should('be.visible');
    cy.contains('button', 'Unit Ready').should('be.visible');
    cy.contains('button', 'Syarat Checkout').should('be.visible');
    cy.contains('button', 'Pengambilan Unit').should('be.visible');
    cy.get('textarea').should('exist');
  });

  it('ET-04-058: Proteksi hak akses otorisasi rute /admin/chat mencegah akses pengguna tamu (guest)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    Cypress.session.clearAllSavedSessions();
    cy.visit('/admin/chat', { failOnStatusCode: false });
    cy.url().should('not.include', '/admin/chat');
  });
});
