import { expect, test } from '@playwright/test';
import { expectNoHighImpactViolations } from './support/accessibility';
import { loginAs, loginAsSuperSystem } from './support/authentication';

test('critical flow empat module aman, accessible, dan responsif', async ({
    browser,
    page,
}, testInfo) => {
    test.setTimeout(180_000);

    const browserErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            browserErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => browserErrors.push(error.message));

    const suffix = testInfo.project.name.startsWith('mobile')
        ? 'Mobile'
        : 'Desktop';
    const retrySuffix = testInfo.retry === 0 ? '' : `Retry${testInfo.retry}`;
    const roleName = `E2EReviewer${suffix}${retrySuffix}`;
    const userEmail = `e2e-${suffix.toLowerCase()}-${testInfo.retry}@example.test`;
    const rateLimit = suffix === 'Mobile' ? '62' : '61';

    await loginAsSuperSystem(page);

    await test.step('AccessControl membuat role dan menjaga fokus keyboard', async () => {
        await page.goto('/system/access-control');
        await expect(
            page.getByRole('heading', { name: 'Access Control' }),
        ).toBeVisible();
        await expectNoHighImpactViolations(page);

        await page.keyboard.press('r');
        await expect(
            page.getByRole('searchbox', { name: 'Cari role' }),
        ).toBeFocused();
        await page.keyboard.press('Escape');

        const addRole = page.getByRole('button', { name: 'Tambah role' });
        await addRole.click();
        await page.getByLabel('Nama role').fill(roleName);
        await page.getByRole('button', { name: 'Simpan role' }).click();
        await expect(
            page.getByText('Role berhasil ditambahkan.'),
        ).toBeVisible();

        await page.getByRole('combobox', { name: 'Role aktif' }).click();
        await page.getByRole('searchbox', { name: 'Cari role' }).fill(roleName);
        await expect(
            page.getByRole('option', { name: roleName }),
        ).toBeVisible();
        await page.keyboard.press('Escape');
        await expectNoHighImpactViolations(page);
    });

    await test.step('UserManagement menolak duplicate lalu mengirim invitation', async () => {
        await page.goto('/system/users');
        await expect(
            page.getByRole('heading', { name: 'User Management' }),
        ).toBeVisible();
        await expectNoHighImpactViolations(page);

        await page.getByRole('button', { name: 'Tambah user' }).first().click();
        await page.getByLabel('Nama', { exact: true }).fill(`E2E ${suffix}`);
        await page
            .getByLabel('Email', { exact: true })
            .fill('super-system@example.test');
        await page.getByRole('button', { name: 'Kirim invitation' }).click();
        await expect(page.getByRole('alert')).toBeVisible();

        await page.getByLabel('Email', { exact: true }).fill(userEmail);
        await page.route('**/system/users/invitations', async (route) => {
            await new Promise((resolve) => setTimeout(resolve, 300));
            await route.continue();
        });
        await page.getByRole('button', { name: 'Kirim invitation' }).click();
        await expect(
            page.getByRole('button', { name: 'Menyimpan...' }),
        ).toBeDisabled();
        await expect(
            page.getByText('Undangan user berhasil dikirim.'),
        ).toBeVisible();
        await expect(page.getByText(userEmail)).toBeVisible();
        await expect(
            page
                .locator('[data-sonner-toast]')
                .filter({ hasText: 'Undangan user berhasil dikirim.' }),
        ).toHaveCSS('opacity', '1');
        await expectNoHighImpactViolations(page);
    });

    await test.step('SystemSetting memvalidasi input dan menyimpan perubahan', async () => {
        await page.goto('/system/system-settings');
        await expect(
            page.getByRole('heading', {
                name: 'SystemSetting',
                exact: true,
            }),
        ).toBeVisible();

        await page.keyboard.press('/');
        await expect(
            page.getByRole('textbox', { name: 'Cari SystemSetting' }),
        ).toBeFocused();
        await page
            .getByRole('textbox', { name: 'Cari SystemSetting' })
            .fill('tidak-ada-setting');
        await expect(
            page.getByRole('heading', { name: 'Setting tidak ditemukan' }),
        ).toBeVisible();
        await page
            .getByRole('textbox', { name: 'Cari SystemSetting' })
            .fill('');

        await page.getByRole('button', { name: 'Ubah kategori' }).click();
        const rateLimitInput = page.getByLabel('Batas request API per menit');
        await rateLimitInput.fill('0');
        await page
            .getByLabel('Alasan perubahan kategori')
            .fill('Verifikasi browser untuk validasi input runtime.');
        await page.getByRole('button', { name: /Simpan 1 perubahan/ }).click();
        await expect(
            rateLimitInput.evaluate(
                (input: HTMLInputElement) => input.validity.valid,
            ),
        ).resolves.toBe(false);

        await rateLimitInput.fill(rateLimit);
        await page.route(
            '**/system/system-settings/categories/api',
            async (route) => {
                await new Promise((resolve) => setTimeout(resolve, 300));
                await route.continue();
            },
        );
        await page.getByRole('button', { name: /Simpan 1 perubahan/ }).click();
        await expect(
            page.getByRole('button', { name: 'Menyimpan...' }),
        ).toBeDisabled();
        await expect(
            page.getByText('1 SystemSetting berhasil diperbarui.'),
        ).toBeVisible();
        await expect(
            page.getByText(`Nilai saat ini: ${rateLimit}`),
        ).toBeVisible();

        await page.emulateMedia({ colorScheme: 'dark' });
        await expectNoHighImpactViolations(page);
        await page.emulateMedia({ colorScheme: 'light' });
    });

    await test.step('AuditLog membaca hasil mutation, detail, dan empty state', async () => {
        await page.goto('/system/audit-logs');
        await expect(
            page.getByRole('heading', { name: 'Audit Log' }),
        ).toBeVisible();
        await expectNoHighImpactViolations(page);

        await page.keyboard.press('/');
        const search = page.getByPlaceholder(
            'Cari action, subject, correlation...',
        );
        await expect(search).toBeFocused();

        await page
            .getByRole('button', { name: /^Lihat detail/ })
            .first()
            .click();
        await expect(
            page
                .getByRole('dialog')
                .getByRole('heading', { name: 'Detail audit log' }),
        ).toBeVisible();
        await expectNoHighImpactViolations(page);
        await page.keyboard.press('Escape');
        await expect(page.getByRole('dialog')).toBeHidden();

        await search.fill('e2e-empty-state-yang-tidak-ada');
        await page.getByRole('button', { name: 'Terapkan filter' }).click();
        await expect(
            page.getByRole('button', { name: 'Mencari...' }),
        ).toBeDisabled();
        await page.waitForURL(
            (url) =>
                url.searchParams.get('search') ===
                'e2e-empty-state-yang-tidak-ada',
            { timeout: 60_000 },
        );
        await expect(
            page.getByRole('heading', { name: 'Belum ada audit log' }),
        ).toBeVisible();
        await expectNoHighImpactViolations(page);
    });

    await test.step('SecurityAdmin ditolak oleh security boundary SystemSetting', async () => {
        const deniedContext = await browser.newContext();
        const deniedPage = await deniedContext.newPage();

        await loginAs(deniedPage, 'security-admin@example.test');
        const response = await deniedPage.goto('/system/system-settings');

        expect(response?.status()).toBe(403);
        await deniedContext.close();
    });

    expect(browserErrors).toEqual([]);
});
