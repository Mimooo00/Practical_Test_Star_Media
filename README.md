# Social Share Tracker

A task-focused PHP 8 + MySQL demo. It has a small public website with five social share buttons, a tracking API that records each share click, and a login-protected admin dashboard with filterable charts and CSV export.

The project does **not** require XAMPP. Docker Desktop is the recommended setup on Windows and macOS; native Windows/macOS stacks are documented below as alternatives.

## Quick start with Docker (recommended)

**Prerequisites:** Docker Desktop for Windows (WSL2 backend) or macOS. No PHP, Apache, or MySQL installation is required on the host.

1. Clone the repository and open a terminal in the project folder:

   ```bash
   git clone https://github.com/Mimooo00/Practical_Test_Star_Media.git
   cd Practical_Test_Star_Media
   ```

2. Optional: create a local environment file to customise ports or passwords:

   macOS/Linux:
   ```bash
   cp .env.example .env
   ```

   Windows PowerShell:
   ```powershell
   Copy-Item .env.example .env
   ```

   Compose also works without `.env` by using its built-in defaults.

3. Build and start the stack:

   ```bash
   docker compose up -d --build
   ```

4. Open:

   | Service | URL |
   | --- | --- |
   | Website | <http://localhost:8080> |
   | Admin | <http://localhost:8080/admin/> |
   | phpMyAdmin | <http://localhost:8081> |

   Default admin credentials: **admin / admin123**. Change the password after signing in:

   ```bash
   docker compose exec app php scripts/set_admin_password.php admin "YourStrongPassword123"
   ```

   Default database credentials (see `.env.example`):
   - Database: `social_share_tracker`
   - App user: `share_app` / `share_pass`
   - Root password: `root_pass`

5. Useful commands:

   ```bash
   docker compose ps                 # container status
   docker compose logs -f app        # Apache/PHP logs
   docker compose logs -f db         # MySQL logs
   docker compose down               # stop containers
   docker compose down -v            # stop and delete the database volume
   docker compose up -d              # start again without rebuilding
   ```

The schema in `database/schema.sql` is imported automatically on the first start of the database volume. If you change the schema or want a clean database, run `docker compose down -v` and start again.

## Windows without Docker

Use Laragon, WAMP, or an existing XAMPP installation.

1. Install and start Laragon/WAMP/XAMPP with Apache and MySQL running.
2. Copy or clone the project into the Apache web root (`C:\xampp\htdocs\Practical_Test_Star_Media` for XAMPP).
3. Create/import the database:
   - Open phpMyAdmin and import `database/schema.sql`, or
   - Run:

     ```powershell
     Get-Content -Raw database\schema.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root
     ```

     For Laragon, use its terminal's `mysql -u root < database\schema.sql`, or import through phpMyAdmin.
4. Make sure Apache allows `.htaccess` overrides (`AllowOverride All`). Laragon, WAMP, and a standard XAMPP install do this by default for the web root.
5. Set `APP_URL` in `config/config.php` to the address you use, for example `http://localhost/Practical_Test_Star_Media`. If your MySQL root account has a password, update the DB settings in the same file or set the `DB_*` environment variables.
6. Open the site and admin URLs shown by your stack.

### If XAMPP is installed but the Control Panel is missing

Start the services directly from PowerShell:

```powershell
C:\xampp\apache\bin\httpd.exe
C:\xampp\mysql\bin\mysqld.exe --console
```

Or start the `Apache2.4` and `MySQL` entries from Windows **Services** (`services.msc`). The website path and commands remain the same.

## macOS without Docker

Use MAMP (Apache + MySQL), or use Docker Desktop as above.

1. Install MAMP and start the servers.
2. Point the MAMP document root at this project folder, or copy the project into MAMP's `htdocs` directory.
3. Import `database/schema.sql` through MAMP's phpMyAdmin, or from a terminal:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

4. Match the MAMP ports in `config/config.php`:
   - Apache default: `http://localhost:8888`
   - MySQL default: `3306` on some builds, `8889` on the standard MAMP configuration
   - MAMP MySQL credentials are often `root` / `root`
5. Ensure Apache allows `.htaccess` overrides, then open the configured site URL.

If the ports or credentials differ, adjust `APP_URL`, `DB_PORT`, `DB_USER`, and `DB_PASS` in `config/config.php` (or set them as environment variables).

## Features

**Public site**
- Home page plus two sample pages, responsive layout (Tailwind via CDN).
- Share buttons for Facebook, X, WhatsApp, Telegram, and Email.
- Each page has its own canonical URL, title, description, and Open Graph/Twitter Card image.
- Share URLs are generated per page from database-backed platform templates.
- Clicks are sent with `navigator.sendBeacon` (with a `fetch` keepalive fallback) while the share window opens.

**Tracking API (`api/track.php`)**
- POST-only JSON endpoint storing platform, page URL, title, path, hashed IP, and timestamp.
- Validates the platform, URL scheme/host, field lengths, and payload shape.
- Origin/Referer checks, per-IP/platform rate limiting (20/minute), and a 3-second duplicate guard.
- Returns `204` on success and JSON errors for rejected requests.

**Admin dashboard (`/admin/`)**
- Session login with CSRF protection, password hashing, and a 5-failure/15-minute throttle.
- KPI cards, line chart by platform over time, platform doughnut chart, top-pages bar chart, and recent clicks.
- Filters for date range, platform, page, and day/week/month grouping.
- CSV export respects the active filters.

## Requirements

- PHP 8.1+ with PDO MySQL (`pdo_mysql`); the Docker image uses PHP 8.3
- MySQL 5.7+ / 8.x or MariaDB 10.3+
- Apache with `.htaccess` / `AllowOverride` enabled (or the Docker stack)
- Internet access for the Tailwind Play CDN and Chart.js CDN
- No Composer or build step

