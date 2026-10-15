# Trail of the Devil

A small PHP project for a modern business landing page with a contact form and an admin dashboard for managing submitted messages.

## Features

- Responsive landing page for a digital agency / startup
- Contact form that saves messages into MySQL
- Admin panel to view, search, read/unread, edit, and delete messages
- RTL Persian layout with custom styling
- Lightweight JavaScript-powered form handling and bulk actions

## Project structure

- `index.php` — main landing page and contact form
- `admin/view_messages.php` — admin panel for managing incoming messages
- `config/db.php` — database connection and table initialization
- `setup_db.php` — database setup script
- `assets/css/style.css` — landing page styles
- `assets/css/admin.css` — admin dashboard styles
- `assets/js/script.js` — frontend interactions
- `assets/fonts/` — custom fonts and Persian typography assets

## Tech stack

- PHP
- MySQL
- JavaScript
- HTML / CSS
- XAMPP local server

## Setup

1. Start Apache and MySQL in XAMPP.
2. Make sure your database credentials are configured in the environment or in `config/db.php`.
3. Open the project in a browser, for example:
   - `http://localhost/test%20project/`
4. For the first run, open the setup script:
   - `http://localhost/test%20project/setup_db.php`
5. Then open the admin panel:
   - `http://localhost/test%20project/admin/view_messages.php`

## Notes

- The project is built for local development and testing.
- For production, store database secrets in environment variables instead of hardcoded values.
- The admin page supports filtering, bulk deletion, marking as read/unread, and inline editing.
