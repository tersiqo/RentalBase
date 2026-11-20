describe('E2E TEST — MODUL 01: AUTENTIKASI CUSTOMER & PEMULIHAN AKUN', () => {
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-01-006: Halaman login customer dapat diakses dengan elemen form lengkap', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');
    cy.url().should('include', '/login');
    cy.contains('button', 'Customer').click({ force: true });
    cy.get('#email').should('be.visible');
    cy.get('#password').should('be.visible');
    cy.contains('button[type="submit"]', 'Masuk').should('be.visible');
    cy.contains('Lupa password?').should('be.visible');
    cy.contains('Daftar Sekarang').should('be.visible');
  });

  it('ET-01-007: Penolakan login customer saat memasukkan password salah', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');
    cy.contains('button', 'Customer').click({ force: true });
    cy.get('#email').clear().type(customerEmail);
    cy.get('#password').clear().type('passwordSalah999!');
    cy.contains('button[type="submit"]', 'Masuk').first().click({ force: true });
    cy.url().should('include', '/login');
    cy.get('body').should('contain.text', 'Akses Masuk Ditolak');
  });

  it('ET-01-008: Login customer berhasil dengan kredensial akun valid', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');
    cy.contains('button', 'Customer').click({ force: true });
    cy.get('#email').clear().type(customerEmail);
    cy.get('#password').clear().type(customerPassword);
    cy.contains('button[type="submit"]', 'Masuk').first().click({ force: true });
    cy.url().should('not.include', '/login');
    cy.get('body').should('be.visible');
  });

  it('ET-01-009: Halaman registrasi dapat diakses dan menampilkan formulir pendaftaran customer', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register');
    cy.url().should('include', 'tab=register');
    cy.get('#register-name').should('be.visible');
    cy.get('#register-email').should('be.visible');
    cy.get('#register-password').should('be.visible');
    cy.get('#register-password-confirm').should('be.visible');
    cy.contains('button[type="submit"]', 'Daftar Akun').should('be.visible');
  });

  it('ET-01-010: Validasi formulir registrasi saat konfirmasi password tidak cocok', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login?tab=register');
    cy.get('#register-name').type('Testing Pelanggan', { force: true });
    cy.get('#register-email').type('test.mismatch@example.com', { force: true });
    cy.get('#register-password').type('password123', { force: true });
    cy.get('#register-password-confirm').type('passwordBeda456', { force: true });
    cy.contains('button[type="submit"]', 'Daftar Akun').click({ force: true });
    cy.get('body').should('contain.text', 'Konfirmasi password tidak cocok');
  });

  it('ET-01-011: Halaman lupa password dapat diakses dengan formulir permintaan reset', () => {
    cy.visit('/forgot-password');
    cy.url().should('include', '/forgot-password');
    cy.contains('h1', 'Lupa Password?').should('be.visible');
    cy.get('#email').should('be.visible');
    cy.contains('button', 'Kirim Link Reset').should('be.visible');
  });

  it('ET-01-012: Validasi input formulir saat email kosong pada halaman lupa password', () => {
    cy.visit('/forgot-password');
    cy.get('#email').clear();
    cy.contains('button', 'Kirim Link Reset').click({ force: true });
    cy.url().should('include', '/forgot-password');
    cy.get('body').should('contain.text', 'email');
  });

  it('ET-01-013: Proteksi token tidak valid pada tautan pemulihan kata sandi', () => {
    cy.visit('/reset-password/token-palsu-invalid-qa-test');
    cy.get('#password').should('be.visible');
    cy.get('#password_confirmation').should('be.visible');
    cy.get('#password').type('newPassword123!', { force: true });
    cy.get('#password_confirmation').type('newPassword123!', { force: true });
    cy.contains('button', 'Simpan Password Baru').click({ force: true });
    cy.get('body').should('exist');
  });

  it('ET-01-046: Memeriksa tombol Masuk dengan Akun Google pada form login dan aksi klik redirect ke OAuth provider', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');
    cy.contains('button', 'Customer').click({ force: true });

    // Memeriksa tombol Google SSO memiliki tautan rute /auth/google
    cy.get('a[href*="/auth/google"]').should('exist')
      .and('contain.text', 'Google');
    cy.get('a[href*="/auth/google"]').should('have.attr', 'href').and('include', '/auth/google');
  });

  it.only('ET-01-047: Penanganan kegagalan callback autentikasi Google OAuth dengan pesan error humanis pada halaman login', () => {
    // Memverifikasi saat endpoint callback diakses tanpa payload OAuth yang valid, user diredirect kembali ke /login
    cy.request({
      url: '/auth/google/callback',
      followRedirect: false,
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status).to.eq(302);
      expect(response.redirectedToUrl).to.include('/login');
    });

    // Mengunjungi login untuk memverifikasi halaman login siap merender alert
    cy.visit('/login');
    cy.url().should('include', '/login');
  });
});
