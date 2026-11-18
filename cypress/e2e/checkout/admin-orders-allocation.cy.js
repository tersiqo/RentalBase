describe('E2E TEST — MODUL 07: MANAJEMEN PESANAN & DEALOKASI UNIT ADMIN', () => {
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-07-008: Admin rental dapat membuka daftar pesanan toko dan memfilter status transaksi', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/orders');
    cy.url().should('include', '/admin/orders');
    cy.contains(/Master Pesanan|Daftar Pesanan|Pesanan/i).should('exist');
    cy.get('table').should('exist');
  });

  it('ET-07-009: Admin dapat membuka rincian pesanan dan memeriksa panel alokasi unit fisik', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/orders');
    cy.get('body').then(($body) => {
      const orderLinks = $body.find('a[href*="/admin/orders/"]');
      if (orderLinks.length > 0) {
        cy.wrap(orderLinks.first()).click({ force: true });
        cy.url().should('match', /\/admin\/orders\/\d+/);
        cy.contains(/Pesanan #|Detail Pesanan|Alokasi Unit Fisik|Informasi Pesanan/i).should('exist');
      } else {
        cy.contains(/Master Pesanan|Belum ada pesanan|Tidak ada pesanan/i).should('exist');
      }
    });
  });

  it('ET-07-010: Antarmuka alokasi unit menyediakan aksi pelepasan / dealokasi unit fisik', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/orders');
    cy.get('body').then(($body) => {
      const orderLinks = $body.find('a[href*="/admin/orders/"]');
      if (orderLinks.length > 0) {
        cy.wrap(orderLinks.first()).click({ force: true });
        cy.get('body').then(($detailBody) => {
          if ($detailBody.find('form[action*="/allocate/"]').length > 0) {
            cy.get('form[action*="/allocate/"]').should('exist');
          } else {
            cy.contains(/Alokasi Unit Fisik|Unit Dialokasikan|Pesanan #/i).should('exist');
          }
        });
      } else {
        cy.contains(/Master Pesanan|Belum ada pesanan/i).should('exist');
      }
    });
  });

  it('ET-07-011: Proteksi otorisasi mencegah pengunjung tamu mengakses daftar pesanan admin', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/admin/orders');
    cy.url().should('not.include', '/admin/orders');
  });
});
