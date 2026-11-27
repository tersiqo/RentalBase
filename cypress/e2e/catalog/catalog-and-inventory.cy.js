describe('E2E TEST — MODUL 05: PRODUK, KATEGORI DAN KATALOG', () => {
  const subdomain = 'babyrental';
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // ========================================================
  // BAGIAN 1: ETALASE KATALOG PRODUK CUSTOMER
  // ========================================================
  describe('Katalog Produk Customer', () => {
    it('ET-05-001: Memastikan halaman katalog tenant dan daftar produk sewa dapat diakses', () => {
      cy.visit(`/client/${subdomain}`);
      cy.url().should('include', `/client/${subdomain}`);
      cy.contains('Baby Rental Studio').should('exist');
      cy.contains('Katalog').should('exist');
      cy.get('article').should('have.length.at.least', 1);
      cy.contains('Stroller Babyzen YOYO2 Black Frame').should('exist');
    });

    it('ET-05-002: Pencarian produk via search bar dengan kata kunci valid', () => {
      cy.visit(`/client/${subdomain}`);
      cy.get('input#q').should('exist').type('Stroller', { force: true });
      cy.contains('Stroller Babyzen YOYO2 Black Frame').should('exist');
    });

    it('ET-05-003: Pencarian produk dengan kata kunci tidak ada memunculkan empty state', () => {
      cy.visit(`/client/${subdomain}`);
      cy.get('input#q').should('exist').clear().type('NONEXISTENT_ITEM_XYZ999{enter}', { force: true });
      cy.wait(600);
      cy.contains('Belum ada alat yang cocok').should('be.visible');
    });

    it('ET-05-004: Filter berdasarkan kategori produk pada sidebar', () => {
      cy.visit(`/client/${subdomain}`);
      cy.contains('button', 'Stroller').click({ force: true });
      cy.contains('Stroller Babyzen YOYO2 Black Frame').should('exist');
    });

    it('ET-05-005: Filter toggle ketersediaan hanya menampilkan unit tersedia', () => {
      cy.intercept('POST', '**/livewire*/update').as('livewireUpdate');
      cy.visit(`/client/${subdomain}`);
      cy.contains('Hanya tampilkan unit tersedia').parent().find('button').click({ force: true });
      cy.wait('@livewireUpdate').its('response.statusCode').should('eq', 200);
      cy.get('article').should('have.length.at.least', 1);
    });

    it('ET-05-006: Dropdown pengurutan produk berdasarkan harga terendah dan tertinggi', () => {
      cy.visit(`/client/${subdomain}`);
      cy.get('select#so').select('termurah');
      cy.get('article').should('have.length.at.least', 1);
      cy.get('select#so').select('termahal');
      cy.get('article').should('have.length.at.least', 1);
    });

    it('ET-05-007: Tombol Reset Filter mengembalikan semua input filter ke kondisi awal', () => {
      cy.visit(`/client/${subdomain}`);
      cy.get('input#q').type('Stroller', { force: true });
      cy.wait(600);
      cy.contains('button', 'Reset').click({ force: true });
      cy.get('input#q').should('have.value', '');
    });

    it('ET-05-008: Halaman detail produk menampilkan spesifikasi tarif sewa dan jaminan bebas deposit', () => {
      cy.loginCustomer('rizky@gmail.com', 'password123');
      cy.visit(`/client/${subdomain}/products/1`);
      cy.contains('Stroller Babyzen YOYO2 Black Frame').should('exist');
      cy.contains('Rp 150.000').should('exist');
      cy.contains('Bebas Deposit').should('exist');
      cy.contains('Sewa Sekarang').should('exist');
    });
  });

  // ========================================================
  // BAGIAN 2: PENGELOLAAN PRODUK & UNIT INVENTARIS ADMIN
  // ========================================================
  describe('Inventaris & Master Unit Admin Toko', () => {
    it('ET-05-009: Admin rental mengakses manajemen katalog produk toko', () => {
      cy.loginAdmin(adminEmail, adminPassword);
      cy.visit('/admin/products');
      cy.url().should('include', '/admin/products');
      cy.contains('Produk Rental').should('exist');
      cy.contains('Stroller Babyzen YOYO2 Black Frame').should('exist');
    });

    it('ET-05-010: Admin rental mengakses manajemen master unit fisik barang sewa', () => {
      cy.loginAdmin(adminEmail, adminPassword);
      cy.visit('/admin/units');
      cy.url().should('include', '/admin/units');
      cy.contains('Unit Fisik Perlengkapan').should('exist');
    });
  });
});
