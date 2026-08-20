import { expect, test } from '@playwright/test';
import { expectNoHighImpactViolations } from './support/accessibility';

test('halaman login dapat dibuka dan lolos accessibility gate', async ({
    page,
}) => {
    await page.goto('/login');

    await expect(page.getByRole('heading', { name: /log in/i })).toBeVisible();
    await expect(page.getByLabel('Email address')).toBeVisible();
    await expect(
        page.getByRole('textbox', { name: 'Password', exact: true }),
    ).toBeVisible();
    await expectNoHighImpactViolations(page);
});
