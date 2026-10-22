# Social Share Tracker

A task-focused PHP 8 + MySQL demo. It has a small public website with five social share buttons, a tracking API that records each share click, and a login-protected admin dashboard with filterable charts and CSV export.

## Features

**Public site**
- Home page plus two neutral sample pages, responsive layout (Tailwind via CDN).
- Share buttons for Facebook, X, WhatsApp, Telegram, and Email.
- Every page has its own canonical URL, title, description, and Open Graph/Twitter Card image.
- Share URLs are generated per page from database-backed platform templates.
- Clicks are sent with `navigator.sendBeacon` (with a `fetch` keepalive fallback) while the share window opens.
- The buttons are real links, so sharing still works without JavaScript (without click tracking).

**Tracking API (`api/track.php`)**
- POST-only JSON endpoint storing platform, page URL, title, path, hashed IP, and timestamp.
- Validates the platform, URL scheme and host, field lengths, and payload shape.
- Origin/Referer host checks, per-IP/platform rate limiting (20/minute), and a 3-second duplicate guard.
- Returns `204` on success and JSON errors for invalid or rejected requests.

**Admin dashboard (`/admin/`)**
- Session login with CSRF protection, password hashing, generic errors, and a 5-failure/15-minute throttle.
- KPI cards, clicks-over-time line chart (one dataset per platform, day/week/month grouping), platform doughnut chart, top-pages bar chart, and recent clicks table.
- Filters for date range, social platform, page, and grouping interval.
- CSV export respects the active filters, with a UTF-8 BOM and formula-injection protection.
- Platforms come from a lookup table, so adding one appears automatically in the share bar, filters, and charts.

## Requirements

- PHP 8.1+ with PDO MySQL (`pdo_mysql`); verified on PHP 8.3.31
- MySQL 5.7+ or MariaDB 10.3+
- Apache (XAMPP is fine) with `.htaccess` / `AllowOverride` enabled
- Internet access for the Tailwind Play CDN and Chart.js CDN
- No Composer, build step, or extra PHP extensions

## Setup

1. Copy the project to the web root (already at `C:\xampp\htdocs\Practical_Test_Star_Media` for XAMPP).
2. Start Apache and MySQL from the XAMPP control panel.
3. Import the schema and seed data:

   ```powershell
   Get-Content -Raw database\schema.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root
   ```

   Or import `database/schema.sql` through phpMyAdmin. This creates the `social_share_tracker` database with the `platforms`, `share_clicks`, and `admins` tables, seeds the five platforms, and creates the default admin.

4. Check `config/config.php`. The defaults are:

   | Setting | Default |
   | --- | --- |
   | App URL | `http://localhost/Practical_Test_Star_Media` |
   | Database | `social_share_tracker` on `127.0.0.1:3306` |
   | DB user/password | `root` / empty |
   | Timezone | `Asia/Kuala_Lumpur` |

   Environment overrides: `APP_URL`, `APP_TIMEZONE`, `APP_DEBUG`, `APP_IP_SALT`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

   `APP_DEBUG` defaults to `true` locally. Set it to `false` (or `APP_DEBUG=0`) in a real deployment, and set a unique `APP_IP_SALT` so stored IP hashes cannot be reproduced.

5. Open the site: <http://localhost/Practical_Test_Star_Media/>.

6. Open the admin: <http://localhost/Practical_Test_Star_Media/admin/>.

   Default credentials: **admin / admin123**. Change the password immediately:

   ```powershell
   php scripts/set_admin_password.php admin "YourStrongPassword123"
   ```

## Project structure

```
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
5. The dashboard aggregates `share_clicks` joined to `platforms`, so filtering and charts remain dynamic.

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

## Notes and limitations

- Tailwind's Play CDN and Chart.js are loaded from CDNs. Tailwind's CDN is intended for development/prototyping; compile a CSS file for production.
- Email sharing opens the user's mail client (`mailto:`); the app does not send mail itself.
- IP addresses are stored only as an HMAC-SHA256 hash for rate limiting; no raw IP is persisted.
- The duplicate guard suppresses repeat clicks from the same IP/platform/page within 3 seconds. Adjust `track_duplicate_seconds` in `config/config.php` if exact per-click counting is preferred.
- Social platforms cannot scrape previews from `localhost`. Set `APP_URL` to a public HTTPS URL when deploying so they can read the Open Graph metadata.
- The folder name and local URL still include `Practical_Test_Star_Media` because that is the existing project directory; rename the folder and update `APP_URL` if a different path is required.
