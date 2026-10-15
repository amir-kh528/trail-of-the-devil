<div align="center">
  <a href="#english"><strong>English</strong></a> |
  <a href="#persian"><strong>فارسی</strong></a> |
  <a href="#russian"><strong>Русский</strong></a>
</div>

<hr />

<div id="english">

# Trail of the Devil

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

</div>

<hr />

<div id="persian">

# Trail of the Devil

یک پروژه‌ی سبک و کاربردی PHP برای ساخت یک صفحه فرود مدرن کسب‌وکار با فرم تماس و داشبورد مدیریت پیام‌ها.

## معرفی پروژه

این پروژه برای نمایش یک وب‌سایت ساده اما حرفه‌ای برای آژانس دیجیتال یا استارتاپ طراحی شده است. در این پروژه یک صفحه اصلی پاسخ‌گو، فرم تماس برای ثبت پیام‌ها و یک داشبورد مدیریتی برای مشاهده، جست‌وجو، ویرایش و حذف پیام‌ها وجود دارد.

## ویژگی‌ها

- صفحه فرود پاسخ‌گو برای آژانس یا استارتاپ
- فرم تماس با اعتبارسنجی و بازخورد ناهمزمان
- ذخیره پیام‌ها در پایگاه داده MySQL
- داشبورد ادمین برای نمایش و فیلتر پیام‌ها
- مدیریت وضعیت خوانده/نخوانده
- عملیات حذف دسته‌جمعی و علامت‌گذاری دسته‌جمعی
- چیدمان راست‌به‌چپ فارسی با فونت و استایل اختصاصی

## ساختار پوشه‌ها

```text
test project/
├── index.php                     # صفحه اصلی و فرم تماس
├── setup_db.php                 # اسکریپت راه‌اندازی پایگاه داده
├── config/
│   └── db.php                   # تنظیمات و راه‌اندازی پایگاه داده
├── admin/
│   └── view_messages.php        # داشبورد مدیریت پیام‌ها
├── assets/
│   ├── css/
│   │   ├── style.css            # استایل‌های صفحه اصلی
│   │   └── admin.css            # استایل‌های پنل مدیریت
│   ├── js/
│   │   └── script.js            # تعاملات فرانت‌اند
│   └── fonts/
│       ├── fonts.css            # تعریف فونت‌ها
│       └── *.woff2              # فایل‌های فونت فارسی
├── README.md
└── .gitignore
```

## راهنمای نصب و راه‌اندازی

1. Apache و MySQL را در XAMPP روشن کنید.
2. تنظیمات پایگاه داده را در `config/db.php` یا از طریق متغیرهای محیطی وارد کنید.
3. پروژه را در مرورگر باز کنید:
   - `http://localhost/test%20project/`
4. برای اولین اجرا، فایل راه‌اندازی را باز کنید:
   - `http://localhost/test%20project/setup_db.php`
5. در نهایت پنل ادمین را باز کنید:
   - `http://localhost/test%20project/admin/view_messages.php`

## تکنولوژی‌های استفاده شده

- PHP
- MySQL
- JavaScript
- HTML
- CSS
- XAMPP

</div>

<hr />

<div id="russian">

# Trail of the Devil

Лёгкий проект на PHP для современной страницы бизнеса с формой обратной связи и административной панелью для управления входящими сообщениями.

## О проекте

Этот проект создан как простой, но профессиональный локальный веб-проект для digital-агентства или стартапа. В него входят адаптивная landing page, форма обратной связи, сохраняющая заявки в MySQL, и административная панель для просмотра, поиска, редактирования и удаления сообщений.

## Возможности

- Адаптивная landing page для digital-агентства или стартапа
- Форма обратной связи с валидацией и AJAX-ответом
- Хранение сообщений в MySQL
- Панель администратора для просмотра и фильтрации сообщений
- Управление статусом прочитано/не прочитано
- Массовое удаление и пометка сообщений
- RTL-структура для Persian с пользовательской типографикой и стилями

## Структура проекта

```text
test project/
├── index.php                     # Главная страница и форма связи
├── setup_db.php                 # Скрипт настройки базы данных
├── config/
│   └── db.php                   # Конфигурация и инициализация БД
├── admin/
│   └── view_messages.php        # Административная панель управления сообщениями
├── assets/
│   ├── css/
│   │   ├── style.css            # Стили главной страницы
│   │   └── admin.css            # Стили панели администратора
│   ├── js/
│   │   └── script.js            # Front-end логика
│   └── fonts/
│       ├── fonts.css            # Объявления шрифтов
│       └── *.woff2              # Файлы шрифтов для Persian
├── README.md
└── .gitignore
```

## Установка и настройка

1. Запустите Apache и MySQL через XAMPP.
2. Укажите параметры подключения к базе данных в `config/db.php` или через переменные окружения.
3. Откройте проект в браузере:
   - `http://localhost/test%20project/`
4. Для первого запуска откройте скрипт настройки:
   - `http://localhost/test%20project/setup_db.php`
5. После этого откройте административную панель:
   - `http://localhost/test%20project/admin/view_messages.php`

## Используемые технологии

- PHP
- MySQL
- JavaScript
- HTML
- CSS
- XAMPP

</div>
