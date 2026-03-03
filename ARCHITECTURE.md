# OwwCommerce Project Architecture

This document explains the technical structure and philosophy behind the development of OwwCommerce.

## Folder Structure

```text
owwcommerce/
├── assets/             # Static files (CSS, JS, Images)
├── includes/           # Core PHP Logic (PSR-4 Autoloading)
│   ├── Api/            # REST API Controllers
│   ├── Core/           # Bootstrapping & Plugin Container
│   ├── Database/       # Installer & Migration Logic
│   ├── Models/         # Data representation for Products, Orders, etc.
│   ├── Repositories/   # Database interactions (Query Logic)
│   └── Shortcodes/     # WordPress Shortcode definitions
├── templates/          # HTML/PHP template files (View Layer)
├── tests/              # Unit & Integration Tests (PHPUnit)
├── e2e/                # End-to-End Tests (Playwright)
└── owwcommerce.php     # Main Plugin Entry Point
```

## Design Philosophy

### 1. Custom Database Tables
Unlike standard e-commerce plugins that store everything in the standard `wp_posts` and `wp_postmeta`, OwwCommerce utilizes custom SQL tables (`wp_oww_products`, `wp_oww_orders`, etc.).
- **Benefit**: Simpler queries, more efficient indexing, and keeping the database clean (*zero bloat*).

### 2. Dependency Injection & Container
We use a simple Container located at `includes/Core/Container.php` to manage class instances. This facilitates *mocking* during testing and keeps the code organized.

### 3. API-First Approach
The admin interface and frontend communicate via an internal REST API. This ensures easier integration with third-party applications or *headless WordPress* in the future.

### 4. Zero Dependencies
We are committed to not using third-party libraries on the frontend. All interactivity is built using native **Vanilla JavaScript** to ensure ultra-fast loading times.

## Data Flow (Product Listing)

1. The request enters via **Shortcode** or **REST API**.
2. The **Controller** requests data from the **Repository**.
3. The **Repository** executes the SQL query against the **Custom Tables**.
4. The data is wrapped into **Model** objects and formatted as an array/JSON.
5. The **Template** or JS renders the data to the user interface.
