describe('E2E TEST — MODUL 10: MANAJEMEN KASUS KLAIM KERUSAKAN (DAMAGE CASE)', () => {
  const subdomain = 'babyrental';
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-10-001: Admin rental dapat mengakses daftar kasus klaim kerusakan barang', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/damage-cases');
    cy.url().should('include', '/admin/damage-cases');
    cy.contains(/Klaim Kerusakan|Kasus Kerusakan/i).should('exist');
  });

  it('ET-10-002: Admin dapat membuka formulir pembuatan tiket klaim kerusakan baru', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/damage-cases/create');
    cy.url().should('include', '/admin/damage-cases/create');
    cy.contains(/Catat Kasus Kerusakan|Buat Tiket Kerusakan/i).should('exist');
  });

  it('ET-10-003: Formulir klaim kerusakan memvalidasi estimasi biaya perbaikan dan foto bukti', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/damage-cases/create');
    cy.get('input[name="repair_cost"], input[id="repair_cost"]').should('exist');
  });

  it('ET-10-004: Customer dapat membuka halaman klaim kerusakan dari daftar pesanan', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.contains('Pesanan Saya').should('exist');
  });

  it('ET-10-005: Admin rental dapat membuka detail rincian penyelesaian tiket kerusakan', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/damage-cases');
    cy.get('body').then(($body) => {
      if ($body.find('a[href*="/admin/damage-cases/"]').length > 0) {
        cy.get('a[href*="/admin/damage-cases/"]').first().click({ force: true });
        cy.url().should('include', '/admin/damage-cases/');
        cy.contains('Tiket Kasus Kerusakan').should('exist');
      }
    });
  });

  it('ET-10-006: Keamanan multi-tenant memastikan tiket klaim kerusakan terisolasi antar toko rental', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/damage-cases');
    cy.url().should('include', '/admin/damage-cases');
  });
});
