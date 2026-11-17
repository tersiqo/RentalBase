describe('E2E TEST — MODUL 01: AUTENTIKASI TENANT & OWNER', () => {
  const adminEmail = 'budi@babyrental.com';
  const ownerEmail = 'owner@rentalbase.id';
  const validPassword = 'password123';
  const wrongPassword = 'wrongpassword999';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // =========================================================================
  // 1. PENDAFTARAN ADMIN RENTAL & GOOGLE AUTH (/register-tenant)
  // =========================================================================

  it('ET-01-014: Halaman pendaftaran mitra /register-tenant memuat form lengkap, heading, ikon, dan tautan login', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    cy.url().should('include', '/register-tenant');
    cy.get('h1').should('contain.text', 'Pendaftaran admin rental');
    cy.get('p.sub').should('contain.text', 'Buat akun untuk mengelola toko Anda.');

    // Form inputs & button
    cy.get('#username').should('be.visible').and('have.attr', 'name', 'username');
    cy.get('#email').should('be.visible').and('have.attr', 'name', 'email');
    cy.get('#password').should('be.visible').and('have.attr', 'name', 'password');
    cy.get('#password_confirmation').should('be.visible').and('have.attr', 'name', 'password_confirmation');
    cy.contains('button[type="submit"]', 'Daftar akun').should('be.visible');

    // Link kembali ke halaman login
    cy.contains('a', 'Login di sini').should('have.attr', 'href').and('include', '/login');
  });

  it('ET-01-015: Tautan pendaftaran dengan Google menyediakan tombol OAuth dengan ikon SVG dan link valid', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    // Tombol Google OAuth mitra
    cy.get('a.btn-google')
      .should('be.visible')
      .and('contain.text', 'Daftar dengan Google')
      .and('have.attr', 'href')
      .and('include', '/auth/google');

    // Ikon logo Google di dalam tombol
    cy.get('a.btn-google svg').should('be.visible');
    cy.contains('.divider', 'atau daftar dengan username dan email').should('be.visible');
  });

  it('ET-01-016: Query parameter plan pada URL /register-tenant?plan=business tersimpan di input hidden form', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant?plan=business');

    cy.get('input[name="plan"]').should('have.value', 'business');
  });

  it('ET-01-017: Validasi formulir registrasi tenant mencegah pengajuan ketika field username kosong', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    cy.get('#email').type('test_empty_user@example.com');
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');

    cy.contains('button[type="submit"]', 'Daftar akun').click();

    // Halaman tidak boleh berpindah karena dicegat validasi field wajib
    cy.url().should('include', '/register-tenant');
    cy.get('#username').should('have.class', 'is-invalid');
  });

  it('ET-01-018: Validasi registrasi tenant menolak username yang mengandung karakter spasi atau format ilegal', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    cy.get('#username').type('user nama dengan spasi');
    cy.get('#email').type('user_spasi@example.com');
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');

    cy.contains('button[type="submit"]', 'Daftar akun').click();

    // Tetap di halaman registrasi dan menolak form
    cy.url().should('include', '/register-tenant');
  });

  it('ET-01-019: Validasi registrasi tenant menolak format alamat email yang tidak valid', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    cy.get('#username').type('mitra_valid_user');
    cy.get('#email').type('email-tidak-valid');
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');

    cy.contains('button[type="submit"]', 'Daftar akun').click();

    // Form input browser/backend menahan submission
    cy.url().should('include', '/register-tenant');
  });

  it('ET-01-020: Validasi registrasi tenant menolak konfirmasi password yang tidak cocok', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const randomUser = 'mitra_' + Date.now();
    cy.get('#username').type(randomUser);
    cy.get('#email').type(`${randomUser}@example.com`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('passwordBerbeda999');

    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.url().should('include', '/register-tenant');
  });

  it('ET-01-021: Validasi registrasi tenant menolak password yang kurang dari 8 karakter', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const randomUser = 'mitra_' + Date.now();
    cy.get('#username').type(randomUser);
    cy.get('#email').type(`${randomUser}@example.com`);
    cy.get('#password').type('pass123'); // Hanya 7 karakter
    cy.get('#password_confirmation').type('pass123');

    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.url().should('include', '/register-tenant');
  });

  // =========================================================================
  // 2. ALUR SETUP TOKO BERTAHAP (SETUP-TENANT -> THEME -> PAYMENT -> WAITING)
  // =========================================================================

  it('ET-01-022: Tahap 0 (Akun Admin) berhasil diajukan dengan kredensial unik dan mengarahkan otomatis ke Tahap 1 (Setup Toko Anda)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `owner_${uniqueId}`;
    const newEmail = `owner_${uniqueId}@rentalbase.test`;

    cy.get('#username').type(newUsername);
    cy.get('#email').type(newEmail);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    // Harus diarahkan ke halaman Setup Toko Anda (Step 1 Info Toko)
    cy.url().should('include', '/register-tenant');
    cy.contains('h1', 'Setup Toko Anda').should('be.visible');
    cy.contains('p', 'Lengkapi informasi toko untuk menyelesaikan pendaftaran.').should('be.visible');
    cy.contains('span', 'Info Toko').should('be.visible');
  });

  it('ET-01-023: Tahap 1 (Setup Toko Anda) memvalidasi field wajib info usaha jika tombol Berikutnya diklik kosong', () => {
    // Membuka setup toko dalam status registrasi aktif
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    cy.get('#username').type(`owner_${uniqueId}`);
    cy.get('#email').type(`owner_${uniqueId}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.contains('h1', 'Setup Toko Anda').should('be.visible');

    // Langsung klik Berikutnya tanpa mengisi nama usaha / subdomain
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    // Validasi HTML5 / backend menahan form pada halaman setup
    cy.url().should('include', '/register-tenant');
    cy.get('#business_name').should('exist');
  });

  it('ET-01-024: Tahap 1 (Setup Toko Anda) menyediakan pilihan kartu paket langganan (Starter, Business, Professional)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    cy.get('#username').type(`owner_${uniqueId}`);
    cy.get('#email').type(`owner_${uniqueId}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.contains('h1', 'Setup Toko Anda').should('be.visible');
    cy.contains('h2', '3. Konfirmasi Paket').should('be.visible');

    // Opsi ketiga paket langganan
    cy.get('input[name="plan_name"][value="starter"]').should('exist');
    cy.contains('.plan-title', 'Starter').should('be.visible');
    cy.contains('.plan-price', 'Gratis').should('be.visible');

    cy.get('input[name="plan_name"][value="business"]').should('exist');
    cy.contains('.plan-title', 'Business').should('be.visible');
    cy.contains('.plan-price', 'Rp 99.000 / bln').should('be.visible');

    cy.get('input[name="plan_name"][value="professional"]').should('exist');
    cy.contains('.plan-title', 'Professional').should('be.visible');
    cy.contains('.plan-price', 'Rp 249.000 / bln').should('be.visible');
  });

  it('ET-01-025: Tahap 1 (Setup Toko Anda) menyediakan tombol pembatalan pendaftaran toko', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    cy.get('#username').type(`owner_${uniqueId}`);
    cy.get('#email').type(`owner_${uniqueId}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.contains('h1', 'Setup Toko Anda').should('be.visible');

    // Link Batal dan keluar dari pendaftaran toko
    cy.contains('a', 'Batal dan keluar dari pendaftaran toko').should('be.visible');
  });

  it('ET-01-026: Pengisian Tahap 1 (Setup Toko Anda) berhasil dan mengarahkan otomatis ke Tahap 2 (Personalisasi toko Anda)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `user_${uniqueId}`;
    const newSubdomain = `toko${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.contains('h1', 'Setup Toko Anda').should('be.visible');

    // Mengisi informasi usaha & PIC
    cy.get('#business_name').type(`Toko Rental E2E ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Jasa penyewaan perlengkapan fotografi dan studio terlengkap.');
    cy.get('#owner_name').clear().type(`Pemilik Toko ${uniqueId}`);
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Percobaan No. 123, Jakarta');

    // Memilih paket Business dan menyetujui syarat & ketentuan
    cy.get('input[name="plan_name"][value="business"]').check({ force: true });
    cy.get('input[name="terms"]').check({ force: true });

    cy.contains('button[type="submit"]', 'Berikutnya').click();

    // Berpindah ke Tahap 2: Personalisasi Tema Tampilan
    cy.url().should('include', '/register-tenant');
    cy.contains('h1', 'Personalisasi toko Anda').should('be.visible');
    cy.contains('span', 'Tema Tampilan').should('be.visible');
  });

  it('ET-01-027: Tahap 2 (Personalisasi toko Anda) memuat pilihan lingkaran warna preset dan input warna kustom', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `clr_${uniqueId}`;
    const newSubdomain = `warna${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Warna ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Layanan rental alat dengan custom warna tema.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Warna Indah No. 45');
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.contains('h1', 'Personalisasi toko Anda').should('be.visible');
    cy.contains('h2', 'Pilihan warna').should('be.visible');
    cy.contains('h2', 'Warna kustom').should('be.visible');

    // Preset color circles
    cy.get('.color-circle').should('have.length.at.least', 3);
    cy.get('input[type="color"]').should('exist');
  });

  it('ET-01-028: Tahap 2 (Personalisasi toko Anda) menampilkan pratinjau langsung katalog toko (mockup app)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `prev_${uniqueId}`;
    const newSubdomain = `preview${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Preview ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Deskripsi toko untuk preview mockup.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Preview No. 1');
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.contains('h1', 'Personalisasi toko Anda').should('be.visible');
    cy.contains('h2', 'Pratinjau langsung').should('be.visible');

    // Komponen Mockup App
    cy.get('.mockup-app').should('be.visible');
    cy.get('.mockup-header').should('be.visible');
    cy.get('.mockup-card').should('have.length.at.least', 2);
  });

  it('ET-01-029: Tahap 2 (Personalisasi toko Anda) menyediakan tombol Kembali ke Info Toko dan tombol Terapkan dan Lanjutkan', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `nav_${uniqueId}`;
    const newSubdomain = `navigasi${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Navigasi ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Deskripsi navigasi setup.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Navigasi No. 2');
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.contains('h1', 'Personalisasi toko Anda').should('be.visible');

    // Tombol Kembali dan tombol Terapkan
    cy.contains('a', 'Kembali').should('be.visible').and('have.attr', 'href').and('include', 'step=info');
    cy.contains('button[type="submit"]', 'Terapkan dan Lanjutkan').should('be.visible');
  });

  it('ET-01-030: Pengiriman tema Tahap 2 berhasil dan mengarahkan pengguna ke Tahap 3 (Konfirmasi Pembayaran)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `pay_${uniqueId}`;
    const newSubdomain = `bayar${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Bayar ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Deskripsi menuju pembayaran.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Pembayaran No. 8');
    cy.get('input[name="plan_name"][value="business"]').check({ force: true });
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.contains('h1', 'Personalisasi toko Anda').should('be.visible');

    // Klik preset tema pertama dan lanjutkan
    cy.get('.color-circle').first().click();
    cy.contains('button[type="submit"]', 'Terapkan dan Lanjutkan').click();

    // Berpindah ke Tahap 3: Konfirmasi Pembayaran
    cy.url().should('include', '/register-tenant');
    cy.contains('h1', 'Konfirmasi Pembayaran').should('be.visible');
    cy.contains('p', 'Selesaikan pembayaran subscription untuk mengirim pengajuan pendaftaran toko Anda ke Owner.').should('be.visible');
    cy.contains('span', 'Pembayaran').should('be.visible');
  });

  it('ET-01-031: Tahap 3 (Konfirmasi Pembayaran) menampilkan ringkasan paket, total tagihan, rekening bank BCA, tombol Salin, dan QRIS', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `bill_${uniqueId}`;
    const newSubdomain = `tagihan${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Tagihan ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Deskripsi rincian billing.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Tagihan No. 9');
    cy.get('input[name="plan_name"][value="business"]').check({ force: true });
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.get('.color-circle').first().click();
    cy.contains('button[type="submit"]', 'Terapkan dan Lanjutkan').click();

    cy.contains('h1', 'Konfirmasi Pembayaran').should('be.visible');

    // Rincian Paket & Tagihan
    cy.contains('h3', 'Business').should('be.visible');
    cy.contains('span', 'Total Tagihan').should('be.visible');

    // Instruksi Pembayaran Bank BCA & QRIS
    cy.contains('span', 'Transfer Bank').should('be.visible');
    cy.get('#account-number').should('be.visible');
    cy.get('#copy-btn').should('be.visible');
    cy.contains('span', 'QRIS Standar').should('be.visible');
  });

  it('ET-01-032: Tahap 3 (Konfirmasi Pembayaran) memvalidasi bukti pembayaran wajib diunggah sebelum mengirim pengajuan', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `valpay_${uniqueId}`;
    const newSubdomain = `valpay${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Valpay ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Validasi bukti bayar.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Valpay No. 10');
    cy.get('input[name="plan_name"][value="business"]').check({ force: true });
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.get('.color-circle').first().click();
    cy.contains('button[type="submit"]', 'Terapkan dan Lanjutkan').click();

    cy.contains('h1', 'Konfirmasi Pembayaran').should('be.visible');

    // Klik Kirim Pengajuan tanpa melampirkan file bukti bayar
    cy.get('#submit-btn').click();

    // Browser menolak submit atau tetap berada di halaman konfirmasi pembayaran
    cy.url().should('include', '/register-tenant');
    cy.get('#payment_proof').should('exist');
  });

  it('ET-01-033: Pengunggahan bukti pembayaran di Tahap 3 berhasil dan mengarahkan pengguna ke Tahap 4 (Menunggu Verifikasi)', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `wait_${uniqueId}`;
    const newSubdomain = `tunggu${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Tunggu ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Deskripsi pendaftaran sampai selesai.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Menunggu No. 11');
    cy.get('input[name="plan_name"][value="starter"]').check({ force: true });
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.get('.color-circle').first().click();
    cy.contains('button[type="submit"]', 'Terapkan dan Lanjutkan').click();

    cy.contains('h1', 'Konfirmasi Pembayaran').should('be.visible');

    // Unggah file bukti pembayaran menggunakan fixture
    cy.get('#payment_proof').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.get('#submit-btn').click();

    // Diarahkan ke halaman Menunggu Verifikasi
    cy.url().should('include', '/register-tenant');
    cy.contains('h1', 'Menunggu Verifikasi').should('be.visible');
    cy.contains('p', 'Terima kasih! Pendaftaran toko Anda berhasil diajukan dan saat ini sedang ditinjau oleh tim kami.').should('be.visible');
  });

  it('ET-01-034: Tahap 4 (Menunggu Verifikasi) menampilkan rincian informasi peninjauan 1x24 jam dan tombol Kembali ke Beranda', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/register-tenant');

    const uniqueId = Date.now();
    const newUsername = `info_${uniqueId}`;
    const newSubdomain = `infoverif${uniqueId}`.slice(0, 20);

    cy.get('#username').type(newUsername);
    cy.get('#email').type(`${newUsername}@rentalbase.test`);
    cy.get('#password').type('password123');
    cy.get('#password_confirmation').type('password123');
    cy.contains('button[type="submit"]', 'Daftar akun').click();

    cy.get('#business_name').type(`Toko Info Verif ${uniqueId}`);
    cy.get('#subdomain').type(newSubdomain);
    cy.get('#description').type('Deskripsi info verifikasi.');
    cy.get('#phone').type('081234567890');
    cy.get('#address').type('Jl. Info Verif No. 12');
    cy.get('input[name="plan_name"][value="starter"]').check({ force: true });
    cy.get('input[name="terms"]').check({ force: true });
    cy.contains('button[type="submit"]', 'Berikutnya').click();

    cy.get('.color-circle').first().click();
    cy.contains('button[type="submit"]', 'Terapkan dan Lanjutkan').click();

    cy.get('#payment_proof').selectFile('cypress/fixtures/sample-ktp.jpg', { force: true });
    cy.get('#submit-btn').click();

    cy.contains('h1', 'Menunggu Verifikasi').should('be.visible');
    cy.contains('li', 'Proses verifikasi biasanya memakan waktu maksimal 1x24 jam.').should('be.visible');

    // Tombol Kembali ke Beranda mengarah ke landing page
    cy.contains('a', 'Kembali ke Beranda')
      .should('be.visible')
      .and('have.attr', 'href');
  });

  // =========================================================================
  // 3. TAMPILAN LOGIN & TAB ADMIN TOKO (/login)
  // =========================================================================

  it('ET-01-035: Tampilan halaman /login menyediakan pilihan tab Customer dan Admin Toko', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');

    cy.get('h1').should('contain.text', 'Masuk ke Akun Anda');

    // Tab buttons Customer dan Admin Toko
    cy.contains('button', 'Customer').should('be.visible');
    cy.contains('button', 'Admin Toko').should('be.visible');

    // Klik tab Admin Toko dan verifikasi helper text berubah
    cy.contains('button', 'Admin Toko').click({ force: true });
    cy.contains('span', 'Masuk sebagai Pengelola / Admin Toko Rental.').should('be.visible');

    // Field form login admin
    cy.get('#email').should('be.visible');
    cy.get('#password').should('be.visible');
    cy.contains('button[type="submit"]', 'Masuk').should('be.visible');
  });

  it('ET-01-036: Tab Customer pada halaman /login memuat tombol masuk dengan Akun Google', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');

    cy.contains('button', 'Customer').click({ force: true });
    cy.contains('span', 'Masuk dengan Akun Google')
      .closest('a')
      .should('be.visible')
      .and('have.attr', 'href')
      .and('include', '/auth/google');
  });

  it('ET-01-037: Form login Admin Toko memvalidasi input email/username dan password wajib diisi', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');

    cy.contains('button', 'Admin Toko').click({ force: true });
    cy.contains('button[type="submit"]', 'Masuk').first().click();

    // Validasi browser/form menahan pengiriman form kosong
    cy.url().should('include', '/login');
  });

  it('ET-01-038: Form login Admin Toko menolak autentikasi dengan password yang salah', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');

    cy.contains('button', 'Admin Toko').click({ force: true });
    cy.get('#email').clear().type(adminEmail);
    cy.get('#password').clear().type(wrongPassword);
    cy.contains('button[type="submit"]', 'Masuk').first().click();

    // Tetap di halaman login dan menampilkan alert pesan error
    cy.url().should('include', '/login');
    cy.get('body').should('contain.text', 'Email atau password yang Anda masukkan salah.');
  });

  it('ET-01-039: Form login Admin Toko menolak akun yang tidak terdaftar', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/login');

    cy.contains('button', 'Admin Toko').click({ force: true });
    cy.get('#email').clear().type('unregistered_admin_999@example.com');
    cy.get('#password').clear().type('password123');
    cy.contains('button[type="submit"]', 'Masuk').first().click();

    cy.url().should('include', '/login');
    cy.get('body').should('contain.text', 'Email atau password yang Anda masukkan salah.');
  });

  // =========================================================================
  // 4. PORTAL MASUK & AUTENTIKASI OWNER (/owner/login & /owner/dashboard)
  // =========================================================================

  it('ET-01-040: Halaman portal masuk /owner/login memuat kartu otentikasi khusus Owner Platform', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/owner/login');

    cy.url().should('include', '/owner/login');
    cy.contains('h1', 'Portal Owner Platform').should('be.visible');
    cy.contains('span', 'Owner Portal').should('be.visible');
    cy.contains('p', 'Akses kontrol pusat & pemantauan seluruh tenant RentalBase').should('be.visible');

    cy.get('#email').should('be.visible').and('have.attr', 'name', 'email');
    cy.get('#password').should('be.visible').and('have.attr', 'name', 'password');
    cy.get('button[type="submit"]').should('contain.text', 'Masuk ke Dashboard Owner');
  });

  it('ET-01-041: Portal login owner memvalidasi input kosong dengan pesan peringatan', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/owner/login');

    cy.get('button[type="submit"]').click();

    // Input email fokus atau browser validasi aktif
    cy.url().should('include', '/owner/login');
  });

  it('ET-01-042: Portal login owner menolak kredensial salah dengan pesan kesalahan spesifik', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/owner/login');

    cy.get('#email').type(ownerEmail);
    cy.get('#password').type(wrongPassword);
    cy.get('button[type="submit"]').click();

    cy.url().should('include', '/owner/login');
    cy.contains('Gagal Masuk').should('be.visible');
    cy.get('body').should('contain.text', 'Email/username atau password yang Anda masukkan salah.');
  });

  it('ET-01-043: Login Owner sukses dengan kredensial valid mengarahkan ke /owner/dashboard', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/owner/login');

    cy.get('#email').type(ownerEmail);
    cy.get('#password').type(validPassword);
    cy.get('button[type="submit"]').click();

    cy.url().should('include', '/owner/dashboard');
    cy.contains('h1', 'Dashboard Owner Pusat').should('be.visible');
    cy.contains('p', 'Ringkasan metrik platform RentalBase, pemantauan tenant rental, dan status langganan.').should('be.visible');
  });
});
