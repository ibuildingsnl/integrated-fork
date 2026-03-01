import { defineConfig } from '@playwright/test';

const baseURL = process.env.TOOLBAR_E2E_BASE_URL || 'https://bakkersinbedrijf.localhost.e-active.nl';

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 45_000,
  expect: {
    timeout: 10_000,
  },
  use: {
    baseURL,
    ignoreHTTPSErrors: true,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  reporter: [['list']],
});
