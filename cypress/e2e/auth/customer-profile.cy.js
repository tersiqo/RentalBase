describe('E2E TEST — MODUL 01: PROFIL CUSTOMER & KEAMANAN AKUN', () => {
  const subdomain = 'babyrental';
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-01-001: Tamu tanpa sesi login dicegat saat mencoba membuka profil customer', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit(`/client/${subdomain}/profile`);
    cy.url().should('not.include', '/profile');
  });

  it('ET-01-002: Customer aktif dapat mengakses antarmuka halaman profil akun', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/profile`);
    cy.url().should('include', `/client/${subdomain}/profile`);
    cy.contains('Profil Saya').should('exist');
  });

  it('ET-01-003: Customer memperbarui informasi biodata profil (nama, telepon, alamat)', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/profile`);
    cy.get('input[name="name"]').clear().type('Rizky Pelanggan Terverifikasi');
    cy.get('input[name="phone"]').clear().type('081234567899');
    cy.get('textarea[name="address"]').clear().type('Jl. Simpang Borobudur No. 99 Malang');
    cy.contains('button', 'Simpan Perubahan Data Diri').click({ force: true });
    cy.url().should('include', `/client/${subdomain}/profile`);
    cy.contains('Profil Saya').should('exist');
  });

  it('ET-01-004: Validasi formulir penggantian password akun customer', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/profile`);
    cy.get('input[name="current_password"]').should('exist');
    cy.get('input[name="password"]').should('exist');
    cy.get('input[name="password_confirmation"]').should('exist');
  });

  it('ET-01-005: Unggah foto profil avatar baru customer', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit(`/client/${subdomain}/profile`);
    cy.get('input[type="file"]').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
  });
});
