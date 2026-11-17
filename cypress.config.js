import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8000',
    defaultCommandTimeout: 15000,
    pageLoadTimeout: 60000,
    screenshotOnRunFailure: false,
    video: false,
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
