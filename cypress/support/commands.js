// Custom Cypress Commands for RentalBase

Cypress.Commands.add('loginCustomer', (email = 'rizky@gmail.com', password = 'password123') => {
  cy.session(['customer', email], () => {
    cy.visit('/login');
    cy.contains('button', 'Customer').click({ force: true });
    cy.get('#email').clear().type(email);
    cy.get('#password').clear().type(password);
    cy.contains('button[type="submit"]', 'Masuk').first().click();
    cy.url().should('not.include', '/login');
  });
});

Cypress.Commands.add('loginAdmin', (email = 'budi@babyrental.com', password = 'password123') => {
  cy.session(['admin', email], () => {
    cy.visit('/login');
    cy.contains('button', 'Admin Toko').click({ force: true });
    cy.get('#email').clear().type(email);
    cy.get('#password').clear().type(password);
    cy.contains('button[type="submit"]', 'Masuk').first().click();
    cy.url().should('not.include', '/login');
  });
});