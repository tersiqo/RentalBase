describe('Registrasi Admin Tenant', () => {

  // Buka halaman register sebelum tiap test
  beforeEach(() => {
    cy.visit('/register')
  })

  // ─────────────────────────────────────────
  // HAPPY PATH
  // ─────────────────────────────────────────

  it('Halaman register tampil dengan semua elemen form', () => {
    cy.get('h1').should('contain', 'Pendaftaran admin rental')
    cy.get('.btn-google').should('be.visible').and('contain', 'Daftar dengan Google')
    cy.get('.divider').should('contain', 'atau daftar dengan username dan email')
    cy.get('#username').should('be.visible')
    cy.get('#email').should('be.visible')
    cy.get('#password').should('be.visible')
    cy.get('#password_confirmation').should('be.visible')
    cy.get('button[type="submit"]').should('be.visible').and('contain', 'Daftar akun')
    cy.get('p.switch a').should('contain', 'Login di sini')
  })

  it('Daftar berhasil dengan data lengkap yang valid', () => {
    const timestamp = Date.now()

    cy.get('#username').type(`tesuser${timestamp}`)
    cy.get('#email').type(`tesuser${timestamp}@example.com`)
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('button[type="submit"]').click()

    // Setelah register berhasil, seharusnya diarahkan ke step setup toko
     cy.get('h1').should('contain', 'Setup Toko Anda')
  })

  // // ─────────────────────────────────────────
  // // VALIDASI FIELD WAJIB
  // // ─────────────────────────────────────────

    it('Submit form kosong: semua error validasi muncul', () => {
    cy.get('button[type="submit"]').click()

    // Verifikasi pesan error dari server muncul di bawah masing-masing input
    cy.contains('.error', 'Username wajib diisi.').should('be.visible')
    cy.contains('.error', 'Email wajib diisi.').should('be.visible')
    cy.contains('.error', 'Password wajib diisi.').should('be.visible')

    // Verifikasi input mendapatkan class is-invalid
    cy.get('#username').should('have.class', 'is-invalid')
    cy.get('#email').should('have.class', 'is-invalid')
    cy.get('#password').should('have.class', 'is-invalid')

    // Pastikan tetap berada di halaman pendaftaran
    cy.url().should('include', '/register')
  })


  it('Submit tanpa username: muncul error dari server', () => {
    cy.get('#email').type('test@example.com')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('button[type="submit"]').click()

    cy.url().should('include', '/register')
    cy.get('.error').should('exist')
  })

  it('Submit tanpa email: muncul error dari server', () => {
    cy.get('#username').type('tesuser_noemail')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('button[type="submit"]').click()

    cy.url().should('include', '/register')
    cy.get('.error').should('exist')
  })

  // ─────────────────────────────────────────
  // VALIDASI FORMAT INPUT
  // ─────────────────────────────────────────

  it('Email tidak valid (format salah): error validasi muncul', () => {
    cy.get('#username').type('tesuser_invalid')
    cy.get('#email').type('inibukanemail')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('button[type="submit"]').click()

    // Browser native validation menolak email yang tidak valid
    cy.get('#email:invalid').should('exist')
    cy.url().should('include', '/register')
  })

  it('Password dan konfirmasi tidak cocok: muncul error validasi', () => {
    const timestamp = Date.now()

    cy.get('#username').type(`tesuser${timestamp}`)
    cy.get('#email').type(`tesuser${timestamp}@example.com`)
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('PasswordSalah999!')
    cy.get('button[type="submit"]').click()

    cy.url().should('include', '/register')
    cy.contains('.error', /konfirmasi/i).should('be.visible')
  })


  it('Password kurang dari 8 karakter: muncul error validasi', () => {
    const timestamp = Date.now()

    cy.get('#username').type(`tesuser${timestamp}`)
    cy.get('#email').type(`tesuser${timestamp}@example.com`)
    cy.get('#password').type('abc')
    cy.get('#password_confirmation').type('abc')
    cy.get('button[type="submit"]').click()

    cy.url().should('include', '/register')
    cy.get('.error').should('exist')
  })

  it('Username sudah dipakai orang lain: muncul error duplikat', () => {
    // Asumsikan username "admin" sudah ada di database
    cy.get('#username').type('admin')
    cy.get('#email').type('newuser_unique@example.com')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('button[type="submit"]').click()

    cy.url().should('include', '/register')
    cy.contains('.error', /sudah/i).should('be.visible')
  })

  it('Email sudah terdaftar: muncul error email duplikat', () => {
    // Asumsikan email ini sudah ada di database
    cy.get('#username').type('userbaru_unik_' + Date.now())
    cy.get('#email').type('admin@rentalbase.com')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('button[type="submit"]').click()

    cy.url().should('include', '/register')
    cy.contains('.error', /sudah/i).should('be.visible')
  })

  // ─────────────────────────────────────────
  // FITUR UI
  // ─────────────────────────────────────────

  it('Tombol show/hide password berfungsi di field password', () => {
    cy.get('#password').type('rahasiabanget')

    // Default type-nya adalah password (tersembunyi)
    cy.get('#password').should('have.attr', 'type', 'password')

    // Klik tombol toggle show/hide
    cy.get('[data-toggle-pw="password"]').click()

    // Setelah diklik, type berubah jadi text (terlihat)
    cy.get('#password').should('have.attr', 'type', 'text')

    // Klik lagi untuk sembunyi kembali
    cy.get('[data-toggle-pw="password"]').click()
    cy.get('#password').should('have.attr', 'type', 'password')
  })

  it('Tombol show/hide password berfungsi di field konfirmasi password', () => {
    cy.get('#password_confirmation').type('rahasiabanget')

    cy.get('#password_confirmation').should('have.attr', 'type', 'password')
    cy.get('[data-toggle-pw="password_confirmation"]').click()
    cy.get('#password_confirmation').should('have.attr', 'type', 'text')
  })

  it('Link "Login di sini" mengarah ke halaman login', () => {
    cy.get('p.switch a').click()
    cy.url().should('include', '/login')
  })

  it('Nilai lama (old value) tetap ada di form setelah validasi gagal', () => {
    cy.get('#username').type('testoldvalue')
    cy.get('#email').type('oldvalue@example.com')
    cy.get('#password').type('abc') // sengaja pendek supaya gagal
    cy.get('#password_confirmation').type('abc')
    cy.get('button[type="submit"]').click()

    // Setelah redirect balik karena gagal validasi, field username & email tetap terisi
    cy.url().should('include', '/register')
    cy.get('#username').should('have.value', 'testoldvalue')
    cy.get('#email').should('have.value', 'oldvalue@example.com')
  })

  it('Link Google OAuth mengarah ke route auth.google', () => {
    cy.get('.btn-google')
      .should('have.attr', 'href')
      .and('include', '/auth/google')
  })

})