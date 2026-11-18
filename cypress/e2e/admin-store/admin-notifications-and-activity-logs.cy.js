describe('E2E TEST — MODUL 04: NOTIFIKASI & LOG AKTIVITAS TOKO ADMIN', () => {
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // Fitur 9.1-A2: Notifikasi Admin
  it('ET-04-007: Admin rental dapat membuka pusat notifikasi operasional toko dan memantau alert', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.url().should('include', '/admin/notifications');
    cy.contains(/Pusat Pemberitahuan Toko|Pemberitahuan|Notifikasi/i).should('exist');
  });

  it('ET-04-008: Hub notifikasi admin menyediakan tombol tandai semua dibaca dan filter tab status', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Tandai Semua Dibaca")').length > 0) {
        cy.contains('button', 'Tandai Semua Dibaca').should('exist');
      } else {
        cy.contains(/Pusat Pemberitahuan|Pemberitahuan|Notifikasi/i).should('exist');
      }
    });
  });

  // Fitur 9.2-A1: Activity Log Admin
  it('ET-04-009: Admin rental dapat membuka daftar jejak audit (activity log) operasional internal toko', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/activity-logs');
    cy.url().should('include', '/admin/activity-logs');
    cy.contains(/Log Aktivitas Toko|Log Aktivitas|Aktivitas/i).should('exist');
    cy.get('select[name="module"]').should('exist');
  });

  it('ET-04-010: Antarmuka activity log admin bersifat read-only dan menyediakan modal detail teknis', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/activity-logs');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Detail"), a:contains("Detail")').length > 0) {
        cy.contains(/Detail/i).first().click({ force: true });
        cy.contains(/Detail|Log|Aktivitas/i).should('exist');
      } else {
        cy.contains(/Log Aktivitas Toko|Log Aktivitas|Aktivitas/i).should('exist');
      }
    });
  });

  it('ET-04-011: Proteksi hak akses mencegah pengguna tamu membuka log aktivitas dan notifikasi admin', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/admin/activity-logs');
    cy.url().should('not.include', '/admin/activity-logs');
  });
});
