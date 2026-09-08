import { expect, test } from '@playwright/test';
import type { Locator, Page } from '@playwright/test';
import { expectNoHighImpactViolations } from './support/accessibility';
import {
    cleanupPerizinanSantriFixture,
    createPerizinanSantriFixture,
} from './support/perizinan-santri-fixture';
import type { PerizinanSantriFixture } from './support/perizinan-santri-fixture';

test.describe('Perizinan Santri browser QA', () => {
    let fixture: PerizinanSantriFixture | undefined;

    test.beforeEach(({ browserName }, testInfo) => {
        void browserName;
        fixture = createPerizinanSantriFixture(testInfo);
    });

    test.afterEach(() => {
        if (fixture) {
            cleanupPerizinanSantriFixture(fixture);
        }
    });

    test('operator mengelola izin dari draft sampai kembali serta reject dan void', async ({
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
            throw new Error('Fixture PerizinanSantri belum siap.');
        }

        await loginAsFixtureUser(page, currentFixture);

        await test.step('membuka halaman perizinan dan membuat draft izin', async () => {
            await page.goto('/pesantrian/student-permits');
            await expect(
                page.getByRole('heading', {
                    name: 'Perizinan Santri',
                    exact: true,
                }),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);

            await createDraftPermit(page, currentFixture, {
                startsAt: '2027-09-12T08:00',
                endsAt: '2027-09-12T17:00',
                destination: 'Rumah wali browser QA',
                reason: 'Pulang untuk keperluan keluarga browser QA.',
            });
        });

        await test.step('mengedit draft lalu submit untuk review', async () => {
            await page.getByRole('button', { name: 'Edit izin' }).click();

            const dialog = page.getByRole('dialog');
            await expect(
                dialog.getByRole('heading', { name: 'Edit izin santri' }),
            ).toBeVisible();
            await dialog.getByLabel('Tujuan').fill('Gedung kegiatan browser QA');
            await dialog
                .getByLabel('Alasan koreksi')
                .fill('Koreksi tujuan izin browser QA.');
            await dialog
                .getByRole('button', { name: 'Simpan perubahan' })
                .click();
            await expect(
                page.getByText('Permohonan izin santri berhasil diperbarui.'),
            ).toBeVisible();
            await expect(page.getByText('Gedung kegiatan browser QA')).toBeVisible();

            await submitCurrentPermit(page);
            await expect(page.getByText('Menunggu review')).toBeVisible();
        });

        await test.step('approve, check-out, dan return izin dari detail', async () => {
            await page.getByRole('button', { name: 'Approve' }).click();
            const approveDialog = page.getByRole('dialog');
            await approveDialog
                .getByLabel('Catatan review')
                .fill('Izin disetujui dari browser QA.');
            await approveDialog
                .getByRole('button', { name: 'Setujui izin' })
                .click();
            await expect(
                page.getByText('Permohonan izin santri berhasil disetujui.'),
            ).toBeVisible();
            await expect(
                page.getByText('Disetujui', { exact: true }),
            ).toBeVisible();

            await page.getByRole('button', { name: 'Check-out' }).click();
            await page
                .getByRole('dialog')
                .getByRole('button', { name: 'Catat check-out' })
                .click();
            await expect(
                page.getByText('Izin santri berhasil di-check-out.'),
            ).toBeVisible();
            await expect(
                page.getByText('Sedang izin', { exact: true }),
            ).toBeVisible();

            await page.getByRole('button', { name: 'Return/check-in' }).click();
            const returnDialog = page.getByRole('dialog');
            await returnDialog.getByLabel('Waktu kembali').fill('2027-09-12T18:30');
            await returnDialog
                .getByLabel('Catatan kembali')
                .fill('Santri kembali terlambat karena macet browser QA.');
            await returnDialog
                .getByRole('button', { name: 'Catat kembali' })
                .click();
            await expect(
                page.getByText('Kepulangan santri berhasil dicatat.'),
            ).toBeVisible();
            await expect(
                page.getByText('Sudah kembali', { exact: true }),
            ).toBeVisible();
            await expect(
                page.getByText('Santri kembali terlambat karena macet browser QA.'),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);
        });

        await test.step('reject dan void dari izin yang dibuat lewat UI', async () => {
            await page.goto('/pesantrian/student-permits');
            await createDraftPermit(page, currentFixture, {
                startsAt: '2027-09-13T08:00',
                endsAt: '2027-09-13T17:00',
                destination: 'Kantor wali browser QA',
                reason: 'Fixture reject browser QA.',
            });
            await submitCurrentPermit(page);
            await page.getByRole('button', { name: 'Reject' }).click();

            const rejectDialog = page.getByRole('dialog');
            await rejectDialog
                .getByLabel('Alasan penolakan')
                .fill('Data izin fixture ditolak browser QA.');
            await rejectDialog.getByRole('button', { name: 'Tolak izin' }).click();
            await expect(
                page.getByText('Permohonan izin santri berhasil ditolak.'),
            ).toBeVisible();

            await page.goto('/pesantrian/student-permits');
            await createDraftPermit(page, currentFixture, {
                startsAt: '2027-09-14T08:00',
                endsAt: '2027-09-14T17:00',
                destination: 'Klinik browser QA',
                reason: 'Fixture void browser QA.',
            });
            await submitCurrentPermit(page);
            await page.getByRole('button', { name: 'Void' }).click();

            const voidDialog = page.getByRole('dialog');
            await voidDialog
                .getByLabel('Alasan pembatalan')
                .fill('Permohonan fixture dibatalkan browser QA.');
            await voidDialog
                .getByRole('button', { name: 'Batalkan izin' })
                .click();
            await expect(
                page.getByText('Izin santri berhasil dibatalkan.'),
            ).toBeVisible();
            await expectNoHighImpactViolations(page);
        });

        expect({ browserErrors, forbiddenResponses }).toEqual({
            browserErrors: [],
            forbiddenResponses: [],
        });
    });
});

