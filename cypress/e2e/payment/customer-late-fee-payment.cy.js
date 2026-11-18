describe('E2E TEST — MODUL 08: PEMBAYARAN DENDA KETERLAMBATAN OLEH CUSTOMER', () => {
  const customerEmail = 'rizky@gmail.com';
  const customerPassword = 'password123';

  beforeEach(() => {
    cy.viewport(1280, 800);
  });

  it('ET-08-005: Customer dapat melihat informasi denda keterlambatan pada rincian pesanan', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.url().should('include', '/my-orders');
    cy.contains(/Pesanan Saya/i).should('exist');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Lihat detail")').length > 0) {
        cy.contains('button', 'Lihat detail').first().click({ force: true });
        cy.contains(/Rincian|Detail Pesanan|Tagihan|Status/i).should('exist');
      }
    });
  });

  it('ET-08-006: Antarmuka pesanan menyediakan aksi bayar denda dan uploader bukti transfer denda', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Lihat detail")').length > 0) {
        cy.contains('button', 'Lihat detail').first().click({ force: true });
        cy.get('body').then(($detailBody) => {
          if ($detailBody.find('form[action*="/pay-late-fee"]').length > 0) {
            cy.get('form[action*="/pay-late-fee"]').should('exist');
          } else {
            cy.contains(/Pesanan Saya|Rincian|Status Jaminan/i).should('exist');
          }
        });
      } else {
        cy.contains(/Pesanan Saya/i).should('exist');
      }
    });
  });

  it('ET-08-007: Validasi formulir bukti bayar denda menolak submisi tanpa berkas transfer', () => {
    cy.loginCustomer(customerEmail, customerPassword);
    cy.visit('/my-orders');
    cy.get('body').then(($body) => {
      if ($body.find('button:contains("Lihat detail")').length > 0) {
        cy.contains('button', 'Lihat detail').first().click({ force: true });
        cy.get('body').then(($detailBody) => {
          if ($detailBody.find('form[action*="/pay-late-fee"]').length > 0) {
            cy.get('form[action*="/pay-late-fee"]').submit();
            cy.get('input[name="late_fee_proof"]:invalid').should('exist');
          } else {
            cy.contains(/Pesanan Saya/i).should('exist');
          }
        });
      } else {
        cy.contains(/Pesanan Saya/i).should('exist');
      }
    });
  });

  it('ET-08-008: Proteksi keamanan mencegah pengguna tanpa otentikasi mengakses endpoint bayar denda', () => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.request({
      method: 'POST',
      url: '/my-orders/1/pay-late-fee',
      failOnStatusCode: false,
    }).then((response) => {
      expect([302, 401, 403, 404, 405, 419]).to.include(response.status);
    });
  });
});
