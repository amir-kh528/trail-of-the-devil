<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شروع پروژه جدید | NovaWeb</title>
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .start-main {
            padding: 4rem 0 5rem;
            min-height: calc(100vh - 78px);
        }

        .start-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .start-header h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            color: var(--dark);
            margin-bottom: 0.75rem;
        }

        .start-header p {
            color: var(--muted);
            font-size: 1.1rem;
            max-width: 560px;
            margin: 0 auto;
        }

        .choice-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.75rem;
            align-items: stretch;
        }

        .choice-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 2.4rem 2rem;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .choice-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 50px rgba(76, 70, 150, 0.18);
        }

        .choice-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(108, 99, 255, 0.12);
            color: var(--primary-dark);
            font-size: 1.8rem;
            margin-bottom: 1.4rem;
        }

        .choice-card h2 {
            color: var(--dark);
            font-size: 1.55rem;
            margin-bottom: 0.9rem;
        }

        .choice-card p {
            color: var(--muted);
            flex-grow: 1;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .choice-card .primary-btn,
        .choice-card .secondary-btn {
            width: 100%;
            text-align: center;
            cursor: default;
            pointer-events: none;
            user-select: none;
        }

        @media (max-width: 768px) {
            .start-main {
                padding: 2.5rem 0 3.5rem;
            }

            .choice-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar container">
            <div class="brand">NovaWeb</div>
        </nav>
    </header>

    <main class="start-main">
        <div class="container">
            <div class="start-header">
                <h1>شروع پروژه جدید</h1>
                <p>مسیر مناسب خودتان را انتخاب کنید تا پروژه را از همان نقطه شروع کنیم.</p>
            </div>

            <div class="choice-grid">
                <article class="choice-card">
                    <div class="choice-icon">✎</div>
                    <h2>فقط توضیح می‌دم</h2>
                    <p>ترجیح می‌دم نیازم رو بنویسم و شما طراحی و اجرا رو انجام بدید.</p>
                    <span class="primary-btn">ادامه با توضیح متنی</span>
                </article>

                <article class="choice-card">
                    <div class="choice-icon">◈</div>
                    <h2>خودم ظاهر رو مشخص می‌کنم</h2>
                    <p>می‌خوام قالب، رنگ، فونت و سبک دکمه‌ها رو خودم انتخاب کنم.</p>
                    <span class="secondary-btn">شروع طراحی ظاهری</span>
                </article>
            </div>
        </div>
    </main>
</body>
</html>
