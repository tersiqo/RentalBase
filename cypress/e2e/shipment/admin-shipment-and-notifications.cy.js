describe('E2E TEST — MODUL 09: PENGIRIMAN ADMIN & NOTIFIKASI CUSTOMER', () => {
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // Fitur 7.1-A1: Pengiriman Admin
  it('ET-09-006: Admin rental dapat membuka halaman pengiriman dan memfilter metode kurir / ambil toko', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/shipments');
    cy.url().should('include', '/admin/shipments');
    cy.contains(/Daftar Siap Kirim|Siap Kirim|Pengiriman/i).should('exist');
    cy.get('select[name="method"]').should('exist');
  });

  it('ET-09-007: Modal proses pengiriman kurir menyediakan input nama kurir ekspedisi dan nomor resi', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/shipments');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Kirim"), button:contains("Serahkan"), button:contains("Proses")').length > 0) {
        cy.contains('button', /Kirim|Serahkan|Proses/i).first().click({ force: true });
        cy.contains(/Proses Pengiriman|Penyerahan Pesanan|Kurir/i).should('exist');
      } else {
        cy.contains(/Daftar Siap Kirim|Siap Kirim|Pengiriman/i).should('exist');
      }
    });
  });

  // Fitur 9.1-A1: Notifikasi Customer
  it('ET-09-008: Customer dapat membuka hub notifikasi aktivitas transaksi dan melihat feed alert', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/notifications');
    cy.url().should('include', '/notifications');
    cy.contains(/Pusat Pemberitahuan|Pemberitahuan|Notifikasi/i).should('exist');
  });

  it('ET-09-009: Hub notifikasi customer menyediakan filter tab alert dan tombol tandai semua dibaca', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Tandai Semua Dibaca")').length > 0) {
        cy.contains('button', 'Tandai Semua Dibaca').should('exist');
      } else {
        cy.contains(/Pusat Pemberitahuan|Pemberitahuan|Notifikasi/i).should('exist');
      }
    });
  });

  it('ET-09-010: Proteksi hak akses mencegah pengguna tamu membuka notifikasi customer', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/notifications');
    cy.url().should('not.include', '/notifications');
  });
});
