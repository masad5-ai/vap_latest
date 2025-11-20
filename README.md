# VaporPulse prototype

A neon-inspired PHP/MySQL-ready ecommerce prototype for a vape storefront with customer and admin flows.

## Quick start
1. Serve `public/` with PHP (e.g., `php -S 0.0.0.0:8000 -t public`).
2. Log into the admin console via `/admin.php` using **admin@vaporpulse.test / admin123**.
3. Update `src/config.php` with your MySQL credentials and swap JSON persistence for real tables when ready.

## Features
- Customer registration/login, catalog browsing, cart management, checkout, and order timeline.
- Admin dashboard for product creation, order status updates, and configurable branding.
- Payment, WhatsApp, and email gateway settings controllable from the admin panel.
- Theme tuned for vape retailers with gradients, badges, and elevated cards.

See `docs/ARCHITECTURE.md` for structure and next steps.
