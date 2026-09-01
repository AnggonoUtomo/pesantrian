import { expect, test } from '@playwright/test';
import type { Locator, Page } from '@playwright/test';
import { expectNoHighImpactViolations } from './support/accessibility';
import {
    cleanupPenerimaanSantriFixture,
    createPenerimaanSantriFixture,
} from './support/penerimaan-santri-fixture';
import type { PenerimaanSantriFixture } from './support/penerimaan-santri-fixture';

test.describe('Penerimaan Santri browser QA', () => {
    let fixture: PenerimaanSantriFixture;

    test.beforeEach(({ browserName }, testInfo) => {
        void browserName;
        fixture = createPenerimaanSantriFixture(testInfo);
    });

    test.afterEach(() => {
        cleanupPenerimaanSantriFixture(fixture);
    });

    test('operator mengelola pendaftaran dari input sampai diterima', async ({
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

        await loginAsFixtureUser(page, fixture);

        await test.step('membuka halaman PPDB dan membuat pendaftaran', async () => {
            await page.goto('/pesantrian/admissions');
            await expect(
                page.getByRole('heading', {
                    name: 'PPDB / Penerimaan Santri',
                }),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);

            await page
                .getByRole('button', { name: 'Tambah pendaftaran' })
                .click();

            const dialog = page.getByRole('dialog');
            await expect(
                dialog.getByRole('heading', { name: 'Tambah pendaftaran' }),
            ).toBeVisible();

            await dialog.getByLabel('Periode PPDB').fill(fixture.period);
            await selectOption(dialog, page, 'Status awal', 'Diajukan');
            await dialog
                .getByLabel('Nama calon santri')
                .fill(fixture.candidateName);
            await selectOption(dialog, page, 'Jenis kelamin', 'Laki-laki');
            await dialog.getByLabel('Tempat lahir').fill('Bandung');
            await dialog.getByLabel('Tanggal lahir').fill('2013-05-10');
            await dialog.getByLabel('Sekolah asal').fill('SD E2E Awal');
            await selectOption(dialog, page, 'Unit tujuan', fixture.unitName);
            await dialog.getByLabel('Nama wali').fill(fixture.guardianName);
            await dialog.getByLabel('Nomor HP wali').fill('081234567890');
            await selectOption(dialog, page, 'Hubungan wali', 'Ayah');
            await dialog
                .getByLabel('Catatan internal')
                .fill('Dibuat otomatis oleh browser QA PPDB.');

            await dialog
                .getByRole('button', { name: 'Tambah pendaftaran' })
                .click();
            await expect(
                page.getByText('Pendaftaran santri berhasil dibuat.'),
            ).toBeVisible();
            await expect(
                admissionRecord(page, fixture.candidateName),
            ).toBeVisible();
        });

        await test.step('memfilter data dan memperbarui pendaftaran', async () => {
            await page
                .getByRole('searchbox', { name: 'Cari calon santri' })
                .fill(fixture.candidateName);
            await page.getByRole('button', { name: 'Terapkan' }).click();

            const record = admissionRecord(page, fixture.candidateName);
            await expect(record).toBeVisible();
            await record
                .getByRole('button', { name: 'Edit pendaftaran' })
                .click();

            const dialog = page.getByRole('dialog');
            await expect(
                dialog.getByRole('heading', { name: 'Edit pendaftaran' }),
            ).toBeVisible();
            await dialog
                .getByLabel('Sekolah asal')
                .fill(fixture.previousSchoolAfterEdit);
            await dialog
                .getByRole('button', { name: 'Simpan perubahan' })
                .click();
            await expect(
                page.getByText('Pendaftaran santri berhasil diperbarui.'),
            ).toBeVisible();
            await page.reload();
            await expect(
                admissionRecord(page, fixture.candidateName),
            ).toBeVisible();
        });

        await test.step('membuka detail dan memverifikasi isi penting', async () => {
            const record = admissionRecord(page, fixture.candidateName);
            await record.getByRole('button', { name: 'Lihat detail' }).click();

            const detailDialog = page.getByRole('dialog');
            await expect(
                detailDialog.getByRole('heading', {
                    name: 'Detail pendaftaran',
                }),
            ).toBeVisible();
            await expect(detailDialog.getByText(fixture.period)).toBeVisible();
            await expect(
                detailDialog.getByText(fixture.previousSchoolAfterEdit),
            ).toBeVisible();
            await expect(detailDialog.getByText(fixture.guardianName)).toBeVisible();
            await expectNoHighImpactViolations(page);

            await page.keyboard.press('Escape');
            await expect(detailDialog).toBeHidden();
        });

        await test.step('menjalankan lifecycle verifikasi sampai diterima', async () => {
            let record = admissionRecord(page, fixture.candidateName);
            await record
                .getByRole('button', { name: 'Verifikasi pendaftaran' })
                .click();
            await page.getByRole('button', { name: 'Ya, verifikasi' }).click();
            await expect(
                page.getByText('Pendaftaran santri berhasil diverifikasi.'),
            ).toBeVisible();

            record = admissionRecord(page, fixture.candidateName);
            await expect(record.getByText('Terverifikasi')).toBeVisible();
            await record.getByRole('button', { name: 'Terima santri' }).click();
            await page.getByRole('button', { name: 'Ya, terima' }).click();
            await expect(
                page.getByText('Pendaftaran santri berhasil diterima.'),
            ).toBeVisible();

            record = admissionRecord(page, fixture.candidateName);
            await expect(record.getByText('Diterima')).toBeVisible();
        });

        await test.step('memastikan pagination dasar tersedia', async () => {
            await expect(
                page.getByRole('button', { name: 'Sebelumnya' }),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Berikutnya' }),
            ).toBeVisible();
        });

        expect({ browserErrors, forbiddenResponses }).toEqual({
            browserErrors: [],
            forbiddenResponses: [],
        });
    });
});

async function loginAsFixtureUser(
    page: Page,
    fixture: PenerimaanSantriFixture,
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
            `Login fixture PPDB ditolak: status ${loginResponse.status()}, location ${loginResponse.headers().location ?? '-'}`,
        );
    }
}

function admissionRecord(
    page: Page,
    candidateName: string,
): Locator {
    return page
        .locator('tr:visible, article:visible')
        .filter({ hasText: candidateName })
        .first();
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
