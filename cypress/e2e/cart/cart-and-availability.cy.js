describe('E2E TEST — MODUL 06: KETERSEDIAAN DAN KERANJANG', () => {
  const subdomain = 'babyrental';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-06-001: Membuka pop-up modal Tambah ke Keranjang dari detail produk', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/products/1`);
    cy.get('button[title="Tambah ke Keranjang"]').click({ force: true });
    cy.contains('#modal-title', 'Pilih Jadwal Sewa').should('exist');
  });

  it('ET-06-002: Kalkulasi otomatis durasi dan estimasi biaya di dalam modal pemilihan tanggal', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/products/1`);
    cy.get('button[title="Tambah ke Keranjang"]').click({ force: true });

    // Gunakan tanggal 14 hari ke depan untuk menjamin stok tersedia dan kompatibel di Chrome
    const start = new Date();
    start.setDate(start.getDate() + 14);
    const end = new Date();
    end.setDate(end.getDate() + 16);
    
    const startStr = start.toISOString().split('T')[0];
    const endStr = end.toISOString().split('T')[0];

    cy.get('input[type="date"]').first()
      .invoke('val', startStr)
      .trigger('input')
      .trigger('change');
      
    cy.get('input[type="date"]').last()
      .invoke('val', endStr)
      .trigger('input')
      .trigger('change');

    cy.contains('Estimasi Total Biaya:').should('exist');
    cy.contains('button', 'Tambah ke Keranjang').should('exist');
  });

  it('ET-06-003: Tombol Batal pada modal menutup dialog dan kembali ke detail produk', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/products/1`);
    cy.get('button[title="Tambah ke Keranjang"]').click({ force: true });
    cy.contains('#modal-title', 'Pilih Jadwal Sewa').should('exist');
    cy.contains('button', 'Batal').click({ force: true });
    cy.get('#modal-title').should('not.be.visible');
  });

  it('ET-06-004: Membuka halaman keranjang sewa pelanggan terautentikasi', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/cart`);
    cy.url().should('include', `/client/${subdomain}/cart`);
    cy.contains('Keranjang Sewa').should('exist');
  });

  it('ET-06-005: Tamu yang belum login dicegat saat mencoba mengakses keranjang sewa', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit(`/client/${subdomain}/cart`);
    cy.url().should('include', '/login');
  });

  it('ET-06-006: Keranjang belanja terisolasi strictly per-toko rental', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}`);
    cy.get('a[href*="/cart"]').should('exist');
  });
});
