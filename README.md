# Peniel Properties — Real Estate Website

A full-stack, production-ready real estate web application built for **Peniel Properties**, an exclusive property sales and rentals agency operating in Zimbabwe.

Designed with an international luxury aesthetic and tailored to Zimbabwean property buyers and investors, the platform features a database-driven property catalogue, robust search and filtering, pre-filled WhatsApp click-to-chat integration, CSRF-protected lead capture, and a secure administration dashboard.

---

## Brand Identity & Design System

The platform strictly adheres to the approved Peniel Properties visual identity:
- **Primary Navy:** `#0B2D50`
- **Deep Navy:** `#082541`
- **Secondary Blue:** `#123E68`
- **Lime Green Accent:** `#A5D94A`
- **Pure White & Soft Off-White:** `#FFFFFF` / `#F8F9FA`
- **Typography:** Serif headings (*Playfair Display*) paired with modern geometric UI copy (*Plus Jakarta Sans*).
- **Approved Architectural Logo:** Integrated with configurable asset paths in `app/config/config.php`.

---

## Technical Stack

- **Backend:** PHP 8.2+
- **Database:** MySQL 5.7+ / MariaDB 10.3+ with PDO (Prepared Statements throughout)
- **Frontend:** Semantic HTML5, Vanilla CSS3 (Custom Design Tokens), Vanilla JavaScript (No heavy frameworks or Node.js runtime required)
- **Hosting Compatibility:** Apache shared hosting, cPanel `public_html`, and local XAMPP environments
- **SEO & Security:** Dynamic XML sitemap, `robots.txt`, CSRF protection, session hardening, MIME-type upload validation, and Apache `.htaccess` script execution prevention.

---

## Directory Architecture

```
Peniel Properties/
├── .htaccess                 # Apache rewrite rules, security headers & directory protection
├── .env.example              # Environment variables template
├── .gitignore                # Ignored cache, logs, and sensitive files
├── README.md                 # Documentation and deployment manual
├── index.php                 # Homepage (Hero, search, featured listings, categories, CTAs)
├── properties.php            # Catalogue (Dynamic search, category/type/price filters, sorting, pagination)
├── property.php              # Details page (Gallery, specs, amenities, WhatsApp CTA, inquiry form)
├── about.php                 # Corporate background, values & agency ethos
├── contact.php               # Contact information, opening hours, interactive lead capture form
├── 404.php                   # Custom branded error page
├── sitemap.xml.php           # Dynamic search engine sitemap
├── robots.txt                # Search indexation directives
├── assets/
│   ├── css/
│   │   ├── style.css         # Master design system & responsive styling
│   │   └── admin.css         # Sleek administration portal stylesheet
│   ├── js/
│   │   ├── main.js           # Navigation, search tabs & gallery thumbnail switcher
│   │   └── admin.js          # Slug generator, image previews & confirmation dialogs
│   └── images/
│       ├── logo.png          # Approved Peniel Properties logo (transparent PNG, navy panels & footer)
│       ├── favicon.png       # Navy favicon / social share tile
│       └── hero.jpg          # Hero photography
├── uploads/
│   ├── .htaccess             # Security guard disabling PHP execution in media folders
│   └── properties/           # Uploaded listing photographs
├── admin/
│   ├── index.php             # Executive dashboard metrics & recent leads
│   ├── login.php             # Secure staff authentication with login rate-limiting
│   ├── logout.php            # Session termination
│   ├── properties.php        # Property portfolio manager (publish/feature toggles, deletion)
│   ├── property-create.php   # Comprehensive listing creator with multi-photo upload
│   ├── property-edit.php     # Full listing editor & gallery manager
│   ├── inquiries.php         # Customer inquiry lead manager with WhatsApp quick-reply
│   ├── settings.php          # Configurable company phone, WhatsApp, email, and office address
│   └── includes/
│       ├── auth.php          # Session authentication guard
│       ├── header.php        # Reusable admin header & sidebar navigation
│       └── footer.php        # Reusable admin footer
├── app/
│   ├── .htaccess             # Protects application core from direct web browsing
│   ├── config/
│   │   ├── config.php        # Global configuration, dynamic base URL, and logo paths
│   │   └── database.php      # PDO database connection factory
│   ├── helpers/
│   │   └── helpers.php       # Escaping, formatting, CSRF, uploads, WhatsApp helpers
│   └── includes/
│       ├── header.php        # Global site navigation
│       ├── footer.php        # Global site footer
│       └── property-card.php # Reusable property card component
└── database/
    ├── .htaccess             # Restricts web access to database directory
    └── database.sql          # Complete importable schema & demonstration listings
```

