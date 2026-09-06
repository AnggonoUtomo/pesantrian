import { expect, test } from '@playwright/test';
import type { Locator, Page } from '@playwright/test';
import { expectNoHighImpactViolations } from './support/accessibility';
import {
    cleanupPresensiSantriFixture,
    createPresensiSantriFixture,
} from './support/presensi-santri-fixture';
import type { PresensiSantriFixture } from './support/presensi-santri-fixture';

test.describe('Presensi Santri browser QA', () => {
    let fixture: PresensiSantriFixture | undefined;

    test.beforeEach(({ browserName }, testInfo) => {
        void browserName;
        fixture = createPresensiSantriFixture(testInfo);
    });

    test.afterEach(() => {
        if (fixture) {
            cleanupPresensiSantriFixture(fixture);
        }
    });

    test('operator mengelola sesi presensi dari draft sampai void', async ({
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
            throw new Error('Fixture PresensiSantri belum siap.');
        }

        await loginAsFixtureUser(page, currentFixture);

        await test.step('membuka halaman presensi dan membuat sesi draft', async () => {
            await page.goto('/pesantrian/student-attendances');
            await expect(
                page.getByRole('heading', {
                    name: 'Presensi Santri',
                    exact: true,
                }),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);

            await page
                .getByRole('button', { name: 'Tambah sesi presensi' })
                .click();

            const dialog = page.getByRole('dialog');
            await expect(
                dialog.getByRole('heading', {
                    name: 'Tambah sesi presensi',
                }),
            ).toBeVisible();
            await dialog.getByLabel('Tanggal presensi').fill('2026-09-06');
            await selectOption(dialog, page, 'Konteks presensi', 'Kegiatan umum');
            await dialog.getByLabel('Nama konteks').fill('Kegiatan E2E');
            await dialog.getByLabel('Kode sesi').fill(currentFixture.sessionCode);
            await dialog.getByLabel('Nama sesi').fill(currentFixture.sessionName);

            await dialog.getByRole('button', { name: 'Tambah entry' }).click();
            await selectOption(dialog, page, 'Santri', currentFixture.studentOneName);
            await selectOption(dialog, page, 'Status', 'Hadir');

            await dialog.getByRole('button', { name: 'Tambah entry' }).click();
            const rows = dialog.locator('.grid.rounded-lg.border');
            const secondRow = rows.nth(1);
            await selectOption(secondRow, page, 'Santri', currentFixture.studentTwoName);
            await selectOption(secondRow, page, 'Status', 'Terlambat');
            await secondRow.getByLabel('Menit telat').fill('12');
            await secondRow.getByLabel('Catatan').fill('Terlambat apel.');

            await dialog.getByRole('button', { name: 'Buat sesi' }).click();
            await expect(
                page.getByText('Sesi presensi santri berhasil dibuat.'),
            ).toBeVisible();
            await expect(page).toHaveURL(/\/pesantrian\/student-attendances\//);
            await expect(
                page.getByRole('heading', {
                    name: new RegExp(`^${escapeRegExp(currentFixture.sessionCode)} · `),
                }),
            ).toBeVisible();
            await expect(
                studentEntry(page, currentFixture.studentTwoName),
            ).toBeVisible();
        });

        await test.step('mengedit sesi dan entry dari halaman detail', async () => {
            await page
                .getByRole('button', { name: 'Edit sesi presensi' })
                .click();
            const dialog = page.getByRole('dialog');
            await expect(
                dialog.getByRole('heading', { name: 'Edit sesi presensi' }),
            ).toBeVisible();
            await dialog.getByLabel('Nama sesi').fill(currentFixture.sessionNameAfterEdit);
            await dialog.getByRole('button', { name: 'Simpan sesi' }).click();
            await expect(
                page.getByText('Sesi presensi santri berhasil diperbarui.'),
            ).toBeVisible();
            await expect(page.getByText(currentFixture.sessionNameAfterEdit)).toBeVisible();

            const editor = page
                .locator('section')
                .filter({ hasText: 'Editor entry presensi' });
            const firstEntry = editor
                .locator('.grid.rounded-xl.border')
                .filter({ hasText: currentFixture.studentOneName })
                .first();
            await selectOption(firstEntry, page, 'Status', 'Sakit');
            await firstEntry.getByLabel('Catatan').fill('Sakit dari klinik.');
            await editor
                .getByRole('button', { name: 'Simpan entry presensi' })
                .click();
            await expect(
                page.getByText('Entry presensi santri berhasil diperbarui.'),
            ).toBeVisible();
            await expect(
                studentEntry(page, currentFixture.studentOneName).filter({
                    hasText: 'Sakit dari klinik.',
                }),
            ).toBeVisible();
        });

        await test.step('submit, revisi, dan void sesi', async () => {
            await page.getByRole('button', { name: 'Submit presensi' }).click();
            await page
                .getByRole('dialog')
                .getByRole('button', { name: 'Submit presensi' })
                .click();
            await expect(
                page.getByText('Sesi presensi santri berhasil disubmit.'),
            ).toBeVisible();

            await page.getByRole('button', { name: 'Buka revisi' }).click();
            await page
                .getByRole('dialog')
                .getByLabel('Alasan')
                .fill('Koreksi hasil browser QA.');
            await page
                .getByRole('dialog')
                .getByRole('button', { name: 'Buka revisi' })
                .click();
            await expect(
                page.getByText(
                    'Sesi presensi santri berhasil dibuka untuk revisi.',
                ),
            ).toBeVisible();

            await page
                .getByRole('button', { name: 'Batalkan sesi presensi' })
                .click();
            await page
                .getByRole('dialog')
                .getByLabel('Alasan')
                .fill('Sesi diganti data final.');
            await page
                .getByRole('dialog')
                .getByRole('button', { name: 'Batalkan sesi presensi' })
                .click();
            await expect(
                page.getByText('Sesi presensi santri berhasil dibatalkan.'),
            ).toBeVisible();
            await expect(page).toHaveURL(/\/pesantrian\/student-attendances$/);
        });

        await test.step('memastikan list, filter, dan pagination tetap tersedia', async () => {
            await page
                .getByRole('searchbox', { name: 'Cari sesi presensi' })
                .fill(currentFixture.sessionCode);
            await page.getByRole('button', { name: 'Terapkan' }).click();
            await expect(
                attendanceRecord(page, currentFixture.sessionCode),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Sebelumnya' }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Berikutnya' }),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);
        });

        expect({ browserErrors, forbiddenResponses }).toEqual({
            browserErrors: [],
            forbiddenResponses: [],
        });
    });
});

async function loginAsFixtureUser(
    page: Page,
    fixture: PresensiSantriFixture | undefined,
): Promise<void> {
    if (!fixture) {
        throw new Error('Fixture PresensiSantri belum siap untuk login.');
    }

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
            `Login fixture PresensiSantri ditolak: status ${loginResponse.status()}, location ${loginResponse.headers().location ?? '-'}`,
        );
    }
}

function attendanceRecord(page: Page, sessionCode: string): Locator {
    return page
        .locator('tr:visible, article:visible')
        .filter({ hasText: sessionCode })
        .first();
}

function studentEntry(page: Page, studentName: string): Locator {
    return page
        .locator('tr:visible, article:visible')
        .filter({ hasText: studentName })
        .first();
}

function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

async function selectOption(
    container: Locator,
    page: Page,
    label: string,
    optionName: string,
): Promise<void> {
    await container.getByRole('combobox', { name: label }).click();
    const option = page.getByRole('option', { name: new RegExp(optionName) });

    try {
        await option.click({ timeout: 2_000 });
    } catch {
        await page.keyboard.type(optionName);
        await page.keyboard.press('Enter');
    }
}
