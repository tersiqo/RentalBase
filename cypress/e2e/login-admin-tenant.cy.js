describe('Login Admin Tenant', () => {

  // Kunjungi halaman login sebelum setiap test
  beforeEach(() => {
    cy.visit('/login')
  })

  // ─────────────────────────────────────────
  // 1. TAMPILAN HALAMAN & ELEMEN UI
  // ─────────────────────────────────────────

  it('Halaman login tampil dengan semua elemen form yang sesuai', () => {
    // Judul & deskripsi form
    cy.get('h1').should('contain', 'Masuk ke Akun Anda')
    cy.get('p.sub').should('contain', 'Akses Dashboard Admin Rental atau Panel Owner Pusat RentalBase.')

    // Tombol Google OAuth & divider
    cy.get('.btn-google').should('be.visible').and('contain', 'Masuk dengan Google')
    cy.get('.divider').should('contain', 'atau masuk dengan email')

    // Field input form
    cy.get('#email').should('be.visible').and('have.attr', 'placeholder', 'admin@rental.com atau nama_user')
    cy.get('#password').should('be.visible').and('have.attr', 'placeholder', 'Masukkan password')

    // Checkbox remember me & link lupa password
    cy.get('input[name="remember"]').should('exist')
    cy.contains('label.remember', 'Ingat saya').should('be.visible')
    cy.contains('a', 'Lupa password?').should('be.visible')

    // Tombol submit & link registrasi tenant
    cy.get('button[type="submit"]').should('be.visible').and('contain', 'Masuk')
    cy.get('p.switch a').should('contain', 'Daftar Tenant')
  })

  // ─────────────────────────────────────────
  // 2. HAPPY PATH (LOGIN BERHASIL)
  // ─────────────────────────────────────────

  it('Login berhasil menggunakan email admin tenant yang valid', () => {
    cy.get('#email').type('budi@babyrental.com')
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    // Setelah login berhasil, seharusnya diarahkan ke Dashboard Admin Rental
    cy.url().should('include', '/admin/dashboard')
    cy.get('body').should('be.visible')
  })

  it('Login berhasil menggunakan username admin tenant yang valid', () => {
    cy.get('#email').type('Budi Admin Baby Rental')
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    // Login via username juga berhasil mengarah ke dashboard
    cy.url().should('include', '/admin/dashboard')
  })

  // ─────────────────────────────────────────
  // 3. VALIDASI FORM WAJIB DIISI
  // ─────────────────────────────────────────

  it('Submit form kosong: muncul pesan validasi email dan password wajib diisi', () => {
    cy.get('button[type="submit"]').click()

    // Memastikan pesan error validasi muncul
    cy.contains('Email atau username wajib diisi.').should('be.visible')
    cy.contains('Password wajib diisi.').should('be.visible')

    // Memastikan input diberi class is-invalid
    cy.get('#email').should('have.class', 'is-invalid')
    cy.get('#password').should('have.class', 'is-invalid')

    // URL tetap di halaman login
    cy.url().should('include', '/login')
  })

  it('Submit hanya mengisi email: muncul validasi password wajib diisi', () => {
    cy.get('#email').type('budi@babyrental.com')
    cy.get('button[type="submit"]').click()

    cy.contains('Password wajib diisi.').should('be.visible')
    cy.get('#password').should('have.class', 'is-invalid')
    cy.get('#email').should('have.value', 'budi@babyrental.com')
  })

  it('Submit hanya mengisi password: muncul validasi email wajib diisi', () => {
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    cy.contains('Email atau username wajib diisi.').should('be.visible')
    cy.get('#email').should('have.class', 'is-invalid')
  })

  // ─────────────────────────────────────────
  // 4. KREDENSIAL SALAH / TIDAK VALID
  // ─────────────────────────────────────────

  it('Login gagal jika password salah: muncul notifikasi error', () => {
    cy.get('#email').type('budi@babyrental.com')
    cy.get('#password').type('passwordSalahTotal!')
    cy.get('button[type="submit"]').click()

    // Memastikan pesan error kredensial tidak cocok tampil
    cy.contains('Email atau password yang Anda masukkan salah.').should('be.visible')
    cy.get('#email').should('have.class', 'is-invalid')
    cy.url().should('include', '/login')
  })

  it('Login gagal jika email / username tidak terdaftar', () => {
    cy.get('#email').type('user_tidak_ada_di_db@example.com')
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    cy.contains('Email atau password yang Anda masukkan salah.').should('be.visible')
    cy.url().should('include', '/login')
  })

  // ─────────────────────────────────────────
  // 5. FITUR INTERAKTIF & NAVIGASI
  // ─────────────────────────────────────────

  it('Tombol toggle password berfungsi untuk menampilkan dan menyembunyikan password', () => {
    cy.get('#password').type('rahasia123')

    // Tipe awal adalah password (tersembunyi)
    cy.get('#password').should('have.attr', 'type', 'password')

    // Klik tombol toggle mata
    cy.get('[data-toggle-pw="password"]').click()
    cy.get('#password').should('have.attr', 'type', 'text')

    // Klik lagi untuk menyembunyikan kembali
    cy.get('[data-toggle-pw="password"]').click()
    cy.get('#password').should('have.attr', 'type', 'password')
  })

  it('Checkbox "Ingat saya" dapat dicentang dan tidak tercentang', () => {
    cy.get('input[name="remember"]').should('not.be.checked')
    cy.get('input[name="remember"]').check().should('be.checked')
    cy.get('input[name="remember"]').uncheck().should('not.be.checked')
  })

  it('Link "Daftar Tenant" mengarah ke halaman registrasi tenant', () => {
    cy.get('p.switch a').click()
    cy.url().should('include', '/register')
  })


  it('Lupa Password', () => {
    cy.contains('a', 'Lupa password?').click()

    cy.url().should('include', '/forgot-password')
    cy.get('h1').should('contain', 'Lupa Password?')
    cy.get('p.sub').should('contain', 'Masukkan email Anda')

    cy.get('#email').type('budi@babyrental.com')
    cy.get('button[type="submit"]').contains('Kirim Link Reset').click()

    cy.get('.alert').should('be.visible').and('contain', 'Kami telah mengirimkan link reset password')
  })

  it('Lupa Password gagal jika email tidak terdaftar', () => {
    cy.contains('a', 'Lupa password?').click()
    cy.url().should('include', '/forgot-password')

    cy.get('#email').type('email_palsu_tidak_ada@example.com')
    cy.get('button[type="submit"]').click()

    cy.get('.alert').should('be.visible').and('contain', 'Kami tidak dapat menemukan pengguna')
  })

  it('Link "Kembali ke Login" di halaman lupa password mengarahkan kembali ke login', () => {
    cy.contains('a', 'Lupa password?').click()
    cy.contains('a', 'Kembali ke Login').click()
    cy.url().should('include', '/login')
  })


  it('Tombol Google OAuth memiliki link menuju route auth.google', () => {
    cy.get('.btn-google')
      .should('have.attr', 'href')
      .and('include', '/auth/google')
  })

})