// Custom Cypress Commands for RentalBase

Cypress.Commands.add('loginCustomer', (email = 'rizky@gmail.com', password = 'password123') => {
  cy.session(['customer', email, password], () => {
    cy.visit('/login');
    cy.contains('button', 'Customer').click({ force: true });
    cy.get('#email').clear().type(email);
    cy.get('#password').clear().type(`${password}{enter}`);
    cy.get('body').then(($body) => {
      if ($body.text().includes('Akses Masuk Ditolak')) {
        const fallback = password === 'password123' ? '12345678' : 'password123';
        cy.get('#password').clear().type(`${fallback}{enter}`);
      }
    });
    cy.url({ timeout: 30000 }).should('not.include', '/login');
  });
});

Cypress.Commands.add('loginAdmin', (email = 'budi@babyrental.com', password = 'password123') => {
  cy.session(['admin', email, password], () => {
    cy.visit('/login');
    cy.contains('button', 'Admin Toko').click({ force: true });
    cy.get('#email').clear().type(email);
    cy.get('#password').clear().type(`${password}{enter}`);
    cy.url({ timeout: 30000 }).should('not.include', '/login');
  });
});

Cypress.Commands.add('loginOwner', (email = 'owner@rentalbase.id', password = 'password123') => {
  cy.session(['owner', email], () => {
    cy.visit('/owner/login');
    cy.get('#email').clear().type(email);
    cy.get('#password').clear().type(`${password}{enter}`);
    cy.url({ timeout: 30000 }).should('include', '/owner/dashboard');
  });
});
