import AxeBuilder from '@axe-core/playwright';
import { expect } from '@playwright/test';
import type { Page } from '@playwright/test';

export async function expectNoHighImpactViolations(page: Page): Promise<void> {
    await page.addStyleTag({
        content: `
            *, *::before, *::after {
                animation-delay: 0s !important;
                animation-duration: 0s !important;
                transition-delay: 0s !important;
                transition-duration: 0s !important;
            }
        `,
    });
    await page.evaluate(async () => {
        await new Promise<void>((resolve) => {
            requestAnimationFrame(() => requestAnimationFrame(() => resolve()));
        });
    });

    const results = await new AxeBuilder({ page }).analyze();
    const highImpact = results.violations.filter(
        (violation) =>
            violation.impact === 'critical' || violation.impact === 'serious',
    );

    expect(highImpact).toEqual([]);
}
