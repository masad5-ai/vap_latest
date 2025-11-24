# VaporPulse prototype

A neon-inspired PHP/MySQL-ready ecommerce prototype for a vape storefront with customer and admin flows.

## Quick start
1. Serve `public/` with PHP (e.g., `php -S 0.0.0.0:8000 -t public`).
2. Log into the admin console via `/admin/index.php` using **admin@vaporpulse.test / admin123**.
3. Update `src/config.php` with your MySQL credentials and swap JSON persistence for real tables when ready.

### Bringing in the provided UI templates
The requested Vapor-inspired HTML templates are hosted at `https://github.com/masad5-ai/theme.git`. Network restrictions in this environment block direct access (403 on clone). To use the supplied designs:
- Clone or download the theme repository on a machine with GitHub access.
- Copy the **admin** HTML files into `public/admin-theme/` and the **storefront** HTML files into `public/theme/` within this project.
- Once copied, run `php -S localhost:8000 -t public` and wire the PHP routes to those templates.

See `docs/THEME_IMPORT.md` for a short walkthrough. If you can provide a ZIP of the `admin` and `front` folders, I can hook them into the PHP views directly.

## Pages

- **Storefront**: `index.php` (home), `shop.php`, `cart.php`, `checkout.php`, `login.php`, and `account.php` for customers.
- **Admin console**: `admin/index.php` dashboard plus dedicated screens for `products.php`, `orders.php`, `settings.php`, and `users.php`.

## Features
- Customer registration/login, catalog browsing with search, cart management, checkout, saved profile, and order timeline.
- Admin dashboard for product creation, editing, inventory status, order status with audit history, and configurable branding.
- Payment, WhatsApp, email gateways, and multi-tier shipping calculators configurable from the admin panel plus customer notification preferences.
- Theme tuned for vape retailers with gradients, badges, and elevated cards.

See `docs/ARCHITECTURE.md` for structure and next steps.

## Merge readiness
If GitHub reports that the branch has conflicts, pull the latest `main` branch locally and merge it into this branch. There are no conflict markers in the tracked files, and consistent line endings (enforced via `.gitattributes`) help avoid false positives.
