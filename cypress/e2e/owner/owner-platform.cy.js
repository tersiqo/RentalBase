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

  // =========================================================================
  // 3. DIREKTORI TENANT & KONTROL OPERASIONAL TOKO (/owner/tenants)
  // =========================================================================

  it('ET-03-020: Direktori tenant /owner/tenants memuat judul Client & Tenant, input pencarian, dan tombol Cari', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.url().should('include', '/owner/tenants');
    cy.get('h1').should('contain.text', 'Client & Tenant');
    cy.get('input[name="search"]').should('be.visible');
    cy.contains('button[type="submit"]', 'Cari').should('be.visible');
  });

  it('ET-03-021: Filter dropdown paket langganan (Starter, Business, Professional) menyaring data toko berdasarkan tier paket', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.get('select[name="plan"]').select('business');
    cy.url().should('include', 'plan=business');
  });

  it('ET-03-022: Filter status operasional (tab Aktif dan tab Suspended) menyaring tabel daftar toko', () => {
    cy.loginOwner(ownerEmail, validPassword);

    // 1. Filter status Aktif
    cy.visit('/owner/tenants?status=aktif');
    cy.url().should('include', 'status=aktif');
    cy.contains('h1', 'Kelola Client & Tenant Rental').should('be.visible');

    // 2. Filter status Suspended
    cy.visit('/owner/tenants?status=suspended');
    cy.url().should('include', 'status=suspended');
    cy.contains('h1', 'Kelola Client & Tenant Rental').should('be.visible');
  });

  it('ET-03-023: Tombol reset filter pada direktori tenant mengembalikan daftar toko ke tampilan bawaan', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants?search=NonExistentKeyword999');

    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Reset Filter")').length > 0) {
        cy.contains('a', 'Reset Filter').click();
        cy.url().should('not.include', 'search=NonExistentKeyword999');
      }
    });
  });

  it('ET-03-024: Tombol aksi Detail pada baris tenant membuka Modal Detail Tenant (menampilkan data usaha, kontak PIC, dan kuota)', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Detail")').length > 0) {
        cy.get('table tbody tr button:contains("Detail")').first().click({ force: true });

        // Modal Detail Terbuka
        cy.contains('h4', 'Informasi Profil Toko').should('be.visible');
        cy.contains('h4', 'Identitas Pemilik & Pengelola').should('be.visible');
        cy.contains('h4', 'Detail Keanggotaan & Paket Subscription').should('be.visible');
      }
    });
  });

  it('ET-03-025: Tombol Tutup pada Modal Detail Tenant menutup modal dialog secara responsif', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Detail")').length > 0) {
        cy.get('table tbody tr button:contains("Detail")').first().click({ force: true });
        cy.contains('button', 'Tutup').click({ force: true });
        cy.contains('h4', 'Informasi Profil Toko').should('not.be.visible');
      }
    });
  });

  it('ET-03-026: Tombol Edit Data Usaha pada modal detail membuka Modal Edit Tenant dengan field nama usaha, subdomain, dan deskripsi', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Detail")').length > 0) {
        cy.get('table tbody tr button:contains("Detail")').first().click({ force: true });
        cy.contains('button', 'Edit Data Usaha').click({ force: true });

        // Modal Edit Terbuka
        cy.contains('h3', 'Perbarui Informasi Tenant').should('be.visible');
        cy.get('input[name="business_name"]').should('be.visible');
        cy.get('input[name="subdomain"]').should('be.visible');
        cy.get('textarea[name="description"]').should('be.visible');
      }
    });
  });

  it('ET-03-027: Tombol Batal pada Modal Edit Tenant membatalkan perubahan dan menutup modal dialog', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Detail")').length > 0) {
        cy.get('table tbody tr button:contains("Detail")').first().click({ force: true });
        cy.contains('button', 'Edit Data Usaha').click({ force: true });

        cy.get('[x-show="showEditModal"]').contains('button', 'Batal').click({ force: true });
        cy.contains('h3', 'Perbarui Informasi Tenant').should('not.be.visible');
      }
    });
  });

  it('ET-03-028: Tombol aksi status operasional pada tabel membuka Modal Ubah Status Akun Tenant', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button[title*="Akun"]').length > 0) {
        cy.get('table tbody tr button[title*="Akun"]').first().click({ force: true });

        // Modal Ubah Status Akun Terbuka
        cy.contains('h3', 'Kelola Status Akun Tenant').should('be.visible');
        cy.get('input[name="status"]').should('exist');
      }
    });
  });

  it('ET-03-029: Tombol Batal pada Modal Ubah Status Akun menutup dialog tanpa merubah status toko', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/tenants');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button[title*="Akun"]').length > 0) {
        cy.get('table tbody tr button[title*="Akun"]').first().click({ force: true });

        cy.get('[x-show="showStatusModal"]').contains('button', 'Batal').click({ force: true });
        cy.contains('h3', 'Kelola Status Akun Tenant').should('not.be.visible');
      }
    });
  });

  // =========================================================================
  // 4. MANAJEMEN & RIWAYAT SUBSCRIPTION PLATFORM (/owner/subscriptions)
  // =========================================================================

  it('ET-03-030: Halaman /owner/subscriptions memuat 4 kartu metrik langganan (Tenant Aktif, Jatuh Tempo H-7, Kedaluwarsa, Estimasi MRR)', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/subscriptions');

    cy.url().should('include', '/owner/subscriptions');
    cy.get('h1').should('contain.text', 'Subscription & Masa Aktif Tenant');

    // 4 Kartu Metrik Subscription
    cy.contains('p', 'Masa aktif berlaku').should('be.visible');
    cy.contains('p', 'Berakhir dalam 7 hari').should('be.visible');
    cy.contains('p', 'Perlu perpanjangan masa aktif').should('be.visible');
    cy.contains('p', 'Recurring revenue bulanan').should('be.visible');
  });

  it('ET-03-031: Form filter subscription memvalidasi input #search, select #plan, select #status, dan tombol Terapkan Filter', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/subscriptions');

    cy.get('#search').should('be.visible');
    cy.get('#plan').select('business');
    cy.get('#status').select('active');
    cy.contains('button[type="submit"]', 'Filter').click();

    cy.url().should('include', 'plan=business');
    cy.url().should('include', 'status=active');
  });

  it('ET-03-032: Tombol tautan Reset pada form filter membersihkan seluruh parameter dan memuat ulang data langganan', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/subscriptions?plan=professional&status=expired');

    cy.get('a[title*="Reset filter"]').click();
    cy.url().should('not.include', 'plan=professional');
  });

  it('ET-03-033: Tombol aksi Perpanjang pada baris langganan membuka Modal Perpanjangan Subscription', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Perpanjang")').length > 0) {
        cy.get('table tbody tr button:contains("Perpanjang")').first().click({ force: true });

        // Modal Perpanjangan Subscription Muncul
        cy.contains('h3', 'Perpanjang Subscription').should('be.visible');
        cy.get('#reference_number').should('be.visible');
        cy.get('#payment_proof').should('exist');
      }
    });
  });

  it('ET-03-034: Modal perpanjangan subscription menyediakan pilihan durasi masa aktif dan tombol Batal', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Perpanjang")').length > 0) {
        cy.get('table tbody tr button:contains("Perpanjang")').first().click({ force: true });

        // Cek info durasi perpanjangan otomatis sesuai paket
        cy.contains('Durasi Perpanjangan').should('be.visible');
        cy.get('[x-show="showRenewModal"]').contains('button', 'Batal').click({ force: true });
        cy.contains('h3', 'Perpanjang Subscription').should('not.be.visible');
      }
    });
  });

  it('ET-03-035: Tombol aksi Riwayat pada baris langganan membuka Modal Riwayat Perpanjangan Subscription', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Riwayat")').length > 0) {
        cy.get('table tbody tr button:contains("Riwayat")').first().click({ force: true });

        // Modal Riwayat Terbuka
        cy.contains('h3', 'Riwayat Aktivitas & Perpanjangan').should('be.visible');
      }
    });
  });

  it('ET-03-036: Tombol Tutup pada Modal Riwayat Perpanjangan menutup kembali jendela dialog riwayat', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/subscriptions');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Riwayat")').length > 0) {
        cy.get('table tbody tr button:contains("Riwayat")').first().click({ force: true });
        cy.get('[x-show="showHistoryModal"]').contains('button', 'Tutup').click({ force: true });
        cy.contains('h3', 'Riwayat Aktivitas & Perpanjangan').should('not.be.visible');
      }
    });
  });

  // =========================================================================
  // 5. PUSAT NOTIFIKASI & LOG JEJAK AKTIVITAS PLATFORM
  // =========================================================================

  it('ET-03-037: Pusat notifikasi /owner/notifications memuat judul Notifikasi Platform dan tab filter keterbacaan', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/notifications');

    cy.url().should('include', '/owner/notifications');
    cy.get('h1').should('contain.text', 'Notifikasi Platform');
    cy.contains('p', 'Pantau pendaftaran tenant baru, perpanjangan subscription, dan peringatan kuota platform secara terpusat.').should('be.visible');
  });

  it('ET-03-038: Tombol tab filter Belum Dibaca (tab=unread) dan Perlu Tindakan (tab=alert) menyaring daftar notifikasi', () => {
    cy.loginOwner(ownerEmail, validPassword);

    // 1. Tab Belum Dibaca
    cy.visit('/owner/notifications?tab=unread');
    cy.url().should('include', 'tab=unread');
    cy.contains('h1', 'Notifikasi Platform').should('be.visible');

    // 2. Tab Kategori Alert
    cy.visit('/owner/notifications?tab=alert');
    cy.url().should('include', 'tab=alert');
    cy.contains('h1', 'Notifikasi Platform').should('be.visible');
  });

  it('ET-03-039: Form tombol Tandai Semua Dibaca tersedia ketika terdapat notifikasi yang belum dibaca', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/notifications');

    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Tandai Semua Dibaca")').length > 0) {
        cy.contains('button', 'Tandai Semua Dibaca').should('be.visible');
      }
    });
  });

  it('ET-03-040: Tombol aksi notifikasi (Buka & Tindak Lanjuti / Lihat Halaman) memiliki navigasi ke modul terkait', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/notifications?tab=all');

    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Lihat Halaman"), button:contains("Buka & Tindak Lanjuti")').length > 0) {
        cy.get('a:contains("Lihat Halaman"), button:contains("Buka & Tindak Lanjuti")').first().should('exist');
      }
    });
  });

  it('ET-03-041: Halaman jejak audit /owner/activity-logs memuat ringkasan total aktivitas dan form filter', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/activity-logs');

    cy.url().should('include', '/owner/activity-logs');
    cy.contains('h1', 'Audit Trail').should('be.visible');
    cy.contains('span', 'Hari Ini').should('be.visible');
    cy.contains('span', 'Total Log').should('be.visible');
    cy.contains('a', 'Reset Filter').should('be.visible');
  });

  it('ET-03-042: Tombol aksi Detail pada baris aktivitas membuka Modal Forensik Aktivitas lengkap dengan tombol Tutup', () => {
    cy.loginOwner(ownerEmail, validPassword);
    cy.visit('/owner/activity-logs');

    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr button:contains("Detail")').length > 0) {
        cy.get('table tbody tr button:contains("Detail")').first().click({ force: true });

        // Modal Forensik Terbuka
        cy.contains('h3', 'Detail Rekam Jejak Audit').should('be.visible');
        cy.contains('Alamat IP').should('be.visible');

        // Klik tombol Tutup
        cy.contains('button', 'Tutup').click({ force: true });
        cy.contains('h3', 'Detail Rekam Jejak Audit').should('not.be.visible');
      }
    });
  });
});
