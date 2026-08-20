import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8013';

export default defineConfig({
    testDir: './tests/Browser',
    outputDir: './build/playwright/results',
    fullyParallel: false,
    workers: 1,
    timeout: 120_000,
    expect: {
        timeout: 15_000,
    },
    retries: process.env.CI ? 1 : 0,
    reporter: [
        ['line'],
        ['json', { outputFile: 'build/playwright/report.json' }],
    ],
    use: {
        baseURL,
        screenshot: 'only-on-failure',
        trace: 'off',
        video: 'off',
    },
    projects: [
        {
            name: 'desktop-chromium',
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 1440, height: 1000 },
            },
        },
        {
            name: 'mobile-chromium',
            use: {
                ...devices['Pixel 5'],
            },
        },
    ],
    webServer:
        process.env.E2E_START_SERVER === 'true'
            ? {
                  command:
                      'php artisan serve --host=127.0.0.1 --port=8013 --no-reload',
                  url: baseURL,
                  reuseExistingServer: false,
                  timeout: 120_000,
                  stdout: 'ignore',
                  stderr: 'pipe',
              }
            : undefined,
});
