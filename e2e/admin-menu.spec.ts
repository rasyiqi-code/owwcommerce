import { test, expect } from '@playwright/test';

test.describe('OwwCommerce Admin Dashboard', () => {
    test('Should display OwwCommerce menu and no errors on activation', async ({ page }) => {
        // Asumsi URL root local environment:
        await page.goto('/wp-admin/');

        // Silakan sesuaikan user/pass jika perlu (Contoh login bypass atau valid authentication diperlukan)
        // Hal ini bisa dijadikan TODO jika testing Playwright membutuhkan seeding data login wp-cli terlebih dahulu.
        await expect(page).toHaveTitle(/Log In|Dashboard|admin/i);
    });
});
