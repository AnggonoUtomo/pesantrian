import { expect, test } from '@playwright/test';
import type { Locator, Page } from '@playwright/test';
import { expectNoHighImpactViolations } from './support/accessibility';
import {
    cleanupTahfidzFixture,
    createTahfidzFixture,
} from './support/tahfidz-fixture';
import type { TahfidzFixture } from './support/tahfidz-fixture';

test.describe('Tahfidz browser QA', () => {
    let fixture: TahfidzFixture | undefined;

    test.beforeEach(({ browserName }, testInfo) => {
        void browserName;
        fixture = createTahfidzFixture(testInfo);
    });

    test.afterEach(() => {
        if (fixture) {
            cleanupTahfidzFixture(fixture);
        }
    });

    test('operator membuka list, detail, dan dialog lifecycle tahfidz', async ({
        page,
    }) => {
        test.setTimeout(180_000);

        const browserErrors: string[] = [];
        const forbiddenResponses: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') {
                browserErrors.push(message.text());
            }
        });
        page.on('pageerror', (error) => browserErrors.push(error.message));
        page.on('response', (response) => {
            if (response.status() === 403) {
                forbiddenResponses.push(
                    `${response.request().method()} ${response.url()}`,
                );
            }
        });

        const currentFixture = fixture;

        if (!currentFixture) {
            throw new Error('Fixture Tahfidz belum siap.');
        }

        await loginAsFixtureUser(page, currentFixture);

        await test.step('membuka list dan filter Tahfidz', async () => {
            await page.goto('/pesantrian/tahfidz');
            await expect(
                page.getByRole('heading', {
                    name: 'Tahfidz / Hafalan',
                    exact: true,
                }),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);

            await page.getByRole('searchbox', { name: 'Cari setoran' }).fill(
                currentFixture.programCode,
            );
            await page.getByRole('button', { name: 'Terapkan' }).click();
            await expect(
                tahfidzRecord(page, currentFixture.studentName),
            ).toBeVisible();
            await expect(
                tahfidzRecord(page, currentFixture.studentName).filter({
                    hasText: currentFixture.programName,
                }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Tambah program' }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Tambah target' }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Tambah setoran' }),
            ).toBeVisible();
        });

        await test.step('membuka dialog mutation utama', async () => {
            await page.getByRole('button', { name: 'Tambah program' }).click();
            await expect(page.getByRole('dialog')).toContainText('Program tahfidz');
            await page.keyboard.press('Escape');

            await page.getByRole('button', { name: 'Tambah target' }).click();
            await expect(page.getByRole('dialog')).toContainText('Target hafalan');
            await page.keyboard.press('Escape');

            await page.getByRole('button', { name: 'Tambah setoran' }).click();
            await expect(page.getByRole('dialog')).toContainText(
                'Tambah setoran tahfidz',
            );
            await page.keyboard.press('Escape');
        });

        await test.step('membuka detail dan lifecycle action', async () => {
            await tahfidzRecord(page, currentFixture.studentName)
                .getByRole('link', { name: 'Lihat detail' })
                .first()
                .click();
            await expect(page).toHaveURL(/\/pesantrian\/tahfidz\/[A-Z0-9]+$/);
            await expect(page.getByText(currentFixture.studentName)).toBeVisible();
            await expect(page.getByText('Menunggu review')).toBeVisible();
            await expect(page.getByText('Ringkasan review')).toBeVisible();
            await expectNoHighImpactViolations(page);

            await expect(
                page.getByRole('button', { name: 'Edit setoran' }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Review setoran' }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Batalkan setoran' }),
            ).toBeVisible();

            await page.getByRole('button', { name: 'Review setoran' }).click();
            await expect(page.getByRole('dialog')).toContainText(
                'Review setoran tahfidz',
            );
            await page.keyboard.press('Escape');
        });

        expect({ browserErrors, forbiddenResponses }).toEqual({
            browserErrors: [],
            forbiddenResponses: [],
        });
    });
});

async function loginAsFixtureUser(
    page: Page,
    fixture: TahfidzFixture,
): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Email address').fill(fixture.email);
    await page
        .getByRole('textbox', { name: 'Password', exact: true })
        .fill(fixture.password);
    const loginResponsePromise = page.waitForResponse(
        (response) =>
            response.url().endsWith('/login') &&
            response.request().method() === 'POST',
    );

    await page.getByRole('button', { name: /log in/i }).click();

    const loginResponse = await loginResponsePromise;
    const redirectTarget = loginResponse.headers().location;

    if (
        loginResponse.status() < 300 ||
        loginResponse.status() >= 400 ||
        !redirectTarget ||
        redirectTarget.endsWith('/login')
    ) {
        throw new Error(
            `Login fixture Tahfidz ditolak: status ${loginResponse.status()}, location ${loginResponse.headers().location ?? '-'}`,
        );
    }
}

function tahfidzRecord(page: Page, studentName: string): Locator {
    return page
        .locator('tr:visible, article:visible')
        .filter({ hasText: studentName })
        .first();
}
