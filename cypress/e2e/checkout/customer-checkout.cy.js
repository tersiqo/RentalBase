describe('E2E TEST — MODUL 07: BOOKING DAN CHECKOUT', () => {
  const subdomain = 'babyrental';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-07-001: Mencegah akses checkout bagi pengunjung yang belum login (guest)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.url().should('not.include', `/client/${subdomain}/checkout/1`);
  });

  it('ET-07-002: Akses direct checkout dari detail produk', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.url().should('include', `/client/${subdomain}/checkout`);
    cy.contains('Ringkasan Pesanan & Periode').should('exist');
  });

  it('ET-07-003: Validasi periode sewa pada checkout wizard', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.contains('button', 'Lanjut ke Pengiriman').should('exist').click({ force: true });
    cy.contains('Pengiriman & Kontak').should('exist');
  });

  it('ET-07-004: Pilihan metode kirim kurir memunculkan form alamat kirim dan kontak', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.contains('button', 'Lanjut ke Pengiriman').click({ force: true });
    cy.get('#addressText').should('exist');
    cy.get('#wa').should('exist');
  });

  it('ET-07-005: Validasi formulir pengiriman jika alamat dan nomor WhatsApp dikosongkan', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.contains('button', 'Lanjut ke Pengiriman').click({ force: true });
    cy.get('#addressText').clear({ force: true });
    cy.get('#wa').clear({ force: true });
    cy.contains('button', 'Lanjut ke Jaminan Identitas').click({ force: true });
  });

  it('ET-07-006: Validasi form jaminan identitas KTP wajib diisi lengkap', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.contains('button', 'Lanjut ke Pengiriman').click({ force: true });
    cy.get('#addressText').clear({ force: true }).type('Jl. Soekarno Hatta No. 123 Lowokwaru Malang', { force: true });
    cy.get('#wa').clear({ force: true }).type('081234567890', { force: true });
    cy.contains('button', 'Lanjut ke Jaminan Identitas').click({ force: true });
    cy.get('#full_name').should('exist');
    cy.get('#identity_number').should('exist');
  });

  it('ET-07-007: Unggah berkas foto KTP dan selfie memunculkan preview gambar', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.contains('button', 'Lanjut ke Pengiriman').click({ force: true });
    cy.get('#addressText').clear({ force: true }).type('Jl. Soekarno Hatta No. 123 Lowokwaru Malang', { force: true });
    cy.get('#wa').clear({ force: true }).type('081234567890', { force: true });
    cy.contains('button', 'Lanjut ke Jaminan Identitas').click({ force: true });
    cy.get('#identity_document_image').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.get('#face_image').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.contains('label', 'Ganti KTP').should('exist');
    cy.contains('label', 'Ganti Swafoto').should('exist');
  });

  it('ET-07-008: Validasi persetujuan syarat dan ketentuan sewa sebelum submit', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.contains('button', 'Lanjut ke Pengiriman').click({ force: true });
    cy.get('#addressText').clear({ force: true }).type('Jl. Soekarno Hatta No. 123 Lowokwaru Malang', { force: true });
    cy.get('#wa').clear({ force: true }).type('081234567890', { force: true });
    cy.contains('button', 'Lanjut ke Jaminan Identitas').click({ force: true });
    cy.get('#full_name').clear({ force: true }).type('Rizky Pelanggan', { force: true });
    cy.get('#identity_number').clear({ force: true }).type('3573012345670001', { force: true });
    cy.get('#identity_address').clear({ force: true }).type('Jl. Borobudur No. 88 Malang', { force: true });
    cy.get('#identity_document_image').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.get('#face_image').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.contains('button', 'Lanjut ke Konfirmasi').click({ force: true });
    cy.get('button[type="submit"]').should('be.disabled');
  });

  it('ET-07-009: Submit pesanan sewa lengkap dan penerbitan nomor pesanan baru', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/checkout/1`);
    cy.contains('button', 'Lanjut ke Pengiriman').click({ force: true });
    cy.get('#addressText').clear({ force: true }).type('Jl. Soekarno Hatta No. 123 Lowokwaru Malang', { force: true });
    cy.get('#wa').clear({ force: true }).type('081234567890', { force: true });
    cy.contains('button', 'Lanjut ke Jaminan Identitas').click({ force: true });
    cy.get('#full_name').clear({ force: true }).type('Rizky Pelanggan', { force: true });
    cy.get('#identity_number').clear({ force: true }).type('3573012345670001', { force: true });
    cy.get('#identity_address').clear({ force: true }).type('Jl. Borobudur No. 88 Malang', { force: true });
    cy.get('#identity_document_image').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.get('#face_image').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.contains('button', 'Lanjut ke Konfirmasi').click({ force: true });
    cy.get('input[name="terms"]').check({ force: true });
    cy.get('button[type="submit"]').click({ force: true });
    cy.url({ timeout: 30000 }).should('include', '/my-orders');
    cy.contains('Pesanan Saya', { timeout: 15000 }).should('be.visible');
  });
});
