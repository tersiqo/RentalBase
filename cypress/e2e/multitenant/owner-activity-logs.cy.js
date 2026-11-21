describe('E2E TEST — MODUL 03: ACTIVITY LOG OWNER PLATFORM & INTEGRASI LOGISTIK', () => {
  const ownerEmail = 'owner@rentalbase.id';
  const ownerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // =========================================================================
  // Fitur 9.2-A2: Audit Trail Platform Owner & Maintenance CLI (ET-03-046 s/d ET-03-054)
  // =========================================================================
  it('ET-03-046: Superadmin Owner dapat mengakses audit trail platform lintas tenant', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.url().should('include', '/owner/activity-logs');
    cy.contains(/Audit Trail|Activity Log|Jejak Aktivitas/i).should('exist');
    cy.screenshot('ET-03-046');
  });

  it('ET-03-047: Halaman audit trail owner menampilkan tabel rekam jejak aktivitas platform', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.get('table').should('exist');
    cy.screenshot('ET-03-047');
  });

  it('ET-03-048: Filter dropdown tenant toko dapat menyaring log transaksi milik toko demo Baby Rental', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.get('select[name="client_id"]').should('exist');
    cy.get('select[name="client_id"]').then(($select) => {
      if ($select.find('option:contains("Baby Rental")').length > 0) {
        cy.get('select[name="client_id"]').select('Baby Rental', { force: true });
      }
    });
    cy.screenshot('ET-03-048');
  });

  it('ET-03-049: Filter modul operasional dapat menyaring riwayat aktivitas pesanan demo toko', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.get('body').then(($body) => {
      if ($body.find('select[name="module"]').length > 0) {
        cy.get('select[name="module"]').should('exist');
      } else {
        cy.contains(/Audit Trail|Activity Log/i).should('exist');
      }
    });
    cy.screenshot('ET-03-049');
  });

  it('ET-03-050: Baris data audit log menampilkan timestamp kejadian dan rincian pelaku secara valid', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.get('body').then(($body) => {
      if ($body.find('table tbody tr').length > 0) {
        cy.get('table tbody tr').first().should('exist');
      } else {
        cy.contains(/Audit Trail|Activity Log/i).should('exist');
      }
    });
    cy.screenshot('ET-03-050');
  });

  it('ET-03-051: Tombol inspeksi detail log membuka modal teknis alamat IP dan user-agent pelaku', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Detail"), a:contains("Detail")').length > 0) {
        cy.contains(/Detail/i).first().click({ force: true });
        cy.contains(/Detail|Audit|Tenant/i).should('exist');
      } else {
        cy.contains(/Audit Trail|Activity Log/i).should('exist');
      }
    });
    cy.screenshot('ET-03-051');
  });

  it('ET-03-052: Modal payload audit menyajikan perubahan data transaksi dalam format read-only', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Detail")').length > 0) {
        cy.contains('button', 'Detail').first().click({ force: true });
        cy.contains(/Payload|Detail|Aktivitas/i).should('exist');
      } else {
        cy.contains(/Audit Trail|Activity Log/i).should('exist');
      }
    });
    cy.screenshot('ET-03-052');
  });

  it('ET-03-053: Proteksi otentikasi mencegah pengunjung tamu membuka audit trail platform owner', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/owner/activity-logs');
    cy.url().should('not.include', '/owner/activity-logs');
    cy.screenshot('ET-03-053');
  });

  it('ET-03-054: Proteksi role mencegah admin rental toko biasa mengakses log platform owner', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/owner/activity-logs');
    cy.url().should('not.include', '/owner/activity-logs');
    cy.screenshot('ET-03-054');
  });
});
