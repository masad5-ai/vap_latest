# Theme import instructions

The requested UI templates live at `https://github.com/masad5-ai/theme.git`. The execution environment for this repository cannot reach that URL (HTTP 403 on git clone), so the HTML/CSS assets need to be provided manually by copying them into the project tree.

## How to bring the templates in
1. Clone or download the theme repository on a machine that can access GitHub: `git clone https://github.com/masad5-ai/theme.git`.
2. Copy the admin HTML files into `theme/admin/` and the storefront HTML files into `theme/front/` in this project (create the folders if they are missing). If you already have a branch with those folders populated (e.g., `main`), check it out before syncing.
3. Run `php scripts/sync_theme.php` to mirror the assets into `public/admin-theme/` and `public/theme/`. The script will warn if the source folders are empty so you know to switch branches or provide the HTML assets.
4. Run `php -S localhost:8000 -t public` to view and wire up the templates with the existing PHP routes.

If you can provide a ZIP of the `admin` and `front` folders from the theme repo, I can wire them directly into the PHP views here.
