# Trail of the Devil

[**English**](README.md) | [**فارسی**](README.fa.md) | [**Русский**](README.ru.md)

A lightweight PHP project for a modern business landing page with a contact form and an admin dashboard for managing incoming messages.

## Overview

This project was designed as a simple but professional local web application for a digital agency or startup. It includes a responsive landing page, a contact form that stores submissions in MySQL, and an admin panel for viewing, searching, editing, and deleting messages.

## Features

- Responsive landing page for a digital agency or startup
- Contact form with validation and AJAX-style feedback
- MySQL-based storage for submitted messages
- Admin dashboard for listing and filtering messages
- Read/unread message status management
- Bulk delete and bulk mark actions
- RTL Persian layout with custom typography and styling

## Project structure

```text
test project/
├── index.php                     # Landing page and contact form
├── setup_db.php                 # Database setup script
├── config/
│   ├── db.php                   # Connection-only database accessor
│   ├── admin_auth.php           # Admin credential and rate-limit helpers
│   ├── requests.php             # Validated request persistence
│   ├── security.php             # Session and CSRF helpers
│   └── validation.php           # Server-side request validation
├── admin/
│   ├── login.php                # Admin sign-in
│   └── view_messages.php        # Protected admin dashboard
├── database/
│   ├── migrate.php              # CLI-only versioned migration runner
│   └── migrations/              # Additive schema migrations
├── assets/
│   ├── css/
│   │   ├── style.css            # Landing page styles
│   │   └── admin.css            # Admin panel styles
│   ├── js/
│   │   └── script.js            # Front-end interactions
│   └── fonts/
│       ├── fonts.css            # Font declarations
│       └── *.woff2              # Persian font files
├── README.md
├── README.fa.md
├── README.ru.md
├── .env.example                 # Environment variable reference only
├── .htaccess                    # Apache 404 routing and directory listing protection
├── 404.php                      # Persian not-found page
└── .gitignore
```

## Installation and setup

1. Start Apache and MySQL in XAMPP.
2. Configure `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `ADMIN_USERNAME`, and `ADMIN_PASSWORD_HASH` as environment variables available to PHP. `.env.example` documents the names but is not loaded by the application.
3. Generate the admin password hash locally with PHP's `password_hash($password, PASSWORD_DEFAULT)` and set only the resulting hash in `ADMIN_PASSWORD_HASH`. Do not put the password or hash in source control.
4. Before migrating an existing/production database, create and verify a backup. Then run the migrations from a CLI in the project root:
   - `php database/migrate.php`
   - For `APP_ENV=production`, explicitly confirm the verified backup with `php database/migrate.php --backup-confirmed`.
   - The web pages never run migrations or database cleanup. `setup_db.php` is CLI-only and delegates to the same runner.
5. Open the project in a browser:
   - `http://localhost/test%20project/`
6. Open the admin login page and sign in with the environment-configured username/password:
   - `http://localhost/test%20project/admin/login.php`
7. Open the admin panel after signing in:
   - `http://localhost/test%20project/admin/view_messages.php`

Apache must allow the project `.htaccess` rules (at least `AllowOverride FileInfo`). If the host does not permit `.htaccess`, configure its not-found document to `/404.php` in the virtual host instead.

## Tech stack

- PHP
- MySQL
- JavaScript
- HTML
- CSS
- XAMPP

## Notes

- The project is built for local development and testing.
- Production requires a non-root database account with a non-empty password and environment-configured admin credentials.
- Keep `.env` and database backups out of Git; `.env.example` is documentation, not a secret store.
- The admin panel supports filtering, bulk deletion, marking as read/unread, and inline editing.
