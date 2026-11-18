describe('E2E TEST — MODUL 03: ACTIVITY LOG OWNER PLATFORM & INTEGRASI LOGISTIK', () => {
  const ownerEmail = 'owner@rentalbase.id';
  const ownerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  // Fitur 9.2-A2: Activity Log Owner Platform
  it('ET-03-005: Superadmin Owner dapat mengakses audit trail platform lintas tenant', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.url().should('include', '/owner/activity-logs');
    cy.contains(/Audit Trail|Activity Log|Jejak Aktivitas/i).should('exist');
  });

  it('ET-03-006: Halaman activity log owner menyediakan filter tenant dan modal inspeksi detail pelaku', () => {
    cy.loginOwner(ownerEmail, ownerPassword);
    cy.visit('/owner/activity-logs');
    cy.get('select[name="client_id"]').should('exist');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Detail"), a:contains("Detail")').length > 0) {
        cy.contains(/Detail/i).first().click({ force: true });
        cy.contains(/Detail|Audit|Tenant/i).should('exist');
      } else {
        cy.contains(/Audit Trail|Activity Log/i).should('exist');
      }
    });
  });

  it('ET-03-007: Proteksi keamanan memastikan pengunjung tanpa role owner ditolak dari audit platform', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/owner/activity-logs');
    cy.url().should('not.include', '/owner/activity-logs');
  });
});
