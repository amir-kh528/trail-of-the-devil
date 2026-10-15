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
│   └── db.php                   # Database configuration and initialization
├── admin/
│   └── view_messages.php        # Admin dashboard for message management
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
└── .gitignore
```

## Installation and setup

1. Start Apache and MySQL in XAMPP.
2. Make sure your database settings are configured in `config/db.php` or through environment variables.
3. Open the project in a browser:
   - `http://localhost/test%20project/`
4. For the first run, open the setup script:
   - `http://localhost/test%20project/setup_db.php`
5. Open the admin panel:
   - `http://localhost/test%20project/admin/view_messages.php`

## Tech stack

- PHP
- MySQL
- JavaScript
- HTML
- CSS
- XAMPP

## Notes

- The project is built for local development and testing.
- For production, store database secrets in environment variables instead of hardcoded values.
- The admin panel supports filtering, bulk deletion, marking as read/unread, and inline editing.
