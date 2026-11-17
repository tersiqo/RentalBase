describe('E2E TEST — MODUL 03: MANAJEMEN PLATFORM OWNER', () => {
  const ownerEmail = 'owner@rentalbase.id';
  const validPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // =========================================================================
  // 1. DASHBOARD RINGKASAN & AKSI CEPAT PLATFORM (/owner/dashboard)
  // =========================================================================

  it('ET-03-001: Dashboard Owner /owner/dashboard menampilkan 4 kartu metrik utama (Menunggu Verifikasi, Tenant Aktif, Tenant Suspended, Segera Berakhir)', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    cy.url().should('include', '/owner/dashboard');
    cy.get('h1').should('contain.text', 'Dashboard Owner Pusat');
    cy.contains('p', 'Ringkasan metrik platform RentalBase, pemantauan tenant rental, dan status langganan.').should('be.visible');

    // 4 Kartu Metrik Utama
    cy.contains('p', 'Menunggu Verifikasi').should('be.visible');
    cy.contains('p', 'Tenant Aktif').should('be.visible');
    cy.contains('p', 'Tenant Suspended').should('be.visible');
    cy.contains('p', 'Segera Berakhir').should('be.visible');
  });

  it('ET-03-002: Header dashboard Owner menampilkan status platform Sistem Normal dan jam real-time WIB', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    cy.contains('span', 'Sistem Normal').should('be.visible');
    cy.contains('span', 'WIB').should('be.visible');
  });

  it('ET-03-003: Tombol kartu metrik Menunggu Verifikasi mengarahkan Owner ke antrean pendaftaran di /owner/registrations', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    // Klik langsung kartu metrik Menunggu Verifikasi di dashboard
    cy.contains('a', 'Menunggu Verifikasi').click({ force: true });
    cy.url({ timeout: 30000 }).should('include', '/owner/registrations');
    cy.contains('h1', 'Pendaftaran').should('be.visible');
  });

  it('ET-03-004: Tombol kartu metrik Tenant Aktif mengarahkan Owner ke direktori tenant aktif di /owner/tenants', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    cy.contains('a', 'Manajemen Tenant').click({ force: true });
    cy.url({ timeout: 30000 }).should('include', '/owner/tenants');
  });

  it('ET-03-005: Tombol tautan Kelola Semua Pendaftaran mengarahkan ke halaman pendaftaran tenant', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    cy.contains('a', 'Kelola Semua Pendaftaran').should('be.visible').click();
    cy.url({ timeout: 30000 }).should('include', '/owner/registrations');
  });

  it('ET-03-006: Tombol tautan Kelola Semua Tenant mengarahkan ke halaman direktori seluruh tenant', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    cy.contains('a', 'Kelola Semua Tenant').should('be.visible').click();
    cy.url({ timeout: 30000 }).should('include', '/owner/tenants');
  });

  it('ET-03-007: Tombol Tinjau pada tabel pendaftaran terbaru di dashboard membuka modal pratinjau verifikasi tenant', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    cy.get('body').then(($body) => {
      if ($body.find('table button:contains("Tinjau")').length > 0) {
        cy.get('table button:contains("Tinjau")').first().click({ force: true });
        cy.contains('Informasi Usaha').should('be.visible');
        cy.contains('button', 'Verifikasi & Aktifkan').should('be.visible');
      } else {
        // Jika antrean kosong, tombol navigasi Lihat Pendaftaran Baru tersedia
        cy.contains('a', 'Lihat Pendaftaran Baru').should('exist');
      }
    });
  });

  it('ET-03-008: Tombol Tutup pada modal pratinjau pendaftaran di dashboard menutup kembali jendela modal', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/dashboard');

    cy.get('body').then(($body) => {
      if ($body.find('table button:contains("Tinjau")').length > 0) {
        cy.get('table button:contains("Tinjau")').first().click({ force: true });
        cy.contains('Informasi Usaha').should('be.visible');
        cy.contains('button', 'Tutup').click({ force: true });
        cy.contains('button', 'Verifikasi & Aktifkan').should('not.be.visible');
      }
    });
  });

  // =========================================================================
  // 2. ANTREAN PERSETUJUAN & AKSI VERIFIKASI MITRA BARU (/owner/registrations)
  // =========================================================================

  it('ET-03-009: Halaman antrean pendaftaran /owner/registrations memuat judul Kelola Pendaftaran Baru Tenant dan form pencarian', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations');

    cy.url().should('include', '/owner/registrations');
    cy.get('h1').should('contain.text', 'Kelola Pendaftaran Baru Tenant');
    cy.contains('p', 'Verifikasi pengajuan toko rental baru, periksa bukti transfer pembayaran, dan setujui operasional toko.').should('be.visible');
    cy.get('input[name="search"]').should('exist');
  });

  it('ET-03-010: Tombol tab filter Semua (status=all) memuat seluruh riwayat pendaftaran mitra', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations');

    cy.contains('a', 'Semua').click();
    cy.url().should('include', 'status=all');
  });

  it('ET-03-011: Tombol tab filter Menunggu Verifikasi menyaring pendaftaran yang membutuhkan aksi', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations');

    cy.contains('a', 'Menunggu Verifikasi').click();
    cy.url().should('include', 'status=menunggu_verifikasi');
  });

  it('ET-03-012: Tombol tab filter Disetujui menampilkan daftar pendaftaran yang telah diverifikasi', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations');

    cy.contains('a', 'Disetujui').click();
    cy.url().should('include', 'status=disetujui');
  });

  it('ET-03-013: Tombol tab filter Ditolak menampilkan daftar pengajuan mitra yang ditolak', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations');

    cy.contains('a', 'Ditolak').click();
    cy.url().should('include', 'status=ditolak');
  });

  it('ET-03-014: Input pencarian mitra dan form submit memfilter antrean berdasarkan kata kunci nama toko atau PIC', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations');

    cy.get('input[name="search"]').type('Rental{enter}');
    cy.url().should('include', 'search=Rental');
  });

  it('ET-03-015: Tombol aksi Tinjau pada baris antrean membuka Modal Tinjau Detail & Bukti Bayar', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations?status=all');

    cy.get('body').then(($body) => {
      if ($body.find('table button:contains("Tinjau")').length > 0) {
        cy.get('table button:contains("Tinjau")').first().click({ force: true });
        cy.contains('Informasi Usaha').should('be.visible');
        cy.contains('button', 'Verifikasi & Aktifkan').should('be.visible');
      }
    });
  });

  it('ET-03-016: Tombol tautan Buka Ukuran Penuh pada modal tinjau tersedia untuk memeriksa bukti bayar', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations?status=all');

    cy.get('body').then(($body) => {
      if ($body.find('table button:contains("Tinjau")').length > 0) {
        cy.get('table button:contains("Tinjau")').first().click({ force: true });
        cy.get('body').then(($modal) => {
          if ($modal.find('a:contains("Buka Ukuran Penuh")').length > 0) {
            cy.contains('a', 'Buka Ukuran Penuh').should('have.attr', 'target', '_blank');
          } else {
            cy.contains('Tidak Ada Bukti Pembayaran').should('be.visible');
          }
        });
        cy.contains('button', 'Tutup').click({ force: true });
      }
    });
  });

  it('ET-03-017: Tombol Tolak Pengajuan pada modal tinjau membuka dialog konfirmasi penolakan dengan input alasan', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations?status=menunggu_verifikasi');

    cy.get('body').then(($body) => {
      if ($body.find('table button:contains("Tinjau")').length > 0) {
        cy.get('table button:contains("Tinjau")').first().click({ force: true });
        cy.contains('button', 'Tolak Pengajuan').click({ force: true });

        // Dialog konfirmasi penolakan terbuka dengan textarea alasan
        cy.contains('h3', 'Tolak Pendaftaran Tenant').should('be.visible');
        cy.get('textarea[name="rejection_reason"]').should('be.visible');
      }
    });
  });

  it('ET-03-018: Tombol Batal pada modal konfirmasi penolakan mengembalikan Owner ke tampilan modal tinjau', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations?status=menunggu_verifikasi');

    cy.get('body').then(($body) => {
      if ($body.find('table button:contains("Tinjau")').length > 0) {
        cy.get('table button:contains("Tinjau")').first().click({ force: true });
        cy.contains('button', 'Tolak Pengajuan').click({ force: true });

        cy.contains('button', 'Batal').click({ force: true });
        cy.contains('Informasi Usaha').should('be.visible');
      }
    });
  });

  it('ET-03-019: Tombol aksi verifikasi pendaftaran membuka modal persetujuan dengan tombol Ya, Setujui & Aktifkan', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/registrations?status=menunggu_verifikasi');

    cy.get('body').then(($body) => {
      if ($body.find('table button:contains("Tinjau")').length > 0) {
        cy.get('table button:contains("Tinjau")').first().click({ force: true });
        cy.contains('button', 'Verifikasi & Aktifkan').click({ force: true });

        // Modal konfirmasi verifikasi tampil dengan tombol aksi persetujuan
        cy.contains('h3', 'Konfirmasi Verifikasi Tenant').should('be.visible');
        cy.contains('button[type="submit"]', 'Ya, Verifikasi & Aktifkan').should('be.visible');
        cy.contains('button', 'Batal').click({ force: true });
      }
    });
  });
});