## Database access

**Docker**
- Open <http://localhost:8081>, server `db`, user `share_app`, password `share_pass` (defaults).
- CLI: `docker compose exec db mysql -u share_app -p social_share_tracker`
- To connect a desktop client from the host, uncomment the `db` port mapping in `compose.yaml` and restart. It exposes port `3307`.

**Native (XAMPP/Laragon/WAMP/MAMP)**
- Use the stack's phpMyAdmin, or run `mysql -u root` (add `-p` if your root account has a password).

## Configuration

Defaults live in `config/config.php`:

| Setting | Default |
| --- | --- |
| App URL | `http://localhost/Practical_Test_Star_Media` |
| Database | `social_share_tracker` on `127.0.0.1:3306` |
| DB user/password | `root` / empty |
| Timezone | `Asia/Kuala_Lumpur` |

Environment overrides: `APP_URL`, `APP_TIMEZONE`, `APP_DEBUG`, `APP_IP_SALT`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

`APP_URL` must match the address used in the browser. A mismatch causes tracking requests to be rejected. Set `APP_DEBUG=false` in production and use a unique `APP_IP_SALT`.

## Project structure

```
Dockerfile                PHP 8.3 + Apache image
compose.yaml              app + MySQL + phpMyAdmin
docker/apache.conf        Apache virtual host
.env.example              optional local overrides
api/track.php             tracking endpoint
admin/                    login, dashboard, CSV export, auth helpers
assets/                   CSS, JS, favicon, Open Graph images
config/config.php         app, database, security, and sharing config
config/database.php       PDO connection
content/pages.php         sample page metadata and body content
database/schema.sql       schema + platform/admin seed data
includes/                 bootstrap, helpers, layout, share bar
scripts/                  CLI admin password tool
index.php                 home page
page.php                  ?slug=... sample page renderer (404 for unknown slugs)
```

## How click tracking works

1. Each page renders share links from the `platforms` table (for example `https://wa.me/?text={title}%20{url}`). Tokens are URL-encoded per component.
2. On click, `assets/js/share.js` opens the share popup synchronously to avoid popup blockers, then sends `{platform, url, title}` to `api/track.php` via `sendBeacon`.
3. Email uses a `mailto:` link; tracking is queued first, then the mail client opens.
4. The API validates the payload, checks the request origin, applies rate/duplicate limits, and inserts a row into `share_clicks`.
5. The dashboard aggregates `share_clicks` joined to `platforms`, so filtering and charts stay dynamic.

## Adding another social platform

No code changes are required:

```sql
USE social_share_tracker;

INSERT INTO platforms (slug, name, color, share_url_template, is_active, sort_order)
VALUES (
    'linkedin',
    'LinkedIn',
    '#0A66C2',
    'https://www.linkedin.com/sharing/share-offsite/?url={url}',
    1,
    6
);
```

Supported tokens: `{url}`, `{title}`, `{description}`. Platforms with an empty template are skipped in the share bar. Known icon slugs are `facebook`, `x`, `whatsapp`, `telegram`, and `email`; other slugs receive a generic share icon. Add an SVG path in `platform_icon_svg()` in `includes/functions.php` for a custom icon.

## Verified behaviour

- `php -l` passes on every PHP file; both JavaScript files pass `node --check`.
- All five share links point to the correct platform endpoints with encoded page URL/title/description.
- The click API returns `204` and inserts a row; duplicate clicks within 3 seconds are suppressed; unknown platforms, external URLs, oversized titles, malformed JSON, GET requests, and cross-origin requests are rejected; the 21st click in a minute returns `429`.
- Each sample page exposes distinct canonical, Open Graph, and Twitter metadata.
- Admin login, logout, CSRF protection, lockout messaging, filters, charts, recent-click escaping, and CSV export were tested against the local database.
- `.htaccess` denies direct access to `config/`, `includes/`, `content/`, `database/`, `scripts/`, admin helper files, and `.git`.

## Troubleshooting

- **Port 8080 or 8081 already in use** — change `APP_PORT` / `PMA_PORT` in `.env`, update `APP_URL` to match the new app port, then run `docker compose up -d`.
- **Database changes are not appearing** — the schema is only imported when the database volume is first created. Run `docker compose down -v` and start again. Changing `DB_USER` or `DB_PASS` after the first start also requires recreating the volume or updating the MySQL user manually.
- **Tracking returns 403 or 422** — `APP_URL` does not match the browser address (including the port), or the database is not reachable.
- **Docker daemon not running** — start Docker Desktop and wait for it to report that it is running.
- **Native MySQL access denied** — update `DB_USER`/`DB_PASS`, and `DB_PORT` for MAMP.
- **Blank or unstyled site** — verify internet access for the Tailwind and Chart.js CDNs.

## Notes and limitations

- Tailwind's Play CDN and Chart.js are loaded from CDNs. Tailwind's CDN is intended for development/prototyping; compile a CSS file for production.
- Email sharing opens the user's mail client (`mailto:`); the app does not send mail itself.
- IP addresses are stored only as an HMAC-SHA256 hash for rate limiting; no raw IP is persisted.
- The duplicate guard suppresses repeat clicks from the same IP/platform/page within 3 seconds. Adjust `track_duplicate_seconds` in `config/config.php` if exact per-click counting is preferred.
- Social platforms cannot scrape previews from `localhost`. Set `APP_URL` to a public HTTPS URL when deploying.
- The folder name and local URLs may include `Practical_Test_Star_Media` because that is the existing project directory; rename the folder and update `APP_URL` if a different path is required.
