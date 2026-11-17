describe('E2E TEST — MODUL 04: PENGATURAN PROFIL TOKO & METODE PEMBAYARAN', () => {
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-04-001: Akses halaman pengaturan profil & branding toko dan tema visual', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/settings/profile');
    cy.url().should('include', '/admin/settings/profile');
    cy.contains('Profil & Tema Toko').should('be.visible');
    cy.get('#input-name').should('be.visible');
    cy.get('#input-subdomain').should('be.visible').and('have.attr', 'readonly');

    cy.visit('/admin/theme');
    cy.url().should('include', '/admin/theme');
    cy.contains('Profil & Tema Toko').should('be.visible');
  });

  it('ET-04-002: Validasi formulir profil toko saat field wajib nama toko dikosongkan', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/settings/profile');
    cy.get('#input-name').clear();
    cy.contains('button[type="submit"]', 'Simpan Perubahan Profil').click({ force: true });
    cy.get('#input-name:invalid').should('exist');
  });

  it('ET-04-003: Akses daftar metode pembayaran rekening bank dan QRIS toko', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/settings/payments');
    cy.url().should('include', '/admin/settings/payments');
    cy.contains('Metode Pembayaran Toko').should('be.visible');
    cy.contains('Tambah Metode Pembayaran').should('be.visible');
  });

  it('ET-04-004: Validasi formulir penambahan metode pembayaran saat input tidak lengkap', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/settings/payments');
    cy.contains('button', 'Tambah Metode Pembayaran').first().click({ force: true });
    cy.contains('Pilih Tipe Pembayaran').should('be.visible');
    cy.contains('button[type="submit"]', /Simpan Metode/i).click({ force: true });
    cy.get('input[name="bank_name"]').should(($input) => {
      expect($input[0].checkValidity()).to.be.false;
    });
  });

  it('ET-04-005: Pengubahan status aktif atau nonaktif (toggle) metode pembayaran toko', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/settings/payments');
    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr').length > 0) {
        cy.get('table tbody tr').first().find('button[title*="mengubah status aktif"]').click({ force: true });
        cy.url().should('include', '/admin/settings/payments');
        cy.contains('Berhasil').should('exist');
      } else {
        cy.contains('Belum Ada Metode Pembayaran').should('exist');
      }
    });
  });

  it('ET-04-006: Proteksi hak akses role admin mencegah pengguna tamu membuka pengaturan toko', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/admin/settings/profile');
    cy.url().should('not.include', '/admin/settings/profile');
  });
});
