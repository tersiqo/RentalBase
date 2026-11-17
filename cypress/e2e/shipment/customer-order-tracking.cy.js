describe('E2E TEST — MODUL 09: PESANAN SAYA & DETAIL PESANAN CUSTOMER', () => {
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-09-001: Proteksi rute pesanan saya mencegah pengunjung tamu membuka daftar transaksi', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/my-orders');
    cy.url().should('not.include', '/my-orders');
    cy.url().should('eq', `${Cypress.config().baseUrl}/`);
  });

  it('ET-09-002: Customer terautentikasi dapat membuka antarmuka riwayat pesanan saya', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.url().should('include', '/my-orders');
    cy.contains('h1', 'Pesanan Saya').should('be.visible');
    cy.contains('Pantau status penyewaan alat dan riwayat transaksi Anda').should('be.visible');
  });

  it('ET-09-003: Navigasi dan filter status pesanan sewa berdasarkan tab kategori transaksi', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.contains('button', 'Semua').should('be.visible');
    cy.contains('button', 'Belum Dibayar').should('be.visible').click({ force: true });
    cy.contains('button', 'Menunggu Verifikasi').should('be.visible').click({ force: true });
    cy.contains('button', 'Diproses').should('be.visible').click({ force: true });
    cy.contains('button', 'Selesai').should('be.visible').click({ force: true });
    cy.contains('button', 'Semua').click({ force: true });
  });

  it('ET-09-004: Membuka rincian pesanan via tombol Lihat detail dan memverifikasi komponen stepper & informasi pesanan', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Lihat detail")').length > 0) {
        cy.contains('button', 'Lihat detail').first().click({ force: true });
        cy.contains('Menunggu Konfirmasi').should('exist');
        cy.contains(/Status Jaminan Identitas|Identitas|Pengiriman|Jadwal Sewa/i).should('exist');
      } else {
        cy.contains(/Belum Ada Pesanan|Tidak ada pesanan/i).should('exist');
      }
    });
  });

  it('ET-09-005: Navigasi penutupan rincian pesanan untuk kembali ke daftar riwayat transaksi sewa', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Lihat detail")').length > 0) {
        cy.contains('button', 'Lihat detail').first().click({ force: true });
        cy.contains('button', 'Pesanan Saya').first().click({ force: true });
        cy.contains('h1', 'Pesanan Saya').should('be.visible');
      } else {
        cy.contains('h1', 'Pesanan Saya').should('be.visible');
      }
    });
  });
});
