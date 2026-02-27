import { test, expect } from '@playwright/test';

test.describe('OwwCommerce Customer Flow', () => {

    test('Add product to cart and complete checkout', async ({ page }) => {
        // 1. Pergi ke halaman Shop
        await page.goto('/shop');
        await expect(page).toHaveTitle(/Shop/i);

        // 2. Klik Tambah ke Keranjang pada produk pertama
        const addToCartBtn = page.locator('.owwc-add-to-cart').first();
        const productName = await addToCartBtn.getAttribute('data-title');
        await addToCartBtn.click();

        // 3. Verifikasi notifikasi toast muncul (opsional tergantung UI saat ini)
        // await expect(page.locator('#owwc-toast')).toBeVisible();

        // 4. Pergi ke halaman Cart
        await page.goto('/cart');
        await expect(page.locator('.owwc-cart-table')).toBeVisible();
        await expect(page.locator('.owwc-cart-table').locator(`text=${productName}`)).toBeVisible();

        // 5. Lanjut ke halaman Checkout
        await page.click('text="Lanjut ke Pembayaran"');
        await expect(page).toHaveURL(/.*checkout/);
        await expect(page.locator('.owwc-checkout-wrapper')).toBeVisible();

        // 6. Isi form Checkout (Alamat)
        await page.fill('#first_name', 'Budi');
        await page.fill('#last_name', 'Santoso');
        await page.fill('#email', 'budi.santoso@example.com');
        await page.fill('#phone', '081234567890');
        await page.fill('#address', 'Jl. Merdeka No. 123');
        await page.fill('#city', 'Jakarta Pusat');
        await page.fill('#province', 'DKI Jakarta');
        await page.fill('#zip', '10110');

        // 7. Pilih metode pengiriman dan pembayaran
        await page.check('input.shipping_method[value="flat_rate"]');
        await page.check('input.payment_method[value="bacs"]');

        // 8. Submit Pesanan
        await page.click('#owwc-place-order');

        // 9. Verifikasi halaman sukses or message sukses
        // Checkout JS kita mereplace HTML dengan "Terima kasih, pesanan Anda telah diterima."
        await expect(page.locator('.owwc-checkout-wrapper')).toContainText('Terima kasih');
    });

});