---

## Local Setup Guide (XAMPP / phpMyAdmin)

### Step 1: Place Files in XAMPP Web Root
Ensure the folder is located inside your XAMPP web directory, for example:
`C:\xampp\htdocs\Peniel Properties`

### Step 2: Start Apache and MySQL
Open the **XAMPP Control Panel** and click **Start** on both **Apache** and **MySQL**.

### Step 3: Import Database via phpMyAdmin
1. Open your web browser and navigate to:
   `http://localhost/phpmyadmin`
2. Open the **Databases** tab and create an empty database named `peniel_properties`
   (charset `utf8mb4`, collation `utf8mb4_unicode_ci`).
3. Select `peniel_properties` in the left sidebar, then click **Import**.
4. Click **Choose File** and select:
   `c:\Users\leslie\Documents\Web Projecta\Peniel Properties\database\database.sql`
5. Click **Import** (or **Go**) at the bottom.
6. All tables (`properties`, `property_images`, `inquiries`, `admins`, `site_settings`) are created
   and populated with demonstration data. The SQL file intentionally contains **no**
   `CREATE DATABASE` statement so it imports into any pre-created database (cPanel-safe).

### Step 4: Verify Database Connection
Credentials are read from the `.env` file in the project root (falling back to the
defaults in `app/config/config.php`). For XAMPP the defaults are user `root` with an
empty password:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=peniel_properties
DB_USER=root
DB_PASS=
```

### Step 5: Test in Browser
- **Public Website:** `http://localhost/Peniel%20Properties/`
- **Admin Portal:** `http://localhost/Peniel%20Properties/admin/login.php`

---

## Administrator Credentials

- **Username:** `admin` (or `info@penielproperties.co.zw`)
- **Default Password:** `PenielAdmin2026!`

*(Note: Change your password or create custom administrator accounts directly inside the admin panel or database before production deployment).*

---

## cPanel Shared Hosting Deployment Guide

### 1. Set the PHP Version
1. In cPanel, open **MultiPHP Manager** (or "Select PHP Version").
2. Set the domain/addon domain to **PHP 8.1** or newer (the app uses PHP 8 `match` expressions).
3. Confirm required extensions are enabled: `pdo_mysql`, `mbstring`, `fileinfo`, `json`.

### 2. Create MySQL Database & User
1. In cPanel, navigate to **MySQL® Databases**.
2. Create a new database (e.g. `cpaneluser_peniel`).
3. Create a new database user with a strong password.
4. Add the user to the database and grant **ALL PRIVILEGES**.
5. Note the full prefixed names — cPanel always prefixes them (e.g. DB `cpaneluser_peniel`, user `cpaneluser_dbuser`).

### 3. Upload Project Files
1. In cPanel, open **File Manager**, enter `public_html` (or your addon-domain folder).
2. Upload the project as a ZIP and extract it, keeping the folder structure intact
   (all `*.php`, `assets/`, `admin/`, `app/`, `database/`, `uploads/`, plus `.htaccess`, `robots.txt`, `.env`).
3. Enable **Settings → Show Hidden Files (dotfiles)** so `.htaccess` and `.env` are visible and actually transferred.
4. Verify the root `.htaccess` sits next to `index.php` (not inside a nested folder).

