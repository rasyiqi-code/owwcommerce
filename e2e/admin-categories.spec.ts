import { test, expect } from '@playwright/test';

test.describe('OwwCommerce Admin Categories', () => {
    test.beforeEach(async ({ page }) => {
        // Go to WP Login
        await page.goto('/wp-login.php');

        // Cek apakah form login muncul (belum login)
        const usernameSelector = '#user_login';
        if (await page.locator(usernameSelector).isVisible()) {
            await page.fill(usernameSelector, 'rasyiqi');
            // Catatan: Gunakan password lokal yang valid. Fallback ke 'admin' jika 'rasyiqi' adalah usernatenya
            await page.fill('#user_pass', 'qwerty'); // Sesuaikan dengan pass lokal user 'rasyiqi' atau 'admin'

            await page.click('#wp-submit');
            await page.waitForLoadState('networkidle');
        }

        // Go to Categories Page
        await page.goto('/wp-admin/admin.php?page=owwc-categories');
    });

    test('Should display categories page correctly', async ({ page }) => {
        await expect(page.locator('h1')).toContainText('Kategori Produk');
        await expect(page.locator('#owwc-add-category-form')).toBeVisible();
        await expect(page.locator('.owwc-admin-table')).toBeVisible();
    });

    test('Should be able to add a new category', async ({ page }) => {
        const uniqueCat = `Kategori E2E ${Date.now()}`;

        // Isi form
        await page.fill('#cat-name', uniqueCat);
        await page.fill('#cat-description', 'Deskripsi dari pengujian otomatis E2E Playwright');

        // Submit form
        await page.click('button[type="submit"]');

        // Tunggu pesan sukses
        await expect(page.locator('#cat-form-message')).toBeVisible({ timeout: 5000 });

        // Pastikan tabel memuat nama kategori baru
        await expect(page.locator('#owwc-categories-body')).toContainText(uniqueCat);

        // Hapus kategori yang baru dibuat agar database tetap bersih
        // Kita butuh element tombol delete di row yang mengandung teks uniqueCat
        const row = page.locator('#owwc-categories-body tr', { hasText: uniqueCat }).first();

        // Karena ada dialog confirm() native JS, kita harus accept dialog tersebut
        page.on('dialog', dialog => dialog.accept());

        await row.locator('.owwc-btn-delete').click();

        // Pastikan row tersebut hilang
        await expect(row).not.toBeVisible({ timeout: 5000 });
    });
});
