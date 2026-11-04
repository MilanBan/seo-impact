import { expect, test } from '@playwright/test';

test('invited client can access the isolated portal', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email address').fill('client@seo-impact.test');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/en\/portal$/);
    await expect(page.getByText('Demo Client')).toBeVisible();
    await expect(page.getByText('Organic Growth Programme')).toBeVisible();
    await expect(page.getByText('Internal implementation review')).toHaveCount(
        0,
    );
});
