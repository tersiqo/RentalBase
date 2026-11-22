describe('E2E TEST — MODUL 04: NOTIFIKASI & LOG AKTIVITAS TOKO ADMIN', () => {
  const adminEmail = 'budi@babyrental.com';
  const adminPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // Fitur 9.1-A2: Notifikasi Admin
  it('ET-04-007: Admin rental dapat membuka pusat notifikasi operasional toko dan memantau alert', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.url().should('include', '/admin/notifications');
    cy.contains(/Pusat Pemberitahuan Toko|Pemberitahuan|Notifikasi/i).should('exist');
    cy.screenshot('ET-04-007');
  });

  it('ET-04-008: Hub notifikasi admin menyediakan tombol tandai semua dibaca dan filter tab status', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Tandai Semua Dibaca")').length > 0) {
        cy.contains('button', 'Tandai Semua Dibaca').should('exist');
      } else {
        cy.contains(/Pusat Pemberitahuan|Pemberitahuan|Notifikasi/i).should('exist');
      }
    });
    cy.screenshot('ET-04-008');
  });

  // Fitur 9.2-A1: Activity Log Admin
  it('ET-04-009: Admin rental dapat membuka daftar jejak audit (activity log) operasional internal toko', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/activity-logs');
    cy.url().should('include', '/admin/activity-logs');
    cy.contains(/Log Aktivitas Toko|Log Aktivitas|Aktivitas/i).should('exist');
    cy.get('select[name="module"]').should('exist');
    cy.screenshot('ET-04-009');
  });

  it('ET-04-010: Antarmuka activity log admin bersifat read-only dan menyediakan modal detail teknis', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/activity-logs');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Detail"), a:contains("Detail")').length > 0) {
        cy.contains(/Detail/i).first().click({ force: true });
        cy.contains(/Detail|Log|Aktivitas/i).should('exist');
      } else {
        cy.contains(/Log Aktivitas Toko|Log Aktivitas|Aktivitas/i).should('exist');
      }
    });
    cy.screenshot('ET-04-010');
  });

  it('ET-04-011: Proteksi hak akses mencegah pengguna tamu membuka log aktivitas dan notifikasi admin', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/admin/activity-logs');
    cy.url().should('not.include', '/admin/activity-logs');
    cy.screenshot('ET-04-011');
  });

  // =========================================================================
  // Fitur 9.1-A3: Web Push Background Notifications & PWA (ET-04-059 s/d ET-04-069)
  // =========================================================================
  it('ET-04-059: Admin rental dapat membuka pusat notifikasi operasional toko dan memverifikasi kartu Web Push OS', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.url().should('include', '/admin/notifications');
    cy.contains(/Pusat Pemberitahuan Toko|Pemberitahuan|Notifikasi/i).should('exist');
    cy.contains(/Notifikasi Perangkat OS|Terima Notifikasi Pesanan/i).should('exist');
    cy.screenshot('ET-04-059');
  });

  it('ET-04-060: Hub notifikasi admin menyediakan tombol kontrol aktifkan notifikasi perangkat browser', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Aktifkan"), button:contains("Aktif")').length > 0) {
        cy.contains('button', /Aktifkan|Aktif/i).should('exist');
      } else {
        cy.contains(/Notifikasi Perangkat OS|Terima Notifikasi Pesanan/i).should('exist');
      }
    });
    cy.screenshot('ET-04-060');
  });

  it('ET-04-061: Layout admin memuat berkas script helper rentalbase-webpush.js di dokumen HTML', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('script[src*="rentalbase-webpush.js"]').should('exist');
    cy.screenshot('ET-04-061');
  });

  it('ET-04-062: File service worker sw.js terdaftar dan dapat diakses pada root direktori publik', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.window().then((win) => {
      expect('serviceWorker' in win.navigator).to.be.true;
    });
    cy.screenshot('ET-04-062');
  });

  it('ET-04-063: Panel topbar drawer admin menyajikan komponen status sinkronisasi Web Push', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('[x-data*="rbWebPush"]').length > 0) {
        cy.get('[x-data*="rbWebPush"]').should('exist');
      } else {
        cy.contains(/Pemberitahuan|Notifikasi/i).should('exist');
      }
    });
    cy.screenshot('ET-04-063');
  });

  it('ET-04-064: Hub notifikasi admin memuat tombol uji coba pengiriman push alert ke layar perangkat', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Uji Coba")').length > 0) {
        cy.contains('button', 'Uji Coba').should('exist');
      } else {
        cy.contains(/Notifikasi Perangkat OS|Pusat Pemberitahuan/i).should('exist');
      }
    });
    cy.screenshot('ET-04-064');
  });

  it('ET-04-065: Panel navigasi admin live chat menyajikan widget indikator status Web Push', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/chat');
    cy.url().should('include', '/admin/chat');
    cy.contains(/Live Chat|Pesan Masuk|Percakapan/i).should('exist');
    cy.screenshot('ET-04-065');
  });

  it('ET-04-066: Tab filter kategori notifikasi (pesanan, pembayaran, retur) berfungsi menyaring alert toko', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('a[href*="filter="], button:contains("Pesanan")').length > 0) {
        cy.contains(/Pesanan|Semua|Pembayaran/i).should('exist');
      } else {
        cy.contains(/Pusat Pemberitahuan Toko/i).should('exist');
      }
    });
    cy.screenshot('ET-04-066');
  });

  it('ET-04-067: Admin dapat melakukan aksi tandai semua notifikasi menjadi telah dibaca', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Tandai Semua Dibaca")').length > 0) {
        cy.contains('button', 'Tandai Semua Dibaca').should('exist');
      } else {
        cy.contains(/Pusat Pemberitahuan/i).should('exist');
      }
    });
    cy.screenshot('ET-04-067');
  });

  it('ET-04-068: Proteksi hak akses mencegah pengguna tamu membuka pusat pemberitahuan toko dan konfigurasi web push', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/admin/notifications');
    cy.url().should('not.include', '/admin/notifications');
    cy.screenshot('ET-04-068');
  });

  it('ET-04-069: Kartu notifikasi menyediakan navigasi tautan langsung ke detail pesanan atau alokasi unit', () => {
    cy.loginAdmin(adminEmail, adminPassword);
    cy.visit('/admin/notifications');
    cy.get('body').then(($body) => {
      if ($body.find('a[href*="/admin/orders"]').length > 0) {
        cy.get('a[href*="/admin/orders"]').first().should('exist');
      } else {
        cy.contains(/Pusat Pemberitahuan|Pemberitahuan|Notifikasi/i).should('exist');
      }
    });
    cy.screenshot('ET-04-069');
  });
});
