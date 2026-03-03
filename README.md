<align align="center">
  <img src="assets/images/logo.png" width="160" alt="OwwCommerce Logo">
  <h1>OwwCommerce</h1>
  <p><b>The High-Performance, Zero-Bloatware E-Commerce Engine for WordPress.</b></p>
  <p>
    <img src="https://img.shields.io/badge/PHP-8.1+-777bb4.svg" alt="PHP Version">
    <img src="https://img.shields.io/badge/WordPress-6.0+-21759b.svg" alt="WordPress Version">
    <img src="https://img.shields.io/badge/License-GPL-blue.svg" alt="License">
  </p>
</align>

---

## ⚡ Why OwwCommerce?

OwwCommerce is not just another e-commerce plugin. It's a solution for those who demand speed without the overhead. By leveraging an architecture that avoids the standard `wp_posts` dependency for product and order data, OwwCommerce offers maximum performance even with thousands of SKUs.

### ✨ Key Features
- 🚀 **Lightning Fast**: Uses custom database tables for significantly faster data retrieval.
- 📦 **Zero Bloatware**: Scripts are only loaded when needed. No jQuery, just modern Vanilla JS.
- 🛍️ **Adaptive Checkout**: Built-in support for **External Marketplaces** (Shopee/Tokopedia) and **WhatsApp Checkout**.
- 📱 **Mobile First**: Responsive design with a *sticky action bar* for higher conversions on mobile devices.
- 🧩 **Modular Architecture**: Easily extensible with a simple Container & Dependency Injection pattern.

---

## 🚀 Quick Installation

1. Ensure you are running PHP 8.1+ and WordPress 6.0+.
2. Clone this repository into your plugins directory:
   ```bash
   git clone https://github.com/rasyiqi/owwcommerce.git
   ```
3. Activate the plugin through the WordPress dashboard.
4. Use Shortcodes to build your shop pages.

---

## 🛠️ Shortcode Guide

| Feature | Shortcode | Description |
| :--- | :--- | :--- |
| **Shop Catalog** | `[owwcommerce_shop]` | Displays the product list with filters. |
| **Cart Page** | `[owwcommerce_cart]` | Modern shopping cart page. |
| **Checkout** | `[owwcommerce_checkout]` | Fast payment & shipping form. |
| **My Account** | `[owwcommerce_my_account]` | Customer dashboard & order history. |
| **Cart Icon** | `[owwcommerce_cart_icon]` | Mini icon with item count badge. |

---

## 🤝 Contribution

We are very open to contributors! Whether it's bug fixes, new features, or design suggestions.

- Read [ARCHITECTURE.md](ARCHITECTURE.md) to understand the system structure.
- Check [CONTRIBUTING.md](CONTRIBUTING.md) to start developing.

---

## 📜 License

This project is licensed under the **GPL v2 or later**.
