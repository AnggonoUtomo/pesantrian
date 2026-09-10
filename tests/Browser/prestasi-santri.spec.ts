import { expect, test } from '@playwright/test';
import type { Locator, Page } from '@playwright/test';
import { expectNoHighImpactViolations } from './support/accessibility';
import {
    cleanupPrestasiSantriFixture,
    createPrestasiSantriFixture,
} from './support/prestasi-santri-fixture';
import type { PrestasiSantriFixture } from './support/prestasi-santri-fixture';

test.describe('Prestasi Santri browser QA', () => {
    let fixture: PrestasiSantriFixture | undefined;

    test.beforeEach(({ browserName }, testInfo) => {
        void browserName;
        fixture = createPrestasiSantriFixture(testInfo);
    });

    test.afterEach(() => {
        if (fixture) {
            cleanupPrestasiSantriFixture(fixture);
        }
    });

    test('operator membuka list, detail, dan lifecycle prestasi', async ({
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
            throw new Error('Fixture PrestasiSantri belum siap.');
        }

        await loginAsFixtureUser(page, currentFixture);

        await test.step('membuka list dan filter Prestasi Santri', async () => {
            await page.goto('/pesantrian/prestasi-santri');
            await expect(
                page.getByRole('heading', {
                    name: 'Prestasi Santri',
                    exact: true,
                }),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);

            await page
                .getByRole('searchbox', { name: 'Cari prestasi' })
                .fill(currentFixture.studentNo);
            await page.getByRole('button', { name: 'Terapkan' }).click();
            await expect(
                achievementRecord(page, currentFixture.achievementTitle),
            ).toBeVisible();
            await expect(
                achievementRecord(page, currentFixture.submittedTitle),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Buat kategori' }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Tambah prestasi' }),
            ).toBeVisible();
        });

        await test.step('membuat kategori dan draft prestasi lewat UI', async () => {
            await page.getByRole('button', { name: 'Buat kategori' }).click();
            const categoryDialog = page.getByRole('dialog');
            await expect(categoryDialog).toContainText(
                'Buat kategori prestasi',
            );
            await categoryDialog
                .getByLabel('Kode kategori')
                .fill(currentFixture.newCategoryCode);
            await categoryDialog
                .getByLabel('Nama kategori')
                .fill(currentFixture.newCategoryName);
            await categoryDialog
                .getByLabel('Deskripsi')
                .fill('Kategori dibuat dari browser QA Prestasi Santri.');
            await categoryDialog
                .getByRole('button', { name: 'Buat kategori' })
                .click();
            await expect(
                page.getByText('Kategori prestasi santri berhasil dibuat.'),
            ).toBeVisible();

            await page.getByRole('button', { name: 'Tambah prestasi' }).click();
            const draftDialog = page.getByRole('dialog');
            await expect(draftDialog).toContainText('Buat draft prestasi');
            await selectOption(
                draftDialog,
                page,
                'Santri',
                currentFixture.studentName,
            );
            await selectOption(
                draftDialog,
                page,
                'Kategori',
                currentFixture.newCategoryName,
            );
            await selectOption(draftDialog, page, 'Jenis prestasi', 'Lomba');
            await selectOption(draftDialog, page, 'Tingkat', 'Kabupaten');
            await draftDialog
                .getByLabel('Judul prestasi')
                .fill(currentFixture.newAchievementTitle);
            await draftDialog.getByLabel('Hasil').fill('Juara harapan');
            await draftDialog.getByLabel('Tanggal prestasi').fill('2027-09-17');
            await draftDialog
                .getByLabel('Penyelenggara')
                .fill('Panitia browser QA');
            await draftDialog
                .getByLabel('Deskripsi')
                .fill('Draft prestasi dibuat dari browser QA.');
            await draftDialog
                .getByRole('button', { name: 'Buat draft prestasi' })
                .click();

            await expect(
                page.getByText('Draft prestasi santri berhasil dibuat.'),
            ).toBeVisible();
            await expect(page).toHaveURL(/\/pesantrian\/prestasi-santri\//);
            await expect(
                page.getByRole('heading', {
                    name: currentFixture.newAchievementTitle,
                    exact: true,
                }).first(),
            ).toBeVisible();
        });

        await test.step('submit dan void draft dari detail', async () => {
            await page.getByRole('button', { name: 'Submit' }).click();
            await page
                .getByRole('dialog')
                .getByRole('button', { name: 'Submit prestasi' })
                .click();
            await expect(
                page.getByText('Draft prestasi santri berhasil disubmit.'),
            ).toBeVisible();
            await expect(
                page.getByText('Menunggu verifikasi', { exact: true }),
            ).toBeVisible();

            await page.getByRole('button', { name: 'Batalkan' }).click();
            const voidDialog = page.getByRole('dialog');
            await voidDialog
                .getByLabel('Alasan pembatalan')
                .fill('Draft browser QA dibatalkan setelah submit.');
            await voidDialog
                .getByRole('button', { name: 'Batalkan prestasi' })
                .click();
            await expect(
                page.getByText('Prestasi santri berhasil dibatalkan.'),
            ).toBeVisible();
            await expect(
                page
                    .locator('[data-slot="badge"]')
                    .filter({ hasText: 'Dibatalkan' })
                    .first(),
            ).toBeVisible();
        });

        await test.step('membuka detail submitted dan dialog verifikasi/revisi', async () => {
            await page.goto('/pesantrian/prestasi-santri');
            await page
                .getByRole('searchbox', { name: 'Cari prestasi' })
                .fill(currentFixture.submittedTitle);
            await page.getByRole('button', { name: 'Terapkan' }).click();
            await achievementRecord(page, currentFixture.submittedTitle)
                .getByRole('link', { name: 'Lihat detail' })
                .first()
                .click();
            await expect(page).toHaveURL(
                /\/pesantrian\/prestasi-santri\/[A-Z0-9]+$/,
            );
            await expect(
                page
                    .getByRole('heading', {
                        name: currentFixture.submittedTitle,
                        exact: true,
                    })
                    .first(),
            ).toBeVisible();
            await expect(
                page
                    .locator('[data-slot="badge"]')
                    .filter({ hasText: 'Menunggu verifikasi' })
                    .first(),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);

            await page.getByRole('button', { name: 'Verifikasi' }).click();
            await expect(page.getByRole('dialog')).toContainText(
                'Verifikasi prestasi',
            );
            await page.keyboard.press('Escape');

            await page.getByRole('button', { name: 'Revisi' }).click();
            await expect(page.getByRole('dialog')).toContainText(
                'Minta revisi prestasi',
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
    fixture: PrestasiSantriFixture,
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
            `Login fixture PrestasiSantri ditolak: status ${loginResponse.status()}, location ${loginResponse.headers().location ?? '-'}`,
        );
    }
}

async function selectOption(
    dialog: Locator,
    page: Page,
    label: string,
    optionName: string,
): Promise<void> {
    await dialog.getByRole('combobox', { name: label }).click();
    await page.getByRole('option', { name: new RegExp(optionName) }).click();
}

function achievementRecord(page: Page, text: string): Locator {
    return page
        .locator('tr:visible, article:visible')
        .filter({ hasText: text })
        .first();
}
