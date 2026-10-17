# Trail of the Devil

[**English**](README.md) | [**فارسی**](README.fa.md) | [**Русский**](README.ru.md)

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
│   ├── db.php                   # اتصال فقط‌خواندنی از نظر ساختار schema
│   ├── admin_auth.php           # احراز هویت و محدودسازی تلاش ورود
│   ├── requests.php             # اعتبارسنجی و ذخیره درخواست‌ها
│   ├── security.php             # نشست و CSRF
│   └── validation.php           # اعتبارسنجی سمت سرور
├── admin/
│   ├── login.php                # ورود مدیر
│   └── view_messages.php        # داشبورد محافظت‌شده پیام‌ها
├── database/
│   ├── migrate.php              # اجرای مهاجرت نسخه‌بندی‌شده فقط از CLI
│   └── migrations/              # مهاجرت‌های افزایشی دیتابیس
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
├── README.fa.md
├── README.ru.md
├── .env.example                 # فقط مرجع نام متغیرهای محیطی
├── .htaccess                    # مسیریابی ۴۰۴ و جلوگیری از فهرست پوشه‌ها
├── 404.php                      # صفحه فارسی پیدا نشدن
└── .gitignore
```

## راهنمای نصب و راه‌اندازی

1. Apache و MySQL را در XAMPP روشن کنید.
2. متغیرهای `DB_HOST`، `DB_NAME`، `DB_USER`، `DB_PASS`، `ADMIN_USERNAME` و `ADMIN_PASSWORD_HASH` را در محیطی که PHP به آن دسترسی دارد تنظیم کنید. فایل `.env.example` فقط مرجع است و برنامه آن را بارگذاری نمی‌کند.
3. هش رمز مدیر را به‌صورت محلی با `password_hash($password, PASSWORD_DEFAULT)` بسازید و فقط هش را در `ADMIN_PASSWORD_HASH` قرار دهید؛ رمز و هش را وارد Git نکنید.
4. پیش از مهاجرت دیتابیس موجود یا عملیاتی، نسخه پشتیبان تهیه و صحت آن را بررسی کنید. سپس از ریشه پروژه، مهاجرت‌ها را با CLI اجرا کنید:
   - `php database/migrate.php`
   - برای `APP_ENV=production` پس از تأیید پشتیبان، از `php database/migrate.php --backup-confirmed` استفاده کنید.
   - صفحات وب مهاجرت یا پاک‌سازی دیتابیس اجرا نمی‌کنند. `setup_db.php` فقط CLI است و همین runner را اجرا می‌کند.
5. پروژه را در مرورگر باز کنید:
   - `http://localhost/test%20project/`
6. صفحه ورود مدیر را باز کرده و با اطلاعات محیطی تنظیم‌شده وارد شوید:
   - `http://localhost/test%20project/admin/login.php`
7. پس از ورود، داشبورد ادمین را باز کنید:
   - `http://localhost/test%20project/admin/view_messages.php`

برای اعمال قواعد `.htaccess`، Apache باید اجازه `AllowOverride FileInfo` بدهد. اگر میزبان `.htaccess` را نمی‌پذیرد، صفحه ۴۰۴ را در تنظیمات VirtualHost به `/404.php` متصل کنید.

## تنظیم ورود ادمین در XAMPP ویندوز

`config/admin_auth.php` نام کاربری و هش رمز را فقط با `getenv('ADMIN_USERNAME')` و `getenv('ADMIN_PASSWORD_HASH')` می‌خواند. برنامه فایل `.env` یا `.env.example` را بارگذاری نمی‌کند؛ مقدارهای CLI یا سرور موقت PHP نیز لزوماً به Apache واقعی منتقل نمی‌شوند.

1. هش رمز را روی رایانه خود با PHP بسازید؛ رمز را در آرگومان دستور، سورس، گفتگو یا Git قرار ندهید:

   ```powershell
   php -r "$password = readline('Admin password: '); echo password_hash($password, PASSWORD_DEFAULT), PHP_EOL;"
   ```

2. در فایل `C:\xampp\apache\conf\extra\httpd-xampp.conf`، داخل بلوک موجود `<IfModule env_module>`، متغیرهای زیر را با مقدارهای محلی خود تنظیم کنید. مقادیر داخل `<...>` نمونه‌اند و باید جایگزین شوند؛ هیچ مقدار واقعی را در مخزن ننویسید:

   ```apache
   SetEnv APP_ENV development
   SetEnv DB_HOST localhost
   SetEnv DB_NAME test_project_db
   SetEnv DB_USER <local-database-user>
   SetEnv DB_PASS <local-database-password>
   SetEnv ADMIN_USERNAME <local-admin-name>
   SetEnv ADMIN_PASSWORD_HASH <output-from-password_hash>
   ```

3. فایل را ذخیره کنید و در XAMPP Control Panel، Apache را با Stop متوقف و دوباره با Start اجرا کنید. Restart کردن فقط تب مرورگر کافی نیست.

4. برای بررسی دسترسی Apache، موقتاً فایل `check-admin-env.php` را در ریشه پروژه با محتوای زیر بسازید، آن را از `http://localhost/test%20project/check-admin-env.php` باز کنید و بلافاصله فایل را حذف کنید. این بررسی فقط وجود متغیرها را گزارش می‌کند و مقدارشان را چاپ نمی‌کند:

   ```php
   <?php
   if (!in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)) {
      http_response_code(404);
      exit;
   }

   foreach (['ADMIN_USERNAME', 'ADMIN_PASSWORD_HASH'] as $name) {
      echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ': ';
      echo getenv($name) !== false && getenv($name) !== '' ? 'configured' : 'missing';
      echo "<br>";
   }
   ```

   `php -r "var_dump(getenv(...))"` در ترمینال فقط محیط CLI را می‌سنجد و جای این بررسی Apache را نمی‌گیرد.

تا وقتی هر دو متغیر `ADMIN_USERNAME` و `ADMIN_PASSWORD_HASH` در فرایند Apache تنظیم و بررسی نشده‌اند، صفحه ورود عمداً HTTP 503 می‌دهد و ورود ادمین روی Apache آماده نیست. برای محیط عملیاتی، `APP_ENV=production` و کاربر دیتابیس غیر `root` با رمز غیرخالی لازم است. تغییر تنظیمات Apache یا restart آن از طرف این پروژه انجام نشده است.

## تکنولوژی‌های استفاده شده

- PHP
- MySQL
- JavaScript
- HTML
- CSS
- XAMPP

## نکات

- این پروژه برای توسعه محلی و تست طراحی شده است.
- محیط تولید به کاربر دیتابیس غیر `root` با رمز غیرخالی و اعتبارنامه مدیر در متغیر محیطی نیاز دارد.
- فایل `.env` و نسخه‌های پشتیبان دیتابیس را وارد Git نکنید؛ `.env.example` محل نگهداری رمز نیست.
- داشبورد ادمین از فیلتر، حذف دسته‌جمعی، علامت‌گذاری خوانده/نخوانده و ویرایش درجا پشتیبانی می‌کند.
