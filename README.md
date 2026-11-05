# PHP Invoice Generator

A self-hosted invoice generator built with plain PHP 8.2, SQLite, Tailwind CSS (via CDN), and Dompdf. It supports client management, invoice creation, invoice numbering, status tracking, PDF downloads, and CSV exports without a framework.

## Features

- Single-admin login with password hashing and CSRF protection
- Business profile, logo upload, and invoice defaults
- Client management
- Invoice drafts with line items, discounts, and tax
- Sequential invoice numbers in `PREFIX-YYYY-0001` format
- Lifecycle tracking: draft, sent, paid, overdue, and void
- Invoice snapshots after sending
- PDF invoice downloads with embedded logo
- Dashboard totals for outstanding, paid this month, and overdue invoices
- CSV export of all invoices
- SQLite database created automatically on first run

## Requirements

- PHP 8.2 or newer
- Composer 2
- PHP extensions: `pdo_sqlite`, `mbstring`, `dom`, `xml`, and `gd`
- Writable `storage/` directory
- SQLite support enabled

## Project Structure

```text
database/schema.sql       SQLite schema
public/index.php          Front controller
src/                      Application code
templates/                PHP view templates
storage/                  SQLite database and uploaded logos
tests/                    PHPUnit tests
.env.example              Environment variable template
```

## Local Setup

### 1. Install dependencies

```bash
composer install
```

On Windows:

```powershell
composer install
```

### 2. Create your environment file

Unix/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 3. Generate an admin password hash

```bash
php -r "echo password_hash('change-this-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Put the generated hash in `ADMIN_PASSWORD_HASH`. Wrap the hash in single quotes so the `$` characters are preserved:

```dotenv
ADMIN_PASSWORD_HASH='$2y$10$...'
```

The hash in `.env.example` is for the password `password` and is only suitable for local testing.

### 4. Configure `.env`

```dotenv
APP_URL=http://localhost:8000
DB_PATH=storage/invoices.sqlite
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD_HASH='$2y$10$...'
MAX_LOGO_KB=2048
```

### 5. Start the development server

```bash
php -S localhost:8000 -t public public/index.php
```

Open `http://localhost:8000`. Log in with the `ADMIN_EMAIL` and password you configured.

The SQLite database is created automatically at `DB_PATH` the first time the application runs.

## Environment Variables

| Variable | Required | Default | Description |
| --- | --- | --- | --- |
| `APP_URL` | Yes | None | Full application URL, for example `https://invoices.example.com`. Used for URL and cookie security decisions. |
| `DB_PATH` | Yes | None | SQLite database path. Relative paths are resolved from the project root. Use an absolute path when the database should live elsewhere. |
| `ADMIN_EMAIL` | Yes | None | Email address used to log in. |
| `ADMIN_PASSWORD_HASH` | Yes | None | Password hash generated with `password_hash()`. Use single quotes around the hash. |
| `MAX_LOGO_KB` | No | `2048` | Maximum logo upload size in kilobytes. |

The application also honors `DB_PATH=:memory:` for tests. Do not use an in-memory database in production.

## Routes

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/login` | Login form |
| `POST` | `/login` | Authenticate |
| `POST` | `/logout` | End the session |
| `GET` | `/dashboard` | Financial dashboard |
| `GET` | `/dashboard/export.csv` | Download all invoices as CSV |
| `GET` | `/settings` | Business settings |
| `POST` | `/settings` | Update business settings |
| `GET` | `/logo` | Serve the uploaded logo |
| `GET` | `/clients` | Client list |
| `GET` | `/clients/new` | New client form |
| `POST` | `/clients` | Create client |
| `GET` | `/clients/{id}/edit` | Edit client form |
| `POST` | `/clients/{id}/edit` | Update client |
| `POST` | `/clients/{id}/delete` | Delete client |
| `GET` | `/invoices` | Invoice list |
| `GET` | `/invoices/new` | New invoice form |
| `POST` | `/invoices` | Create invoice draft |
| `GET` | `/invoices/{id}` | Invoice detail |
| `POST` | `/invoices/{id}/send` | Mark draft as sent and store snapshots |
| `POST` | `/invoices/{id}/pay` | Mark sent or overdue invoice as paid |
| `POST` | `/invoices/{id}/void` | Void a draft, sent, or overdue invoice |
| `GET` | `/invoices/{id}/pdf` | Download invoice PDF |
| `GET` | `/health` | Health check |

## Dashboard Totals

- **Outstanding**: total of all `sent` and `overdue` invoices.
- **Paid This Month**: total of invoices marked `paid` with a `paid_at` timestamp in the current UTC month.
- **Overdue**: total of unpaid invoices whose due date is before today.

Dashboard amounts use the default business currency. The CSV export includes each invoice's stored currency.

## Tests

```bash
vendor/bin/phpunit tests
```

On Windows:

```powershell
& .\vendor\bin\phpunit tests
```

## Deployment on a Shared PHP Host

### Preferred: point the document root to `public/`

1. Confirm the host provides PHP 8.2+ with `pdo_sqlite`, `mbstring`, `dom`, `xml`, and `gd`.
2. Install dependencies locally or over SSH:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. Upload the whole project outside the public web root, for example `/home/USER/invoice-generator`.
4. Set the domain or subdomain document root to `/home/USER/invoice-generator/public`.
5. Create `.env` on the server with production values:

   ```dotenv
   APP_URL=https://invoices.example.com
   DB_PATH=storage/invoices.sqlite
   ADMIN_EMAIL=you@example.com
   ADMIN_PASSWORD_HASH='$2y$10$...'
   MAX_LOGO_KB=2048
   ```

6. Make `storage/` and `storage/logos/` writable by PHP:
   - Directory permissions: `775` or `755` depending on the host.
   - The SQLite database and its directory must be writable.
7. Set `APP_URL` to the final HTTPS URL and enable HTTPS.
8. Visit `https://your-domain.example/health` and then log in.

### If the host forces `public_html` as the document root

Upload the project outside `public_html`, then use `public_html/index.php` as a thin front controller:

```php
<?php
declare(strict_types=1);

require '/home/USER/invoice-generator/public/index.php';
```

Add this `.htaccess` in `public_html` so clean URLs are routed to the front controller:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

In this setup, only the front controller is inside the web root. Keep `.env`, `src/`, `vendor/`, `templates/`, `database/`, and `storage/` outside `public_html`.

### Production checklist

- Change the default admin password and use a strong password.
- Use HTTPS and set `APP_URL` to the HTTPS URL.
- Keep `.env` out of the web root and restrict its permissions.
- Keep the SQLite database out of the web root when possible.
- Disable `display_errors` and log PHP errors instead.
- Ensure upload limits (`upload_max_filesize`, `post_max_size`) are at least `MAX_LOGO_KB`.
- Back up `storage/invoices.sqlite` and `storage/logos/` regularly.
- Do not run the PHP development server in production.
- Test login, invoice creation, PDF download, and CSV export after deployment.

## Troubleshooting

### "Missing required environment variable(s)"

Copy `.env.example` to `.env` and set `APP_URL`, `DB_PATH`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD_HASH`.

### Unable to write the SQLite database

Make sure the `storage/` directory and the path in `DB_PATH` are writable by the PHP process.

### Logo upload fails

Check `MAX_LOGO_KB`, PHP upload limits, and that `storage/logos/` is writable.

### Routes return 404 on a shared host

The document root must be `public/`, or requests must be rewritten to `public/index.php` using the `.htaccess` example above.

### Sessions or secure cookies do not work behind HTTPS

Set `APP_URL=https://your-domain.example` and confirm the host forwards HTTPS correctly to PHP.
