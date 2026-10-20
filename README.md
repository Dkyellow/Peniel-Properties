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
│       ├── logo.svg          # Approved Peniel Properties horizontal logo
│       ├── logo-white.svg    # Transparent header logo for navy panels
│       └── favicon.svg       # Favicon architectural mark
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
2. Click **Import** in the top navigation bar.
3. Click **Choose File** and select:
   `c:\Users\leslie\Documents\Web Projecta\Peniel Properties\database\database.sql`
4. Click **Import** (or **Go**) at the bottom.
5. The database `peniel_properties` and all tables (`properties`, `property_images`, `inquiries`, `admins`, `site_settings`) will be created and populated with demonstration data.

### Step 4: Verify Database Connection
Open `app/config/config.php` and verify your credentials (default for XAMPP is user `root` with empty password):
```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'peniel_properties');
define('DB_USER', 'root');
define('DB_PASS', '');
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

### 1. Upload Project Files
1. Log into your hosting **cPanel**.
2. Open **File Manager** and enter `public_html` (or your addon domain directory).
3. Upload all project files maintaining the folder structure.
4. Ensure the `.htaccess` files are visible and present.

### 2. Create MySQL Database & User
1. In cPanel, navigate to **MySQL® Databases**.
2. Create a new database (e.g. `cpaneluser_peniel`).
3. Create a new database user with a strong password.
4. Add the user to the database and grant **ALL PRIVILEGES**.

### 3. Import Schema via cPanel phpMyAdmin
1. In cPanel, open **phpMyAdmin**.
2. Select your newly created database from the left sidebar.
3. Click **Import**, choose `database/database.sql`, and execute.

### 4. Configure Production Credentials
Edit `app/config/config.php` (or set environment variables):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cpaneluser_peniel');
define('DB_USER', 'cpaneluser_dbuser');
define('DB_PASS', 'YourStrongPasswordHere');
```

### 5. File Permissions
Ensure the upload directory has write permissions:
- `uploads/properties/` &rarr; `755` (or `775` depending on server environment)

### 6. SSL / HTTPS
Enable **AutoSSL** or **Let's Encrypt** in cPanel for your domain.

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
