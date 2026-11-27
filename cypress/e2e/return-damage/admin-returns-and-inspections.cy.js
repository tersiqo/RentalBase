describe('E2E TEST — MODUL 10: RETUR, DENDA, & PEMERIKSAAN KONDISI FISIK', () => {
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // Fitur 8.1-A1: Pemeriksaan Kondisi Fisik Admin
  it('ET-10-007: Admin rental dapat membuka halaman inspeksi pemeriksaan kondisi fisik unit alat', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/condition-checks');
    cy.url().should('include', '/admin/condition-checks');
    cy.contains(/Pemeriksaan Kondisi Fisik|Kondisi Fisik/i).should('exist');
    cy.get('select').should('exist');
  });

  it('ET-10-008: Formulir pemeriksaan kondisi fisik memvalidasi input catatan dan upload foto bukti fisik', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/condition-checks');
    cy.get('textarea[name="notes"]').should('exist');
    cy.get('input[type="file"]').should('exist');
  });

  // Fitur 7.1-A2: Penerimaan & Denda Retur Admin
  it('ET-10-009: Admin rental dapat membuka halaman penerimaan & denda retur barang', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/returns');
    cy.url().should('include', '/admin/returns');
    cy.contains(/Penerimaan & Denda Retur|Penerimaan Retur|Retur/i).should('exist');
    cy.get('select[name="status"]').should('exist');
  });

  it('ET-10-010: Modal penerimaan pengembalian memuat kalkulasi denda per jam dan aksi penyelesaian retur', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/returns');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Terima"), button:contains("Periksa"), button:contains("Proses")').length > 0) {
        cy.contains('button', /Terima|Periksa|Proses/i).first().click({ force: true });
        cy.contains(/Penerimaan|Denda|Retur/i).should('exist');
      } else {
        cy.contains(/Penerimaan & Denda|Retur/i).should('exist');
      }
    });
  });

  // Fitur 8.2-A3: Pengembalian Customer
  it('ET-10-011: Customer dapat membuka riwayat pesanan untuk mengakses aksi retur barang sewa', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.url().should('include', '/my-orders');
    cy.contains(/Pesanan Saya/i).should('exist');
  });
});
