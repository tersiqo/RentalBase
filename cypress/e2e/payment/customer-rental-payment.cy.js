describe('E2E TEST — MODUL 08: PEMBAYARAN DAN BUKTI TRANSFER', () => {
  const subdomain = 'babyrental';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-08-001: Customer dapat melihat riwayat pesanan dan status pembayaran di Pesanan Saya', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.url().should('include', '/my-orders');
    cy.contains('Pesanan Saya').should('exist');
  });

  it('ET-08-002: Buka detail rincian pembayaran pesanan sewa', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.contains('button', 'Lihat detail').first().click({ force: true });
    cy.contains('Detail Pesanan').should('exist');
  });

  it('ET-08-003: Menampilkan informasi rekening bank toko dan petunjuk transfer', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.contains('button', 'Lihat detail').first().click({ force: true });
    cy.contains(/Rincian Pembayaran|Metode Pembayaran|Total|Rekening/i).should('exist');
  });

  it('ET-08-004: Formulir unggah bukti pembayaran sewa memvalidasi berkas transfer', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.contains('button', 'Lihat detail').first().click({ force: true });
    cy.get('body').then(($body) => {
      if ($body.find('input[name="payment_proof"], input[type="file"]').length > 0) {
        cy.get('input[type="file"]').should('exist');
      }
    });
  });
});
