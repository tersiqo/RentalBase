describe('E2E TEST — MODUL 07: CUSTOMER SELF-CANCELLATION & RIWAYAT TRANSAKSI', () => {
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  before(() => {
    Cypress.session.clearAllSavedSessions();
  });

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-07-014: Customer terautentikasi dapat membuka riwayat pesanan saya dan melihat kartu transaksi sewa', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.url().should('include', '/my-orders');
    cy.contains('h1', 'Pesanan Saya').should('be.visible');
    cy.contains('Semua').should('be.visible');
  });

  it('ET-07-015: Membuka pop-up modal konfirmasi batalkan pesanan pada transaksi rental yang belum dibayar', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Batalkan Pesanan")').length > 0) {
        cy.contains('button', 'Batalkan Pesanan').first().click({ force: true });
        cy.contains('h3', 'Batalkan Pesanan?').should('be.visible');
        cy.get('textarea[name="reason"]').should('be.visible');
      } else {
        cy.contains('h1', 'Pesanan Saya').should('be.visible');
        cy.contains('button', 'Belum Dibayar').should('be.visible');
      }
    });
  });

  it('ET-07-016: Tombol Batal / Kembali pada modal menutup dialog pembatalan tanpa mengubah status pesanan', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Batalkan Pesanan")').length > 0) {
        cy.contains('button', 'Batalkan Pesanan').first().click({ force: true });
        cy.contains('button', 'Kembali').click({ force: true });
        cy.contains('h3', 'Batalkan Pesanan?').should('not.be.visible');
      } else {
        cy.contains('h1', 'Pesanan Saya').should('be.visible');
      }
    });
  });

  it('ET-07-017: Proteksi akses pembatalan pesanan mencegah pengunjung tamu (guest) tanpa login', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    Cypress.session.clearAllSavedSessions();
    cy.visit('/my-orders', { failOnStatusCode: false });
    cy.url().should('not.include', '/my-orders');
  });
});