### 4. Configure Production Credentials (`.env`)
Edit the `.env` file in the web root (File Manager → Edit). It is excluded from git and
blocked from web access by `.htaccess`, so credentials stay private:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cpaneluser_peniel
DB_USER=cpaneluser_dbuser
DB_PASS=YourStrongPasswordHere
```

No code changes are needed — `app/config/config.php` reads this file automatically
(falling back to server environment variables, then safe defaults).

### 5. Import Schema via cPanel phpMyAdmin
1. In cPanel, open **phpMyAdmin**.
2. Select your newly created (prefixed) database in the left sidebar — this is required,
   because the SQL file contains **no** `CREATE DATABASE` statement (cPanel users cannot
   create databases directly, which previously caused error **#1044**).
3. Click **Import**, choose `database/database.sql`, and press **Go**.
4. Confirm tables exist: `properties`, `property_images`, `inquiries`, `admins`, `site_settings`.
5. After import, update the contact numbers stored in the DB if they differ
   (**Admin → Site Settings**, or `UPDATE site_settings ...` for `contact_phone` / `whatsapp_number`).

### 6. File Permissions
- `uploads/` and `uploads/properties/` → `755` (owner: your cPanel account). Use `775` only if
  the host runs PHP as a different user and image uploads fail.
- All PHP/CSS/JS files → `644`.
- Change the admin password (**Admin → Site Settings** or DB) from the README default before going live.

### 7. SSL / HTTPS
Enable **AutoSSL** (or Let's Encrypt) in cPanel for your domain — required for
browser push notifications and secure sessions.

### 8. Post-Deployment Checklist
- `https://yourdomain.com/` renders the homepage with listings
- `https://yourdomain.com/admin/login.php` → sign in with the admin account
- Submit a test inquiry → toast appears, the record shows in **Admin → Inquiries**, and the sidebar badge increments
- Upload a test property image → file saves under `uploads/properties/`
- `https://yourdomain.com/sitemap.xml` and `/robots.txt` respond correctly
- `/app/`, `/database/`, `/.env` all return **403 Forbidden** (not file contents)

### Troubleshooting
- **500 Internal Server Error right after upload:** almost always an `.htaccess` directive your host
  disallows. In File Manager rename `.htaccess` to `.htaccess.bak` — if the site loads, rename it back
  and remove `Options -Indexes` (host has `AllowOverride` without `Options`), then re-test.
- **"Access denied" / blank admin pages:** confirm `mod_rewrite` is active (cPanel → Apache Modules)
  and the `.htaccess` is in the web root, not a subfolder.
- **Database connection failed:** re-check the prefixed `DB_NAME`/`DB_USER`/`DB_PASS` in `.env`
  (cPanel prefixes both with your account name) and that the user has **ALL PRIVILEGES** on that database.
- **Images not saving:** set `uploads/properties/` to `755`/`775` owned by the cPanel account.

---

## WhatsApp Click-to-Chat Integration

Official WhatsApp click-to-chat links are integrated throughout the application using the required format:
- Base URL: `https://wa.me/{phone}?text={encoded_message}`
- Property Detail Page message:
  `Hello Peniel Properties, I am interested in [Property Title], reference [Property Reference]. Please provide more information.`
- Centrally managed phone number configurable in `admin/settings.php` or `app/config/config.php`.

---

## Security Implementation

1. **SQL Injection Prevention:** 100% of database interactions utilize PDO prepared statements with parameter binding.
2. **XSS Mitigation:** All dynamic outputs are escaped using `e()` (`htmlspecialchars` with `ENT_QUOTES` and `UTF-8`).
3. **CSRF Tokens:** All state-changing POST forms (client inquiries, admin login, property creation, editing, deletion) require valid session CSRF tokens verified via `hash_equals()`.
4. **Authentication Hardening:** Passwords hashed with `PASSWORD_BCRYPT`. Sessions use `session.cookie_httponly = 1`, `session.use_only_cookies = 1`, and regenerate session IDs on successful login.
5. **Upload Security:** Server-side MIME inspection via `finfo`, strict file extension whitelist (`jpg`, `png`, `webp`), size caps (6MB), cryptographically random filenames, and `.htaccess` execution prevention.
6. **Direct Access Protection:** `.htaccess` rules prevent direct access to `app/`, `database/`, and sensitive server configuration files.
