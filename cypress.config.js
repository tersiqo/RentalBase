import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8000',
    experimentalStudio: true,
    defaultCommandTimeout: 30000,
    pageLoadTimeout: 60000,
    responseTimeout: 60000,
    requestTimeout: 30000,
    screenshotOnRunFailure: false,
    video: false,
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
