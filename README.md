# Rahyaft Sanat — corporate & product website

A multilingual (Persian / English / Arabic) corporate and product website with a
full administration panel, built to run on ordinary PHP shared hosting.

Persian is the default language and the whole layout mirrors correctly for RTL.
There is no build step, no Node.js requirement and no Composer requirement on
the production server.

---

## Contents

- [Requirements](#requirements)
- [What is included](#what-is-included)
- [Local installation](#local-installation)
- [cPanel / shared-hosting installation](#cpanel--shared-hosting-installation)
- [Database setup](#database-setup)
- [Creating the first administrator](#creating-the-first-administrator)
- [File permissions](#file-permissions)
- [E-mail configuration](#e-mail-configuration)
- [Configuration reference](#configuration-reference)
- [Project structure](#project-structure)
- [Content model](#content-model)
- [Re-importing the source content](#re-importing-the-source-content)
- [Production checklist](#production-checklist)
- [Troubleshooting](#troubleshooting)

---

## Requirements

| Requirement | Notes |
|---|---|
| PHP >= 8.1 | 8.2 or 8.3 recommended. Tested on 8.5. |
| MySQL >= 5.7 or MariaDB >= 10.2 | InnoDB, `utf8mb4` |
| `pdo_mysql` | required |
| `mbstring` | required |
| `json` | required |
| `fileinfo` | required — used to verify uploaded file types |
| `gd` | recommended — generates responsive image sizes on upload |
| `openssl` | recommended — needed for SMTP over TLS |
| `zip` | optional |
| Apache `mod_rewrite` | recommended — see the fallback note below |

Composer is **not** required: PHPMailer is vendored in `app/Vendor/PHPMailer`
and the project ships its own PSR-4 autoloader.

**Without `mod_rewrite`** the site still works, but URLs take the form
`/index.php/fa/products` instead of `/fa/products`. If your host cannot enable
it, set `APP_URL` to include `/index.php` — for example
`APP_URL=https://example.com/index.php`.

---

## What is included

- Public site in Persian, English and Arabic with language-prefixed URLs
  (`/fa/…`, `/en/…`, `/ar/…`), correct `dir` switching, and `hreflang`
  alternates on every page.
- Product catalogue with categories, galleries, grouped specification tables,
  capabilities, applications, advantages, downloadable datasheets and related
  products.
- Research & Development section with per-project pages and galleries.
- Editable Home, About, Research and Contact pages built from modular sections.
- Contact form with server-side validation, CSRF protection, honeypot and
  timing checks, per-IP rate limiting, database storage and optional SMTP
  notification.
- Administration panel at `/admin`: dashboard, product CRUD, categories, R&D
  projects, page editor, message inbox with CSV export, media library, site
  settings and administrator accounts with roles.
- Technical SEO: canonical URLs, `hreflang`, Open Graph, Twitter cards, XML
  sitemap, `robots.txt`, and Organization / Product / BreadcrumbList structured
  data.
- Installation wizard at `/install.php` that creates the schema, imports the
  prepared content, creates the first administrator and then locks itself.

---

## Local installation

### The quick way

```bash
./dev.sh fresh     # create the database, load the schema, import the content
./dev.sh start     # start the site and open it in a browser
```

`fresh` creates a local admin account: **admin@localhost / password1234**.
These are development credentials — never use them on a live site.

`dev.sh` commands:

| Command | What it does |
|---|---|
| `./dev.sh` or `start` | Check requirements, start MariaDB if needed, serve the site |
| `./dev.sh stop` | Stop the dev server |
| `./dev.sh restart` | Stop, then start |
| `./dev.sh status` | Show what is running and which database is configured |
| `./dev.sh fresh` | Rebuild the database and reimport all content (destructive, asks first) |
| `./dev.sh install` | Clear `.env` and open the installation wizard instead |
| `./dev.sh seed` | Re-import content from `database/content/` |
| `./dev.sh logs` | Follow the application and server logs |
| `./dev.sh check` | Verify extensions, syntax, routes and that private paths are blocked |

Options: `--port N` to use another port, `--no-open` to skip opening a browser.

`dev.sh` is a development convenience only — production uses Apache and
`index.php`. You may delete it, along with `server.php`, before deploying.

### By hand

```bash
# 1. Create the database and a user
mysql -u root -p -e "CREATE DATABASE rahyaft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'rahyaft_user'@'localhost' IDENTIFIED BY 'choose-a-password';
                     GRANT ALL PRIVILEGES ON rahyaft.* TO 'rahyaft_user'@'localhost'; FLUSH PRIVILEGES;"

# 2. Start the development server from the project root
php -S localhost:8000 server.php

# 3. Open the installer and follow the five steps
open http://localhost:8000/install.php
```

`server.php` is a router for PHP's built-in server only. It refuses to run under
any other SAPI, so uploading it to a real host is harmless — but you may delete
it before deploying.

After installation:

- website — <http://localhost:8000/>
- admin — <http://localhost:8000/admin>

To reinstall locally, drop the database, delete `.env` and `installed.lock`, and
open `/install.php` again.

---

## cPanel / shared-hosting installation

1. **Upload the files.** Put the *contents* of this project directly into
   `public_html` (or into a subfolder if the site is not at the domain root).
   The application is designed for this layout: `index.php` sits at the web
   root and the sensitive folders are protected by `.htaccess`.

2. **Create the database.** In cPanel → *MySQL® Databases*:
   - create a database, e.g. `myaccount_rahyaft`
   - create a user with a strong password
   - add the user to the database with **All Privileges**
   - note the full names — cPanel prefixes them with your account name

3. **Check the PHP version.** cPanel → *Select PHP Version* → choose 8.1 or
   newer, and make sure `pdo_mysql`, `mbstring`, `fileinfo` and `gd` are ticked.

4. **Run the installer.** Visit `https://yourdomain.com/install.php` and follow
   the wizard:
   - Step 1 checks the server requirements
   - Step 2 takes the database details and creates the tables
   - Step 3 takes the site name, address and default language
   - Step 4 creates the first administrator
   - Step 5 confirms, and writes `installed.lock`

5. **Delete the installer.** Once step 5 reports success, delete
   `install.php` from the server. The lock file already prevents it running
   again, but removing the file is the safer default.

6. **Enable HTTPS**, then uncomment the HTTPS redirect block near the top of
   `.htaccess`.

The site needs no Docker, no shell access and no cron jobs.

---

## Database setup

The installer creates every table for you. If you prefer to do it by hand
(for example through phpMyAdmin), import `database/schema.sql` into an empty
database and then copy `.env.example` to `.env` and fill in the credentials.

To load the prepared content afterwards, run:

```bash
php database/seed.php
```

The schema uses InnoDB with foreign keys and `utf8mb4_unicode_ci`. Deleting a
category leaves its products in place (they simply become uncategorised);
deleting a product removes its translations, images, specifications and
documents through cascades.

---

## Creating the first administrator

The first administrator is created by the installer at step 4 and is given the
**owner** role.

Further accounts are created inside the panel at **Administrators**, which only
an owner can open. Three roles are available:

| Role | Can do |
|---|---|
| `owner` | everything, including managing administrator accounts and settings |
| `admin` | manage all content, including deleting it |
| `editor` | create and edit content, but not delete it |

The application refuses to remove or demote the last active owner, so you cannot
lock yourself out.

If you lose access entirely, reset the password directly in the database with a
hash generated by PHP:

```bash
php -r 'echo password_hash("your-new-password", PASSWORD_DEFAULT), PHP_EOL;'
```

```sql
UPDATE admin_users SET password_hash = '<paste the hash>' WHERE email = 'you@example.com';
```

---

## File permissions

Only these directories need to be writable by PHP:

| Path | Permission |
|---|---|
| `storage/logs` | `755` (directory), files `644` |
| `storage/cache` | `755` |
| `uploads/media` | `755` |
| `uploads/files` | `755` |
| `.env` | `600` — readable only by the account that owns it |

Everything else can stay at `644` for files and `755` for directories.

**Never use `777`.** On shared hosting PHP runs as your own user, so `755` is
sufficient. If uploads fail with a permissions error, check ownership rather
than widening the mode.

---

## E-mail configuration

Contact-form notifications are optional and are sent with PHPMailer over SMTP.
**Enquiries are always written to the database first**, so a mail failure never
loses a message — it is recorded in `storage/logs/` and the enquiry still
appears in the admin inbox.

Set these in `.env`:

```ini
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls          ; tls for 587, ssl for 465, blank for none
MAIL_USERNAME=no-reply@yourdomain.com
MAIL_PASSWORD=your-mailbox-password
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="Rahyaft Sanat"
MAIL_NOTIFY_TO=sales@yourdomain.com
```

Notes:

- Create the mailbox in cPanel → *Email Accounts* first, and send **from** an
  address on your own domain. Sending as a Gmail/Yahoo address will fail SPF
  and be treated as spam.
- `MAIL_NOTIFY_TO` is where new enquiries are announced. Leave it blank to
  disable notifications; the first address in **Settings → Contact information**
  is then used as a fallback.
- To use PHP's `mail()` instead of SMTP, set `MAIL_MAILER=mail`. This needs no
  credentials but is far more likely to be filtered as spam.
- Credentials live only in `.env`. They are never stored in the database and
  never rendered into a page.

---

## Configuration reference

| Variable | Purpose |
|---|---|
| `APP_ENV` | `production` on a live site, `local` while developing |
| `APP_DEBUG` | `false` in production — `true` prints stack traces |
| `APP_URL` | Public base URL, no trailing slash. Used for canonicals and the sitemap |
| `APP_KEY` | Random 32+ character string, generated by the installer |
| `APP_DEFAULT_LOCALE` | `fa`, `en` or `ar` — the fallback language for untranslated fields |
| `APP_LOCALES` | Comma-separated list of active languages |
| `DB_HOST` `DB_PORT` `DB_NAME` `DB_USER` `DB_PASSWORD` `DB_CHARSET` | Database connection |
| `MAIL_MAILER` `MAIL_HOST` `MAIL_PORT` `MAIL_ENCRYPTION` `MAIL_USERNAME` `MAIL_PASSWORD` | SMTP transport |
| `MAIL_FROM_ADDRESS` `MAIL_FROM_NAME` | Envelope sender for outgoing mail |
| `MAIL_NOTIFY_TO` | Where new contact enquiries are announced |

`.env.example` documents the same list and is safe to commit; `.env` is ignored
by git and must never be committed.

---

## Project structure

```
/
├── index.php                 single public entry point (front controller)
├── install.php               installation wizard — delete after installing
├── server.php                router for PHP's built-in dev server only
├── dev.sh                    local development helper (start/stop/fresh/check)
├── .htaccess                 rewriting, directory protection, security headers
│
├── app/
│   ├── Core/                 Router, Database, Auth, View, Uploader, Mailer, …
│   ├── Controllers/
│   │   ├── Site/             public pages
│   │   └── Admin/            administration panel
│   ├── Models/               translation-aware data access
│   ├── Support/              global template helpers
│   └── Vendor/PHPMailer/     vendored so no Composer is needed on the host
│
├── config/                   app, database and mail configuration
├── database/
│   ├── schema.sql            full schema
│   ├── Seeder.php            content importer
│   ├── seed.php              CLI entry point for the importer
│   ├── media_manifest.json   media produced by the build-time image pipeline
│   └── content/              the extracted source content, as PHP arrays
│
├── resources/
│   ├── views/                layouts, partials, site pages, admin screens
│   └── lang/                 fa.php, en.php, ar.php — interface strings
│
├── routes/web.php            route table
├── assets/                   css, js, fonts, logo and favicons
├── uploads/                  media and documents (writable, never executable)
└── storage/                  logs and cache (writable)
```

Everything except `assets/`, `uploads/` and the three root PHP files is blocked
from direct HTTP access by `.htaccess`.

---

## Content model

Every translatable entity has a companion `*_translations` table keyed by
`(entity_id, lang)`. Reads join the active language and fall back to
`APP_DEFAULT_LOCALE`, so a partially translated site still renders complete
pages rather than blank fields.

Adding a fourth language is a data change, not a schema change:

1. add it to `Lang::SUPPORTED` in `app/Core/Lang.php`
2. create `resources/lang/<code>.php`
3. add the code to `APP_LOCALES`

The admin forms then show a tab for it automatically.

**Conventions used by the page editor:**

- multi-line text fields render one paragraph, or one list item, per line
- `features` sections take `Title | description` per line
- `stats` sections take `Value | label` per line

---

## Re-importing the source content

`database/content/` holds the content extracted from the supplied raw materials
as readable PHP arrays. To reload it:

```bash
php database/seed.php
```

The importer is keyed on slugs, so it is safe to re-run: it updates existing
records instead of duplicating them. Note that it **rewrites** specification
tables, capabilities and page sections, since those are derived data — so
edits made in the admin panel to those specific structures will be replaced.
Product text, categories and settings you have edited are preserved.

Each imported product records the file it came from in `products.source_ref`,
and entries that could not be fully verified are flagged with `needs_review`
and listed on the admin dashboard.

---

## Production checklist

- [ ] `APP_ENV=production` and `APP_DEBUG=false`
- [ ] `APP_URL` set to the real `https://` address, with no trailing slash
- [ ] `APP_KEY` set to a unique random value
- [ ] `install.php` deleted from the server
- [ ] `installed.lock` present in the project root
- [ ] `.env` set to `600` and confirmed unreachable — visiting
      `https://yourdomain.com/.env` must return 403
- [ ] HTTPS certificate installed and the redirect block in `.htaccess`
      uncommented
- [ ] Strong, unique administrator passwords (minimum 10 characters)
- [ ] SMTP configured and a test enquiry received
- [ ] Automatic database backups enabled in cPanel
- [ ] `uploads/` and `storage/` writable but not world-writable
- [ ] Sitemap reachable at `/sitemap.xml` and submitted to Google Search Console

Verify the protections quickly:

```bash
curl -I https://yourdomain.com/.env               # expect 403
curl -I https://yourdomain.com/app/Core/Database.php   # expect 403
curl -I https://yourdomain.com/database/schema.sql     # expect 403
```

---

## Troubleshooting

**"The site is not installed yet"** — `.env` or `installed.lock` is missing.
Run `/install.php`.

**Installer says "already installed"** — delete `installed.lock` to unlock it.
Doing so lets the wizard rewrite `.env`, so only do it deliberately.

**404 on every page except the home page** — `mod_rewrite` is unavailable or
`AllowOverride` is off. Either enable it, or set `APP_URL` to end in
`/index.php` and use `/index.php/fa/products` style URLs.

**Uploads fail with a permissions error** — make `uploads/media` and
`uploads/files` writable by the PHP user (`755` with correct ownership).

**Uploads rejected as "contents do not match its extension"** — this is the
MIME check working. The file is not really the type its extension claims.

**Images upload but no smaller sizes appear** — the `gd` extension is missing.
The original is stored and still displayed; enable `gd` and re-upload for
responsive sizes.

**Contact form saves but no e-mail arrives** — check `storage/logs/` for the
delivery error, confirm `MAIL_NOTIFY_TO` is set, and send from an address on
your own domain. The enquiry is in the admin inbox regardless.

**Persian text shows as question marks** — the database or connection is not
`utf8mb4`. Confirm `DB_CHARSET=utf8mb4` and that the tables use
`utf8mb4_unicode_ci`.

---

## Credits

- **Vazirmatn** typeface by Saber Rastikerdar — SIL Open Font License 1.1
  (`assets/fonts/Vazirmatn-OFL.txt`)
- **PHPMailer** — LGPL-2.1 (`app/Vendor/PHPMailer/LICENSE`)

Company logo, product renders, photographs and all technical specifications are
the property of Rahyaft Sanat and were taken from the source materials supplied
with this project.