async function createDraftPermit(
    page: Page,
    fixture: PerizinanSantriFixture,
    input: {
        startsAt: string;
        endsAt: string;
        destination: string;
        reason: string;
    },
): Promise<void> {
    await page.getByRole('button', { name: 'Buat izin' }).click();

    const dialog = page.getByRole('dialog');
    await expect(
        dialog.getByRole('heading', { name: 'Buat izin santri' }),
    ).toBeVisible();

    await selectOption(dialog, page, 'Santri', fixture.studentName);
    await selectOption(dialog, page, 'Jenis izin', 'Pulang ke rumah');
    await dialog.getByLabel('Mulai izin').fill(input.startsAt);
    await dialog.getByLabel('Batas kembali').fill(input.endsAt);
    await dialog.getByLabel('Tujuan').fill(input.destination);
    await dialog.getByLabel('Alasan izin').fill(input.reason);
    await dialog.getByRole('button', { name: 'Buat draft izin' }).click();

    await expect(
        page.getByText('Permohonan izin santri berhasil dibuat.'),
    ).toBeVisible();
    await expect(page).toHaveURL(/\/pesantrian\/student-permits\//);
    await expect(page.getByText(fixture.studentName).first()).toBeVisible();
    await expect(page.getByText(input.destination)).toBeVisible();
}

async function submitCurrentPermit(page: Page): Promise<void> {
    await page.getByRole('button', { name: 'Submit' }).click();
    await page
        .getByRole('dialog')
        .getByRole('button', { name: 'Submit izin' })
        .click();
    await expect(
        page.getByText('Permohonan izin santri berhasil disubmit.'),
    ).toBeVisible();
}

async function loginAsFixtureUser(
    page: Page,
    fixture: PerizinanSantriFixture,
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
            `Login fixture PerizinanSantri ditolak: status ${loginResponse.status()}, location ${loginResponse.headers().location ?? '-'}`,
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
