import { expect } from '@playwright/test';
import type { Page } from '@playwright/test';

export async function loginAs(
    page: Page,
    email = process.env.E2E_EMAIL,
): Promise<void> {
    const password = process.env.E2E_PASSWORD;

    if (!email || !password) {
        throw new Error(
            'E2E_EMAIL dan E2E_PASSWORD wajib tersedia saat login test.',
        );
    }

    await page.goto('/login');
    await page.getByLabel('Email address').fill(email);
    await page
        .getByRole('textbox', { name: 'Password', exact: true })
        .fill(password);
    await page.getByRole('button', { name: /log in/i }).click();
    await expect(page).not.toHaveURL(/\/login$/);
}

export async function loginAsSuperSystem(page: Page): Promise<void> {
    await loginAs(page);
}
