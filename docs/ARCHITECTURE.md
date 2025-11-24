# VaporPulse prototype overview

This lightweight PHP prototype demonstrates the ecommerce flows requested:

- Customer features: registration/login, product catalog, cart, checkout with custom payment selector, order history, WhatsApp/email contact cues.
- Admin features: product creation, order list, status updates, access-controlled by the `admin` role.
- Persistence: JSON files under `data/` for quick demos; replace with MySQL using `src/config.php` as the connection source.
- Styling: vape-inspired neon gradients via `assets/css/style.css`.

## Key entry points
- `public/index.php` — storefront, cart, checkout, authentication, and customer dashboard.
- `public/admin.php` — admin console for catalogue and orders (login with `admin@vaporpulse.test` / `admin123`).

## Data files
- `data/products.json` — initial catalog items.
- `data/users.json` — seeded admin user; customers added on registration.
- `data/orders.json` — generated at checkout.

## Next steps for production
- Swap JSON storage for MySQL tables (users, products, orders, payments, messages).
- Wire real payment provider + WhatsApp/email gateways controlled from an admin settings table.
- Harden validation, CSRF protection, and input sanitization.
- Add routing, templating, and automated tests.
